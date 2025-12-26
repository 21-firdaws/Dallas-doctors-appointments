<?php
session_start();

// Set the new timezone
date_default_timezone_set('Africa/Nairobi');

// Import database connection
include("connection.php");

$error = '';
$success = '';
$showResetForm = false;
$email = '';

// Handle token from URL (if coming from reset link)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $sql = "SELECT email, expDate FROM password_reset_temp WHERE token=?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $expDate = $row['expDate'];
        
        // Check if token has expired
        if (strtotime($expDate) >= time()) {
            $showResetForm = true;
            $success = '<div style="color:#059669; text-align:center; padding:10px; background:#ecfdf5; border-radius:8px; margin-bottom:15px;">
                <i class="fas fa-check-circle"></i> Valid reset link. Please create your new password.
            </div>';
        } else {
            $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:88px; margin-bottom:15px;">
                <i class="fas fa-exclamation-circle"></i> This password reset link has expired.
            </div>';
        }
    } else {
        $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> Invalid password reset link.
        </div>';
    }
}

// Handle email submission for reset link
if ($_POST && isset($_POST['email']) && !$showResetForm) {
    $email = $_POST['email'];
    
    // Check if email exists in the system
    $sql = "SELECT * FROM webuser WHERE email=?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // Generate a unique token
        $token = md5(rand() . $email . time());
        $expDate = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        // Delete any existing tokens for this email
        $deleteSql = "DELETE FROM password_reset_temp WHERE email=?";
        $deleteStmt = $database->prepare($deleteSql);
        $deleteStmt->bind_param("s", $email);
        $deleteStmt->execute();
        
        // Insert new token
        $insertSql = "INSERT INTO password_reset_temp (email, token, expDate) VALUES (?, ?, ?)";
        $insertStmt = $database->prepare($insertSql);
        $insertStmt->bind_param("sss", $email, $token, $expDate);
        
        if ($insertStmt->execute()) {
            // Create a clickable reset link
            $resetLink = "reset-password.php?token=" . $token;
            $success = '<div style="color:#059669; text-align:center; padding:15px; background:#ecfdf5; border-radius:8px; margin-bottom:15px;">
                <i class="fas fa-check-circle"></i> Password reset link generated! 
                <br><br>
                <a href="' . $resetLink . '" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-key"></i> Click to Reset Password
                </a>
                <br><br>
                <small style="color: #6b7280;">Or copy this link: ' . $resetLink . '</small>
            </div>';
        } else {
            $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
                <i class="fas fa-exclamation-circle"></i> Error generating reset link.
            </div>';
        }
    } else {
        $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> No account found with this email.
        </div>';
    }
}

// Handle password reset
if ($_POST && isset($_POST['newpassword']) && $showResetForm) {
    $newpassword = $_POST['newpassword'];
    $cpassword = $_POST['cpassword'];
    $token = $_GET['token'];
    
    if ($newpassword == $cpassword) {
        // Get email from token
        $sql = "SELECT email FROM password_reset_temp WHERE token=?";
        $stmt = $database->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $email = $row['email'];
            
            // Determine user type and update password
            $userTypeSql = "SELECT usertype FROM webuser WHERE email=?";
            $userTypeStmt = $database->prepare($userTypeSql);
            $userTypeStmt->bind_param("s", $email);
            $userTypeStmt->execute();
            $userTypeResult = $userTypeStmt->get_result();
            
            if ($userTypeResult->num_rows == 1) {
                $userTypeRow = $userTypeResult->fetch_assoc();
                $userType = $userTypeRow['usertype'];
                
                // Update password based on user type
                if ($userType == 'p') {
                    $updateSql = "UPDATE patient SET ppassword=? WHERE pemail=?";
                } elseif ($userType == 'd') {
                    $updateSql = "UPDATE doctor SET docpassword=? WHERE docemail=?";
                } elseif ($userType == 'a') {
                    $updateSql = "UPDATE admin SET apassword=? WHERE aemail=?";
                }
                
                $updateStmt = $database->prepare($updateSql);
                $updateStmt->bind_param("ss", $newpassword, $email);
                
                if ($updateStmt->execute()) {
                    // Delete the used token
                    $deleteSql = "DELETE FROM password_reset_temp WHERE email=?";
                    $deleteStmt = $database->prepare($deleteSql);
                    $deleteStmt->bind_param("s", $email);
                    $deleteStmt->execute();
                    
                    $success = '<div style="color:#059669; text-align:center; padding:15px; background:#ecfdf5; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-check-circle"></i> Password reset successfully! 
                        <br><br>
                        <a href="login.php" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </a>
                    </div>';
                    $showResetForm = false;
                } else {
                    $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-exclamation-circle"></i> Error updating password.
                    </div>';
                }
            }
        }
    } else {
        $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> Passwords do not match.
        </div>';
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
        
    <title><?php echo $showResetForm ? 'Reset Password' : 'Forgot Password'; ?> - Dallas Hospital</title>
    
    <style>
        /* Keep all the CSS styles from the previous version */
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
            width: 800px;
            max-width: 95%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: transitionIn 0.5s ease-out;
        }

        @keyframes transitionIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .forgot-left {
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

        .forgot-left::before {
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

        .forgot-benefits {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .forgot-benefits li {
            margin: 15px 0;
            display: flex;
            align-items: center;
            font-size: 0.95em;
        }

        .forgot-benefits i {
            margin-right: 15px;
            font-size: 1.3em;
            color: #93c5fd;
        }

        .forgot-right {
            flex: 1.2;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .forgot-header {
            margin-bottom: 30px;
        }

        .forgot-header h2 {
            font-size: 2em;
            color: #1e293b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .forgot-header h2 i {
            color: #2563eb;
        }

        .forgot-header p {
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
            display: flex;
            flex-direction: column;
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

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #e2e8f0;
        }

        .btn-secondary:hover {
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

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .forgot-left {
                padding: 40px 30px;
            }

            .forgot-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <center>
    <div class="container">
        <div class="forgot-left" style="<?php echo $showResetForm ? 'background: linear-gradient(135deg, #10b981 0%, #059669 100%);' : ''; ?>">
            <div class="hospital-logo">
                <i class="fas fa-hospital"></i>
            </div>
            <div class="hospital-name">Dallas Hospital</div>
            <div class="hospital-tagline">
                <?php echo $showResetForm ? 'Create Your New Password' : 'Password Recovery Assistance'; ?>
            </div>
            <ul class="forgot-benefits">
                <?php if ($showResetForm): ?>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Password Reset</span>
                </li>
                <li>
                    <i class="fas fa-lock"></i>
                    <span>Strong Password Requirements</span>
                </li>
                <li>
                    <i class="fas fa-user-check"></i>
                    <span>Immediate Account Access</span>
                </li>
                <li>
                    <i class="fas fa-headset"></i>
                    <span>Support Available if Needed</span>
                </li>
                <?php else: ?>
                <li>
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Password Reset Process</span>
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    <span>Quick Recovery - Less Than 5 Minutes</span>
                </li>
                <li>
                    <i class="fas fa-lock"></i>
                    <span>One-Time Use Reset Links</span>
                </li>
                <li>
                    <i class="fas fa-user-check"></i>
                    <span>Verified Account Security</span>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="forgot-right">
            <div class="forgot-header">
                <h2>
                    <i class="fas <?php echo $showResetForm ? 'fa-lock-open' : 'fa-key'; ?>"></i>
                    <?php echo $showResetForm ? 'Reset Your Password' : 'Forgot Your Password?'; ?>
                </h2>
                <p>
                    <?php echo $showResetForm ? 'Create a new secure password for your account' : 'Enter your email to reset your password'; ?>
                </p>
            </div>

            <?php 
            if($error){
                echo $error;
            }
            
            if($success){
                echo $success;
            }
            ?>

            <?php if (!$showResetForm && !$success): ?>
            <!-- Email Request Form -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                We'll send a password reset link to your registered email address.
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Send Reset Link
                    </button>
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </form>

            <?php elseif ($showResetForm && !$success): ?>
            <!-- Password Reset Form -->
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                You're resetting password for: <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="newpassword">
                        <i class="fas fa-lock"></i> New Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="Create a new password" required>
                    </div>
                    <div class="password-strength">
                        <i class="fas fa-shield-alt"></i> Use at least 8 characters with letters and numbers
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpassword">
                        <i class="fas fa-lock"></i> Confirm New Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="cpassword" name="cpassword" class="form-control" placeholder="Re-enter your new password" required>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-check-circle"></i>
                        Reset Password
                    </button>
                    <a href="login.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </form>
            <?php endif; ?>

            <div class="login-prompt">
                Remember your password?
                <a href="login.php">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </div>
    </div>
    </center>
</body>
</html>