/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */

/**
 * Dish variant resource object
 */
export interface DishVariant {
  id: number;
  dish_id: number;
  type: string;
  price: number;
  /** @nullable */
  weight: string | null;
  /** @nullable */
  weight_unit: string | null;
  /** @nullable */
  calories: number | null;
  /** @nullable */
  preparation_time: number | null;
}
