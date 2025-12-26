<?php
session_start();
date_default_timezone_set('Africa/Nairobi');
include("connection.php");

$error = '';
$success = '';
$email = '';

// DEBUG: Check what's happening
echo "<!-- DEBUG: Starting reset-password.php -->";
echo "<!-- DEBUG: GET token = " . ($_GET['token'] ?? 'NOT SET') . " -->";

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    echo "<!-- DEBUG: Token found: $token -->";
    
    // Check if token exists in database
    $sql = "SELECT email, expDate FROM password_reset_temp WHERE token=?";
    $stmt = $database->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<!-- DEBUG: Database query executed, rows found: " . $result->num_rows . " -->";
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        $expDate = $row['expDate'];
        
        echo "<!-- DEBUG: Token valid for email: $email -->";
        echo "<!-- DEBUG: Token expires: $expDate -->";
        
        // Check if token has expired
        if (strtotime($expDate) >= time()) {
            $showForm = true;
            echo "<!-- DEBUG: Token is valid, showing form -->";
        } else {
            $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
                <i class="fas fa-exclamation-circle"></i> This password reset link has expired.';
            $showForm = false;
        }
    } else {
        $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> Invalid password reset link.';
        $showForm = false;
    }
} else {
    $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
        <i class="fas fa-exclamation-circle"></i> No password reset token provided.';
    $showForm = false;
}

// Process password reset
if ($_POST && isset($_POST['newpassword']) && isset($showForm) && $showForm) {
    echo "<!-- DEBUG: Form submitted -->";
    
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
                    $showForm = false;
                } else {
                    $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
                        <i class="fas fa-exclamation-circle"></i> Error updating password. Please try again.';
                }
            }
        }
    } else {
        $error = '<div style="color:#ef4444; text-align:center; padding:10px; background:#fef2f2; border-radius:8px; margin-bottom:15px;">
            <i class="fas fa-exclamation-circle"></i> Password confirmation error! Passwords do not match.';
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
    <title>Reset Password - Dallas Hospital</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; background: url('img/DallasHospital.png'); background-size: cover; background-position: center; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .header p { color: #64748b; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; }
        .alert-error { background: #fef2f2; color: #dc2626; border-left: 4px solid #ef4444; }
        .alert-info { background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #334155; font-weight: 600; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .form-control { width: 100%; padding: 12px 15px 12px 45px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
        .form-control:focus { outline: none; border-color: #3b82f6; }
        .btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 10px; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .login-link { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-lock"></i> Reset Password</h1>
            <p>Create a new password for your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($showForm) && $showForm): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Resetting password for: <strong><?php echo htmlspecialchars($email); ?></strong>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="newpassword">New Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="Enter new password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cpassword">Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="cpassword" name="cpassword" class="form-control" placeholder="Confirm new password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </form>
        <?php elseif (!isset($showForm) || !$showForm): ?>
            <a href="forget-password.php" class="btn btn-secondary">
                <i class="fas fa-key"></i> Request New Reset Link
            </a>
        <?php endif; ?>

        <div class="login-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>