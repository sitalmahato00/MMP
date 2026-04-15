<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $facilities = Facility::with(['department', 'program'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20);

        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        $departments = Department::all();
        $programs = Program::all();
        return view('admin.facilities.create', compact('departments', 'programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => 'nullable|exists:programs,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $data['is_published'] = $request->has('is_published');
        
        // Handle Multiple Images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('facilities/images', 'public');
            }
            $data['images'] = $imagePaths;
        }

        // Handle Multiple Documents
        if ($request->hasFile('documents')) {
            $docPaths = [];
            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file->store('facilities/documents', 'public');
            }
            $data['documents'] = $docPaths;
        }

        Facility::create($data);

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
    }

    public function edit(Facility $facility)
    {
        $departments = Department::all();
        $programs = Program::all();
        return view('admin.facilities.edit', compact('facility', 'departments', 'programs'));
    }

    public function update(Request $request, Facility $facility)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'program_id' => 'nullable|exists:programs,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'location' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $data['is_published'] = $request->has('is_published');
        
        // Append new images
        if ($request->hasFile('images')) {
            $imagePaths = $facility->images ?? [];
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('facilities/images', 'public');
            }
            $data['images'] = $imagePaths;
        }

        // Append new documents
        if ($request->hasFile('documents')) {
            $docPaths = $facility->documents ?? [];
            foreach ($request->file('documents') as $file) {
                $docPaths[] = $file->store('facilities/documents', 'public');
            }
            $data['documents'] = $docPaths;
        }

        $facility->update($data);

        return redirect()->route('admin.facilities.index')->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        // Delete all associated files
        if ($facility->images) {
            foreach ($facility->images as $img) Storage::disk('public')->delete($img);
        }
        if ($facility->documents) {
            foreach ($facility->documents as $doc) Storage::disk('public')->delete($doc);
        }

        $facility->delete();
        return back()->with('success', 'Facility removed.');
    }
}
