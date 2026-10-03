import {DateTime} from 'luxon'
import type {EditorInterval, EditorRestaurant, EditorWeekdays} from '@/api'
import type {HoursDraft} from '@/editor/drafts'

export type OpenState = 'open' | 'closed' | 'temporarily_closed'

/**
 * Intervals of the day: of its special day, if there's one, or of its weekday.
 */
function intervalsOn(restaurant: EditorRestaurant, day: DateTime): EditorInterval[] {
  const date = day.toISODate() as string
  const exception = (restaurant.exceptions ?? [])
    .find((e) => e.starts_on <= date && date <= (e.ends_on || e.starts_on))

  if (exception) {
    return exception.is_closed ? [] : [{
      beg_hour: exception.beg_hour ?? 0,
      beg_minute: exception.beg_minute ?? 0,
      end_hour: exception.end_hour ?? 0,
      end_minute: exception.end_minute ?? 0,
    }]
  }

  const weekday = day.setLocale('en').weekdayLong?.toLowerCase() as keyof EditorWeekdays

  return restaurant.weekdays?.[weekday] ?? []
}

/**
 * Whether the restaurant is open now, in its time zone.
 */
export function openState(restaurant: EditorRestaurant, now: DateTime = DateTime.now()): OpenState {
  const local = now.setZone(restaurant.timezone || 'Europe/Kyiv')

  if (restaurant.closed_until && (local.toISODate() as string) <= restaurant.closed_until) {
    return 'temporarily_closed'
  }

  // yesterday's hours may still go on after midnight
  for (const offset of [-1, 0]) {
    const day = local.plus({days: offset}).startOf('day')

    for (const interval of intervalsOn(restaurant, day)) {
      const beg = day.set({hour: interval.beg_hour, minute: interval.beg_minute})
      let end = day.set({hour: interval.end_hour, minute: interval.end_minute})

      // closing at or before the opening time means closing after midnight
      if (end <= beg) {
        end = end.plus({days: 1})
      }

      if (beg <= local && local < end) {
        return 'open'
      }
    }
  }

  return 'closed'
}

/** What's wrong with hours (see `editor.hours.*` for the messages). */
export type HoursProblem = 'same_time' | 'overlap' | 'pick_date' | 'end_before_start'

const minutes = (hour: number, minute: number) => hour * 60 + minute

/**
 * What's wrong with the hours of a day: an interval closes when it opens, or two overlap
 * (the same check as the server's).
 */
export function intervalsProblem(intervals: EditorInterval[]): HoursProblem | null {
  const ranges: number[][] = []

  for (const interval of intervals) {
    const beg = minutes(interval.beg_hour, interval.beg_minute)
    const end = minutes(interval.end_hour, interval.end_minute)

    if (beg === end) {
      return 'same_time'
    }

    const range = [beg, end < beg ? end + 24 * 60 : end]

    if (ranges.some((other) => range[0] < other[1] && other[0] < range[1])) {
      return 'overlap'
    }

    ranges.push(range)
  }

  return null
}

/** What's wrong with a special day: no date, or its hours. */
export function specialDayProblem(day: EditorInterval & { starts_on: string, ends_on: string, is_closed: boolean }): HoursProblem | null {
  if (!day.starts_on) {
    return 'pick_date'
  }

  if (day.ends_on && day.ends_on < day.starts_on) {
    return 'end_before_start'
  }

  return day.is_closed ? null : intervalsProblem([day])
}

/** Whether the hours can be saved as they are. */
export function hoursValid(hours: HoursDraft): boolean {
  return !(hours.closed && !hours.closed_until)
    && Object.values(hours.weekdays).every((intervals) => !intervalsProblem(intervals))
    && hours.exceptions.every((day) => !specialDayProblem(day))
}
