<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db.php';

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error registering user!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Zaayka Junction</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF9800;
            --secondary-color: #FF5722;
            --text-color: #333;
            --light-text: #666;
            --bg-color: #f9f9f9;
            --card-bg: #fff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-image: url('https://images.unsplash.com/photo-1543353071-10c8ba85a904?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .food-showcase {
            flex: 1;
            background: linear-gradient(rgba(255, 152, 0, 0.7), rgba(255, 87, 34, 0.7)), url('../assets/images/food-bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            color: white;
            position: relative;
        }
        
        .food-showcase img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.6;
            z-index: 0;
        }
        
        .food-categories {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: auto;
            z-index: 1;
            position: relative;
        }
        
        .food-category {
            background: white;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            width: 120px;
        }
        
        .food-category img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
            position: static;
            opacity: 1;
        }
        
        .food-category p {
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-header h2 {
            font-size: 28px;
            color: var(--text-color);
            margin-bottom: 10px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 500;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .logo span {
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .tagline {
            font-size: 16px;
            text-align: center;
            color: #666;
            font-style: italic;
            margin-bottom: 30px;
        }
        
        form {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            background: var(--secondary-color);
        }
        
        .error {
            color: #e53935;
            margin-top: 10px;
            font-size: 14px;
            text-align: center;
        }
        
        .form-footer {
            margin-top: 25px;
            text-align: center;
            color: var(--light-text);
        }
        
        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--light-text);
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 14px;
        }
        
        .social-login {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: background 0.3s;
            color: #1A202C;
        }
        
        .social-btn:hover {
            background: #f5f5f5;
        }
        
        .social-btn svg {
            margin-right: 10px;
        }
        
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .food-showcase {
                display: none;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .form-header {
                margin-top: 0;
            }
        }
        
        @media (max-width: 500px) {
            .logo {
                font-size: 28px;
            }
            
            .form-group input {
                padding: 12px;
            }
            
            button {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="food-showcase">
            <img src="../assets/images/indian-thali.jpg" alt="Indian Food Spread">
            <div class="food-categories">
                <div class="food-category">
                    <img src="../assets/images/Low-Carb-Butter-Chicken.jpg" alt="Butter Chicken">
                    <p>Butter Chicken</p>
                </div>
                <div class="food-category">
                    <img src="../assets/images/VegBiryani.jpg" alt="Vegetable Biryani">
                    <p>Vegetable Biryani</p>
                </div>
                <div class="food-category">
                    <img src="../assets/images/msd.jpg" alt="Masala Dosa">
                    <p>Masala Dosa</p>
                </div>
            </div>
        </div>
        
        <div class="form-container">
            <div class="form-header">
                <div class="logo">
                    🍴 Zaayka <span>Junction</span>
                </div>
                <p class="tagline">Flavors Meet Here</p>
                <h2>Create Your Account</h2>
                <p style="color: #666;">Join Zaayka Junction and discover amazing flavors</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                
                <button type="submit">Register</button>
                
                <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
                
                <div class="divider">
                    <span>OR CONTINUE WITH</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </button>
                </div>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>