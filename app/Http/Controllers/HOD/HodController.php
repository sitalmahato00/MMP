<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

/**
 * Base controller for all HOD controllers.
 * 
 * Provides common functionality for department-scoped operations.
 */
abstract class HodController extends Controller
{
    /**
     * Get the current user's department.
     */
    protected function currentDepartment(Request $request): Department
    {
        $user = $request->user();
        
        // Get department from HOD assignment
        $department = Department::where('hod_id', $user->id)->first();
        
        if (!$department) {
            // Fallback: try to get from teacher record
            if ($user->teacher && $user->teacher->department_id) {
                $department = Department::find($user->teacher->department_id);
            }
        }
        
        if (!$department) {
            abort(403, 'You are not assigned to any department.');
        }
        
        return $department;
    }

    /**
     * Verify that a model belongs to the current user's department.
     */
    protected function authorizeDepartment(Request $request, $model): void
    {
        $department = $this->currentDepartment($request);
        
        if (method_exists($model, 'department') && $model->department_id !== $department->id) {
            abort(403, 'Unauthorized access to resource.');
        }
        
        // For students
        if (method_exists($model, 'department') && isset($model->department_id) && $model->department_id !== $department->id) {
            abort(403, 'Unauthorized access to student.');
        }
        
        // For teachers
        if (method_exists($model, 'department') && isset($model->department_id) && $model->department_id !== $department->id) {
            abort(403, 'Unauthorized access to teacher.');
        }
    }
}