<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Category;
use App\Models\Comment;
use App\Models\NewsUserView;
use App\Models\NewsView;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        // Validate allowed filter and sorting values
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:published,draft,breaking,schedule'],
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

            case 'schedule':
                $query->whereNotNull('schedule');
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
        return Inertia::render('FrontEnd/AdminNews/CreateNews', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'subcategories' => SubCategory::orderBy('name')->get(['id', 'category_id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'required|image|max:2048',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'integer|exists:sub_categories,id',
            'tag' => 'nullable|string|max:255',
            'featured' => 'nullable|in:yes,no',
            'breaking' => 'nullable|in:yes,no',
            'status' => 'required|in:0,1',
            'division_name' => 'required|string|max:255',
            'imagenote' => 'required|string|max:255',
            'schedule' => 'nullable|date',
            'expire' => 'nullable|date|after_or_equal:schedule',
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
            'expire' => $request->expire ?: null,
            'status' => (int) $request->status,
            'created_by' => Auth::id(),
        ]);

        $news->categories()->sync($request->categories ?? []);
        $news->subcategories()->sync($request->subcategories ?? []);

        return redirect()->route('admin.news')->with('status', 'News Created Successfully');
    }

    public function edit(News $news)
    {
        $news->load(['categories', 'subcategories']);

        $news->schedule = $news->schedule
            ? Carbon::parse($news->schedule)->format('Y-m-d H:i:s')
            : null;
        $news->expire = $news->expire
            ? Carbon::parse($news->expire)->format('Y-m-d H:i:s')
            : null;

        return Inertia::render('FrontEnd/AdminNews/EditNews', [
            'news' => $news,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'subcategories' => SubCategory::orderBy('name')->get(['id', 'category_id', 'name']),
            'selectedCategories' => $news->categories->pluck('id'),
            'selectedSubcategories' => $news->subcategories->pluck('id'),
        ]);
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'language' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required',
            'image' => 'nullable|image|max:2048',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'integer|exists:sub_categories,id',
            'tag' => 'nullable|string|max:255',
            'featured' => 'nullable|in:yes,no',
            'breaking' => 'nullable|in:yes,no',
            'status' => 'required|in:0,1',
            'division_name' => 'required|string|max:255',
            'imagenote' => 'required|string|max:255',
            'schedule' => 'nullable|date',
            'expire' => 'nullable|date|after_or_equal:schedule',
        ]);

        $imagePath = $news->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/news', 'public');
        }

        $tags = explode(',', $request->tag ?? '');

        $firstCategory = $request->categories[0] ?? null;
        $firstSubcategory = $request->subcategories[0] ?? null;

        $news->update([
            'language' => $request->language,
            'title' => $request->title,
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
            'expire' => $request->expire ?: null,
            'status' => (int) $request->status,
        ]);

        $news->categories()->sync($request->categories ?? []);
        $news->subcategories()->sync($request->subcategories ?? []);

        return redirect()->route('admin.news')->with('status', 'News Updated Successfully');
    }

    public function delete($id)
    {
        $news = News::findOrFail($id);
        Comment::where('news_id', $news->id)->delete();
        NewsUserView::where('news_id', $news->id)->delete();
        NewsView::where('news_id', $news->id)->delete();

        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()->back()->with('status', 'News deleted successfully');
    }

    // GET /admin/news/{news}/show — returns full details as JSON
    public function show(News $news)
    {
        $news->load(['category', 'subcategory', 'categories', 'subcategories']);

        return response()->json([
            'news' => [
                'id' => $news->id,
                'title' => $news->title,
                'slug' => $news->slug,
                'metatag' => $news->metatag,
                'tag' => $news->tag,
                'description' => $news->description,
                'image' => $news->image,
                'imagenote' => $news->imagenote,
                'category' => $news->category?->name,
                'subcategory' => $news->subcategory?->name,
                'categories' => $news->categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name]),
                'subcategories' => $news->subcategories->map(fn($sub) => ['id' => $sub->id, 'name' => $sub->name]),
                'featured' => (bool) $news->featured,
                'breaking' => (bool) $news->breaking,
                'schedule' => $news->schedule,
                'expire' => $news->expire,
                'status' => (int) $news->status,
                'total_view' => (int) $news->total_view,
                'current_view' => (int) $news->current_view,
                'division_name' => $news->division_name,
                'created_at' => $news->created_at?->toIso8601String(),
                'updated_at' => $news->updated_at?->toIso8601String(),
                'published_by' => $news->published_by,
                'created_by' => $news->created_by,
            ],
            'publishers' => User::whereIn('id', function ($q) {
                // You can restrict this — for now, list all users
                $q->select('id')->from('users');
            })->select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    // PATCH /admin/news/{news}/status — update status + published_by
    public function updateStatus(Request $request, News $news)
    {
        $data = $request->validate([
            'status' => ['required', 'in:0,1'],
        ]);

        $news->status = (int) $data['status'];

        if ($news->status === 1) {
            // Marking as Published — record who published it
            $news->published_by = auth()->id();
        } else {
            // Reverting to Draft — clear publisher
            $news->published_by = null;
        }

        $news->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'news' => [
                'id' => $news->id,
                'status' => (int) $news->status,
                'published_by' => $news->published_by,
            ],
        ]);
    }
}
