/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import type { AttachingComment } from "./attachingComment";
import type { AttachingDiscount } from "./attachingDiscount";

/**
 * Store order request ticket field
 */
export interface StoreOrderRequestTicketField {
  ticket_id: number;
  amount: number;
  comments?: AttachingComment[];
  discounts?: AttachingDiscount[];
}
