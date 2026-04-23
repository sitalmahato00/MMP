@extends('layouts.guest')
@section('title', $page->title ?? 'Page')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header" style="background-color: #003D82;">📄 {{ $page->title }}</div>
            <div class="bg-white border border-gray-200 border-t-0 p-8 prose prose-sm max-w-none prose-headings:text-blue-900 prose-a:text-blue-700">
                {!! $page->content !!}
            </div>
        </div>
        <div>
            <div class="section-header" style="background-color: #003D82;">🔗 Quick Links</div>
            <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Notices</a>
                <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Our Programs</a>
                <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Downloads</a>
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-blue-800"><span>🔐</span> Student Portal</a>
            </div>
        </div>
    </div>
</div>
@endsection

