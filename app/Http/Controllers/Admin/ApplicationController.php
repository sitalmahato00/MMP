<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::with('department')
            ->when($request->search, fn($q) => $q->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->latest()
            ->paginate(20);

        $departments = \App\Models\Department::orderBy('name')->get();

        return view('admin.applications.index', compact('applications', 'departments'));
    }

    public function show(Application $application)
    {
        $application->load('department');
        return view('admin.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application)
    {
        $data = $request->validate([
            'status'      => 'required|in:pending,reviewed,contacted,accepted,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $application->update($data);

        return back()->with('success', 'Application status updated.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return redirect()->route('admin.applications.index')->with('success', 'Application deleted.');
    }
}
