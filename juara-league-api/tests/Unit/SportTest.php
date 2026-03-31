<?php

namespace Tests\Unit;

use App\Models\Sport;
use App\Repositories\Eloquent\SportRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportTest extends TestCase
{
    use RefreshDatabase;

    protected SportRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SportRepository();
    }

    public function test_it_can_create_a_sport()
    {
        $data = [
            'name' => 'Football',
            'type' => 'sport',
            'icon_url' => 'https://example.com/football.svg',
            'is_active' => true,
        ];

        $sport = $this->repository->create($data);

        $this->assertDatabaseHas('sports', [
            'id' => $sport->id,
            'name' => 'Football',
        ]);
        $this->assertEquals('Football', $sport->name);
        $this->assertEquals('sport', $sport->type);
    }

    public function test_it_can_get_active_sports()
    {
        Sport::factory()->create(['name' => 'Active Sport', 'is_active' => true]);
        Sport::factory()->create(['name' => 'Inactive Sport', 'is_active' => false]);

        $results = $this->repository->getAll(['active_only' => true]);

        $this->assertCount(1, $results);
        $this->assertEquals('Active Sport', $results[0]->name);
    }

    public function test_it_can_search_sports_by_name()
    {
        Sport::factory()->create(['name' => 'Badminton']);
        Sport::factory()->create(['name' => 'Basketball']);
        Sport::factory()->create(['name' => 'Tennis']);

        $results = $this->repository->getAll(['search' => 'Bad']);

        $this->assertCount(1, $results);
        $this->assertEquals('Badminton', $results[0]->name);
    }
}
