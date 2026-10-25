<?php

namespace App\Http\Controllers;

use App\Models\BloodGroup;
use App\Models\District;
use App\Models\Division;
use App\Models\Education;
use App\Models\EducationYear;
use App\Models\PathockProfile;
use App\Models\ReporterArea;
use App\Models\ReporterProfile;
use App\Models\ReporterType;
use App\Models\Union;
use App\Models\Upazila;
use App\Models\User;
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
            'ref' => $request->ref,
            'divisions' => Division::select('id', 'name')->get(),
            'districts' => District::select('id', 'division_id', 'name')->get(),
            'upazilas' => Upazila::select('id', 'district_id', 'name')->get(),
            'unions' => Union::select('id', 'upazilla_id', 'name')->get(),
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
        $bloodGroups = BloodGroup::all();
        $educations = Education::all();
        $educationals = EducationYear::all();
        $divisions = Division::all();
        $reporterAreas = ReporterArea::all();
        $reporterTypes = ReporterType::all();

        return Inertia::render('FrontEnd/Register/Reporter', [
            'bloodGroups' => $bloodGroups,
            'educations' => $educations,
            'educationals' => $educationals,
            'divisions' => $divisions,
            'reporterAreas' => $reporterAreas,
            'reporterTypes' => $reporterTypes,
        ]);
    }

    public function getDistricts($divisionId)
    {
        return District::where('division_id', $divisionId)->get();
    }

    public function getUpazilas($districtId)
    {
        return Upazila::where('district_id', $districtId)->get();
    }

    public function getUnions($upazilaId)
    {
        return Union::where('upazilla_id', $upazilaId)->get();
    }

    public function storePathock(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|min:6|confirmed',
            'division_id' => 'required|exists:divisions,id',
            'district_id' => 'required|exists:districts,id',
            'upazilla_id' => 'required|exists:upazilas,id',
            'union_id' => 'required|exists:unions,id',
            'village' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email',
        ]);

        // create user
        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'pathock',
            'email' => $data['email'] ?? 'user' . Str::random(5) . '@example.com',
        ]);

        PathockProfile::create([
            'user_id' => $user->id,
            'division_id' => $data['division_id'],
            'district_id' => $data['district_id'],
            'upazilla_id' => $data['upazilla_id'],
            'union_id' => $data['union_id'],
            'village' => $data['village'] ?? null,
            'occupation' => $data['occupation'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REFERRAL SYSTEM
        |--------------------------------------------------------------------------
        */
        if ($request->filled('ref')) {
            $refUser = User::where('ref_id', $request->ref)->first();
            if ($refUser) {
                $user->referred_by = $refUser->id;
                $user->save();
            }
        }

        return redirect()
            ->route('user.login')
            ->with('status', 'Pathock Register Success');
    }

    public function storeMotamot(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed|min:6',
            'email' => 'nullable'
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'motamot',
            'email' => 'user' . Str::random(5) . '@example.com'
        ]);
        return redirect()->route('user.login')->with('status', 'Motamot Register Success');
    }

    public function storeReporter(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|confirmed',
            'photo' => 'required|image',
            'signature' => 'required|image',
            'nid_file' => 'required|mimes:pdf,jpg,png',
            'dob' => 'required|date',
            'reporter_area' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'reporter',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('images/register', 'public')
            : null;

        $signaturePath = $request->hasFile('signature')
            ? $request->file('signature')->store('images/register', 'public')
            : null;

        $nidPath = $request->hasFile('nid_file')
            ? $request->file('nid_file')->store('images/register', 'public')
            : null;

        ReporterProfile::create([
            'user_id' => $user->id,
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
            'is_approved' => 1,
        ]);

        // Auth::login($user);
        return redirect()->route('user.login')->with('status', 'Reporter Register Success');
    }
}
