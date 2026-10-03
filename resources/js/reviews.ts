import {DateTime} from "luxon";

/**
 * Reviews on the public site: links to the restaurant's pages, the device's token and the reviews
 * it left (kept in the browser, so it sees the ones, which wait for approval), and their dates.
 */

/**
 * A page of the restaurant (`''` for the restaurant page, `'/reviews'`…): the query stays,
 * e.g. `?editor=1` of the editor's preview.
 */
export function restaurantUrl(page: string = ''): string {
  const base = window.location.pathname.match(/^\/[^/]+\/web\/[^/]+/)?.[0] ?? '';

  return `${base}${page}${window.location.search}`;
}

const TOKEN_KEY = 'reviews:token';

// the token, when the browser doesn't keep it (e.g. a private window): it lasts while the page is open
let sessionToken: string | null = null;

/** A random token of the device: its reviews are asked for with it. */
export function deviceToken(): string {
  try {
    const saved = localStorage.getItem(TOKEN_KEY);

    if (saved) {
      return saved;
    }
  } catch (error) {
    // not kept in this browser
  }

  sessionToken ??= [...crypto.getRandomValues(new Uint8Array(16))]
    .map((byte) => byte.toString(16).padStart(2, '0'))
    .join('');

  try {
    localStorage.setItem(TOKEN_KEY, sessionToken);
  } catch (error) {
    // not kept in this browser
  }

  return sessionToken;
}

const reviewsKey = (restaurantId: number) => `reviews:${restaurantId}`;

/** Ids of the restaurant's reviews, which the device left and which waited for approval. */
export function myReviewIds(restaurantId: number): number[] {
  try {
    const ids = JSON.parse(localStorage.getItem(reviewsKey(restaurantId)) ?? '[]');

    return Array.isArray(ids) ? ids.filter((id) => Number.isInteger(id)) : [];
  } catch (error) {
    return [];
  }
}

/** Keep these ids of the restaurant's reviews (the ones, which still wait for approval). */
export function keepMyReviews(restaurantId: number, ids: number[]): void {
  try {
    if (ids.length) {
      localStorage.setItem(reviewsKey(restaurantId), JSON.stringify(ids));
    } else {
      localStorage.removeItem(reviewsKey(restaurantId));
    }
  } catch (error) {
    // not kept in this browser
  }
}

/** "2 days ago", or "just now" (given in the page's language) within a minute. */
export function reviewDate(iso: string | null, locale: string, justNow: string): string {
  const date = iso ? DateTime.fromISO(iso) : null;

  if (!date || DateTime.now().diff(date, 'minutes').minutes < 1) {
    return justNow;
  }

  return date.setLocale(locale).toRelative({unit: ['years', 'months', 'weeks', 'days', 'hours', 'minutes']}) ?? '';
}

/** "4.6" ("4,6" in Ukrainian): an average rating, to one decimal. */
export function averageFormatted(average: number, locale: string): string {
  return new Intl.NumberFormat(locale, {minimumFractionDigits: 1, maximumFractionDigits: 1}).format(average);
}
