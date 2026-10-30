<?php

namespace App\Http\Controllers;

use App\Models\BloodGroup;
use App\Models\Category;
use App\Models\Division;
use App\Models\Education;
use App\Models\EducationYear;
use App\Models\Motamot;
use App\Models\News;
use App\Models\NewsUserView;
use App\Models\NewsView;
use App\Models\PathockProfile;
use App\Models\Product;
use App\Models\ReporterArea;
use App\Models\ReporterProfile;
use App\Models\ReporterType;
use App\Models\SiteSetting;
use App\Models\StockMoney;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class UserController extends Controller
{
    public function home()
    {
        $divisions = Division::all();
        $video = Video::all();

        $homeCategories = Category::whereIn('slug', ['jateey', 'sarades'])
            ->with(['news' => function ($q) {
                $q->where('status', 1)
                    ->latest()
                    ->take(5);
            }])->get();

        $rajcategories = Category::whereIn('slug', ['rajneeti', 'antrjatik', 'kheladhula'])
            ->with(['news' => function ($q) {
                $q->where('status', 1)->latest()->take(5);
            }])->get();

        $techCategories = Category::whereIn('slug', ['przukti', 'binodn'])
            ->with(['news' => function ($q) {
                $q->where('status', 1)
                    ->latest()
                    ->take(10);
            }])->get();
        $subCategories = SubCategory::with(['category', 'news' => function ($q) {
            $q->where('status', 1)->latest()->take(6);
        }])->whereIn('slug', ['swasthz-oo-cikittsa', 'ain-adalt'])->get();

        $sompadokiyos = SubCategory::with(['category', 'news' => function ($q) {
            $q->where('status', 1)->latest()->take(6);
        }])->whereIn('slug', ['smpadkeez', 'orthneeti'])->get();

        $mainCategories = Category::with(['news' => function ($q) {
            $q->where('status', 1)->latest()->take(6);
        }])->whereIn('slug', ['krrishi'])->get();

        $subCategory = SubCategory::with([
            'category',
            'news' => function ($q) {
                $q->where('status', 1)->latest()->take(6);
            },
        ])->where('slug', 'cakurir-khbr')->first();

        $sarades = Category::where('slug', 'sarades')->first();

        $bivag = Category::where('slug', 'bivageey-sngbad')
            ->with('subcategories')
            ->first();

        $allNews = News::whereIn('category_id', [
            optional($sarades)->id,
            optional($bivag)->id,
        ])
            ->where('status', 1)
            ->latest()
            ->take(100)
            ->get();

        return Inertia::render('Home', [
            'divisions' => $divisions,
            'homeCategories' => $homeCategories,
            'rajcategories' => $rajcategories,
            'techCategories' => $techCategories,
            'subCategories' => $subCategories,
            'sompadokiyos' => $sompadokiyos,
            'subCategory' => $subCategory,
            'mainCategories' => $mainCategories,
            'sarades' => $sarades,
            'bivag' => $bivag,
            'allNews' => $allNews,
            'video' => $video,
        ]);
    }

    public function allNews($categorySlug, $subcategorySlug = null)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $subcategory = null;

        if ($subcategorySlug) {
            $subcategory = SubCategory::where('slug', $subcategorySlug)
                ->where('category_id', $category->id)
                ->firstOrFail();
        }

        $newsQuery = News::where('status', 1)->latest();

        if ($subcategory) {
            $newsQuery->where('subcategory_id', $subcategory->id);
        } else {
            $newsQuery->where('category_id', $category->id);
        }

        $news = $newsQuery->get();

        return Inertia::render('FrontEnd/AllNews/AllNews', [
            'category' => $category,
            'subcategory' => $subcategory,
            'news' => $news,
        ]);
    }

    public function newsArchive(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $news = News::whereDate('created_at', $date)
            ->latest()
            ->paginate(12);

        return Inertia::render('FrontEnd/AllNews/Archive', [
            'news' => $news,
            'date' => $date,
        ]);
    }

    public function newsDetails($slug)
    {
        $news = News::with([
            'user.reporterProfile.divisionData',
            'user.reporterProfile.districtData',
            'user.reporterProfile.upazilaData',
            'user.reporterProfile.unionData',
            'user.reporterProfile.cityCorporationData',
            'user.reporterProfile.wardData',
            'comments',
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedNews = News::where('category_id', $news->category_id)
            ->where('id', '!=', $news->id)
            ->latest()
            ->take(4)
            ->get();

        // ---------- Reporter Types (safe) ----------
        if ($news->user && $news->user->reporterProfile) {
            $profile = $news->user->reporterProfile;

            $typeIds = array_filter(
                explode(',', $profile->reporter_types ?? ''),
                fn ($id) => is_numeric($id)
            );

            $profile->types = ! empty($typeIds)
                ? ReporterType::whereIn('id', $typeIds)->get()
                : collect();
        }

        // ---------- Today's Views ----------
        $news->current_view = NewsView::where('news_id', $news->id)
            ->whereDate('created_at', today())
            ->count();

        return Inertia::render('FrontEnd/AllNews/Details', [
            'news' => $news,
            'relatedNews' => $relatedNews,
        ]);
    }

    public function countView(Request $request)
    {
        $news = News::findOrFail($request->news_id);

        $ip = $request->ip();

        $userId = Auth::id();

        // Check already viewed today
        $alreadyViewed = NewsView::where('news_id', $news->id)
            ->where(function ($query) use ($ip, $userId) {

                if ($userId) {

                    $query->where('user_id', $userId);
                } else {

                    $query->where('ip_address', $ip);
                }
            })
            ->whereDate('created_at', today())
            ->exists();

        if (! $alreadyViewed) {

            // Save View
            NewsView::create([
                'news_id' => $news->id,
                'user_id' => $userId,
                'ip_address' => $ip,
            ]);

            // Increment Total View
            $news->increment('total_view');

            // USER EARNING
            if ($userId) {

                $alreadyUserViewed = NewsUserView::where('user_id', $userId)
                    ->where('news_id', $news->id)
                    ->whereDate('created_at', today())
                    ->exists();

                if (! $alreadyUserViewed) {

                    NewsUserView::create([
                        'user_id' => $userId,
                        'news_id' => $news->id,
                    ]);

                    $sitesetting = SiteSetting::first();

                    $perViewAmount = $sitesetting->news_view ?? 0;

                    $user = User::find($userId);

                    $user->earning_from_views += $perViewAmount;

                    $user->marketing_wallet += $perViewAmount;

                    $user->total_earning += $perViewAmount;

                    $user->save();

                    $this->processMarketingWallet($user);
                }
            }
        }

        // Today's Current View Count
        $currentView = NewsView::where('news_id', $news->id)
            ->whereDate('created_at', today())
            ->count();

        return response()->json([
            'success' => true,
            'current_view' => $currentView,
            'total_view' => $news->fresh()->total_view,
        ]);
    }

    // store comment
    public function storeComment(Request $request, News $news)
    {
        $request->validate(['body' => 'required']);
        $news->comments()->create([
            'body' => $request->body,
        ]);

        return redirect()->back()->with('status', 'Comment Added successfully');
    }

    private function distributeGenerationCommission($user, $amount)
    {
        // 10 generation levels
        $levels = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

        $currentUser = $user;

        foreach ($levels as $percent) {

            if (! $currentUser->referred_by) {
                break;
            }

            $parent = User::find($currentUser->referred_by);

            if (! $parent) {
                break;
            }

            $commission = ($amount * $percent) / 100;

            // generation commission
            $parent->generation_commission += $commission;

            // marketing wallet
            $parent->marketing_wallet += $commission;

            // total earning
            $parent->total_earning += $commission;

            $parent->save();

            $currentUser = $parent;
        }
    }

    private function processMarketingWallet($user)
    {
        if ($user->marketing_wallet >= 100) {

            $amount = $user->marketing_wallet;

            // 70% main balance
            $balanceAmount = ($amount * 70) / 100;

            // 30% generation
            $generationAmount = ($amount * 30) / 100;

            // add balance
            $user->total_balance += $balanceAmount;

            // remove wallet
            $user->marketing_wallet = 0;

            $user->save();

            // distribute generation
            $this->distributeGenerationCommission(
                $user,
                $generationAmount
            );
        }
    }

    public function pathockDashboard()
    {
        $user = Auth::user();

        return Inertia::render('FrontEnd/Dashboard/PathockDashboard', [

            'role' => $user->role,

            'affiliate_link' => url('/register/pathock?ref='.$user->ref_id),

            'products' => Product::latest()->get(),
            'stockMoney' => StockMoney::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereNotNull('amount')
                ->sum('amount'),

            'wallets' => [
                'balance' => $user->total_balance,
                'total_income' => $user->total_earning,
                'daily_return' => $user->daily_return_wallet,
                'gift_wallet' => $user->gift_wallet,
                'marketing_wallet' => $user->marketing_wallet,
            ],
        ]);
    }

    public function reporterDashboard()
    {
        $user = Auth::user();

        // Count all news created by this reporter
        $totalNews = News::where('created_by', $user->id)->count();

        // Count pending news (status = 0)
        $totalPending = News::where('created_by', $user->id)
            ->where('status', 0)
            ->count();

        // Count published news (status = 1)
        $totalPublished = News::where('created_by', $user->id)
            ->where('status', 1)
            ->count();

        return Inertia::render('FrontEnd/Dashboard/ReporterDashboard', [
            'role' => $user->role,
            'stats' => [
                'total_news' => $totalNews,
                'pending_news' => $totalPending,
                'published_news' => $totalPublished,
                'reporter_income' => $user->reporter_income ?? 0,
                'total_withdraw' => $user->total_withdraw ?? 0,
                'total_balance' => ($user->reporter_income ?? 0) - ($user->total_withdraw ?? 0),
            ],
        ]);
    }

    public function motamotDashboard()
    {
        $user = Auth::user();
        $motamots = Motamot::where('user_id', $user->id)->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/MotamotDashboard', [
            'role' => $user->role,
            'motamots' => $motamots,
        ]);
    }

    public function pathockProfile()
    {
        $user = Auth::user();

        $profile = PathockProfile::with([
            'division:id,name,bn_name',
            'district:id,name,bn_name',
            'upazilla:id,name,bn_name',
            'union:id,name,bn_name',
            'cityCorporation:id,name,bn_name',
            'ward:id,name,bn_name',
        ])->where('user_id', $user->id)->first();

        return Inertia::render('FrontEnd/Dashboard/Pathock/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->image,
            ],
            'profile' => $profile,
            // IMPORTANT: expose BOTH name and bn_name so Vue can pick.
            'divisions' => Division::select('id', 'name', 'bn_name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function pathockPayment()
    {
        return Inertia::render('FrontEnd/Dashboard/Pathock/Payment');
    }

    public function pathockProfileUpdate(Request $request)
    {
        $user = Auth::user();

        // ---------- VALIDATION ----------
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:20|unique:users,phone,'.$user->id,

            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'upazilla_id' => 'nullable|exists:upazilas,id',
            'union_id' => 'nullable|exists:unions,id',
            'city_corporation_id' => 'nullable|exists:city_corporations,id',
            'ward_id' => 'nullable|exists:wards,id',

            'village' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        // Only require password fields if changing password
        if ($request->filled('password') || $request->filled('old_password')) {
            $rules['old_password'] = 'required|string';
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules);

        // ---------- OLD PASSWORD CHECK ----------
        if ($request->filled('password')) {
            if (! Hash::check($request->old_password, $user->password)) {
                return back()->withErrors([
                    'old_password' => 'Old password is incorrect',
                ])->withInput();
            }
        }

        // ---------- LOCATION EXCLUSIVITY ----------
        // If district is selected, clear city corporation path (and vice versa)
        $districtId = $validated['district_id'] ?? null;
        $cityCorporationId = $validated['city_corporation_id'] ?? null;

        if (! empty($districtId)) {
            $cityCorporationId = null;
            $wardId = null;
        } elseif (! empty($cityCorporationId)) {
            $districtId = null;
            $upazillaId = null;
            $unionId = null;
        } else {
            $upazillaId = $validated['upazilla_id'] ?? null;
            $unionId = $validated['union_id'] ?? null;
            $wardId = $validated['ward_id'] ?? null;
        }

        // Re-normalize after exclusivity
        $upazillaId = $districtId ? ($validated['upazilla_id'] ?? null) : null;
        $unionId = $upazillaId ? ($validated['union_id'] ?? null) : null;
        $wardId = $cityCorporationId ? ($validated['ward_id'] ?? null) : null;

        // ---------- UPDATE USER ----------
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Avatar upload — delete old first
        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $userData['image'] = $request->file('image')->store('images/avatars', 'public');
        }

        $user->update($userData);

        // ---------- UPDATE / CREATE PATHOCK PROFILE ----------
        PathockProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'division_id' => $validated['division_id'] ?? null,
                'district_id' => $districtId,
                'upazilla_id' => $upazillaId,
                'union_id' => $unionId,
                'city_corporation_id' => $cityCorporationId,
                'ward_id' => $wardId,
                'village' => $validated['village'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
            ]
        );

        return redirect()
            ->back()
            ->with('status', 'Profile updated successfully');
    }

    // motamot profile
    public function motamotProfile()
    {
        $user = Auth::user();

        return Inertia::render('FrontEnd/Dashboard/Motamot/Profile', ['user' => $user]);
    }

    public function motamotProfileUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|unique:users,phone,'.$user->id,
            'password' => 'nullable|confirmed',
            'image' => 'nullable|image',
        ]);

        $data = $request->only(['name', 'email', 'phone']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images/avatars', 'public');
        }

        $user->update($data);

        return redirect()->back()->with('status', 'Profile updated successfully');
    }

    public function editReporter($id)
    {
        $user = User::with(['reporterProfile'])->findOrFail($id);

        return Inertia::render('FrontEnd/Dashboard/Reporter/Profile', [
            'user' => $user,
            'profile' => $user->reporterProfile,
            'bloodGroups' => BloodGroup::select('id', 'name')->orderBy('name')->get(),
            'educations' => Education::select('id', 'name')->orderBy('name')->get(),
            'educationals' => EducationYear::select('id', 'year')->orderBy('year', 'desc')->get(),
            'divisions' => Division::select('id', 'name', 'bn_name')->orderBy('name')->get(),
            'reporterAreas' => ReporterArea::select('id', 'name')->orderBy('name')->get(),
            'reporterTypes' => ReporterType::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function updateReporter(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $profile = ReporterProfile::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.$user->id],
            'dob' => ['required', 'date'],

            // blood_group: Vue sends the NAME ("A+"), not the id.
            'blood_group' => ['required', 'string', 'exists:blood_groups,name'],

            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            // 'address' => ['nullable', 'string', 'max:500'],

            'education' => ['required', 'string', 'max:255'],
            'education_year' => ['required', 'string', 'max:50'],

            'division' => ['nullable', 'integer', 'exists:divisions,id'],
            'district' => ['nullable', 'integer', 'exists:districts,id'],
            'upazila' => ['nullable', 'integer', 'exists:upazilas,id'],
            'union' => ['nullable', 'integer', 'exists:unions,id'],
            'city_corporation' => ['nullable', 'integer', 'exists:city_corporations,id'],
            'ward' => ['nullable', 'integer', 'exists:wards,id'],

            'reporter_area' => ['required', 'string', 'max:255'],
            'nid_number' => ['nullable', 'string', 'max:20'],

            'photo' => ['nullable', 'image', 'max:2048'],
            'signature' => ['nullable', 'image', 'max:2048'],
            'nid_file' => ['nullable', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],

            'reporter_types' => ['nullable', 'array'],
            'reporter_types.*' => ['integer', 'exists:reporter_types,id'],

            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['nullable', 'string'],
        ]);

        // ============ UPDATE USER ============
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        // ============ FILE UPLOADS ============
        $photoPath = $profile->photo;
        $signaturePath = $profile->signature;
        $nidPath = $profile->nid_file;

        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('images/register', 'public');
        }

        if ($request->hasFile('signature')) {
            if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            $signaturePath = $request->file('signature')->store('images/register', 'public');
        }

        if ($request->hasFile('nid_file')) {
            if ($nidPath && Storage::disk('public')->exists($nidPath)) {
                Storage::disk('public')->delete($nidPath);
            }
            $nidPath = $request->file('nid_file')->store('images/register', 'public');
        }

        // ============ UPDATE PROFILE ============
        $profile->update([
            'dob' => $validated['dob'],
            'blood_group' => $validated['blood_group'],
            'father_name' => $validated['father_name'],
            'mother_name' => $validated['mother_name'],
            // 'address' => $validated['address'] ?? null,
            'education' => $validated['education'],
            'education_year' => $validated['education_year'],
            'division' => $validated['division'] ?? null,
            'district' => $validated['district'] ?? null,
            'upazila' => $validated['upazila'] ?? null,
            'union' => $validated['union'] ?? null,
            'city_corporation' => $validated['city_corporation'] ?? null,
            'ward' => $validated['ward'] ?? null,
            'reporter_area' => $validated['reporter_area'],
            'nid_number' => $validated['nid_number'],
            'photo' => $photoPath,
            'signature' => $signaturePath,
            'nid_file' => $nidPath,
            'reporter_types' => ! empty($validated['reporter_types'])
                ? implode(',', $validated['reporter_types'])
                : null,
        ]);

        return redirect()->back()->with('status', 'Reporter Profile Updated Successfully');
    }

    public function applicationForm()
    {
        return Inertia::render('FrontEnd/Dashboard/Reporter/Application');
    }

    public function addNews()
    {
        return Inertia::render('FrontEnd/Dashboard/Reporter/AddNews');
    }
}
