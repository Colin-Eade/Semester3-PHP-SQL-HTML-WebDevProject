<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename contact_email_processing.php
 */

session_start();
ob_start();

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $sender = filter_var($_POST['sender'], FILTER_SANITIZE_EMAIL);
    $recipient = "support@inft2100.ca";
    $subject = strip_tags($_POST['subject']);
    $message = strip_tags($_POST['message']);
    $attachment = null;
    $attachmentName = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {

        try {

            $file = $_FILES['attachment'];
            validateEmailAttachment($file);

            $attachment = $_FILES['attachment']['tmp_name'];
            $attachmentName = $_FILES['attachment']['name'];

        } catch (Exception $e) {
            set_flash_message("Attachment Error: " . $e->getMessage(), ALERT_DANGER);
            writeToLog("Contact support email failure");
            redirect('contact.php');
            exit;
        }
    }
    
    try {

        $mail = new PHPMailer(true);

        // Try to send the email to support
        if (sendEmailViaMailTrap($mail, $sender, $recipient, $subject, $message, $attachment, $attachmentName))
        {
            // if the email sends then have an auto email send a response
            try {

                $mail = new PHPMailer(true);
                $subject = "Support Request Confirmation";
                $message = "Thank you for contacting us. We have received your support request and will get back to you shortly.";

                sendEmailViaMailTrap($mail, $recipient, $sender, $subject, $message, null);

                set_flash_message("Email sent. A confirmation will be sent to your email shortly.", ALERT_SUCCESS);
                writeToLog("Contact support email success");

            } catch (Exception $e) {
                set_flash_message("Email sent. Error sending confirmation email: " . $e->getMessage(), ALERT_WARNING);
                writeToLog("Support auto-response email failure");
            }
        }

    } catch (Exception $e) {
        set_flash_message("Error sending your email: " . $e->getMessage(), ALERT_DANGER);
        writeToLog("Contact support email failure");
    }

} 

redirect('contact.php');
exit;