@props(['paginator'])

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                {{ $head ?? '' }}
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            {{ $slot }}
        </tbody>
    </table>
</div>

@if($paginator && method_exists($paginator, 'links'))
    <div class="mt-4">
        {{ $paginator->links() }}
    </div>
@endif
