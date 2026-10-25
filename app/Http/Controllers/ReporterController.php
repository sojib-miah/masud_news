<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ReporterType;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReporterController extends Controller
{
    public function idCard()
    {
        return Inertia::render('FrontEnd/Dashboard/Reporter/IdCard');
    }

    public function news()
    {
        if (auth()->user()->status !== 'approved') {

            return redirect()->back()
                ->with('error', 'Your account is not approved yet. Please wait for approval from the admin.');
        }

        $subcategories = SubCategory::all();
        $categories = Category::all();
        return Inertia::render('FrontEnd/Dashboard/Reporter/News', ['categories' => $categories, 'subcategories' => $subcategories]);
    }

    public function audioNews()
    {
        $subcategories = SubCategory::all();
        $categories = Category::all();
        return Inertia::render('FrontEnd/Dashboard/Reporter/AudioNews', ['categories' => $categories, 'subcategories' => $subcategories]);
    }

    public function videoNews()
    {
        $subcategories = SubCategory::all();
        $categories = Category::all();
        return Inertia::render('FrontEnd/Dashboard/Reporter/VideoNews', ['categories' => $categories, 'subcategories' => $subcategories]);
    }

    // all reporter 
    public function allReporter(Request $request)
    {
        $query = User::where('role', 'reporter')
            ->with('reporterProfile')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $reporters = $query->paginate(10);

        foreach ($reporters as $reporter) {
            if ($reporter->reporterProfile && $reporter->reporterProfile->reporter_types) {
                $ids = explode(',', $reporter->reporterProfile->reporter_types);
                $names = ReporterType::whereIn('id', $ids)->pluck('name')->toArray();
                $reporter->reporterProfile->reporter_types_names = implode(', ', $names);
            }
        }

        return Inertia::render('FrontEnd/Dashboard/Reporter/Reporter', [
            'reporters' => $reporters,
            'filters' => $request->only('search'),
        ]);
    }

    // category and sub ategory 
    public function index()
    {
        $categories = Category::with('subcategories')->get();
        $subcategories = SubCategory::with('category')->get();
        return Inertia::render('FrontEnd/Dashboard/Reporter/Category', ['categories' => $categories, 'subcategories' => $subcategories,]);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name',]);
        Category::create(['name' => $request->name, 'slug' => Str::slug($request->name),]);
        return back()->with('status', 'Category created successfully');
    }
    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name,' . $category->id,]);
        $category->update(['name' => $request->name, 'slug' => Str::slug($request->name),]);
        return back()->with('status', 'Category updated successfully');
    }
    public function destroy(Category $category)
    {
        $category->subcategories()->delete();
        $category->delete();

        return back()->with('status', 'Category deleted successfully');
    }

    public function substore(Request $request)
    {
        $request->validate(['category_id' => 'required|exists:categories,id', 'name' => 'required|string|max:255|unique:sub_categories,name',]);
        SubCategory::create(['category_id' => $request->category_id, 'name' => $request->name, 'slug' => Str::slug($request->name),]);
        return back()->with('status', 'Subcategory created successfully');
    }
    public function subupdate(Request $request, SubCategory $subcategory)
    {
        $request->validate(['category_id' => 'required|exists:categories,id', 'name' => 'required|string|max:255|unique:sub_categories,name,' . $subcategory->id,]);
        $subcategory->update(['category_id' => $request->category_id, 'name' => $request->name, 'slug' => Str::slug($request->name),]);
        return back()->with('status', 'Subcategory updated successfully');
    }
    public function subdestroy(SubCategory $subcategory)
    {
        $subcategory->delete();
        return back()->with('status', 'Subcategory deleted successfully');
    }

    // update user status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved',
        ]);
        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();
        return back()->with('status', 'Reporter status updated successfully');
    }
}
