/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { ProductVariantWeightUnit } from "./productVariantWeightUnit";
import type { Alternation } from "./alternation";

/**
 * Product variant resource object
 */
export interface ProductVariant {
  id: number;
  product_id: number;
  type: string;
  price: number;
  weight: number;
  weight_unit: ProductVariantWeightUnit;
  alterations?: Alternation[];
  pendingAlterations?: Alternation[];
  performedAlterations?: Alternation[];
}
