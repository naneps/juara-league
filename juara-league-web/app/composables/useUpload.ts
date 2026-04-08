import type { ApiResponse } from '~/types/api'

export interface UploadResult {
  path: string
  url: string
  filename: string
  original_name: string
  size: number
  mime_type: string
}

export const useUpload = () => {
  const isUploading = useState<boolean>('file_uploading', () => false)
  const uploadError = useState<string | null>('file_upload_error', () => null)

  const uploadFile = async (file: File, folder: string = 'general'): Promise<UploadResult | null> => {
    isUploading.value = true
    uploadError.value = null

    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder', folder)

    try {
      const response = await useApi<ApiResponse<UploadResult>>('/api/v1/upload', {
        method: 'POST',
        body: formData
      })

      return response.data
    } catch (error: any) {
      uploadError.value = error.data?.message || 'Gagal mengunggah file.'
      console.error('File upload error:', error)
      return null
    } finally {
      isUploading.value = false
    }
  }

  return {
    isUploading,
    uploadError,
    uploadFile
  }
}
