<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        // Validate allowed filter and sorting values
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:published,draft,breaking'],
            'sort' => ['nullable', 'in:newest,oldest,views,title'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? '';
        $sort = $validated['sort'] ?? 'newest';

        $query = News::query();

        // Search by title
        if ($search !== '') {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Filter by status
        switch ($status) {
            case 'published':
                $query->where('status', 1);
                break;

            case 'draft':
                $query->where('status', 0);
                break;

            case 'breaking':
                $query->where('breaking', 1);
                break;
        }

        // Server-side sorting
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                break;

            case 'views':
                $query->orderBy('total_view', 'desc')
                    ->orderBy('id', 'desc');
                break;

            case 'title':
                $query->orderBy('title', 'asc')
                    ->orderBy('id', 'desc');
                break;

            case 'newest':
            default:
                $query->latest('created_at')
                    ->orderBy('id', 'desc');
                break;
        }

        $news = $query->paginate(20)->withQueryString();

        return Inertia::render('FrontEnd/AdminNews/News', [
            'news' => $news,

            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort,
            ],

            // Overall counts, not filtered counts
            'publishedCount' => News::where('status', 1)->count(),
            'draftCount' => News::where('status', 0)->count(),
            'breakingCount' => News::where('breaking', 1)->count(),
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        $subcategories = SubCategory::all();

        return Inertia::render('FrontEnd/AdminNews/CreateNews', [
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/news', 'public');
        }

        $tags = explode(',', $request->tag ?? '');

        News::create([
            'language' => $request->language,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'metatag' => $request->metatag,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'image' => $imagePath,
            'imagenote' => $request->imagenote,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'featured' => $request->featured,
            'breaking' => $request->breaking,
            'schedule' => $request->schedule,
            'expire' => $request->expire,
            'status' => $request->status,
            'audio' => $request->audio,
            'video' => $request->video,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.news')
            ->with('status', 'News Created Successfully');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);

        return Inertia::render('FrontEnd/AdminNews/EditNews', [
            'news' => $news,
            'categories' => Category::all(),
            'subcategories' => SubCategory::all()
        ]);
    }

    public function update(Request $request, $id)
    {

        $news = News::findOrFail($id);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/news', 'public');
            $news->image = $imagePath;
        }

        $tags = explode(',', $request->tag ?? '');

        $news->update([
            'language' => $request->language,
            'title' => $request->title,
            'metatag' => $request->metatag,
            'tag' => json_encode($tags),
            'description' => $request->description,
            'imagenote' => $request->imagenote,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'featured' => $request->featured,
            'breaking' => $request->breaking,
            'schedule' => $request->schedule,
            'expire' => $request->expire,
            'status' => $request->status,
            'audio' => $request->audio,
            'video' => $request->video,
        ]);

        return redirect()->route('admin.news')
            ->with('status', 'News Updated Successfully');
    }

    public function delete($id)
    {
        News::findOrFail($id)->delete();

        return redirect()->back();
    }
}
