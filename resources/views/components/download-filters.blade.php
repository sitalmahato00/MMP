{{--
    x-download-filters
    Filter form for downloads/gallery using form components
    Props:
      categories - Array of category options
--}}
@props(['categories' => []])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
    <form method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Search Input --}}
            <x-input 
                name="search" 
                label="Search"
                placeholder="Search files or titles..." 
                :value="request('search')"
            />

            {{-- Category Select --}}
            <x-select 
                name="category" 
                label="Type"
                :value="request('category')"
            >
                <option value="">All Types</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}</option>
                @endforeach
            </x-select>

            {{-- From Date Input --}}
            <x-input 
                name="from_date" 
                label="From Date"
                placeholder="From BS date"
                :value="request('from_date')"
            />

            {{-- To Date Input --}}
            <x-input 
                name="to_date" 
                label="To Date"
                placeholder="To BS date"
                :value="request('to_date')"
            />
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-2 justify-between pt-2">
            <div class="flex gap-2">
                <x-btn type="submit" variant="danger">Filter</x-btn>
                <x-btn href="?" variant="ghost">Reset</x-btn>
            </div>
        </div>
    </form>
</div>
