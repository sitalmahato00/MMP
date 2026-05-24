import { useMemo } from 'react';
import { adToBS, formatBS, type BSDate } from '@shared/utils/nepaliDate';

interface UseBsDateResult {
  bs: BSDate | null;
  formatted: string;
}

/**
 * Converts an AD date (string or Date) to a BS date.
 * @param date  AD date — ISO string, Date object, or null/undefined
 * @param format  BS format string (default 'Y, F d')
 */
export function useBsDate(
  date: Date | string | null | undefined,
  format = 'Y, F d'
): UseBsDateResult {
  return useMemo(() => {
    if (!date) return { bs: null, formatted: '' };
    const d = typeof date === 'string' ? new Date(date) : date;
    if (isNaN(d.getTime())) return { bs: null, formatted: '' };
    try {
      const bs = adToBS(d);
      return { bs, formatted: formatBS(bs, format) };
    } catch {
      return { bs: null, formatted: '' };
    }
  }, [date, format]);
}
