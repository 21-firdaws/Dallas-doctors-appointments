<?php
session_start();
$_SESSION["user"]="";
$_SESSION["usertype"]="";

// Set the new timezone
date_default_timezone_set('Africa/Nairobi');
$date = date('Y-m-d');
$_SESSION["date"]=$date;

if($_POST){
    $_SESSION["personal"]=array(
        'fname'=>$_POST['fname'],
        'lname'=>$_POST['lname'],
        'address'=>$_POST['address'],
        'nic'=>$_POST['nic'],
        'dob'=>$_POST['dob']
    );
    
    header("location: create-account.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
    <title>Sign Up - Dallas Hospital</title>
    
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
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
        }

        .progress-step.active {
            background: #2563eb;
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

            .form-row {
                grid-template-columns: 1fr;
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
            <div class="hospital-tagline">Join Our Healthcare Community</div>
            <ul class="signup-benefits">
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure & Private Health Records</span>
                </li>
                <li>
                    <i class="fas fa-calendar-check"></i>
                    <span>Easy Online Appointment Booking</span>
                </li>
                <li>
                    <i class="fas fa-user-md"></i>
                    <span>Direct Access to Specialists</span>
                </li>
                <li>
                    <i class="fas fa-mobile-alt"></i>
                    <span>Mobile & Desktop Access</span>
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    <span>24/7 Portal Availability</span>
                </li>
            </ul>
        </div>

        <div class="signup-right">
            <div class="signup-header">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Let's Get Started
                </h2>
                <p>Add Your Personal Details to Continue</p>
            </div>

            <div class="progress-indicator">
                <div class="progress-step active"></div>
                <div class="progress-step"></div>
                <div class="progress-step"></div>
            </div>

            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="fname">
                            <i class="fas fa-user"></i> First Name
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="fname" name="fname" class="form-control" placeholder="First Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lname">
                            <i class="fas fa-user"></i> Last Name
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" id="lname" name="lname" class="form-control" placeholder="Last Name" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="address" name="address" class="form-control" placeholder="Enter your address" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nic">
                        <i class="fas fa-id-card"></i> NIC Number
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card"></i>
                        <input type="text" id="nic" name="nic" class="form-control" placeholder="National ID Card Number" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dob">
                        <i class="fas fa-calendar"></i> Date of Birth
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar"></i>
                        <input type="date" id="dob" name="dob" class="form-control" required>
                    </div>
                </div>

                <div class="button-group">
                    <button type="reset" class="btn btn-reset">
                        <i class="fas fa-redo"></i>
                        Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Next
                        <i class="fas fa-arrow-right"></i>
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