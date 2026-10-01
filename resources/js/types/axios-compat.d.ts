import type { AxiosRequestConfig, AxiosResponse } from 'axios';

/**
 * Compatibility shim for the orval-generated client in resources/js/api.
 *
 * Since axios 1.19, `get()`, `post()`, etc. return `Promise<AxiosResponseResult<...>>`,
 * which can't be assigned to the generic `Promise<TData>` that orval 7 generates.
 * These overloads restore the pre-1.19 signatures (`Promise<R>`), the runtime is unchanged.
 *
 * Remove once the client is regenerated with an orval version that supports the new axios types.
 */
declare module 'axios' {
  interface Axios {
    get<T = any, R = AxiosResponse<T>, D = any>(url: string, config?: AxiosRequestConfig<D>): Promise<R>;
    delete<T = any, R = AxiosResponse<T>, D = any>(url: string, config?: AxiosRequestConfig<D>): Promise<R>;
    post<T = any, R = AxiosResponse<T>, D = any>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
    patch<T = any, R = AxiosResponse<T>, D = any>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
    put<T = any, R = AxiosResponse<T>, D = any>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
  }
}
