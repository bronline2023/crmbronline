<?php
/**
 * views/includes/sidebar.php
 *
 * This file contains the HTML structure and PHP logic for the navigation sidebar.
 * It dynamically displays menu items based on the logged-in user's role.
 * This file is included in all pages that require the sidebar.
 */

// Ensure configuration and authentication functions are available
// Use ROOT_PATH for reliable inclusion. This file is in views/includes.
// So, ROOT_PATH is two directories up from here (views/includes -> views -> project_management_system).
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
}
require_once ROOT_PATH . 'config.php'; // Ensure config.php is loaded first
require_once MODELS_PATH . 'auth.php'; // For isLoggedIn() and $_SESSION['user_role']
require_once MODELS_PATH . 'db.php';   // For connectDB() and getUnreadMessageCount()
require_once RECRUITMENT_MODELS_PATH . 'recruitment_post.php'; // For DEO recruitment post counts
require_once WITHDRAWAL_MODELS_PATH . 'withdrawal.php'; // For DEO withdrawal counts

// Check if user is logged in
if (!isLoggedIn()) {
    // This file should ideally only be included on authenticated pages,
    // but as a fallback, if not logged in, redirect to login page.
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

$current_page = $_GET['page'] ?? ''; // Get the current page from GET parameter
$userRole = $_SESSION['user_role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? 'Guest';
$currentUserId = $_SESSION['user_id'] ?? null; // Get current user ID from session

// Fetch app logo and name from settings for the sidebar header
$app_logo_url = '';
$app_name_setting = 'Project Management System'; // Default value
$unreadMessageCount = 0; // Initialize unread message count
$returnedForEditCount = 0; // Initialize returned for edit count
$pendingWithdrawalCount = 0; // Initialize pending withdrawal count for DEO
$detailsRequestedWithdrawalCount = 0; // Initialize details requested withdrawal count for DEO
$adminPendingWithdrawalCount = 0; // Initialize pending withdrawal count for Admin

try {
    $pdo = connectDB(); // Assuming connectDB() returns a PDO object
    $stmt = $pdo->query("SELECT app_name, app_logo_url FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($settings) {
        // Use null coalescing operator to ensure a string is always passed to htmlspecialchars
        $app_name_setting = htmlspecialchars($settings['app_name'] ?? 'Project Management System');
        $app_logo_url = htmlspecialchars($settings['app_logo_url'] ?? '');
    }

    // Fetch unread message count if user is logged in and user ID is available
    if ($currentUserId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :user_id AND is_read = 0");
        $stmt->bindParam(':user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        $unreadMessageCount = $stmt->fetchColumn();

        // Fetch counts based on user role
        if ($userRole === 'data_entry_operator') {
            $returnedForEditCount = getDeoReturnedForEditPostCount($currentUserId);
            $pendingWithdrawalCount = getDeoPendingWithdrawalCount($currentUserId);
            $detailsRequestedWithdrawalCount = getDeoDetailsRequestedWithdrawalCount($currentUserId);
        } elseif ($userRole === 'admin') {
            // For admin, count all pending, processing, and details_requested withdrawal requests
            $adminPendingWithdrawalCount = count(getAllWithdrawalRequests('pending')) +
                                           count(getAllWithdrawalRequests('processing')) +
                                           count(getAllWithdrawalRequests('details_requested'));
        }
    }

} catch (PDOException $e) {
    error_log("Error fetching app settings or unread messages/returned posts/withdrawal counts in sidebar: " . $e->getMessage());
    // Fallback to default names already initialized, counts remain 0
}

?>

<!-- Sidebar -->
<nav id="sidebar" class="rounded-end-4 shadow">
    <div class="p-4">
        <div class="mb-4 text-center">
            <?php if (!empty($app_logo_url)): ?>
                <img src="<?= $app_logo_url ?>" alt="App Logo" class="img-fluid mb-2" style="max-height: 60px;">
            <?php else: ?>
                <i class="fas fa-project-diagram fa-3x text-white mb-2"></i>
            <?php endif; ?>
            <h3 class="text-white"><?= APP_NAME ?></h3>
            <p class="text-white-50 mb-0">Hello, <?= htmlspecialchars($userName) ?> (<?= ucwords(str_replace('_', ' ', $userRole)) ?>)</p>
        </div>
        <ul class="list-unstyled components">
            <?php if (isLoggedIn()): ?>
                <?php if ($userRole === 'admin'): ?>
                    <li class="<?= ($current_page === 'dashboard' || $current_page === 'home') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=dashboard">
                            <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'users' || $current_page === 'add_user' || $current_page === 'edit_user') ? 'active' : '' ?>">
                        <a href="#usersSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <i class="fas fa-fw fa-users"></i> Users
                        </a>
                        <ul class="collapse list-unstyled" id="usersSubmenu">
                            <li><a href="<?= BASE_URL ?>?page=users"><i class="fas fa-user-cog me-2"></i>Manage Users</a></li>
                            <li><a href="<?= BASE_URL ?>?page=add_user"><i class="fas fa-user-plus me-2"></i>Register New User</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($current_page === 'clients' || $current_page === 'add_client') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=clients">
                            <i class="fas fa-fw fa-user-tie"></i> Clients
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'all_tasks' || $current_page === 'assign_task' || $current_page === 'edit_task') ? 'active' : '' ?>">
                        <a href="#tasksSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <i class="fas fa-fw fa-tasks"></i> Tasks
                        </a>
                        <ul class="collapse list-unstyled" id="tasksSubmenu">
                            <li>
                                <a href="<?= BASE_URL ?>?page=assign_task">Assign New Task</a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>?page=all_tasks">View All Tasks</a>
                            </li>
                        </ul>
                    </li>
                     <li class="<?= ($current_page === 'expenses' || $current_page === 'add_expense' || $current_page === 'manage_expenses' || $current_page === 'edit_expense') ? 'active' : '' ?>">
                        <a href="#expensesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <i class="fas fa-fw fa-money-bill-wave"></i> Expenses
                        </a>
                        <ul class="collapse list-unstyled" id="expensesSubmenu">
                            <li>
                                <a href="<?= BASE_URL ?>?page=add_expense">Add Expense</a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>?page=manage_expenses">Manage Expenses</a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?= ($current_page === 'categories' || $current_page === 'subcategories') ? 'active' : '' ?>">
                        <a href="#categoriesSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <i class="fas fa-fw fa-sitemap"></i> Categories
                        </a>
                        <ul class="collapse list-unstyled" id="categoriesSubmenu">
                            <li><a href="<?= BASE_URL ?>?page=categories"><i class="fas fa-folder-open me-2"></i>Manage Categories</a></li>
                            <li><a href="<?= BASE_URL ?>?page=subcategories"><i class="fas fa-sitemap me-2"></i>Manage Subcategories</a></li>
                        </ul>
                    </li>
                    <li class="<?= ($current_page === 'reports') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=reports">
                            <i class="fas fa-fw fa-chart-pie"></i> Reports
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'settings') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=settings">
                            <i class="fas fa-fw fa-cogs"></i> Settings
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'manage_recruitment_posts') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=manage_recruitment_posts">
                            <i class="fas fa-fw fa-bullhorn"></i> Recruitment Posts
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'manage_withdrawals') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=manage_withdrawals" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-money-check-alt"></i> Manage Withdrawals</span>
                            <span id="admin-withdrawal-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= ($adminPendingWithdrawalCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= $adminPendingWithdrawalCount ?>
                            </span>
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'messages') ? 'active' : '' ?>">
                        <!-- Added ID for JavaScript to update the count and added conditional display -->
                        <a href="<?= BASE_URL ?>?page=messages" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-comments"></i> Messenger</span>
                            <span id="message-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= ($unreadMessageCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= $unreadMessageCount ?>
                            </span>
                        </a>
                    </li>
                <?php elseif ($userRole === 'data_entry_operator'): ?>
                    <!-- DEO Dashboard -->
                    <li class="<?= ($current_page === 'deo_dashboard' || $current_page === 'home') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=deo_dashboard">
                            <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'add_client') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=add_client">
                            <i class="fas fa-fw fa-user-plus"></i> Add New Client
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'add_recruitment_post') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=add_recruitment_post">
                            <i class="fas fa-fw fa-bullhorn"></i> Add Recruitment Post
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'generate_poster') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=generate_poster">
                            <i class="fas fa-fw fa-image"></i> Generate Poster
                        </a>
                    </li>
                    <?php
                    // Determine if the 'My Tasks' submenu should be expanded
                    $myTasksSubmenuExpanded = ($current_page === 'my_tasks' || $current_page === 'submit_work' || $current_page === 'update_task');
                    ?>
                    <li class="<?= $myTasksSubmenuExpanded ? 'active' : '' ?>">
                        <a href="#myTasksSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $myTasksSubmenuExpanded ? 'true' : 'false' ?>" class="dropdown-toggle"><i class="fas fa-fw fa-tasks"></i> My Tasks</a>
                        <ul class="collapse list-unstyled <?= $myTasksSubmenuExpanded ? 'show' : '' ?>" id="myTasksSubmenu">
                            <li><a href="<?= BASE_URL ?>?page=my_tasks">View My Tasks</a></li>
                            <li><a href="<?= BASE_URL ?>?page=submit_work">Submit Work</a></li>
                            <!-- Update Task page is typically accessed via an ID from my_tasks -->
                        </ul>
                    </li>
                    <li class="<?= ($current_page === 'my_withdrawals') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=my_withdrawals" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-wallet"></i> My Withdrawals</span>
                            <span id="deo-withdrawal-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= (($pendingWithdrawalCount + $detailsRequestedWithdrawalCount) > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= ($pendingWithdrawalCount + $detailsRequestedWithdrawalCount) ?>
                            </span>
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'bank_details') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=bank_details">
                            <i class="fas fa-fw fa-bank"></i> My Bank Details
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'returned_for_edit_posts') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=deo_dashboard&status=returned_for_edit" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-edit"></i> Edit Requests</span>
                            <span id="edit-request-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= ($returnedForEditCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= $returnedForEditCount ?>
                            </span>
                        </a>
                    </li>
                    <li class="<?= ($current_page === 'messages') ? 'active' : '' ?>">
                        <!-- Added ID for JavaScript to update the count and added conditional display -->
                        <a href="<?= BASE_URL ?>?page=messages" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-comments"></i> Messenger</span>
                            <span id="message-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= ($unreadMessageCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= $unreadMessageCount ?>
                            </span>
                        </a>
                    </li>
                <?php elseif (in_array($userRole, ['manager', 'assistant', 'coordinator', 'sales', 'accountant'])): ?>
                    <!-- Other regular user roles -->
                    <li class="<?= ($current_page === 'dashboard' || $current_page === 'home') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=dashboard">
                            <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <?php
                    // Determine if the 'My Tasks' submenu should be expanded
                    $myTasksSubmenuExpanded = ($current_page === 'my_tasks' || $current_page === 'submit_work' || $current_page === 'update_task');
                    ?>
                    <li class="<?= $myTasksSubmenuExpanded ? 'active' : '' ?>">
                        <a href="#myTasksSubmenu" data-bs-toggle="collapse" aria-expanded="<?= $myTasksSubmenuExpanded ? 'true' : 'false' ?>" class="dropdown-toggle"><i class="fas fa-fw fa-tasks"></i> My Tasks</a>
                        <ul class="collapse list-unstyled <?= $myTasksSubmenuExpanded ? 'show' : '' ?>" id="myTasksSubmenu">
                            <li><a href="<?= BASE_URL ?>?page=my_tasks">View My Tasks</a></li>
                            <li><a href="<?= BASE_URL ?>?page=submit_work">Submit Work</a></li>
                            <!-- Update Task page is typically accessed via an ID from my_tasks -->
                        </ul>
                    </li>
                    <li class="<?= ($current_page === 'clients') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>?page=clients"><i class="fas fa-handshake me-3"></i> Clients</a>
                    </li>
                    <li class="<?= ($current_page === 'messages') ? 'active' : '' ?>">
                        <!-- Added ID for JavaScript to update the count and added conditional display -->
                        <a href="<?= BASE_URL ?>?page=messages" class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-fw fa-comments"></i> Messenger</span>
                            <span id="message-count-badge" class="badge bg-danger rounded-pill ms-auto px-2 py-1"
                                  style="display: <?= ($unreadMessageCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?= $unreadMessageCount ?>
                            </span>
                        </a>
                    </li>
                <?php else: // For any other unexpected roles or guests (though isLoggedIn() should prevent this) ?>
                     <li>
                        <a href="<?= BASE_URL ?>?page=home">
                            <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= BASE_URL ?>?page=logout">
                        <i class="fas fa-fw fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            <?php else: ?>
                <li class="<?= ($current_page === 'login') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>?page=login"><i class="fas fa-sign-in-alt me-3"></i> Login</a>
                </li>
                <li class="<?= ($current_page === 'register') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>?page=register"><i class="fas fa-user-plus me-3"></i> Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<!-- End Sidebar -->
