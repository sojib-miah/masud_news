<?php

namespace App\Http\Controllers;

use App\Models\BloodGroup;
use App\Models\CityCorporation;
use App\Models\District;
use App\Models\Division;
use App\Models\Education;
use App\Models\EducationYear;
use App\Models\PathockProfile;
use App\Models\Pourashava;
use App\Models\PourashavaWard;
use App\Models\ReporterArea;
use App\Models\ReporterProfile;
use App\Models\ReporterType;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('FrontEnd/Register/Register');
    }

    public function pathockRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('FrontEnd/Register/Pathock', [
            'referralCode' => $request->ref,
            'divisions' => Division::select('id', 'bn_name')->orderBy('name')->get(),
        ]);
    }

    public function motamotRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('FrontEnd/Register/Motamot');
    }

    public function reporterRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('FrontEnd/Register/Reporter', [
            'bloodGroups' => BloodGroup::select('id', 'name')->get(),
            'educations' => Education::select('id', 'name')->get(),
            'educationals' => EducationYear::select('id', 'year')->get(),
            'divisions' => Division::select('id', 'bn_name')->get(),
            'reporterAreas' => ReporterArea::select('id', 'name')->get(),
            'reporterTypes' => ReporterType::select('id', 'name')->get(),
        ]);
    }

    public function getDistricts($divisionId)
    {
        return District::where('division_id', $divisionId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getUpazilas($districtId)
    {
        return Upazila::where('district_id', $districtId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getUnions($upazilaId)
    {
        return Union::where('upazilla_id', $upazilaId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getCityCorporations($districtId)
    {
        return CityCorporation::where('district_id', $districtId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getWards($cityCorporationId)
    {
        return Ward::where('city_corporation_id', $cityCorporationId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getPourashava($districtId)
    {
        return Pourashava::where('district_id', $districtId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function getPourashavaWards($pourashavaId)
    {
        return PourashavaWard::where('pourashava_id', $pourashavaId)
            ->select('id', 'bn_name')
            ->orderBy('name')
            ->get();
    }

    public function storePathock(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'email'    => 'nullable|email|unique:users,email',

            'division_id' => 'nullable|exists:divisions,id',
            // District path
            'district_id' => 'nullable|exists:districts,id',
            'upazilla_id' => 'nullable|exists:upazilas,id',
            'union_id'    => 'nullable|exists:unions,id',
            // City corporation path
            'city_corporation_id' => 'nullable|exists:city_corporations,id',
            'ward_id'             => 'nullable|exists:wards,id',
            // Pourashava path
            'pourashava_id'       => 'nullable|exists:pourashavas,id',
            'pourashava_ward_id'  => 'nullable|exists:pourashava_wards,id',

            'village'    => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'ref'        => 'nullable|string|max:255',
        ]);

        // ---------------- LOCATION EXCLUSIVITY ----------------
        // district_id is the common parent for all three sub-paths; keep it.
        // Only one sub-path (district / city corp / pourashava) may have data.
        if (! empty($data['city_corporation_id'])) {
            // City corporation chosen → drop district & pourashava sub-paths
            $data['upazilla_id']        = null;
            $data['union_id']           = null;
            $data['pourashava_id']      = null;
            $data['pourashava_ward_id'] = null;
        } elseif (! empty($data['pourashava_id'])) {
            // Pourashava chosen → drop district & city corp sub-paths
            $data['upazilla_id']        = null;
            $data['union_id']           = null;
            $data['city_corporation_id'] = null;
            $data['ward_id']            = null;
        } else {
            // District path chosen (or nothing) → drop city corp & pourashava
            $data['city_corporation_id'] = null;
            $data['ward_id']            = null;
            $data['pourashava_id']      = null;
            $data['pourashava_ward_id'] = null;
        }

        // ---------------- CREATE USER ----------------
        $user = User::create([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'role'     => 'pathock',
            'email'    => $data['email'] ?? 'pathock' . Str::random(5) . '@pathock.com',
        ]);

        // ---------------- CREATE PROFILE ----------------
        PathockProfile::create([
            'user_id'             => $user->id,
            'division_id'         => $data['division_id']         ?? null,
            'district_id'         => $data['district_id']         ?? null,
            'upazilla_id'         => $data['upazilla_id']         ?? null,
            'union_id'            => $data['union_id']            ?? null,
            'city_corporation_id' => $data['city_corporation_id'] ?? null,
            'ward_id'             => $data['ward_id']             ?? null,
            'pourashava_id'       => $data['pourashava_id']       ?? null,
            'pourashava_ward_id'  => $data['pourashava_ward_id']  ?? null,
            'village'             => $data['village']             ?? null,
            'occupation'          => $data['occupation']          ?? null,
        ]);

        // ---------------- REFERRAL ----------------
        if (! empty($data['ref'])) {
            $refUser = User::where('ref_id', $data['ref'])->first();
            if ($refUser) {
                $user->referred_by = $refUser->id;
                $user->save();
            }
        }

        return redirect()->route('user.login')->with('status', 'Pathock Registered Successfully');
    }

    public function storeMotamot(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed|min:6',
            'email' => 'nullable|email|unique:users,email',
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'motamot',
            'email' => $request->email ?? 'motamot' . Str::random(5) . '@motamot.com',
        ]);

        return redirect()->route('user.login')->with('status', 'Motamot Register Success');
    }

    public function storeReporter(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'blood_group' => 'required|string',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            // 'address' => 'required|string|max:500',
            'education' => 'required|string',
            'education_year' => 'required|string',
            'dob' => 'required|date',

            'division' => 'nullable|exists:divisions,id',
            // District path
            'district' => 'nullable|exists:districts,id',
            'upazila' => 'nullable|exists:upazilas,id',
            'union' => 'nullable|exists:unions,id',
            // City corporation path
            'city_corporation' => 'nullable|exists:city_corporations,id',
            'ward' => 'nullable|exists:wards,id',
            // pourashava path 
            'pourashava'       => 'nullable|exists:pourashavas,id',
            'pourashava_ward'  => 'nullable|exists:pourashava_wards,id',

            'village' => 'nullable|string|max:255',
            'reporter_area' => 'required|string',
            'nid_number' => 'nullable|string|max:30',
            'reporter_types' => 'nullable|array',
            'reporter_types.*' => 'exists:reporter_types,id',

            'image' => 'required|image|max:2048',
            'signature' => 'required|image|max:2048',
            'nid_file' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // ---------- LOCATION EXCLUSIVITY ----------
        // Only one sub-path (upazila / city corp / pourashava) may have data.

        if (! empty($data['city_corporation'])) {
            // City corporation chosen → drop district path & pourashava path
            $data['upazila']         = null;
            $data['union']           = null;
            $data['pourashava']      = null;
            $data['pourashava_ward'] = null;
        } elseif (! empty($data['pourashava'])) {
            // Pourashava chosen → drop district path & city corp path
            $data['upazila']          = null;
            $data['union']            = null;
            $data['city_corporation'] = null;
            $data['ward']             = null;
        } else {
            // Upazila path chosen (or nothing) → drop city corp & pourashava
            $data['city_corporation'] = null;
            $data['ward']             = null;
            $data['pourashava']       = null;
            $data['pourashava_ward']  = null;
        }

        $imagePath = $request->file('image')->store('images/avatars', 'public');
        // ---------- CREATE USER ----------
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? 'reporter' . Str::random(5) . '@reporter.com',
            'phone' => $data['phone'],
            'image' => $imagePath,
            'password' => Hash::make($data['password']),
            'role' => 'reporter',
        ]);

        // ---------- UPLOADS ----------
        $signaturePath = $request->file('signature')->store('images/register', 'public');
        $nidPath = $request->file('nid_file')->store('images/register', 'public');

        // ---------- PROFILE ----------
        ReporterProfile::create([
            'user_id' => $user->id,
            'dob' => $data['dob'],
            'blood_group' => $data['blood_group'],
            'father_name' => $data['father_name'],
            'mother_name' => $data['mother_name'],
            // 'address' => $data['address'],
            'education' => $data['education'],
            'education_year' => $data['education_year'],
            'division' => $data['division'] ?? null,
            'district' => $data['district'] ?? null,
            'upazila' => $data['upazila'] ?? null,
            'union' => $data['union'] ?? null,
            'city_corporation' => $data['city_corporation'] ?? null,
            'ward' => $data['ward'] ?? null,
            'pourashava' => $data['pourashava'] ?? null,
            'pourashava_ward' => $data['pourashava_ward'] ?? null,
            'village' => $data['village'] ?? null,
            'reporter_area' => $data['reporter_area'],
            'nid_number' => $data['nid_number'] ?? null,
            'signature' => $signaturePath,
            'nid_file' => $nidPath,
            'reporter_types' => ! empty($data['reporter_types'])
                ? implode(',', $data['reporter_types'])
                : null,
            'is_approved' => false,
        ]);

        return redirect()
            ->route('user.login')
            ->with('status', 'Reporter Registered Successfully');
    }
}
