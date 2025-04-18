<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = null;
$stmt = $conn->prepare("SELECT username, email, avatar FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    die("SQL error: " . $conn->error);
}

// Fetch user's recipes count
$recipes_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM recipes WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $recipes_count = $row['count'];
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = trim($_POST["username"]);
    $avatar = $user['avatar'];

    if (!empty($_FILES["avatar"]["name"])) {
        $target_dir = "../uploads/avatars/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $filename = basename($_FILES["avatar"]["name"]);
        $target_file = $target_dir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $avatar = basename($target_file);
        }
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, avatar = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $new_name, $avatar, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update session data
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit();
    } else {
        die("SQL error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF9800; /* Orange */
            --primary-light: #FFE0B2; /* Light Orange */
            --secondary-color: #FFC107; /* Yellow */
            --accent-color: #FF5722; /* Deep Orange */
            --text-color: #1A202C;
            --light-text: #4A5568;
            --light-color: #FFFFFF;
            --background-color: #F7FAFC;
            --nav-color: #e9edc9; 
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --footer-bg: #2D3748;
            --success-color: #48BB78;
            --error-color: #F56565;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-color);
            color: var(--text-color);
            overflow-x: hidden;
            position: relative;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Animated Elements */
        .animated-elements {
            position: fixed;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            z-index: -1;
            overflow: hidden;
            opacity: 0.6;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: var(--secondary-color);
            opacity: 0.2;
            animation: float 15s infinite ease-in-out;
        }

        .square {
            position: absolute;
            background: var(--primary-color);
            opacity: 0.15;
            animation: rotate 20s infinite linear;
        }

        .circle:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 20px;
            right: 50px;
            animation-delay: 0s;
        }

        .circle:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 120px;
            right: 120px;
            animation-delay: 2s;
        }

        .square:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 180px;
            right: 30px;
            transform: rotate(45deg);
            animation-delay: 1s;
        }

        .square:nth-child(4) {
            width: 40px;
            height: 40px;
            top: 70px;
            right: 180px;
            transform: rotate(20deg);
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-20px) scale(1.1);
            }
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Header and Navigation */
        header {
            background-color: var(--nav-color);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        header:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo span {
            color: var(--accent-color);
            margin-left: 5px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        .nav-links li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links li a:hover {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-links li a.active {
            background-color: var(--primary-color);
            color: white;
        }

        .logout-btn {
            background-color: rgba(255, 87, 34, 0.1);
            color: var(--accent-color);
            padding: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background-color: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Main Content */
        main {
            flex: 1;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-card {
            background: var(--light-color);
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            background: linear-gradient(135deg, rgba(255,152,0,0.1) 0%, rgba(255,87,34,0.1) 100%);
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--secondary-color);
        }

        .profile-header h2 {
            color: var(--accent-color);
            margin: 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .edit-icon {
            cursor: pointer;
            color: var(--primary-color);
            font-size: 22px;
            transition: all 0.3s ease;
            padding: 10px;
            border-radius: 50%;
            background-color: rgba(255, 152, 0, 0.1);
        }

        .edit-icon:hover {
            color: var(--accent-color);
            background-color: rgba(255, 152, 0, 0.2);
            transform: rotate(15deg);
        }

        .profile-body {
            padding: 35px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .avatar-container {
            position: relative;
            margin-bottom: 25px;
        }

        .avatar {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--primary-color);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.3);
            transition: all 0.4s ease;
        }

        .avatar:hover {
            transform: scale(1.05);
            border-color: var(--accent-color);
            box-shadow: 0 12px 30px rgba(255, 87, 34, 0.3);
        }

        .username {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin: 15px 0 5px;
            transition: all 0.3s ease;
        }

        .username:hover {
            color: var(--primary-color);
        }

        .email {
            color: var(--light-text);
            font-size: 1.1rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .email:hover {
            color: var(--accent-color);
        }

        .stats {
            display: flex;
            justify-content: center;
            margin: 35px 0;
        }

        .stat {
            text-align: center;
            padding: 20px;
            background-color: rgba(255, 152, 0, 0.05);
            border-radius: var(--border-radius);
            min-width: 120px;
            transition: all 0.4s ease;
            border: 1px solid transparent;
        }

        .stat:hover {
            background-color: rgba(255, 152, 0, 0.1);
            transform: translateY(-8px);
            border-color: rgba(255, 152, 0, 0.2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            transition: all 0.3s ease;
        }

        .stat:hover .stat-value {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .stat-label {
            font-size: 1rem;
            color: var(--light-text);
            margin-top: 8px;
            transition: all 0.3s ease;
        }

        .stat:hover .stat-label {
            color: var(--text-color);
        }

        /* Edit Form */
        .form-section {
            display: none;
            margin-top: 35px;
            padding: 30px;
            background: #f9f9f9;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            animation: slideDown 0.4s ease-out;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .form-group label:hover {
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 16px;
            border-radius: var(--border-radius);
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }

        .file-input-container {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 14px 22px;
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .file-input-label:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .file-name {
            margin-top: 10px;
            font-size: 0.95rem;
            color: var(--light-text);
            transition: all 0.3s ease;
        }

        .btn {
            padding: 14px 24px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            gap: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            font-size: 1rem;
        }

        .back-btn {
            background-color: #4a5568;
            color: white;
        }

        .back-btn:hover {
            background-color: #2d3748;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(74, 85, 104, 0.3);
        }

        .recipes-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .recipes-btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }

        /* Alert Messages */
        .alert {
            padding: 18px;
            margin-bottom: 25px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: #C6F6D5;
            color: #2F855A;
            border-left: 4px solid #2F855A;
        }

        /* Footer */
        footer {
            background-color: var(--footer-bg);
            color: white;
            padding: 25px 0;
            margin-top: 70px;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: inline-block;
        }

        .footer-logo span {
            color: var(--accent-color);
        }

        .copyright {
            color: #A0AEC0;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .container {
                margin: 20px auto;
            }
            
            .animated-elements {
                width: 200px;
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .edit-icon {
                align-self: flex-end;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
            }
            
            .nav-links {
                gap: 10px;
            }
            
            .nav-links li a {
                padding: 8px 10px;
                font-size: 14px;
            }
        }

        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                background-color: var(--nav-color);
                padding: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                z-index: 100;
            }
            
            .nav-links.show {
                display: flex;
            }
            
            .header-container {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        /* Tooltip styles */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
            font-weight: normal;
        }

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Animated Elements -->
    <div class="animated-elements">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="square"></div>
        <div class="square"></div>
    </div>

    <header>
        <div class="header-container">
            <a href="feed.php" class="logo">
                <i class="fas fa-utensils"></i> Zaayka<span>Junction</span>
            </a>
            
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="feed.php"><i class="fas fa-home"></i> Home</a></li>
                
                <li><a href="add_recipe.php"><i class="fas fa-plus"></i> Add Recipe</a></li>
                <li><a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <div class="profile-card">
                <div class="profile-header">
                    <h2><i class="fas fa-user"></i> My Profile</h2>
                    <div class="tooltip">
                        <i class="fas fa-pen-to-square edit-icon" onclick="toggleEditForm()"></i>
                        <span class="tooltiptext">Edit your profile</span>
                    </div>
                </div>

                <div class="profile-body">
                    <div class="profile-info">
                        <div class="avatar-container">
                            <img src="../uploads/avatars/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>" id="previewImg" alt="Avatar" class="avatar">
                        </div>
                        <h3 class="username"><?= htmlspecialchars($user['username']) ?></h3>
                        <p class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                        
                        <div class="stats">
                            <div class="stat">
                                <div class="stat-value"><?= $recipes_count ?></div>
                                <div class="stat-label"><i class="fas fa-utensils"></i> Recipes</div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <form method="POST" enctype="multipart/form-data" class="form-section" id="editForm">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-image"></i> Profile Picture</label>
                                <div class="file-input-container">
                                    <label class="file-input-label">
                                        <i class="fas fa-upload"></i> Choose File
                                        <input type="file" name="avatar" class="file-input" accept="image/*" onchange="previewImage(event)">
                                    </label>
                                    <div class="file-name" id="fileName">No file chosen</div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <a href="feed.php" class="action-btn back-btn"><i class="fas fa-arrow-left"></i> Back to Feed</a>
                <a href="my_recipes.php" class="action-btn recipes-btn"><i class="fas fa-utensils"></i> My Recipes</a>
            </div>
           
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-utensils"></i> Zaayka <span>Junction</span>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Zaayka Junction. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('show');
        });

        function toggleEditForm() {
            const form = document.getElementById('editForm');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                // Scroll to the form
                setTimeout(() => {
                    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                form.style.display = 'none';
            }
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                document.getElementById('previewImg').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
            
            // Update file name display
            const fileName = event.target.files[0].name;
            document.getElementById('fileName').textContent = fileName;
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>