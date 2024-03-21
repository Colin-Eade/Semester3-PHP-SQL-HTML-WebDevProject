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

if (isset($_GET['user_email'])) {
    $email = urldecode($_GET['user_email']);

    if ($email === $_SESSION['user']['email_address']) {
        set_flash_message("You cannot edit your own profile", ALERT_DANGER);
        redirect('user_management.php');
        exit;
    }

    try {

        $selected_user = user_select($email);
        if(!$selected_user)
        {
            set_flash_message("User does not exist", ALERT_DANGER);
            redirect('user_management.php');
            exit;
        }

    } catch (Exception $e) {
        set_flash_message("Error: " . $e->getMessage(), ALERT_DANGER);
        redirect('user_management.php');
        exit;
    }

} else {
    set_flash_message("Could not retrieve user details", ALERT_DANGER);
    redirect('user_management.php');
    exit;
}

$selected_user_id = $selected_user['id'];
$selected_email = $selected_user['email_address'];
$selected_first_name = $selected_user['first_name'];
$selected_last_name = $selected_user['last_name'];
$selected_password = $selected_user['password'];
$selected_type = $selected_user['user_type'];
$selected_extension = $selected_user['phone_extension'];
$selected_signup_date = $selected_user['created_time'];
$selected_last_login =  $selected_user['last_time'];
$selected_user_profile_img = pg_unescape_bytea($selected_user['profile_img']);

if(!$selected_last_login)
{
    $selected_last_login = "N/A";
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

<div class="row justify-content-center mt-3">
    <!-- User Details Card -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">User Details</h4>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($selected_user_profile_img); ?>" class="rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;">
                </div>
                <table class="table table-borderless">
                    <tbody>
                    <tr><th style="width: 50%;">ID:</th><td><?php echo htmlspecialchars($selected_user_id); ?></td></tr>
                    <tr><th>Email:</th><td><?php echo htmlspecialchars($selected_email); ?></td></tr>
                    <tr><th>First Name:</th><td><?php echo htmlspecialchars($selected_first_name); ?></td></tr>
                    <tr><th>Last Name:</th><td><?php echo htmlspecialchars($selected_last_name); ?></td></tr>
                    <tr><th>Type:</th><td><?php echo htmlspecialchars($selected_type); ?></td></tr>
                    <tr><th>Phone Extension:</th><td><?php echo htmlspecialchars($selected_extension); ?></td></tr>
                    <tr><th>Sign-up Time:</th><td><?php echo htmlspecialchars($selected_signup_date); ?></td></tr>
                    <tr><th>Last Login Time:</th><td><?php echo htmlspecialchars($selected_last_login); ?></td></tr>
                    </tbody>
                </table>
                <form action="update_user_processing.php" method="POST">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($selected_email); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($selected_user_id); ?>">
                    <button type="submit" name="delete" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Form Card -->
    <div class="row justify-content-center">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit User</h4>
                </div>
                <div class="card-body">
                    <form action="update_user_processing.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address:</label>
                            <input type="email" class="form-control mb-2" name="email">
                        </div>
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name:</label>
                            <input type="text" class="form-control mb-2" name="first_name">
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name:</label>
                            <input type="text" class="form-control mb-2" name="last_name">
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control mb-2" name="password">
                        </div>
                        <div class="form-group">
                            <label for="phone_extension" class="form-label">Phone Extension:</label>
                            <input type="text" class="form-control mb-2" name="phone">
                        </div>
                        <div class="form-group mb-3">
                            <label for="user_type">User Type:</label>
                            <select name="user_type" class="form-control mb-1">
                                <option value="">Type</option> <!-- Set the value to an empty string -->
                                <option value="a">Agent</option>
                                <option value="c">Client</option>
                                <option value="s">Super</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="profile_img">Profile Image:</label>
                            <input type="file" name="profile_img" class="form-control mb-2">
                        </div>
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($selected_user_id); ?>">
                        <button type="submit" name="update" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact User Form Card -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Contact User</h4>
                </div>
                <div class="card-body">
                    <form action="update_user_processing.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject:</label>
                            <input type="text" name="subject" class="form-control">
                        </div>
                        <div class="form-group mb-2">
                            <label for="message" class="form-label">Message:</label>
                            <textarea name="message" rows="5" class="form-control" style="resize: none;"></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label for="attachment" class="form-label">Attachment:</label>
                            <input type="file" name="attachment" class="form-control-file">
                        </div>
                        <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($selected_email); ?>">
                        <input type="hidden" name="sender" value="<?php echo htmlspecialchars($_SESSION['user']['email_address']); ?>">
                        <button type="submit" name="send_email" class="btn btn-primary">Send Email</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include 'includes/footer.php';
?>

