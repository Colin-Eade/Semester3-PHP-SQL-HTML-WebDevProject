<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename header.php
 */

session_start();
ob_start();

require_once 'config/constants.php';
require_once 'lib/db.php';
require_once 'lib/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INFT2100 Assignment 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
<div class="d-flex flex-column min-vh-100"> <!-- Flex container -->
    <?php create_dynamic_navbar(); ?>
    <div class="container">
