import client from './client';
import type { ApiResponse } from '../types/api';

export interface ResumeListItem {
  uuid: string;
  title: string;
  is_active: boolean;
  current_version: ResumeVersionItem | null;
  version_count: number;
  created_at: string;
  deleted_at?: string | null;
}

export interface ResumeVersionItem {
  uuid: string;
  version_number: number;
  is_current: boolean;
  original_filename: string;
  file_type: string;
  file_size: number;
  parse_status: string;
  parsed_content: Record<string, string> | null;
  created_at: string;
}

export interface ResumeDetailData {
  resume: ResumeListItem;
  versions: ResumeVersionItem[];
  current_version: ResumeVersionItem | null;
}

export const resumeApi = {
  list: () =>
    client.get<ApiResponse<ResumeListItem[]>>('/resumes'),

  create: (title: string, file: File) => {
    const formData = new FormData();
    formData.append('title', title);
    formData.append('file', file);
    return client.post<ApiResponse<ResumeListItem>>('/resumes', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  show: (uuid: string) =>
    client.get<ApiResponse<ResumeDetailData>>(`/resumes/${uuid}`),

  update: (uuid: string, data: { title?: string; is_active?: boolean }) =>
    client.put<ApiResponse<ResumeListItem>>(`/resumes/${uuid}`, data),

  delete: (uuid: string) =>
    client.delete<ApiResponse<null>>(`/resumes/${uuid}`),

  addVersion: (resumeUuid: string, file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return client.post<ApiResponse<ResumeListItem>>(`/resumes/${resumeUuid}/versions`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },

  activateVersion: (resumeUuid: string, versionUuid: string) =>
    client.put<ApiResponse<ResumeListItem>>(`/resumes/${resumeUuid}/versions/${versionUuid}/activate`),

  updateParsedContent: (resumeUuid: string, versionUuid: string, parsedContent: Record<string, string>) =>
    client.put<ApiResponse<ResumeListItem>>(`/resumes/${resumeUuid}/versions/${versionUuid}/content`, { parsed_content: parsedContent }),

  downloadVersion: (resumeUuid: string, versionUuid: string) =>
    client.get(`/resumes/${resumeUuid}/versions/${versionUuid}/download`, { responseType: 'blob' }),

  trash: () =>
    client.get<ApiResponse<ResumeListItem[]>>('/resumes/trash'),

  restore: (uuid: string) =>
    client.post<ApiResponse<ResumeListItem>>(`/resumes/${uuid}/restore`),

  forceDelete: (uuid: string) =>
    client.delete<ApiResponse<null>>(`/resumes/${uuid}/force`),
};
