<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Puzzle;
use App\Services\PuzzleService;

use Illuminate\Foundation\Testing\RefreshDatabase;

class PuzzleServiceTest extends TestCase
{
    use RefreshDatabase; // Rollback DB after each test

    protected PuzzleService $puzzleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->puzzleService = new PuzzleService();
    }

    /** @test */
    public function it_creates_a_puzzle_for_a_user()
    {
        $userId = 1;

        $puzzle = $this->puzzleService->createPuzzle($userId, 3);

        $this->assertInstanceOf(Puzzle::class, $puzzle);
        $this->assertEquals($userId, $puzzle->user_id);
        $this->assertIsString($puzzle->letters);
        $this->assertNotEmpty($puzzle->letters);
        $this->assertEquals(3, strlen($puzzle->letters) >= 3); // min letters check
    }

    /** @test */
    public function generated_puzzle_letters_are_shuffled()
    {
        $userId = 1;

        $puzzle1 = $this->puzzleService->createPuzzle($userId, 3);
        $puzzle2 = $this->puzzleService->createPuzzle($userId, 3);

        $this->assertNotEquals($puzzle1->letters, $puzzle2->letters);
    }

}
