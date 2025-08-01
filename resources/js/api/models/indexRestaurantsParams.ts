/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { RestaurantIncludes } from "./restaurantIncludes";

export type IndexRestaurantsParams = {
  include?: RestaurantIncludes;
  /**
   * Available sorts: `popularity` (is default, but in descending order)
   */
  sort?: string;
  "filter[slug]"?: string;
  "filter[name]"?: string;
};
