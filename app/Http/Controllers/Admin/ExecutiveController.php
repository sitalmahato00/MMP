<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Executive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExecutiveController extends Controller
{
    public function index()
    {
        $presidents = Executive::presidents()->get();
        $principals = Executive::principals()->get();
        return view('admin.executives.index', compact('presidents', 'principals'));
    }

    public function create()
    {
        return view('admin.executives.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:principal,president',
            'designation' => 'nullable|string|max:255',
            'start_date_bs' => 'required|string|max:10',
            'end_date_bs' => 'nullable|string|max:10',
            'message' => 'nullable|string',
            'order' => 'required|integer',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data['is_current'] = $request->has('is_current');

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('executives', 'public');
        }

        Executive::create($data);

        return redirect()->route('admin.executives.index')->with('success', 'Executive profile saved.');
    }

    public function edit(Executive $executive)
    {
        return view('admin.executives.edit', compact('executive'));
    }

    public function update(Request $request, Executive $executive)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:principal,president',
            'designation' => 'nullable|string|max:255',
            'start_date_bs' => 'required|string|max:10',
            'end_date_bs' => 'nullable|string|max:10',
            'message' => 'nullable|string',
            'order' => 'required|integer',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data['is_current'] = $request->has('is_current');

        if ($request->hasFile('avatar')) {
            if ($executive->avatar) {
                Storage::disk('public')->delete($executive->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('executives', 'public');
        }

        $executive->update($data);

        return redirect()->route('admin.executives.index')->with('success', 'Executive profile updated.');
    }

    public function destroy(Executive $executive)
    {
        if ($executive->avatar) {
            Storage::disk('public')->delete($executive->avatar);
        }
        $executive->delete();
        
        return back()->with('success', 'Executive record removed.');
    }
}
