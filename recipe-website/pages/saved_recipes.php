<?php
require_once('../includes/db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT recipes.id, recipes.name, recipes.ingredients, recipes.instructions, recipes.image, 
                        recipes.user_id, users.username, 
                        (SELECT COUNT(*) FROM likes WHERE recipe_id = recipes.id) as like_count,
                        (SELECT COUNT(*) FROM comments WHERE recipe_id = recipes.id) as comment_count
                        FROM saved_recipes
                        JOIN recipes ON saved_recipes.recipe_id = recipes.id
                        JOIN users ON recipes.user_id = users.id
                        WHERE saved_recipes.user_id = ?
                        ORDER BY saved_recipes.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💾 Saved Recipes | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF9800; /* Orange */
            --secondary-color: #FFC107; /* Yellow */
            --accent-color: #FF5722; /* Deep Orange */
            --text-color: #1A202C;
            --light-text: #4A5568;
            --light-color: #FFFFFF;
            --background-color: #F7FAFC;
            --nav-color: #E2E8F0; /* Baby gray for navbar */
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            --footer-bg: #2D3748;
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
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
        }

        .nav-links li a.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* Main Content */
        main {
            flex: 1;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            padding-bottom: 15px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 60px;
            color: var(--light-text);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .empty-state p {
            color: var(--light-text);
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-state .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        /* Recipe Grid */
        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .recipe-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .recipe-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .recipe-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 3px solid var(--primary-color);
        }

        .recipe-content {
            padding: 20px;
        }

        .recipe-title {
            font-size: 1.3rem;
            color: var(--accent-color);
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 2.8em;
        }

        .recipe-author {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .recipe-description {
            color: var(--text-color);
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 4.8em;
        }

        .recipe-meta {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #eee;
            color: var(--light-text);
            font-size: 0.9rem;
        }

        .recipe-meta div {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .recipe-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .recipe-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .recipe-btn:hover {
            background-color: var(--accent-color);
        }

        .recipe-btn.view-btn {
            background-color: var(--primary-color);
        }

        .recipe-btn.unsave-btn {
            background-color: #e53e3e;
            margin-left: 10px;
        }

        .recipe-btn.unsave-btn:hover {
            background-color: #c53030;
        }

        .bookmark-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 18px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .bookmark-icon:hover {
            transform: scale(1.1);
        }

        /* Footer */
        footer {
            background-color: var(--footer-bg);
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
        }

        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--secondary-color);
            position: relative;
            padding-bottom: 10px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #CBD5E0;
            text-decoration: none;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #A0AEC0;
            font-size: 14px;
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
            
            .recipe-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .recipe-grid {
                grid-template-columns: 1fr;
            }
            
            .recipe-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .recipe-btn {
                width: 100%;
            }
            
            .recipe-btn.unsave-btn {
                margin-left: 0;
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
                padding: 10px;
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            }
            
            .nav-links.show {
                display: flex;
            }
            
            .header-container {
                flex-direction: row;
                justify-content: space-between;
            }
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
                🍴 Zaayka<span>Junction</span>
            </a>
            
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="feed.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="add_recipe.php"><i class="fas fa-plus"></i> Add Recipe</a></li>
                <li><a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a></li>
                <li><a href="saved.php" class="active"><i class="fas fa-bookmark"></i> Saved</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </div>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title"><i class="fas fa-bookmark"></i> Saved Recipes</h1>

            <?php if ($result->num_rows > 0): ?>
                <div class="recipe-grid">
                    <?php while ($recipe = $result->fetch_assoc()): ?>
                        <div class="recipe-card">
                            <div class="bookmark-icon">
                                <i class="fas fa-bookmark"></i>
                            </div>
                            <img src="../uploads/<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['name']) ?>" class="recipe-image">
                            <div class="recipe-content">
                                <h3 class="recipe-title"><?= htmlspecialchars($recipe['name']) ?></h3>
                                <div class="recipe-author">
                                    <i class="fas fa-user-circle"></i> 
                                    <span>By <?= htmlspecialchars($recipe['username']) ?></span>
                                </div>
                                <p class="recipe-description">
                                    <?= substr(htmlspecialchars($recipe['ingredients']), 0, 150) . (strlen($recipe['ingredients']) > 150 ? '...' : '') ?>
                                </p>
                                <div class="recipe-meta">
                                    <div><i class="fas fa-heart"></i> <?= $recipe['like_count'] ?> likes</div>
                                    <div><i class="fas fa-comment"></i> <?= $recipe['comment_count'] ?> comments</div>
                                </div>
                                <div class="recipe-actions">
                                    <a href="view_recipe.php?id=<?= $recipe['id'] ?>" class="recipe-btn view-btn">
                                        <i class="fas fa-eye"></i> View Recipe
                                    </a>
                                    <a href="unsave_recipe.php?id=<?= $recipe['id'] ?>" class="recipe-btn unsave-btn">
                                        <i class="fas fa-bookmark"></i> Unsave
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <h3>No Saved Recipes Yet</h3>
                    <p>You haven't saved any recipes yet. Browse the feed and save recipes you like to view them later.</p>
                    <a href="feed.php" class="btn">
                        <i class="fas fa-home"></i> Go to Feed
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
    <div class="footer-container">
            <div class="footer-section">
                <h3>Zaayka Junction</h3>
                <p>Share your culinary creations and discover recipes from around the world.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="feed.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="explore.php"><i class="fas fa-compass"></i> Explore</a></li>
                    <li><a href="add_recipe.php"><i class="fas fa-plus"></i> Add Recipe</a></li>
                    <li><a href="saved.php"><i class="fas fa-bookmark"></i> Saved</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-hamburger"></i> Fast Food</a></li>
                    <li><a href="#"><i class="fas fa-pizza-slice"></i> Italian</a></li>
                    <li><a href="#"><i class="fas fa-drumstick-bite"></i> Non-Veg</a></li>
                    <li><a href="#"><i class="fas fa-seedling"></i> Vegan</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="mailto:info@zaaykajunction.com"><i class="fas fa-envelope"></i> info@zaaykajunction.com</a></li>
                    <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +123 456 7890</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Food Street, Cuisine City</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Zaayka Junction. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('show');
        });
    </script>
</body>
</html>