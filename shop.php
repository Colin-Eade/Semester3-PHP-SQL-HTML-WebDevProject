<?php
/**
 * @author Colin Eade
 * @author Megan Clarke
 * @filename shop.php
 */

include 'includes/header.php';

?>
<br/>
    <div class="row justify-content-center align-items-center">
        <div class="card text-center">
            <div class="card-header">
                <h2 class="card-title">Merch Shop</h2>
                <?php
                    if(has_flash_message()){
                    echo flash_message();
                    }
                ?>
            </div>
                <div class="card-body">
                    <form action="payment_form.php" method="post">
                        <!-- Checkbox for each product -->
                        <div class="row row-cols-1 row-cols-md-3 g-4">
                            <div class="col">
                                <div class="card">
                                    <img src="images/tshirtimg.jpg" class="card-img-top" alt="T-shirt Image">
                                    <div class="card-body">
                                        <h6 class="card-title">T-Shirt: $<?= number_format(36.68, 2) ?></h6>
                                        <br/>
                                        <label for="tshirt-size">Select Size:</label>
                                        <select id="tshirt-size" name="tshirt-size">
                                            <option value="small">Small</option>
                                            <option value="medium">Medium</option>
                                            <option value="large">Large</option>
                                        </select><br/>
                                        <label for="tshirt">Add to Cart: </label>
                                        <input type="checkbox" id="tshirt" name="tshirt" value="tshirt">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card">
                                    <img src="images/crewneckimg.jpg" class="card-img-top" alt="Crewneck Image">
                                    <div class="card-body">
                                        <h6 class="card-title">Crew Neck Sweater: $<?= number_format(47.80, 2) ?></h6>
                                        <br/>
                                        <label for="crewneck-size">Select Size:</label>
                                        <select id="crewneck-size" name="crewneck-size">
                                            <option value="small">Small</option>
                                            <option value="medium">Medium</option>
                                            <option value="large">Large</option>
                                        </select><br/>
                                        <label for="crewneck">Add to Cart: </label>
                                        <input type="checkbox" id="crewneck" name="crewneck" value="crewneck">
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card">
                                    <img src="images/hoodieimg.jpg" class="card-img-top" alt="Hoodie Image">
                                    <div class="card-body">
                                        <h6 class="card-title">Hoodie: $<?= number_format(45.50, 2) ?></h6>
                                        <br/>
                                        <label for="hoodie-size">Select Size:</label>
                                        <select id="hoodie-size" name="hoodie-size">
                                            <option value="small">Small</option>
                                            <option value="medium">Medium</option>
                                            <option value="large">Large</option>
                                        </select><br/>
                                        <label for="hoodie">Add to Cart: </label>
                                        <input type="checkbox" id="hoodie" name="hoodie" value="hoodie">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <input class="btn btn-primary btn-lg" type="submit" value="Go to Checkout">
                        </div>
                    </form>
                </div>
        </div>
    </div>

<?php

include 'includes/footer.php';

?>
