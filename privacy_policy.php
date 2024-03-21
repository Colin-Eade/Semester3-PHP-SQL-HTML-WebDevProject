<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename privacy_policy.php
 */

include 'includes/header.php';

try {
    $privacyPolicyContent = get_policy_content('privacy_policy');
} catch (Exception $e) {
    set_flash_message($e->getMessage());
}
?>

<div class='mt-3 mb-3'>
    <h1>Privacy Policy</h1>

    <?php
    // Session flash
    if (has_flash_message()) {
        echo flash_message();
    }

    echo nl2br($privacyPolicyContent);

    ?>
</div>

<?php
include 'includes/footer.php';
?>
