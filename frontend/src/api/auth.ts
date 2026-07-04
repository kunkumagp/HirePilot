import client from './client';
import type { ApiResponse } from '../types/api';
import type {
  AuthData,
  ChangePasswordData,
  DeleteAccountData,
  ForgotPasswordData,
  LoginData,
  RegisterData,
  ResetPasswordData,
  Session,
  UpdateProfileData,
  UserWithProfile,
} from '../types/auth';

export const authApi = {
  register: (data: RegisterData) =>
    client.post<ApiResponse<AuthData>>('/auth/register', data),

  login: (data: LoginData) =>
    client.post<ApiResponse<AuthData>>('/auth/login', data),

  logout: () =>
    client.post<ApiResponse<null>>('/auth/logout'),

  me: () =>
    client.get<ApiResponse<UserWithProfile>>('/auth/me'),

  forgotPassword: (data: ForgotPasswordData) =>
    client.post<ApiResponse<null>>('/auth/forgot-password', data),

  resetPassword: (data: ResetPasswordData) =>
    client.post<ApiResponse<null>>('/auth/reset-password', data),

  resendVerification: () =>
    client.post<ApiResponse<null>>('/auth/email/verification-notification'),

  updateProfile: (data: UpdateProfileData) =>
    client.put<ApiResponse<UserWithProfile>>('/profile', data),

  changePassword: (data: ChangePasswordData) =>
    client.put<ApiResponse<null>>('/password', data),

  deleteAccount: (data: DeleteAccountData) =>
    client.delete<ApiResponse<null>>('/account', { data }),

  getSessions: () =>
    client.get<ApiResponse<Session[]>>('/sessions'),

  revokeSession: (tokenId: number) =>
    client.delete<ApiResponse<null>>(`/sessions/${tokenId}`),
};
