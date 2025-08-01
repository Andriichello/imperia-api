/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { StoreDishRequestWeightUnit } from "./storeDishRequestWeightUnit";
import type { StoreDishRequestMetadata } from "./storeDishRequestMetadata";

/**
 * Store dish request.
 */
export interface StoreDishRequest {
  menu_id: number;
  /** @nullable */
  category_id?: number | null;
  /**
   * Must be unique within the menu.
   * @nullable
   */
  slug?: string | null;
  /** @nullable */
  badge?: string | null;
  title: string;
  /** @nullable */
  description?: string | null;
  price: number;
  weight?: number;
  weight_unit?: StoreDishRequestWeightUnit;
  /** @nullable */
  calories?: number | null;
  /** @nullable */
  preparation_time?: number | null;
  archived?: boolean;
  popularity?: number;
  /** @nullable */
  metadata?: StoreDishRequestMetadata;
}
