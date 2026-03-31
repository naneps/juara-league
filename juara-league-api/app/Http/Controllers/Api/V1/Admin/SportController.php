<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSportRequest;
use App\Http\Requests\Api\V1\UpdateSportRequest;
use App\Http\Resources\SportResource;
use App\Services\SportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SportController extends Controller
{
    /**
     * @param SportService $sportService
     */
    public function __construct(
        protected SportService $sportService
    ) {}

    /**
     * Create a new sport.
     * 
     * @param StoreSportRequest $request
     * @return JsonResponse
     */
    public function store(StoreSportRequest $request): JsonResponse
    {
        $sport = $this->sportService->createSport($request->validated());
        
        return response()->json([
            'message' => 'Sport created successfully.',
            'data' => new SportResource($sport),
        ], 201);
    }

    /**
     * Update an existing sport.
     * 
     * @param UpdateSportRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateSportRequest $request, string $id): JsonResponse
    {
        $sport = $this->sportService->getSportById($id);
        
        if (! $sport) {
            return response()->json([
                'message' => 'Sport not found.'
            ], 404);
        }
        
        $updatedSport = $this->sportService->updateSport($sport, $request->validated());
        
        return response()->json([
            'message' => 'Sport updated successfully.',
            'data' => new SportResource($updatedSport),
        ]);
    }

    /**
     * Delete a sport.
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $sport = $this->sportService->getSportById($id);
        
        if (! $sport) {
            return response()->json([
                'message' => 'Sport not found.'
            ], 404);
        }
        
        $this->sportService->deleteSport($sport);
        
        return response()->json([
            'message' => 'Sport deleted successfully.',
        ]);
    }
}
