<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Header;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HeaderController extends Controller
{
    public function index(Request $request)
    {
        $headers = Header::paginate(10);
        return Inertia::render('FrontEnd/Header/Header', ['headers' => $headers,]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'youtube' => 'nullable|string',
            'linkdin' => 'nullable|string',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'ad' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp',
        ]);

        if ($request->hasFile('ad')) {
            $data['ad'] = $request->file('ad')->store('images/headers', 'public');
        }

        Header::create($data);

        return redirect()->back()->with('status', 'Header created successfully!');
    }

    public function update(Request $request, Header $header)
    {
        $data = $request->validate([
            'youtube' => 'nullable|string',
            'linkdin' => 'nullable|string',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'ad' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        if ($request->hasFile('ad')) {
            if ($header->ad) {
                Storage::disk('public')->delete($header->ad);
            }
            $data['ad'] = $request->file('ad')->store('images/headers', 'public');
        } else {
            unset($data['ad']);
        }

        $header->update($data);

        return redirect()->back()->with('status', 'Header updated successfully!');
    }



    public function destroy(Header $header)
    {
        if ($header->ad) {
            Storage::disk('public')->delete($header->ad);
        }
        $header->delete();

        return redirect()->back()->with('status', 'Header deleted successfully!');
    }
}
