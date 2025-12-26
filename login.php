<?php
session_start();

$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Africa/Nairobi');
$date = date('Y-m-d');

$_SESSION["date"]=$date;

//import database
include("connection.php");

// Initialize error as empty
$error='';

if($_POST){
    // Sanitize input to prevent SQL injection
    $email = mysqli_real_escape_string($database, $_POST['useremail']);
    $password = mysqli_real_escape_string($database, $_POST['userpassword']);
    
    $result = $database->query("SELECT * FROM webuser WHERE email='$email'");
    
    if($result->num_rows==1){
        $utype=$result->fetch_assoc()['usertype'];
        
        if ($utype=='p'){
            // Patient login
            $checker = $database->query("SELECT * FROM patient WHERE pemail='$email' AND ppassword='$password'");
            if ($checker->num_rows==1){
                $_SESSION['user']=$email;
                $_SESSION['usertype']='p';
                header('location: patient/index.php');
                exit();
            }else{
                $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> Wrong credentials: Invalid email or password</div>';
            }

        }elseif($utype=='a'){
            // Admin login
            $checker = $database->query("SELECT * FROM admin WHERE aemail='$email' AND apassword='$password'");
            if ($checker->num_rows==1){
                $_SESSION['user']=$email;
                $_SESSION['usertype']='a';
                header('location: admin/index.php');
                exit();
            }else{
                $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> Wrong credentials: Invalid email or password</div>';
            }

        }elseif($utype=='d'){
            // Doctor login
            $checker = $database->query("SELECT * FROM doctor WHERE docemail='$email' AND docpassword='$password'");
            if ($checker->num_rows==1){
                $_SESSION['user']=$email;
                $_SESSION['usertype']='d';
                header('location: doctor/index.php');
                exit();
            }else{
                $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> Wrong credentials: Invalid email or password</div>';
            }
        }
        
    }else{
        $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> We can\'t find any account for this email.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dallas Hospital - Patient Portal Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            background: url('img/DallasHospital.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            display: flex;
            width: 900px;
            max-width: 95%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .hospital-logo {
            font-size: 3em;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .hospital-name {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .hospital-tagline {
            font-size: 1.1em;
            opacity: 0.95;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .hospital-features {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .hospital-features li {
            margin: 15px 0;
            display: flex;
            align-items: center;
            font-size: 1em;
        }

        .hospital-features i {
            margin-right: 15px;
            font-size: 1.3em;
            color: #93c5fd;
        }

        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 2em;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #64748b;
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1em;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: #64748b;
            font-size: 0.9em;
        }

        .remember-me input {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .forgot-password {
            color: #2563eb;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #1d4ed8;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #94a3b8;
            font-size: 0.9em;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 15px;
        }

        .signup-prompt {
            text-align: center;
            color: #64748b;
            font-size: 0.95em;
        }

        .signup-prompt a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .signup-prompt a:hover {
            text-decoration: underline;
        }

        .emergency-note {
            margin-top: 30px;
            padding: 15px;
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            font-size: 0.85em;
            color: #991b1b;
        }

        .emergency-note i {
            margin-right: 8px;
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 100%;
                margin: 20px;
            }

            .login-left {
                padding: 40px 30px;
            }

            .login-right {
                padding: 40px 30px;
            }

            .hospital-name {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="hospital-name">Dallas Hospital</div>
            <div class="hospital-tagline">Specialty Wing - Login Portal</div>
            <ul class="hospital-features">
                <li>
                    <i class="fas fa-calendar-check"></i>
                    <span>Schedule Appointments Online</span>
                </li>
                <li>
                    <i class="fas fa-file-medical"></i>
                    <span>Access Appointment Records 24/7</span>
                </li>
                <li>
                    <i class="fas fa-user-md"></i>
                    <span>Connect with Healthcare Providers</span>
                </li>
                <li>
                    <i class="fas fa-pills"></i>
                    <span>Print/Download Appointment Invoices</span>
                </li>
                <li>
                    <i class="fas fa-comment-medical"></i>
                    <span>Secure Finding Required Doctors</span>
                </li>
            </ul>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Login with your details to continue</p>
            </div>

            <?php 
            if($error){
                echo $error;
            }
            ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="useremail" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="userpassword" class="form-control" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="form-footer">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="divider">
                <span>New to Dallas Hospital?</span>
            </div>

            <div class="signup-prompt">
                Don't have an account?
                <a href="signup.php">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>

            <div class="emergency-note">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Emergency?</strong> Call +254799000000.
            </div>
        </div>
    </div>
</body>
</html>