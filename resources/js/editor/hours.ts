import {DateTime} from 'luxon'
import type {EditorInterval, EditorRestaurant, EditorWeekdays} from '@/api'

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
