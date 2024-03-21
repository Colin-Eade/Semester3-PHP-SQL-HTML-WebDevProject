<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename payment_from.php
 */

include 'includes/header.php';

$productPrices = [
    'tshirt' => 36.68,
    'crewneck' => 47.80,
    'hoodie' => 45.50,
];

// Initialize variables
$totalCost = 0;
$costWithHST = 0;
$selectedProductNames = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atLeastOneProductSelected = false;

    foreach ($productPrices as $productName => $price) {
        if (isset($_POST[$productName])) {
            $size = $_POST[$productName . '-size'] ?? '';
            $totalCost += $price;
            $selectedProductNames .= $productName . ' (Size: ' . $size . '), ';
            $atLeastOneProductSelected = true;
        }
    }

    if (!$atLeastOneProductSelected) {
        set_flash_message("No product selected.", ALERT_WARNING);
        redirect('shop.php');
    }

    // Add HST to the price
    $costWithHST = $totalCost * 1.13;
    // Trim leading space from the selected product names
    $selectedProductNames = substr($selectedProductNames, 0,-2);
}else {
    set_flash_message("Please select a product before continuing to checkout.", ALERT_WARNING);
    redirect('shop.php');
}

?>

<br/>
<div class="row justify-content-center align-items-center">
    <div class="card text-center">
        <div class="card-header">
            <h2 class="card-title">Shopping Cart</h2>
        </div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php
                    // Display selected product images
                    foreach ($productPrices as $productName => $price) {
                        if (isset($_POST[$productName])) {
                            $imagePath = "images/{$productName}img.jpg";
                            echo '<div class="col">';
                            echo '<div class="card">';
                            echo '<br/><img src="' . $imagePath . '" class="card-img-top mx-auto" alt="' . $productName . '"  style="width: 100px; height: 100px;">';
                            echo '<div class="card-body">';
                            echo '<h6 class="card-title">';
                            if ($productName == 'tshirt') {
                                echo 'T-Shirt';
                            }
                            if ($productName == 'crewneck') {
                                echo 'Crewneck Sweater';
                            }
                            if ($productName == 'hoodie') {
                                echo 'Hoodie';
                            }
                            if (!empty($size)) {
                                echo '<br/>(Size: ' . $size . ')';
                            }
                            echo '</h6>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
            </div>
            <div class="mt-3">
                <div class="card">
                    <br/>
                    <h6>Sub Total: $<?= number_format($totalCost, 2) ?></h6>
                    <h6>Total +HST: $<?= number_format($costWithHST, 2) ?></h6>
                    <br/>
                </div>
                <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                    <!-- Identify your business so that you can collect the payments -->
                    <!-- ToDo: update email to MY business sandbox email-->
                    <input type="hidden" name="business" value="sb-q0gdw28084542@business.example.com">

                    <!-- Specify a Buy Now button -->
                    <input type="hidden" name="cmd" value="_xclick">

                    <!-- Specify details about the item that buyers will purchase -->
                    <!-- ToDo: update with calculated information based on customer cart-->
                    <input type="hidden" name="item_name" value="<?php echo $selectedProductNames ?>">
                    <input type="hidden" name="amount" value="<?php echo $costWithHST ?>">
                    <input type="hidden" name="currency_code" value="CAD">
                    <!-- Return and notification URLs -->
                    <!-- ToDo: update with urls for redirects-->
                    <input type="hidden" name="return" value="https://5c94-2607-fea8-59df-9a00-29e6-4b13-112c-d925.ngrok-free.app/ICE/ICE5/success.php">
                    <input type="hidden" name="cancel_return" value="https://5c94-2607-fea8-59df-9a00-29e6-4b13-112c-d925.ngrok-free.app/ICE/ICE5/cancel.php">
                    <input type="hidden" name="notify_url" value="https://5c94-2607-fea8-59df-9a00-29e6-4b13-112c-d925.ngrok-free.app/ICE/ICE5/ipn_listener.php">
                    <br/>
                    <!-- Display the payment button -->
                    <input type="image" name="submit" border="0"
                           src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif"
                           alt="PayPal - The safer, easier way to pay online">
                    <img alt="" border="0" width="1" height="1"
                         src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif">
                </form>
            </div>
        </div>
    </div>
</div>


<?php

include 'includes/footer.php';

?>
