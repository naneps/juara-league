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
    public function __construct(
        protected SportService $sportService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['search', 'type']);
        $filters['active_only'] = true;

        $perPage = $request->get('per_page', 50);
        $sports  = $this->sportService->getAllSports($filters, $perPage);

        return SportResource::collection($sports);
    }

    public function show(string $id): JsonResponse|SportResource
    {
        $sport = $this->sportService->getSportById($id);

        if (! $sport || ! $sport->is_active) {
            return response()->json(['message' => 'Sport not found or inactive.'], 404);
        }

        return new SportResource($sport);
    }
}
