/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { Category } from "./category";
import type { PaginationMeta } from "./paginationMeta";

/**
 * Index categories response object.
 */
export interface IndexCategoryResponse {
  data: Category[];
  meta: PaginationMeta;
  message: string;
}
