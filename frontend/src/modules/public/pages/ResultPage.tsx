import { useQuery } from '@tanstack/react-query';
import { useState, useEffect } from 'react';
import { getResultForm } from '@shared/services/public.service';

const CTEVT_URL = 'https://itms.ctevt.org.np:5580/search_results';

export default function ResultPage() {
  const { data, isLoading } = useQuery({ queryKey: ['public-result-form'], queryFn: getResultForm });
  const form: any = data ?? {};
  const fields: any[] = form.fields ?? [];
  const hiddenFields: any[] = form.hidden_fields ?? [];
  const formTitle: string = form.title ?? 'Yearly/Semester Check Results';
  const officialResultUrl: string = form.action_url ?? CTEVT_URL;

  const [values, setValues] = useState<Record<string, string>>({});

  // Initialize select fields with their pre-selected option from CTEVT form
  useEffect(() => {
    if (fields.length > 0) {
      const initial: Record<string, string> = {};
      fields.forEach((f: any) => {
        if (f.type === 'select' && Array.isArray(f.options)) {
          const sel = f.options.find((o: any) => o.selected);
          if (sel) initial[f.name] = sel.value;
        }
      });
      setValues(initial);
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  function handleChange(name: string, value: string) {
    setValues(prev => ({ ...prev, [name]: value }));
  }

  if (isLoading) return <div className="flex justify-center py-32"><div className="w-10 h-10 border-4 border-[#003D82] border-t-transparent rounded-full animate-spin" /></div>;

  return (
    <div className="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
      <div className="grid grid-cols-1 xl:grid-cols-5 gap-8 items-start">
        {/* Left info panel */}
        <div className="xl:col-span-2 order-1 xl:order-none">
          <div className="rounded-3xl overflow-hidden shadow-xl border border-blue-100 bg-gradient-to-br from-[#003D82] via-[#002D5F] to-[#001F4D] text-white">
            <div className="p-8 md:p-10 space-y-5">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-50 border border-white/15">
                Official CTEVT Portal
              </div>
              <div className="space-y-3">
                <h1 className="text-3xl md:text-4xl font-bold leading-tight">Online Result Search</h1>
                <p className="text-blue-50/90 leading-relaxed text-sm md:text-base max-w-xl">
                  Use the exact CTEVT inputs below. The form validates on MMP and then opens the official result page in a new tab.
                </p>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                {[
                  { title: 'Fast access', desc: 'Submit once and land on the live CTEVT result page.' },
                  { title: 'Mobile friendly', desc: 'The layout adapts cleanly on smaller screens.' },
                  { title: 'Exact inputs', desc: 'Examination Year, Level, Symbol Number, Date of Birth (B.S.).' },
                  { title: 'Official URL', desc: officialResultUrl },
                ].map(c => (
                  <div key={c.title} className="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <div className="font-semibold text-white">{c.title}</div>
                    <div className="text-blue-50/80 mt-1 break-all">{c.desc}</div>
                  </div>
                ))}
              </div>
            </div>
            <div className="px-8 md:px-10 py-4 bg-black/10 text-xs md:text-sm text-blue-50/90 border-t border-white/10">
              The form uses the same field names and values as the official CTEVT page.
            </div>
          </div>
        </div>

        {/* Form panel */}
        <div className="xl:col-span-3 order-2 xl:order-none">
          <div className="section-header flex items-center justify-between" style={{ backgroundColor: '#003D82' }}>
            <span>🔎 Result Search Form</span>
            <span className="text-blue-200 text-xs font-semibold">Public Page</span>
          </div>
          <div className="bg-white border border-gray-200 border-t-0 shadow-sm overflow-hidden">
            <div className="px-4 md:px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50/40">
              <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <div>
                  <h2 className="text-lg md:text-xl font-semibold text-gray-800">{formTitle}</h2>
                  <p className="text-xs md:text-sm text-gray-500 mt-1">Enter the same details shown on the official CTEVT page.</p>
                </div>
                <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-[0.18em] border bg-emerald-50 text-emerald-700 border-emerald-100">
                  Official Input Set
                </span>
              </div>
            </div>

            <form name="frmCheckResults" action={`/api/v1/public/result/submit`} method="post" target="_blank"
              data-ctevt-action={officialResultUrl}
              className="bg-gradient-to-b from-white to-blue-50/20 p-5 md:p-6 space-y-4">
              {/* Hidden fields */}
              {hiddenFields.map((f: any) => <input key={f.name} type="hidden" name={f.name} value={f.value} />)}

              {fields.map((field: any) => {
                const name: string = field.name ?? '';
                const label: string = field.label ?? name.replace(/_/g, ' ');
                const type: string = field.type ?? 'input';
                const inputType: string = field.input_type ?? 'text';
                const required: boolean = field.required ?? false;
                const placeholder: string = field.placeholder ?? '';
                const val = values[name] ?? '';

                return (
                  <div key={name} className="grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)] gap-3 md:gap-6 items-start md:items-center rounded-2xl border border-gray-100 bg-white px-4 py-4 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                    <label className="text-sm md:text-base font-semibold text-gray-700 leading-snug">
                      {label}{required && <span className="text-red-500">*</span>}:
                    </label>
                    <div className="w-full">
                      {type === 'select' ? (
                        <select name={name} required={required} value={val}
                          onChange={e => handleChange(name, e.target.value)}
                          className="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150">
                          {(field.options ?? []).map((opt: any) => (
                            <option key={opt.value} value={opt.value}>{opt.label ?? opt.value}</option>
                          ))}
                        </select>
                      ) : (
                        <input type={inputType} name={name} placeholder={placeholder || (name === 'dob' ? 'YYYY-MM-DD' : '')}
                          required={required} value={val} onChange={e => handleChange(name, e.target.value)}
                          className="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#003D82]/25 focus:border-[#003D82] transition-all duration-150" />
                      )}
                    </div>
                  </div>
                );
              })}

              <div className="pt-2 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-start">
                <button type="submit"
                  className="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-[#003D82] px-6 py-3 text-white font-semibold shadow-sm hover:bg-[#001F4D] transition-colors">
                  {form.submit?.label ?? 'Search'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
