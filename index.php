<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename index.php
 */

// 1. Include the header
include 'includes/header.php';

// 2. Display introduction
echo "<div class='mt-3'>
    <h1>Welcome to our Web Application</h1>
    <p>Discover the amazing features we offer and join our community!</p>
</div>";

// 3. Provide navigation options
echo "<div class='navigation' >
    <a href='sign-up.php' class='btn btn-primary mb-5'>Sign Up</a>
    <a href='sign-in.php' class='btn btn-primary mb-5'>Sign In</a>
    <!-- Consider adding styles or icons to these buttons for better visual appeal -->
</div>";

// 4. Include the footer
include 'includes/footer.php';
