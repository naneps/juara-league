<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Handle general file upload.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB default max
            'folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Upload gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $folder = $request->input('folder', 'general');
        
        try {
            $result = $this->fileService->upload($request->file('file'), $folder);
            
            return response()->json([
                'message' => 'File berhasil diunggah.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengunggah file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
