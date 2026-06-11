{{--
    x-form-sidebar
    Slot:
      $slot - additional custom cards
--}}

<div class="space-y-4">
    <div class="rounded-[8px] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-sm font-semibold text-slate-900 mb-3">Guidelines</div>
        <ul class="space-y-2 text-sm leading-6 text-slate-600 list-disc list-inside">
            <li>Fill all required fields correctly.</li>
            <li>Provide accurate and complete information.</li>
            <li>Upload clear documents where requested.</li>
            <li>Fields marked with <span class="font-semibold text-red-600">*</span> are mandatory.</li>
        </ul>
    </div>

    <div class="rounded-[8px] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-sm font-semibold text-slate-900 mb-3">Help Information</div>
        <p class="text-sm leading-6 text-slate-600">Please ensure all information is correct before submitting the form. Contact administration for any help or clarification.</p>
    </div>

    <div class="rounded-[8px] border border-slate-200 bg-white p-4 shadow-sm">
        <div class="text-sm font-semibold text-slate-900 mb-3">Important Notice</div>
        <p class="text-sm leading-6 text-slate-600">Information provided must be true and correct. Any false information may result in administrative action or cancellation of admission.</p>
    </div>

    {{ $slot }}
</div>
