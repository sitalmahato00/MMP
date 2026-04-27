@extends('layouts.guest')

@section('title', 'Check Result')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-slate-950 py-8 lg:py-12">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg p-6 lg:p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#003D82] dark:text-blue-400 mb-2">Check Your Result</h1>
                <p class="text-gray-600 dark:text-gray-400">Enter your details to view your examination results</p>
            </div>

            <form method="POST" action="{{ route('public.result.submit') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="src_year" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Examination Year
                    </label>
                    <select name="src_year" id="src_year" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#003D82] focus:border-transparent dark:bg-slate-800 dark:text-white">
                        <option value="">Select Year</option>
                        <option value="2082">2082</option>
                        <option value="2081">2081</option>
                        <option value="2080">2080</option>
                        <option value="2079">2079</option>
                        <option value="2078">2078</option>
                        <option value="2077">2077</option>
                    </select>
                    @error('src_year')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="src_level" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Level
                    </label>
                    <select name="src_level" id="src_level" required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#003D82] focus:border-transparent dark:bg-slate-800 dark:text-white">
                        <option value="">Select Level</option>
                        <option value="2">Diploma Level 2</option>
                        <option value="3">Diploma Level 3</option>
                    </select>
                    @error('src_level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="exam_symbol_number" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Symbol Number
                    </label>
                    <input type="text" name="exam_symbol_number" id="exam_symbol_number" required
                           placeholder="Enter your symbol number"
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#003D82] focus:border-transparent dark:bg-slate-800 dark:text-white">
                    @error('exam_symbol_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dob" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Date of Birth (YYYY-MM-DD)
                    </label>
                    <input type="date" name="dob" id="dob" required
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-[#003D82] focus:border-transparent dark:bg-slate-800 dark:text-white">
                    @error('dob')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full bg-[#003D82] hover:bg-[#002a5c] text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg">
                    Check Result
                </button>
            </form>

            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Note:</strong> Please enter your details exactly as they appear on your admit card.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
