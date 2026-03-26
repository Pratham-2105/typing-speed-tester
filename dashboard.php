<?php
// ============================================
//  TypeForge — Dashboard (Protected Page)
// ============================================

session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$username   = htmlspecialchars($_SESSION['username']);
$last_login = isset($_COOKIE['last_login'])
    ? htmlspecialchars($_COOKIE['last_login'])
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — TypeForge</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Our CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="dashboard-page">

    <!-- ===== Nav ===== -->
    <nav class="dash-nav">
        <a href="index.html" class="nav-logo">TypeForge</a>
        <div class="user-info">
            <?php if ($last_login): ?>
                <span>
                    last login:
                    <strong><?= $last_login ?></strong>
                </span>
            <?php endif; ?>
            <span>Hello, <strong><?= $username ?></strong></span>
            <a href="index.html">← Test</a>
            <a href="php/logout.php">Logout</a>
        </div>
    </nav>

    <!-- ===== Dashboard Content ===== -->
    <div class="dash-content">

        <!-- Your Stats -->
        <div class="dash-section">
            <h3>Your Stats</h3>
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="label">best wpm</div>
                    <div class="value" id="statBest">—</div>
                </div>
                <div class="stat-card">
                    <div class="label">avg wpm</div>
                    <div class="value" id="statAvg">—</div>
                </div>
                <div class="stat-card">
                    <div class="label">avg accuracy</div>
                    <div class="value" id="statAcc">—</div>
                </div>
                <div class="stat-card">
                    <div class="label">tests taken</div>
                    <div class="value" id="statTests">—</div>
                </div>
            </div>
        </div>

        <!-- Recent Tests -->
        <div class="dash-section">
            <h3>Recent Tests</h3>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>WPM</th>
                        <th>Accuracy</th>
                        <th>Mode</th>
                        <th>Duration</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="historyBody">
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--text-sub); padding:1.5rem;">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Global Leaderboard -->
        <div class="dash-section">
            <h3>Global Leaderboard</h3>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Username</th>
                        <th>Best WPM</th>
                        <th>Avg Accuracy</th>
                        <th>Tests</th>
                    </tr>
                </thead>
                <tbody id="leaderboardBody">
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-sub); padding:1.5rem;">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
    <!-- /.dash-content -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    'use strict';

    // ===== Rank badge HTML — template literal =====
    const rankBadge = rank => {
        const cls = ['gold', 'silver', 'bronze'][rank - 1] ?? '';
        return `<span class="rank-badge ${cls}">${rank}</span>`;
    };

    // ===== Format date string =====
    const formatDate = str => {
        const d = new Date(str);
        return d.toLocaleDateString('en-IN', {
            day:   '2-digit',
            month: 'short',
            year:  'numeric'
        });
    };

    // ===== Load all dashboard data — async/await =====
    const loadDashboard = async () => {
        try {
            const res = await fetch('php/get_scores.php');

            // Destructuring with defaults
            const {
                leaderboard = [],
                history     = [],
                stats       = {}
            } = await res.json();

            // ---- Stats Cards ----
            if (stats && stats.total_tests > 0) {
                document.getElementById('statBest').textContent  = stats.best_wpm     ?? '—';
                document.getElementById('statAvg').textContent   = stats.avg_wpm      ?? '—';
                document.getElementById('statAcc').textContent   = stats.avg_accuracy
                    ? `${stats.avg_accuracy}%`
                    : '—';
                document.getElementById('statTests').textContent = stats.total_tests  ?? '0';
            } else {
                document.getElementById('statBest').textContent  = '—';
                document.getElementById('statAvg').textContent   = '—';
                document.getElementById('statAcc').textContent   = '—';
                document.getElementById('statTests').textContent = '0';
            }

            // ---- Recent History — array.map + template literals ----
            const histBody = document.getElementById('historyBody');

            if (history.length === 0) {
                histBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center; color:var(--text-sub); padding:1.5rem;">
                            No tests yet.
                            <a href="index.html" style="color:var(--accent);">
                                Take your first test!
                            </a>
                        </td>
                    </tr>`;
            } else {
                histBody.innerHTML = history.map((row, i) => `
                    <tr>
                        <td style="color:var(--text-sub)">
                            ${i + 1}
                        </td>
                        <td style="color:var(--accent); font-weight:500">
                            ${row.wpm}
                        </td>
                        <td>${row.accuracy}%</td>
                        <td style="color:var(--text-sub); text-transform:capitalize">
                            ${row.mode}
                        </td>
                        <td style="color:var(--text-sub)">
                            ${row.duration}s
                        </td>
                        <td style="color:var(--text-sub)">
                            ${formatDate(row.created_at)}
                        </td>
                    </tr>
                `).join('');
            }

            // ---- Leaderboard — array.map + template literals ----
            const lbBody = document.getElementById('leaderboardBody');

            if (leaderboard.length === 0) {
                lbBody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-sub); padding:1.5rem;">
                            No scores yet. Be the first!
                        </td>
                    </tr>`;
            } else {
                lbBody.innerHTML = leaderboard.map((row, i) => `
                    <tr>
                        <td>${rankBadge(i + 1)}</td>
                        <td>${row.username}</td>
                        <td style="color:var(--accent); font-weight:500">
                            ${row.best_wpm}
                        </td>
                        <td>${row.avg_accuracy}%</td>
                        <td style="color:var(--text-sub)">
                            ${row.total_tests}
                        </td>
                    </tr>
                `).join('');
            }

        } catch (err) {
            console.error('Dashboard load error:', err);
        }
    };

    // Run on page load
    loadDashboard();
    </script>

</body>
</html>