@extends('layouts.guest')
@section('title', 'Apply Now')
@section('meta_description', 'Apply for admission at Manmohan Memorial Polytechnic. Fill out the online application form for diploma courses.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Apply for Admission</h1>
            <p class="text-gray-500">Fill out the form below to apply for a diploma program at MMP.</p>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6 flex items-start gap-3">
                <svg class="w-6 h-6 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-semibold text-green-800">Application Submitted Successfully!</p>
                    <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('public.apply.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Personal Information --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                    <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Personal Information</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name <span class="text-blue-500">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Enter your full name"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] @error('full_name') border-blue-400 bg-blue-50 @enderror">
                            @error('full_name') <p class="text-blue-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-blue-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] @error('email') border-blue-400 bg-blue-50 @enderror">
                            @error('email') <p class="text-blue-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone <span class="text-blue-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="98XXXXXXXX"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] @error('phone') border-blue-400 bg-blue-50 @enderror">
                            @error('phone') <p class="text-blue-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date of Birth (BS)</label>
                            <x-bs-date-picker name="dob"/>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gender</label>
                            <select name="gender" class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="Your address"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guardian Information --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                    <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Guardian Information</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Guardian Name</label>
                            <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Parent/Guardian name"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Guardian Phone</label>
                            <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="98XXXXXXXX"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 rounded-t-xl">
                    <h2 class="text-xs font-bold text-gray-600 uppercase tracking-widest">Academic Information</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Preferred Department <span class="text-blue-500">*</span></label>
                            <select name="department_id" required class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] @error('department_id') border-blue-400 bg-blue-50 @enderror">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="text-blue-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">SEE GPA</label>
                            <input type="text" name="gpa" value="{{ old('gpa') }}" placeholder="e.g. 3.60"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Previous School/College</label>
                        <input type="text" name="previous_school" value="{{ old('previous_school') }}" placeholder="Name of your previous school"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Additional Message</label>
                        <textarea name="message" rows="3" placeholder="Anything else you'd like us to know..."
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82]">{{ old('message') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-[#003D82] hover:bg-[#a00000] text-white px-8 py-3 rounded-lg font-bold text-sm shadow-lg transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

