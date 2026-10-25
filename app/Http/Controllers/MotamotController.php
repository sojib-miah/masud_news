<?php

namespace App\Http\Controllers;

use App\Models\Motamot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MotamotController extends Controller
{
    public function index()
    {
        $motamots = Motamot::latest()->paginate(10);

        return Inertia::render(
            'FrontEnd/Dashboard/Motamot/Motamot',
            [
                'motamots' => $motamots
            ]
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',

            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $images = [];

        if ($request->file('images')) {

            foreach ($request->file('images') as $image) {

                $path = $image->store(
                    'images/motamot',
                    'public'
                );

                $images[] = $path;
            }
        }

        Motamot::create([
            'subject' => $request->subject,
            'description' => $request->description,
            'images' => $images,
        ]);

        return back()->with(
            'status',
            'Motamot Added Successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $motamot = Motamot::findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',

            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $images = $motamot->images ?? [];

        if ($request->file('images')) {

            // delete old
            if ($motamot->images) {

                foreach ($motamot->images as $oldImage) {

                    Storage::disk('public')
                        ->delete($oldImage);
                }
            }

            $images = [];

            foreach ($request->file('images') as $image) {

                $path = $image->store(
                    'images/motamot',
                    'public'
                );

                $images[] = $path;
            }
        }

        $motamot->update([
            'subject' => $request->subject,
            'description' => $request->description,
            'images' => $images,
        ]);

        return back()->with(
            'status',
            'Motamot Updated Successfully'
        );
    }

    public function destroy($id)
    {
        $motamot = Motamot::findOrFail($id);

        if ($motamot->images) {

            foreach ($motamot->images as $image) {

                Storage::disk('public')->delete($image);
            }
        }

        $motamot->delete();

        return back()->with(
            'status',
            'Motamot Deleted Successfully'
        );
    }

    // admin route 
    public function motamotList()
    {
        $motamots = User::where('role', 'motamot')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Motamot/List', [
            'motamots' => $motamots
        ]);
    }

    public function motamotUser($id)
    {
        $user = User::with('motamots')->findOrFail($id);

        return Inertia::render('Admin/Motamot/UserMotamots', [
            'user' => $user,
            'motamots' => $user->motamots
        ]);
    }
}
