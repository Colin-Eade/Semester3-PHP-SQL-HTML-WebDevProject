<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename sign-in.php
 */

include 'includes/header.php';

// If a user is already logged in redirect to the dashboard
if (isset($_SESSION['user'])) {
    redirect('dashboard.php');
    exit;
}

// if/else       if this is set                      do this                   if not do this
$stored_email = (isset($_COOKIE['remember_email'])) ? $_COOKIE['remember_email'] : '';

if(isset($_POST['login'])){

    // Grab params from form
    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    try
    {
        if(user_authenticate($email_address, $password)) {

            $user = user_select($email_address);
            $_SESSION['user'] = $user;

            // Check if user is admin
            if ($_SESSION['user']['user_type'] === 's') {
                $_SESSION['user']['is_admin'] = true;
            } else {
                $_SESSION['user']['is_admin'] = false;
            }

            // Get and store the last login from the user
            $_SESSION['last_login'] = get_last_login($user);

            // Update the last login
            user_update_login_time($email_address);

            // Write to the sign in log
            writeToLog("login success for email {$email_address}");

            // Cookie for 'remember me' field
            if(isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
                setcookie('remember_email', $email_address, time() + COOKIE_LIFESPAN, "/");
            } else {
                // remove/expire cookie if it exists
                if(isset($_COOKIE['remember_email'])){
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }

            // Set a flash message for the redirect
            set_flash_message("Login successful", ALERT_SUCCESS);

            // Redirect to dashboard
            redirect('dashboard.php');
            exit;

            // User was not authenticated
        } else {
            // Write to sign in log
            writeToLog("login failure for email {$email_address}");

            // set flash message
            set_flash_message("Invalid login", ALERT_DANGER);
        }

    } catch (Exception $e) {
        set_flash_message("Error: ". $e->getMessage(), ALERT_DANGER);
    }
}
?>
<div class="row">
    <div class="col-md-6 offset-md-3 mt-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Login</h2>
                <form action="sign-in.php" method="POST">
                    <?php
                    // Session flash
                    if(has_flash_message()) {
                        echo flash_message();
                    }
                    // Cookie flash
                    if(isset($_COOKIE['flash_message'])) {
                        echo '<div class="alert alert-success" role="alert">' . $_COOKIE['flash_message'] . '</div>';
                        setcookie('flash_message', '', time() - 3600, "/");
                    }
                    ?>
                    <div class="form-group">
                        <label for="email_address">Email Address:</label>
                        <input type="email" name="email_address" value="<?= $stored_email ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" class="form-control mb-2" required>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <label for="remember_me" class="mb-2">Remember Me</label>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary mb-2">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
require 'includes/footer.php';
?>