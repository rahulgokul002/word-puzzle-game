
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <span class="text-lg font-semibold">
                        {{ __("Welcome ") }}{{ session('user_name') }}
                    </span>

                    <div class="flex space-x-3">
                        <button 
                            onclick="generatePuzzle()" 
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">
                            Start Puzzle
                        </button>
                        <button 
                            id="scoreboard-btn"
                            data-user-id="{{ session('user_id') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">
                            Score Board
                        </button>
                    </div>
                </div>
            </div>

            <!-- Puzzle Display Area -->
            <div id="puzzle-result" class="hidden"></div>
            <div id="leaderboard" class="hidden bg-white shadow-sm sm:rounded-lg mt-4">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        🏆 Top 10 High Scores
                    </h2>
                    <ul id="leaderboard-list" class="space-y-2"></ul>
                </div>
            </div>
        </div>
    </div>
    <div id="score-popup" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="bg-white max-w-md mx-auto mt-20 p-6 rounded shadow">
            <h2 class="text-xl font-bold">Game Over!</h2>
            <p class="mt-2">Great job! Here's how you did.</p>

            <div class="text-6xl font-bold my-4" id="score-value"></div>

            <h3 class="font-semibold">Words You Found</h3>
            <div id="found-words" class="flex flex-wrap gap-2 my-2"></div>

            <!-- <h3 class="font-semibold mt-4">Words You Could Have Made</h3>
            <div id="missed-words" class="flex flex-wrap gap-2 my-2"></div> -->

            <button onclick="location.reload()" class="bg-green-500 text-white px-4 py-2 rounded mt-4">
                Play Again
            </button>
        </div>
    </div>

    <script>
        const puzzleResult = document.getElementById('puzzle-result');
        const leaderboard = document.getElementById('leaderboard');
        const scoreboardBtn = document.getElementById('scoreboard-btn');

        scoreboardBtn.addEventListener('click', () => {
            if (leaderboard.classList.contains('hidden')) {
                leaderboard.classList.remove('hidden');
                puzzleResult.classList.add('hidden');
            } else {
                leaderboard.classList.add('hidden');
                puzzleResult.classList.remove('hidden');
            }
        });

        async function generatePuzzle() {
            try {
                puzzleResult.classList.remove('hidden');
                leaderboard.classList.add('hidden');

                const token = document.querySelector('meta[name="api-token"]')?.content || 
                              localStorage.getItem('api_token');

                if (!token) {
                    alert('No token found. Please log in again.');
                    return;
                }

                const response = await fetch('/api/puzzle/generate', {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const html = await response.text();
                const container = document.getElementById('puzzle-result');
                container.classList.remove('hidden');
                container.innerHTML = html;
                if (typeof window.initPuzzle === 'function') {
                    window.initPuzzle();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to generate puzzle: ' + error.message);
            }
        }
     

    document.getElementById('scoreboard-btn').addEventListener('click', async function() {

        const token = document.querySelector('meta[name="api-token"]')?.content || 
        localStorage.getItem('api_token');

        const userId = "{{ auth()->id() }}"; // or session('user_id')

        const response = await fetch(`/api/puzzle/leaderboard?user_id=${userId}`, {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json",
            }
        });

        const result = await response.json();

        const list = document.getElementById('leaderboard-list');
        list.innerHTML = ""; // clear old data

        let rank = 1;

        result.data.forEach(item => {
            let icon = "🏅";

            if (rank === 1) icon = "🥇";
            if (rank === 2) icon = "🥈";
            if (rank === 3) icon = "🥉";

            list.innerHTML += `
                <li class="flex justify-between items-center bg-gray-50 p-3 rounded shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-gray-700">${rank}</span>
                        <span class="text-xl">${icon}</span>
                        <span class="font-semibold uppercase">${item.word}</span>
                    </div>
                    <div class="font-bold text-green-600">${item.score} pts</div>
                </li>
            `;

            rank++;
        });
    document.getElementById('leaderboard').classList.remove('hidden');
});
</script>
<script src="{{ asset('js/puzzle.js') }}"></script>
</x-app-layout>