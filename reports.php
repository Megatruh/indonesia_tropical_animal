<?php
session_start();
// Check login session
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
require 'function.php';

// Get current user data
$currentUser = getCurrentUser();
$username = $currentUser['username'] ?? 'User';
$userJob = $currentUser['job'] ?? 'Member';
$userInitials = strtoupper(substr($username, 0, 2));

// ==========================================================================================
// DATA UNTUK CHART - SESUAIKAN QUERY INI DENGAN DATABASE ANDA
// ==========================================================================================

// Query untuk menghitung jumlah species berdasarkan status konservasi
// Hasil: array dengan key 'status' dan 'total'
$statsQuery = query("SELECT status, COUNT(*) as total FROM anima_table GROUP BY status");

// Inisialisasi stats dengan nilai default 0
$stats = [
    'stable' => 0,
    'vulnerable' => 0,
    'endangered' => 0
];

// Mapping hasil query ke array stats
// CATATAN: Loop ini memproses hasil dari database
// Jika Anda memiliki status tambahan, tambahkan di array $stats di atas
foreach ($statsQuery as $row) {
    $status = strtolower($row['status']);
    if (isset($stats[$status])) {
        $stats[$status] = (int)$row['total'];
    }
}

// Total semua species
$totalSpecies = array_sum($stats);

// Query untuk data class (untuk chart kedua)
$classQuery = query("SELECT `class`, COUNT(*) as total FROM anima_table GROUP BY `class` ORDER BY total DESC LIMIT 5");

// Query untuk species terbaru
$recentSpecies = query("SELECT * FROM anima_table ORDER BY idAnima DESC LIMIT 5");

// Hitung persentase
$percentages = [];
foreach ($stats as $key => $value) {
    $percentages[$key] = $totalSpecies > 0 ? round(($value / $totalSpecies) * 100, 1) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - AnimaBase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="assets/css/reports.css">
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
                    <a href="index.php" class="nav-item">
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
                    <a href="reports.php" class="nav-item active">
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
                    <a href="index.php?filter=vulnerable" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </span>
                        <span>Vulnerable</span>
                        <span class="nav-badge"><?= $stats['vulnerable'] ?></span>
                    </a>
                    <a href="index.php?filter=endangered" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </span>
                        <span>Endangered</span>
                        <span class="nav-badge"><?= $stats['endangered'] ?></span>
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
                        <span>Reports</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <h1>Conservation Reports</h1>
                        <p>Visual analytics of Indonesian tropical animal species database</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card stable">
                        <div class="stat-number stable"><?= $stats['stable'] ?></div>
                        <div class="stat-label">Stable Species</div>
                        <div class="stat-percentage"><?= $percentages['stable'] ?>% of total</div>
                    </div>
                    <div class="stat-card vulnerable">
                        <div class="stat-number vulnerable"><?= $stats['vulnerable'] ?></div>
                        <div class="stat-label">Vulnerable Species</div>
                        <div class="stat-percentage"><?= $percentages['vulnerable'] ?>% of total</div>
                    </div>
                    <div class="stat-card endangered">
                        <div class="stat-number endangered"><?= $stats['endangered'] ?></div>
                        <div class="stat-label">Endangered Species</div>
                        <div class="stat-percentage"><?= $percentages['endangered'] ?>% of total</div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Conservation Status Doughnut Chart -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <h3 class="chart-card-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                        <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                    </svg>
                                    Conservation Status
                                </h3>
                                <span class="badge" style="background: var(--stone-100); color: var(--stone-600);">
                                    <?= $totalSpecies ?> Total
                                </span>
                            </div>
                            <div class="chart-container">
                                <canvas id="conservationChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Class Distribution Bar Chart -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <h3 class="chart-card-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="20" x2="18" y2="10"></line>
                                        <line x1="12" y1="20" x2="12" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="14"></line>
                                    </svg>
                                    Top Classes
                                </h3>
                            </div>
                            <div style="height: 250px;">
                                <canvas id="classChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Species & Additional Info -->
                <div class="row g-4">
                    <!-- Recently Added Species -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <h3 class="chart-card-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Recently Added
                                </h3>
                                <a href="index.php?sort=recent" class="btn btn-sm btn-secondary">View All</a>
                            </div>
                            <div class="recent-species-list">
                                <?php if(empty($recentSpecies)): ?>
                                <p class="text-muted text-center py-3">No species added yet</p>
                                <?php else: ?>
                                <?php foreach($recentSpecies as $species): ?>
                                <div class="recent-species-item">
                                    <img src="assets/IMG/<?= $species['image'] ?>" 
                                         alt="<?= htmlspecialchars($species['name']) ?>" 
                                         class="recent-species-img"
                                         onerror="this.src='https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=100&h=100&fit=crop'">
                                    <div class="recent-species-info">
                                        <div class="recent-species-name"><?= htmlspecialchars($species['name']) ?></div>
                                        <div class="recent-species-habitat"><?= htmlspecialchars($species['habitat']) ?></div>
                                    </div>
                                    <span class="recent-species-status <?= $species['status'] ?>">
                                        <?= ucfirst($species['status']) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-card-header">
                                <h3 class="chart-card-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <line x1="10" y1="9" x2="8" y2="9"></line>
                                    </svg>
                                    Database Summary
                                </h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">Total Species</td>
                                            <td class="text-end fw-semibold"><?= $totalSpecies ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Unique Classes</td>
                                            <td class="text-end fw-semibold"><?= count($classQuery) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Conservation Rate</td>
                                            <td class="text-end fw-semibold">
                                                <?= $totalSpecies > 0 ? round(($stats['stable'] / $totalSpecies) * 100, 1) : 0 ?>% Stable
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">At Risk Species</td>
                                            <td class="text-end fw-semibold text-warning">
                                                <?= $stats['vulnerable'] + $stats['endangered'] ?> species
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Critical Status</td>
                                            <td class="text-end fw-semibold text-danger">
                                                <?= $stats['endangered'] ?> endangered
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <script>
    // ==========================================================================================
    // CHART.JS CONFIGURATION
    // ==========================================================================================
    
    // Set default font untuk semua chart agar sesuai dengan font dashboard (Inter)
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#6b7280';

    // ==========================================================================================
    // DATA DARI PHP - INI ADALAH VARIABEL YANG TERHUBUNG KE DATABASE
    // Variabel ini diisi dari PHP $stats yang sudah di-query dari database
    // ==========================================================================================
    
    // Data untuk Conservation Status Chart
    // CATATAN: Nilai-nilai ini diambil dari variabel PHP $stats
    const conservationData = {
        // Label kategori status
        labels: ['Stable', 'Vulnerable', 'Endangered'],
        
        // Data jumlah untuk setiap kategori
        // <?= $stats['stable'] ?> = jumlah species dengan status 'stable'
        // <?= $stats['vulnerable'] ?> = jumlah species dengan status 'vulnerable'  
        // <?= $stats['endangered'] ?> = jumlah species dengan status 'endangered'
        data: [<?= $stats['stable'] ?>, <?= $stats['vulnerable'] ?>, <?= $stats['endangered'] ?>]
    };

    // Data untuk Class Distribution Chart
    // CATATAN: Data ini diambil dari query GROUP BY class
    const classData = {
        // Label class dari database
        labels: [<?php 
            $classLabels = array_map(function($h) { 
                return "'" . addslashes($h['class']) . "'"; 
            }, $classQuery);
            echo implode(', ', $classLabels);
        ?>],
        
        // Jumlah species per class
        data: [<?php 
            $classCounts = array_map(function($h) { 
                return $h['total']; 
            }, $classQuery);
            echo implode(', ', $classCounts);
        ?>]
    };

    // ==========================================================================================
    // CONSERVATION STATUS - DOUGHNUT CHART
    // ==========================================================================================
    
    const conservationCtx = document.getElementById('conservationChart').getContext('2d');
    const conservationChart = new Chart(conservationCtx, {
        type: 'doughnut', // Bisa diganti 'pie' jika mau pie chart penuh
        data: {
            labels: conservationData.labels,
            datasets: [{
                data: conservationData.data,
                // Warna: Teal untuk Stable, Oranye untuk Vulnerable, Merah Tua untuk Endangered
                backgroundColor: [
                    '#0d9488', // Teal - Stable
                    '#f59e0b', // Oranye - Vulnerable
                    '#b91c1c'  // Merah Tua - Endangered
                ],
                borderColor: [
                    '#0d9488',
                    '#f59e0b', 
                    '#b91c1c'
                ],
                borderWidth: 2,
                hoverOffset: 8,
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '60%', // Ukuran lubang tengah (0% = pie chart penuh)
            plugins: {
                legend: {
                    position: 'bottom', // Posisi legend: 'right', 'bottom', 'top', 'left'
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            family: "'Inter', sans-serif",
                            size: 13,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: {
                        family: "'Inter', sans-serif",
                        size: 14,
                        weight: '600'
                    },
                    bodyFont: {
                        family: "'Inter', sans-serif",
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                            return `${context.label}: ${context.raw} species (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true
            }
        }
    });

    // ==========================================================================================
    // CLASS DISTRIBUTION - HORIZONTAL BAR CHART
    // ==========================================================================================
    
    const classCtx = document.getElementById('classChart').getContext('2d');
    const classChart = new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classData.labels,
            datasets: [{
                label: 'Species Count',
                data: classData.data,
                backgroundColor: '#0369a1',
                borderColor: '#075985',
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal bar chart
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: {
                        family: "'Inter', sans-serif",
                        size: 14,
                        weight: '600'
                    },
                    bodyFont: {
                        family: "'Inter', sans-serif",
                        size: 13
                    },
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    },
                    ticks: {
                        font: {
                            family: "'Inter', sans-serif"
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: "'Inter', sans-serif",
                            weight: '500'
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
