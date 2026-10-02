import {DateTime} from "luxon";
import {Dish, DishVariant, Restaurant, Schedule, ScheduleWeekday} from "@/api";
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

export function sortSchedules(items: Schedule[]): Schedule[] {
    const schedules = [];

    for (const scheduleWeekdayEnumKey in ScheduleWeekday) {
        const weekday = ScheduleWeekday[scheduleWeekdayEnumKey as keyof typeof ScheduleWeekday];
        const schedule = items.find((s) => s.weekday === weekday);

        if (schedule) {
            schedules.push(schedule);
        }
    }

    return schedules;
}

export function filterAndSortSchedules(items: Schedule[]): Schedule[] {
    return sortSchedules(items.filter((schedule) => !schedule.archived));
}

export function getCurrentUtcWithOffset(timezoneOffset: number) {
    // Get the current UTC time and apply the timezone offset
    return DateTime.utc()
      .plus({minutes: timezoneOffset});
}

export function getNextOccurrence(baseDate: DateTime, weekday: string) {
    // Map weekday name to a number (1 = Monday, 7 = Sunday)
    const weekdayMap = {
        monday: 1, tuesday: 2, wednesday: 3, thursday: 4, friday: 5, saturday: 6, sunday: 7,
    };

    const targetWeekday = weekdayMap[weekday.toLowerCase() as keyof typeof weekdayMap] ?? 0;
    const daysUntilNext = (targetWeekday + 7 - baseDate.weekday) % 7;

    return baseDate.plus({days: daysUntilNext});
}

export function getUpcomingSchedules(now: DateTime, schedules: Schedule[], timezoneOffset: number): (Schedule & ScheduleCalculations)[] {
    const upcomingSchedules: (Schedule & ScheduleCalculations)[] = [];

    schedules.forEach(schedule => {
        // The schedule's weekday this week (today included). A week earlier is checked too,
        // because yesterday's hours may still be going on after midnight.
        const day = getNextOccurrence(now, schedule.weekday);

        for (const date of [day.minus({days: 7}), day, day.plus({days: 7})]) {
            const beg = date
              .set({hour: schedule.beg_hour, minute: schedule.beg_minute, second: 0, millisecond: 0})
              .minus({minutes: timezoneOffset}); // Adjust back to UTC

            let end = date
              .set({hour: schedule.end_hour, minute: schedule.end_minute, second: 0, millisecond: 0})
              .minus({minutes: timezoneOffset}); // Adjust back to UTC

            // Closing at or before the opening time means closing after midnight
            if (end <= beg) {
                end = end.plus({days: 1});
            }

            // The first occurrence that hasn't ended yet
            if (end > now) {
                upcomingSchedules.push({
                    ...schedule,
                    closestBegDate: beg,
                    closestEndDate: end,
                });

                break;
            }
        }
    });

    return upcomingSchedules.sort((a, b) => a.closestBegDate.toMillis() - b.closestBegDate.toMillis());
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

export interface ScheduleCalculations {
  closestBegDate: DateTime,
  closestEndDate: DateTime,
}

/** Minutes before closing or opening that count as closing or opening soon. */
export const SOON_MINUTES = 60;

export type ScheduleState = 'open' | 'closing_soon' | 'opens_soon' | 'closed';

export interface ScheduleInfo {
  state: ScheduleState,
  active: (ScheduleCalculations & Schedule) | null,
  relevant: (ScheduleCalculations & Schedule) | null,
  upcoming: (ScheduleCalculations & Schedule)[],
  // Days with hours, from Monday to Sunday, closed ones (archived) included
  schedules: Schedule[],
  // Weekday of the restaurant's current date
  today: ScheduleWeekday,
  // Closed now, opens later today
  opensToday: boolean,
  // Minutes until closing (when open) or opening (when closed), rounded up
  minutesLeft: number | null,
}

export function getScheduleInfo(restaurant: Restaurant): ScheduleInfo {
  const now = getCurrentUtcWithOffset(restaurant.timezone_offset);
  // Days marked as closed in the admin are archived
  const schedules = filterAndSortSchedules(restaurant.schedules ?? []);

  // Sorted by opening time, so the one that is open now (if any) comes first
  const upcoming = getUpcomingSchedules(now, schedules, 0);
  const relevant = upcoming[0] ?? null;
  const active = relevant && relevant.closestBegDate <= now && now < relevant.closestEndDate
    ? relevant : null;

  const minutesLeft = relevant
    ? Math.ceil((active ? relevant.closestEndDate : relevant.closestBegDate).diff(now, 'minutes').minutes)
    : null;

  let state: ScheduleState = active ? 'open' : 'closed';

  if (minutesLeft !== null && minutesLeft <= SOON_MINUTES) {
    state = active ? 'closing_soon' : 'opens_soon';
  }

  return {
    state,
    active,
    relevant,
    upcoming,
    schedules: sortSchedules(restaurant.schedules ?? []),
    // Luxon's weekdays go from 1 (Monday) to 7 (Sunday)
    today: Object.values(ScheduleWeekday)[now.weekday - 1],
    opensToday: !!relevant && !active && relevant.closestBegDate.hasSame(now, 'day'),
    minutesLeft,
  };
}
