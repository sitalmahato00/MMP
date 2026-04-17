<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni as Alumnus;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.students.index')
            ->with('success', 'Alumni are promoted automatically from student records when an academic session ends.');
    }

    public function create() { abort(404, 'To be implemented'); }
    public function store(Request $request) { abort(404, 'To be implemented'); }
    public function show(Alumnus $alumnus) { abort(404, 'To be implemented'); }
    public function edit(Alumnus $alumnus) { abort(404, 'To be implemented'); }
    public function update(Request $request, Alumnus $alumnus) { abort(404, 'To be implemented'); }
    public function destroy(Alumnus $alumnus) { abort(404, 'To be implemented'); }
}
