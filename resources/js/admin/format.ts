import {DateTime} from 'luxon'

/**
 * Dates, times and lists in the admin's language. English is British: "Saturday, 3 October", 24-hour times.
 */
export function intlLocale(locale: string): string {
  return locale === 'en' ? 'en-GB' : locale
}

/** "14:32" */
export function formatTime(date: DateTime): string {
  return date.toFormat('HH:mm')
}

/** "14:32" of an hour and minute. */
export function formatHour(hour: number, minute: number): string {
  return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
}

/** "Saturday, 3 October" */
export function formatLongDate(date: DateTime, locale: string): string {
  return date.setLocale(intlLocale(locale)).toFormat('cccc, d MMMM')
}

/** "15 October" (with the year, when it's not this one) */
export function formatDayMonth(date: DateTime, locale: string, now: DateTime = DateTime.now()): string {
  return date.setLocale(intlLocale(locale)).toLocaleString({
    day: 'numeric',
    month: 'long',
    ...(date.year !== now.year ? {year: 'numeric'} : {}),
  })
}

/** "Tue 6 Oct, 08:00" (with the year, when it's not this one) */
export function formatDateTime(date: DateTime, locale: string, now: DateTime = DateTime.now()): string {
  const day = date.setLocale(intlLocale(locale)).toLocaleString({
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    ...(date.year !== now.year ? {year: 'numeric'} : {}),
  }).replace(',', '')

  return `${day}, ${formatTime(date)}`
}

/** "Wed" */
export function formatWeekdayShort(date: DateTime, locale: string): string {
  return date.setLocale(intlLocale(locale)).toLocaleString({weekday: 'short'})
}

/** "in 3 days", "in 2 hours" */
export function formatRelative(date: DateTime, locale: string, now: DateTime = DateTime.now()): string {
  return date.setLocale(intlLocale(locale)).toRelative({base: now}) ?? ''
}

/** "a, b and c" */
export function formatList(items: string[], locale: string): string {
  return new Intl.ListFormat(intlLocale(locale), {style: 'long', type: 'conjunction'}).format(items)
}
