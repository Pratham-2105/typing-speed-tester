# TypeForge ⌨️

A Monkeytype-inspired typing speed tester built with vanilla HTML, CSS, JavaScript (ES6), PHP, and MySQL. Built as a college workshop project covering the full web tech stack.

---

## Features

- Multiple test modes — Words, Sentence, Paragraph, Custom
- Selectable time limits — 15s, 30s, 60s, 120s
- Live WPM and accuracy tracking during the test
- Backspace support — fix mistakes mid-test
- Character-by-character highlighting (correct / wrong)
- User registration and login with session-based auth
- Scores saved to database after every test
- Personal dashboard — best WPM, avg WPM, avg accuracy, total tests
- Recent test history (last 10 tests)
- Global leaderboard (top 10 by best WPM)
- Last login shown via cookie

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Frontend   | HTML5, CSS3, JavaScript ES6       |
| Styling    | Custom CSS + Bootstrap 5          |
| Backend    | PHP 8 (procedural, mysqli)        |
| Database   | MySQL (via XAMPP)                 |
| Dev Tools  | XAMPP, VS Code, phpMyAdmin        |

---

## Project Structure
```
typing-speed-tester/
│
├── index.html           → Main typing test UI
├── login.html           → Login page
├── register.html        → Registration page
├── dashboard.php        → Protected user dashboard
├── database.sql         → Database schema + seed data
│
├── css/
│   └── style.css        → All styling
│
├── js/
│   └── script.js        → All typing test logic
│
└── php/
    ├── config.php       → Database connection
    ├── register.php     → Handles registration
    ├── login.php        → Handles login + session
    ├── logout.php       → Destroys session + clears cookie
    ├── auth_check.php   → Returns login status as JSON
    ├── get_text.php     → Fetches random text from DB
    ├── save_score.php   → Saves WPM + accuracy to DB
    └── get_scores.php   → Returns leaderboard + user history
```

---

## How to Run Locally

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL)
- A browser (Chrome recommended)

---

### Step 1 — Clone the repository
```bash
git clone https://github.com/YOUR_USERNAME/typing-speed-tester.git
```

---

### Step 2 — Move to XAMPP's htdocs folder

**Windows:**
```
C:\xampp\htdocs\
```

**Mac/Linux:**
```
/opt/lampp/htdocs/
```

Place the entire `typing-speed-tester` folder inside `htdocs`.

---

### Step 3 — Start XAMPP

Open the XAMPP Control Panel and start:
- **Apache**
- **MySQL**

Both should show green.

---

### Step 4 — Set up the database

1. Open your browser and go to:
```
   http://localhost/phpmyadmin
```
2. Click the **SQL** tab
3. Copy and paste the entire contents of `database.sql`
4. Click **Go**

This creates the `typing_app` database with all tables and seeds the text content automatically.

---

### Step 5 — Open the app
```
http://localhost/typing-speed-tester/index.html
```

---

### Step 6 — Register and test

1. Go to `register.html` and create an account
2. Login at `login.html`
3. Take a typing test on the main page
4. View your stats at `dashboard.php`

---

## How It Works

### Typing Engine
- The target text is split into individual character `<span>` elements
- A hidden `<input>` captures all keystrokes
- Each keypress is compared against the current character span
- Correct → character turns light, Wrong → character turns red with underline
- Backspace steps back one character and removes its state
- A blinking cursor tracks the current position
- The words display auto-scrolls as the cursor moves past the visible area

### WPM Calculation
```
WPM = (correct characters / 5) / elapsed time in minutes
```
The standard definition treats every 5 characters as one word.

### Accuracy Calculation
```
Accuracy = (correct keystrokes / total keystrokes) × 100
```

### Auth Flow
```
Register → PHP validates + hashes password → stored in DB
Login    → PHP verifies hash → sets $_SESSION → sets cookie
Dashboard → PHP checks $_SESSION → if missing, redirect to login
Logout   → session_destroy() + cookie cleared → redirect to login
```

### Score Saving
After every test, the frontend sends WPM, accuracy, mode, and duration
to `save_score.php` via a POST fetch request. Scores are only saved if
the user is logged in (session check on the PHP side).

---

## ES6 Features Used (for reference)

| Feature              | Where used                              |
|----------------------|-----------------------------------------|
| `const` / `let`      | Throughout script.js                    |
| Arrow functions      | All callbacks and handlers              |
| Template literals    | DOM updates, fetch URLs, result display |
| Destructuring        | fetch response parsing                  |
| Spread operator      | Array shuffle, URLSearchParams          |
| Async / Await        | All fetch calls                         |
| Promises             | saveScore, loadText, checkAuth          |
| Array methods        | map, forEach, filter, sort              |
| Default parameters   | getFallback(mode = 'sentence')          |
| Object shorthand     | State resets with Object.assign         |

---

## Database Schema
```sql
users  (id, username, email, password, created_at)
scores (id, user_id, wpm, accuracy, mode, duration, created_at)
texts  (id, content, type)
```

`scores.user_id` references `users.id` with `ON DELETE CASCADE`.

---

## Known Limitations

- Runs on localhost only — not deployed to a live server
- No password reset functionality
- No email verification on registration
- Custom mode text is not saved between sessions

---

## Author

**Pratham Srivastava**  
B.Tech IT — NIET Greater Noida (Batch 2028)  
[LinkedIn](https://linkedin.com/in/pratham-srivastava-54326634b) · [GitHub](https://github.com/YOUR_USERNAME)
