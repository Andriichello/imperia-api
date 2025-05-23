import {DateTime} from "luxon";
import {Restaurant, Schedule, ScheduleWeekday} from "@/api";
import { t } from "@/i18n/utils";

export function authHeaders(token: string, type = 'bearer'): object {
    if (type === 'bearer') {
        return { authorization: 'Bearer ' + token };
    }

    return {};
}

export function jsonHeaders(): object {
    return { 'content-type': 'application/json' };
}

export function randomString(length = 4) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    const charLength = chars.length;

    let result = '';

    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * charLength));
    }

    return result;
}

export function sameDay(d1: Date, d2: Date): boolean {
    return d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate();
}

export function currentTimezone() {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
}

export function dateTimezone(date: Date): string {
    return date.toString().match(/([A-Z]+[+-][0-9]+.*)/)![1];
}

export function dateFormatted(date: Date | string | null): string | null {
    if (date === null) {
        return null;
    }

    date = new Date(date);

    const year = date.getFullYear();
    const month = (1 + date.getMonth()).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');

    // Get the date format from translations, default to DD/MM/YYYY if not available
    const format = t('format.date') || 'DD/MM/YYYY';

    // Replace placeholders with actual values
    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year.toString());
}

export function timeFormatted(date: Date | string | null): string | null {
    if (date === null) {
        return null;
    }

    date = new Date(date);

    const hours = date.getUTCHours().toString().padStart(2, '0');
    const minutes = date.getUTCMinutes().toString().padStart(2, '0');

    // Get the time format from translations, default to HH:MM if not available
    const format = t('format.time') || 'HH:MM';

    // Replace placeholders with actual values
    return format
        .replace('HH', hours)
        .replace('MM', minutes);
}

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

export function sortByPopularity<T extends { popularity?: number }>(items: T[]): T[] {
    return items.sort((a, b) => (b?.popularity ?? 0) - (a?.popularity ?? 0));
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
    const filtered = items.filter((schedule) => !schedule.archived);
    const schedules = [];

    for (const scheduleWeekdayEnumKey in ScheduleWeekday) {
        const weekday = ScheduleWeekday[scheduleWeekdayEnumKey as keyof typeof ScheduleWeekday];
        const schedule = filtered.find((s) => s.weekday === weekday);

        if (schedule) {
            schedules.push(schedule);
        }
    }

    return schedules;
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

    const targetWeekday = weekdayMap[weekday.toLowerCase() as keyof typeof weekdayMap] ?? [];
    const daysUntilNext = (targetWeekday + 7 - baseDate.weekday) % 7;

    return baseDate.plus({days: daysUntilNext});
}

export function getUpcomingSchedules(now: DateTime, schedules: Schedule[], timezoneOffset: number): (Schedule & ScheduleCalculations)[] {
    const upcomingSchedules: (Schedule & ScheduleCalculations)[] = [];

    schedules.forEach(schedule => {
        // Find the next occurrence of the schedule's weekday
        let nextOccurrenceBeg = getNextOccurrence(now, schedule.weekday)
          .set({hour: schedule.beg_hour, minute: schedule.beg_minute, second: 0, millisecond: 0})
          .minus({minutes: timezoneOffset}); // Adjust back to UTC

        let nextOccurrenceEnd = getNextOccurrence(now, schedule.weekday)
          .set({hour: schedule.end_hour, minute: schedule.end_minute, second: 0, millisecond: 0})
          .minus({minutes: timezoneOffset}); // Adjust back to UTC

        // Handle cross-date schedules (end time is before start time)
        if (nextOccurrenceEnd < nextOccurrenceBeg) {
            nextOccurrenceEnd = nextOccurrenceEnd.plus({days: 1});
        }


        if (nextOccurrenceBeg.toMillis() < now.toMillis()) {
            if (nextOccurrenceEnd.toMillis() > now.toMillis()) {
                upcomingSchedules.push({
                    ...schedule,
                    closestBegDate: nextOccurrenceBeg,
                    closestEndDate: nextOccurrenceEnd,
                });

                nextOccurrenceBeg = nextOccurrenceBeg.plus({days: 7});
                nextOccurrenceEnd = nextOccurrenceEnd.plus({days: 7});
            } else {
                nextOccurrenceBeg = nextOccurrenceBeg.plus({days: 7});
                nextOccurrenceEnd = nextOccurrenceEnd.plus({days: 7});
            }
        }

        upcomingSchedules.push({
            ...schedule,
            closestBegDate: nextOccurrenceBeg,
            closestEndDate: nextOccurrenceEnd,
        });
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

export interface ScheduleInfo {
  status: 'Open' | 'Closed',
  active: (ScheduleCalculations & Schedule) | null,
  relevant: ScheduleCalculations & Schedule,
  upcoming: (ScheduleCalculations & Schedule)[],
  schedules: Schedule[],
  timeBeforeOrUntil: string | '-',
}

export function getScheduleInfo(restaurant: Restaurant): ScheduleInfo {
  const now = getCurrentUtcWithOffset(restaurant.timezone_offset);
  const schedules = restaurant.schedules;
  const timezoneOffset = restaurant.timezone_offset;

  const upcoming = getUpcomingSchedules(now, schedules);
  const relevant = upcoming[0] ?? null;
  const active = relevant && relevant.closestBegDate <= now && now <= relevant.closestEndDate
    ? relevant : null;

  const status = !!active ? 'Open' : 'Closed';

  const timeBeforeOrUntil = () => {
    if (!relevant) {
      return '-';
    }

    const beg = relevant.closestBegDate;
    const end = relevant.closestEndDate;

    if (status === 'Open') {
      const minutes = Math.trunc(end.diff(now, 'minutes').values.minutes);
      const hours = Math.trunc(minutes / 60);

      let time = '';

      if (hours > 0) {
        // Use translation for hour abbreviation
        time += hours + t('schedule.hour_short');
      }

      if (minutes % 60 > 0) {
        if (hours > 0) {
          time += ' ';
        }
        // Use translation for minute abbreviation
        time += (minutes % 60) + t('schedule.minute_short');
      }

      // Use translation for time until closing
      return t('schedule.T_until_closing', { time });
    } else if (beg.toMillis() >= now.toMillis()) {
      const minutes = Math.trunc(beg.diff(now, 'minutes').values.minutes);
      const hours = Math.trunc(minutes / 60);

      let time = '';

      if (hours > 0) {
        // Use translation for hour abbreviation
        time += hours + t('schedule.hour_short');
      }

      if (minutes % 60 > 0) {
        if (hours > 0) {
          time += ' ';
        }
        // Use translation for minute abbreviation
        time += (minutes % 60) + t('schedule.minute_short');
      }

      // Use translation for time until opening
      return t('schedule.T_before_opening', { time });
    } else {
      const next = schedules[0];

      let nextBeg = DateTime.utc()
        .set({hours: next.beg_hour, minutes: next.beg_minute, seconds: 0, milliseconds: 0})
        .minus({minutes: timezoneOffset});

      if (next.closest_date) {
        nextBeg = DateTime.fromJSDate(next.closest_date)
          .minus({minutes: timezoneOffset});
      } else {
        const weekdays = {
          monday: 1,
          tuesday: 2,
          wednesday: 3,
          thursday: 4,
          friday: 5,
          saturday: 6,
          sunday: 7,
        };

        const nextWeekdayNumber = weekdays[next.weekday];
        while (nextBeg.weekday !== nextWeekdayNumber) {
          nextBeg = nextBeg.plus({'days': 1});
        }
      }

      const minutes = Math.trunc(nextBeg.diff(now, 'minutes').values.minutes);
      const hours = Math.trunc(minutes / 60);

      // replaced this.$t("schedule.T_before_opening", {time: this.time(hours, minutes % 60)})
      return time(hours, minutes % 60);
    }
  };

  return {
    status,
    active,
    relevant,
    upcoming,
    schedules: filterAndSortSchedules(schedules),
    timeBeforeOrUntil: timeBeforeOrUntil(),
  } as ScheduleInfo;
}

export class ResponseErrors {
    public status?: number;
    public statusText?: string | null;
    public message?: string;
    public errors?: string[] | object;

    constructor(message: string | null = null, errors: string[] | object | null = null) {
        if (message) {
            this.message = message;
        }
        if (errors) {
            this.errors = errors;
        }
    }

    public static async from(response: Response | null): Promise<ResponseErrors> {
        if (!response || response.ok) {
            return new ResponseErrors()
        }

        try {
            const result = new ResponseErrors()

            if (response.status) {
                result.status = response.status;
            }
            if (response.statusText) {
                result.statusText = response.statusText;
            }

            const json = await response.json();
            if (json.message) {
                result.message = json.message;
            }
            if (json.errors) {
                result.errors = json.errors;
            }
            if (result.errors === undefined && typeof result.status === 'number' && (result.status < 200 || result.status > 299)) {
                result.errors = {
                    base: [json.message ?? result.statusText ?? 'Error occurred'],
                };
            }

            return result;
        } catch (e) {
            return new ResponseErrors()
        }
    }

    /**
     * Determines if there are any errors.
     *
     * @returns {boolean}
     */
    public hasErrors(): boolean {
        return this.errors !== undefined;
    }

    /**
     * Determines if there is an error message.
     *
     * @returns {boolean}
     */
    public hasMessage(): boolean {
        return this.message !== undefined;
    }

    /**
     * Determines if there is no error.
     *
     * @returns {boolean}
     */
    public isEmpty(): boolean {
        return !this.hasErrors() && !this.hasMessage();
    }

    /**
     * Determines if there is an error.
     *
     * @returns {boolean}
     */
    public isNotEmpty(): boolean {
        return !this.isEmpty();
    }

    /**
     * Extracts errors about specific attribute.
     *
     * @returns {string[]|null}
     */
    public about(attribute: string): string[] | null {
        if (!this.hasErrors()) {
            return null;
        }
        if (Array.isArray(this.errors)) {
            return null;
        }
        if (typeof this.errors === 'object' && this.errors !== null && attribute in this.errors) {
            // TypeScript doesn't know the type, so we cast optimistically
            return (this.errors as Record<string, string[]>)[attribute] ?? null;
        }

        return null;
    }
}
