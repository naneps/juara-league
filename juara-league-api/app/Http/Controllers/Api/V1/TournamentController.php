<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTournamentRequest;
use App\Http\Requests\Api\V1\UpdateTournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Services\TournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TournamentController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tournaments = $this->tournamentService->getAllTournaments($request->query('per_page', 15));
        return TournamentResource::collection($tournaments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTournamentRequest $request): JsonResponse
    {
        $tournament = $this->tournamentService->createTournament($request->user(), $request->validated());
        
        return (new TournamentResource($tournament))
            ->additional(['message' => 'Turnamen berhasil dibuat.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $slug): TournamentResource
    {
        $includes = $request->has('include') ? explode(',', $request->query('include')) : [];
        $tournament = $this->tournamentService->getTournamentBySlug($slug, $includes);
        
        if (!$tournament) {
             throw new \App\Exceptions\TournamentException('Turnamen tidak ditemukan.', 'TOURNAMENT_NOT_FOUND', 404);
        }

        return new TournamentResource($tournament);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTournamentRequest $request, Tournament $tournament): TournamentResource
    {
        $this->authorize('update', $tournament);

        $updatedTournament = $this->tournamentService->updateTournament($tournament, $request->validated());
        
        return new TournamentResource($updatedTournament);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('delete', $tournament);

        $this->tournamentService->deleteTournament($tournament);
        
        return response()->json(['message' => 'Tournament deleted successfully.']);
    }

    /**
     * Publish tournament: Move from draft to registration.
     */
    public function publish(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('publish', $tournament);

        try {
            $this->tournamentService->publish($tournament);
            return response()->json(['message' => 'Tournament published successfully.']);
        } catch (\App\Exceptions\TournamentException $e) {
            return $e->render($request);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display a listing of tournaments owned by the authenticated user.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $tournaments = $this->tournamentService->getUserTournaments(
            $request->user()->id, 
            $request->query('per_page', 15)
        );
        
        return TournamentResource::collection($tournaments);
    }
}
