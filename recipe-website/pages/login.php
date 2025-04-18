<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: feed.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: feed.php");
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No user found with that email!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background-image: url('https://images.unsplash.com/photo-1543353071-10c8ba85a904?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            display: flex;
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
        }
        
        .login-image {
            flex: 1;
            background-image: url('https://images.unsplash.com/photo-1498837167922-ddd27525d352?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
            display: none;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.7), rgba(255, 87, 34, 0.7));
        }
        
        .login-content {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 36px;
            color: var(--primary-color);
        }
        
        .logo span {
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .slogan {
            text-align: center;
            margin-bottom: 40px;
            color: var(--light-text);
            font-size: 16px;
            font-style: italic;
        }
        
        h2 {
            font-size: 28px;
            margin-bottom: 30px;
            text-align: center;
            color: var(--text-color);
        }
        
        form {
            width: 100%;
        }
        
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember {
            display: flex;
            align-items: center;
        }
        
        .remember input {
            margin-right: 8px;
        }
        
        .forgot a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .forgot a:hover {
            color: var(--accent-color);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }
        
        button:hover {
            background-color: var(--accent-color);
        }
        
        .error {
            color: #e53e3e;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            background-color: #fff5f5;
            padding: 10px;
            border-radius: var(--border-radius);
            border-left: 4px solid #e53e3e;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--light-text);
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: var(--accent-color);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: var(--light-text);
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #e2e8f0;
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 14px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius);
            background-color: white;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            max-width: 220px;
            font-weight: 500;
            color:#1A202C;
        }
        
        .social-btn:hover {
            background-color: #f8f9fa;
            border-color: #cbd5e0;
            
        }
        
        .social-btn img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        .recipe-slider {
            position: absolute;
            bottom: 50px;
            left: 0;
            right: 0;
            width: 100%;
        }
        
        .recipe-slider-inner {
            display: flex;
            justify-content: center;
            gap: 15px;
            padding: 0 20px;
        }
        
        .recipe-card {
            width: 180px;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .recipe-card .recipe-title {
            padding: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            text-align: center;
            background-color: white;
        }
        
        .slider-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 15px;
        }
        
        .slider-arrow {
            width: 30px;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin: 0 5px;
        }
        
        .slider-progress {
            height: 4px;
            background-color: rgba(255, 255, 255, 0.3);
            width: 200px;
            border-radius: 2px;
            margin: 0 10px;
            position: relative;
        }
        
        .slider-progress-bar {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 33%;
            background-color: white;
            border-radius: 2px;
        }
        
        .slider-background {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(to top, rgba(255, 87, 34, 0.9), transparent);
            z-index: -1;
        }
        
        @media (min-width: 768px) {
            .login-image {
                display: block;
            }
        }
        
        @media (max-width: 767px) {
            .container {
                min-height: auto;
            }
            
            .login-content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-image">
            <div class="recipe-slider">
                <div class="slider-background"></div>
                <div class="recipe-slider-inner">
                    <div class="recipe-card">
                        <img src="../assets/images/Low-Carb-Butter-Chicken.jpg" alt="Butter Chicken">
                        <div class="recipe-title">Butter Chicken</div>
                    </div>
                    <div class="recipe-card">
                        <img src="../assets/images/VegBiryani.jpg" alt="Vegetable Biryani">
                        <div class="recipe-title">Vegetable Biryani</div>
                    </div>
                    <div class="recipe-card">
                        <img src="../assets/images/msd.jpg" alt="Masala Dosa">
                        <div class="recipe-title">Masala Dosa</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-content">
            <div class="logo">
                🍴 Zaayka <span>Junction</span>
            </div>
            <div class="slogan">Flavors Meet Here</div>
            <h2>Login to Your Account</h2>
            <form method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot">
                        <a href="forgot_password.php">Forgot password?</a>
                    </div>
                </div>
                <button type="submit">Login</button>
                <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>
                <div class="social-login">
                    <button type="button" class="social-btn">
                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBkPSJNMTcuNiA5LjJsLS4xLTEuOEg5djMuNGg0LjhDMTMuNiAxMiAxMyAxMyAxMiAxMy42djIuMmgzYTguOCA4LjggMCAwIDAgMi42LTYuNnoiIGZpbGw9IiM0Mjg1RjQiIGZpbGwtcnVsZT0ibm9uemVybyIvPjxwYXRoIGQ9Ik05IDE4YzIuNCAwIDQuNS0uOCA2LTIuMmwtMy0yLjJhNS40IDUuNCAwIDAgMS04LTIuOUgxVjEzYTkgOSAwIDAgMCA4IDV6IiBmaWxsPSIjMzRBODUzIiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNNCAxMC43YTUuNCA1LjQgMCAwIDEgMC0zLjRWNUgxYTkgOSAwIDAgMCAwIDhsMy0yLjN6IiBmaWxsPSIjRkJCQzA1IiBmaWxsLXJ1bGU9Im5vbnplcm8iLz48cGF0aCBkPSJNOSAzLjZjMS4zIDAgMi41LjQgMy40IDEuM0wxNSAyLjNBOSA5IDAgMCAwIDEgNWwzIDIuNGE1LjQgNS40IDAgMCAxIDUtMy43eiIgZmlsbD0iI0VBNDMzNSIgZmlsbC1ydWxlPSJub256ZXJvIi8+PHBhdGggZD0iTTAgMGgxOHYxOEgweiIvPjwvZz48L3N2Zz4=" alt="Google">
        Google
    </button>
</div>
            </form>
        </div>
    </div>
</body>
</html>
