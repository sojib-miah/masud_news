<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $videos = Video::query()
            ->when($search, fn($q) => $q->where('link', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Video/Video', [
            'videos' => $videos,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['link' => 'required|string|max:255']);
        Video::create($request->only('link'));
        return back()->with('status', 'Video created successfully!');
    }

    public function update(Request $request, Video $video)
    {
        $request->validate(['link' => 'required|string|max:255']);
        $video->update($request->only('link'));
        return back()->with('status', 'Video updated successfully!');
    }


    public function destroy(Video $video)
    {
        $video->delete();
        return back()->with('status', 'Video deleted successfully!');
    }
}
