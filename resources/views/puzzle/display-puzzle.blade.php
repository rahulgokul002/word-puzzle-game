@push('scripts')
<script src="/js/puzzle.js"></script>
@endpush
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-semibold">Unscramble the letters</h3>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Puzzle ID: <strong>{{ $id }}</strong></p>
                    <p class="text-sm text-gray-600">Player: <strong>{{ $user->name }}</strong></p>
                </div>
            </div>

            <!-- Puzzle Code Display -->
            <div class="mb-6 p-6 bg-blue-50 rounded-lg border-2 border-blue-300">
                <p class="text-gray-600 text-sm mb-3 text-center">Available letters:</p>
                <p class="text-4xl font-bold text-center tracking-widest text-blue-600" id="puzzle-code">{{ $code }}</p>
            </div>

            <!-- Score Display -->
            <div class="mb-6 grid grid-cols-3 gap-4">
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <p class="text-gray-600 text-sm">Current Score</p>
                    <p class="text-3xl font-bold text-green-600" id="current-score">0</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <p class="text-gray-600 text-sm">Words Found</p>
                    <p class="text-3xl font-bold text-blue-600" id="words-count">0</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <p class="text-gray-600 text-sm">Remaining Letters</p>
                    <p class="text-lg font-bold text-purple-600 break-all" id="remaining-letters">{{ $code }}</p>
                </div>
            </div>

            <!-- Input Area for Valid Words -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Enter valid words you can form:</label>
                <div class="flex gap-2 mb-3">
                    <input 
                        type="text" 
                        id="word-input" 
                        placeholder="Type a word and press Enter"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        autocomplete="off"
                    >
                    <button 
                        id="add-word-btn"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                        Add Word
                    </button>
                </div>
            </div>

            <!-- Words Found List -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Words Added (current session):</h4>
                <div id="words-list" class="flex flex-wrap gap-2 mb-4 min-h-10 p-3 bg-gray-50 rounded">
                    <span class="text-gray-500 text-sm italic">No words added yet</span>
                </div>
            </div>

            <!-- Submission History -->
            <div class="mb-6" id="submission-history" style="display: none;">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Submission History:</h4>
                <div id="history-list" class="space-y-2 max-h-48 overflow-y-auto">
                </div>
            </div>

            <!-- Error Messages -->
            <div id="error-messages" class="mb-6" style="display: none;">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-red-700 mb-2">Invalid words:</h4>
                    <ul id="error-list" class="text-sm text-red-600 list-disc list-inside">
                    </ul>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button 
                    id="submit-btn"
                    class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled
                    data-token="{{ session('api_token') ?? '' }}"
                    data-puzzle-id="{{ $id }}"
                    data-user-id="{{ $user->id }}">
                    Submit Answer
                </button>
                <a 
                    href="{{ route('dashboard') }}"
                    class="flex-1 text-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded">
                    Back to Dashboard
                </a>
            </div>

            <!-- Hidden data attributes -->
            <div id="puzzle-data" 
                    data-puzzle-id="{{ $id }}" 
                    data-puzzle-code="{{ $code }}"
                    data-puzzle-user="{{ $user->name }}"
                    data-puzzle-user-id="{{ $user->id }}"
                    data-token="{{ session('api_token') ?? '' }}">
            </div>
        </div>
    </div>
</div>