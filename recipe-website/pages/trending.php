<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Optional search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch trending recipes by number of saves (most popular)
$sql = "SELECT r.*, u.username, u.avatar, COUNT(s.recipe_id) AS saves, 
        (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id) AS likes_count,
        (SELECT COUNT(*) FROM comments WHERE recipe_id = r.id) AS comments_count,
        (SELECT COUNT(*) FROM saved_recipes WHERE recipe_id = r.id AND user_id = ?) AS is_saved
        FROM recipes r
        JOIN users u ON r.user_id = u.id
        LEFT JOIN saved_recipes s ON r.id = s.recipe_id
        WHERE r.name LIKE ? OR r.ingredients LIKE ? OR r.famous_in LIKE ?
        GROUP BY r.id
        ORDER BY saves DESC, r.created_at DESC
        LIMIT 20";

$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("isss", $user_id, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categoryQuery = "SELECT DISTINCT famous_in FROM recipes WHERE famous_in != '' ORDER BY famous_in";
$categoryStmt = $conn->prepare($categoryQuery);
$categoryStmt->execute();
$categoriesResult = $categoryStmt->get_result();
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    if (!empty($row['famous_in'])) {
        $categories[] = $row['famous_in'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending Recipes | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .logo img {
            height: 40px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--light-color);
        }
        
        .logo-text span {
            color: var(--secondary-color);
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
        }
        
        .nav-links a {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 0.5rem 0.75rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-form {
            display: flex;
            gap: 0.5rem;
            width: 100%;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
        }
        
        .search-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .search-button:hover {
            background-color: var(--accent-color);
        }
        
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        
        .filter-button {
            background-color: white;
            border: 2px solid #e2e8f0;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-button:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .filter-button.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .recipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .recipe-card {
            background-color: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .recipe-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .recipe-save {
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
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-save:hover {
            transform: scale(1.1);
        }
        
        .recipe-save i {
            color: var(--save-color);
            font-size: 1.2rem;
        }
        
        .recipe-content {
            padding: 1.5rem;
        }
        
        .recipe-category {
            display: inline-block;
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
        }
        
        .recipe-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .recipe-author {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
        }
        
        .author-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-name {
            font-size: 0.9rem;
            color: var(--light-text);
        }
        
        .recipe-stats {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: var(--light-text);
        }
        
        .stat i.fa-heart {
            color: var(--heart-color);
        }
        
        .stat i.fa-bookmark {
            color: var(--save-color);
        }
        
        .stat i.fa-comment {
            color: var(--comment-color);
        }
        
        .recipe-details {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--light-text);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .view-recipe {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .view-recipe:hover {
            background-color: var(--accent-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e2e8f0;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            font-size: 1.2rem;
            color: var(--light-text);
        }
        
        .trending-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, var(--heart-color), var(--accent-color));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-button {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f8f8f8;
        }
        
        .action-button:hover {
            background-color: #f0f0f0;
        }
        
        .action-button.like i {
            color: var(--heart-color);
        }
        
        .action-button.comment i {
            color: var(--comment-color);
        }
        
        .action-button.share i {
            color: var(--primary-color);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            max-width: 800px;
            position: relative;
            box-shadow: var(--box-shadow);
        }
        
        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--light-text);
            transition: color 0.3s ease;
        }
        
        .close-modal:hover {
            color: var(--accent-color);
        }
        
        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .modal-author {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        
        .modal-section {
            margin-bottom: 1.5rem;
        }
        
        .modal-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .ingredients-list, .instructions-list {
            list-style-position: inside;
            padding-left: 1rem;
        }
        
        .ingredients-list li, .instructions-list li {
            margin-bottom: 0.5rem;
        }
        
        .instructions-list li {
            counter-increment: step-counter;
        }
        
        .instructions-list li::before {
            content: counter(step-counter);
            margin-right: 10px;
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 0 6px;
            border-radius: 50%;
            font-size: 0.8rem;
            display: inline-block;
            text-align: center;
            width: 20px;
            height: 20px;
            line-height: 20px;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        
        .modal-action {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .modal-action.like {
            background-color: rgba(233, 30, 99, 0.1);
            color: var(--heart-color);
        }
        
        .modal-action.save {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--save-color);
        }
        
        .modal-action.comment {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--comment-color);
        }
        
        .modal-action.share {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }
        
        .modal-action:hover {
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                width: 100%;
                justify-content: space-between;
            }
            
            .recipe-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            
            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .modal-content {
                margin: 10% 1rem;
                padding: 1.5rem;
            }
        }
        
        /* Loading animation */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255, 152, 0, 0.2);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--accent-color);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1002;
        }
        
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">
                
                <div class="logo-text">🍴Zaayka <span>Junction</span></div>
            </a>
            <ul class="nav-links">
                <li><a href="feed.php"><i class="fas fa-home"></i> Feed</a></li>
                <li><a href="trending.php" class="active"><i class="fas fa-fire"></i> Trending</a></li>
                <li><a href="add_recipe.php"><i class="fas fa-plus-circle"></i> Add Recipe</a></li>
                <li><a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-fire"></i> Trending Recipes</h1>
        </div>
        
        <!-- Search Form -->
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Search recipes, ingredients, or cuisines..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        
        <!-- Filters -->
        <div class="filters">
            <button type="button" class="filter-button active" data-filter="all">All</button>
            <?php foreach ($categories as $category): ?>
                <button type="button" class="filter-button" data-filter="<?php echo htmlspecialchars($category); ?>">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Recipe Grid -->
        <?php if ($result->num_rows > 0): ?>
            <div class="recipe-grid">
                <?php 
                $rank = 1;
                while($row = $result->fetch_assoc()): 
                    $isTrending = $rank <= 5; // Top 5 are trending
                    $rank++;
                ?>
                <div class="recipe-card" data-category="<?php echo htmlspecialchars($row['famous_in']); ?>" data-id="<?php echo $row['id']; ?>">
                    <?php if ($isTrending): ?>
                    <div class="trending-badge">
                        <i class="fas fa-fire"></i> Trending
                    </div>
                    <?php endif; ?>
                    
                    <div class="recipe-save <?php echo $row['is_saved'] ? 'saved' : ''; ?>" data-id="<?php echo $row['id']; ?>">
                        <i class="<?php echo $row['is_saved'] ? 'fas' : 'far'; ?> fa-bookmark"></i>
                    </div>
                    
                    <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="recipe-image">
                    
                    <div class="recipe-content">
                        <?php if (!empty($row['famous_in'])): ?>
                        <div class="recipe-category"><?php echo htmlspecialchars($row['famous_in']); ?></div>
                        <?php endif; ?>
                        
                        <h3 class="recipe-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                        
                        <div class="recipe-author">
                            <img src="<?php echo $row['avatar'] ? '../uploads/avatar/' . htmlspecialchars($row['avatar']) : '../assets/default-avatar.png'; ?>" alt="Author" class="author-avatar">
                            <span class="author-name">By <?php echo htmlspecialchars($row['username']); ?></span>
                        </div>
                        
                        <div class="recipe-details">
                            <?php 
                            $ingredients = explode("\n", $row['ingredients']);
                            $ingredientsList = array_slice($ingredients, 0, 3);
                            echo implode(", ", array_map('htmlspecialchars', $ingredientsList));
                            if (count($ingredients) > 3) echo "...";
                            ?>
                        </div>
                        
                        <div class="recipe-stats">
                            <div class="stat">
                                <i class="fas fa-heart"></i>
                                <span><?php echo $row['likes_count']; ?></span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-bookmark"></i>
                                <span><?php echo $row['saves']; ?></span>
                            </div>
                            <div class="stat">
                                <i class="fas fa-comment"></i>
                                <span><?php echo $row['comments_count']; ?></span>
                            </div>
                        </div>
                        
                        <div class="recipe-actions">
                            <a href="javascript:void(0);" class="view-recipe" onclick="openRecipeModal(<?php echo $row['id']; ?>)">View Recipe</a>
                            
                            <div class="action-buttons">
                                <div class="action-button like" data-id="<?php echo $row['id']; ?>">
                                    <i class="far fa-heart"></i>
                                </div>
                                <div class="action-button comment" data-id="<?php echo $row['id']; ?>">
                                    <i class="far fa-comment"></i>
                                </div>
                                <div class="action-button share" data-id="<?php echo $row['id']; ?>">
                                    <i class="fas fa-share-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No recipes found. Try a different search term.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recipe Modal -->
    <div id="recipeModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="modalContent">
                <div class="loading">
                    <div class="loading-spinner"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">Recipe saved successfully!</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter functionality
            const filterButtons = document.querySelectorAll('.filter-button');
            const recipeCards = document.querySelectorAll('.recipe-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Filter recipes
                    recipeCards.forEach(card => {
                        if (filter === 'all' || card.getAttribute('data-category') === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
            
            // Save recipe functionality
            const saveButtons = document.querySelectorAll('.recipe-save');
            
            saveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const recipeId = this.getAttribute('data-id');
                    const isSaved = this.classList.contains('saved');
                    
                    // Toggle saved state
                    if (isSaved) {
                        this.classList.remove('saved');
                        this.querySelector('i').classList.replace('fas', 'far');
                        showToast('Recipe removed from saved');
                    } else {
                        this.classList.add('saved');
                        this.querySelector('i').classList.replace('far', 'fas');
                        showToast('Recipe saved successfully!');
                    }
                    
                    // Send AJAX request to save/unsave recipe
                    fetch('save_recipe.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `recipe_id=${recipeId}&action=${isSaved ? 'unsave' : 'save'}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                });  => {
                        console.error('Error:', error);
                    });
                });
            });
            
            // Recipe card click to open modal
            const recipeCards = document.querySelectorAll('.recipe-card');
            
            recipeCards.forEach(card => {
                card.addEventListener('click', function() {
                    const recipeId = this.getAttribute('data-id');
                    openRecipeModal(recipeId);
                });
            });
            
            // Modal functionality
            const modal = document.getElementById('recipeModal');
            const closeModal = document.querySelector('.close-modal');
            
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // Like button functionality
            const likeButtons = document.querySelectorAll('.action-button.like');
            
            likeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const recipeId = this.getAttribute('data-id');
                    const isLiked = this.querySelector('i').classList.contains('fas');
                    
                    // Toggle like state
                    if (isLiked) {
                        this.querySelector('i').classList.replace('fas', 'far');
                        showToast('Like removed');
                    } else {
                        this.querySelector('i').classList.replace('far', 'fas');
                        showToast('Recipe liked!');
                    }
                    
                    // Send AJAX request to like/unlike recipe
                    fetch('like_recipe.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `recipe_id=${recipeId}&action=${isLiked ? 'unlike' : 'like'}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                });
            });
            
            // Comment button functionality
            const commentButtons = document.querySelectorAll('.action-button.comment');
            
            commentButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const recipeId = this.getAttribute('data-id');
                    window.location.href = `view_recipe.php?id=${recipeId}#comments`;
                });
            });
            
            // Share button functionality
            const shareButtons = document.querySelectorAll('.action-button.share');
            
            shareButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const recipeId = this.getAttribute('data-id');
                    
                    // Create a temporary input to copy the URL
                    const tempInput = document.createElement('input');
                    tempInput.value = `${window.location.origin}/view_recipe.php?id=${recipeId}`;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    
                    showToast('Recipe link copied to clipboard!');
                });
            });
        });
        
        // Function to open recipe modal
        function openRecipeModal(recipeId) {
            const modal = document.getElementById('recipeModal');
            const modalContent = document.getElementById('modalContent');
            
            // Show loading spinner
            modalContent.innerHTML = '<div class="loading"><div class="loading-spinner"></div></div>';
            modal.style.display = 'block';
            
            // Fetch recipe details
            fetch(`get_recipe.php?id=${recipeId}`)
                .then(response => response.json())
                .then(data => {
                    // Create modal content
                    let content = `
                        <img src="../uploads/${data.image}" alt="${data.name}" class="modal-image">
                        <h2 class="modal-title">${data.name}</h2>
                        <div class="modal-author">
                            <img src="${data.avatar ? '../uploads/avatars/' + data.avatar : '../assets/default-avatar.png'}" alt="Author" class="author-avatar">
                            <span class="author-name">By ${data.username}</span>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title"><i class="fas fa-map-marker-alt"></i> Famous In</h3>
                            <p>${data.famous_in}</p>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title"><i class="fas fa-list"></i> Ingredients</h3>
                            <ul class="ingredients-list">
                    `;
                    
                    // Add ingredients
                    const ingredients = data.ingredients.split('\n');
                    ingredients.forEach(ingredient => {
                        if (ingredient.trim()) {
                            content += `<li>${ingredient}</li>`;
                        }
                    });
                    
                    content += `
                            </ul>
                        </div>
                        
                        <div class="modal-section">
                            <h3 class="modal-section-title"><i class="fas fa-utensils"></i> Instructions</h3>
                            <ol class="instructions-list">
                    `;
                    
                    // Add instructions
                    const instructions = data.instructions.split('\n');
                    instructions.forEach(instruction => {
                        if (instruction.trim()) {
                            content += `<li>${instruction}</li>`;
                        }
                    });
                    
                    content += `
                            </ol>
                        </div>
                        
                        <div class="modal-actions">
                            <div class="modal-action like" data-id="${data.id}">
                                <i class="${data.is_liked ? 'fas' : 'far'} fa-heart"></i>
                                <span>Like</span>
                            </div>
                            <div class="modal-action save" data-id="${data.id}">
                                <i class="${data.is_saved ? 'fas' : 'far'} fa-bookmark"></i>
                                <span>Save</span>
                            </div>
                            <div class="modal-action comment" data-id="${data.id}">
                                <i class="far fa-comment"></i>
                                <span>Comment</span>
                            </div>
                            <div class="modal-action share" data-id="${data.id}">
                                <i class="fas fa-share-alt"></i>
                                <span>Share</span>
                            </div>
                        </div>
                    `;
                    
                    modalContent.innerHTML = content;
                    
                    // Add event listeners to modal actions
                    addModalActionListeners();
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<p>Error loading recipe details. Please try again.</p>';
                });
        }
        
        // Function to add event listeners to modal actions
        function addModalActionListeners() {
            // Like action
            const likeAction = document.querySelector('.modal-action.like');
            if (likeAction) {
                likeAction.addEventListener('click', function() {
                    const recipeId = this.getAttribute('data-id');
                    const isLiked = this.querySelector('i').classList.contains('fas');
                    
                    // Toggle like state
                    if (isLiked) {
                        this.querySelector('i').classList.replace('fas', 'far');
                        showToast('Like removed');
                    } else {
                        this.querySelector('i').classList.replace('far', 'fas');
                        showToast('Recipe liked!');
                    }
                    
                    // Send AJAX request
                    fetch('like_recipe.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `recipe_id=${recipeId}&action=${isLiked ? 'unlike' : 'like'}`
                    });
                });
            }
            
            // Save action
            const saveAction = document.querySelector('.modal-action.save');
            if (saveAction) {
                saveAction.addEventListener('click', function() {
                    const recipeId = this.getAttribute('data-id');
                    const isSaved = this.querySelector('i').classList.contains('fas');
                    
                    // Toggle saved state
                    if (isSaved) {
                        this.querySelector('i').classList.replace('fas', 'far');
                        showToast('Recipe removed from saved');
                    } else {
                        this.querySelector('i').classList.replace('far', 'fas');
                        showToast('Recipe saved successfully!');
                    }
                    
                    // Send AJAX request
                    fetch('save_recipe.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `recipe_id=${recipeId}&action=${isSaved ? 'unsave' : 'save'}`
                    });
                });
            }
            
            // Comment action
            const commentAction = document.querySelector('.modal-action.comment');
            if (commentAction) {
                commentAction.addEventListener('click', function() {
                    const recipeId = this.getAttribute('data-id');
                    window.location.href = `view_recipe.php?id=${recipeId}#comments`;
                });
            }
            
            // Share action
            const shareAction = document.querySelector('.modal-action.share');
            if (shareAction) {
                shareAction.addEventListener('click', function() {
                    const recipeId = this.getAttribute('data-id');
                    
                    // Create a temporary input to copy the URL
                    const tempInput = document.createElement('input');
                    tempInput.value = `${window.location.origin}/view_recipe.php?id=${recipeId}`;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);
                    
                    showToast('Recipe link copied to clipboard!');
                });
            }
        }
        
        // Function to show toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            
            toastMessage.textContent = message;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>