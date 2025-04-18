<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/db.php';

    $name = $_POST['name'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $famous_in = $_POST['famous_in'];
    $category = $_POST['category'];
    $cooking_time = $_POST['cooking_time'];
    $difficulty = $_POST['difficulty'];
    $servings = $_POST['servings'];
    $image = $_FILES['image']['name'];
    $target_dir = "../uploads/";
    
    // Generate unique filename to prevent overwriting
    $image_extension = pathinfo($image, PATHINFO_EXTENSION);
    $unique_image_name = uniqid() . '.' . $image_extension;
    $target_file = $target_dir . $unique_image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO recipes (user_id, name, ingredients, instructions, image, famous_in, category, cooking_time, difficulty, servings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssssssi', $_SESSION['user_id'], $name, $ingredients, $instructions, $unique_image_name, $famous_in, $category, $cooking_time, $difficulty, $servings);

        if ($stmt->execute()) {
            header("Location: feed.php");
            exit();
        } else {
            $error = "Error adding recipe: " . $conn->error;
        }
    } else {
        $error = "Error uploading image!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recipe - Zaayka Junction</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

        .header {
            background: linear-gradient(135deg, var(--background-color), var(--accent-color));
            color: white;
            padding: 15px 20px;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
        }

        .logo-text span:first-child {
            color: var(--secondary-color);
        }

        .logo-text span:last-child {
            color: white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-menu a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 20px;
            transition: all 0.3s;
        }

        .user-menu a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        nav {
            background-color: #f5f5f5;
            padding: 12px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 70px;
            z-index: 99;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        nav a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        nav a:hover, nav a.active {
            color: var(--accent-color);
            background-color: rgba(255, 87, 34, 0.1);
        }

        nav a i {
            margin-right: 5px;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--accent-color);
            font-size: 28px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .page-title::after {
            content: "";
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            margin: 10px auto;
            border-radius: 2px;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        .form-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title i {
            color: var(--primary-color);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .image-upload {
            position: relative;
            width: 100%;
            height: 200px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            overflow: hidden;
        }

        .image-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(255, 152, 0, 0.05);
        }

        .image-upload input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .image-upload i {
            font-size: 40px;
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        .image-upload p {
            color: var(--light-text);
            font-size: 14px;
        }

        .image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            display: none;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        }

        .btn-primary:hover {
            background: linear-gradient(to right, var(--accent-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 87, 34, 0.2);
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .error-message {
            background-color: #FFEBEE;
            color: #D32F2F;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 18px;
        }

        .success-message {
            background-color: #E8F5E9;
            color: #2E7D32;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message i {
            font-size: 18px;
        }

        .tips-card {
            background-color: #FFF8E1;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
        }

        .tips-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tips-list {
            list-style-type: none;
        }

        .tips-list li {
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .tips-list li i {
            color: var(--primary-color);
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .header {
                padding: 12px 15px;
            }
            
            .logo-text {
                font-size: 20px;
            }
            
            .user-menu a {
                font-size: 12px;
                padding: 6px 10px;
            }
            
            nav {
                top: 60px;
                padding: 10px;
                overflow-x: auto;
                justify-content: flex-start;
            }
            
            nav::-webkit-scrollbar {
                display: none;
            }
            
            nav a {
                padding: 6px 10px;
                font-size: 13px;
                white-space: nowrap;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .form-card {
                padding: 20px;
            }
            
            .page-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
           
            <div class="logo-text">
            🍴<span>Zaayka</span> <span>Junction</span>
            </div>
        </div>
        <div class="user-menu">
            <a href="#"><i class="fas fa-bell"></i></a>
            <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
        </div>
    </div>

    <nav>
        <a href="feed.php"><i class="fas fa-home"></i> Home</a>
        <a href="trending.php"><i class="fas fa-fire"></i> Trending</a>
        <a href="categories.php"><i class="fas fa-th-large"></i> Categories</a>
        <a href="saved_recipes.php"><i class="fas fa-bookmark"></i> Saved</a>
        <a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a>
        <a href="add_recipe.php" class="active"><i class="fas fa-plus-circle"></i> Add Recipe</a>
    </nav>

    <div class="container">
        <h1 class="page-title"><i class="fas fa-utensils"></i> Add Your Recipe</h1>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="tips-card">
            <div class="tips-title">
                <i class="fas fa-lightbulb"></i> Tips for a Great Recipe
            </div>
            <ul class="tips-list">
                <li><i class="fas fa-check-circle"></i> <span>Be specific with ingredients - include quantities and preparation methods</span></li>
                <li><i class="fas fa-check-circle"></i> <span>Break down instructions into clear, numbered steps</span></li>
                <li><i class="fas fa-check-circle"></i> <span>Add a high-quality photo that showcases your dish</span></li>
                <li><i class="fas fa-check-circle"></i> <span>Include cooking time, difficulty level, and number of servings</span></li>
            </ul>
        </div>

        <form method="POST" enctype="multipart/form-data" class="form-card">
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-info-circle"></i> Basic Information
                </div>
                <div class="form-group">
                    <label for="name">Recipe Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter a descriptive name for your recipe" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="famous_in">Origin/Famous In</label>
                        <input type="text" id="famous_in" name="famous_in" class="form-control" placeholder="Country or region where this dish is from" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                            <option value="Appetizer">Appetizer</option>
                            <option value="Dessert">Dessert</option>
                            <option value="Snack">Snack</option>
                            <option value="Beverage">Beverage</option>
                            <option value="Soup">Soup</option>
                            <option value="Salad">Salad</option>
                            <option value="Bread">Bread</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cooking_time">Cooking Time (minutes)</label>
                        <input type="number" id="cooking_time" name="cooking_time" class="form-control" placeholder="Total time to prepare and cook" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Difficulty Level</label>
                        <select id="difficulty" name="difficulty" class="form-control" required>
                            <option value="">Select difficulty</option>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="servings">Servings</label>
                        <input type="number" id="servings" name="servings" class="form-control" placeholder="Number of servings" min="1" required>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-shopping-basket"></i> Ingredients
                </div>
                <div class="form-group">
                    <label for="ingredients">List all ingredients (one per line)</label>
                    <textarea id="ingredients" name="ingredients" class="form-control" placeholder="Example:
2 cups all-purpose flour
1 teaspoon baking powder
1/2 teaspoon salt
..." required></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-list-ol"></i> Instructions
                </div>
                <div class="form-group">
                    <label for="instructions">Step-by-step instructions (one step per line)</label>
                    <textarea id="instructions" name="instructions" class="form-control" placeholder="Example:
1. Preheat oven to 350°F (175°C).
2. In a large bowl, mix flour, baking powder, and salt.
3. Add wet ingredients and stir until combined.
..." required></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-section-title">
                    <i class="fas fa-image"></i> Recipe Image
                </div>
                <div class="form-group">
                    <div class="image-upload" id="imageUploadContainer">
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload an image of your dish</p>
                        <img id="imagePreview" class="image-preview">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="feed.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Recipe</button>
            </div>
        </form>
    </div>

    <script>
        // Image preview functionality
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const imageUploadContainer = document.getElementById('imageUploadContainer');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                    imagePreview.style.display = 'block';
                    imageUploadContainer.querySelector('i').style.display = 'none';
                    imageUploadContainer.querySelector('p').style.display = 'none';
                });
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
