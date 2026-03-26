'use strict';

/* ============================================
   TypeForge — script.js
   ES6 features used:
   - const / let
   - Arrow functions
   - Template literals
   - Destructuring
   - Spread operator
   - Async / Await
   - Promises
   - Array methods (map, filter, forEach, sort)
   - Default parameters
   - Object shorthand
============================================ */

// ====== Top 100 Common Words ======
const TOP_WORDS = [
    "the", "be", "to", "of", "and", "a", "in", "that", "have", "it",
    "for", "not", "on", "with", "he", "as", "you", "do", "at", "this",
    "but", "his", "by", "from", "they", "we", "say", "her", "she", "or",
    "an", "will", "my", "one", "all", "would", "there", "their", "what",
    "so", "up", "out", "if", "about", "who", "get", "which", "go", "me",
    "when", "make", "can", "like", "time", "no", "just", "him", "know",
    "take", "people", "into", "year", "your", "good", "some", "could",
    "them", "see", "other", "than", "then", "now", "look", "only", "come",
    "its", "over", "think", "also", "back", "after", "use", "two", "how",
    "our", "work", "first", "well", "way", "even", "new", "want", "because",
    "any", "these", "give", "day", "most", "us"
];

// ====== State Object ======
const state = {
    text:         '',
    charEls:      [],
    currentIndex: 0,
    timer:        60,
    selectedTime: 60,
    mode:         'words',
    isRunning:    false,
    isFinished:   false,
    interval:     null,
    correctChars: 0,
    totalTyped:   0,
    startTime:    null,
};

// ====== DOM References ======
const wordsDisplay = document.getElementById('wordsDisplay');
const hiddenInput  = document.getElementById('hiddenInput');
const timeDisplay  = document.getElementById('timeDisplay');
const liveWpm      = document.getElementById('liveWpm');
const liveAcc      = document.getElementById('liveAcc');
const statsBar     = document.getElementById('statsBar');
const typingArea   = document.getElementById('typingArea');
const cursorHint   = document.getElementById('cursorHint');
const resultsPanel = document.getElementById('resultsPanel');
const customBox    = document.getElementById('customBox');

// ====== Init on DOM Ready ======
document.addEventListener('DOMContentLoaded', () => {
    setupControls();
    loadText();
    setupInput();
    setupShortcuts();
    checkAuth();
    // Auto-focus so user can start typing immediately
    setTimeout(() => focusInput(), 300);
});

// ====== Setup Mode & Time Controls ======
const setupControls = () => {

    // Mode buttons
    document.querySelectorAll('#modeGroup .ctrl-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#modeGroup .ctrl-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.mode = btn.dataset.mode;

            // Show / hide custom textarea
            customBox.style.display = state.mode === 'custom' ? 'flex' : 'none';

            if (state.mode !== 'custom') {
                resetTest();
                loadText();
            }
        });
    });

    // Time buttons
    document.querySelectorAll('#timeGroup .ctrl-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#timeGroup .ctrl-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.selectedTime = parseInt(btn.dataset.time, 10);
            state.timer        = state.selectedTime;
            timeDisplay.textContent = state.timer;
            resetTest();
        });
    });
};

// ====== Load Text Based on Mode ======
const loadText = async () => {

    // Words mode — shuffle array with spread + sort, pick 40
    if (state.mode === 'words') {
        const shuffled = [...TOP_WORDS].sort(() => Math.random() - 0.5);
        state.text = shuffled.slice(0, 40).join(' ');
        renderText();
        return;
    }

    // Custom mode — wait for user input
    if (state.mode === 'custom') return;

    // Sentence / Paragraph — fetch from DB
    try {
        const res  = await fetch(`php/get_text.php?type=${state.mode}`);
        const data = await res.json();

        // Destructuring
        const { success, content } = data;
        state.text = success ? content : getFallback(state.mode);
    } catch (err) {
        state.text = getFallback(state.mode);
    }

    renderText();
};

// Fallback texts if DB fetch fails
const getFallback = (mode = 'sentence') => {
    const fallbacks = {
        sentence:  'The quick brown fox jumps over the lazy dog near the old riverbank.',
        paragraph: 'Programming is the process of creating instructions that tell a computer how to perform a task. Developers use logic and creativity to build software that powers the modern world. Every great application starts as a simple idea and grows through consistent effort and practice.'
    };
    return fallbacks[mode] ?? fallbacks.sentence;
};

// ====== Render Characters into DOM ======
const renderText = () => {
    wordsDisplay.innerHTML = '';
    state.charEls          = [];
    state.currentIndex     = 0;
    wordsDisplay.scrollTop = 0;

    const words = state.text.trim().split(' ');

    // forEach — build word and char spans
    words.forEach((word, wi) => {
        const wordEl = document.createElement('span');
        wordEl.className = 'word';

        // Spread string into array of chars
        [...word].forEach(char => {
            const charEl = document.createElement('span');
            charEl.className   = 'char';
            charEl.textContent = char;
            state.charEls.push(charEl);
            wordEl.appendChild(charEl);
        });

        // Space after every word except last
        if (wi < words.length - 1) {
            const spaceEl = document.createElement('span');
            spaceEl.className   = 'char';
            spaceEl.textContent = ' ';
            state.charEls.push(spaceEl);
            wordEl.appendChild(spaceEl);
        }

        wordsDisplay.appendChild(wordEl);
    });

    placeCursor();
};

// ====== Place Blinking Cursor ======
const placeCursor = () => {
    state.charEls.forEach(el => el.classList.remove('cursor'));
    if (state.currentIndex < state.charEls.length) {
        state.charEls[state.currentIndex].classList.add('cursor');
        scrollToLine();
    }
};

// Scroll words display when cursor moves past visible area
const scrollToLine = () => {
    const cursor = wordsDisplay.querySelector('.cursor');
    if (!cursor) return;

    const areaTop   = wordsDisplay.getBoundingClientRect().top;
    const cursorTop = cursor.getBoundingClientRect().top;
    const offset    = cursorTop - areaTop;
    const lineH     = parseFloat(getComputedStyle(wordsDisplay).lineHeight) || 40;

    if (offset > lineH * 2) {
        wordsDisplay.scrollTop += lineH;
    }
};

// ====== Setup Keyboard Input ======
const setupInput = () => {
    hiddenInput.addEventListener('keydown', onKeyDown);

    typingArea.addEventListener('click', focusInput);

    hiddenInput.addEventListener('focus', () => {
        cursorHint.style.display = 'none';
    });

    hiddenInput.addEventListener('blur', () => {
        if (!state.isRunning) {
            cursorHint.style.display = 'block';
        }
    });
};

const focusInput = () => {
    hiddenInput.focus();
    cursorHint.style.display = 'none';
};

// ====== Handle Each Keystroke ======
const onKeyDown = (e) => {
    if (state.isFinished) return;

    // ---- Backspace ----
    if (e.key === 'Backspace') {
        e.preventDefault();

        if (state.currentIndex === 0) return;

        // Move back one character
        state.currentIndex--;

        const prevChar = state.charEls[state.currentIndex];

        // Adjust counters based on what that char was
        if (prevChar.classList.contains('correct')) {
            state.correctChars--;
        }

        // Remove its state class
        prevChar.classList.remove('correct', 'wrong');

        // Adjust total typed
        if (state.totalTyped > 0) state.totalTyped--;

        placeCursor();
        updateLiveAcc();
        return;
    }

    // ---- Ignore non-character keys ----
    if (e.key.length !== 1) return;
    if (e.ctrlKey || e.metaKey || e.altKey) return;

    e.preventDefault();

    // Start timer on very first keystroke
    if (!state.isRunning) beginTimer();

    const target = state.charEls[state.currentIndex];
    if (!target) return;

    state.totalTyped++;

    if (e.key === target.textContent) {
        target.classList.add('correct');
        state.correctChars++;
    } else {
        target.classList.add('wrong');
    }

    state.currentIndex++;

    placeCursor();
    updateLiveAcc();

    // All chars typed — end early
    if (state.currentIndex >= state.charEls.length) {
        endTest();
    }
};

// ====== Start Timer ======
const beginTimer = () => {
    state.isRunning = true;
    state.startTime = Date.now();
    statsBar.classList.add('visible');

    state.interval = setInterval(() => {
        state.timer--;
        timeDisplay.textContent = state.timer;
        updateLiveWpm();

        if (state.timer <= 0) {
            clearInterval(state.interval);
            endTest();
        }
    }, 1000);
};

// ====== Live WPM (updates every second) ======
const updateLiveWpm = () => {
    const elapsed = (Date.now() - state.startTime) / 60000;
    const wpm     = elapsed > 0
        ? Math.round((state.correctChars / 5) / elapsed)
        : 0;
    liveWpm.textContent = wpm;
};

// ====== Live Accuracy (updates every keystroke) ======
const updateLiveAcc = () => {
    const acc = state.totalTyped > 0
        ? Math.round((state.correctChars / state.totalTyped) * 100)
        : 100;
    liveAcc.textContent = `${acc}%`;
};

// ====== Called by Start Button ======
const startTest = () => {
    if (state.mode === 'custom') {
        const val = document.getElementById('customInput').value.trim();
        if (!val) {
            document.getElementById('customInput').focus();
            return;
        }
        state.text = val;
        renderText();
    }
    focusInput();
};

// ====== Called by Apply button in custom mode ======
const applyCustom = () => {
    const val = document.getElementById('customInput').value.trim();
    if (!val) return;
    state.text = val;
    renderText();
    focusInput();
};

// ====== End Test ======
const endTest = () => {
    if (state.isFinished) return;

    clearInterval(state.interval);
    state.isFinished = true;
    state.isRunning  = false;
    hiddenInput.blur();

    const elapsed  = state.startTime
        ? (Date.now() - state.startTime) / 60000
        : state.selectedTime / 60;

    const wpm      = Math.round((state.correctChars / 5) / elapsed);

    const accuracy = state.totalTyped > 0
        ? parseFloat(((state.correctChars / state.totalTyped) * 100).toFixed(1))
        : 0;

    const timeTaken = state.startTime
        ? Math.round((Date.now() - state.startTime) / 1000)
        : state.selectedTime;

    // Template literals to display results
    document.getElementById('finalWpm').textContent   = wpm;
    document.getElementById('finalAcc').textContent   = `${accuracy}%`;
    document.getElementById('finalChars').textContent = `${state.correctChars}/${state.totalTyped}`;
    document.getElementById('finalTime').textContent  = `${timeTaken}s`;

    typingArea.style.display                             = 'none';
    document.querySelector('.action-row').style.display  = 'none';
    resultsPanel.style.display                           = 'block';

    saveScore({ wpm, accuracy });
};

// ====== Save Score — async / await + Promise ======
const saveScore = async ({ wpm, accuracy }) => {
    const saveStatus = document.getElementById('saveStatus');
    saveStatus.textContent = 'saving...';

    // URLSearchParams with spread operator
    const body = new URLSearchParams({
        wpm,
        accuracy,
        mode:     state.mode,
        duration: state.selectedTime
    });

    try {
        const res = await fetch('php/save_score.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });

        const { success, message } = await res.json();

        saveStatus.textContent = success
            ? '✓ score saved'
            : 'login to save your scores';

    } catch (err) {
        saveStatus.textContent = 'could not save — is XAMPP running?';
    }
};

// ====== Reset Test ======
const resetTest = () => {
    clearInterval(state.interval);

    // Object.assign to batch reset state
    Object.assign(state, {
        text:         '',
        charEls:      [],
        currentIndex: 0,
        timer:        state.selectedTime,
        isRunning:    false,
        isFinished:   false,
        interval:     null,
        correctChars: 0,
        totalTyped:   0,
        startTime:    null,
    });

    timeDisplay.textContent  = state.selectedTime;
    liveWpm.textContent      = '0';
    liveAcc.textContent      = '100%';

    statsBar.classList.remove('visible');
    typingArea.style.display                             = 'block';
    document.querySelector('.action-row').style.display  = 'flex';
    resultsPanel.style.display                           = 'none';

    hiddenInput.value        = '';
    cursorHint.style.display = 'block';
    wordsDisplay.scrollTop   = 0;

    if (state.mode !== 'custom') loadText();
};

// ====== Keyboard Shortcuts ======
const setupShortcuts = () => {
    document.addEventListener('keydown', e => {
        // Tab to restart
        if (e.key === 'Tab') {
            e.preventDefault();
            resetTest();
            return;
        }
        // Escape to restart
        if (e.key === 'Escape') {
            resetTest();
            return;
        }
        // Any other key — make sure hidden input is focused
        if (document.activeElement !== hiddenInput) {
            hiddenInput.focus();
        }
    });
};

// ====== Check Login Status — show/hide nav links ======
const checkAuth = async () => {
    try {
        const res          = await fetch('php/auth_check.php');
        const { loggedIn } = await res.json();

        const dashLink  = document.getElementById('dashLink');
        const loginLink = document.getElementById('loginLink');

        if (dashLink)  dashLink.style.display  = loggedIn ? 'inline' : 'none';
        if (loginLink) loginLink.style.display = loggedIn ? 'none'   : 'inline';

    } catch (err) {
        // Fail silently — nav defaults visible in HTML
    }
};