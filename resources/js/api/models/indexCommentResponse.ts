/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { Comment } from "./comment";
import type { PaginationMeta } from "./paginationMeta";

/**
 * Index comments response object.
 */
export interface IndexCommentResponse {
  data: Comment[];
  meta: PaginationMeta;
  message: string;
}
