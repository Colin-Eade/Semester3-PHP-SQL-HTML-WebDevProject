<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename dashboard.php
 */

// 1. Include the header
include 'includes/header.php';

// 2. Check user authentication

if (!isset($_SESSION['user'])) {
    // Not authenticated, redirect to login
   redirect('sign-in.php');
   exit;
}

// grab the current user array and store it in a variable
$user = $_SESSION['user'];

// grab the user's last login from the session array
$last_login = $_SESSION['last_login'];

// store the user's email for update query
$email = $user['email_address'];

// Store profile picture data
$profilePictureData = $user['profile_img'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form submitted, process the data

    // Get the form data
    $newEmail = $_POST['email_address'];
    $newPassword = $_POST['password'];
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];
    $newProfileImg = $_FILES['profile_img'];

    $userId = $user['id'];

    // If all fields are empty
    if(empty($newEmail) && empty($newPassword) && empty($newFirstName) && empty($newLastName) &&
        (empty($newProfileImg) || $newProfileImg['error'] !== UPLOAD_ERR_OK || $newProfileImg['size'] === 0)){
        set_flash_message("Please enter new information in at least one field to update.", ALERT_DANGER);
        return false;
    }else{
        try {
            // Run a query to update the user info
            $result = update_user($userId, $newEmail, $newPassword, $newFirstName, $newLastName, $newProfileImg);

            // If the query was successful
            if ($result) {
                if(!empty($newEmail)){
                    $email = $newEmail;
                }
                // get updated user info
                $user = user_select($email);
                $_SESSION['user'] = $user;

                // Check if user is admin
                if ($_SESSION['user']['user_type'] === 's') {
                    $_SESSION['user']['is_admin'] = true;
                } else {
                    $_SESSION['user']['is_admin'] = false;
                }
                set_flash_message("User data updated successfully.", ALERT_SUCCESS);
                writeToLog("self update success");
            }

            // Query was not successful
        } catch (Exception $e) {
            set_flash_message("Error: ". $e->getMessage(), ALERT_DANGER);
            writeToLog("self update failure");
        }
    }
}
?>

<div class="container">
    <h1 class="h2">Dashboard</h1>
    <?php
    // Session flash
    if(has_flash_message()){
        echo flash_message();
    }
    ?>
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <?php
                    if ($profilePictureData) {
                        $unescapedImageData = pg_unescape_bytea($user['profile_img']);

                        echo '<img src="data:image/jpeg;base64,' . base64_encode($unescapedImageData) . '" alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 150px;">';
                    }
                    ?>
                </div>
                <div class="col-md-9">
                <h3><?php echo "Welcome <strong>" . $user['first_name'] . "</strong>!"; ?></h3><br/>
                    <p>
                        <?php
                        if ($last_login) {
                            // If the timestamp is available, format it as a date and time
                            $last_login = new DateTime($last_login);
                            $last_login = $last_login->format('F j, Y, H:i');
                            echo "<strong>Last login: " . $last_login . "</strong>";
                        } else {
                            // If the timestamp is not available, show the first-time login message
                            echo "<strong>This is your first time logging in!</strong>";
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php

    if ($user)
    {
        echo '<div class="card mt-4 shadow-sm">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">User Details</h5>';
        echo '<p class="card-text">';
        echo '<strong>Email: ' . $user['email_address'] . '</strong><br>';
        echo '<strong>Name: ' . $user['first_name'] . ' ' . $user['last_name'] . '</strong><br>';
        echo '<strong>ID: ' . $user['id'] . '</strong><br>';
        echo '<strong>Phone Extension: ' . $user['phone_extension'] . '</strong><br>';
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }else{
        echo '<p class="mt-4 alert alert-warning">No employee data found.</p>';
    }

    ?>
    <div class="card mt-4 shadow-sm">
        <div class="card-body">
        <h2 class="card-title">Edit Profile</h2>
        <form action="dashboard.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="email_address">New Email Address:</label>
                <input type="email" name="email_address" class="form-control">
            </div>
            <div class="form-group">
                <label for="password">New Password:</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="first_name">New First Name:</label>
                <input type="text" name="first_name" class="form-control">
            </div>
            <div class="form-group">
                <label for="last_name">New Last Name:</label>
                <input type="text" name="last_name" class="form-control mb-2">
            </div>
            <div class="form-group">
                <label for="profile_img">New Profile Image:</label>
                <input type="file" name="profile_img" class="form-control mb-2">
            </div>
            <button type="submit" name="save" class="btn btn-primary mb-2">Save</button>
        </form>
        </div>
    </div>
</div>

<?php
// 6. Log Out option
echo "<a href='logout.php' class='btn btn-primary mt-2 mb-5 '>Logout</a>";

// 7. Include the footer
include 'includes/footer.php';
?>

