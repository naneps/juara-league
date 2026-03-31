<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportResource;
use App\Services\SportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SportController extends Controller
{
    /**
     * @param SportService $sportService
     */
    public function __construct(
        protected SportService $sportService
    ) {}

    /**
     * Get list of active sports.
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'type']);
        $filters['active_only'] = true; // Public only see active sports
        
        $perPage = $request->get('per_page', 50);
        
        $sports = $this->sportService->getAllSports($filters, $perPage);
        
        return SportResource::collection($sports);
    }

    /**
     * Get detail of a sport.
     * 
     * @param string $id
     * @return JsonResponse|SportResource
     */
    public function show(string $id)
    {
        $sport = $this->sportService->getSportById($id);
        
        if (! $sport || ! $sport->is_active) {
            return response()->json([
                'message' => 'Sport not found or inactive.'
            ], 404);
        }
        
        return new SportResource($sport);
    }
}
