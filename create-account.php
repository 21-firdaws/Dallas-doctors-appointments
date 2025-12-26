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
    $result= $database->query("select * from webuser");

    $fname=$_SESSION['personal']['fname'];
    $lname=$_SESSION['personal']['lname'];
    $name=$fname." ".$lname;
    $address=$_SESSION['personal']['address'];
    $nic=$_SESSION['personal']['nic'];
    $dob=$_SESSION['personal']['dob'];
    $email=$_POST['newemail'];
    $tele=$_POST['tele'];
    $newpassword=$_POST['newpassword'];
    $cpassword=$_POST['cpassword'];
    
    if ($newpassword==$cpassword){
        $sqlmain= "select * from webuser where email=?;";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows==1){
            $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> Already have an account for this Email address.</div>';
        }else{
            $database->query("insert into patient(pemail,pname,ppassword, paddress, pnic,pdob,ptel) values('$email','$name','$newpassword','$address','$nic','$dob','$tele');");
            $database->query("insert into webuser values('$email','p')");

            $_SESSION["user"]=$email;
            $_SESSION["usertype"]="p";
            $_SESSION["username"]=$fname;

            header('Location: patient/index.php');
            exit();
        }
        
    }else{
        $error='<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;"><i class="fas fa-exclamation-circle"></i> Password Confirmation Error! Passwords do not match.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
    <title>Create Account - Dallas Hospital</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: url('img/DallasHospital.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        .container {
            display: flex;
            width: 1000px;
            max-width: 95%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: transitionIn 0.5s ease-out;
        }

        @keyframes transitionIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .signup-left {
            flex: 0.9;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .signup-left::before {
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
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .hospital-tagline {
            font-size: 1em;
            opacity: 0.95;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .signup-benefits {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .signup-benefits li {
            margin: 15px 0;
            display: flex;
            align-items: center;
            font-size: 0.95em;
        }

        .signup-benefits i {
            margin-right: 15px;
            font-size: 1.3em;
            color: #93c5fd;
        }

        .signup-right {
            flex: 1.5;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .signup-header {
            margin-bottom: 30px;
        }

        .signup-header h2 {
            font-size: 2em;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .signup-header h2 i {
            color: #2563eb;
        }

        .signup-header p {
            color: #64748b;
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .form-group label i {
            color: #2563eb;
            font-size: 1em;
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
            font-size: 1em;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 42px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95em;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.85em;
            color: #64748b;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 14px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-reset {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
        }

        .btn-reset:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
        }

        .login-prompt {
            text-align: center;
            color: #64748b;
            font-size: 0.95em;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .login-prompt a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .progress-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .progress-step {
            width: 40px;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            position: relative;
        }

        .progress-step.active {
            background: #2563eb;
        }

        .progress-step.completed {
            background: #10b981;
        }

        .info-box {
            background: #eff6ff;
            border-left: 4px solid #2563eb;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #1e40af;
        }

        .info-box i {
            margin-right: 8px;
        }

        @media (max-width: 968px) {
            .container {
                flex-direction: column;
            }

            .signup-left {
                padding: 40px 30px;
            }

            .signup-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <center>
    <div class="container">
        <div class="signup-left">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="hospital-name">Dallas Hospital</div>
            <div class="hospital-tagline">Almost There!</div>
            <ul class="signup-benefits">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Personal Details Completed</span>
                </li>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Password Protection</span>
                </li>
                <li>
                    <i class="fas fa-lock"></i>
                    <span>Data Privacy Guaranteed</span>
                </li>
                <li>
                    <i class="fas fa-headset"></i>
                    <span>24/7 Support Access</span>
                </li>
            </ul>
        </div>

        <div class="signup-right">
            <div class="signup-header">
                <h2>
                    <i class="fas fa-user-shield"></i>
                    Create Your Account
                </h2>
                <p>Complete your registration with login credentials</p>
            </div>

            <div class="progress-indicator">
                <div class="progress-step completed"></div>
                <div class="progress-step active"></div>
                <div class="progress-step"></div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Welcome <?php echo $_SESSION['personal']['fname'] ?? ''; ?>!</strong> Just a few more details to complete your account.
            </div>

            <?php 
            if($error){
                echo $error;
            }
            ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="newemail">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="newemail" name="newemail" class="form-control" placeholder="your.email@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tele">
                        <i class="fas fa-phone"></i> Mobile Number
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="tele" name="tele" class="form-control" placeholder="0712345678" pattern="[0]{1}[0-9]{9}" required>
                    </div>
                    <div class="password-strength">
                        <i class="fas fa-info-circle"></i> Format: 07XXXXXXXX (10 digits starting with 0)
                    </div>
                </div>

                <div class="form-group">
                    <label for="newpassword">
                        <i class="fas fa-lock"></i> Create Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="Create a strong password" required>
                    </div>
                    <div class="password-strength">
                        <i class="fas fa-shield-alt"></i> Use at least 8 characters with letters and numbers
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpassword">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="cpassword" name="cpassword" class="form-control" placeholder="Re-enter your password" required>
                    </div>
                </div>

                <div class="button-group">
                    <button type="reset" class="btn btn-reset">
                        <i class="fas fa-redo"></i>
                        Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i>
                        Sign Up
                    </button>
                </div>
            </form>

            <div class="login-prompt">
                Already have an account?
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </div>
    </center>
</body>
</html>