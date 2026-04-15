<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = Staff::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('order')
            ->latest()
            ->paginate(20);

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('admin.staff.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department'  => 'nullable|string|max:255',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:20',
            'photo'       => 'nullable|image|max:2048',
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }
        
        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        Staff::create($data);
        return redirect()->route('admin.staff.index')->with('success', 'Staff member added.');
    }

    public function show(Staff $staff)
    {
        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        return view('admin.staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department'  => 'nullable|string|max:255',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:20',
            'photo'       => 'nullable|image|max:2048',
            'order'       => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $data['is_active'] = $request->has('is_active');
        $data['order'] = $request->order ?? 0;

        $staff->update($data);
        return redirect()->route('admin.staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(Staff $staff)
    {
        if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
            Storage::disk('public')->delete($staff->photo);
        }
        $staff->delete();
        return redirect()->route('admin.staff.index')->with('success', 'Staff removed.');
    }
}
