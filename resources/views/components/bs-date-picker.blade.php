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
    class="relative"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    @scroll.window="if (open) calcPlacement()"
    @resize.window="if (open) calcPlacement()"
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
            {{ $attributes->merge(['class' => 'w-full border border-gray-200 rounded-lg pl-3.5 pr-10 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/25 focus:border-blue-600 transition-all duration-150' . ($errors->has($name) ? ' border-red-400 bg-red-50' : '')]) }}
        >
        <button type="button" @click.stop="toggleCalendar()" tabindex="-1" class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-blue-600 transition-colors">
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
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :class="{
            'bottom-full mb-1.5': dropUp,
            'top-full mt-1.5': !dropUp,
            'right-0': alignRight,
            'left-0': !alignRight
        }"
        class="absolute z-[9999] w-[310px] sm:w-[320px] max-w-[calc(100vw-2rem)] bg-white border border-gray-200 rounded-xl shadow-2xl shadow-black/20 p-3"
        :style="dropUp ? 'position: absolute; bottom: calc(100% + 6px); top: auto;' : 'position: absolute; top: calc(100% + 6px); bottom: auto;'"
    >
        {{-- Header: month/year nav --}}
        <div class="mb-2 flex items-center justify-between gap-1">
            <button type="button" @click.stop="prevMonth()" @mousedown.prevent class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex flex-1 flex-wrap items-center justify-center gap-1 min-w-0">
                <select x-model.number="viewMonth" @change="buildCalendar()" class="min-w-[6.5rem] text-xs font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 px-2 py-1">
                    <template x-for="(m, i) in monthNames" :key="i">
                        <option :value="i" x-text="m" :selected="i === viewMonth"></option>
                    </template>
                </select>
                <select x-model.number="viewYear" @change="buildCalendar()" class="w-[5.2rem] text-xs font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 px-2 py-1">
                    <template x-for="y in yearRange" :key="y">
                        <option :value="y" x-text="y" :selected="y === viewYear"></option>
                    </template>
                </select>
            </div>
            <button type="button" @click.stop="nextMonth()" @mousedown.prevent class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-800 transition-colors">
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
        <div class="grid grid-cols-7 gap-1" @mousedown.prevent>
            <template x-for="cell in calendarCells" :key="cell.key">
                <button
                    type="button"
                    x-text="cell.day || ''"
                    :disabled="!cell.day"
                    @click="cell.day && selectDate(cell.day)"
                    class="h-8 w-full rounded-lg text-xs font-medium transition-all duration-100 flex items-center justify-center"
                    :class="{
                        'hover:bg-blue-50 hover:text-blue-600 text-gray-700 cursor-pointer': cell.day && !cell.isToday && !cell.isSelected,
                        'bg-blue-600 text-white font-bold shadow-sm': cell.isSelected,
                        'ring-2 ring-blue-500/40 font-bold text-blue-600 bg-blue-50/50': cell.isToday && !cell.isSelected,
                        'text-transparent cursor-default pointer-events-none': !cell.day,
                    }"
                ></button>
            </template>
        </div>

        {{-- Footer: Today button --}}
        <div class="mt-2.5 pt-2 border-t border-gray-100 flex items-center justify-between">
            <button type="button" @click="goToday()" @mousedown.prevent class="text-xs font-bold text-blue-600 hover:underline">Today</button>
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
