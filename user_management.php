<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename manage_user.php
 */

include 'includes/header.php';

if (!isset($_SESSION['user'])) {
    // Not authenticated, redirect to login
    redirect('sign-in.php');
    exit;
} else if ($_SESSION['user']['is_admin'] === false) {
    // Not admin, redirect to dashboard
    redirect('dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // All fields not filled
    if (!isset($_POST['search_field']) || !isset($_POST['search_term'])) {

        // Inform the user and return the full user table
        set_flash_message("Search parameters incomplete.", ALERT_DANGER);
        $users = getAllUsers();

        // All fields filled on form
    } else {

        // Get fields
        $search_field = $_POST['search_field'];
        $search_term = $_POST['search_term'];

        try {
            // Return a table based on the search
            $users = searchUsers($search_field, $search_term);

            // If no result from search
            if(!$users) {
                // Inform the user and return the full user table
                set_flash_message("No results found for your search.", ALERT_DANGER);
                $users = getAllUsers();
            }

        } catch (Exception $e) {
            set_flash_message("Error: ". $e->getMessage(), ALERT_DANGER);
            $users = getAllUsers();
        }
    }

} else {
    $users = getAllUsers(); // Default action when the page is first opened
}

?>

<div class='mt-3'>
    <h1>User Management</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2 mt-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">User Search</h2>
                <form action="user_management.php" method="POST" class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="search_term" class="form-control" placeholder="Search..." required>
                    </div>
                    <div class="col-md-2">
                        <select name="search_field" class="form-control">
                            <option value="" disabled selected>Filter</option>
                            <option value="id">User ID</option>
                            <option value="email">Email</option>
                            <option value="first_name">First Name</option>
                            <option value="last_name">Last Name</option>
                            <option value="user_type">User Type</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-3 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">User List</h2>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Profile Image</th>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>User Type</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php
                                $unescapedImageData = pg_unescape_bytea($user['profile_img']);
                                $imageData = 'data:image/jpeg;base64,' . base64_encode($unescapedImageData);
                                ?>
                                <img src="<?php echo $imageData; ?>" alt="Profile Picture" style="width: 50px; height: 50px;">
                            </td>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email_address']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                            <td>
                                <a href="manage_user.php?user_email=<?php echo urlencode($user['email_address']); ?>" class="btn btn-sm btn-primary">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
