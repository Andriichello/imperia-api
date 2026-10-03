import {DateTime} from 'luxon'
import type {EditorDashboard, EditorInterval, Restaurant, Schedule, ScheduleException} from '@/api'
import {getScheduleInfo, Interval, ScheduleInfo, specialOn, WeekdayHours} from '@/helpers'

/**
 * Whether the restaurant is open right now, worked out like its public page does it
 * (`getScheduleInfo()`), from the dashboard's hours.
 */
export type NowState = 'open' | 'closing_soon' | 'opens_soon' | 'closed' | 'holiday' | 'temporarily_closed'

/** A part of a day, in minutes from midnight. */
export interface DaySpan {
  beg: number
  end: number
}

export interface RightNow {
  state: NowState
  info: ScheduleInfo
  // the restaurant's current date and time (in its time zone)
  now: DateTime
  // today's own hours (none, when it's closed)
  hours: Interval[]
  // today's openings on a 24-hour bar, yesterday's ones going on after midnight included
  today: DaySpan[]
  // weekly hours, from Monday
  week: WeekdayHours[]
}

const DAY = 24 * 60

/**
 * The dashboard's hours as the public page has them.
 */
export function publicHours(dashboard: EditorDashboard): Restaurant {
  const schedules = Object.entries(dashboard.weekdays).flatMap(([weekday, intervals]) => (intervals as EditorInterval[])
    .map((interval) => ({...interval, weekday, archived: false}) as unknown as Schedule))

  return {
    timezone: dashboard.timezone,
    // minutes from UTC now
    timezone_offset: DateTime.now().setZone(dashboard.timezone).offset,
    schedules,
    exceptions: dashboard.exceptions as unknown as ScheduleException[],
    closed_until: dashboard.closed_until,
  } as unknown as Restaurant
}

/**
 * Hours of the date: of its special day, if there's one, or of its weekday (none, while the
 * restaurant is temporarily closed).
 */
function intervalsOn(restaurant: Restaurant, info: ScheduleInfo, day: DateTime): Interval[] {
  const iso = day.toISODate() as string

  if (restaurant.closed_until && iso <= restaurant.closed_until) {
    return []
  }

  const special = specialOn(restaurant.exceptions ?? [], iso)

  if (special) {
    return special.is_closed ? [] : [{
      beg_hour: special.beg_hour ?? 0,
      beg_minute: special.beg_minute ?? 0,
      end_hour: special.end_hour ?? 0,
      end_minute: special.end_minute ?? 0,
    }]
  }

  return info.week[day.weekday - 1].intervals
}

/**
 * Openings of the date, in minutes from its midnight: its own ones (till midnight, when they
 * go on after it) and yesterday's ones, which go on after midnight.
 */
function spansOn(restaurant: Restaurant, info: ScheduleInfo, date: DateTime): DaySpan[] {
  const spans: DaySpan[] = []

  for (const interval of intervalsOn(restaurant, info, date.minus({days: 1}))) {
    const beg = interval.beg_hour * 60 + interval.beg_minute
    const end = interval.end_hour * 60 + interval.end_minute

    if (end <= beg && end > 0) {
      spans.push({beg: 0, end})
    }
  }

  for (const interval of intervalsOn(restaurant, info, date)) {
    const beg = interval.beg_hour * 60 + interval.beg_minute
    const end = interval.end_hour * 60 + interval.end_minute

    spans.push({beg, end: end <= beg ? DAY : end})
  }

  return spans
}

export function rightNow(dashboard: EditorDashboard): RightNow {
  const restaurant = publicHours(dashboard)
  const info = getScheduleInfo(restaurant)
  // the restaurant's date and time (UTC shifted by its offset, like the public page)
  const now = DateTime.utc().plus({minutes: restaurant.timezone_offset})

  let state: NowState = info.state

  // closed for its special day (not for the weekly hours)
  if (state !== 'temporarily_closed' && !info.active && info.special?.is_closed) {
    state = 'holiday'
  }

  return {
    state,
    info,
    now,
    hours: intervalsOn(restaurant, info, now),
    today: spansOn(restaurant, info, now),
    week: info.week,
  }
}
