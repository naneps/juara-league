export interface ApiResponse<T> {
  data: T;
  message?: string;
  token?: string;
}
