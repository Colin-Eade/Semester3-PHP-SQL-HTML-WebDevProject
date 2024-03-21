<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename terms_of_service.php
 */

include 'includes/header.php';

try {
    $termsOfServiceContent = get_policy_content('terms_of_service');
} catch (Exception $e) {
    set_flash_message($e->getMessage());
}
?>

    <div class='mt-3 mb-3'>
        <h1>Terms of Service</h1>

        <?php
        // Session flash
        if (has_flash_message()) {
            echo flash_message();
        }

        echo nl2br($termsOfServiceContent);

        ?>
    </div>

<?php
include 'includes/footer.php';
?>