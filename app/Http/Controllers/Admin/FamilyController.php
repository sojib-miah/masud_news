<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $families = Family::orderByDesc('id')
            ->get()
            ->map(fn(Family $f) => [
                'id'          => $f->id,
                'name'        => $f->name,
                'email'       => $f->email,
                'designation' => $f->designation,
                'phone'       => $f->phone,
                'location'    => $f->location,
                'image'       => $f->image,
                'image_url'   => $f->image_url,
                'short_desc'  => $f->short_desc,
                'long_desc'   => $f->long_desc,
                'created_at'  => optional($f->created_at)->format('d M Y'),
            ]);

        $editing = null;
        if ($request->filled('edit')) {
            $editingModel = Family::find($request->integer('edit'));
            if ($editingModel) {
                $editing = [
                    'id'          => $editingModel->id,
                    'name'        => $editingModel->name,
                    'email'       => $editingModel->email,
                    'designation' => $editingModel->designation,
                    'phone'       => $editingModel->phone,
                    'location'    => $editingModel->location,
                    'image'       => $editingModel->image,
                    'image_url'   => $editingModel->image_url,
                    'short_desc'  => $editingModel->short_desc,
                    'long_desc'   => $editingModel->long_desc,
                ];
            }
        }

        return Inertia::render('Admin/Family/Index', [
            'families' => $families,
            'editing'  => $editing,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images/families', 'public');
        } else {
            unset($data['image']); // keep DB default (null) clean
        }

        Family::create($data);

        return redirect()
            ->route('admin.families.index')
            ->with('status', 'Family created successfully');
    }

    public function update(Request $request, Family $family)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            // Delete old file
            if ($family->image && Storage::disk('public')->exists($family->image)) {
                Storage::disk('public')->delete($family->image);
            }
            $data['image'] = $request->file('image')->store('images/families', 'public');
        } else {
            // KEY FIX: remove 'image' from data so existing value is preserved
            unset($data['image']);
        }

        $family->update($data);

        return redirect()
            ->route('admin.families.index')
            ->with('status', 'Family updated successfully');
    }

    public function destroy(Family $family)
    {
        if ($family->image && Storage::disk('public')->exists($family->image)) {
            Storage::disk('public')->delete($family->image);
        }

        $family->delete();

        return redirect()->route('admin.families.index')->with('status', 'Family deleted successfully');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['nullable', 'email', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'location'    => ['nullable', 'string', 'max:255'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'short_desc'  => ['nullable', 'string', 'max:500'],
            'long_desc'   => ['nullable', 'string'],
        ]);
    }
}
