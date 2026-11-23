<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReporterType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReporterActivityController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Reporter/Inactive');
    }

    public function list(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $cutoff = now()->subDays(7);

        $query = User::query()
            ->where('role', 'reporter')                      // ← plain string
            ->whereDoesntHave('news', function ($q) use ($cutoff) {
                $q->where('created_at', '>=', $cutoff);
            })
            ->withCount(['news as total_news'])
            ->withMax('news as last_post_at', 'created_at')
            ->with('reporterProfile');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reporters = $query
            ->orderByRaw('last_post_at IS NULL DESC, last_post_at ASC')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($reporters);
    }

    public function details(User $user)
    {
        abort_unless($user->role === 'reporter', 404);       // ← plain string

        $user->load([
            'reporterProfile.divisionData',
            'reporterProfile.districtData',
            'reporterProfile.upazilaData',
            'reporterProfile.unionData',
            'reporterProfile.cityCorporationData',
            'reporterProfile.wardData',
            'reporterProfile.pourashavaData',
            'reporterProfile.pourashavaWardData',
            'reporterProfile.reporterAreaData',
        ]);

        $profile = $user->reporterProfile;

        $types = collect();
        if ($profile && ! empty($profile->reporter_types)) {
            $ids = array_filter(explode(',', $profile->reporter_types), 'is_numeric');
            if (! empty($ids)) {
                $types = ReporterType::whereIn('id', $ids)->select('id', 'name')->get();
            }
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->role,
                'image'  => $user->image,
                'status' => $user->status,
                'created_at' => optional($user->created_at)->toDateTimeString(),
                'reporter_income' => $user->reporter_income ?? 0,
                'published_news'  => $user->published_news  ?? 0,
            ],
            'profile' => $profile ? [
                'dob'            => $profile->dob,
                'blood_group'    => $profile->blood_group,
                'father_name'    => $profile->father_name,
                'mother_name'    => $profile->mother_name,
                'education'      => $profile->education,
                'education_year' => $profile->education_year,
                'nid_number'     => $profile->nid_number,
                'signature'      => $profile->signature,
                'nid_file'       => $profile->nid_file,
                'division'         => optional($profile->divisionData)->bn_name,
                'district'         => optional($profile->districtData)->bn_name,
                'upazila'          => optional($profile->upazilaData)->bn_name,
                'union'            => optional($profile->unionData)->bn_name,
                'city_corporation' => optional($profile->cityCorporationData)->bn_name,
                'ward'             => optional($profile->wardData)->bn_name,
                'pourashava'             => optional($profile->pourashavaData)->bn_name,
                'pourashava_word'             => optional($profile->pourashavaWardData)->bn_name,
                'reporter_area'             => optional($profile->reporterAreaData)->name,
            ] : null,
            'types' => $types,
            'stats' => [
                'total_news'    => $user->news()->count(),
                'last_post_at'  => $user->news()->max('created_at'),
                'posted_last_7' => $user->news()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'total_views'   => (int) $user->news()->sum('total_view'),
            ],
        ]);
    }
}
