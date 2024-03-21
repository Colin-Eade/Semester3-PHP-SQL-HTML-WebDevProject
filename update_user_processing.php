<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename update_user_processing.php
 */

session_start();
ob_start();

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // Delete button clicked
    if (isset($_POST['delete'])) {

        // Get fields from form
        $email = $_POST['email'];
        $user_id = $_POST['id'];

        try {
            // Delete the user, log it, and set a flash
            if (delete_user($email)) {
                set_flash_message("Deletion Successful", ALERT_SUCCESS);
                writeToLog("deletion success on user id {$user_id}");
                // No deletion, log it, and set a flash
            } else {
                set_flash_message("No deletion occurred", ALERT_DANGER);
                writeToLog("deletion failure on user id {$user_id}");
            }
            // Error, log it, and set a flash
        } catch (Exception $e) {
            set_flash_message("Error: " . $e->getMessage(), ALERT_DANGER);
            writeToLog("deletion error on user id {$user_id}");
        }

        // Update form was submitted
    } else if (isset($_POST['update'])) {

        // Grab fields
        $user_id = $_POST['id'];
        $email = $_POST['email'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];
        $user_type = $_POST['user_type'];
        $profile_img = $_FILES['profile_img'];

        // Catch if no field was changed
        if (empty($email) && empty($first_name) && empty($last_name) && empty($password) && empty($phone) && empty($user_type) &&
            (empty($profile_img) || $profile_img['error'] !== UPLOAD_ERR_OK || $profile_img['size'] === 0)) {
            set_flash_message("No fields changed.", ALERT_DANGER);
        } else {
            try {
                $result = admin_update_user($user_id, $email, $first_name, $last_name, $password, $phone, $user_type, $profile_img);
                if ($result) {
                    set_flash_message("Update successful", ALERT_SUCCESS);
                    writeToLog("update success on user id {$user_id}");
                }
            } catch (Exception $e) {
                set_flash_message("Error: " . $e->getMessage(), ALERT_DANGER);
                writeToLog("update failure on user {$user_id}");
            }
        }

        // Email sent
    } else if (isset($_POST['send_email'])) {

        // Grab fields
        $sender = filter_var($_POST['sender'], FILTER_SANITIZE_EMAIL);
        $recipient = filter_var($_POST['recipient'], FILTER_SANITIZE_EMAIL);
        $subject = strip_tags($_POST['subject']);
        $message = strip_tags($_POST['message']);
        $attachment = null;
        $attachmentName = null;

        // Check for attachment
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {

            try {

                // Validate the attachment
                $file = $_FILES['attachment'];
                validateEmailAttachment($file);

                $attachment = $_FILES['attachment']['tmp_name'];
                $attachmentName = $_FILES['attachment']['name'];

            } catch (Exception $e) {
                set_flash_message("Attachment Error: " . $e->getMessage(), ALERT_DANGER);
                writeToLog("Email send failure");
                redirect('user_management.php');
                exit;
            }
        }
        
        try {

            $mail = new PHPMailer(true);

            // Try to send the email 
            sendEmailViaMailTrap($mail, $sender, $recipient, $subject, $message, $attachment, $attachmentName);
            set_flash_message("Email successful", ALERT_SUCCESS);
            writeToLog("Email send successful");

        } catch (Exception $e) {
            set_flash_message("Error: " . $e->getMessage(), ALERT_DANGER);
            writeToLog("Email send failure");
        }
    }
}

redirect('user_management.php');
exit;