<?php
$role_title = ucfirst($_SESSION['role']);
$username = htmlspecialchars($_SESSION['user']);
// Formatted Date
$date_now = date('d M Y');
// Try to get dynamic page title, default to Dashboard
$page_title = $page_title ?? 'Dashboard'; 
?>
<!-- Topbar Header -->
<header class="topbar">
    <h1 class="topbar-title"><?= $page_title ?></h1>
    
    <div class="topbar-user">
        <div class="user-role-badge">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <?= $username ?> (<?= $role_title ?>)
        </div>
        <div class="current-date">
            <?= $date_now ?>
        </div>
    </div>
</header>
