/**
 * Nepali BS (Bikram Sambat) ↔ AD date conversion utility.
 * No external dependencies — pure TypeScript.
 *
 * Supported range: BS 2000–2090 (AD 1943–2033)
 */

// ─── Month names ──────────────────────────────────────────────────────────────
export const BS_MONTHS = [
  'Baisakh', 'Jestha', 'Ashadh', 'Shrawan',
  'Bhadra',  'Ashwin', 'Kartik', 'Mangsir',
  'Poush',   'Magh',   'Falgun', 'Chaitra',
] as const;

export const BS_MONTHS_SHORT = [
  'Bai','Jes','Ash','Shr','Bha','Asw','Kar','Man','Pou','Mag','Fal','Cha',
] as const;

export const BS_DAYS = [
  'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday',
] as const;

export const BS_DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as const;

// ─── Calendar data ────────────────────────────────────────────────────────────
// Each row = days per month for that BS year (index 0 = BS 2000)
const BS_CALENDAR_DATA: number[][] = [
  [30,32,31,32,31,30,30,30,29,30,29,31], // 2000
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2001
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2002
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2003
  [30,32,31,32,31,30,30,30,29,30,29,31], // 2004
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2005
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2006
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2007
  [31,31,31,32,31,31,29,30,30,29,29,31], // 2008
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2009
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2010
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2011
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2012
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2013
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2014
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2015
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2016
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2017
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2018
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2019
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2020
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2021
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2022
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2023
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2024
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2025
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2026
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2027
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2028
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2029
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2030
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2031
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2032
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2033
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2034
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2035
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2036
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2037
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2038
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2039
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2040
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2041
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2042
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2043
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2044
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2045
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2046
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2047
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2048
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2049
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2050
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2051
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2052
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2053
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2054
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2055
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2056
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2057
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2058
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2059
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2060
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2061
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2062
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2063
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2064
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2065
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2066
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2067
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2068
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2069
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2070
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2071
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2072
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2073
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2074
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2075
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2076
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2077
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2078
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2079
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2080
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2081
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2082
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2083
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2084
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2085
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2086
  [31,32,31,32,31,30,30,30,29,29,30,31], // 2087
  [31,31,31,32,31,31,29,30,30,29,30,30], // 2088
  [31,31,32,31,31,31,30,29,30,29,30,30], // 2089
  [31,31,32,32,31,30,30,29,30,29,30,30], // 2090
];

const BS_START_YEAR = 2000;
// BS 2000 Baisakh 1 = AD 1943 April 14
const AD_EPOCH = new Date(1943, 3, 14); // month is 0-indexed

// ─── Types ────────────────────────────────────────────────────────────────────
export interface BSDate {
  year: number;
  month: number; // 1–12
  day: number;   // 1–32
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function daysBetween(a: Date, b: Date): number {
  const msPerDay = 86_400_000;
  const utcA = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
  const utcB = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
  return Math.round((utcB - utcA) / msPerDay);
}

// ─── Core conversions ─────────────────────────────────────────────────────────

/** Convert an AD Date to a BS date object. */
export function adToBS(date: Date): BSDate {
  let totalDays = daysBetween(AD_EPOCH, date);

  if (totalDays < 0) throw new RangeError('Date is before BS 2000 (AD 1943-04-14)');

  let year = BS_START_YEAR;
  let month = 1;
  let day = 1;

  // Walk through years
  for (let y = 0; y < BS_CALENDAR_DATA.length; y++) {
    const daysInYear = BS_CALENDAR_DATA[y].reduce((s, d) => s + d, 0);
    if (totalDays < daysInYear) {
      year = BS_START_YEAR + y;
      // Walk through months
      for (let m = 0; m < 12; m++) {
        const daysInMonth = BS_CALENDAR_DATA[y][m];
        if (totalDays < daysInMonth) {
          month = m + 1;
          day = totalDays + 1;
          return { year, month, day };
        }
        totalDays -= daysInMonth;
      }
    }
    totalDays -= daysInYear;
  }

  throw new RangeError('Date is beyond BS 2090');
}

/** Convert a BS date to an AD Date. */
export function bsToAD(year: number, month: number, day: number): Date {
  const yIdx = year - BS_START_YEAR;
  if (yIdx < 0 || yIdx >= BS_CALENDAR_DATA.length) {
    throw new RangeError(`BS year ${year} is out of supported range (2000–2090)`);
  }

  let totalDays = 0;

  // Sum all days in complete years before this one
  for (let y = 0; y < yIdx; y++) {
    totalDays += BS_CALENDAR_DATA[y].reduce((s, d) => s + d, 0);
  }

  // Sum complete months in this year
  for (let m = 0; m < month - 1; m++) {
    totalDays += BS_CALENDAR_DATA[yIdx][m];
  }

  // Add remaining days (day is 1-based)
  totalDays += day - 1;

  const result = new Date(AD_EPOCH);
  result.setDate(result.getDate() + totalDays);
  return result;
}

/** Returns today's date in BS. */
export function todayBS(): BSDate {
  return adToBS(new Date());
}

/** Returns the number of days in a given BS month. */
export function getDaysInBSMonth(year: number, month: number): number {
  const yIdx = year - BS_START_YEAR;
  if (yIdx < 0 || yIdx >= BS_CALENDAR_DATA.length) return 30;
  return BS_CALENDAR_DATA[yIdx][month - 1];
}

/** Validates a BS date. */
export function isValidBS(year: number, month: number, day: number): boolean {
  if (month < 1 || month > 12 || day < 1) return false;
  const yIdx = year - BS_START_YEAR;
  if (yIdx < 0 || yIdx >= BS_CALENDAR_DATA.length) return false;
  return day <= BS_CALENDAR_DATA[yIdx][month - 1];
}

// ─── Formatting ───────────────────────────────────────────────────────────────
/**
 * Format a BS date using PHP-style tokens:
 *   Y  = 4-digit year          (2081)
 *   m  = 2-digit month         (04)
 *   n  = month without padding (4)
 *   d  = 2-digit day           (05)
 *   j  = day without padding   (5)
 *   F  = full month name       (Shrawan)
 *   M  = short month name      (Shr)
 *   D  = short day name        (Mon)
 *   l  = full day name         (Monday)
 */
export function formatBS(bs: BSDate, format = 'Y-m-d'): string {
  const adDate = bsToAD(bs.year, bs.month, bs.day);
  const dow = adDate.getDay(); // 0=Sun

  return format
    .replace(/Y/g, String(bs.year))
    .replace(/m/g, String(bs.month).padStart(2, '0'))
    .replace(/n/g, String(bs.month))
    .replace(/d/g, String(bs.day).padStart(2, '0'))
    .replace(/j/g, String(bs.day))
    .replace(/F/g, BS_MONTHS[bs.month - 1])
    .replace(/M/g, BS_MONTHS_SHORT[bs.month - 1])
    .replace(/D/g, BS_DAYS_SHORT[dow])
    .replace(/l/g, BS_DAYS[dow]);
}

/** Parse a BS date string 'YYYY-MM-DD' into a BSDate object. */
export function parseBSString(value: string): BSDate | null {
  const parts = value.split('-').map(Number);
  if (parts.length !== 3) return null;
  const [year, month, day] = parts;
  if (!isValidBS(year, month, day)) return null;
  return { year, month, day };
}

/** Format a BSDate as 'YYYY-MM-DD' string. */
export function bsToString(bs: BSDate): string {
  return `${bs.year}-${String(bs.month).padStart(2, '0')}-${String(bs.day).padStart(2, '0')}`;
}
