<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddWordRequest;
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
    public function generate(Request $request)
    {
        $user = $request->user();

        $puzzle = $this->puzzleService->createPuzzle($user->id);

        return view('puzzle.display-puzzle', [
            'id'           => $puzzle->id,
            'code'         => $puzzle->letters,
            'user'         => $user,
            'source_words' => $puzzle->letters, // optionally pass the original words if stored
        ]);
    }

    /**
     * Return top 10 scores for a given user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaderboard(Request $request)
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
    public function submit(Request $request)
    {
        $request->validate([
            'puzzle_id' => 'required|integer',
            'user_id'   => 'required|integer',
        ]);

        $result = $this->puzzleService->calculateScore(
            $request->puzzle_id,
            $request->user_id
        );

        return response()->json([
            'score'       => $result['score'],
            'words_found' => $result['words_found'],
            // 'words_missed' => $result['words_missed'] ?? [],
        ]);
    }    
}
