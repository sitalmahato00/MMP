{{--
    x-bs-date-picker
    Nepali (Bikram Sambat) datepicker with calendar popup.
    Props:
      name        - Input name (required)
      value       - Pre-filled BS date string YYYY-MM-DD (optional)
      placeholder - Placeholder text (default: YYYY-MM-DD)
      required    - Boolean (default: false)
      adName      - Hidden input name for AD date (optional, e.g. 'dob_ad')
--}}
@props(['name', 'value' => null, 'placeholder' => 'YYYY-MM-DD', 'required' => false, 'adName' => null])

@php
    $uid = 'bsdp_' . str_replace(['.', '[', ']', ' '], '_', $name) . '_' . Str::random(4);
    $resolvedValue = old($name, $value);
@endphp

<div
    x-data="bsDatePicker('{{ $uid }}', '{{ $resolvedValue }}')"
    x-init="init(); window.addEventListener('resize', () => { if (open) _calcPopupPlacement(); }); window.addEventListener('scroll', () => { if (open) _calcPopupPlacement(); }, true)"
    class="relative"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    id="{{ $uid }}_wrap"
>
    {{-- Visible BS date input --}}
    <div class="relative">
        <input
            type="text"
            name="{{ $name }}"
            id="{{ $uid }}"
            x-ref="input"
            x-model="bsValue"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            @focus="openCalendar()"
            @input="onManualInput()"
            autocomplete="off"
            {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg pl-3.5 pr-10 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B0000]/25 focus:border-[#8B0000] transition-all duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}
        >
        <button type="button" @click="toggleCalendar()" tabindex="-1" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-[#8B0000] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>
    </div>

    @if($adName)
    <input type="hidden" name="{{ $adName }}" x-model="adValue">
    @endif

    {{-- Calendar dropdown --}}
    <div
        x-show="open"
        x-cloak
        x-ref="panel"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed z-[9999] mt-1 w-[320px] max-w-[calc(100vw-1.5rem)] bg-white border border-gray-200 rounded-xl shadow-2xl shadow-black/10 p-3"
        :style="`top: ${popupTop}px; left: ${popupLeft}px;`"
    >
        {{-- Header: month/year nav --}}
        <div class="mb-2 flex items-center justify-between gap-1">
            <button type="button" @click="prevMonth()" @mousedown.prevent class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex flex-1 flex-wrap items-center justify-center gap-1 min-w-0">
                <select x-model.number="viewMonth" @change="buildCalendar()" class="min-w-[7.5rem] max-w-full flex-1 text-sm font-bold text-gray-800 bg-transparent border-none focus:ring-0 cursor-pointer px-1 py-0.5 rounded hover:bg-gray-50">
                    <template x-for="(m, i) in monthNames" :key="i">
                        <option :value="i" x-text="m" :selected="i === viewMonth"></option>
                    </template>
                </select>
                <select x-model.number="viewYear" @change="buildCalendar()" class="w-[5.5rem] shrink-0 text-sm font-bold text-gray-800 bg-transparent border-none focus:ring-0 cursor-pointer px-1 py-0.5 rounded hover:bg-gray-50">
                    <template x-for="y in yearRange" :key="y">
                        <option :value="y" x-text="y" :selected="y === viewYear"></option>
                    </template>
                </select>
            </div>
            <button type="button" @click="nextMonth()" @mousedown.prevent class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Day names --}}
        <div class="grid grid-cols-7 mb-1">
            <template x-for="d in dayNames" :key="d">
                <div class="text-center text-[10px] font-bold text-gray-400 uppercase py-1" x-text="d"></div>
            </template>
        </div>

        {{-- Day grid --}}
        <div class="grid grid-cols-7 gap-0.5" @mousedown.prevent>
            <template x-for="cell in calendarCells" :key="cell.key">
                <button
                    type="button"
                    x-text="cell.day || ''"
                    :disabled="!cell.day"
                    @click="cell.day && selectDate(cell.day)"
                    class="h-8 w-full rounded-lg text-sm font-medium transition-all duration-100"
                    :class="{
                        'hover:bg-red-50 hover:text-[#8B0000] text-gray-700 cursor-pointer': cell.day && !cell.isToday && !cell.isSelected,
                        'bg-[#8B0000] text-white font-bold shadow-sm': cell.isSelected,
                        'ring-2 ring-[#8B0000]/30 font-bold text-[#8B0000]': cell.isToday && !cell.isSelected,
                        'text-transparent cursor-default': !cell.day,
                    }"
                ></button>
            </template>
        </div>

        {{-- Footer: Today button --}}
        <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
            <button type="button" @click="goToday()" @mousedown.prevent class="text-xs font-semibold text-[#8B0000] hover:underline">Today</button>
            <span class="text-[10px] text-gray-400" x-text="todayLabel"></span>
        </div>
    </div>
</div>

@error($name)
    <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ $message }}
    </p>
@enderror
