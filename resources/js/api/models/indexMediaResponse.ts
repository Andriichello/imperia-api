/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { Media } from "./media";
import type { PaginationMeta } from "./paginationMeta";

/**
 * Index media response object.
 */
export interface IndexMediaResponse {
  data: Media[];
  meta: PaginationMeta;
  message: string;
}
