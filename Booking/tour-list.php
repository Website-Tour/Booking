<div id="tour-grid">
    <div class="txt-heading">
        <div class="txt-heading-label">Tours</div>
    </div>
    <?php

    require_once("DBController.php");
    require_once("BookingCart.php");

    $bookingCart = new BookingCart();

    $tourResult = json_decode($bookingCart->getAllTour(), true); // Chuyển đổi JSON thành mảng
    if (!empty($tourResult)) {
        foreach ($tourResult as $key => $tour) {
            ?>
 
            <div class="tour-item">
                < method="post"
                      action="index.php?action=add_to_cart">
                    <?= htmlspecialchars($row['book_title']) ?>
                    <input type="hidden" name="tourid" value="<?php echo $tour["tourid"]; ?>">
                    <input type="hidden" name="name" value="<?php echo $tour["name"]; ?>">
                    <input type="hidden" name="image" value="<?php echo $tour["image"]; ?>">
                    <input type="hidden" name="price" value="<?php echo $tour["price"]; ?>">
                    <div class="tour-image">
                        <img src="<?php echo $tour["image"]; ?>">
                        <div class="tour-title">
                            <?php echo $tour["name"]; ?>
                        </div>
                    </div>
                    <div class="tour-footer">
                        <div class="tour-right">
                            <input type="text" name="quantity" value="1"
                                   size="2" class="input-cart-quantity"/><input type="submit"
                                                                                value="Add to Cart"
                                                                               class="btnAddAction"/>
                        </div>
                        <div class="tour-price float-left"
                             id="tour-price-<?php echo $tour["tourid"]; ?>"><?php echo "$" . $tour["price"]; ?></div>

                    </div>
                </form>
            </div>
            <?php
        }
    }
    ?>
</div>
