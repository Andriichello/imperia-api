import {DateTime} from 'luxon'
import type {
  EditorInterval,
  EditorRestaurant,
  EditorUpdateRestaurantHoursRequest,
  EditorUpdateRestaurantNotesRequest,
  EditorUpdateRestaurantRequest,
  EditorWeekdays,
  Media,
  Schedule,
  ScheduleException,
} from '@/api'
import {ScheduleWeekday} from '@/api'
import type {PreviewPatch} from '@/editor/protocol'
import {translated, Translations, translationsOf} from '@/editor/translations'

/**
 * What the restaurant panels edit (drafts), what they send to save it, and how the preview
 * shows it: the public page's data in the preview's language, which falls back to the
 * default one like the public page does.
 */

export const WEEKDAYS: ScheduleWeekday[] = Object.values(ScheduleWeekday)

/** Text in the preview's language, null when there's none. */
function text(value: Translations, locale: string, fallback: string): string | null {
  return translated(value, locale, fallback) || null
}

/** Texts to save: empty ones aren't written. */
function texts(value: Translations): Record<string, string | null> {
  return Object.fromEntries(Object.entries(value).map(([locale, item]) => [locale, item.trim() || null]))
}

// Details

export interface DetailsDraft {
  name: Translations
  establishment: string | null
  phone: string
  address: Translations
}

export function detailsOf(restaurant: EditorRestaurant): DetailsDraft {
  const locales = restaurant.supported_locales

  return {
    name: translationsOf(restaurant.name, locales),
    establishment: restaurant.establishment,
    phone: restaurant.phone ?? '',
    address: translationsOf(restaurant.address, locales),
  }
}

export function detailsRequest(details: DetailsDraft): EditorUpdateRestaurantRequest {
  return {
    name: texts(details.name),
    establishment: details.establishment as EditorUpdateRestaurantRequest['establishment'],
    phone: details.phone.trim() || null,
    address: texts(details.address),
  }
}

export function detailsPreview(details: DetailsDraft, locale: string, fallback: string): PreviewPatch {
  const address = text(details.address, locale, fallback)

  return {
    restaurant: {
      name: text(details.name, locale, fallback) ?? '',
      establishment: details.establishment,
      phone: details.phone.trim() || null,
      // without an address of its own, the page has the one put together from its parts
      ...(address ? {full_address: address} : {}),
    },
  }
}

// Notes

export interface NoteDraft {
  // of the list (ids of new notes are unknown)
  key: string
  id: number | null
  text: Translations
  is_hidden: boolean
}

export function notesOf(restaurant: EditorRestaurant): NoteDraft[] {
  return [...restaurant.notes]
    .sort((a, b) => a.order - b.order)
    .map((note) => ({
      key: `note-${note.id}`,
      id: note.id,
      text: translationsOf(note.text, restaurant.supported_locales),
      is_hidden: note.is_hidden,
    }))
}

export function notesRequest(notes: NoteDraft[]): EditorUpdateRestaurantNotesRequest {
  return {
    notes: notes.map((note) => ({
      id: note.id,
      text: texts(note.text),
      is_hidden: note.is_hidden,
    })),
  } as EditorUpdateRestaurantNotesRequest
}

export function notesPreview(notes: NoteDraft[], locale: string, fallback: string): PreviewPatch {
  return {
    restaurant: {
      notes: notes
        .filter((note) => !note.is_hidden)
        .map((note) => text(note.text, locale, fallback))
        .filter((note): note is string => !!note),
    },
  }
}

// Photos

export function photosPreview(photos: Media[]): PreviewPatch {
  return {restaurant: {media: photos}}
}

// Working hours

export interface SpecialDayDraft extends EditorInterval {
  key: string
  id: number | null
  starts_on: string
  // the same day for one day
  ends_on: string
  is_closed: boolean
  reason: Translations
}

export interface HoursDraft {
  timezone: string
  weekdays: Record<ScheduleWeekday, EditorInterval[]>
  exceptions: SpecialDayDraft[]
  // closed temporarily till the day (inclusive)
  closed: boolean
  closed_until: string
  closed_reason: Translations
}

/** Hours of an interval only (ids of the saved ones aren't sent). */
export function intervalOf(interval: EditorInterval): EditorInterval {
  return {
    beg_hour: interval.beg_hour,
    beg_minute: interval.beg_minute,
    end_hour: interval.end_hour,
    end_minute: interval.end_minute,
  }
}

export function hoursOf(restaurant: EditorRestaurant): HoursDraft {
  const locales = restaurant.supported_locales
  const weekdays = restaurant.weekdays ?? {}

  return {
    timezone: restaurant.timezone,
    weekdays: Object.fromEntries(WEEKDAYS.map((weekday) => [
      weekday,
      (weekdays[weekday as keyof EditorWeekdays] ?? []).map(intervalOf),
    ])) as Record<ScheduleWeekday, EditorInterval[]>,
    exceptions: (restaurant.exceptions ?? []).map((exception) => ({
      key: `day-${exception.id}`,
      id: exception.id,
      starts_on: exception.starts_on,
      ends_on: exception.ends_on,
      is_closed: exception.is_closed,
      beg_hour: exception.beg_hour ?? 10,
      beg_minute: exception.beg_minute ?? 0,
      end_hour: exception.end_hour ?? 18,
      end_minute: exception.end_minute ?? 0,
      reason: translationsOf(exception.reason, locales),
    })),
    closed: !!restaurant.closed_until,
    closed_until: restaurant.closed_until ?? '',
    closed_reason: translationsOf(restaurant.closed_reason, locales),
  }
}

export function hoursRequest(hours: HoursDraft): EditorUpdateRestaurantHoursRequest {
  return {
    timezone: hours.timezone,
    weekdays: hours.weekdays as EditorWeekdays,
    exceptions: hours.exceptions.map((day) => ({
      id: day.id,
      starts_on: day.starts_on,
      ends_on: day.ends_on || day.starts_on,
      is_closed: day.is_closed,
      ...(day.is_closed
        ? {beg_hour: null, beg_minute: null, end_hour: null, end_minute: null}
        : intervalOf(day)),
      reason: texts(day.reason),
    })),
    closed_until: hours.closed ? (hours.closed_until || null) : null,
    closed_reason: hours.closed ? texts(hours.closed_reason) : null,
  } as EditorUpdateRestaurantHoursRequest
}

export function hoursPreview(hours: HoursDraft, locale: string, fallback: string): PreviewPatch {
  const schedules = WEEKDAYS.flatMap((weekday) => hours.weekdays[weekday].map((interval, index) => ({
    // ids only tell them apart in the preview
    id: -(WEEKDAYS.indexOf(weekday) * 10 + index + 1),
    weekday,
    ...intervalOf(interval),
    archived: false,
  }) as Schedule))

  const exceptions = hours.exceptions
    .filter((day) => !!day.starts_on)
    .map((day, index) => ({
      id: day.id ?? -(index + 1),
      starts_on: day.starts_on,
      ends_on: day.ends_on || day.starts_on,
      is_closed: day.is_closed,
      beg_hour: day.beg_hour,
      beg_minute: day.beg_minute,
      end_hour: day.end_hour,
      end_minute: day.end_minute,
      reason: text(day.reason, locale, fallback),
    }) as ScheduleException)
    .sort((a, b) => a.starts_on.localeCompare(b.starts_on))

  const closed = hours.closed && !!hours.closed_until

  return {
    restaurant: {
      timezone: hours.timezone,
      // minutes from UTC now: the page's Open / Closed is calculated with it
      timezone_offset: DateTime.now().setZone(hours.timezone).offset,
      schedules,
      exceptions,
      closed_until: closed ? hours.closed_until : null,
      closed_reason: closed ? text(hours.closed_reason, locale, fallback) : null,
    },
  }
}
