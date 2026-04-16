<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $parents = ParentModel::with('user', 'students.user')
            ->when($request->search, function($q) use ($request) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(20);

        return view('admin.parents.index', compact('parents'));
    }

    public function create() { abort(404, 'To be implemented'); }
    public function store(Request $request) { abort(404, 'To be implemented'); }
    public function show(ParentModel $parent) { abort(404, 'To be implemented'); }
    public function edit(ParentModel $parent) { abort(404, 'To be implemented'); }
    public function update(Request $request, ParentModel $parent) { abort(404, 'To be implemented'); }
    public function destroy(ParentModel $parent) { abort(404, 'To be implemented'); }
}
