import { useState, useRef, useEffect, useCallback } from 'react';
import { clsx } from 'clsx';
import {
  adToBS,
  bsToAD,
  bsToString,
  parseBSString,
  todayBS,
  getDaysInBSMonth,
  isValidBS,
  formatBS,
  BS_MONTHS,
  BS_MONTHS_SHORT,
  BS_DAYS_SHORT,
  type BSDate,
} from '@shared/utils/nepaliDate';
import { ChevronLeft, ChevronRight, Calendar } from 'lucide-react';

// ─── Props ────────────────────────────────────────────────────────────────────
interface BsDatePickerProps {
  /**
   * Current value — either a BS date string 'YYYY-MM-DD'
   * or an AD ISO string (auto-detected by length / format).
   */
  value?: string;
  /** Called with (bsDateString 'YYYY-MM-DD', adDate) on selection. */
  onChange?: (bsDate: string, adDate: Date) => void;
  label?: string;
  error?: string;
  hint?: string;
  placeholder?: string;
  minYear?: number;
  maxYear?: number;
  disabled?: boolean;
  required?: boolean;
  className?: string;
  inputClassName?: string;
  name?: string;
  id?: string;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function isBSString(v: string): boolean {
  // Looks like YYYY-MM-DD where year > 1999 (BS years start at 2000)
  return /^\d{4}-\d{2}-\d{2}$/.test(v) && parseInt(v.slice(0, 4)) >= 2000;
}

function parseValue(value?: string): BSDate | null {
  if (!value) return null;
  if (isBSString(value)) return parseBSString(value);
  // Try as AD ISO
  const d = new Date(value);
  if (!isNaN(d.getTime())) {
    try { return adToBS(d); } catch { return null; }
  }
  return null;
}

// ─── Sub-components ───────────────────────────────────────────────────────────
function NavButton({
  onClick,
  children,
  title,
}: {
  onClick: () => void;
  children: React.ReactNode;
  title?: string;
}) {
  return (
    <button
      type="button"
      title={title}
      onClick={onClick}
      className="flex h-7 w-7 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
    >
      {children}
    </button>
  );
}

// ─── Main component ───────────────────────────────────────────────────────────
export function BsDatePicker({
  value,
  onChange,
  label,
  error,
  hint,
  placeholder = 'YYYY-MM-DD',
  minYear = 2070,
  maxYear = 2090,
  disabled = false,
  required = false,
  className,
  inputClassName,
  name,
  id,
}: BsDatePickerProps) {
  const today = todayBS();
  const selected = parseValue(value);

  // Calendar view state
  const [open, setOpen] = useState(false);
  const [viewYear, setViewYear] = useState(selected?.year ?? today.year);
  const [viewMonth, setViewMonth] = useState(selected?.month ?? today.month);
  const [mode, setMode] = useState<'calendar' | 'month' | 'year'>('calendar');

  const containerRef = useRef<HTMLDivElement>(null);
  const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '_') ?? 'bs-datepicker';

  // Close on outside click
  useEffect(() => {
    function handleClick(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
        setMode('calendar');
      }
    }
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  // Sync view when value changes externally
  useEffect(() => {
    if (selected) {
      setViewYear(selected.year);
      setViewMonth(selected.month);
    }
  }, [value]);

  const selectDate = useCallback(
    (bs: BSDate) => {
      const adDate = bsToAD(bs.year, bs.month, bs.day);
      onChange?.(bsToString(bs), adDate);
      setOpen(false);
      setMode('calendar');
    },
    [onChange]
  );

  const prevMonth = () => {
    if (viewMonth === 1) { setViewYear(y => y - 1); setViewMonth(12); }
    else setViewMonth(m => m - 1);
  };
  const nextMonth = () => {
    if (viewMonth === 12) { setViewYear(y => y + 1); setViewMonth(1); }
    else setViewMonth(m => m + 1);
  };

  // Build calendar grid
  const daysInMonth = getDaysInBSMonth(viewYear, viewMonth);
  const firstAD = bsToAD(viewYear, viewMonth, 1);
  const startDow = firstAD.getDay(); // 0=Sun

  const cells: (number | null)[] = [
    ...Array(startDow).fill(null),
    ...Array.from({ length: daysInMonth }, (_, i) => i + 1),
  ];
  // Pad to complete last row
  while (cells.length % 7 !== 0) cells.push(null);

  const displayValue = selected ? formatBS(selected, 'Y-m-d') : '';

  return (
    <div className={clsx('relative w-full', className)} ref={containerRef}>
      {/* Label */}
      {label && (
        <label htmlFor={inputId} className="form-label">
          {label}
          {required && <span className="ml-0.5 text-red-500">*</span>}
        </label>
      )}

      {/* Input trigger */}
      <div className="relative">
        <input
          id={inputId}
          name={name}
          type="text"
          readOnly
          value={displayValue}
          placeholder={placeholder}
          disabled={disabled}
          onClick={() => !disabled && setOpen(o => !o)}
          className={clsx(
            'form-input cursor-pointer pr-9',
            error && 'border-red-500 focus:border-red-500 focus:ring-red-500',
            disabled && 'cursor-not-allowed opacity-60',
            inputClassName
          )}
        />
        <Calendar
          className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
        />
      </div>

      {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
      {hint && !error && <p className="mt-1 text-xs text-gray-500">{hint}</p>}

      {/* Dropdown panel */}
      {open && (
        <div className="absolute left-0 top-full z-50 mt-1 w-72 rounded-xl border border-slate-200 bg-white shadow-xl">

          {/* ── Calendar mode ── */}
          {mode === 'calendar' && (
            <>
              {/* Header */}
              <div className="flex items-center justify-between border-b border-slate-100 px-3 py-2.5">
                <NavButton onClick={prevMonth} title="Previous month">
                  <ChevronLeft className="h-4 w-4" />
                </NavButton>
                <button
                  type="button"
                  onClick={() => setMode('month')}
                  className="text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors"
                >
                  {BS_MONTHS[viewMonth - 1]} {viewYear}
                </button>
                <NavButton onClick={nextMonth} title="Next month">
                  <ChevronRight className="h-4 w-4" />
                </NavButton>
              </div>

              {/* Day headers */}
              <div className="grid grid-cols-7 border-b border-slate-100 px-2 py-1.5">
                {BS_DAYS_SHORT.map(d => (
                  <div key={d} className="text-center text-[10px] font-bold uppercase text-slate-400">
                    {d.slice(0, 2)}
                  </div>
                ))}
              </div>

              {/* Day cells */}
              <div className="grid grid-cols-7 gap-y-0.5 px-2 py-2">
                {cells.map((day, i) => {
                  if (!day) return <div key={`e-${i}`} />;

                  const isToday =
                    viewYear === today.year &&
                    viewMonth === today.month &&
                    day === today.day;
                  const isSelected =
                    selected &&
                    viewYear === selected.year &&
                    viewMonth === selected.month &&
                    day === selected.day;
                  const valid = isValidBS(viewYear, viewMonth, day);

                  return (
                    <button
                      key={day}
                      type="button"
                      disabled={!valid}
                      onClick={() => selectDate({ year: viewYear, month: viewMonth, day })}
                      className={clsx(
                        'mx-auto flex h-8 w-8 items-center justify-center rounded-full text-sm transition-colors',
                        isSelected
                          ? 'bg-blue-600 font-bold text-white'
                          : isToday
                          ? 'ring-2 ring-blue-400 font-semibold text-blue-700 hover:bg-blue-50'
                          : 'text-slate-700 hover:bg-slate-100'
                      )}
                    >
                      {day}
                    </button>
                  );
                })}
              </div>

              {/* Footer */}
              <div className="border-t border-slate-100 px-3 py-2 flex justify-between items-center">
                <button
                  type="button"
                  onClick={() => selectDate(today)}
                  className="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors"
                >
                  Today ({BS_MONTHS_SHORT[today.month - 1]} {today.day})
                </button>
                {selected && (
                  <button
                    type="button"
                    onClick={() => { onChange?.('', new Date()); setOpen(false); }}
                    className="text-xs text-slate-400 hover:text-red-500 transition-colors"
                  >
                    Clear
                  </button>
                )}
              </div>
            </>
          )}

          {/* ── Month picker mode ── */}
          {mode === 'month' && (
            <>
              <div className="flex items-center justify-between border-b border-slate-100 px-3 py-2.5">
                <button
                  type="button"
                  onClick={() => setMode('year')}
                  className="text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors"
                >
                  {viewYear}
                </button>
                <button
                  type="button"
                  onClick={() => setMode('calendar')}
                  className="text-xs text-slate-400 hover:text-slate-700"
                >
                  ✕
                </button>
              </div>
              <div className="grid grid-cols-3 gap-2 p-3">
                {BS_MONTHS.map((m, i) => (
                  <button
                    key={m}
                    type="button"
                    onClick={() => { setViewMonth(i + 1); setMode('calendar'); }}
                    className={clsx(
                      'rounded-lg py-2 text-xs font-medium transition-colors',
                      viewMonth === i + 1
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-700 hover:bg-slate-100'
                    )}
                  >
                    {BS_MONTHS_SHORT[i]}
                  </button>
                ))}
              </div>
            </>
          )}

          {/* ── Year picker mode ── */}
          {mode === 'year' && (
            <>
              <div className="flex items-center justify-between border-b border-slate-100 px-3 py-2.5">
                <span className="text-sm font-semibold text-slate-800">Select Year</span>
                <button
                  type="button"
                  onClick={() => setMode('calendar')}
                  className="text-xs text-slate-400 hover:text-slate-700"
                >
                  ✕
                </button>
              </div>
              <div className="grid grid-cols-4 gap-1.5 max-h-52 overflow-y-auto p-3">
                {Array.from(
                  { length: maxYear - minYear + 1 },
                  (_, i) => minYear + i
                ).map(y => (
                  <button
                    key={y}
                    type="button"
                    onClick={() => { setViewYear(y); setMode('month'); }}
                    className={clsx(
                      'rounded-lg py-1.5 text-xs font-medium transition-colors',
                      viewYear === y
                        ? 'bg-blue-600 text-white'
                        : 'text-slate-700 hover:bg-slate-100'
                    )}
                  >
                    {y}
                  </button>
                ))}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
}
