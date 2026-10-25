<?php

namespace App\Http\Controllers;

use App\Models\BloodGroup;
use App\Models\Category;
use App\Models\Division;
use App\Models\Education;
use App\Models\EducationYear;
use App\Models\Motamot;
use App\Models\News;
use App\Models\NewsView;
use App\Models\ReporterArea;
use App\Models\ReporterProfile;
use App\Models\ReporterType;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\NewsUserView;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\StockMoney;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            }
        ])->where('slug', 'cakurir-khbr')->first();

        $sarades = Category::where('slug', 'sarades')->first();

        $bivag = Category::where('slug', 'bivageey-sngbad')
            ->with('subcategories')
            ->first();

        $allNews = News::whereIn('category_id', [
            optional($sarades)->id,
            optional($bivag)->id
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
            'comments'
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedNews = News::where('category_id', $news->category_id)
            ->where('id', '!=', $news->id)
            ->latest()
            ->take(4)
            ->get();

        // Reporter Types
        if ($news->user && $news->user->reporterProfile) {

            $typeIds = explode(',', $news->user->reporterProfile->reporter_types ?? '');

            $types = ReporterType::whereIn('id', $typeIds)->get();

            $news->user->reporterProfile->types = $types;
        } else {

            $news->user->reporterProfile = null;
        }

        // Current Today's Views
        $currentView = NewsView::where('news_id', $news->id)
            ->whereDate('created_at', today())
            ->count();

        $news->current_view = $currentView;

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

        if (!$alreadyViewed) {

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

                if (!$alreadyUserViewed) {

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

            if (!$currentUser->referred_by) {
                break;
            }

            $parent = User::find($currentUser->referred_by);

            if (!$parent) {
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

            'affiliate_link' => url('/register/pathock?ref=' . $user->ref_id),

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
            ]
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
                'total_news'      => $totalNews,
                'pending_news'    => $totalPending,
                'published_news'  => $totalPublished,
                'reporter_income' => $user->reporter_income ?? 0,
                'total_withdraw'  => $user->total_withdraw ?? 0,
                'total_balance'   => ($user->reporter_income ?? 0) - ($user->total_withdraw ?? 0),
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
        return Inertia::render('FrontEnd/Dashboard/Pathock/Profile', ['user' => $user]);
    }

    public function pathockPayment()
    {
        return Inertia::render('FrontEnd/Dashboard/Pathock/Payment');
    }

    public function pathockProfileUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|unique:users,phone,' . $user->id,
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
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|unique:users,phone,' . $user->id,
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
        $user = User::with('reporterProfile')->findOrFail($id);

        return Inertia::render('FrontEnd/Dashboard/Reporter/Profile', [
            'user' => $user,
            'profile' => $user->reporterProfile,
            'bloodGroups' => BloodGroup::all(),
            'educations' => Education::all(),
            'educationals' => EducationYear::all(),
            'divisions' => Division::all(),
            'reporterAreas' => ReporterArea::all(),
            'reporterTypes' => ReporterType::all(),
        ]);
    }

    public function updateReporter(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $profile = ReporterProfile::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|unique:users,phone,' . $user->id,
            'photo' => 'nullable|image',
            'signature' => 'nullable|image',
            'nid_file' => 'nullable|mimes:pdf,jpg,png',
            'dob' => 'required|date',
            'reporter_area' => 'required',
        ]);

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $photoPath = $profile->photo;
        if ($request->hasFile('photo')) {
            if ($profile->photo && Storage::disk('public')->exists($profile->photo)) {
                Storage::disk('public')->delete($profile->photo);
            }
            $photoPath = $request->file('photo')->store('images/register', 'public');
        }

        $signaturePath = $profile->signature;
        if ($request->hasFile('signature')) {
            if ($profile->signature && Storage::disk('public')->exists($profile->signature)) {
                Storage::disk('public')->delete($profile->signature);
            }
            $signaturePath = $request->file('signature')->store('images/register', 'public');
        }

        $nidPath = $profile->nid_file;
        if ($request->hasFile('nid_file')) {
            if ($profile->nid_file && Storage::disk('public')->exists($profile->nid_file)) {
                Storage::disk('public')->delete($profile->nid_file);
            }
            $nidPath = $request->file('nid_file')->store('images/register', 'public');
        }


        $profile->update([
            'dob' => $request->dob,
            'blood_group' => $request->blood_group,
            'father_name' => $request->father_name,
            'mother_name' => $request->mother_name,
            'address' => $request->address,
            'education' => $request->education,
            'education_year' => $request->education_year,
            'division' => $request->division,
            'district' => $request->district,
            'upazila' => $request->upazila,
            'union' => $request->union,
            'reporter_area' => $request->reporter_area,
            'nid_number' => $request->nid_number,
            'photo' => $photoPath,
            'signature' => $signaturePath,
            'nid_file' => $nidPath,
            'reporter_types' => is_array($request->reporter_types)
                ? implode(',', $request->reporter_types)
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
