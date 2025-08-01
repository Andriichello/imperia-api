/**
 * Generated by orval v7.9.0 🍺
 * Do not edit manually.
 * imperia-api
 * OpenAPI spec version: 0.1
 */
import axios from "axios";
import type { AxiosRequestConfig, AxiosResponse } from "axios";

import type {
  IndexUserResponse,
  IndexUsersParams,
  MeParams,
  MeResponse,
  ShowUserResponse,
  UpdateUserRequest,
  UpdateUserResponse,
} from "../models";

/**
 * @summary Get currently logged user.
 */
export const me = <TData = AxiosResponse<MeResponse>>(
  params?: MeParams,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/users/me`, {
    ...options,
    params: { ...params, ...options?.params },
  });
};
/**
 * @summary Index users.
 */
export const indexUsers = <TData = AxiosResponse<IndexUserResponse>>(
  params?: IndexUsersParams,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/users`, {
    ...options,
    params: { ...params, ...options?.params },
  });
};
/**
 * @summary Show user by id.
 */
export const showUser = <TData = AxiosResponse<ShowUserResponse>>(
  id: number,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/users/${id}`, options);
};
/**
 * @summary Update user.
 */
export const updateUser = <TData = AxiosResponse<UpdateUserResponse>>(
  id: number,
  updateUserRequest: UpdateUserRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.patch(`/api/user/${id}`, updateUserRequest, options);
};
export type MeResult = AxiosResponse<MeResponse>;
export type IndexUsersResult = AxiosResponse<IndexUserResponse>;
export type ShowUserResult = AxiosResponse<ShowUserResponse>;
export type UpdateUserResult = AxiosResponse<UpdateUserResponse>;
