<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\NewsletterSubscriber;
use App\Models\SubCategory;
use App\Models\Termes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TermesController extends Controller
{
    public function termesCondition()
    {
        $term = Termes::first();
        return Inertia::render('FrontEnd/Termes/AdminTermes', ['term' => $term,]);
    }

    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'termes' => 'nullable|string',
            'privacy' => 'nullable|string',
        ]);
        $term = Termes::first();
        if ($term) {
            $term->update($data);
            $message = 'Terms updated successfully!';
        } else {
            Termes::create($data);
            $message = 'Terms created successfully!';
        }
        return redirect()->back()->with('status', $message);
    }

    // terms and controller 
    public function newsTermes()
    {
        $term = Termes::first();
        $categories = Category::withCount('news')->get();
        $subCategories = SubCategory::all();
        return Inertia::render('FrontEnd/Termes/Termes', ['term' => $term, 'categories' => $categories, 'subCategories' => $subCategories]);
    }
    public function newsPrivacy()
    {
        $privacy = Termes::first();
        $categories = Category::withCount('news')->get();
        $subCategories = SubCategory::all();
        return Inertia::render('FrontEnd/Termes/Privacy', [
            'privacy' => $privacy,
            'categories' => $categories,
            'subCategories' => $subCategories
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
        ]);

        return back()->with('status', 'আপনি সফলভাবে সাবস্ক্রাইব করেছেন!');
    }
}
