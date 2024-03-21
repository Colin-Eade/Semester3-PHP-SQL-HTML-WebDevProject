<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename functions.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function validate_register_inputs(string $email, string $firstName, string $lastName, string $password,
                                  string $phoneExtension) : bool
{
    // Validate inputs
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        set_flash_message("Invalid email", ALERT_DANGER);
        return false;
    }
    elseif (empty($firstName) || !ctype_alpha($firstName)){
        set_flash_message("Invalid first name", ALERT_DANGER);
        return false;
    }
    elseif (empty($lastName) || !ctype_alpha($lastName)){
        set_flash_message("Invalid last name", ALERT_DANGER);
        return false;
    }
    elseif (empty($password)){
        set_flash_message("Invalid password", ALERT_DANGER);
        return false;
    }
    elseif (empty($phoneExtension) || !ctype_digit($phoneExtension) || strlen($phoneExtension) > 10)
    {
        set_flash_message("Invalid phone extension", ALERT_DANGER);
        return false;
    }
    return true;
}

/**
 * Redirects the user to the specified url
 * @param string $url
 * @return void
 */
function redirect(string $url) : void
{
    header("Location: " . $url);
    exit;
}

/**
 * Sets a flash message in the session
 * @param string $message The message you want to set
 * @param string $type The bootstrap alert type you want it to be
 * @return void
 */
function set_flash_message(string $message, string $type) : void
{
    $_SESSION['flash'] = '<div class="' . $type . '" role="alert">' . $message . '</div>';
}

/**
 * Retrieves current flash message from the session
 * @return string|null
 */
function get_flash_message() : ?string
{
    return $_SESSION['flash'] ?? null;
}

/**
 * Checks if flash message was set
 * @return bool
 */
function has_flash_message() : bool
{
    return isset($_SESSION['flash']);
}

/**
 * Removes flash message
 * @return void
 */
function remove_flash_message() : void
{
    unset($_SESSION['flash']);
}

/**
 * Retrieves and removes the current flash message from the session
 * @return string The flash message or an empty string if it doesn't exist
 */
function flash_message() : string
{
    $message = get_flash_message();
    remove_flash_message();
    return $message;
}

/**
 * Logs user activity
 * Writes an entry to a log file for each login attempt indicating whether the login was successful or no
 * The timestamp of each log entry is in the format 'Y-m-d H:i:s'
 * @param string $email The email of the user attempting to login
 * @param string $message The type of log entry
 * @return void
 */
function writeToLog(string $message) : void
{
    // Variables
    $directory = './logs/';
    $current_time = date('Y-m-d H:i:s');
    $file_name = 'system_activity.log';
    $user = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : "NA";

    // Make the logs directory if it doesn't exist
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Create or open the log file
    $log_file = fopen($directory . $file_name, 'a');

    // Create the log entry as an associative array
    $logEntry = [
        'time' => $current_time,
        'message' => strtoupper($message),
        'user' => $user
    ];

    // Convert the log entry to a JSON string
    $jsonLogEntry = json_encode($logEntry);

    // Write the JSON string to the log file
    fwrite($log_file, $jsonLogEntry . "\n");

    // Close the log file
    fclose($log_file);
}

/**
 * Gets all log entries from the log file
 * @return array An associative array of all the logs
 */
function getAllLogEntries() : array {

    // Variables
    $logFilePath = './logs/system_activity.log';
    $logEntries = [];

    // Check if the log file exists
    if (!file_exists($logFilePath)) {
        return $logEntries; // Return empty array if no log file
    }

    // Open the log file
    $logFile = fopen($logFilePath, "r");

    if ($logFile) {
        while (($line = fgets($logFile)) !== false) {

            // Decode each line from JSON to an associative array
            $logEntry = json_decode($line, true);

            if ($logEntry) {
                $logEntries[] = $logEntry;
            }
        }

        // Close the log file
        fclose($logFile);
    }

    return $logEntries;
}

/**
 * Gets all log entries from the last 24-hour period
 * @return array An associative array of the logs
 */
function getRecentLogEntries() : array {

    $logFilePath = './logs/system_activity.log';
    $recentLogEntries = [];
    $twentyFourHoursAgo = time() - 24 * 60 * 60; // 24 hours ago

    // Check if the log file exists
    if (!file_exists($logFilePath)) {
        return $recentLogEntries; // Return empty array if no log file
    }

    // Open the log file
    $logFile = fopen($logFilePath, "r");

    if ($logFile) {
        while (($line = fgets($logFile)) !== false) {

            // Decode each line from JSON to an associative array
            $logEntry = json_decode($line, true);

            if ($logEntry && strtotime($logEntry['time']) >= $twentyFourHoursAgo) {
                $recentLogEntries[] = $logEntry;
            }
        }

        // Close the log file
        fclose($logFile);
    }

    return $recentLogEntries;
}

/**
 * Gets the last_login field from a user
 * @param array $user The user we are grabbing the last login field from
 * @return string|null
 */
function get_last_login(array $user) : ?string
{
    return $user['last_time'];
}

function sendEmailViaMailTrap(PHPMailer $mail, string $sender, string $recipient, string $subject, string $message,
                              ?string $attachment = null, ?string $attachmentName = null) : bool
{
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '3309fd367e8d60';
    $mail->Password = '2be6990c6138b0';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 2525;

    $mail->setFrom($sender);
    $mail->addAddress($recipient);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;

    if (!empty($attachment)) {
        $mail->addAttachment($attachment, $attachmentName);
    }

    $result = $mail->send();

    if (!$result) {
        throw new Exception("Email could not be sent: " . $mail->ErrorInfo);
    }

    return true;
}

/**
 * Creates a dynamic navigation bar that offers the user different options depending on if they are logged in
 * @return void
 */
function create_dynamic_navbar(): void {
    echo '<nav class="navbar navbar-expand-lg bg-light">';
    echo '<div class="container">';
    // Navbar content starts here
    if (isset($_SESSION['user'])) {
        // User is signed in
        echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="file_management.php">File Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact Us</a>
                    </li>';

        // Admin options
        if ($_SESSION['user']['is_admin'] === true) {
            echo '<li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="admin_dashboard.php">Admin Dashboard</a></li>
                        <li><a class="dropdown-item" href="user_management.php">User Management</a></li>
                        <li><a class="dropdown-item" href="file_management_admin.php">File Management</a></li>
                        <li><a class="dropdown-item" href="https://www.paypal.com/signin">PayPal Management</a></li>
                        <li><a class="dropdown-item" href="logs.php">Logs</a></li>
                        <li><a class="dropdown-item" href="update_policy.php">Policy Management</a></li>
                    </ul>
                  </li>';
        }

        echo '<li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
              </li>
            </ul>
          </div>';
    } else {
        // User is not signed in
        echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sign-in.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sign-up.php">Sign Up</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact Us</a>
                    </li>
                </ul>
              </div>';
    }
    echo '</div>';
    echo '</nav>';
}

// Handling deletion
function delete_file(string $fileName, string $thumbDir)
{
    $fileToDelete = $fileName;
    $directory = pathinfo($fileName, PATHINFO_DIRNAME) . '/';
    $baseFileName = ltrim($fileName, $directory . '/');

    if(file_exists($fileToDelete)){
        // Delete thumbnail
        unlink($fileToDelete);

        $thumbnail_to_delete = $thumbDir . 'thumb_' . $baseFileName;

        // Check if the full-size image exists
        if (file_exists($thumbnail_to_delete)) {
            // Delete the full-size image
            unlink($thumbnail_to_delete);
        }

        set_flash_message("File deleted.", ALERT_INFO);
        redirectAfterFileMgmt();
        exit;
    }else{
        set_flash_message("Error: File not found.", ALERT_WARNING);
        redirectAfterFileMgmt();
    }
}

// Function to generate a thumbnail from an image
function generate_thumbnail($imagePath, $thumbPath, $imageFileType) : void
{
    try{
        // Define the size of the thumbnail
        $thumbWidth = $thumbHeight = 75;

        // Create a true color image as a canvas for the thumbnail
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // Create an image resource from the original image based on its type
        switch ($imageFileType) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($imagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($imagePath);
                break;
            default:
                die("");
        }

        // Get the original image's width and height
        list($originalWidth, $originalHeight) = getimagesize($imagePath);

        // Resize and copy from the original image to the thumbnail
        imagecopyresized($thumb, $image, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);

        // Save the thumbnail to the thumbnails directory
        switch ($imageFileType) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumb, $thumbPath);
                break;
            case 'gif':
                imagegif($thumb, $thumbPath);
                break;
            case 'png':
                imagepng($thumb, $thumbPath);
                break;
            default:
                die("Unsupported file type");
        }

        // Free up memory
        imagedestroy($image);
        imagedestroy($thumb);

    }catch(Exception $e)
    {
        set_flash_message('Error creating thumbnail: ' . $e->getMessage(), ALERT_WARNING);
    }

}

/**
 * Validate the uploaded file is not too large and is a valid type
 * @param string $fileType
 * @param int $file_size
 * @return bool
 */
function ValidateUpload(string $fileType, int $file_size) : bool
{

    if($file_size > 500000)
    {
        set_flash_message("Sorry your file is too large.", ALERT_WARNING);
        return false;
    }

    // Check the file type is valid - .jpg, .png, .jpeg, .pdf
    $allowedTypes = ['jpg', 'png', 'jpeg', 'pdf'];
    if (!in_array($fileType, $allowedTypes))
    {
        set_flash_message("Sorry we only accept jpg, png, jpeg, and pdf file types.", ALERT_WARNING);
        return false;
    }

    return true;
}

/**
 * Validates the input new file name
 * @param $fileName
 * @return string|null
 */
function validate_file_name($fileName) {
    // Check if the input is not empty
    if (empty($fileName)) {
        return "Please enter a new name.";
    }

    // Check if the input contains only valid characters for a file name
    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $fileName)) {
        return "Invalid characters in the new name. Please use only letters, numbers, underscores (_), periods (.), and hyphens (-).";
    }

    // If no validation issues were found, return null
    return null;
}

/**
 * Validates an email attachment
 * @param array $file
 * @return true
 * @throws Exception
 */
function validateEmailAttachment(array $file) {

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Unable to upload file");
    }

    return true;
}

/**
 * Dynamically redirect depending on if admin or not
 * @return void
 */
function redirectAfterFileMgmt() : void {
    if ($_SESSION['user']['is_admin'] === true) {
        // User is admin
        redirect('file_management_admin.php');
        exit;
    } else if ($_SESSION['user']['is_admin'] === false) {
        // User is not admin
        redirect('file_management.php');
        exit;
    }
}