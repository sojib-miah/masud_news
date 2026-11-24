<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\ReporterArea;
use App\Models\ReporterType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FamilyController extends Controller
{
    public function ourFamily()
    {
        // ---- Family members ----
        $families = Family::orderByDesc('id')
            ->get()
            ->map(fn(Family $f) => [
                'id'          => $f->id,
                'name'        => $f->name,
                'designation' => $f->designation,
                'phone'       => $f->phone,
                'email'       => $f->email,
                'location'    => $f->location,
                'image_url'   => $f->image ? asset('storage/' . $f->image) : null,
                'short_desc'  => $f->short_desc,
            ]);

        // ---- Reporter types lookup ----
        $reporterTypes = ReporterType::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name]);

        $typeNameById = $reporterTypes->pluck('name', 'id')->all();

        // ---- Reporter areas lookup NEW ----
        $areaNameById = ReporterArea::pluck('name', 'id')->all();

        // ---- Reporters ----
        $reporters = User::query()
            ->whereHas('reporterProfile')
            ->with([
                'reporterProfile.divisionData',
                'reporterProfile.districtData',
                'reporterProfile.upazilaData',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function (User $u) use ($typeNameById, $areaNameById) {
                $p = $u->reporterProfile;

                $typeIds = $p?->reporter_types_array ?? [];

                $typeNames = array_values(array_filter(array_map(
                    fn($id) => $typeNameById[$id] ?? null,
                    $typeIds,
                )));

                // reporter_area from reporter_areas table (NOT types)
                $areaId   = $p?->reporter_area;
                $areaName = $areaId !== null
                    ? ($areaNameById[(int) $areaId] ?? null)
                    : null;

                return [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'photo_url'      => $u->image ? asset('storage/' . $u->image) : null,
                    'reporter_area'  => $areaName,
                    'type_names'     => $typeNames,
                    'location'       => $this->buildLocation($p),
                    'reporter_types' => $typeIds,
                    'status'         => $u->status,
                    'is_approved'    => $u->status === 'approved',
                ];
            });

        // ---- Top 10 reporters by number of news posts ----
        $topReportersByNews = User::query()
            ->where('role', 'reporter') // plain string role column
            ->whereHas('news')          // must have at least one news
            ->withCount('news as news_count')
            ->orderByDesc('news_count')
            ->take(10)
            ->get()
            ->map(function (User $u) {
                return [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'photo_url'      => $u->image ? asset('storage/' . $u->image) : null,
                    'news_count'     => $u->news_count,
                    'status'         => $u->status,
                    'is_approved'    => $u->status === 'approved',
                ];
            });

        // ---- Top 10 reporters by total view count (sum of news.total_view) ----
        $topReportersByViews = User::query()
            ->where('role', 'reporter')
            ->whereHas('news')
            ->withSum('news as total_views', 'total_view')
            ->orderByDesc('total_views')
            ->take(10)
            ->get()
            ->map(function (User $u) {
                return [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'photo_url'      => $u->image ? asset('storage/' . $u->image) : null,
                    'total_views'    => (int) ($u->total_views ?? 0),
                    'status'         => $u->status,
                    'is_approved'    => $u->status === 'approved',
                ];
            });

        return Inertia::render('FrontEnd/Family/Family', [
            'families'      => $families,
            'reporters'     => $reporters,
            'reporterTypes' => $reporterTypes,
            'topReportersByNews'   => $topReportersByNews,
            'topReportersByViews'  => $topReportersByViews,
        ]);
    }

    public function familyDetail(Family $family)
    {
        return Inertia::render('FrontEnd/Family/FamilyDetail', [
            'family' => [
                'id'          => $family->id,
                'name'        => $family->name,
                'designation' => $family->designation,
                'phone'       => $family->phone,
                'email'       => $family->email,
                'location'    => $family->location,
                'image_url'   => $family->image ? asset('storage/' . $family->image) : null,
                'short_desc'  => $family->short_desc,
                'long_desc'   => $family->long_desc,
                'created_at'  => optional($family->created_at)->format('d M Y'),
            ],
        ]);
    }

    /**
     * Build a readable location string from ReporterProfile relations.
     */
    protected function buildLocation(?object $p): ?string
    {
        if (! $p) return null;

        $parts = array_filter([
            optional($p->divisionData)->bn_name,
            optional($p->districtData)->bn_name,
            optional($p->upazilaData)->bn_name,
            optional($p->unionData)->bn_name,
            optional($p->cityCorporationData)->bn_name,
            optional($p->wardData)->bn_name,
            optional($p->pourashavaData)->bn_name,
            optional($p->pourashavaWardData)->bn_name,
        ]);

        return $parts ? implode(', ', $parts) : null;
    }

    public function show(User $user)
    {
        $user->load([
            'reporterProfile.divisionData',
            'reporterProfile.districtData',
            'reporterProfile.upazilaData',
            'reporterProfile.unionData',
            'reporterProfile.bloodGroupData',
            'reporterProfile.cityCorporationData',
            'reporterProfile.wardData',
            'reporterProfile.pourashavaData',
            'reporterProfile.pourashavaWardData',
        ]);

        abort_unless($user->reporterProfile, 404);

        $p = $user->reporterProfile;

        // Reporter types -> names
        $typeIds = $p->reporter_types_array;
        $typeNames = $typeIds
            ? ReporterType::whereIn('id', $typeIds)->pluck('name')->all()
            : [];

        // Reporter area -> name (from reporter_areas table, not types!)
        $areaName = null;
        if ($p->reporter_area !== null && $p->reporter_area !== '') {
            $areaName = ReporterArea::whereKey((int) $p->reporter_area)->value('name');
        }

        // Build readable location
        $locationParts = array_filter([
            optional($p->divisionData)->bn_name,
            optional($p->districtData)->bn_name,
            optional($p->upazilaData)->bn_name,
            optional($p->unionData)->bn_name,
            optional($p->cityCorporationData)->bn_name,
            optional($p->wardData)->bn_name,
            optional($p->pourashavaData)->bn_name,
            optional($p->pourashavaWardData)->bn_name,
        ]);

        return Inertia::render('FrontEnd/Family/ReporterDetail', [
            'reporter' => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,

                // Use USER image (consistent with card)
                'photo_url'      => $user->image ? asset('storage/' . $user->image) : null,
                'signature_url'  => $p->signature ? asset('storage/' . $p->signature) : null,
                'nid_url'  => $p->nid_file ? asset('storage/' . $p->nid_file) : null,

                'dob'            => $p->dob,
                'blood_group'    => optional($p->bloodGroupData)->name,
                'father_name'    => $p->father_name,
                'mother_name'    => $p->mother_name,
                'address'        => $p->address,
                'education'      => $p->education,
                'education_year' => $p->education_year,
                'reporter_area'  => $areaName,   // proper name
                'nid_number'     => $p->nid_number,
                'type_names'     => $typeNames,
                'location'       => $locationParts ? implode(', ', $locationParts) : null,
                'joined_at'      => optional($user->created_at)->format('d M Y'),

                'status'         => $user->status,           // 'approved' | 'pending' | ...
                'is_approved'    => $user->status === 'approved',
            ],
        ]);
    }
}
