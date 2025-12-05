<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddWordRequest;
use App\Http\Requests\GeneratePuzzleRequest;
use App\Http\Requests\LeaderboardRequest;
use App\Http\Requests\SubmitPuzzleRequest;
use Illuminate\Http\Request;
use App\Services\PuzzleService;
use Illuminate\Validation\ValidationException;

class PuzzleController extends Controller
{
    protected PuzzleService $puzzleService;
    public function __construct(PuzzleService $puzzleService)
    {
        $this->puzzleService = $puzzleService;
    }
    /** 
    * Generate and display a new puzzle.
    */
    public function generate(GeneratePuzzleRequest $request)
    {
        $user = $request->user();

        $puzzle = $this->puzzleService->createPuzzle($user->id);

        return view('puzzle.display-puzzle', [
            'id'           => $puzzle->id,
            'code'         => $puzzle->letters,
            'user'         => $user,
            'source_words' => $puzzle->letters,
        ]);
    }

    /**
     * Return top 10 scores for a given user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaderboard(LeaderboardRequest  $request)
    {
        $userId = (int) $request->query('user_id');

        $leaderboard = $this->puzzleService->getTopScores($userId);

        return response()->json([
            'success' => true,
            'data'    => $leaderboard
        ]);
    }

    /**
     * Add a word for the puzzle.
     */
    public function addWord(AddWordRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'word'      => 'required|string|min:2',
            'puzzle_id' => 'required|integer',
            'code'      => 'required|string',
        ]);

        try {
            $submission = $this->puzzleService->addWord(
                $user->id,
                $request->puzzle_id,
                $request->word,
                $request->code
            );

            return response()->json([
                'message'    => 'Word added successfully',
                'submission' => $submission
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 400);
        }
    }

    /**
     * Submit puzzle and get score & words found.
     */
    public function submit(SubmitPuzzleRequest  $request)
    {
        $data = $request->validated();

        $puzzleId = (int) $data['puzzle_id'];
        $userId   = (int) $data['user_id'];

        $result = $this->puzzleService->calculateScore(
            $puzzleId,
            $userId
        );

        return response()->json([
            'score'       => $result['score'],
            'words_found' => $result['words_found'],
            // 'words_missed' => $result['words_missed'] ?? [],
        ]);
    }    
}
