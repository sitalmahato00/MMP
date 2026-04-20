<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

/**
 * Base controller for HOD portal.
 *
 * Provides a single, reliable way to resolve the currently authenticated
 * HOD's department. The {@see \App\Http\Middleware\DepartmentIsolation}
 * middleware has already attached `department_id` to the request, so in
 * practice we simply read it from there.
 */
abstract class HodController extends Controller
{
    /**
     * Get the Department owned by the currently authenticated HOD.
     *
     * Aborts with 403 if the HOD has no department assigned. This should
     * never normally happen for protected routes because
     * DepartmentIsolation will have already blocked the request — this
     * is a defensive safeguard.
     */
    protected function currentDepartment(Request $request): Department
    {
        $departmentId = $request->input('department_id');

        if (!$departmentId) {
            abort(403, 'You are not assigned to any department.');
        }

        /** @var Department|null $department */
        $department = Department::find($departmentId);

        if (!$department) {
            abort(403, 'Your assigned department could not be found.');
        }

        return $department;
    }

    /**
     * Abort if the given model does not belong to the current HOD's department.
     *
     * @param object $model Any model with a `department_id` attribute.
     */
    protected function authorizeDepartment(Request $request, object $model): void
    {
        $departmentId = (int) $request->input('department_id');
        $modelDeptId  = (int) ($model->department_id ?? 0);

        if ($modelDeptId !== $departmentId) {
            abort(404);
        }
    }
}
