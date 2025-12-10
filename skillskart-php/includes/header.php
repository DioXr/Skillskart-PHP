<?php
// 1. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Page Logic
$current_page = basename($_SERVER['PHP_SELF']);
$guest_pages = ['login.php', 'register.php'];
$is_guest_page = in_array($current_page, $guest_pages);
$is_admin_login_page = (strpos($_SERVER['REQUEST_URI'], '/admin/login.php') !== false);

// 3. Context Logic
$in_admin_folder = (strpos($_SERVER['REQUEST_URI'], '/skillskart-php/admin/') !== false);

// 4. Auth Check (THE FIX IS HERE)
// We check for 'student_id' because that matches your login.php
$is_student = isset($_SESSION['student_id']); 
$is_admin   = isset($_SESSION['admin_id']); 

// 5. Super Admin Logic
$is_super_admin = false;
if ($is_admin && array_key_exists('assigned_language', $_SESSION)) {
    if ($_SESSION['assigned_language'] === null) {
        $is_super_admin = true;
    }
}

// 6. Page Title Helper
$page_title_display = isset($page_title) ? htmlspecialchars($page_title) . ' - Skillskart' : 'Skillskart';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title_display; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="/skillskart-php/assets/css/style.css?v=8">

    <script>
        (function() {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="/skillskart-php/index.php" class="logo">
                <i class="fa-solid fa-graduation-cap"></i> Skillskart
            </a>
        </div>

        <div class="nav-center">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="live-search" placeholder="Search roadmaps..." autocomplete="off">
                <div id="search-results" class="search-results-dropdown"></div>
            </div>
        </div>

        <div class="nav-right">
            <div id="theme-toggle" class="theme-toggle" title="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </div>

            <?php if ($is_guest_page || $is_admin_login_page): ?>
                <?php elseif ($in_admin_folder && $is_admin): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fa-solid fa-user-shield"></i> Admin <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="/skillskart-php/admin/index.php">Dashboard</a>
                        
                        <?php if ($is_super_admin): ?>
                            <a href="/skillskart-php/admin/manage_users.php">Manage Users</a>
                            <a href="/skillskart-php/admin/manage_admins.php">Manage Admins</a>
                        <?php endif; ?>
                        
                        <a href="/skillskart-php/admin/logout.php" class="logout-link">Logout</a>
                    </div>
                </div>

            <?php elseif ($is_student): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fa-solid fa-user"></i> Account <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="/skillskart-php/profile.php">My Profile</a>
                        <a href="/skillskart-php/logout.php" class="logout-link">Logout</a>
                    </div>
                </div>

            <?php elseif ($is_admin): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fa-solid fa-user-shield"></i> Admin <i class="fa-solid fa-caret-down"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="/skillskart-php/admin/index.php">Dashboard</a>
                        
                        <?php if ($is_super_admin): ?>
                            <a href="/skillskart-php/admin/manage_users.php">Manage Users</a>
                            <a href="/skillskart-php/admin/manage_admins.php">Manage Admins</a>
                        <?php endif; ?>

                        <a href="/skillskart-php/admin/logout.php" class="logout-link">Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="auth-buttons">
                    <a href="/skillskart-php/login.php" class="btn-login">Login</a>
                    <a href="/skillskart-php/register.php" class="btn-register">Get Started</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container">

<script>
    const searchInput = document.getElementById('live-search');
    const resultsBox = document.getElementById('search-results');

    if(searchInput) {
        searchInput.addEventListener('input', function() {
            let query = this.value;
            if (query.length < 2) {
                resultsBox.style.display = 'none';
                return;
            }
            fetch(`/skillskart-php/core/ajax_search.php?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let html = '';
                        data.forEach(item => {
                            let icon = item.category === 'Language' ? 'fa-layer-group' : 'fa-book-open';
                            html += `
                                <a href="/skillskart-php/${item.url}" class="search-item">
                                    <div class="icon"><i class="fa-solid ${icon}"></i></div>
                                    <div class="info">
                                        <span class="title">${item.title}</span>
                                        <span class="category">${item.category}</span>
                                    </div>
                                </a>`;
                        });
                        resultsBox.innerHTML = html;
                        resultsBox.style.display = 'block';
                    } else {
                        resultsBox.innerHTML = '<div class="no-results">No results found</div>';
                        resultsBox.style.display = 'block';
                    }
                })
                .catch(err => console.error(err));
        });
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });
    }
</script>