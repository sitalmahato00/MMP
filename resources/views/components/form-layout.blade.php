{{--
    x-form-layout
    Props:
      title      - Page title (required)
      subtitle   - Subtitle / description (optional)
      back       - Back link URL (optional)
      breadcrumb - Breadcrumb HTML or string (optional)
    Slots:
      $slot      - Main form content
      $sidebar   - Right information panel content
      $footer    - Footer action bar content
--}}
@props(['title', 'subtitle' => null, 'back' => null])

<div class="w-full max-w-screen-2xl mx-auto space-y-6 px-0 sm:px-4 lg:px-6">
    @if(trim($breadcrumb ?? ''))
        <div class="text-sm text-slate-500">
            {!! $breadcrumb !!}
        </div>
    @endif

    <div class="rounded-[8px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50/80">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                        @if($back)
                            <a href="{{ $back }}" class="inline-flex items-center gap-2 rounded border border-slate-200 bg-white px-3 py-2 text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back
                            </a>
                        @endif
                        <span class="font-medium">{{ $title }}</span>
                    </div>
                    <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $subtitle }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{ $header ?? '' }}
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[3fr_1fr] px-6 py-6">
            <div class="space-y-6">
                {{ $slot }}
            </div>

            <aside class="space-y-4">
                {{ $sidebar ?? '' }}
            </aside>
        </div>

        @isset($footer)
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
