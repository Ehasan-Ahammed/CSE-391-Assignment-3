<?php
require_once __DIR__ . '/session.php';
$currentUser = getCurrentUser();
$currentAdmin = getCurrentAdmin();

// Detect if current page is an admin page
$adminPages = ['admin.php', 'manage-mechanics.php'];
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdminPage = in_array($currentPage, $adminPages);
?>
<nav class="navbar">
    <div class="navbar-brand">
        <a href="index.php">
            <img src="assets/images/logo.png" alt="Car Workshop Logo" class="navbar-logo">
            <span>Car Workshop</span>
        </a>
    </div>
    
    <button class="navbar-toggler" onclick="toggleNavbar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="navbar-menu" id="navbarMenu">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo isAdmin() ? 'admin.php' : 'login.php?admin=1'; ?>" class="nav-link">
                    <i class="fas fa-tools"></i> Admin Panel
                </a>
            </li>
            
            <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a href="appointments.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i> My Appointments
                    </a>
                </li>
                
                <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a href="manage-mechanics.php" class="nav-link">
                            <i class="fas fa-user-cog"></i> Manage Mechanics
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" onclick="toggleDropdown(event)">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo htmlspecialchars($currentUser ? $currentUser['username'] : $_SESSION['user_username']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                        <li>
                            <a href="settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a href="logout.php?type=user" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout (User)
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if (isAdmin() && $isAdminPage): ?>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" onclick="toggleDropdown(event)">
                        <i class="fas fa-user-shield"></i> 
                        <?php echo htmlspecialchars($currentAdmin ? $currentAdmin['username'] : $_SESSION['admin_username']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="admin.php" class="dropdown-item">
                                <i class="fas fa-tools"></i> Admin Panel
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <a href="logout.php?type=admin" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout (Admin)
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if (!isLoggedIn() && !isAdmin()): ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a href="register.php" class="nav-link">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<style>
.navbar {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1rem 2rem;
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar-brand {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: var(--primary-color);
    font-weight: 600;
    font-size: 1.2rem;
}

.navbar-logo {
    height: 40px;
    margin-right: 0.5rem;
}

.navbar-toggler {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-color);
    cursor: pointer;
    padding: 0.5rem;
}

.navbar-menu {
    display: flex;
    align-items: center;
}

.navbar-nav {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 1rem;
}

.nav-item {
    position: relative;
}

.nav-link {
    color: var(--text-color);
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: var(--light-bg);
    color: var(--primary-color);
}

.dropdown-toggle {
    cursor: pointer;
}

.dropdown-toggle i.fa-chevron-down {
    font-size: 0.8rem;
    margin-left: 0.5rem;
    transition: transform 0.3s ease;
}

.dropdown-toggle.active i.fa-chevron-down {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    min-width: 200px;
    padding: 0.5rem 0;
    display: none;
    z-index: 1000;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    color: var(--text-color);
    text-decoration: none;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: var(--light-bg);
    color: var(--primary-color);
}

.dropdown-divider {
    height: 1px;
    background: #eee;
    margin: 0.5rem 0;
}

.text-danger {
    color: var(--error-color);
}

.text-danger:hover {
    color: var(--error-color);
    background: rgba(220, 53, 69, 0.1);
}

@media (max-width: 768px) {
    .navbar-toggler {
        display: block;
    }

    .navbar-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: none;
    }

    .navbar-menu.show {
        display: block;
    }

    .navbar-nav {
        flex-direction: column;
        gap: 0.5rem;
    }

    .nav-link {
        padding: 0.75rem 1rem;
    }

    .dropdown-menu {
        position: static;
        box-shadow: none;
        padding-left: 1rem;
    }
}
</style>

<script>
function toggleNavbar() {
    const menu = document.getElementById('navbarMenu');
    menu.classList.toggle('show');
}

function toggleDropdown(event) {
    event.preventDefault();
    const dropdown = event.target.closest('.dropdown');
    const menu = dropdown.querySelector('.dropdown-menu');
    const toggle = dropdown.querySelector('.dropdown-toggle');
    
    menu.classList.toggle('show');
    toggle.classList.toggle('active');
}

// Close dropdowns when clicking outside
document.addEventListener('click', (event) => {
    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
        document.querySelectorAll('.dropdown-toggle.active').forEach(toggle => {
            toggle.classList.remove('active');
        });
    }
});

// Close mobile menu when clicking outside
document.addEventListener('click', (event) => {
    const menu = document.getElementById('navbarMenu');
    const toggler = document.querySelector('.navbar-toggler');
    
    if (!event.target.closest('.navbar-menu') && !event.target.closest('.navbar-toggler')) {
        menu.classList.remove('show');
    }
});
</script> 