<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use App\Models\NewsUserView;
use App\Models\NewsView;
use App\Models\SubCategory;
use App\Models\User;
use App\Notifications\NewsStored;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'required|image',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'integer|exists:sub_categories,id',
            'tag' => 'nullable|string|max:255',
            'featured' => 'nullable|in:yes,no',
            'breaking' => 'nullable|in:yes,no',
            'division_name' => 'required|string',
            'imagenote' => 'required|string|max:255',
            'schedule' => 'nullable|date',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('images/news', 'public')
            : null;

        $tags = explode(',', $request->tag ?? '');

        $firstCategory = $request->categories[0] ?? null;
        $firstSubcategory = $request->subcategories[0] ?? null;

        $news = News::create([
            'language' => $request->language,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'metatag' => $request->title,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'image' => $imagePath,
            'division_name' => $request->division_name,
            'imagenote' => $request->imagenote,

            'category_id' => $firstCategory,
            'subcategory_id' => $firstSubcategory,

            'featured' => $request->featured === 'yes' ? 1 : 0,
            'breaking' => $request->breaking === 'yes' ? 1 : 0,
            'schedule' => $request->schedule ?: null,
            'status' => 0,
            'created_by' => Auth::id(),
        ]);

        $news->categories()->sync($request->categories ?? []);
        $news->subcategories()->sync($request->subcategories ?? []);

        Auth::user()->increment('published_news', 1);

        // Admins-দের notify (news জমা পড়েছে)
        $admins = User::role('admin')->get();

        Notification::send($admins, new NewsStored(
            news: $news,
            message: "{$news->user->name} একটি নতুন নিউজ জমা দিয়েছেন: {$news->title}",
        ));

        return redirect()->route('news.all')->with('status', 'News Created Successfully');
    }

    public function edit(News $news)
    {
        $news->load(['categories', 'subcategories']);

        return Inertia::render('FrontEnd/Dashboard/Reporter/EditNews', [
            'news' => $news,
            'categories' => Category::orderBy('name')->get(),
            'subcategories' => SubCategory::orderBy('name')->get(),
            'selectedCategories' => $news->categories->pluck('id'),
            'selectedSubcategories' => $news->subcategories->pluck('id'),
        ]);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'language' => 'required|in:1,2',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'nullable|image',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'integer|exists:sub_categories,id',
            'tag' => 'nullable|string|max:255',
            'featured' => 'nullable|in:yes,no',
            'breaking' => 'nullable|in:yes,no',
            'division_name' => 'required|string',
            'imagenote' => 'required|string|max:255',
            'schedule' => 'nullable|date',
        ]);

        // Image
        $imagePath = $news->image;
        if ($request->hasFile('image')) {
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }
            $imagePath = $request->file('image')->store('images/news', 'public');
        }

        $tags = explode(',', $request->tag ?? '');

        $firstCategory = $request->categories[0] ?? null;
        $firstSubcategory = $request->subcategories[0] ?? null;

        $news->update([
            'language' => $request->language,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . $news->id,
            'metatag' => $request->title,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'image' => $imagePath,

            'category_id' => $firstCategory,
            'subcategory_id' => $firstSubcategory,

            'featured' => $request->featured === 'yes' ? 1 : 0,
            'breaking' => $request->breaking === 'yes' ? 1 : 0,
            'division_name' => $request->division_name,
            'imagenote' => $request->imagenote,
            'schedule' => $request->schedule ?: null,   // ← NEW
        ]);

        $news->categories()->sync($request->categories ?? []);
        $news->subcategories()->sync($request->subcategories ?? []);

        return redirect()->route('news.all')->with('status', 'News Updated Successfully');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        Comment::where('news_id', $news->id)->delete();
        NewsUserView::where('news_id', $news->id)->delete();
        NewsView::where('news_id', $news->id)->delete();
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
        if ($news->audio) {
            Storage::disk('public')->delete($news->audio);
        }
        $news->delete();
        return redirect()->back()->with('status', 'News deleted successfully');
    }

    // all news
    public function allNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->where('created_by', Auth::id())
            ->latest();

        if ($request->has('search') && $request->search !== '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/AllNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }

    public function featuredNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->where('featured', 1)->where('created_by', Auth::id())
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/FeaturedNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }

    public function breakingNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->where('breaking', 1)->where('created_by', Auth::id())
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('title', 'like', '%' . $search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/BreakingNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }

    public function pendingNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->where('status', 0)->where('created_by', Auth::id())
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('title', 'like', '%' . $search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/PendingNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }

    public function draftNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->where('status', 0)->where('created_by', Auth::id())
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('title', 'like', '%' . $search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/DraftNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }

    public function scheduleNews(Request $request)
    {
        $query = News::with(['category', 'subcategory', 'categories', 'subcategories'])
            ->whereNotNull('schedule')
            ->where('created_by', Auth::id())
            ->orderBy('schedule', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }

        $news = $query->paginate(10);

        return Inertia::render('FrontEnd/Dashboard/Reporter/ScheduleNews', [
            'news' => $news,
            'filters' => $request->only('search'),
        ]);
    }
}
