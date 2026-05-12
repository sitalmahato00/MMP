<?php

namespace App\Modules\CMS\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Executive;
use App\Services\PublicDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExecutiveController extends Controller
{
    public function index(Request $request)
    {
        $query = Executive::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('designation', 'like', '%' . $request->search . '%');
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_current', $request->status === '1');
        }

        $executives = $query->orderBy('order')->paginate(15)->withQueryString();

        return view('admin.executives.index', compact('executives'));
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
        PublicDataService::invalidate('*');

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
        PublicDataService::invalidate('*');

        return redirect()->route('admin.executives.index')->with('success', 'Executive profile updated.');
    }

    public function destroy(Executive $executive)
    {
        if ($executive->avatar) {
            Storage::disk('public')->delete($executive->avatar);
        }
        $executive->delete();
        PublicDataService::invalidate('*');
        
        return back()->with('success', 'Executive record removed.');
    }
}
