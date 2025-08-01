/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import axios from "axios";
import type { AxiosRequestConfig, AxiosResponse } from "axios";

import type {
  DestroyOrderResponse,
  DestroyRequest,
  IndexOrderResponse,
  IndexOrdersParams,
  RestoreOrderResponse,
  ShowOrderParams,
  ShowOrderResponse,
  StoreOrderRequest,
  StoreOrderResponse,
  UpdateOrderRequest,
  UpdateOrderResponse,
} from "../models";

/**
 * @summary Index orders.
 */
export const indexOrders = <TData = AxiosResponse<IndexOrderResponse>>(
  params?: IndexOrdersParams,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/orders`, {
    ...options,
    params: { ...params, ...options?.params },
  });
};
/**
 * @summary Store order.
 */
export const storeOrder = <TData = AxiosResponse<StoreOrderResponse>>(
  storeOrderRequest: StoreOrderRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.post(`/api/orders`, storeOrderRequest, options);
};
/**
 * @summary Show orders by id.
 */
export const showOrder = <TData = AxiosResponse<ShowOrderResponse>>(
  id: number,
  params?: ShowOrderParams,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/orders/${id}`, {
    ...options,
    params: { ...params, ...options?.params },
  });
};
/**
 * @summary Delete order.
 */
export const destroyOrder = <TData = AxiosResponse<DestroyOrderResponse>>(
  id: number,
  destroyRequest?: DestroyRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.delete(`/api/orders/${id}`, {
    data: destroyRequest,
    ...options,
  });
};
/**
 * @summary Update order.
 */
export const updateOrder = <TData = AxiosResponse<UpdateOrderResponse>>(
  id: number,
  updateOrderRequest: UpdateOrderRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.patch(`/api/orders/${id}`, updateOrderRequest, options);
};
/**
 * @summary Restore order.
 */
export const restoreOrder = <TData = AxiosResponse<RestoreOrderResponse>>(
  id: number,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.post(`/api/orders/${id}/restore`, undefined, options);
};
export type IndexOrdersResult = AxiosResponse<IndexOrderResponse>;
export type StoreOrderResult = AxiosResponse<StoreOrderResponse>;
export type ShowOrderResult = AxiosResponse<ShowOrderResponse>;
export type DestroyOrderResult = AxiosResponse<DestroyOrderResponse>;
export type UpdateOrderResult = AxiosResponse<UpdateOrderResponse>;
export type RestoreOrderResult = AxiosResponse<RestoreOrderResponse>;
