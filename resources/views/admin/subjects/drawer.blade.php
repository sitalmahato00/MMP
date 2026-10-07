<div class="space-y-5">
    <div>
        <div class="flex items-center gap-2">
            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                {{ $subject->code }}
            </span>
            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                {{ ucfirst($subject->type) }}
            </span>
        </div>
        <h3 class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $subject->name }}</h3>
        <p class="text-xs text-slate-500">
            {{ $subject->program?->department?->name ?? 'Dept' }} &bull; {{ $subject->program?->name ?? 'Program' }} &bull; Sem {{ $subject->semester }}
        </p>
    </div>

    {{-- Marks summary --}}
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5 dark:border-slate-800 dark:bg-slate-800/40">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Marks Overview</h4>
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <span class="text-slate-400">Total Full Marks:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 ml-1">{{ $subject->total_full_marks }}</span>
            </div>
            <div>
                <span class="text-slate-400">Total Pass Marks:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 ml-1">{{ $subject->total_pass_marks }}</span>
            </div>
            <div>
                <span class="text-slate-400">Credit Hours:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200 ml-1">{{ $subject->credit_hours ?? 0 }}</span>
            </div>
            <div>
                <span class="text-slate-400">Status:</span>
                <span class="font-bold {{ $subject->is_active ? 'text-emerald-600' : 'text-rose-600' }} ml-1">
                    {{ $subject->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Assigned Teachers --}}
    <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Assigned Teachers</h4>
        @if($assignedTeachers->isNotEmpty())
            <ul class="space-y-2 text-xs">
                @foreach($assignedTeachers as $t)
                    <li class="flex items-center justify-between rounded-lg border border-slate-100 p-2 dark:border-slate-800">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $t->user?->name }}</span>
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $t->pivot->role }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-xs text-slate-400 italic">No teachers currently assigned.</p>
        @endif
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
        <a href="{{ route('admin.subjects.show', $subject) }}"
           class="flex-1 rounded-xl bg-slate-900 py-2 text-center text-xs font-bold text-white hover:bg-slate-800 dark:bg-slate-700">
            View Full Details
        </a>
        <a href="{{ route('admin.subjects.edit', $subject) }}"
           class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300">
            Edit
        </a>
    </div>
</div>
