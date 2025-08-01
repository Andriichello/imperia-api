/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { Restaurant } from "./restaurant";

/**
 * Waiter resource object
 */
export interface Waiter {
  id: number;
  /** @nullable */
  restaurant_id: number | null;
  type: string;
  /** @nullable */
  uuid: string | null;
  name: string;
  surname: string;
  /** @nullable */
  about: string | null;
  restaurant?: Restaurant;
}
