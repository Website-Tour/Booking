<?php
require_once("BookingCart.php");

$member_id = 2; 

$bookingCart = new BookingCart();
 
$bookingCart->updateCartQuantity($_POST["https://web.postman.co/workspace/fdedc87d-4573-42cf-87a8-2b9dc591f03a/request/34007742-5d07dd75-3f63-4464-ba51-31753683adec?action=share&source=copy-link&creator=34007742"], 
$_POST["https://web.postman.co/workspace/fdedc87d-4573-42cf-87a8-2b9dc591f03a/request/34007742-287c11a7-b1a2-42a2-b10f-3919b9b41267?action=share&source=copy-link&creator=34007742"]);
                
?>