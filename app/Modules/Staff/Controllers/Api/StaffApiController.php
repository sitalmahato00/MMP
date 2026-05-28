<?php

namespace App\Modules\Staff\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Staff\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffApiController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $staff = Staff::query()
            ->when($request->search, function ($q) use ($request) {
                $s = trim((string) $request->search);
                $q->where(function ($b) use ($s) {
                    $b->where('name', 'like', "%{$s}%")
                      ->orWhere('staff_code', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('phone', 'like', "%{$s}%")
                      ->orWhere('designation', 'like', "%{$s}%")
                      ->orWhere('department', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('department'), fn($q) => $q->where('department', $request->department))
            ->when($request->filled('designation'), fn($q) => $q->where('designation', $request->designation))
            ->when($request->filled('employment_status'), fn($q) => $q->where('employment_status', $request->employment_status))
            ->when($request->filled('featured'), fn($q) => $q->where('featured', $request->boolean('featured')))
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($staff);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'staff_code'          => ['required', 'string', 'max:50', 'unique:staff,staff_code'],
            'name'                => 'required|string|max:255',
            'designation'         => 'required|string|max:255',
            'department'          => 'nullable|string|max:255',
            'email'               => ['nullable', 'email', 'max:255', 'unique:staff,email'],
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'dob'                 => 'nullable|date',
            'gender'              => 'nullable|in:male,female,other,prefer_not_to_say',
            'employment_type'     => 'nullable|in:full_time,part_time,contract,temporary',
            'employment_status'   => 'required|in:active,leave,resigned',
            'join_date'           => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:join_date',
            'salary_amount'       => 'nullable|numeric|min:0',
            'working_schedule'    => 'nullable|array',
            'assigned_roles'      => 'nullable|array',
            'responsibilities'    => 'nullable|array',
            'bio'                 => 'nullable|string|max:4000',
            'photo'               => 'nullable|image|max:2048',
            'public_visible'      => 'nullable|boolean',
            'featured'            => 'nullable|boolean',
            'show_email_public'   => 'nullable|boolean',
            'show_phone_public'   => 'nullable|boolean',
            'order'               => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $data['is_active'] = $data['employment_status'] !== 'resigned';
        $data['order'] = (int) ($data['order'] ?? 0);

        $staff = Staff::create($data);

        return $this->created($staff, 'Staff member created.');
    }

    public function show(Staff $staff): JsonResponse
    {
        $staff->load(['documents']);
        return $this->success($staff);
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $data = $request->validate([
            'staff_code'          => ['required', 'string', 'max:50', Rule::unique('staff', 'staff_code')->ignore($staff->id)],
            'name'                => 'required|string|max:255',
            'designation'         => 'required|string|max:255',
            'department'          => 'nullable|string|max:255',
            'email'               => ['nullable', 'email', 'max:255', Rule::unique('staff', 'email')->ignore($staff->id)],
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'dob'                 => 'nullable|date',
            'gender'              => 'nullable|in:male,female,other,prefer_not_to_say',
            'employment_type'     => 'nullable|in:full_time,part_time,contract,temporary',
            'employment_status'   => 'required|in:active,leave,resigned',
            'join_date'           => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:join_date',
            'salary_amount'       => 'nullable|numeric|min:0',
            'working_schedule'    => 'nullable|array',
            'assigned_roles'      => 'nullable|array',
            'responsibilities'    => 'nullable|array',
            'bio'                 => 'nullable|string|max:4000',
            'photo'               => 'nullable|image|max:2048',
            'public_visible'      => 'nullable|boolean',
            'featured'            => 'nullable|boolean',
            'show_email_public'   => 'nullable|boolean',
            'show_phone_public'   => 'nullable|boolean',
            'order'               => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('photo')) {
            if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
                Storage::disk('public')->delete($staff->photo);
            }
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $data['is_active'] = $data['employment_status'] !== 'resigned';
        $data['order'] = (int) ($data['order'] ?? $staff->order);

        $staff->update($data);

        return $this->success($staff, 'Staff updated.');
    }

    public function destroy(Staff $staff): JsonResponse
    {
        if ($staff->photo && Storage::disk('public')->exists($staff->photo)) {
            Storage::disk('public')->delete($staff->photo);
        }
        $staff->documents()->delete();
        $staff->attendanceRecords()->delete();
        $staff->delete();

        return $this->success(['message' => 'Staff removed.']);
    }

    public function toggleFeatured(Staff $staff): JsonResponse
    {
        $staff->update(['featured' => !$staff->featured]);
        return $this->success($staff, $staff->featured ? 'Featured' : 'Unfeatured');
    }

    public function togglePublic(Staff $staff): JsonResponse
    {
        $staff->update(['public_visible' => !$staff->public_visible]);
        return $this->success($staff, $staff->public_visible ? 'Made public' : 'Hidden');
    }
}
