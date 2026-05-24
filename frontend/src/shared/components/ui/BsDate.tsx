import { useBsDate } from '@hooks/useBsDate';
import { clsx } from 'clsx';

interface BsDateProps {
  /** AD date input — ISO string or Date object */
  date: Date | string | null | undefined;
  /** BS format string. Default: 'Y, F d' → e.g. "2081, Shrawan 15" */
  format?: string;
  className?: string;
  /** Also show the AD date in muted parentheses */
  showAd?: boolean;
}

/**
 * Display-only component that renders an AD date as a BS (Bikram Sambat) date.
 *
 * Usage:
 *   <BsDate date={notice.published_at} />
 *   <BsDate date="2024-07-30" format="Y-m-d" showAd />
 */
export function BsDate({ date, format = 'Y, F d', className, showAd }: BsDateProps) {
  const { formatted } = useBsDate(date, format);

  if (!formatted) return null;

  const adStr =
    showAd && date
      ? (() => {
          const d = typeof date === 'string' ? new Date(date) : date;
          return isNaN(d.getTime())
            ? ''
            : d.toLocaleDateString('en', { year: 'numeric', month: 'short', day: '2-digit' });
        })()
      : '';

  return (
    <time
      dateTime={typeof date === 'string' ? date : date?.toISOString()}
      className={clsx('inline', className)}
    >
      {formatted}
      {showAd && adStr && (
        <span className="ml-1 text-gray-400 text-[0.85em]">({adStr})</span>
      )}
    </time>
  );
}
