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

// Handle form submission
if (isset($_POST["submit"])) {
    if (tambahHewan($_POST) > 0) {
        echo "<script>
            alert('Species added successfully!');
            document.location.href = 'index.php';
        </script>";
    } else {
        echo "<script>alert('Failed to add species!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Species - AnimaBase</title>
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
                    <a href="create.php" class="nav-item active">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </span>
                        <span>Add Species</span>
                    </a>
                    <a href="reports.php" class="nav-item">
                        <span class="nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </span>
                        <span>Reports</span>
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
                        <a href="index.php">Dashboard</a>
                        <span>/</span>
                        <span>Add Species</span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <h1>Add New Species</h1>
                        <p>Add a new tropical animal species to the database</p>
                    </div>
                    <div class="page-actions">
                        <a href="index.php" class="btn btn-secondary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="4" r="2"></circle>
                                <circle cx="18" cy="8" r="2"></circle>
                                <circle cx="20" cy="16" r="2"></circle>
                                <path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3>Species Information</h3>
                            <p>Fill in the details of the animal species</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <!-- Basic Information Section -->
                            <div class="form-section">
                                <h4 class="form-section-title">Basic Information</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Species Name <span class="required">*</span></label>
                                            <input type="text" class="form-input" id="name" name="name" autocomplete="off" placeholder="e.g., Komodo Dragon" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status" class="form-label">Conservation Status <span class="required">*</span></label>
                                            <select class="form-input" id="status" name="status" required>
                                                <option value="">Select status...</option>
                                                <option value="stable">Stable</option>
                                                <option value="vulnerable">Vulnerable</option>
                                                <option value="endangered">Endangered</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="habitat" class="form-label">Habitat <span class="required">*</span></label>
                                    <input type="text" class="form-input" id="habitat" autocomplete="off" name="habitat" placeholder="e.g., Tropical Rainforest, Coral Reef" required>
                                </div>
                                <div class="form-group">
                                    <label for="describe" class="form-label">Description <span class="required">*</span></label>
                                    <textarea class="form-input form-textarea" id="describe" name="describe" placeholder="Describe the species characteristics, behavior, and unique features..." required></textarea>
                                    <span class="form-hint">Provide a detailed description of the animal species</span>
                                </div>
                            </div>

                            <!-- Planting Information Section -->
                            <div class="form-section">
                                <h4 class="form-section-title">Planting Information</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nationalPlanting" class="form-label">National Planting</label>
                                            <textarea class="form-input form-textarea" id="nationalPlanting" name="nationalPlanting" placeholder="Describe national planting locations and programs..."></textarea>
                                            <span class="form-hint">Conservation efforts within Indonesia</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="internationalPlanting" class="form-label">International Planting</label>
                                            <textarea class="form-input form-textarea" id="internationalPlanting" name="internationalPlanting" placeholder="Describe international planting locations and programs..."></textarea>
                                            <span class="form-hint">Conservation efforts worldwide</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Taxonomy Section -->
                            <div class="form-section">
                                <h4 class="form-section-title">Taxonomy Classification</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="kingdom" class="form-label">Kingdom</label>
                                            <input type="text" class="form-input" id="kingdom" name="kingdom" value="Animalia" placeholder="e.g., Animalia" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phylum" class="form-label">Phylum</label>
                                            <input type="text" class="form-input" id="pylum" name="pylum" value="Chordata" placeholder="e.g., Chordata" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="class" class="form-label">Class</label>
                                            <input type="text" class="form-input" id="class" name="class" placeholder="e.g., Mammalia, Reptilia" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ordo" class="form-label">Order</label>
                                            <input type="text" class="form-input" id="ordo" name="ordo" placeholder="e.g., Primates, Carnivora" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="famili" class="form-label">Family</label>
                                            <input type="text" class="form-input" id="famili" name="famili" placeholder="e.g., Felidae, Varanidae" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="genus" class="form-label">Genus</label>
                                            <input type="text" class="form-input" id="genus" name="genus" placeholder="e.g., Panthera, Varanus" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="form-section">
                                <h4 class="form-section-title">Species Image</h4>
                                <div class="form-group">
                                    <label for="gambar" class="form-label">Upload Image <span class="required">*</span></label>
                                    <div class="file-upload-wrapper">
                                        <input type="file" class="form-input file-input" id="gambar" name="gambar" accept="image/jpeg,image/jpg,image/png" required>
                                        <div class="file-upload-preview" id="imagePreview">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21 15 16 10 5 21"></polyline>
                                            </svg>
                                            <span>Click to upload or drag and drop</span>
                                            <small>JPG, JPEG, PNG (Max 5MB)</small>
                                        </div>
                                    </div>
                                    <span class="form-hint">Upload a clear image of the species</span>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <a href="index.php" class="btn btn-secondary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                    Cancel
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                        <polyline points="7 3 7 8 15 8"></polyline>
                                    </svg>
                                    Save Species
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <script>
    // Image preview functionality
    const fileInput = document.getElementById('gambar');
    const imagePreview = document.getElementById('imagePreview');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 8px;">`;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>
