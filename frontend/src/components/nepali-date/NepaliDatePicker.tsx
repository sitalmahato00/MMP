import { useState, useRef, useEffect, useMemo } from 'react'
import NepaliDate from 'nepali-date-converter'
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-react'

interface NepaliDatePickerProps {
  value: string
  onChange: (dateBs: string) => void
  label?: string
  error?: string
  placeholder?: string
  disabled?: boolean
}

const monthsEn = [
  'Baisakh', 'Jestha', 'Ashad', 'Shrawan', 'Bhadra', 'Ashwin',
  'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra',
]

const monthsNe = [
  'बैशाख', 'जेठ', 'असार', 'साउन', 'भदौ', 'असोज',
  'कात्तिक', 'मंसिर', 'पुष', 'माघ', 'फागुन', 'चैत',
]

const weekDaysNe = ['आ', 'सो', 'म', 'बु', 'बि', 'शु', 'श']

function getDaysInMonth(year: number, month: number): number {
  let day = 32
  while (day > 1) {
    day--
    try {
      const d = new NepaliDate(year, month, day)
      if (d.getMonth() === month && d.getYear() === year) return day
    } catch {
      continue
    }
  }
  return 30
}

export function NepaliDatePicker({ value, onChange, label, error, placeholder, disabled }: NepaliDatePickerProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [viewYear, setViewYear] = useState(2080)
  const [viewMonth, setViewMonth] = useState(0)
  const [locale, setLocale] = useState<'ne' | 'en'>('ne')
  const ref = useRef<HTMLDivElement>(null)

  useEffect(() => {
    if (value) {
      try {
        const parts = value.split('-')
        setViewYear(parseInt(parts[0]))
        setViewMonth(parseInt(parts[1]) - 1)
      } catch { /* ignore */ }
    }
  }, [value])

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setIsOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const daysInMonth = useMemo(() => getDaysInMonth(viewYear, viewMonth), [viewYear, viewMonth])

  const calendarDays = useMemo(() => {
    try {
      const firstDay = new NepaliDate(viewYear, viewMonth, 1).getDay()
      const days: ({ day: number; isToday: boolean; dateBs: string } | null)[] = []
      for (let i = 0; i < firstDay; i++) days.push(null)
      for (let d = 1; d <= daysInMonth; d++) {
        const dateBs = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
        days.push({ day: d, isToday: dateBs === value, dateBs })
      }
      return days
    } catch {
      return []
    }
  }, [viewYear, viewMonth, daysInMonth, value])

  const handleSelect = (dateBs: string) => {
    onChange(dateBs)
    setIsOpen(false)
  }

  const prevMonth = () => {
    if (viewMonth === 0) { setViewYear((y) => y - 1); setViewMonth(11) }
    else setViewMonth((m) => m - 1)
  }

  const nextMonth = () => {
    if (viewMonth === 11) { setViewYear((y) => y + 1); setViewMonth(0) }
    else setViewMonth((m) => m + 1)
  }

  const months = locale === 'ne' ? monthsNe : monthsEn
  const days = locale === 'ne' ? weekDaysNe : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

  const formatDisplay = (bs: string) => {
    if (!bs) return ''
    const p = bs.split('-')
    const mi = parseInt(p[1]) - 1
    return `${p[0]} ${months[mi]} ${parseInt(p[2])}`
  }

  return (
    <div ref={ref} className="relative">
      {label && <label className="form-label">{label}</label>}
      <div
        className={`form-input flex items-center gap-2 cursor-pointer ${disabled ? 'opacity-50' : ''}`}
        onClick={() => !disabled && setIsOpen(!isOpen)}
      >
        <CalendarDays className="h-4 w-4 text-muted shrink-0" />
        <span className={value ? '' : 'text-muted'}>
          {value ? formatDisplay(value) : (placeholder || 'Select BS date')}
        </span>
      </div>
      {error && <p className="form-error">{error}</p>}

      {isOpen && (
        <div className="absolute z-50 mt-1 w-72 rounded-lg border bg-white p-3 shadow-lg">
          <div className="mb-2 flex items-center justify-between">
            <button type="button" onClick={prevMonth} className="rounded p-1 hover:bg-gray-100">
              <ChevronLeft className="h-4 w-4" />
            </button>
            <button
              type="button"
              onClick={() => setLocale((l) => (l === 'ne' ? 'en' : 'ne'))}
              className="text-sm font-semibold hover:text-primary"
            >
              {months[viewMonth]} {viewYear}
            </button>
            <button type="button" onClick={nextMonth} className="rounded p-1 hover:bg-gray-100">
              <ChevronRight className="h-4 w-4" />
            </button>
          </div>

          <div className="mb-1 grid grid-cols-7 text-center text-xs font-medium text-muted">
            {days.map((d) => (
              <div key={d} className="py-1">{d}</div>
            ))}
          </div>

          <div className="grid grid-cols-7 text-center text-sm">
            {calendarDays.map((day, i) => (
              <div key={i} className="py-0.5">
                {day && (
                  <button
                    type="button"
                    onClick={() => handleSelect(day.dateBs)}
                    className={`h-8 w-8 rounded-full text-sm transition-colors ${
                      day.isToday ? 'bg-primary text-white' : 'hover:bg-gray-100'
                    }`}
                  >
                    {day.day}
                  </button>
                )}
              </div>
            ))}
          </div>

          <div className="mt-2 border-t pt-2">
            <div className="flex gap-1 text-xs">
              <button
                type="button"
                onClick={() => {
                  const today = NepaliDate.now()
                  const bs = today.format('YYYY-MM-DD')
                  onChange(bs)
                  setIsOpen(false)
                }}
                className="rounded bg-gray-100 px-2 py-1 hover:bg-gray-200"
              >
                {locale === 'ne' ? 'आज' : 'Today'}
              </button>
              <button
                type="button"
                onClick={() => setIsOpen(false)}
                className="rounded bg-gray-100 px-2 py-1 hover:bg-gray-200"
              >
                {locale === 'ne' ? 'बन्द' : 'Close'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
