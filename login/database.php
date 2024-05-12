<?php
    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require 'vendor/autoload.php';
    define('host', '127.0.0.1');
    define('user','root');
    define('pass','');
    define('db','lab08');

    function open_database() {
        $conn = new mysqli(host, user, pass, db);
        if ($conn->connect_error) {
            die('Connect error: '. $conn->connect_error);
        }
        return $conn;
    }

    function login($user, $pass) {
    
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT * FROM `account` WHERE `username`=?";
        $conn = open_database();
        $stm  = $conn->prepare($sql);
        
        // Bind parameters
        $stm->bind_param("s", $user);
        
        // Execute the statement
        if (!$stm->execute()) {
            return array('code'=>1, 'error'=>'Cannot execute command');
        }
        $result = $stm->get_result();
        
        // Check if there are any rows returned
        if ($result->num_rows == 0) {
            return array('code'=>1, 'error'=>'User does not exist'); // No user found with the given username
        }
        
        // Fetch the row
        $data =  $result->fetch_assoc();
        
        // Verify password
        $hash_password = $data["password"];
        if (!password_verify($pass, $hash_password)) {
            return array('code'=>2, 'error'=>'Invalid password'); // Incorrect password
        } elseif ($data['activated'] == 0) {
            return array('code'=>3, 'error'=>'This account is not activated'); // Login successful, return user data
        }
        else {
            return array('code'=>0, 'error'=>'', 'data'=>$data); // Statement execution failed
        }
    }

    function is_email_exists($email) {
        $sql = "SELECT `username` FROM `account` WHERE `email`=?";
        $conn = open_database();
        $stm  = $conn->prepare($sql);
        $stm->bind_param("s", $email);
        
        // Execute the statement
        if (!$stm->execute()) {
            die("Query error: " . $stm->error);
        }
        $result = $stm->get_result();
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
        
    }

    function register($user, $pass, $first_name, $last_name, $email) {
        if(is_email_exists(($email))) {
            return array('code' => 1,'error'=> 'Email exists');
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $rand = random_int(0,1000);
        $token = md5($user .'+'. $rand);
        $sql = "INSERT INTO `account` (`username`, `firstname`, `lastname`, `email`, `password`, `activate_token`) VALUES (?, ?, ?, ?, ?, ?)";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ssssss", $user, $first_name, $last_name, $email, $hash, $token);
        if (!$stm->execute()) {
            return array('code' => 2,'error'=> 'Can not execute command');
        }
        //send verification email
        sendActivationEmail($email,$token);
        return array('code' => 0,'error'=> 'Create account successfully');
    }

    function sendActivationEmail($email, $token) {
        

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'vynguyen717@gmail.com';                     //SMTP username
            $mail->Password   = 'nwco peds yzwv gxke';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('vynguyen717@gmail.com', 'Quản Trị Viên');
            $mail->addAddress($email, 'Người nhận');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Xác minh tài khoản của bạn';
            $mail->CharSet = 'UTF-8';
            $mail->Body    = "Click <a href='http://localhost/Lab08/source%20code/activate.php?email=$email&token=$token'>vào đây</a> để xác minh tài khoản của bạn";
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }

    function send_reset_email($email, $token) {
        

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'vynguyen717@gmail.com';                     //SMTP username
            $mail->Password   = 'nwco peds yzwv gxke';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('vynguyen717@gmail.com', 'Quản Trị Viên');
            $mail->addAddress($email, 'Người nhận');     //Add a recipient
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Khôi phục mật khẩu của bạn';
            $mail->CharSet = 'UTF-8';
            $mail->Body    = "Click <a href='http://localhost/Lab08/source%20code/reset_password.php?email=$email&token=$token'>vào đây</a> để khôi phục mật khẩu của bạn";
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
    function activeAccount($email, $token) {
        $sql = "SELECT `username` FROM `account` WHERE `email`=? and `activate_token` =? and `activated`= 0";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ss", $email, $token);
        if (!$stm->execute()) {
            return array("code"=> 1,"error"=> "Can not execute command");
        }
        $result = $stm->get_result();
        if ($result->num_rows == 0) {
            return array("code"=> 2,"error"=> "Invalid email address or token not found");
        }
        //found
        $sql = "UPDATE `account` SET `activated`= 1, `activate_token`=''WHERE `email` =?";
        $stm =$conn->prepare($sql);
        $stm->bind_param("s", $email);
        if (!$stm->execute()) {
            return array("code"=> 1,"error"=> "Can not execute command");
        }
        return array("code"=> 0,"message"=> "Account activated");
    }

    function reset_password($email) {
        if (!is_email_exists( $email )) {
            return array("code"=> 1,"error"=> "Email does not exist");
        }
        $token = md5( $email.'+'.random_int(1000, 2000));
        $sql = "UPDATE `reset_token` SET `token` =? WHERE `email`=?";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ss", $token, $email);
        if (!$stm->execute()) { 
            return array("code"=> 2,"error"=> "Cannot execute command");
        }
        if ($stm->affected_rows == 0) {
            //chua co dong nao cua mail nay, them vao dong moi
            $exp = time() + 3600*24; //het han sau 24h
            $sql = "INSERT INTO `reset_token` VALUES (?,?,?)";
            $stm = $conn->prepare($sql);
            $stm->bind_param("ssi", $email, $token, $exp);
            if (!$stm->execute()) { 
                return array("code"=> 1,"error"=> "Cannot execute command");
            }
        }
        // Update the password in the account table
        
        //chen thanh cong/update thanh cong
        send_reset_email($email, $token);
        // return array("code" => 0, "message" => "Password updated successfully");    
    }

    function update_password($email, $new_password) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE `account` SET `password` = ? WHERE `email` = ?";
        $conn = open_database();
        $stm = $conn->prepare($sql);
        $stm->bind_param("ss", $hash, $email);
        if (!$stm->execute()) {
            return array("code" => 3, "error" => "Cannot update password");
        }
        return array("code" => 0, "message" => "Password updated successfully");    
    }