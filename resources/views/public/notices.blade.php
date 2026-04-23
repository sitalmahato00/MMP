@extends('layouts.guest')
@section('title', 'Notices & Announcements')
@section('breadcrumb', true)

@section('content')
@php
    $activeType = $activeType ?? 'all';
    $isCtevtType = in_array($activeType, ['ctevt-general', 'ctevt-result'], true);
    $activeCtevtTab = $activeType === 'ctevt-result' ? 'result' : 'general';
    $ctevtGeneralItems = collect($ctevtGeneralNotices['items'] ?? []);
    $ctevtResultItems = collect($ctevtResultNotices['items'] ?? []);
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #003D82;">
                <span>📋 All Notices & Announcements</span>
                <span class="text-blue-200 text-xs">{{ $isCtevtType ? ($ctevtGeneralItems->count() + $ctevtResultItems->count()) : $notices->total() }} notices</span>
            </div>
            <div class="bg-white border border-gray-200 border-t-0" x-data="{ activeCtevtTab: '{{ $activeCtevtTab }}' }">
                <div class="flex border-b border-gray-200">
                    <a href="{{ route('public.notices') }}"
                       class="flex-1 text-center py-3 text-sm font-bold transition-colors {{ !$isCtevtType ? 'bg-[#003D82] text-white' : 'bg-white text-gray-700 hover:bg-blue-50' }}">
                        All Notices
                    </a>
                    <a href="{{ route('public.notices', ['type' => 'ctevt-general']) }}"
                       class="flex-1 text-center py-3 text-sm font-bold transition-colors {{ $isCtevtType ? 'bg-[#003D82] text-white' : 'bg-white text-gray-700 hover:bg-blue-50' }}">
                        CTEVT Notices
                    </a>
                </div>
                @if($isCtevtType)
                    <div class="flex border-b border-gray-200 bg-gray-50">
                        <button type="button" @click="activeCtevtTab = 'general'" :class="activeCtevtTab === 'general' ? 'bg-[#003D82] text-white' : 'bg-transparent text-gray-700 hover:bg-blue-50'" class="flex-1 py-3 text-sm font-bold transition-colors">
                            General Notices
                        </button>
                        <button type="button" @click="activeCtevtTab = 'result'" :class="activeCtevtTab === 'result' ? 'bg-[#003D82] text-white' : 'bg-transparent text-gray-700 hover:bg-blue-50'" class="flex-1 py-3 text-sm font-bold transition-colors">
                            Published Result
                        </button>
                    </div>

                    <div x-show="activeCtevtTab === 'general'" x-cloak>
                        @forelse($ctevtGeneralItems as $notice)
                            <a href="{{ $notice['url'] ?? route('public.notices', ['type' => 'ctevt-general']) }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors">
                                <div class="flex-shrink-0 w-12 h-12 text-white flex items-center justify-center rounded" style="background-color: #003D82;">
                                    <span class="text-[9px] font-bold uppercase leading-tight text-center">CTEVT</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 group-hover:text-blue-800 text-sm leading-snug block transition-colors">
                                        {{ $notice['title'] ?? 'Notice' }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        @if(!empty($notice['updated_date']))
                                            <span class="text-xs text-gray-400">{{ $notice['updated_date'] }}</span>
                                        @endif
                                        @if(!empty($notice['publisher']))
                                            <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">{{ $notice['publisher'] }}</span>
                                        @endif
                                        @if(!empty($notice['files_count']))
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $notice['files_count'] }} file{{ $notice['files_count'] > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-16 text-center text-gray-400">
                                <p class="text-5xl mb-4">📋</p>
                                <p class="font-semibold text-gray-500">No live CTEVT general notices found.</p>
                            </div>
                        @endforelse
                    </div>

                    <div x-show="activeCtevtTab === 'result'" x-cloak>
                        @forelse($ctevtResultItems as $notice)
                            <a href="{{ $notice['url'] ?? route('public.notices', ['type' => 'ctevt-result']) }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors">
                                <div class="flex-shrink-0 w-12 h-12 text-white flex items-center justify-center rounded" style="background-color: #003D82;">
                                    <span class="text-[9px] font-bold uppercase leading-tight text-center">CTEVT</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 group-hover:text-blue-800 text-sm leading-snug block transition-colors">
                                        {{ $notice['title'] ?? 'Result Notice' }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        @if(!empty($notice['updated_date']))
                                            <span class="text-xs text-gray-400">{{ $notice['updated_date'] }}</span>
                                        @endif
                                        @if(!empty($notice['publisher']))
                                            <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">{{ $notice['publisher'] }}</span>
                                        @endif
                                        @if(!empty($notice['files_count']))
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $notice['files_count'] }} file{{ $notice['files_count'] > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="py-16 text-center text-gray-400">
                                <p class="text-5xl mb-4">📋</p>
                                <p class="font-semibold text-gray-500">No live CTEVT result notices found.</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="mb-6">
                    @forelse($notices as $notice)
                        @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                        <a href="{{ route('public.notice.show', $notice->slug) }}" class="group flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors cursor-pointer">
                            <div class="flex-shrink-0 w-12 h-14 text-white flex flex-col items-center justify-center rounded" style="background-color: #003D82;">
                                <span class="text-[8px] font-bold">{{ bsDate($noticeDate, 'Y') }}</span>
                                <span class="text-xl font-black leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                                <span class="text-[8px] font-bold uppercase">{{ bsDate($noticeDate, 'F') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 group-hover:text-blue-800 text-sm leading-snug transition-colors">
                                    {{ $notice->title }}
                                </div>
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase">{{ $notice->type }}</span>
                                    
                                    {{-- Show department name for department-specific notices --}}
                                    @if($notice->department)
                                        <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                            {{ $notice->department->name }}
                                        </span>
                                    @endif
                                    
                                    {{-- Show program details for program-specific notices --}}
                                    @if($notice->program)
                                        <span class="text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                            {{ $notice->program->name }}
                                        </span>
                                        @if($notice->semester)
                                            <span class="text-xs font-medium text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-100">
                                                Semester {{ $notice->semester }}
                                            </span>
                                        @endif
                                    @endif
                                    
                                    <span class="text-xs text-gray-400">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                                    @if($notice->attachment)
                                        <span class="text-xs text-blue-700 flex items-center gap-1 font-semibold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Attachment
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-16 text-center text-gray-400">
                            <p class="text-5xl mb-4">📋</p>
                            <p class="font-semibold text-gray-500">
                                @if($activeType === 'exam')
                                    No exam schedule or result notices published yet.
                                @elseif($activeType === 'department')
                                    No department notices published yet.
                                @elseif($activeType === 'program')
                                    No program notices published yet.
                                @elseif($activeType === 'all')
                                    No notices published yet.
                                @else
                                    No notices published yet.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
                {{ $notices->withQueryString()->links() }}
                @endif
        </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #003D82;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                    @foreach([
                        ['label' => 'All Notices', 'href' => route('public.notices')],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Departments', 'href' => route('public.departments')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors">
                            <span class="text-blue-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

