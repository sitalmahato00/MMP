@props(['selectedSemester', 'currentSemester', 'semesterOptions', 'routeName', 'extraParams' => []])

@if($currentSemester > 1)
<div class="flex items-center gap-2 flex-wrap">
    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Semester:</span>
    <div class="flex flex-wrap gap-1.5">
        @foreach($semesterOptions as $sem)
            @php
                $params = array_merge($extraParams, ['semester' => $sem]);
                $isActive = $sem === $selectedSemester;
                $isCurrent = $sem === $currentSemester;
            @endphp
            <a href="{{ route($routeName, $params) }}"
               class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold transition
                      {{ $isActive
                          ? 'bg-blue-600 text-white shadow-sm'
                          : 'bg-slate-100 dark:bg-[#1e3a5f] text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#2d4a70]' }}">
                Sem {{ $sem }}
                @if($isCurrent)
                    <span class="inline-block h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-white' : 'bg-emerald-500' }}"></span>
                @endif
            </a>
        @endforeach
    </div>
    @if($selectedSemester < $currentSemester)
        <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/30 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-700">
            Viewing Semester {{ $selectedSemester }} (Previous)
        </span>
    @endif
</div>
@endif
