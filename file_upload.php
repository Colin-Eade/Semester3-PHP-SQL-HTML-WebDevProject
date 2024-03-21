<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename file_upload.php
 */

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';

session_start();

$user = $_SESSION['user'];
$email = $user['email_address'];

// Directories for file uploads and thumbnails
$targetDir = "uploads/";
$thumbDir = "thumbnails/";

$uniqID = uniqid();


// Check and create upload directories if they don't exist
if (!file_exists($targetDir)) {
    if (!file_exists($targetDir)) {
        // Create the directory
        mkdir($targetDir, 0755, true);

        // Set appropriate permissions
        chmod($targetDir, 0755);
    }
}

if (!file_exists($thumbDir)) {
    // Create the directory
    mkdir($thumbDir, 0755, true); // The third parameter creates parent directories if they don't exist

    // Set appropriate permissions
    chmod($thumbDir, 0755);
}

// If the deleted button was clicked
if(isset($_GET["delete"])){

    try {
        // Store the thumbnail's file name
        $fileName = ($_GET["delete"]);
        // call the function to delete the images
        delete_file($fileName, $thumbDir);
    }catch(Exception $e){
        // Catch exception
        set_flash_message('Error: ' . $e->getMessage(), ALERT_WARNING);
    }
}


// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle Rename submission
    if (isset($_POST['rename_file'])) {

        // Grab the unique file name from the form
        $file_name = $_POST['file_name'];
        // Grab the fil extension from the unique filename
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $directory = pathinfo($file_name, PATHINFO_DIRNAME) . '/';
        $uniqueFileName = ltrim($file_name, $directory . '/');

        // Grab new name from form
        $new_name = $_POST['new_name'];

        // Validate and sanitize the new name as needed
        $validationResult = validate_file_name($new_name);

        if ($validationResult !== null) {
            // Display an error message
            set_flash_message($validationResult, ALERT_WARNING);
        } else {
            // Attach the file extension
            $new_name = $new_name . '.' . $file_extension;

            // Rename file and update database
            try {
                rename_file($uniqueFileName, $new_name);
                redirectAfterFileMgmt();
            } catch (Exception $e) {
                set_flash_message('Error renaming file: ' . $e->getMessage(), ALERT_WARNING);
            }
        }
        // Handle upload submission
    }elseif (isset($_POST["submit"]) && isset($_FILES['file'])) {
        try {
            $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $origFileName = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_FILENAME)) . '.' . $fileType;
            $mime_type = strtolower($_FILES['file']['type']);
            $file_size = $_FILES['file']['size'];

        } catch (Exception $e) {
            set_flash_message('Error: ' . $e->getMessage(), ALERT_WARNING);
        }

        // Validate file
        if (!ValidateUpload($fileType, $file_size)) {
            redirectAfterFileMgmt();
        } else {
            // Get the selected destination folder from the form
            $destinationFolder = $_POST['destinationFolder'];

            // Generate a unique file name for the uploaded file within the selected folder
            $uniqueFileName = $uniqID . '.' . $fileType;
            $uniqueFileNameAndDirectory = $destinationFolder . $uniqueFileName;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $uniqueFileNameAndDirectory)) {
                try {
                    // Store the image metadata to the database
                    store_File($destinationFolder, $origFileName, $uniqueFileName, $mime_type, $email);
                } catch (Exception $e) {
                    set_flash_message('Error storing the image metadata to the database: ' . $e->getMessage(), ALERT_WARNING);
                }

                // Generate a thumbnail of the uploaded image
                if ($fileType == "jpeg" || $fileType == "jpg" || $fileType == "png") {
                    try {
                        // Create a name for the thumbnail
                        $target_thumb = $thumbDir . 'thumb_' . $uniqID . '.' . $fileType;

                        // Call the function to generate a thumbnail
                        generate_thumbnail($uniqueFileNameAndDirectory, $target_thumb, $fileType);
                    } catch (Exception $e) {
                        // Show error if thumbnail cannot be created
                        set_flash_message('Error creating thumbnail: ' . $e->getMessage(), ALERT_WARNING);
                    }
                }

                set_flash_message("'The file " . htmlspecialchars(basename($_FILES["file"]["name"])) . " has been uploaded", ALERT_SUCCESS);
                redirectAfterFileMgmt();
            } else {
                set_flash_message("Sorry, there was an error uploading your file", ALERT_WARNING);
            }
        }
    }elseif (isset($_POST['move_file'])) {

        // Get the file name and new destination from the form
        $fileToMove = $_POST['file_name'];
        $newDestination = $_POST['destination_folder'];

        $directory = pathinfo($fileToMove, PATHINFO_DIRNAME) . '/';
        $baseFileName = ltrim($fileToMove, $directory . '/');

        $newFilePath = $newDestination . $baseFileName;

        // Move the file
        if (rename($fileToMove, $newFilePath)) {
            try {
                if (update_file_metadata($baseFileName, $newDestination)) {
                    set_flash_message("File moved successfully.", ALERT_SUCCESS);
                }
            } catch (Exception $e) {
                set_flash_message('Error updating database: ' . $e->getMessage(), ALERT_WARNING);
            }
        } else {
            set_flash_message("Error moving the file.", ALERT_WARNING);
        }

        // Redirect or display a message
        redirectAfterFileMgmt();

    } else {
        set_flash_message("Error, no file uploaded. Please select a file.", ALERT_WARNING);
        redirectAfterFileMgmt();
    }
}

