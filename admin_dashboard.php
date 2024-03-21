<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename admin_dashboard.php
 */

include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    // Not authenticated, redirect to login
    redirect('sign-in.php');
    exit;
}
else if ($_SESSION['user']['is_admin'] === false) {
    // Not admin, redirect to dashboard
    redirect('dashboard.php');
    exit;
}

$totalUsersCount = "";
$recentActiveUsersCount = "";
$totalLogEntriesCount = "";
$recentLogEntriesCount = "";

// Get metrics
try {
    $totalUsersCount = count(getAllUsers());
    $recentActiveUsersCount = count(getRecentlyActiveUsers());
    $totalLogEntriesCount = count(getAllLogEntries());
    $recentLogEntriesCount = count(getRecentLogEntries());
} catch (Exception $e) {
    set_flash_message("Error: ". $e->getMessage(), ALERT_DANGER);
}

?>

<div class='mt-3'>
    <h1>Admin Dashboard</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>

<div class="row">
    <!-- Rectangle Card 1 - User Management -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">User Management</h5>
                <p class="card-text">Manage your users here.</p>
                <a href="user_management.php" class="btn btn-primary">Manage Users</a>
            </div>
        </div>
    </div>
    <!-- Rectangle Card 2 - File Management -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">File Management</h5>
                <p class="card-text">Manage all uploaded files here.</p>
                <a href="file_management_admin.php" class="btn btn-primary">Manage Files</a>
            </div>
        </div>
    </div>
    <!-- Rectangle Card 3 - PayPal Management -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">PayPal Management</h5>
                <p class="card-text">Manage PayPal accounts here.</p>
                <a href="https://www.paypal.com/signin" class="btn btn-primary">Manage PayPal</a>
            </div>
        </div>
    </div>
    <!-- Rectangle Card 4 - Logs -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Logs</h5>
                <p class="card-text">View system logs here.</p>
                <a href="logs.php" class="btn btn-primary">View Logs</a>
            </div>
        </div>
    </div>
    <!-- Centered Rectangle Card 5 - Update Policy Page -->
    <div class="col-md-6 mx-auto">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Policy Management</h5>
                <p class="card-text">Update website policies here.</p>
                <a href="update_policy.php" class="btn btn-primary">Update Policies</a>
            </div>
        </div>
    </div>
</div>

<!-- Row for Square Cards -->
<div class="row">
    <!-- Square Card 1 -->
    <div class="col-3 square-card">
        <div class="card">
            <div class="card-body">
                <p class="card-metric-label">Total Users</p>
                <div class="centered-content">
                    <p class="card-metric-value"><?= $totalUsersCount ?></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Square Card 2 -->
    <div class="col-3 square-card">
        <div class="card">
            <div class="card-body">
                <p class="card-metric-label">Recently Active Users</p>
                <div class="centered-content">
                    <p class="card-metric-value"><?= $recentActiveUsersCount ?></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Square Card 3 -->
    <div class="col-3 square-card">
        <div class="card">
            <div class="card-body">
                <p class="card-metric-label">Total Log Entries</p>
                <div class="centered-content">
                    <p class="card-metric-value"><?= $totalLogEntriesCount ?></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Square Card 4 -->
    <div class="col-3 square-card">
        <div class="card">
            <div class="card-body">
                <p class="card-metric-label">Recent Log Entries</p>
                <div class="centered-content">
                    <p class="card-metric-value"><?= $recentLogEntriesCount ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'includes/footer.php';
?>
