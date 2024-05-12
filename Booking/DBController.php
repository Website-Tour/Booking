<?php
class DBController {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "shopping_cart";
    private static $conn;

    function __construct() {
        self::$conn = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        if (mysqli_connect_errno()) {
            die("Failed to connect to MySQL: " . mysqli_connect_error());
        }
    }

    public static function getConnection() {
        if (empty(self::$conn)) {
            new self();
        }
        return self::$conn;
    }

    function auth($username, $password) {
        // Thực hiện kiểm tra xác thực từ cơ sở dữ liệu
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = self::$conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                return true;
            }
        }
        return false;
    }


    function getDBResult($query, $params = array()) {
        $sql_statement = self::$conn->prepare($query);
        if (!empty($params)) {
            $this->bindParams($sql_statement, $params);
        }
        $sql_statement->execute();
        $result = $sql_statement->get_result();

        $resultset = array(); // Initialize an empty array to store the result set

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultset[] = $row;
            }
        }
        return $resultset; // Return the result set array
    }

    function updateDB($query, $params = array()) {
        // Chuẩn bị câu lệnh SQL
        $sql_statement = self::$conn->prepare($query);
        
        // Nếu có tham số, gán các tham số và kiểu dữ liệu tương ứng
        if (!empty($params)) {
            $this->bindParams($sql_statement, $params);
        }
        
        // Thực thi câu lệnh SQL
        $sql_statement->execute();
    
        $sql_statement->close();
    }
    

    function bindParams($sql_statement, $params) {
        if ($sql_statement instanceof mysqli_stmt) { // Kiểm tra xem $sql_statement có phải là một mysqli_stmt hợp lệ không
            $param_type = "";
            $bind_params = array();
            
            foreach ($params as $query_param) {
                $param_type .= $query_param["param_type"];
                $bind_params[] = &$query_param["param_value"];
            }

            // Thêm $param_type vào đầu mảng $bind_params
            array_unshift($bind_params, $param_type);
            
            // Sử dụng call_user_func_array để truyền các tham số vào hàm bind_param
            call_user_func_array(array($sql_statement, 'bind_param'), $bind_params);
        } else {
            die("Invalid mysqli_stmt object");
        }
    }       
}
?>