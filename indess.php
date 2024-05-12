<?php
session_start();
require_once "DBController.php";
require_once "BookingCart.php";

$db = new DBController();
$bookingCart = new BookingCart();

// Xử lý đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        if ($bookingCart->auth($_POST["username"], $_POST["password"])) {
            $_SESSION["user"] = $_POST["username"];
            $_SESSION["authenticated"] = true;
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Tên người dùng hoặc mật khẩu không chính xác.";
        }
    }
}

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa, chuyển hướng đến trang đăng nhập
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: login.php");
    exit();
}

// Lấy danh sách lịch trình
$itineraries = json_decode($bookingCart->getAllTour(), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" type="text/css" rel="stylesheet" />
    <script src="jquery-3.7.1.min.js"></script>
    <script src="jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <head>
        
    <title>BOOKING</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">BOOKING</h1>       
        <form action="index.php" method="post">
            <div class="form-group">
                <label for="customerName">Name:</label>
                <input type="text" class="form-control" id="customerName" name="customerName" required>
            </div>
            <div class="form-group">
                <label for="tourId">Select:</label>
                <select class="form-control" id="tourId" name="tourId" required>
                    <option value="1">Tour A</option>
                    <option value="2">Tour B</option>
                    <!-- Thêm các tùy chọn khác tại đây -->
                </select>
            </div>
            <div class="form-group">
                <label for="adult_tickets">Adult (18+ age):</label>
                <input type="number" id="adult_tickets" name="adult_tickets" min="0" required>
            </div>
            <div class="form-group">
                <label for="youth_tickets">Youth (13-17 age):</label>
                <input type="number" id="youth_tickets" name="youth_tickets" min="0" required>
            </div>
            <div class="form-group">
                <label for="children_tickets">Children (0-12 age):</label>
                <input type="number" id="children_tickets" name="children_tickets" min="0" required>
            </div>
            <label>Time:</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="time_12" name="tour_time" value="12:00" required>
                <label class="form-check-label" for="time_12">12:00</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="time_17" name="tour_time" value="17:00" required>
                <label class="form-check-label" for="time_17">17:00</label>
            </div>
            <div class="form-group">
                <label for="startDate">Start-Date:</label>
                <input type="date" class="form-control" id="startDate" name="startDate" required>
            </div>
            <div class="form-group">
                <label for="endDate">End-Date:</label>
                <input type="date" class="form-control" id="endDate" name="endDate" required>
            </div>
            <br>
            <div id="shopping-cart">
                <div class="txt-heading">
                    <a id="btnEmpty" href="index.php?action=empty"><img src="empty-cart.png" alt="empty-cart" title="Empty Cart" class="float-right" /></a>
                    <div class="cart-status">
                        <div class="form-group">
                            <label for="quantity">Total Quantity:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" readonly>
                        </div>
                        <div>Total Price: $<span id="total-price">0</span></div>
                    </div>
                </div>
            </div> 
            <button type="submit" class="btn btn-primary">Book</button>
        </form>
        
    </div>

    <script>
        const adultInput = document.getElementById('adult_tickets');
        const youthInput = document.getElementById('youth_tickets');
        const childrenInput = document.getElementById('children_tickets');
        const totalInput = document.getElementById('quantity');
    
        adultInput.addEventListener('input', updateTotal);
        youthInput.addEventListener('input', updateTotal);
        childrenInput.addEventListener('input', updateTotal);
    
        function updateTotal() {
            const adultCount = parseInt(adultInput.value) || 0;
            const youthCount = parseInt(youthInput.value) || 0;
            const childrenCount = parseInt(childrenInput.value) || 0;
    
            // Giá tiền cho từng loại vé (đơn vị là $)
            const adultPrice = 100;
            const youthPrice = 70;
            const childrenPrice = 50;
    
            // Tính tổng giá tiền
            let totalCost = adultCount * adultPrice + youthCount * youthPrice + childrenCount * childrenPrice;
    
            // Kiểm tra khoảng thời gian
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const oneDay = 24 * 60 * 60 * 1000; // Số mili giây trong 1 ngày
    
            if ((endDate - startDate) / oneDay > 1) {
                // Nếu end-date cách start-date hơn 1 ngày, thêm 30%
                totalCost *= 1.3;
            }
    
            totalInput.value = totalCost.toFixed(2); // Làm tròn đến 2 chữ số thập phân
        }
    </script>
        <script>
    $(document).ready(function() {
        $(".select-guests input").replaceWith(function() {
            const id = $(this).attr("id");
            const price = $(this).data("price");
            const min = $(this).attr("min");
            const max = $(this).attr("max");
            const defaultValue = $(this).val();

            return `
                <div class="slider-container">
                    <label for="${id}">${$(this).attr("name")}:</label>
                    <div id="${id}-slider" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all">
                        <span class="ui-slider-range ui-corner-top ui-widget-header" style="left: 0%; width: 0%;"></span>
                        <span class="ui-slider-handle ui-corner-all ui-state-default" style="left: ${defaultValue / max * 100}%"></span>
                    </div>
                    <span id="price-per-guest-${id}">${defaultValue * price}</span>
                </div>
            `;
        });

        $(".slider-container").each(function() {
            const id = $(this).find("input").attr("id");
            const price = $(this).find("input").data("price");
            const min = $(this).find("input").attr("min");
            const max = $(this).find("input").attr("max");

            $("#" + id + "-slider").slider({
                range: true,
                min: min,
                max: max,
                values: [min, parseInt($(this).find("input").val())],
                slide: function(event, ui) {
                    $(this).find("input").val(ui.values[1]);
                    updateTotalPrice();
                    $("#price-per-guest-" + id).text(ui.values[1] * price);
                },
            });
        });
    });
    </script>
        <script>
            function increment_quantity(cart_id, price) {
                var inputQuantityElement = $("#input-quantity-"+cart_id);
                var newQuantity = parseInt($(inputQuantityElement).val())+1;
                var newPrice = newQuantity * price;
                save_to_db(cart_id, newQuantity, newPrice);
            }
            function decrement_quantity(cart_id, price) {
                var inputQuantityElement = $("#input-quantity-"+cart_id);
                if($(inputQuantityElement).val() > 1) 
                {
                    var newQuantity = parseInt($(inputQuantityElement).val()) - 1;
                    var newPrice = newQuantity * price;
                    save_to_db(cart_id, newQuantity, newPrice);
                }
            }
            function save_to_db(cart_id, new_quantity, newPrice) {
                var inputQuantityElement = $("#input-quantity-"+cart_id);
                var priceElement = $("#cart-price-"+cart_id);
                $.ajax({
                    url : "update_cart_quantity.php",
                    data : "cart_id="+cart_id+"&new_quantity="+new_quantity,
                    type : 'post',
                    success : function(response) {
                        $(inputQuantityElement).val(new_quantity);
                        $(priceElement).text("$"+newPrice);
                        var totalQuantity = 0;
                        $("input[id*='input-quantity-']").each(function() {
                            var cart_quantity = $(this).val();
                            totalQuantity = parseInt(totalQuantity) + parseInt(cart_quantity);
                        });
                        $("#total-quantity").text(totalQuantity);
                        var totalItemPrice = 0;
                        $("div[id*='cart-price-']").each(function() {
                            var cart_price = $(this).text().replace("$","");
                            totalItemPrice = parseInt(totalItemPrice) + parseInt(cart_price);
                        });
                        $("#total-price").text(totalItemPrice);
                    }
                });
            }
            function updateTotalPrice() {
                const itinerary = document.getElementById("itinerary").value;
                const guests = {
                    adult: parseInt(document.getElementById("guests-adult").value),
                    youth: parseInt(document.getElementById("guests-youth").value),
                    child: parseInt(document.getElementById("guests-child").value),
            };
                let totalPrice = 0;
                if (itinerary && guests) {
                    const itineraryData = itineraries[itinerary];
                    totalPrice += itineraryData.price_adult * guests.adult;
                    totalPrice += itineraryData.price_youth * guests.youth;
                    totalPrice += itineraryData.price_child
                }
            }

            function isAuthenticated() {
                return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
            }

            if (!isAuthenticated()) {
                header("Location: login.php");
                exit();
            }
        </script>
    <script>
    $(document).ready(function() {

        $("#btnAddCart").click(function() {
            addToCart();
            $("#cart-items").append(`
                <li class="cart-item">
                    <span class="item-name">Tour ${$("#itinerary").val()}</span>
                    <span class="item-quantity">x ${$("#guests-adult").val()} + ${$("#guests-youth").val()} + ${$("#guests-child").val()}</span>
                    <span class="item-price">$ ${$("#total-price").text()}</span>
                </li>
            `).hide().fadeIn("slow");
        });
        // Xử lý khi xóa mục khỏi giỏ hàng
        $(".btnRemoveAction").click(function() {
            $(this).parent().fadeOut("slow", function() {
                $(this).parent().remove();
            });
        });
    });
    </script>


</body>
</html>
