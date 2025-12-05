<?php

namespace App\Services;

use App\Models\Puzzle;
use App\Models\Submission;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PuzzleService
{
    /**
     * Generate a new puzzle for a given user.
     *
     * @param int $userId
     * @param int $wordCount
     * @return Puzzle
     */
    public function createPuzzle(int $userId, int $wordCount = 3): Puzzle
    {
        $sourceWords = $this->getRandomWords($wordCount);
        $puzzleCode  = $this->generatePuzzleCode($sourceWords);

        return Puzzle::create([
            'user_id' => $userId,
            'letters' => $puzzleCode,
        ]);
    }

    /**
     * Generate a shuffled puzzle code from words.
     *
     * @param array $words
     * @return string
     */
    private function generatePuzzleCode(array $words): string
    {
        return str_shuffle(implode('', $words));
    }

    /**
     * Fetch random words to use in puzzle.
     *
     * @param int $count
     * @return array
     */
    private function getRandomWords(int $count): array
    {
        $randomWords = [];

        try {
            // Use Random Word API
            $response = Http::timeout(5)->get('https://random-word-api.herokuapp.com/all');

            if ($response->successful()) {
                $words = $response->json();
                
                // Filter words by length (3-5 characters)
                $validWords = array_filter($words, function ($word) {
                    return strlen($word) >= 3 && strlen($word) <= 5 && ctype_alpha($word);
                });

                // Shuffle and pick random words
                $validWords = array_values($validWords);
                shuffle($validWords);

                $randomWords = array_slice($validWords, 0, $count);
            }
        } catch (\Exception $e) {
            // Fallback: use hardcoded common 3-5 letter words
            $fallbackWords = [
                'code', 'word', 'love', 'help', 'team', 'test', 'game', 'play',
                'work', 'life', 'time', 'data', 'task', 'plan', 'kind', 'mind',
                'good', 'best', 'true', 'make', 'take', 'like', 'know', 'have',
                'move', 'jump', 'walk', 'run', 'fly', 'swim', 'sing', 'read'
            ];
            shuffle($fallbackWords);
            $randomWords = array_slice($fallbackWords, 0, $count);
        }

        return $randomWords;
    }

    /**
     * Get top scores for a given user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopScores(int $userId, int $limit = 10)
    {
        return Submission::where('user_id', $userId)
            ->select('word', DB::raw('MAX(score) as score'))
            ->groupBy('word')
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }
     /**
     * Add a word for a given puzzle and user.
     *
     * @param int $userId
     * @param int $puzzleId
     * @param string $word
     * @param string $code
     * @return Submission
     * @throws ValidationException
     */
    public function addWord(int $userId, int $puzzleId, string $word, string $code): Submission
    {
        $word = strtolower($word);

        if (!$this->isValidWord($word)) {
            throw ValidationException::withMessages(['word' => 'Not a valid English word']);
        }

        if ($this->wordExists($userId, $puzzleId, $word)) {
            throw ValidationException::withMessages(['word' => 'Word already exists']);
        }

        $remainingString = $this->calculateRemainingLetters($word, $code);

        return Submission::create([
            'user_id'           => $userId,
            'puzzle_id'         => $puzzleId,
            'word'              => $word,
            'score'             => strlen($word),
            'remaining_letters' => $remainingString,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    /**
     * Check if the word exists for the user & puzzle.
     */
    private function wordExists(int $userId, int $puzzleId, string $word): bool
    {
        return DB::table('submissions')
            ->where('user_id', $userId)
            ->where('puzzle_id', $puzzleId)
            ->where('word', $word)
            ->exists();
    }

    /**
     * Calculate remaining letters after using word letters.
     */
    private function calculateRemainingLetters(string $word, string $code): string
    {
        $wordLetters = str_split($word);
        $codeLetters = str_split($code);

        foreach ($wordLetters as $letter) {
            $index = array_search($letter, $codeLetters);
            if ($index !== false) {
                unset($codeLetters[$index]); // remove only one instance
            }
        }

        return implode('', array_values($codeLetters));
    }

    /**
     * Validate word using external dictionary API.
     */
    private function isValidWord(string $word): bool
    {
        try {
            $response = Http::timeout(5)
                ->withoutVerifying()
                ->get("https://api.dictionaryapi.dev/api/v2/entries/en/{$word}");

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();

            return is_array($data) && count($data) > 0 && !(isset($data['title']) && $data['title'] === 'No Definitions Found');
        } catch (\Exception $e) {
            return false;
        }
    }
     /**
     * Calculate the total score and words found for a given puzzle & user.
     *
     * @param int $puzzleId
     * @param int $userId
     * @return array
     */
    public function calculateScore(int $puzzleId, int $userId): array
    {
        // Fetch all words submitted by user for the puzzle
        $wordsFound = Submission::where('puzzle_id', $puzzleId)
            ->where('user_id', $userId)
            ->pluck('word')
            ->toArray();

        $score = array_sum(array_map('strlen', $wordsFound));
        $puzzleCode = Puzzle::where('id', $puzzleId)->value('letters');

        return [
            'score'       => $score,
            'words_found' => $wordsFound,
            'puzzle_code' => $puzzleCode,
            // 'words_missed' => $this->findPossibleWords($puzzleCode, $wordsFound),
        ];
    }

    /**
     * Optionally, find words that could have been made from the puzzle code
     * but were not submitted by the user.
     * Implement this if needed.
     */
    private function findPossibleWords(string $code, array $usedWords): array
    {
        $code = strtolower(trim($code));
        $codeLetters = count_chars($code, 1);
        $usedWords = array_map('strtolower', $usedWords);
        $response = Http::timeout(10)->withoutVerifying()
            ->get("https://random-word-api.herokuapp.com/all");

        if (!$response->successful()) {
            return [];
        }

        $dict = $response->json();

        $possible = [];

        foreach ($dict as $word) {

            $word = strtolower(trim($word));
            if (in_array($word, $usedWords)) {
                continue;
            }
            if (!ctype_alpha($word)) {
                continue;
            }
            $wordLetters = count_chars($word, 1);
            $valid = true;
            foreach ($wordLetters as $charAscii => $needCount) {
                if (
                    !isset($codeLetters[$charAscii]) ||
                    $needCount > $codeLetters[$charAscii]
                ) {
                    $valid = false;
                    break;
                }
            }

            if ($valid) {
                $possible[] = $word;
            }
        }
        usort($possible, fn($a, $b) => strlen($b) <=> strlen($a));
        return $possible;
    }
}
