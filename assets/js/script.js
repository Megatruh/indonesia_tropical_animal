/**
 * Tropical Plant Species Dashboard
 * Interactive JavaScript Functions
 */

document.addEventListener("DOMContentLoaded", function () {
  // Initialize all components
  initSidebar();
  initFilterChips();
  initViewToggle();
  initFavoriteButtons();
  initSearchBox();
  initAnimations();
  initMobileMenu();
});

/**
 * Sidebar Navigation
 */
function initSidebar() {
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  // Add active state to current nav item based on URL
  const currentPage = window.location.pathname.split("/").pop() || "index.php";
  const navItems = document.querySelectorAll(".nav-item");

  navItems.forEach((item) => {
    const href = item.getAttribute("href");
    if (href === currentPage) {
      item.classList.add("active");
    } else if (currentPage === "" && href === "index.php") {
      item.classList.add("active");
    }
  });
}

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
  // Create mobile menu button if on mobile
  if (window.innerWidth <= 992) {
    const header = document.querySelector(".top-header");
    const sidebar = document.querySelector(".sidebar");

    if (header && !document.querySelector(".mobile-menu-btn")) {
      const menuBtn = document.createElement("button");
      menuBtn.className = "header-btn mobile-menu-btn";
      menuBtn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            `;
      header.querySelector(".header-left").prepend(menuBtn);

      menuBtn.addEventListener("click", () => {
        sidebar.classList.toggle("open");
      });

      // Close sidebar when clicking outside
      document.addEventListener("click", (e) => {
        if (
          sidebar.classList.contains("open") &&
          !sidebar.contains(e.target) &&
          !menuBtn.contains(e.target)
        ) {
          sidebar.classList.remove("open");
        }
      });
    }
  }
}

/**
 * Filter Chips Interaction
 */
function initFilterChips() {
  const filterChips = document.querySelectorAll(".filter-chip");
  const plantCards = document.querySelectorAll(".plant-card");

  filterChips.forEach((chip) => {
    chip.addEventListener("click", function () {
      // Remove active class from all chips
      filterChips.forEach((c) => c.classList.remove("active"));
      // Add active to clicked chip
      this.classList.add("active");

      const filter = this.textContent.toLowerCase().trim();

      plantCards.forEach((card) => {
        const badge = card.querySelector(".habitat-badge");
        const status = badge ? badge.textContent.toLowerCase().trim() : "";

        if (filter === "all") {
          card.style.display = "";
          card.classList.add("fade-in");
        } else if (status.includes(filter)) {
          card.style.display = "";
          card.classList.add("fade-in");
        } else {
          card.style.display = "none";
        }
      });
    });
  });
}

/**
 * View Toggle (Grid/List)
 */
function initViewToggle() {
  const viewBtns = document.querySelectorAll(".view-btn");
  const plantsGrid = document.querySelector(".plants-grid");

  viewBtns.forEach((btn, index) => {
    btn.addEventListener("click", function () {
      viewBtns.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");

      if (index === 0) {
        // Grid view
        plantsGrid.style.gridTemplateColumns =
          "repeat(auto-fill, minmax(300px, 1fr))";
        document.querySelectorAll(".plant-card").forEach((card) => {
          card.classList.remove("list-view");
        });
      } else {
        // List view
        plantsGrid.style.gridTemplateColumns = "1fr";
        document.querySelectorAll(".plant-card").forEach((card) => {
          card.classList.add("list-view");
        });
      }
    });
  });
}

/**
 * Favorite Buttons
 */
function initFavoriteButtons() {
  const favButtons = document.querySelectorAll(".favorite-btn");

  favButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      this.classList.toggle("active");

      const svg = this.querySelector("svg");
      if (this.classList.contains("active")) {
        svg.setAttribute("fill", "currentColor");
        showToast("Added to favorites!", "success");
      } else {
        svg.setAttribute("fill", "none");
        showToast("Removed from favorites", "info");
      }
    });
  });
}

/**
 * Search Box Functionality
 */
function initSearchBox() {
  const searchInput = document.querySelector(".search-box input");
  const plantCards = document.querySelectorAll(".plant-card");

  if (searchInput) {
    let debounceTimer;

    searchInput.addEventListener("input", function () {
      clearTimeout(debounceTimer);

      debounceTimer = setTimeout(() => {
        const searchTerm = this.value.toLowerCase().trim();

        plantCards.forEach((card) => {
          const name =
            card.querySelector("h3")?.textContent.toLowerCase() || "";
          const scientificName =
            card.querySelector(".scientific-name")?.textContent.toLowerCase() ||
            "";
          const description =
            card
              .querySelector(".plant-card-description")
              ?.textContent.toLowerCase() || "";
          const habitat =
            card.querySelector(".meta-item")?.textContent.toLowerCase() || "";

          const matches =
            name.includes(searchTerm) ||
            scientificName.includes(searchTerm) ||
            description.includes(searchTerm) ||
            habitat.includes(searchTerm);

          if (matches || searchTerm === "") {
            card.style.display = "";
            card.classList.add("fade-in");
          } else {
            card.style.display = "none";
          }
        });

        // Check if no results
        const visibleCards = document.querySelectorAll(
          '.plant-card[style=""], .plant-card:not([style])',
        );
        const emptyState = document.querySelector(".empty-state");

        if (visibleCards.length === 0 && searchTerm !== "") {
          if (!document.querySelector(".no-results")) {
            const noResults = document.createElement("div");
            noResults.className = "empty-state no-results";
            noResults.style.gridColumn = "1 / -1";
            noResults.innerHTML = `
                            <div class="empty-state-icon">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <h3>No Results Found</h3>
                            <p>No species match your search "${searchTerm}". Try different keywords.</p>
                        `;
            document.querySelector(".plants-grid").appendChild(noResults);
          }
        } else {
          const noResults = document.querySelector(".no-results");
          if (noResults) noResults.remove();
        }
      }, 300);
    });
  }
}

/**
 * Scroll Animations
 */
function initAnimations() {
  // Intersection Observer for card animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in");
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll(".plant-card, .stat-card").forEach((card) => {
    observer.observe(card);
  });
}

/**
 * Toast Notification
 */
function showToast(message, type = "info") {
  // Remove existing toasts
  const existingToast = document.querySelector(".toast-notification");
  if (existingToast) existingToast.remove();

  const toast = document.createElement("div");
  toast.className = `toast-notification toast-${type}`;
  toast.innerHTML = `
        <span class="toast-icon">
            ${
              type === "success"
                ? `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            `
                : `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            `
            }
        </span>
        <span class="toast-message">${message}</span>
    `;

  // Add toast styles
  toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        background: ${type === "success" ? "#059669" : "#2563eb"};
        color: white;
        border-radius: 0.75rem;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        z-index: 9999;
        animation: slideInUp 0.3s ease;
        font-size: 0.9375rem;
        font-weight: 500;
    `;

  document.body.appendChild(toast);

  // Auto remove after 3 seconds
  setTimeout(() => {
    toast.style.animation = "slideOutDown 0.3s ease forwards";
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/**
 * Confirm Delete
 */
function confirmDelete(id, name) {
  const modal = document.createElement("div");
  modal.className = "modal-overlay active";
  modal.innerHTML = `
        <div class="modal">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>"${name}"</strong>? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                <a href="delete.php?id=${id}" class="btn btn-danger">Delete Species</a>
            </div>
        </div>
    `;

  document.body.appendChild(modal);

  // Close on outside click
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.remove();
  });

  return false;
}

// Add CSS animations via JavaScript
const style = document.createElement("style");
style.textContent = `
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideOutDown {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(20px);
        }
    }
    
    .plant-card.list-view {
        display: grid;
        grid-template-columns: 200px 1fr;
    }
    
    .plant-card.list-view .plant-card-image {
        height: 100%;
        min-height: 180px;
    }
    
    .plant-card.list-view .plant-card-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .plant-card.list-view {
            grid-template-columns: 1fr;
        }
        
        .toast-notification {
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
        }
    }
`;
document.head.appendChild(style);