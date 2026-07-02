import { useState, useEffect } from 'react'
import { getLocale, setLocale, subscribe, type Locale } from '@/i18n'

export function useLocale() {
  const [locale, setLocaleState] = useState<Locale>(getLocale())

  useEffect(() => {
    return subscribe(() => setLocaleState(getLocale()))
  }, [])

  return {
    locale,
    setLocale: (l: Locale) => setLocale(l),
    toggleLocale: () => setLocale(locale === 'en' ? 'ne' : 'en'),
  }
}
