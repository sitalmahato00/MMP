<div class="w-full overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="w-full text-sm text-left text-gray-600">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
            <tr>
                {{ $header }}
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
