(function () {
    let wordsList = [];
    let usedLetters = [];
    let originalCode = '';
    let totalScore = 0;
    let submissionHistory = [];
    function getAuthHeaders() {
        const token = document.querySelector('meta[name="api-token"]')?.content || 
        localStorage.getItem('api_token');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        return headers;
    }

    function updateWordsList() {
        const el = document.getElementById('words-list');
        if (!el) return;

        if (wordsList.length === 0) {
            el.innerHTML = '<span class="text-gray-500 text-sm italic">No words added yet</span>';
            return;
        }

        el.innerHTML = wordsList.map(word => `
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm flex items-center gap-2">
                <strong>${word}</strong>
                <span class="text-xs bg-white text-green-700 px-2 py-0.5 rounded">+${word.length}</span>
                <button type="button" class="text-red-600 hover:text-red-800 font-bold" data-word="${word}"></button>
            </span>
        `).join('');

        updateScore();
    }

    function updateScore() {
        const score = wordsList.reduce((sum, word) => sum + word.length, 0);
        const el = document.getElementById('current-score');
        if (el) el.textContent = score;

        const countEl = document.getElementById('words-count');
        if (countEl) countEl.textContent = wordsList.length;

        const remaining = getAvailableLetters();
        const remainingStr = Object.keys(remaining)
            .sort()
            .map(k => k.repeat(remaining[k]))
            .join('');
        const remainingEl = document.getElementById('remaining-letters');
        if (remainingEl) remainingEl.textContent = remainingStr || '(none)';

        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) submitBtn.disabled = wordsList.length === 0;
    }

    function getAvailableLetters() {
        const code = originalCode;
        const letterCount = {};

        for (let char of code) {
            letterCount[char] = (letterCount[char] || 0) + 1;
        }

        for (let char of usedLetters) {
            if (letterCount[char]) letterCount[char]--;
        }

        return letterCount;
    }

    function canFormWord(word) {
        const available = getAvailableLetters();
        const wordLetters = {};

        for (let char of word.toLowerCase()) {
            wordLetters[char] = (wordLetters[char] || 0) + 1;
        }

        for (let char in wordLetters) {
            if ((available[char] || 0) < wordLetters[char]) {
                return false;
            }
        }

        return true;
    }

    async function addWord() {
        const input = document.getElementById('word-input');
        if (!input) return;

        const word = input.value.trim().toLowerCase();
        if (!word) {
            alert('Please enter a word');
            return;
        }

        if (wordsList.includes(word)) {
            alert('Word already added to this submission');
            input.value = '';
            return;
        }

        const puzzleData = document.getElementById('puzzle-data');
        if (!puzzleData) return alert('Puzzle data not found');

        const puzzleId = puzzleData.dataset.puzzleId;
        const userId = puzzleData.dataset.puzzleUserId;
        const remainingLettersElement = document.getElementById('remaining-letters');

        let code = '';
        if (remainingLettersElement) {
            code = remainingLettersElement.textContent.trim(); 
        }
        if (!isConstructable(word, code)) {
            alert(`The word "${word}" uses letters not available in the code: ${code}, or uses a letter too many times.`);
            input.value = '';
            return;
        }
        try {
            const response = await fetch('/api/puzzle/add-word', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    puzzle_id: puzzleId,
                    word: word,
                    user_id: userId,
                    code: code,
                    used_letters: usedLetters
                })
            });

            const data = await response.json();
            console.log(data);

            if (response.ok) {
                // Word is valid
                wordsList.push(word);
                
                // Update used letters from API response
                if (data.used_letters) {
                    usedLetters = data.used_letters;
                }

                input.value = '';
                updateWordsList();
                input.focus();

                // Update remaining letters display
                const remainingEl = document.getElementById('remaining-letters');
                if (remainingEl && data.submission.remaining_letters) {
                    remainingEl.textContent = data.submission.remaining_letters || '(none)';
                }
            } else {
                // Word is invalid
                alert(data.message || `Cannot form "${word}"`);
                input.value = '';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to validate word: ' + error.message);
        }
    }

    function removeWord(word) {
        wordsList = wordsList.filter(w => w !== word);
        updateWordsList();
    }

    function displayErrors(errors) {
        const errorDiv = document.getElementById('error-messages');
        const errorList = document.getElementById('error-list');

        if (!errors || errors.length === 0) {
            if (errorDiv) errorDiv.style.display = 'none';
            return;
        }

        if (errorList) {
            errorList.innerHTML = errors.map(err => `<li>${err}</li>`).join('');
        }
        if (errorDiv) errorDiv.style.display = 'block';
    }

    function displaySubmissionHistory() {
        const historyDiv = document.getElementById('submission-history');
        const historyList = document.getElementById('history-list');

        if (!submissionHistory.length) {
            if (historyDiv) historyDiv.style.display = 'none';
            return;
        }

        if (historyList) {
            historyList.innerHTML = submissionHistory.map((sub, idx) => `
                <div class="bg-gray-50 p-3 rounded border border-gray-200">
                    <p class="text-sm font-semibold text-gray-700">Submission ${idx + 1}</p>
                    <p class="text-sm text-gray-600">Words: <strong>${sub.words.join(', ')}</strong></p>
                    <p class="text-sm text-gray-600">Points: <strong class="text-green-600">${sub.score}</strong></p>
                </div>
            `).join('');
        }
        if (historyDiv) historyDiv.style.display = 'block';
    }

    function closePuzzle() {
        if (confirm('Are you sure? You will lose your current progress.')) {
            wordsList = [];
            usedLetters = [];
            updateWordsList();
            window.location.href = '/dashboard';
        }
    }

    function init() {
        const puzzleData = document.getElementById('puzzle-data');
        if (puzzleData) {
            originalCode = puzzleData.dataset.puzzleCode || '';
        }

        const input = document.getElementById('word-input');
        const addBtn = document.getElementById('add-word-btn');
        const wordsList_el = document.getElementById('words-list');

        if (input) {
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addWord();
                }
            });
        }

        if (addBtn) addBtn.addEventListener('click', addWord);
        if (wordsList_el) {
            wordsList_el.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-word');
                if (btn) removeWord(btn.dataset.word);
            });
        }

        updateScore();
    }

    window.initPuzzle = init;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function isConstructable(word, code) {
        const codeCounts = {};
        for (const char of code) {
            codeCounts[char] = (codeCounts[char] || 0) + 1;
        }

        for (const char of word) {
            if (!codeCounts[char] || codeCounts[char] <= 0) {
                return false;
            }
            codeCounts[char]--;
        }

        return true;
    }
    document.addEventListener("click", async function (event) {

        if (!event.target.matches("#submit-btn")) return;

        const btn = event.target;

        const token = btn.dataset.token;
        const puzzleId = btn.dataset.puzzleId;
        const userId = btn.dataset.userId;

        try {
            const response = await fetch(`/api/puzzle/submit`, {
                method: "POST",
                headers: getAuthHeaders(),
                body: JSON.stringify({
                    puzzle_id: puzzleId,
                    user_id: userId
                })
            });

            const result = await response.json();
            showScorePopup(result);

        } catch (err) {
            console.error(err);
            alert("Network Error");
        }
    });
    function showScorePopup(data) {
        document.getElementById('score-value').innerText = data.score;

        const foundContainer = document.getElementById('found-words');
        foundContainer.innerHTML = "";
        data.words_found.forEach(word => {
            foundContainer.innerHTML += `<span class="bg-green-200 px-2 py-1 rounded">${word.toUpperCase()}</span>`;
        });
        document.getElementById('score-popup').classList.remove('hidden');
    }
})();