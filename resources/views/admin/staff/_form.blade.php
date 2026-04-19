@php
    $model = $staff ?? null;
    $schedule = $model?->working_schedule ?? [];
    $assignedRoles = old('assigned_roles', implode(', ', $model?->assigned_roles ?? []));
    $responsibilities = old('responsibilities', implode(', ', $model?->responsibilities ?? []));
    $scheduleDays = old('working_schedule_days', implode(', ', data_get($schedule, 'days', [])));
    $scheduleLabel = old('working_schedule_label', data_get($schedule, 'label'));
    $scheduleStart = old('working_schedule_start', data_get($schedule, 'start'));
    $scheduleEnd = old('working_schedule_end', data_get($schedule, 'end'));
    $publicVisible = old('public_visible', $model?->public_visible ?? true);
    $featured = old('featured', $model?->featured ?? false);
    $showEmailPublic = old('show_email_public', $model?->show_email_public ?? true);
    $showPhonePublic = old('show_phone_public', $model?->show_phone_public ?? true);
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Identity</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Staff profile basics</h2>
                <p class="mt-1 text-sm text-slate-500">These fields drive the administrative record and public directory card.</p>
            </div>
            @if(! empty($model?->photo_url))
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                    <img src="{{ $model->photo_url }}" alt="{{ $model->name }}" class="h-24 w-24 object-cover">
                </div>
            @endif
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Staff Code <span class="text-[#8B0000]">*</span></label>
                <input type="text" name="staff_code" value="{{ old('staff_code', $model?->staff_code) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('staff_code')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-medium text-slate-700">Full Name <span class="text-[#8B0000]">*</span></label>
                <input type="text" name="name" value="{{ old('name', $model?->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('name')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Designation <span class="text-[#8B0000]">*</span></label>
                <input type="text" name="designation" value="{{ old('designation', $model?->designation) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('designation')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Department / Office</label>
                <input type="text" name="department" value="{{ old('department', $model?->department) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('department')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $model?->email) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('email')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $model?->phone) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('phone')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 md:col-span-2 xl:col-span-3">
                <label class="text-sm font-medium text-slate-700">Address</label>
                <input type="text" name="address" value="{{ old('address', $model?->address) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('address')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Profile Photo</label>
                <input type="file" name="photo" accept="image/*" class="block w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#8B0000] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                @error('photo')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Display Order</label>
                <input type="number" name="order" value="{{ old('order', $model?->order ?? 0) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('order')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Date of Birth</label>
                <x-bs-date-picker name="dob" :value="old('dob', $model?->dob ? bsDate($model->dob) : '')" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
                @error('dob')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Gender</label>
                <select name="gender" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">Select gender</option>
                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $model?->gender) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Employment</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">Work and visibility settings</h2>
            <p class="mt-1 text-sm text-slate-500">These values control status filters, public cards, and the staff profile timeline.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Employment Type</label>
                <select name="employment_type" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">Select type</option>
                    @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'temporary' => 'Temporary'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_type', $model?->employment_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_type')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Employment Status <span class="text-[#8B0000]">*</span></label>
                <select name="employment_status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    @foreach(['active' => 'Active', 'leave' => 'On Leave', 'resigned' => 'Resigned'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('employment_status', $model?->employment_status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('employment_status')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Join Date</label>
                <x-bs-date-picker name="join_date" :value="old('join_date', $model?->join_date ? bsDate($model->join_date) : '')" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
                @error('join_date')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">End Date</label>
                <x-bs-date-picker name="end_date" :value="old('end_date', $model?->end_date ? bsDate($model->end_date) : '')" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10" />
                @error('end_date')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Salary Amount</label>
                <input type="number" step="0.01" name="salary_amount" value="{{ old('salary_amount', $model?->salary_amount) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('salary_amount')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Working Schedule Label</label>
                <input type="text" name="working_schedule_label" value="{{ $scheduleLabel }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('working_schedule_label')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Working Days</label>
                <input type="text" name="working_schedule_days" value="{{ $scheduleDays }}" placeholder="Mon, Tue, Wed" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                <p class="text-xs text-slate-500">Use comma-separated day names.</p>
                @error('working_schedule_days')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Start Time</label>
                <input type="time" name="working_schedule_start" value="{{ $scheduleStart }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('working_schedule_start')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">End Time</label>
                <input type="time" name="working_schedule_end" value="{{ $scheduleEnd }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                @error('working_schedule_end')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 md:col-span-2 xl:col-span-3">
                <label class="text-sm font-medium text-slate-700">Assigned Roles</label>
                <textarea name="assigned_roles" rows="2" placeholder="Admin, Accounts, Procurement" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">{{ $assignedRoles }}</textarea>
                <p class="text-xs text-slate-500">Comma-separated values are stored as a list.</p>
                @error('assigned_roles')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 md:col-span-2 xl:col-span-3">
                <label class="text-sm font-medium text-slate-700">Responsibilities</label>
                <textarea name="responsibilities" rows="3" placeholder="Admissions support, records management, reports" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">{{ $responsibilities }}</textarea>
                <p class="text-xs text-slate-500">Comma-separated values are stored as a list.</p>
                @error('responsibilities')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2 md:col-span-2 xl:col-span-3">
                <label class="text-sm font-medium text-slate-700">Bio</label>
                <textarea name="bio" rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">{{ old('bio', $model?->bio) }}</textarea>
                @error('bio')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#8B0000]">Visibility</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">Public directory controls</h2>
            <p class="mt-1 text-sm text-slate-500">Use these toggles to decide how the profile appears on the public staff page.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="public_visible" value="1" @checked($publicVisible) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Public visible</span>
                    <span class="mt-1 block text-sm text-slate-500">Show in the staff directory and profile routes.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="featured" value="1" @checked($featured) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Featured</span>
                    <span class="mt-1 block text-sm text-slate-500">Highlight this profile in prominent staff surfaces.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="show_email_public" value="1" @checked($showEmailPublic) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Show email</span>
                    <span class="mt-1 block text-sm text-slate-500">Make the email address visible on the public profile.</span>
                </span>
            </label>

            <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="show_phone_public" value="1" @checked($showPhonePublic) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]">
                <span>
                    <span class="block text-sm font-semibold text-slate-900">Show phone</span>
                    <span class="mt-1 block text-sm text-slate-500">Make the phone number visible on the public profile.</span>
                </span>
            </label>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#8B0000]/25 transition hover:bg-[#6f0000]">{{ $submitLabel }}</button>
        <a href="{{ $backUrl }}" class="inline-flex items-center justify-center rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">Cancel</a>
    </div>
</form>