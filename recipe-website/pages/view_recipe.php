<?php require_once('../includes/db.php'); session_start();

if (!isset($_GET['id'])) {
    echo "Recipe not found.";
    exit();
}

$recipe_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT name, ingredients, instructions, image FROM recipes WHERE id = ?");
$stmt->bind_param("i", $recipe_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Recipe not found.";
    exit();
}

$recipe = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($recipe['name']) ?> | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        /* Navigation bar styles */
        .navbar {
            background-color: var(--background-color);
            box-shadow: var(--box-shadow);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .logo a:hover {
            transform: scale(1.05);
        }
        
        .logo-icon {
            margin-right: 10px;
            color: var(--primary-color);
            font-size: 28px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo-zaayka {
            color: #FFC107; /* Yellow/Gold color */
        }
        
        .logo-junction {
            color: #FF5722; /* Deep Orange color */
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 25px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--light-text);
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .nav-links a:hover {
            color: var(--accent-color);
            background-color: rgba(255, 87, 34, 0.1);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .recipe-card {
            background-color: var(--background-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .recipe-header {
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
            background: linear-gradient(to right, rgba(255, 152, 0, 0.05), rgba(255, 87, 34, 0.05));
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            text-decoration: none;
            color: var(--light-text);
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            background-color: rgba(255, 152, 0, 0.1);
        }
        
        .back-link:hover {
            color: var(--accent-color);
            background-color: rgba(255, 152, 0, 0.2);
            transform: translateX(-5px);
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .recipe-title {
            color: var(--text-color);
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .recipe-image-container {
            position: relative;
            overflow: hidden;
            max-height: 500px;
        }
        
        .recipe-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .recipe-image:hover {
            transform: scale(1.03);
        }
        
        .recipe-content {
            padding: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        
        @media (max-width: 768px) {
            .recipe-content {
                grid-template-columns: 1fr;
                padding: 30px 20px;
            }
            
            .recipe-header {
                padding: 20px;
            }
            
            .recipe-title {
                font-size: 26px;
            }
            
            .recipe-image {
                height: 300px;
            }
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            font-size: 24px;
            color: var(--accent-color);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
        }
        
        .section-title i {
            margin-right: 12px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 26px;
        }
        
        .ingredients-list {
            list-style: none;
        }
        
        .ingredients-list li {
            padding: 12px 0;
            border-bottom: 1px dashed #eee;
            position: relative;
            padding-left: 30px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .ingredients-list li:hover {
            background-color: rgba(255, 152, 0, 0.05);
            transform: translateX(5px);
            border-radius: 6px;
        }
        
        .ingredients-list li:before {
            content: "•";
            color: var(--primary-color);
            font-size: 24px;
            position: absolute;
            left: 8px;
        }
        
        .instructions {
            counter-reset: step-counter;
        }
        
        .instruction-step {
            margin-bottom: 25px;
            padding-left: 50px;
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .instruction-step:hover {
            transform: translateX(5px);
        }
        
        .step-number {
            position: absolute;
            left: 0;
            width: 36px;
            height: 36px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 3px 10px rgba(255, 87, 34, 0.3);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-color);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .mobile-menu-btn:hover {
            transform: rotate(90deg);
        }
        
        .recipe-meta {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            color: var(--light-text);
            font-size: 14px;
        }
        
        .recipe-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .recipe-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--accent-color);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background-color: rgba(255, 152, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .print-btn {
            background-color: rgba(255, 87, 34, 0.1);
        }
        
        .print-btn:hover {
            background-color: rgba(255, 87, 34, 0.2);
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 70px;
                left: 0;
                right: 0;
                background-color: var(--background-color);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
                border-radius: 0 0 var(--border-radius) var(--border-radius);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links li {
                margin: 10px 0;
                width: 100%;
            }
            
            .nav-links a {
                width: 100%;
                justify-content: flex-start;
            }
        }
        
        /* Footer styles */
        .footer {
            background-color: #1A202C;
            color: white;
            padding: 40px 20px;
            margin-top: 50px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .footer-section h3 {
            color: var(--secondary-color);
            margin-bottom: 20px;
            font-size: 18px;
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
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #2D3748;
            margin-top: 30px;
            color: #A0AEC0;
            font-size: 14px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-links a {
            color: #CBD5E0;
            font-size: 20px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            color: var(--primary-color);
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <a href="../index.php">
                <span class="logo-icon"><i class="fas fa-utensils"></i></span>
                <span class="logo-text">
                    <span class="logo-zaayka">Zaayka</span> 
                    <span class="logo-junction">Junction</span>
                </span>
            </a>
        </div>
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="feed.php"><i class="fas fa-home"></i> Home</a></li>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a></li>
                <li><a href="add_recipe.php"><i class="fas fa-plus-circle"></i> Add Recipe</a></li>
           
            <?php else: ?>
                <li><a href="../login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="../register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Recipes
        </a>
        
        <div class="recipe-card">
            <div class="recipe-header">
                <h1 class="recipe-title"><?= htmlspecialchars($recipe['name']) ?></h1>
               
                <div class="recipe-actions">
                    <button class="action-btn" onclick="saveRecipe(<?= $recipe_id ?>)">
                        <i class="far fa-bookmark"></i> Save
                    </button>
                    <button class="action-btn print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            
            
            <div class="recipe-content">
                <div class="ingredients-section">
                    <h3 class="section-title"><i class="fas fa-carrot"></i> Ingredients</h3>
                    <ul class="ingredients-list">
                        <?php 
                        $ingredients = explode("\n", $recipe['ingredients']);
                        foreach($ingredients as $ingredient) {
                            if(trim($ingredient) !== '') {
                                echo "<li>" . htmlspecialchars(trim($ingredient)) . "</li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
                
                <div class="instructions-section">
                    <h3 class="section-title"><i class="fas fa-clipboard-list"></i> Instructions</h3>
                    <div class="instructions">
                        <?php 
                        $instructions = explode("\n", $recipe['instructions']);
                        $step = 1;
                        foreach($instructions as $instruction) {
                            if(trim($instruction) !== '') {
                                echo "<div class='instruction-step'>";
                                echo "<div class='step-number'>" . $step . "</div>";
                                echo "<p>" . htmlspecialchars(trim($instruction)) . "</p>";
                                echo "</div>";
                                $step++;
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Zaayka Junction</h3>
                <p>Discover delicious recipes from around the world. Share your culinary creations and connect with food lovers.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="../recipes.php">Recipes</a></li>
                    <li><a href="../categories.php">Categories</a></li>
                    <li><a href="../contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Categories</h3>
                <ul class="footer-links">
                    <li><a href="../categories.php?type=breakfast">Breakfast</a></li>
                    <li><a href="../categories.php?type=lunch">Lunch</a></li>
                    <li><a href="../categories.php?type=dinner">Dinner</a></li>
                    <li><a href="../categories.php?type=dessert">Desserts</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Zaayka Junction. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('navLinks').classList.toggle('active');
        });
        
        // Save recipe function (placeholder)
        function saveRecipe(recipeId) {
            alert('Recipe saved to your favorites!');
            // In a real implementation, this would make an AJAX call to save the recipe
        }
    </script>
</body>
</html>