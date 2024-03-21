<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename cancel.php
 */

require_once "includes/header.php"
?>
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="alert alert-primary">
                <h4 class="alert-heading">Payment Cancelled</h4>
                <p>Your transaction has been cancelled.</p>
                <hr>
                <p class="mb-0">You can return to the shop by clicking the button below.</p>
            </div>
            <a href="http://localhost:8181/Assignments/Assignment2/shop.php" class="btn btn-primary">Return to Merch Shop</a>
        </div>
    </div>
<?php
require_once "includes/footer.php"
?>