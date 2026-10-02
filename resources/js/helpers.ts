import {DateTime} from "luxon";
import {Dish, DishVariant, Restaurant, Schedule, ScheduleException, ScheduleWeekday} from "@/api";
import { t } from "@/i18n/utils";

export function priceFormatted(price: number | null, currencyCode: string = 'uah'): string | null {
    if (price === null || price === undefined) {
        return null;
    }

    const formattedPrice = Number.isInteger(price) ? price.toString() : price.toFixed(2);

    // Get the currency symbol from translations
    const currencySymbol = t(`currency_symbol.${currencyCode.toLowerCase()}`) || currencyCode;

  return t('format.currency', {price: formattedPrice, currency: currencySymbol});
}

/**
 * Format a weight unit using translations
 *
 * @param unit The weight unit code (g, kg, ml, l, cm, pc)
 * @returns The translated weight unit
 */
export function weightUnitFormatted(unit: string): string {
    // Get the weight unit from translations, default to the unit code if not available
    return t(`weight_unit.${unit.toLowerCase()}`) || unit;
}

/** One size of a dish: the dish itself (id is null) or one of its variants. */
export type DishSize = Pick<DishVariant, 'price' | 'weight' | 'weight_unit' | 'calories' | 'preparation_time'>
  & {id: number | null};

/** Sizes of a dish, the dish itself included, from the cheapest. */
export function getDishSizes(dish: Dish): DishSize[] {
    const base: DishSize = {
        id: null,
        price: dish.price,
        weight: dish.weight,
        weight_unit: dish.weight_unit,
        calories: dish.calories,
        preparation_time: dish.preparation_time,
    };

    return [base, ...(dish.variants ?? [])].sort((a, b) => a.price - b.price);
}

/** Weight of a dish size with its unit, e.g. "300 g", or an empty string. */
export function sizeWeightFormatted(size: DishSize): string {
    if (size.weight === null || size.weight === undefined || size.weight === '') {
        return '';
    }

    return `${size.weight} ${size.weight_unit ? weightUnitFormatted(size.weight_unit) : ''}`.trim();
}

/** Days of the week, from Monday. */
const WEEKDAYS: ScheduleWeekday[] = Object.values(ScheduleWeekday);

/** Hours of a day: closing at or before the opening time means closing after midnight. */
export interface Interval {
  beg_hour: number,
  beg_minute: number,
  end_hour: number,
  end_minute: number,
}

/** A day of the week with its hours (several, with a lunch break), none when it's closed. */
export interface WeekdayHours {
  weekday: ScheduleWeekday,
  intervals: Interval[],
}

/**
 * Hours of each day of the week, from Monday, by the opening time.
 * Days marked as closed in the admin are archived.
 */
export function weeklyHours(schedules: Schedule[]): WeekdayHours[] {
  return WEEKDAYS.map((weekday) => ({
    weekday,
    intervals: schedules
      .filter((schedule) => schedule.weekday === weekday && !schedule.archived)
      .sort((a, b) => (a.beg_hour * 60 + a.beg_minute) - (b.beg_hour * 60 + b.beg_minute)),
  }));
}

export function getCurrentUtcWithOffset(timezoneOffset: number) {
    // Get the current UTC time and apply the timezone offset
    return DateTime.utc()
      .plus({minutes: timezoneOffset});
}

export function time(hour: number, minute: number) {
  const hours = hour < 10 ? '0' + hour : hour.toString();
  const minutes = minute < 10 ? '0' + minute : minute.toString();

  // Get the time format from translations, default to HH:MM if not available
  const format = t('format.time') || 'HH:MM';

  // Replace placeholders with actual values
  return format
      .replace('HH', hours)
      .replace('MM', minutes);
}

/** An opening of the restaurant: hours on a date. */
export interface Opening extends Interval {
  // of the date it opens on
  weekday: ScheduleWeekday,
  closestBegDate: DateTime,
  closestEndDate: DateTime,
}

/** Minutes before closing or opening that count as closing or opening soon. */
export const SOON_MINUTES = 60;

/** How many days ahead the next opening is looked for. */
const DAYS_AHEAD = 14;

/** Special days shown in the schedule: the ones within this many days. */
const SPECIAL_DAYS_AHEAD = 30;

export type ScheduleState = 'open' | 'closing_soon' | 'opens_soon' | 'closed' | 'temporarily_closed';

export interface ScheduleInfo {
  state: ScheduleState,
  // the opening going on now
  active: Opening | null,
  // the opening going on now, or the next one
  relevant: Opening | null,
  week: WeekdayHours[],
  // Weekday of the restaurant's current date
  today: ScheduleWeekday,
  // Closed now, opens later today
  opensToday: boolean,
  // Minutes until closing (when open) or opening (when closed), rounded up
  minutesLeft: number | null,
  // The special day of today
  special: ScheduleException | null,
  // Special days from today on, which override the weekly hours
  specials: ScheduleException[],
  // Temporarily closed till this day (inclusive)
  closedUntil: string | null,
}

/**
 * The special day of the date (a holiday or a short day).
 */
export function specialOn(exceptions: ScheduleException[], date: string): ScheduleException | null {
  return exceptions.find((exception) => exception.starts_on <= date && date <= exception.ends_on) ?? null;
}

/**
 * Openings of the restaurant from yesterday (its hours may go on after midnight) on:
 * the hours of its special days or weekdays, none while it's temporarily closed.
 */
function getOpenings(restaurant: Restaurant, now: DateTime): Opening[] {
  const week = weeklyHours(restaurant.schedules ?? []);
  const openings: Opening[] = [];

  for (let offset = -1; offset <= DAYS_AHEAD; offset++) {
    const day = now.startOf('day').plus({days: offset});
    const date = day.toISODate() as string;

    if (restaurant.closed_until && date <= restaurant.closed_until) {
      continue;
    }

    const special = specialOn(restaurant.exceptions ?? [], date);
    let intervals: Interval[] = week[day.weekday - 1].intervals;

    if (special) {
      intervals = special.is_closed ? [] : [{
        beg_hour: special.beg_hour ?? 0,
        beg_minute: special.beg_minute ?? 0,
        end_hour: special.end_hour ?? 0,
        end_minute: special.end_minute ?? 0,
      }];
    }

    for (const interval of intervals) {
      const beg = day.set({hour: interval.beg_hour, minute: interval.beg_minute});
      let end = day.set({hour: interval.end_hour, minute: interval.end_minute});

      // Closing at or before the opening time means closing after midnight
      if (end <= beg) {
        end = end.plus({days: 1});
      }

      openings.push({
        beg_hour: interval.beg_hour,
        beg_minute: interval.beg_minute,
        end_hour: interval.end_hour,
        end_minute: interval.end_minute,
        // Luxon's weekdays go from 1 (Monday) to 7 (Sunday)
        weekday: WEEKDAYS[day.weekday - 1],
        closestBegDate: beg,
        closestEndDate: end,
      });
    }
  }

  return openings.sort((a, b) => a.closestBegDate.toMillis() - b.closestBegDate.toMillis());
}

export function getScheduleInfo(restaurant: Restaurant): ScheduleInfo {
  // the restaurant's current date and time (in UTC, shifted by its offset)
  const now = getCurrentUtcWithOffset(restaurant.timezone_offset);
  const today = now.toISODate() as string;
  const exceptions = restaurant.exceptions ?? [];

  const openings = getOpenings(restaurant, now);
  const active = openings.find((o) => o.closestBegDate <= now && now < o.closestEndDate) ?? null;
  const relevant = active ?? openings.find((o) => o.closestBegDate > now) ?? null;

  const minutesLeft = relevant
    ? Math.ceil((active ? relevant.closestEndDate : relevant.closestBegDate).diff(now, 'minutes').minutes)
    : null;

  const closedUntil = restaurant.closed_until && today <= restaurant.closed_until
    ? restaurant.closed_until
    : null;

  let state: ScheduleState = active ? 'open' : 'closed';

  if (closedUntil) {
    state = 'temporarily_closed';
  } else if (minutesLeft !== null && minutesLeft <= SOON_MINUTES) {
    state = active ? 'closing_soon' : 'opens_soon';
  }

  const lastSpecialDay = now.plus({days: SPECIAL_DAYS_AHEAD}).toISODate() as string;

  return {
    state,
    active,
    relevant,
    week: weeklyHours(restaurant.schedules ?? []),
    today: WEEKDAYS[now.weekday - 1],
    opensToday: !!relevant && !active && relevant.closestBegDate.hasSame(now, 'day'),
    minutesLeft,
    special: specialOn(exceptions, today),
    specials: exceptions.filter((e) => e.ends_on >= today && e.starts_on <= lastSpecialDay),
    closedUntil,
  };
}
