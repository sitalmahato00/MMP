import { getStatusBadgeClass, getStatusLabel, getStatusLabelNe } from '@/lib/utils'
import { getLocale } from '@/i18n'

interface StatusBadgeProps {
  status: string
}

export function StatusBadge({ status }: StatusBadgeProps) {
  const locale = getLocale()
  return (
    <span className={getStatusBadgeClass(status)}>
      {locale === 'ne' ? getStatusLabelNe(status) : getStatusLabel(status)}
    </span>
  )
}
