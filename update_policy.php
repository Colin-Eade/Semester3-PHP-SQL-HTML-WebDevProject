<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename update_policy.php
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

?>

<div class='mt-3'>
    <h1>Policy Management</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>

<div class="row">
    <div class="col-md-6 offset-md-3 mt-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Update Policy</h2>
                <form method="post" action="update_policy_processing.php" enctype="multipart/form-data">
                    <div class="form-group mb-2">
                        <label for="tableName">Select Policy Type:</label>
                        <select name="tableName" id="tableName" class="form-control">
                            <option value="privacy_policy">Privacy Policy</option>
                            <option value="acceptable_use_policy">Acceptable Use Policy</option>
                            <option value="terms_of_service">Terms of Service</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="policyFile">Upload Policy File:</label>
                        <input type="file" name="policyFile" id="policyFile" class="form-control-file">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>



<?php
include 'includes/footer.php';
?>
