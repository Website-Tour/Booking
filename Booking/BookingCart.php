<?php
require_once ("DBController.php");

class BookingCart extends DBController {
    function getAllTour() {
        $query = "SELECT * FROM tour";
        $tourResult = $this->getDBResult($query);
        return json_encode($tourResult);
    }
    function getMemberCartItem($member_id) {
        $conn = DBController::getConnection();

        $query = "SELECT tour.*, cart.id as cart_id, cart.quantity 
                  FROM tour 
                  INNER JOIN cart ON tour.tourid = cart.tour_id 
                  WHERE cart.member_id = ?";
        
        $params = array( 
            array(
                "param_type" => "i",
                "param_value" => $member_id
            )
        );
        
        $cartResult = $this->getDBResult($query, $params);
        return json_encode($cartResult);
    }

 
    function getTourById($tourid) 
    {
        $query = "SELECT * FROM tour WHERE tourid=?";
        
        $params = array(
            array(
                "param_type" => "s",
                "param_value" => $tourid
            )
        );
        
        $tourResult = $this->getDBResult($query, $params);
        return json_encode($tourResult);
    }

    function getCartItemByTour($tourid, $member_id)
    {
        $query = "SELECT * FROM cart WHERE tour_id = ? AND member_id = ?";
        
        $params = array(
            array(
                "param_type" => "i",
                "param_value" => $tourid
            ),
            array(
                "param_type" => "i",
                "param_value" => $member_id
            )
        );
        
        $cartResult = $this->getDBResult($query, $params);
        return json_encode($cartResult);
    }

    function addToCart($name, $tourid, $image, $quantity, $price, $member_id)
    {
        // Câu lệnh SQL để chèn dữ liệu vào bảng cart
        $query = "INSERT INTO cart (name, tour_id, image, quantity, price, date, member_id) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?)";
    
        // Mảng chứa các tham số và kiểu dữ liệu tương ứng
        $params = array(
            array(
                "param_type" => "s",
                "param_value" => $name
            ),
            array(
                "param_type" => "s",
                "param_value" => $tourid
            ),
            array(
                "param_type" => "s",
                "param_value" => $image
            ),
            array(
                "param_type" => "i",
                "param_value" => $quantity
            ),
            array(
                "param_type" => "d",
                "param_value" => $price
            ),
            array(
                "param_type" => "i",
                "param_value" => $member_id
            )
        );

        $this->updateDB($query, $params);
    }
    function updateCartQuantity($quantity, $cart_id)
    {
        $query = "UPDATE cart SET quantity = ? WHERE id = ?";
        
        $params = array(
            array(
                "param_type" => "i",
                "param_value" => $quantity
            ),
            array(
                "param_type" => "i",
                "param_value" => $cart_id
            )
        );
        
        $this->updateDB($query, $params);
    }
    function deleteCartItem($cart_id) {
        $query = "DELETE FROM cart WHERE id = ?";
        $conn = $this->getConnection();
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("i", $cart_id);

        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }

        $stmt->close();
        
        if ($stmt->affected_rows === 0) {
            echo json_encode(array("message" => "Failed to delete cart item. Item may not exist."));
        } else {
            echo json_encode(array("message" => "Cart item deleted successfully."));
        }
    }  
    
    function emptyCart($member_id)
    {
        $conn = $this->getConnection();
        $query = "DELETE FROM cart WHERE member_id = ?";

        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("i", $member_id);
        if (!$stmt->execute()) {
            die("Error executing statement: " . $stmt->error);
        }
        
        $stmt->close();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(array("message" => "Cart emptied successfully."));
        } else {
            echo json_encode(array("message" => "Failed to empty cart."));
        }
    }
}
?>
