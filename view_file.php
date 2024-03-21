<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename view_file.php
 */

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';

include 'includes/header.php';

// Check if the view option is stored in GET
if (isset($_GET["view"])) {
    // Try to find the file in the database
    try {
        $file_pointer = $_GET["view"];
        $directory = pathinfo($file_pointer, PATHINFO_DIRNAME) . '/';

        $uniqueFileName = ltrim($file_pointer, $directory . '/');

        $file_data = file_select($uniqueFileName);

    } catch (Exception $e) {
        set_flash_message("File not found.", ALERT_DANGER);
    }

}else{
    redirectAfterFileMgmt();
}

?>

<div class='mt-3'>
    <h1>View File</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>

<div class="row justify-content-center mt-3">
    <!-- User Details Card -->
    <div class="col-12 mt-3 mb-3">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">File: <?php echo $file_data['original_name'];?></h4>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <?php
                    // Store the file extension
                    $fileExtension = strtolower(pathinfo($file_pointer, PATHINFO_EXTENSION));

                    if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        // Display image if the file extension is in the list
                        echo '<img src="' . $file_pointer . '" class="img-fluid" alt="View Image"><br/><br/>';
                    } elseif ($fileExtension === 'pdf') {
                        // Display the PDF using an iframe
                        echo '<iframe src="' . $file_pointer . '" width="100%" height="600px"></iframe><br/><br/>';
                    } else {
                        // Display a default message or handle other file types
                        echo 'File type not supported.';
                    }
                    ?>
                    <!-- Rename and Move Form -->
                    <form action="file_upload.php" method="post">
                        <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($file_pointer); ?>">

                        <!-- Rename Text Input -->
                        <div class="form-group row">
                            <label for="new_name" class="col-sm-2 col-form-label">New Name:</label>
                            <div class="col-sm-8">
                                <input type="text" name="new_name" id="new_name" class="form-control" placeholder="Enter new name">
                            </div>
                            <div class="col-sm-2">
                                <!-- Rename Button -->
                                <button type="submit" name="rename_file" class="btn btn-info btn-sm">Rename</button>
                            </div>
                        </div>
                        <br/>

                        <!-- Move Dropdown Menu -->
                        <div class="form-group row">
                            <label for="destination_folder" class="col-sm-2 col-form-label">Move to Folder:</label>
                            <div class="col-sm-8">
                                <select name="destination_folder" id="destination_folder" class="form-control">
                                    <option value="uploads/">Uploads</option>
                                    <option value="uploads/projectA/">Project A</option>
                                    <option value="uploads/projectB/">Project B</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" name="move_file" class="btn btn-info btn-sm">Move</button>
                            </div>
                        </div>
                    </form>
                    <div class="col-12 mt-3 mb-2">
                        <a href="file_upload.php?delete=<?php echo urlencode($file_pointer); ?>" class="btn btn-danger btn-sm">Delete File</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3 mb-2">
            <a href="file_management_admin.php" class="btn btn-info btn-sm">Go Back</a>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>