<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\News;
use App\Models\SiteSetting;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'category' => 'required',
            'subcategory' => 'nullable',
        ]);

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('images/news', 'public')
            : null;

        $tags = explode(',', $request->tag ?? '');

        News::create([
            'language' => $request->language,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'metatag' => $request->title,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'image' => $imagePath,
            'imagenote' => $request->imageNote,
            'category_id' => $request->category,
            'subcategory_id' => $request->subcategory,
            'featured' => $request->featured == 'yes' ? 1 : 0,
            'breaking' => $request->breaking == 'yes' ? 1 : 0,
            'status' => 0,
            'created_by' => Auth::id(),
        ]);

        $user = Auth::user();
        $user->increment('published_news', 1);
        $sitesetting = SiteSetting::first();
        $perViewAmount = $sitesetting->news_view ?? 0;
        $user->increment('reporter_income', $perViewAmount);

        return redirect()->route('news.all')->with('status', 'News Created Successfully');
    }

    public function videoStore(Request $request)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'required|image',
            'category' => 'required',
            'video' => 'required',
            'subcategory' => 'nullable',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/news', 'public');
        }

        News::create([
            'language' => $request->language == 'Bangla' ? 1 : 2,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'metatag' => $request->title,
            'tag' => json_encode(explode(',', $request->tag ?? '')),
            'description' => $request->description,
            'image' => $imagePath,
            'imagenote' => $request->imageNote,
            'category_id' => $request->category,
            'subcategory_id' => $request->subcategory,
            'featured' => $request->featured == 'yes' ? 1 : 0,
            'breaking' => $request->breaking == 'yes' ? 1 : 0,
            'video' => $request->video,
            'status' => 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('news.all')->with('status', 'News Created Successfully');
    }
    public function audioStore(Request $request)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'required|image',
            'category' => 'required',
            'subcategory' => 'nullable',
            'audio' => 'nullable|mimes:mp3,wav,ogg',
        ]);

        $imagePath = null;
        $audio = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/news', 'public');
        }
        if ($request->hasFile('audio')) {
            $audio = $request->file('audio')->store('images/news', 'public');
        }

        News::create([
            'language' => $request->language == 'Bangla' ? 1 : 2,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'metatag' => $request->title,
            'tag' => json_encode(explode(',', $request->tag ?? '')),
            'description' => $request->description,
            'image' => $imagePath,
            'audio' => $audio,
            'imagenote' => $request->imageNote,
            'category_id' => $request->category,
            'subcategory_id' => $request->subcategory,
            'featured' => $request->featured == 'yes' ? 1 : 0,
            'breaking' => $request->breaking == 'yes' ? 1 : 0,
            'status' => 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('news.all')->with('status', 'News Created Successfully');
    }

    public function edit(News $news)
    {
        return Inertia::render('FrontEnd/Dashboard/Reporter/EditNews', [
            'news' => $news,
            'categories' => Category::all(),
            'subcategories' => SubCategory::all(),
        ]);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'language' => 'required|in:1,2',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'nullable|image',
            'category' => 'required',
            'subcategory' => 'nullable',
            'tag' => 'nullable|string',
            'featured' => 'nullable|in:yes,no',
            'breaking' => 'nullable|in:yes,no',
        ]);

        $imagePath = $news->image;
        if ($request->hasFile('image')) {
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }

            $imagePath = $request->file('image')->store('images/news', 'public');
        }

        $tags = explode(',', $request->tag ?? '');

        $news->update([
            'language' => $request->language,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . $news->id,
            'metatag' => $request->title,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'image' => $imagePath,
            'imagenote' => $request->imageNote ?? null,
            'category_id' => $request->category,
            'subcategory_id' => $request->subcategory ?? null,
            'featured' => $request->featured === 'yes' ? 1 : 0,
            'breaking' => $request->breaking === 'yes' ? 1 : 0,
        ]);

        return redirect()->route('news.all')->with('status', 'News Updated Successfully');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
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
        $query = News::with(['category', 'subcategory'])
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
        $query = News::query()
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
        $query = News::with(['category', 'subcategory'])
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
        $query = News::with(['category', 'subcategory'])
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
        $query = News::with(['category', 'subcategory'])
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
        $query = News::with(['category', 'subcategory'])->where('created_by', Auth::id())
            ->latest();

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
