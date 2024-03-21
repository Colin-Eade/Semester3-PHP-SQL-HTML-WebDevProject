<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename logs.php
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

$logs = getAllLogEntries();

?>

<div class='mt-3'>
    <h1>Logs</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>

<div class="card mt-3 mb-3">
    <div class="card-header">
        <h4 class="card-title">Log Entries</h4>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
            <tr>
                <th style="width: 33%;">Time</th>
                <th style="width: 34%;">Action</th>
                <th style="width: 33%;">User</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $entry): ?>
                <tr>
                    <td><?php echo htmlspecialchars($entry['time']); ?></td>
                    <td><?php echo htmlspecialchars($entry['message']); ?></td>
                    <td><?php echo htmlspecialchars($entry['user']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<?php
include 'includes/footer.php';
?>