<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Footer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FooterController extends Controller
{
    public function index()
    {
        $footers = Footer::paginate(10);
        return Inertia::render('FrontEnd/Footer/Footer', ['footers' => $footers,]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'som' => 'nullable|string|max:255',
            'pro' => 'nullable|string|max:255',
            'bab' => 'nullable|string|max:255',
            'nir' => 'nullable|string|max:255',
            'ban' => 'nullable|string|max:255',
            'barta' => 'nullable|string|max:255',
            'phone1' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'web1' => 'nullable|string|max:255',
            'web2' => 'nullable|string|max:255',
            'copy' => 'nullable|string|max:255',
        ]);
        Footer::create($data);
        return redirect()->back()->with('status', 'Footer created successfully!');
    }

    public function update(Request $request, Footer $footer)
    {
        $data = $request->validate([
            'som' => 'nullable|string|max:255',
            'pro' => 'nullable|string|max:255',
            'bab' => 'nullable|string|max:255',
            'nir' => 'nullable|string|max:255',
            'ban' => 'nullable|string|max:255',
            'barta' => 'nullable|string|max:255',
            'phone1' => 'nullable|string|max:255',
            'phone2' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'web1' => 'nullable|string|max:255',
            'web2' => 'nullable|string|max:255',
            'copy' => 'nullable|string|max:255',
        ]);
        $footer->update($data);
        return redirect()->back()->with('status', 'Footer updated successfully!');
    }

    public function destroy(Footer $footer)
    {
        $footer->delete();
        return redirect()->back()->with('status', 'Footer deleted successfully!');
    }
}
