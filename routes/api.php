<?php
use App\Http\Controllers\PuzzleController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/puzzle/generate', [PuzzleController::class, 'generate'])->name('puzzles.generate');
    Route::post('/puzzle/add-word', [PuzzleController::class, 'addWord'])->name('puzzle.add-word');
    Route::post('/puzzle/submit', [PuzzleController::class, 'submit'])->name('puzzle.submit');
    Route::get('/puzzle/leaderboard', [PuzzleController::class, 'leaderboard']);
});
