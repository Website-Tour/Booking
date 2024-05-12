<?php
session_start();

require_once("DBController.php");
require_once("BookingCart.php");

$db = new DBController();
$bookingCart = new BookingCart();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        if ($bookingCart->auth($_POST["username"], $_POST["password"])) {
            $_SESSION["user"] = $_POST["username"];
            $_SESSION["authenticated"] = true;
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Tên người dùng hoặc mật khẩu không chính xác.";
        }
    }
}

// Lấy danh sách lịch trình từ API
$api_url = "http://localhost:3000/bookings"; 
$tourResult = json_decode(file_get_contents($api_url), true);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enriched Responsive Booking Cart in PHP</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        td {
            vertical-align: middle;
        }

        img {
            max-height: 100px;
        }
    </style>

</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">BOOKING</h1>
        <form action="index.php" method="post">
            <div class="form-group">
                <label for="customerName">Name:</label>
                <input type="text" class="form-control" id="customerName" name="customerName" required>
            </div>
            <div id="shopping-cart">
                <div class="txt-heading">
                    <a id="btnEmpty" href="index.php?action=empty"><img src="empty-cart.png" alt="empty-cart" title="Empty Cart" class="float-right" /></a>
                    <div class="cart-status">
                        <div class="form-group">
                            <label for="quantity">Total Price:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-form__block">
                <form action="" class="booking-form">
                    <div class="input-group">
                        <div class="booking-block">
                            <label for="itinerary">Itinerary:</label>
                            <select name="itinerary" id="itinerary">
                                <?php foreach ($tourResult as $tour) : ?>
                                    <option value="<?php echo $tour['tourid']; ?>"><?php echo $tour['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="booking-block guests-block">
                            <label class="booking_form_input_label">Guests</label>
                            <div class="booking-guests-result">
                                <div class="select-guests-block input-select-field">
                                    <div class="input-select-title">
                                        <div class="input_select_title_value">Adults (18+ years)</div>
                                        <div class="input_select_wrapper">
                                            <input type="number" id="guests-adult" class="input_select_input select-guests" name="guests[adult]" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="select-guests-block input-select-field">
                                    <div class="input-select-title">
                                        <div class="input_select_title_value">Youth (13-17 years)</div>
                                        <div class="input_select_wrapper">
                                            <input type="number" id="guests-youth" class="input_select_input select-guests" name="guests[youth]" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="select-guests-block input-select-field">
                                    <div class="input-select_title">
                                        <div class="input_select_title_value">Children (0-12 years)</div>
                                        <div class="input_select_wrapper">
                                            <input type="number" id="guests-child" class="input_select_input select-guests" name="guests[child]" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                            <label for="qsuantity">Total Guest:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <?php require_once "tour-list.php"; ?>
        </form>
    </div>

    <script>
        function isAuthenticated() {
            return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
        }

        if (!isAuthenticated()) {
            header("Location: login.php");
            exit();
        }

        $(document).ready(function() {
            // Xử lý khi nhấn nút "Add to Cart"
            $(".btnAddAction").click(function(e) {
                e.preventDefault(); // Ngăn chặn hành động mặc định của form

                // Lấy thông tin của tour từ form
                var form = $(this).closest("form");
                var tourid = form.find("input[name='tourid']").val();
                var name = form.find("input[name='name']").val();
                var image = form.find("input[name='image']").val();
                var price = form.find("input[name='price']").val();
                var quantity = form.find("input[name='quantity']").val();

                // Tạo đối tượng dữ liệu để gửi lên API
                var data = {
                    tourid: tourid,
                    name: name,
                    image: image,
                    price: price,
                    quantity: quantity
                };

                // Gửi yêu cầu POST đến API
                $.ajax({
                    url: "http://localhost:3000/bookings", // Đường dẫn của API
                    type: "POST", // Phương thức POST
                    contentType: "application/json", // Định dạng dữ liệu gửi đi
                    data: JSON.stringify(data), // Chuyển đổi dữ liệu sang JSON
                    success: function(response) {
                        // Xử lý khi nhận được phản hồi từ API (nếu cần)
                        console.log("Success:", response);
                    },
                    error: function(xhr, status, error) {
                        // Xử lý khi có lỗi xảy ra trong quá trình gửi yêu cầu
                        console.error("Error:", error);
                    }
                });
            });

            // Xử lý sự kiện khi thay đổi số lượng khách
            $(".select-guests input").change(function() {
                calculateTotalGuests();
            });

            // Tính tổng số khách
            function calculateTotalGuests() {
                var adults = parseInt($("#guests-adult").val()) || 0;
                var youth = parseInt($("#guests-youth").val()) || 0;
                var children = parseInt($("#guests-child").val()) || 0;
                var totalGuests = adults + youth + children;
                $("#total-guests").text(totalGuests);
            }

            // Tính tổng giá
            function calculateTotalPrice() {
                var price = parseFloat($("#tour-price").text().replace("$", "")); // Lấy giá của tour
                var quantity = parseInt($("#quantity").val()) || 0; // Lấy số lượng được chọn
                var totalPrice = price * quantity; // Tính tổng giá

                $("#total-price").text(totalPrice.toFixed(2)); // Hiển thị tổng giá với hai chữ số thập phân
            }

            $(document).ready(function() {
                // Xử lý khi số lượng tour thay đổi
                $("#quantity").change(function() {
                    calculateTotalPrice();
                });
            });

        });
    </script>

</body>

</html>


