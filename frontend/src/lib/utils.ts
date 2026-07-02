import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatBsDate(date: string): string {
  if (!date) return ''
  const parts = date.split('-')
  if (parts.length === 3) {
    const nepaliMonths = [
      'बैशाख', 'जेठ', 'असार', 'साउन', 'भदौ', 'असोज',
      'कात्तिक', 'मंसिर', 'पुष', 'माघ', 'फागुन', 'चैत',
    ]
    const monthIndex = parseInt(parts[1]) - 1
    const monthName = nepaliMonths[monthIndex] || parts[1]
    return `${parts[0]} ${monthName} ${parts[2]}`
  }
  return date
}

export function getStatusBadgeClass(status: string): string {
  const map: Record<string, string> = {
    draft: 'badge-draft',
    submitted: 'badge-submitted',
    recommended: 'badge-recommended',
    approved: 'badge-approved',
    rejected: 'badge-rejected',
    printed: 'badge-printed',
    completed: 'badge-completed',
  }
  return map[status] || 'badge-draft'
}

export function getStatusLabel(status: string): string {
  const map: Record<string, string> = {
    draft: 'Draft',
    submitted: 'Submitted',
    recommended: 'Recommended',
    approved: 'Approved',
    rejected: 'Rejected',
    printed: 'Printed',
    completed: 'Completed',
  }
  return map[status] || status
}

export function getStatusLabelNe(status: string): string {
  const map: Record<string, string> = {
    draft: 'मस्यौदा',
    submitted: 'पेश गरिएको',
    recommended: 'सिफारिस गरिएको',
    approved: 'स्वीकृत',
    rejected: 'अस्वीकृत',
    printed: 'मुद्रित',
    completed: 'सम्पन्न',
  }
  return map[status] || status
}
