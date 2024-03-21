<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename update_policy_processing.php
 */

session_start();
ob_start();

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';

if (!isset($_SESSION['user'])) {
    // Not authenticated, redirect to login
    redirect('sign-in.php');
    exit;
} else if ($_SESSION['user']['is_admin'] === false) {
    // Not admin, redirect to dashboard
    redirect('dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['policyFile'])) {

    $tableName = $_POST['tableName'];
    $allowedTables = ['privacy_policy', 'acceptable_use_policy', 'terms_of_service'];
    $policyFile = $_FILES['policyFile'];

    // Check for allowed tables
    if (!in_array($tableName, $allowedTables)){
        set_flash_message("Invalid table name.", ALERT_DANGER);

    }
    // Check for upload errors
    else if (!$policyFile['error'] == UPLOAD_ERR_OK) {
        set_flash_message("Unable to upload file.", ALERT_DANGER);
    }
    // Check if file is a text file
    else if ($policyFile['type'] != 'text/plain'){
        set_flash_message("Only text (.txt) files are accepted.", ALERT_DANGER);
    }

    $content = file_get_contents($policyFile['tmp_name']);

    try {
        update_policy_table($tableName, $content);
        set_flash_message("Policy update successful!", ALERT_SUCCESS);
        writeToLog("Policy update success on table {$tableName}");

    } catch (Exception $e) {
        set_flash_message($e->getMessage() , ALERT_DANGER);
        writeToLog("Policy update failure on table {$tableName}");
    }
}

redirect('update_policy.php');
exit;