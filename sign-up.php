<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename sign-up.php
 */

include 'includes/header.php';

if (isset($_SESSION['user'])) {
    redirect('dashboard.php');
    exit;
}
if(isset($_POST['register'])) {

    // Grab form inputs
    $email_address = $_POST['email_address'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];
    $phone_extension = $_POST['phone_extension'];
    $user_type = $_POST['user_type'];

    // Sanitize and/or trim inputs
    $email_address = filter_var($email_address, FILTER_SANITIZE_EMAIL);
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $phone_extension = trim($phone_extension);

    // Validate inputs
    if(validate_register_inputs($email_address, $first_name, $last_name, $password, $phone_extension))
    {
        // Try hashing the password and registering the user
        try {
            $password = hash_password(($password));

            $result = register_user($email_address, $first_name, $last_name, $password, $phone_extension, $user_type);

            // If the register_user insert was successful
            if ($result) {
                // Set a flash for the redirect
                set_flash_message("Register successful", ALERT_SUCCESS);

                // Redirect to login page
                redirect("sign-in.php");

                // Register was not successful
            } else {
                set_flash_message("Registration failed. please try again", ALERT_DANGER);
            }
        } catch (Exception $e) {
            set_flash_message("Error: ". $e->getMessage(), ALERT_DANGER);
        }
    }
}
?>
<div class="row">
    <div class="col-md-6 offset-md-3 mt-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Register</h2>
                <form action="sign-up.php" method="POST">
                    <?php
                    // Session flash message
                    if(has_flash_message()){
                        echo flash_message();
                    }
                    ?>
                    <div class="form-group">
                        <label for="email_address">Email Address:</label>
                        <input type="email" name="email_address" class="form-control" placeholder="Enter Email" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter First Name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter Password" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_extension">Phone Extension:</label>
                        <input type="tel" name="phone_extension" class="form-control" placeholder="Enter Phone Extension" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="user_type">User Type:</label>
                        <select name="user_type" class="form-control mb-1">
                            <option value="a">Agent</option>
                            <option value="c">Client</option>
                        </select>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary mb-2">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
    include 'includes/footer.php';
?>