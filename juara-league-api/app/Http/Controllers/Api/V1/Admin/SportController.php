<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSportRequest;
use App\Http\Requests\Api\V1\UpdateSportRequest;
use App\Http\Resources\SportResource;
use App\Services\SportService;
use Illuminate\Http\JsonResponse;

class SportController extends Controller
{
    public function __construct(
        protected SportService $sportService
    ) {}

    public function store(StoreSportRequest $request): JsonResponse
    {
        $sport = $this->sportService->createSport($request->validated());

        return response()->json([
            'message' => 'Sport created successfully.',
            'data'    => new SportResource($sport),
        ], 201);
    }

    public function update(UpdateSportRequest $request, string $id): JsonResponse
    {
        $sport = $this->sportService->getSportById($id);

        if (! $sport) {
            return response()->json(['message' => 'Sport not found.'], 404);
        }

        $updated = $this->sportService->updateSport($sport, $request->validated());

        return response()->json([
            'message' => 'Sport updated successfully.',
            'data'    => new SportResource($updated),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $sport = $this->sportService->getSportById($id);

        if (! $sport) {
            return response()->json(['message' => 'Sport not found.'], 404);
        }

        $this->sportService->deleteSport($sport);

        return response()->json(['message' => 'Sport deleted successfully.']);
    }
}
