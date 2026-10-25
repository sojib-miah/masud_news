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
            'som' => 'nullable|string', 
            'pro' => 'nullable|string', 
            'bab' => 'nullable|string', 
            'nir' => 'nullable|string', 
            'ban' => 'nullable|string', 
            'phone1' => 'nullable|string', 
            'phone2' => 'nullable|string', 
            'email' => 'nullable|email', 
            'web1' => 'nullable|string', 
            'web2' => 'nullable|string', 
            'copy' => 'nullable|string',
            ]);
        Footer::create($data);
        return redirect()->back()->with('status', 'Footer created successfully!');
    }
    public function update(Request $request, Footer $footer)
    {
        $data = $request->validate([
            'som' => 'nullable|string', 
            'pro' => 'nullable|string', 
            'bab' => 'nullable|string', 
            'nir' => 'nullable|string', 
            'ban' => 'nullable|string', 
            'phone1' => 'nullable|string', 
            'phone2' => 'nullable|string', 
            'email' => 'nullable|email', 
            'web1' => 'nullable|string', 
            'web2' => 'nullable|string', 
            'copy' => 'nullable|string',
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
