import en from './en'
import ne from './ne'

export type Locale = 'en' | 'ne'

let currentLocale: Locale = (localStorage.getItem('locale') as Locale) || 'ne'
const listeners: Set<() => void> = new Set()

export function getLocale(): Locale {
  return currentLocale
}

export function setLocale(locale: Locale): void {
  currentLocale = locale
  localStorage.setItem('locale', locale)
  listeners.forEach((fn) => fn())
}

export function subscribe(fn: () => void): () => void {
  listeners.add(fn)
  return () => listeners.delete(fn)
}

export function t(key: string, params?: Record<string, string | number>): string {
  const strings = currentLocale === 'ne' ? ne : en
  let value = strings[key] || en[key] || key
  if (params) {
    Object.entries(params).forEach(([k, v]) => {
      value = value.replace(`{${k}}`, String(v))
    })
  }
  return value
}

export function getTranslations(): Record<string, string> {
  return currentLocale === 'ne' ? ne : en
}
