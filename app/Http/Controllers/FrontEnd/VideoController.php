<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::query()
            ->orderByDesc('id')
            ->paginate(12);

        return Inertia::render('FrontEnd/Videos/Index', [
            'videos' => $videos,
        ]);
    }
}
