<?php
session_start();
require_once('../includes/db.php');

// Fetch all available categories
$category_sql = "SELECT DISTINCT category FROM recipes WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
$category_result = $conn->query($category_sql);

$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$recipes = [];

if ($selected_category !== '') {
    $stmt = $conn->prepare("SELECT r.*, u.username, u.avatar FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $recipes = $stmt->get_result();
}

// Get current user's avatar and notification count if logged in
$notification_count = 0;
$current_user_avatar = 'default.jpg';
$current_username = '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_stmt = $conn->prepare("SELECT avatar, username FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $current_user = $user_result->fetch_assoc();
    $current_user_avatar = $current_user['avatar'] ? $current_user['avatar'] : 'default.jpg';
    $current_username = $current_user['username'];

    // Get notification count (placeholder - implement your actual notification logic)
    $notification_count = 3; // Example count - replace with actual query
}

// Category icons mapping
$category_icons = [
    'Breakfast' => 'fa-coffee',
    'Lunch' => 'fa-utensils',
    'Dinner' => 'fa-moon',
    'Appetizer' => 'fa-cheese',
    'Dessert' => 'fa-ice-cream',
    'Snack' => 'fa-cookie',
    'Beverage' => 'fa-glass-martini',
    'Soup' => 'fa-bowl-food',
    'Salad' => 'fa-seedling',
    'Bread' => 'fa-bread-slice',
    'Other' => 'fa-ellipsis-h'
];

// Get all categories for the category cards
$all_categories_sql = "SELECT category, COUNT(*) as recipe_count FROM recipes WHERE category IS NOT NULL AND category != '' GROUP BY category ORDER BY category ASC";
$all_categories_result = $conn->query($all_categories_sql);
$all_categories = [];
while ($row = $all_categories_result->fetch_assoc()) {
    $all_categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF9800; /* Orange */
            --secondary-color: #FFC107; /* Yellow */
            --accent-color: #FF5722; /* Deep Orange */
            --text-color: #1A202C;
            --light-text: #4A5568;
            --light-color: #FFFFFF;
            --background-color: #F8F9FA;
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            --heart-color: #E91E63; /* Red for heart icon */
            --save-color: #FF9800; /* Orange for save icon */
            --comment-color: #4CAF50; /* Green for comment icon */
            --logo-orange: #FF9800;
            --logo-red: #FF5722;
            --logo-gray: #9E9E9E;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Main Navigation Bar */
        .main-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-icon {
            display: flex;
            margin-right: 10px;
        }

        .logo-icon svg {
            height: 30px;
            width: auto;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        .logo-text .zaayka {
            color: var(--logo-orange);
        }

        .logo-text .junction {
            color: var(--logo-red);
        }

        .main-nav {
            display: flex;
            gap: 30px;
        }

        .main-nav a {
            color: var(--text-color);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .main-nav a:hover {
            color: var(--accent-color);
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--accent-color);
            transition: width 0.3s;
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        .main-nav a.active {
            color: var(--accent-color);
        }

        .main-nav a.active::after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .login-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .login-btn:hover {
            color: var(--accent-color);
        }

        .login-btn i {
            transition: transform 0.3s;
        }

        .login-btn:hover i {
            transform: translateX(3px);
        }

        /* User Profile Link */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .user-profile:hover {
            color: var(--accent-color);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--accent-color);
            transform: scale(1.05);
        }

        .user-name {
            font-weight: 500;
            font-size: 15px;
        }

        /* Notification Icon */
        .notification-icon {
            position: relative;
            margin-right: 15px;
            cursor: pointer;
        }

        .notification-icon i {
            font-size: 20px;
            color: var(--text-color);
            transition: color 0.3s;
        }

        .notification-icon:hover i {
            color: var(--accent-color);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #E91E63;
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Secondary Navigation */
        .secondary-nav {
            background-color: white;
            padding: 10px 5%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: center;
            gap: 15px;
            overflow-x: auto;
            position: sticky;
            top: 70px;
            z-index: 99;
        }

        .secondary-nav::-webkit-scrollbar {
            display: none;
        }

        .secondary-nav a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            white-space: nowrap;
        }

        .secondary-nav a:hover, .secondary-nav a.active {
            color: var(--accent-color);
            background-color: rgba(255, 87, 34, 0.1);
        }

        .secondary-nav a i {
            margin-right: 5px;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-color);
            cursor: pointer;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            font-size: 36px;
            color: var(--primary-color);
        }

        /* Category Selection */
        .category-selection {
            margin-bottom: 40px;
        }

        .category-selection h2 {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        .category-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .category-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .category-card.active {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
        }

        .category-card.active .category-icon {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .category-card.active .recipe-count {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            transition: all 0.3s;
        }

        .category-card:hover .category-icon {
            transform: scale(1.1);
        }

        .category-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .recipe-count {
            font-size: 12px;
            color: var(--light-text);
            background-color: #f5f5f5;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Recipe Grid */
        .recipe-grid-title {
            font-size: 24px;
            margin: 40px 0 20px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recipe-grid-title span {
            color: var(--accent-color);
            font-weight: 700;
        }

        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .recipe-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: all 0.3s;
        }

        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .recipe-image-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .recipe-card:hover .recipe-image {
            transform: scale(1.05);
        }

        .recipe-difficulty {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .recipe-time {
            position: absolute;
            bottom: 15px;
            left: 15px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .recipe-content {
            padding: 20px;
        }

        .recipe-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--accent-color);
        }

        .recipe-author {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .author-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }

        .author-name {
            font-size: 14px;
            color: var(--light-text);
        }

        .recipe-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .recipe-category {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 12px;
        }

        .recipe-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--accent-color);
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .recipe-link:hover {
            transform: translateX(5px);
        }

        .no-recipes {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .no-recipes i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-recipes h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .no-recipes p {
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .add-recipe-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .add-recipe-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 87, 34, 0.3);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-nav {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .main-header {
                padding: 15px 20px;
            }
        }

        @media (max-width: 768px) {
            .main-header {
                padding: 12px 15px;
            }
            
            .logo-text {
                font-size: 20px;
            }
            
            .secondary-nav {
                top: 60px;
                padding: 10px;
                overflow-x: auto;
                justify-content: flex-start;
            }
            
            .secondary-nav a {
                padding: 6px 10px;
                font-size: 13px;
                white-space: nowrap;
            }
            
            .category-cards {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .recipe-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Main Navigation Bar -->
    <header class="main-header">
        <a href="index.php" class="logo-container">
            <div class="logo-icon">
               
            </div>
            <div class="logo-text">
                <span class="zaayka"> 🍴Zaayka</span> <span class="junction">Junction</span>
            </div>
        </a>
        <nav class="main-nav">
            <a href="feed.php"><i class="fas fa-home"></i> Home</a>
            <a href="trending.php"><i class="fas fa-fire"></i> Trending</a>
            <a href="categories.php" class="active"><i class="fas fa-th-large"></i> Categories</a>
            <a href="saved_recipes.php"><i class="fas fa-bookmark"></i> Saved</a>
            <a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a>
            <a href="add_recipe.php"><i class="fas fa-plus-circle"></i> Add Recipe</a>
        </nav>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Notification Icon -->
                <div class="notification-icon" id="notificationIcon">
                    <i class="fas fa-bell"></i>
                    <?php if ($notification_count > 0): ?>
                        <span class="notification-badge"><?= $notification_count ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- User Profile Link -->
                <a href="profile.php" class="user-profile">
                    <img src="../uploads/avatars/<?= htmlspecialchars($current_user_avatar) ?>" alt="Your Avatar" class="user-avatar">
                    <span class="user-name"><?= htmlspecialchars($current_username) ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Log in <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>

        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- Secondary Navigation -->
  

    <div class="container">
        <h1 class="page-title"><i class="fas fa-utensils"></i> Browse by Category</h1>

        <div class="category-selection">
            <h2>Select a Category</h2>
            <div class="category-cards">
                <?php foreach ($all_categories as $category): ?>
                    <a href="?category=<?= urlencode($category['category']) ?>" class="category-card <?= ($selected_category === $category['category']) ? 'active' : '' ?>">
                        <div class="category-icon">
                            <i class="fas <?= isset($category_icons[$category['category']]) ? $category_icons[$category['category']] : 'fa-utensils' ?>"></i>
                        </div>
                        <div class="category-name"><?= htmlspecialchars($category['category']) ?></div>
                        <div class="recipe-count"><?= $category['recipe_count'] ?> recipes</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($selected_category): ?>
            <h2 class="recipe-grid-title">
                <i class="fas <?= isset($category_icons[$selected_category]) ? $category_icons[$selected_category] : 'fa-utensils' ?>"></i>
                Recipes in <span><?= htmlspecialchars($selected_category) ?></span>
            </h2>

            <?php if ($recipes->num_rows > 0): ?>
                <div class="recipe-grid">
                    <?php while ($recipe = $recipes->fetch_assoc()): ?>
                        <div class="recipe-card">
                            <div class="recipe-image-container">
                                <img src="../uploads/<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>" class="recipe-image">
                                <?php if (isset($recipe['difficulty'])): ?>
                                <div class="recipe-difficulty">
                                    <i class="fas fa-signal"></i> <?= htmlspecialchars($recipe['difficulty']) ?>
                                </div>
                                <?php endif; ?>
                                <?php if (isset($recipe['cooking_time'])): ?>
                                <div class="recipe-time">
                                    <i class="far fa-clock"></i> <?= htmlspecialchars($recipe['cooking_time']) ?> mins
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="recipe-content">
                                <h3 class="recipe-title"><?= htmlspecialchars($recipe['name']) ?></h3>
                                <div class="recipe-author">
                                    <img src="../uploads/avatars/<?= isset($recipe['avatar']) && $recipe['avatar'] ? htmlspecialchars($recipe['avatar']) : 'default.jpg' ?>" alt="Author" class="author-avatar">
                                    <span class="author-name">By <?= htmlspecialchars($recipe['username']) ?></span>
                                </div>
                                <div class="recipe-meta">
                                    <div class="recipe-category">
                                        <i class="fas <?= isset($category_icons[$recipe['category']]) ? $category_icons[$recipe['category']] : 'fa-utensils' ?>"></i>
                                        <?= htmlspecialchars($recipe['category']) ?>
                                    </div>
                                    <a href="recipe_detail.php?id=<?= $recipe['id'] ?>" class="recipe-link">
                                        View Recipe <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-recipes">
                    <i class="fas fa-search"></i>
                    <h3>No recipes found in this category</h3>
                    <p>Be the first to add a recipe in this category!</p>
                    <a href="add_recipe.php" class="add-recipe-btn">Add Recipe</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                alert('Mobile menu coming soon!');
            });
        }

        // Notification icon
        const notificationIcon = document.getElementById('notificationIcon');
        if (notificationIcon) {
            notificationIcon.addEventListener('click', function() {
                window.location.href = 'notification.php';
            });
        }
    </script>
</body>
</html>