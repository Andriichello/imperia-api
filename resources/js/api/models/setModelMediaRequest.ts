/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { AttachingMedia } from "./attachingMedia";

/**
 * Set model's media request
 */
export interface SetModelMediaRequest {
  model_id?: number;
  model_type?: string;
  /** Will be stored/updated according to the given order. */
  media?: AttachingMedia[];
}
