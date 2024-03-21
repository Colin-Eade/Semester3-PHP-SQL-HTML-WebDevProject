<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename file_management_admin.php
 */

include 'includes/header.php';

// Get the selected folder from the dropdown or default to 'uploads/'
$selectedFolder = $_POST['folder'] ?? '';

if (!isset($_SESSION['user'])) {
    // Not authenticated, redirect to login
    redirect('sign-in.php');
    exit;
} else if ($_SESSION['user']['is_admin'] === false) {
    // Not admin, redirect to dashboard
    redirect('dashboard.php');
    exit;
}


?>

<div class='mt-3'>
    <h1>File Management</h1>
</div>

<?php
// Session flash
if (has_flash_message()) {
    echo flash_message();
}
?>
    <!-- File upload form -->
<div class="row">
    <div class="col-md-12 mt-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title">File Upload</h3>
                <form action="file_upload.php" method="POST" enctype="multipart/form-data">
                    <div class="col-md-10 justify-content-center mt-3">
                        <label for="file" class="form-label">Select file to upload:</label>
                        <input type="file" class="form-control-file" name="file" id="file" required>
                        <label for="destinationFolder" class="form-label">Select folder:</label>
                        <select name="destinationFolder" style="width: 200px;">
                            <option value="uploads/">Uploads</option>
                            <option value="uploads/projectA/">Project A</option>
                            <option value="uploads/projectB/">Project B</option>
                        </select>
                        <button type="submit" class="btn btn-info btn-sm" name="submit">Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <!-- File display form -->
<div class="row">
    <div class="col-12 mt-3 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="card-title">Select Folder:</h3>
                <form action="file_management_admin.php" method="POST">
                    <div class="col-md-3">
                        <select name="folder">
                            <option value="uploads/" <?php echo ($selectedFolder === 'uploads/') ? 'selected' : ''; ?>>Uploads</option>
                            <option value="uploads/projectA/" <?php echo ($selectedFolder === 'uploads/projectA/') ? 'selected' : ''; ?>>Project A</option>
                            <option value="uploads/projectB/" <?php echo ($selectedFolder === 'uploads/projectB/') ? 'selected' : ''; ?>>Project B</option>
                        </select>
                        <button type="submit" class="btn btn-info btn-sm">Select</button>
                    </div>
                </form><br/><br/>
                <h3 class="card-title"><?php echo $selectedFolder ?> Contents:</h3>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>File Thumbnail</th>
                        <th>File Name</th>
                        <th>File Type</th>
                        <th>Date Uploaded</th>
                        <th>Uploaded By</th>
                        <th>Options</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $thumb_dir = 'thumbnails/';
                    $pdfImage = 'images/PDFimage.png';

                    // Get all files and associated metadata from the database based on the selected folder
                    $dbConn = db_connect();
                    $query = "SELECT original_name, file_name, mime_type, created_at, created_by FROM files WHERE directory = $1";
                    $result = pg_query_params($dbConn, $query, array($selectedFolder));

                    // Display results if any returned
                    if ($result) {
                        while ($row = pg_fetch_assoc($result)) {

                            $originalName = $row['original_name'];
                            $fileName = $row['file_name'];
                            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            $file_pointer = $selectedFolder . $fileName;
                            // store thumbnail name
                            $thumbnail_name = "thumb_" . $fileName;

                            if (file_exists($file_pointer)) {
                                $thumbnailSrc = ($fileType === 'pdf') ? $pdfImage : $thumb_dir . $thumbnail_name;

                                // Display file information
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $thumbnailSrc; ?>" alt="File Thumbnail" style="width: 50px; height: 50px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($originalName); ?></td>
                                    <td><?php echo htmlspecialchars($row['mime_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                    <td>
                                        <a href="view_file.php?view=<?php echo urlencode($file_pointer); ?>" class="btn btn-info btn-sm">View</a>
                                        <a href="file_upload.php?delete=<?php echo urlencode($file_pointer); ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }

                        // Free result
                        pg_free_result($result);
                    }
                    // Close database connection
                    db_close($dbConn);
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>