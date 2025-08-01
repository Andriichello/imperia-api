/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { UpdateMediaRequestMetadata } from "./updateMediaRequestMetadata";

/**
 * Update media request
 */
export interface UpdateMediaRequest {
  file?: unknown;
  name?: string;
  /** @nullable */
  title?: string | null;
  /** @nullable */
  description?: string | null;
  /** Must start and end with the `/`. */
  folder?: string;
  /** @nullable */
  metadata?: UpdateMediaRequestMetadata;
}
