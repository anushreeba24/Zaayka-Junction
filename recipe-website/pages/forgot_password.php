<?php
require_once('../includes/db.php');
$message = "";
$show_password_form = false;
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: User submitted email
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $show_password_form = true;
        } else {
            $message = "❌ No account found with that email.";
        }
    }

    // Step 2: User submitted new password
    if (isset($_POST['new_password']) && isset($_POST['confirm_password']) && isset($_POST['reset_email'])) {
        $email = $_POST['reset_email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            if ($stmt->execute()) {
                $message = "✅ Password updated successfully!";
            } else {
                $message = "❌ Failed to update password.";
            }
        } else {
            $message = "❌ Passwords do not match.";
            $show_password_form = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | 🍴 Zaayka Junction</title>
    <style>
        :root {
            --primary-color: #FF9800; /* Orange */
            --secondary-color: #FFC107; /* Yellow */
            --accent-color: #FF5722; /* Deep Orange */
            --text-color: #1A202C;
            --light-text: #4A5568;
            --light-color: #FFFFFF;
            --background-color: #FFFFFF;
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f9f9f9;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            margin: 40px auto;
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            text-align: center;
        }
        
        .logo {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .logo img {
            height: 60px;
            margin-bottom: 10px;
        }
        
        h2 {
            color: var(--text-color);
            margin-bottom: 25px;
            font-size: 24px;
            font-weight: 600;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        input {
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        button:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .message {
            margin-top: 20px;
            padding: 12px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }
        
        .message.error {
            background-color: #FEE2E2;
            color: #B91C1C;
        }
        
        .message.success {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .back-link {
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }
        
        .form-title {
            color: var(--light-text);
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        .fork-icon {
            color: var(--primary-color);
            font-size: 24px;
            margin-right: 8px;
        }
        
        .brand-name {
            font-size: 28px;
            font-weight: 700;
        }
        
        .brand-name .first {
            color: var(--secondary-color);
        }
        
        .brand-name .second {
            color: var(--accent-color);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
            
            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <span class="fork-icon">🍴</span>
            <div class="brand-name">
                <span class="first">Zaayka</span> <span class="second">Junction</span>
            </div>
        </div>
        
        <h2>Reset Your Password</h2>
        
        <?php if ($show_password_form): ?>
            <p class="form-title">Please enter your new password below</p>
            <form method="POST">
                <input type="hidden" name="reset_email" value="<?= htmlspecialchars($email) ?>">
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button type="submit">Change Password</button>
            </form>
        <?php else: ?>
            <p class="form-title">Enter your email address to reset your password</p>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter your registered email" required>
                <button type="submit">Continue</button>
            </form>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <a href="login.php" class="back-link">Back to Login</a>
    </div>
</body>
</html>