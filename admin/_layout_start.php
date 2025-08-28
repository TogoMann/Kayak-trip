<?php
require_once __DIR__ . '/_guard.php';
if (!isset($page_title))
    $page_title = 'Admin';
if (!isset($active))
    $active = '';
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #0b1220
        }

        .sidebar {
            width: 260px;
            background: #0f172a;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0
        }

        .sidebar .brand {
            color: #fff;
            padding: 20px;
            font-weight: 700;
            letter-spacing: .5px
        }

        .sidebar a {
            color: #cbd5e1
        }

        .sidebar a.active,
        .sidebar a:hover {
            color: #fff
        }

        .content {
            margin-left: 260px
        }

        .card {
            background: #111827;
            border: 0;
            color: #e5e7eb
        }

        .topbar {
            background: #0b1220;
            border-bottom: 1px solid rgba(255, 255, 255, .08)
        }

        .table thead th {
            color: #9ca3af;
            border-color: rgba(255, 255, 255, .08)
        }

        .table td {
            vertical-align: middle;
            border-color: rgba(255, 255, 255, .08)
        }

        .cover {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: .5rem;
            border: 1px solid rgba(255, 255, 255, .08)
        }
    </style>
</head>

<body>
    <div class="sidebar d-flex flex-column">
        <div class="brand d-flex align-items-center"><span><i class="bi bi-water"></i> Admin Kayak</span></div>
        <div class="px-3">
            <ul class="nav flex-column gap-1">
                <li class="nav-item"><a class="nav-link<?php echo $active === 'dashboard' ? ' active' : ''; ?>"
                        href="/admin/"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'users' ? ' active' : ''; ?>"
                        href="/admin/users.php"><i class="bi bi-people me-2"></i>Utilisateurs</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'reservations' ? ' active' : ''; ?>"
                        href="/admin/reservations.php"><i class="bi bi-calendar-check me-2"></i>Réservations</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'points' ? ' active' : ''; ?>"
                        href="/admin/points.php"><i class="bi bi-geo-alt me-2"></i>Points d’arrêt</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'hebergements' ? ' active' : ''; ?>"
                        href="/admin/hebergements.php"><i class="bi bi-house-door me-2"></i>Hébergements</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'services' ? ' active' : ''; ?>"
                        href="/admin/options.php"><i class="bi bi-bag-check me-2"></i>Services</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'categories' ? ' active' : ''; ?>"
                        href="/admin/categories.php"><i class="bi bi-tags me-2"></i>Catégories</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $active === 'packs' ? ' active' : ''; ?>"
                        href="/admin/packs.php"><i class="bi bi-layers me-2"></i>Packs</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active==='promos'?'active':''; ?>" 
                        href="/admin/promos.php"><i class="bi bi-percent me-2"></i>Codes promos</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active==='chat'?'active':''; ?>"   
                        href="/admin/chat.php"><i class="bi bi-chat-dots me-2"></i>Chat</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $active==='support'?'active':''; ?>" 
                        href="/admin/support.php"><i class="bi bi-life-preserver me-2"></i>Support</a></li>
            </ul>
        </div>
        <div class="mt-auto p-3">
            <a href="/process/logout.php" class="btn btn-outline-light w-100"><i
                    class="bi bi-box-arrow-right me-2"></i>Déconnexion</a>
        </div>
    </div>
    <div class="content">
        <div class="topbar py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="text-white fw-semibold"><?php echo htmlspecialchars($page_title); ?></div>
            <div class="d-flex align-items-center gap-2"><a href="/" class="btn btn-light"><i
                        class="bi bi-eye me-1"></i>Mode public</a></div>
        </div>
        <div class="container-fluid py-4">