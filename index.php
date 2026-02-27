<?php
session_start();
// jika tidak ada session login, maka harus ke halaman login dulu 
if ( !isset ($_SESSION["login"])){
    header("Location: login.php");
    exit;
}
require 'function.php';

// Get current user data
$currentUser = getCurrentUser();
$username = $currentUser['username'] ?? 'User';
$userJob = $currentUser['job'] ?? 'Member';
$userInitials = strtoupper(substr($username, 0, 2));

// Pagination settings - responsive limits
// Desktop: 8, Tablet: 6, Mobile: 4
$defaultLimits = ['desktop' => 8, 'tablet' => 6, 'mobile' => 4];
$deviceType = $_COOKIE['device_type'] ?? 'desktop';
$perPage = $defaultLimits[$deviceType] ?? 8;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Filter by status
$filter = $_GET['filter'] ?? 'all';
$validFilters = ['all', 'stable', 'vulnerable', 'endangered'];
if (!in_array($filter, $validFilters)) $filter = 'all';

// Sort options
$sort = $_GET['sort'] ?? 'recent';
$validSorts = ['recent', 'az', 'za'];
if (!in_array($sort, $validSorts)) $sort = 'recent';

// Build WHERE clause
$whereClause = '';
if ($filter !== 'all') {
    $filter = mysqli_real_escape_string($conn, $filter);
    $whereClause = "WHERE status = '$filter'";
}

// Build ORDER BY clause
$orderClause = match($sort) {
    'az' => 'ORDER BY name ASC',
    'za' => 'ORDER BY name DESC',
    default => 'ORDER BY idAnima DESC'
};

// Get total count with filter
$totalData = query("SELECT COUNT(*) as total FROM anima_table $whereClause")[0]['total'];
$totalPages = ceil($totalData / $perPage);
if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

$offset = ($page - 1) * $perPage;
$row = query("SELECT * FROM anima_table $whereClause $orderClause LIMIT $perPage OFFSET $offset");
$totalPlants = $totalData;

// Get sidebar badge counts from ALL data (unfiltered)
$sidebarCounts = query("SELECT status, COUNT(*) as total FROM anima_table GROUP BY status");
$statusCounts = [
    'stable' => 0,
    'vulnerable' => 0,
    'endangered' => 0
];
foreach ($sidebarCounts as $sc) {
    if (isset($statusCounts[$sc['status']])) {
        $statusCounts[$sc['status']] = (int)$sc['total'];
    }
}

// livesearch
if(isset($_POST["cari"])){
    $row = cari($_POST["keyword"]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnimaBase: Indonesian Tropical Animal Species Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="4" r="2"></circle>
                        <circle cx="18" cy="8" r="2"></circle>
                        <circle cx="20" cy="16" r="2"></circle>
                        <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
                    </svg>
                </div>
                <div class="sidebar-brand">
                    <h2>AnimaBase</h2>
                    <span>Animal Database</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="index.php" class="nav-item <?= $filter === 'all' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="9"></rect>
                                <rect x="14" y="3" width="7" height="5"></rect>
                                <rect x="14" y="12" width="7" height="9"></rect>
                                <rect x="3" y="16" width="7" height="5"></rect>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>
                    <a href="create.php" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </span>
                        <span>Add Species</span>
                    </a>
                    <a href="reports.php" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </span>
                        <span>Reports</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Conservation</div>
                    <a href="?filter=vulnerable&sort=<?= $sort ?>" class="nav-item <?= $filter === 'vulnerable' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </span>
                        <span>Vulnerable</span>
                        <span class="nav-badge">
                            <?= $statusCounts['vulnerable'] ?>
                        </span>
                    </a>
                    <a href="?filter=endangered&sort=<?= $sort ?>" class="nav-item <?= $filter === 'endangered' ? 'active' : '' ?>">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </span>
                        <span>Endangered</span>
                        <span class="nav-badge">
                            <?= $statusCounts['endangered'] ?>
                        </span>
                    </a>
                    
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="logout.php" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </span>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?= $userInitials ?></div>
                    <div class="user-info">
                        <h4><?= htmlspecialchars(ucfirst($username)) ?></h4>
                        <span><?= htmlspecialchars($userJob) ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <div class="breadcrumb">
                        <a href="index.php">Home</a>
                        <span>/</span>
                        <span>Dashboard</span>
                    </div>
                    
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <span class="search-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" id="searchInput" placeholder="Search animals, habitats, or species..." autocomplete="off" aria-label="Search">
                        <div id="searchResults" class="search-results-dropdown"></div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <h1>Indonesian Tropical Animal Species</h1>
                        <p>Manage and explore the Indonesian tropical animal database</p>
                    </div>
                    <div class="page-actions">
                        <a href="create.php" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add New Species
                        </a>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-chips">
                        <a href="?filter=all&sort=<?= $sort ?>" class="filter-chip <?= $filter === 'all' ? 'active' : '' ?>">All</a>
                        <a href="?filter=stable&sort=<?= $sort ?>" class="filter-chip <?= $filter === 'stable' ? 'active' : '' ?>">Stable</a>
                        <a href="?filter=vulnerable&sort=<?= $sort ?>" class="filter-chip <?= $filter === 'vulnerable' ? 'active' : '' ?>">Vulnerable</a>
                        <a href="?filter=endangered&sort=<?= $sort ?>" class="filter-chip <?= $filter === 'endangered' ? 'active' : '' ?>">Endangered</a>
                    </div>
                    <div class="filter-group" style="margin-left: auto;">
                        <span class="filter-label">Sort:</span>
                        <select class="filter-select" onchange="window.location.href='?filter=<?= $filter ?>&sort='+this.value">
                            <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Recently Added</option>
                            <option value="az" <?= $sort === 'az' ? 'selected' : '' ?>>Name (A-Z)</option>
                            <option value="za" <?= $sort === 'za' ? 'selected' : '' ?>>Name (Z-A)</option>
                        </select>
                    </div>
                </div>

                <!-- Animals Grid Section -->
                <div class="cards-section">
                    <div class="section-header">
                        <div class="section-title">
                            <h2>Species Collection</h2>
                            <span class="badge"><?= $totalPlants ?> animals</span>
                        </div>            
                    </div>

                    <!-- Animal Cards Grid -->
                    <div class="animals-grid">
                        <?php $i = 0; foreach($row as $r) : 
                            $status = $r['status'];
                            $habitat = $r['habitat'];
                        ?>
                        <div class="animal-card fade-in" style="animation-delay: <?= $i * 0.1 ?>s">
                            <div class="animal-card-image">
                                <div class="animal-card-badge">
                                    <span class="habitat-badge <?= $status ?>">
                                        <?php if($status === 'endangered'): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                            </svg>
                                        <?php elseif($status === 'vulnerable'): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                        <?php else: ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        <?php endif; ?>
                                        <?= ucfirst($status) ?>
                                    </span>
                                </div>
                                <img src="assets/IMG/<?= $r['image'] ?>" alt="<?= htmlspecialchars($r['name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=400&h=300&fit=crop'">
                            </div>
                            <div class="animal-card-content">
                                <div class="animal-card-header">
                                    <h3><?= htmlspecialchars($r['name']) ?></h3>
                                    <span class="scientific-name"><?= htmlspecialchars($r['name']) ?> sp.</span>
                                </div>
                                <div class="animal-card-meta">
                                    <span class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <?= $habitat ?>
                                    </span>

                                </div>
                                <p class="animal-card-description">
                                    <?= htmlspecialchars($r['describe']) ?>
                                </p>
                                <div class="animal-card-actions">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#speciesModal"
                                        data-name="<?= htmlspecialchars($r['name']) ?>"
                                        data-image="assets/IMG/<?= $r['image'] ?>"
                                        data-habitat="<?= htmlspecialchars($r['habitat'] ?? $habitat) ?>"
                                        data-describe="<?= htmlspecialchars($r['describe']) ?>"
                                        data-kingdom="<?= htmlspecialchars($r['kingdom'] ?? 'Animalia') ?>"
                                        data-phylum="<?= htmlspecialchars($r['pylum'] ?? 'Chordata') ?>"
                                        data-class="<?= htmlspecialchars($r['class'] ?? 'Mammalia') ?>"
                                        data-ordo="<?= htmlspecialchars($r['ordo'] ?? '-') ?>"
                                        data-family="<?= htmlspecialchars($r['famili'] ?? '-') ?>"  
                                        data-genus="<?= htmlspecialchars($r['genus'] ?? '-') ?>"
                                        data-status="<?= htmlspecialchars($status) ?>"
                                        data-national="<?= htmlspecialchars($r['nationalPlanting'] ?? '-') ?>"
                                        data-international="<?= htmlspecialchars($r['internationalPlanting'] ?? '-') ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        View
                                    </button>
                                    <a href="update.php?slug=<?= $r['slug'] ?>" class="btn btn-secondary btn-sm">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit
                                    </a>
                                    <a href="delete.php?slug=<?= $r['slug'] ?>" class="btn btn-outline btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this species?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php $i++; endforeach; ?>

                        <?php if(empty($row)): ?>
                        <!-- Empty State -->
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <div class="empty-state-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="4" r="2"></circle>
                                    <circle cx="18" cy="8" r="2"></circle>
                                    <circle cx="20" cy="16" r="2"></circle>
                                    <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
                                </svg>
                            </div>
                            <h3>No Species Found</h3>
                            <p>Start by adding your first tropical animal species to the database.</p>
                            <a href="create.php" class="btn btn-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Add First Species
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if($totalPages > 1): ?>
                    <div class="pagination">
                        <a href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>&sort=<?= $sort ?>" class="pagination-btn <?= $page <= 1 ? 'disabled' : '' ?>" <?= $page <= 1 ? 'onclick="return false;"' : '' ?>>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </a>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&filter=<?= $filter ?>&sort=<?= $sort ?>" class="pagination-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <a href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>&sort=<?= $sort ?>" class="pagination-btn <?= $page >= $totalPages ? 'disabled' : '' ?>" <?= $page >= $totalPages ? 'onclick="return false;"' : '' ?>>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Species Detail Modal -->
    <div class="modal fade" id="speciesModal" tabindex="-1" aria-labelledby="speciesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content species-modal">
                <!-- Modal Header -->
                <div class="modal-header species-modal-header">
                    <div class="modal-title-wrapper">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="4" r="2"></circle>
                            <circle cx="18" cy="8" r="2"></circle>
                            <circle cx="20" cy="16" r="2"></circle>
                            <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
                        </svg>
                        <h5 class="modal-title" id="speciesModalLabel">Animal Name</h5>
                    </div>
                    <span class="modal-status-badge" id="modalStatusBadge">Stable</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body species-modal-body">
                    <div class="row g-4">
                        <!-- Left Column: Animal Image -->
                        <div class="col-md-5">
                            <div class="species-image-wrapper">
                                <img id="modalAnimalImage" src="" alt="Animal Image" class="species-detail-image">
                            </div>
                        </div>
                        
                        <!-- Right Column: Taxonomy Table -->
                        <div class="col-md-7">
                            <div class="taxonomy-section">
                                <h6 class="taxonomy-title">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    Taxonomic Classification
                                </h6>
                                <table class="taxonomy-table">
                                    <tbody>
                                        <tr>
                                            <td class="taxonomy-label">Kingdom</td>
                                            <td class="taxonomy-value" id="modalKingdom">Animalia</td>
                                        </tr>
                                        <tr>
                                            <td class="taxonomy-label">Phylum</td>
                                            <td class="taxonomy-value" id="modalPhylum">Chordata</td>
                                        </tr>
                                        <tr>
                                            <td class="taxonomy-label">Class</td>
                                            <td class="taxonomy-value" id="modalClass">Mammalia</td>
                                        </tr>
                                        <tr>
                                            <td class="taxonomy-label">Ordo</td>
                                            <td class="taxonomy-value" id="modalOrdo">-</td>
                                        </tr>
                                        <tr>
                                            <td class="taxonomy-label">Family</td>
                                            <td class="taxonomy-value" id="modalFamily">-</td>
                                        </tr>
                                        <tr>
                                            <td class="taxonomy-label">Genus</td>
                                            <td class="taxonomy-value scientific-name" id="modalGenus">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bottom Section: Habitat & Description -->
                    <div class="species-info-section">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6 class="info-card-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        Habitat
                                    </h6>
                                    <p class="info-card-text" id="modalHabitat">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6 class="info-card-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="17" y1="10" x2="3" y2="10"></line>
                                            <line x1="21" y1="6" x2="3" y2="6"></line>
                                            <line x1="21" y1="14" x2="3" y2="14"></line>
                                            <line x1="17" y1="18" x2="3" y2="18"></line>
                                        </svg>
                                        Description
                                    </h6>
                                    <p class="info-card-text" id="modalDescribe">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6 class="info-card-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3v18h18"></path>
                                            <path d="m19 9-5 5-4-4-3 3"></path>
                                        </svg>
                                        National Planting
                                    </h6>
                                    <p class="info-card-text" id="modalNational">-</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6 class="info-card-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" y1="12" x2="22" y2="12"></line>
                                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                        </svg>
                                        International Planting
                                    </h6>
                                    <p class="info-card-text" id="modalInternational">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer species-modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <script>
    // Species Modal Data Population
    const speciesModal = document.getElementById('speciesModal');
    if (speciesModal) {
        speciesModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Extract data from button attributes
            const name = button.getAttribute('data-name');
            const image = button.getAttribute('data-image');
            const habitat = button.getAttribute('data-habitat');
            const describe = button.getAttribute('data-describe');
            const kingdom = button.getAttribute('data-kingdom');
            const phylum = button.getAttribute('data-phylum');
            const plantClass = button.getAttribute('data-class');
            const ordo = button.getAttribute('data-ordo');
            const family = button.getAttribute('data-family');
            const genus = button.getAttribute('data-genus');
            const status = button.getAttribute('data-status');
            const nationalPlanting = button.getAttribute('data-national');
            const internationalPlanting = button.getAttribute('data-international');
            
            // Update modal content
            document.getElementById('speciesModalLabel').textContent = name;
            document.getElementById('modalAnimalImage').src = image;
            document.getElementById('modalAnimalImage').alt = name;
            
            // Update taxonomy table
            document.getElementById('modalKingdom').textContent = kingdom || 'Animalia';
            document.getElementById('modalPhylum').textContent = phylum || 'Chordata';
            document.getElementById('modalClass').textContent = plantClass || 'Mammalia';
            document.getElementById('modalOrdo').textContent = ordo || '-';
            document.getElementById('modalFamily').textContent = family || '-';
            document.getElementById('modalGenus').textContent = genus || '-';
            
            // Update habitat and description
            document.getElementById('modalHabitat').textContent = habitat || '-';
            document.getElementById('modalDescribe').textContent = describe || '-';
            document.getElementById('modalNational').textContent = nationalPlanting || '-';
            document.getElementById('modalInternational').textContent = internationalPlanting || '-';
            
            // Update status badge
            const statusBadge = document.getElementById('modalStatusBadge');
            statusBadge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown';
            statusBadge.className = 'modal-status-badge ' + (status || 'unknown');
        });
    }

    // Responsive pagination - detect device and set cookie
    (function() {
        function getDeviceType() {
            const width = window.innerWidth;
            if (width >= 1024) return 'desktop';  // 8 items
            if (width >= 768) return 'tablet';     // 6 items
            return 'mobile';                        // 4 items
        }
        
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        const currentDevice = getDeviceType();
        const savedDevice = getCookie('device_type');
        
        if (savedDevice !== currentDevice) {
            document.cookie = `device_type=${currentDevice}; path=/; max-age=86400`;
            // Only reload if cookie was previously set (not first visit)
            if (savedDevice !== null) {
                window.location.href = 'index.php?page=1';
            } else {
                // First visit - set cookie and reload to apply
                window.location.reload();
            }
        }
    })();

    // Live Search functionality
    (function() {
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(debounceTimer);
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`search_api.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
                        } else {
                            searchResults.innerHTML = data.map(item => `
                                <div class="search-result-item" 
                                     data-name="${escapeHtml(item.name)}"
                                     data-image="assets/IMG/${item.image}"
                                     data-habitat="${escapeHtml(item.habitat)}"
                                     data-describe="${escapeHtml(item.describe)}"
                                     data-kingdom="${escapeHtml(item.kingdom)}"
                                     data-phylum="${escapeHtml(item.pylum)}"
                                     data-class="${escapeHtml(item.class)}"
                                     data-ordo="${escapeHtml(item.ordo)}"
                                     data-family="${escapeHtml(item.famili)}"
                                     data-genus="${escapeHtml(item.genus)}"
                                     data-status="${escapeHtml(item.status)}"
                                     data-national="${escapeHtml(item.nationalPlanting)}"
                                     data-international="${escapeHtml(item.internationalPlanting)}">
                                    <img src="assets/IMG/${item.image}" alt="${escapeHtml(item.name)}" class="search-result-img" onerror="this.src='https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=50&h=50&fit=crop'">
                                    <div class="search-result-info">
                                        <span class="search-result-name">${escapeHtml(item.name)}</span>
                                        <span class="search-result-meta">
                                            <span class="status-dot ${item.status}"></span>
                                            ${item.habitat}
                                        </span>
                                    </div>
                                </div>
                            `).join('');
                        }
                        searchResults.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                        searchResults.innerHTML = '<div class="search-no-results">Error searching</div>';
                        searchResults.style.display = 'block';
                    });
            }, 300);
        });

        // Click on search result to open modal
        searchResults.addEventListener('click', function(e) {
            const item = e.target.closest('.search-result-item');
            if (item) {
                // Populate modal with data
                document.getElementById('speciesModalLabel').textContent = item.dataset.name;
                document.getElementById('modalAnimalImage').src = item.dataset.image;
                document.getElementById('modalAnimalImage').alt = item.dataset.name;
                document.getElementById('modalKingdom').textContent = item.dataset.kingdom || 'Animalia';
                document.getElementById('modalPhylum').textContent = item.dataset.phylum || 'Chordata';
                document.getElementById('modalClass').textContent = item.dataset.class || 'Mammalia';
                document.getElementById('modalOrdo').textContent = item.dataset.ordo || '-';
                document.getElementById('modalFamily').textContent = item.dataset.family || '-';
                document.getElementById('modalGenus').textContent = item.dataset.genus || '-';
                document.getElementById('modalHabitat').textContent = item.dataset.habitat || '-';
                document.getElementById('modalDescribe').textContent = item.dataset.describe || '-';
                document.getElementById('modalNational').textContent = item.dataset.national || '-';
                document.getElementById('modalInternational').textContent = item.dataset.international || '-';
                
                const statusBadge = document.getElementById('modalStatusBadge');
                const status = item.dataset.status;
                statusBadge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown';
                statusBadge.className = 'modal-status-badge ' + (status || 'unknown');

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('speciesModal'));
                modal.show();

                // Hide search results
                searchResults.style.display = 'none';
                searchInput.value = '';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    })();
    </script>
</body>
</html>