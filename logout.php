<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename logout.php
 */

require_once 'lib/functions.php';
require_once 'config/constants.php';
require_once 'lib/db.php';

session_start();
$_SESSION = array();

session_destroy();

db_close();

setcookie('flash_message', 'Logout successful',time() + 60, "/");

redirect('sign-in.php');
