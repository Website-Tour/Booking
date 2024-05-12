<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once("DBController.php");
require_once("BookingCart.php");

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$bookingCart = new BookingCart();

$method = $_SERVER['REQUEST_METHOD'];

// Xử lý các yêu cầu GET
if ($method === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'get_all_tour') {
        $result = $bookingCart->getAllTour();
        echo $result;
    }
    
    elseif (isset($_GET['action']) && $_GET['action'] === 'get_member_cart_item') {
        if (isset($_GET['member_id'])) {
            $member_id = $_GET['member_id'];
            $result = $bookingCart->getMemberCartItem($member_id);
            echo $result;
        } else {
            echo json_encode(array("message" => "Missing member_id parameter"));
        }
    }

    else {
        echo json_encode(array("message" => "Invalid action"));
    }
}

// Xử lý các yêu cầu POST
elseif ($method === 'POST') {
    // Xử lý yêu cầu thêm mục vào giỏ hàng
    if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
        // Kiểm tra các tham số cần thiết
        if (isset($_POST['name']) && isset($_POST['tourid']) && isset($_POST['image']) 
            && isset($_POST['quantity']) && isset($_POST['price']) && isset($_POST['member_id'])) {
            // Lấy dữ liệu từ POST
            $name = $_POST['name'];
            $tourid = $_POST['tourid'];
            $image = $_POST['image'];
            $quantity = $_POST['quantity'];
            $price = $_POST['price'];
            $member_id = $_POST['member_id'];
            
            $bookingCart->addToCart($name, $tourid, $image, $quantity, $price, $member_id);
            echo json_encode(array("message" => "Tour added to cart successfully"));
        } else {
            echo json_encode(array("message" => "Missing parameters"));
        }
    }

    else {
        echo json_encode(array("message" => "Invalid action"));
    }
}

elseif ($method === 'PUT') {

    parse_str(file_get_contents("php://input"), $put_vars);
    if (isset($put_vars['action']) && $put_vars['action'] === 'update_cart_quantity') {

        if (isset($put_vars['quantity']) && isset($put_vars['cart_id'])) {
            $quantity = $put_vars['quantity'];
            $cart_id = $put_vars['cart_id'];
            $bookingCart->updateCartQuantity($quantity, $cart_id);
            echo json_encode(array("message" => "Cart item quantity updated successfully"));
        } else {
            echo json_encode(array("message" => "Missing parameters"));
        }
    } else {
        echo json_encode(array("message" => "Invalid action"));
    }
}

elseif ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $delete_vars);
    if (isset($delete_vars['action']) && $delete_vars['action'] === 'delete_cart_item') {
        if (isset($delete_vars['cart_id'])) {
            $cart_id = $delete_vars['cart_id'];
            $bookingCart->deleteCartItem($cart_id);
        } else {
            echo json_encode(array("message" => "Missing cart_id parameter"));
        }
    } else {
        echo json_encode(array("message" => "Invalid action"));
    }
}

// Xử lý các yêu cầu không hợp lệ
else {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
