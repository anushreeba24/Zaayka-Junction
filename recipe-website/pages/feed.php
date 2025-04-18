<?php
session_start();
require_once('../includes/db.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = "";

// Search handling
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $query = "
        SELECT recipes.*, users.username AS uploader_name, users.avatar, users.id AS uploader_id
        FROM recipes 
        JOIN users ON recipes.user_id = users.id 
        WHERE recipes.name LIKE CONCAT('%', ?, '%') 
            OR recipes.ingredients LIKE CONCAT('%', ?, '%')
            OR recipes.famous_in LIKE CONCAT('%', ?, '%')
            OR recipes.category LIKE CONCAT('%', ?, '%')
        ORDER BY recipes.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $search, $search, $search, $search);
} else {
    // Default query to get all recipes with user information
    $query = "
        SELECT recipes.*, users.username AS uploader_name, users.avatar, users.id AS uploader_id
        FROM recipes 
        JOIN users ON recipes.user_id = users.id 
        ORDER BY recipes.created_at DESC
    ";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$recipes = $stmt->get_result();

// Handle like
if (isset($_POST['like_recipe'])) {
    $recipe_id = $_POST['recipe_id'];
    $like_stmt = $conn->prepare("INSERT IGNORE INTO likes (user_id, recipe_id) VALUES (?, ?)");
    $like_stmt->bind_param("ii", $user_id, $recipe_id);
    $like_stmt->execute();
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Handle save
if (isset($_POST['save_recipe'])) {
    $recipe_id = $_POST['recipe_id'];
    $save_stmt = $conn->prepare("INSERT IGNORE INTO saved_recipes (user_id, recipe_id) VALUES (?, ?)");
    $save_stmt->bind_param("ii", $user_id, $recipe_id);
    $save_stmt->execute();
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Handle comment
if (isset($_POST['post_comment'])) {
    $recipe_id = $_POST['recipe_id'];
    $comment = htmlspecialchars($_POST['comment']);
    $comment_stmt = $conn->prepare("INSERT INTO comments (user_id, recipe_id, comment) VALUES (?, ?, ?)");
    $comment_stmt->bind_param("iis", $user_id, $recipe_id, $comment);
    $comment_stmt->execute();
    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['REQUEST_URI']);
    exit();
}

// Get current user's avatar and notification count
$user_stmt = $conn->prepare("SELECT avatar, username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$current_user = $user_result->fetch_assoc();
$current_user_avatar = $current_user['avatar'] ? $current_user['avatar'] : 'default.jpg';
$current_username = $current_user['username'];

// Get notification count (this is a placeholder - implement your actual notification logic)
$notification_count = 3; // Example count - replace with actual query
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaayka Junction - Recipe Feed</title>
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

        /* New Main Navigation Bar */
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

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: -100px;
            width: 300px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            z-index: 100;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
        }

        .notification-icon.active .notification-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }

        .notification-title {
            font-weight: 600;
            font-size: 16px;
            color: var(--text-color);
        }

        .mark-all-read {
            font-size: 12px;
            color: var(--primary-color);
            cursor: pointer;
            transition: color 0.3s;
        }

        .mark-all-read:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 15px;
            border-bottom: 1px solid #f5f5f5;
            transition: background-color 0.3s;
        }

        .notification-item:hover {
            background-color: #f9f9f9;
        }

        .notification-item.unread {
            background-color: rgba(255, 152, 0, 0.05);
        }

        .notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .notification-content {
            flex: 1;
        }

        .notification-text {
            font-size: 13px;
            line-height: 1.4;
        }

        .notification-text strong {
            font-weight: 600;
            color: var(--text-color);
        }

        .notification-time {
            font-size: 11px;
            color: var(--light-text);
            margin-top: 3px;
        }

        .notification-footer {
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #eee;
        }

        .view-all-notifications {
            color: var(--primary-color);
            font-size: 13px;
            text-decoration: none;
            transition: color 0.3s;
        }

        .view-all-notifications:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .search-bar {
            margin: 25px auto;
            max-width: 700px;
            padding: 0 20px;
        }

        .search-bar form {
            display: flex;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 30px;
            overflow: hidden;
            background-color: white;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .search-bar form:focus-within {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .search-bar input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            outline: none;
            font-size: 15px;
        }

        .search-bar button {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 0 25px;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .search-bar button:hover {
            background: linear-gradient(to right, var(--accent-color), var(--primary-color));
        }

        .feed-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 0 20px;
        }

        h2.feed-title {
            color: var(--accent-color);
            font-size: 22px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feed-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-btn {
            background-color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .filter-btn:hover {
            background-color: #f5f5f5;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .recipe-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .recipe {
            border: none;
            margin: 30px auto;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: all 0.3s;
        }

        .recipe:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .recipe-header {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f0;
        }

        .recipe-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Enhanced Avatar Styling */
        .author-avatar-container {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            border: 2px solid white;
            background-color: #f0f0f0;
        }

        .author-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .author-avatar-container:hover .author-avatar {
            transform: scale(1.1);
        }

        .author-info {
            display: flex;
            flex-direction: column;
        }

        .author-name {
            font-weight: 600;
            color: var(--text-color);
            font-size: 15px;
        }

        .recipe-date {
            font-size: 12px;
            color: var(--light-text);
        }

        .recipe-options {
            color: var(--light-text);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s;
            background-color: #f5f5f5;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recipe-options:hover {
            background-color: #e0e0e0;
            color: var(--accent-color);
        }

        .recipe-image-container {
            width: 100%;
            height: 350px;
            overflow: hidden;
            position: relative;
        }

        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .recipe:hover .recipe-image {
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
            padding: 25px;
        }

        .recipe-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--accent-color);
            font-weight: 700;
            line-height: 1.3;
        }

        .recipe-meta {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .recipe-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            transition: all 0.3s;
        }

        .recipe-meta-item:hover {
            transform: translateY(-2px);
        }

        .recipe-origin {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--primary-color);
        }

        .recipe-category {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }

        .recipe-cooking-time {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }

        .recipe-difficulty-tag {
            background-color: rgba(156, 39, 176, 0.1);
            color: #9C27B0;
        }

        .recipe-servings {
            background-color: rgba(233, 30, 99, 0.1);
            color: #E91E63;
        }

        .recipe-section {
            margin: 25px 0;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .recipe-section-title {
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            color: var(--accent-color);
        }

        .recipe-section-title i {
            color: var(--primary-color);
            background-color: rgba(255, 152, 0, 0.1);
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .recipe-text {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-line;
            font-size: 14px;
            border: 1px solid #eee;
        }

        .ingredients-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .ingredient-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background-color: white;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            border: 1px solid #eee;
        }

        .ingredient-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .ingredient-item i {
            color: var(--primary-color);
            font-size: 12px;
        }

        .instructions-list {
            counter-reset: step-counter;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .instruction-step {
            position: relative;
            padding: 15px 15px 15px 50px;
            background-color: white;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            border: 1px solid #eee;
        }

        .instruction-step:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .instruction-step::before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 25px;
            height: 25px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        /* Enhanced Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 25px 0;
        }

        .action-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .like-btn {
            background-color: rgba(233, 30, 99, 0.1);
            color: var(--heart-color);
        }

        .like-btn:hover:not(:disabled) {
            background-color: var(--heart-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(233, 30, 99, 0.3);
        }

        .save-btn {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--save-color);
        }

        .save-btn:hover:not(:disabled) {
            background-color: var(--save-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }

        .comment-btn {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--comment-color);
        }

        .comment-btn:hover {
            background-color: var(--comment-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .action-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .action-btn i {
            font-size: 16px;
        }

        .comments-section {
            margin-top: 25px;
            border-top: 1px solid #f0f0f0;
            padding-top: 25px;
        }

        .comments-title {
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--accent-color);
        }

        .comments-title i {
            color: var(--comment-color);
            background-color: rgba(76, 175, 80, 0.1);
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .comment {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        /* Enhanced Comment Avatar */
        .comment-avatar-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            border: 2px solid white;
            background-color: #f0f0f0;
            flex-shrink: 0;
        }

        .comment-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .comment-avatar-container:hover .comment-avatar {
            transform: scale(1.1);
        }

        .comment-content {
            flex: 1;
            background-color: #f9f9f9;
            padding: 12px 18px;
            border-radius: 15px;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .comment-content::before {
            content: "";
            position: absolute;
            left: -8px;
            top: 15px;
            width: 16px;
            height: 16px;
            background-color: #f9f9f9;
            transform: rotate(45deg);
            box-shadow: -2px 2px 5px rgba(0, 0, 0, 0.03);
        }

        .comment-author {
            font-weight: 600;
            color: var(--accent-color);
            font-size: 14px;
        }

        .comment-text {
            font-size: 14px;
            margin-top: 5px;
            line-height: 1.5;
        }

        .comment-time {
            font-size: 11px;
            color: var(--light-text);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .comment-time i {
            font-size: 10px;
        }

        .comment-form {
            margin-top: 25px;
            display: flex;
            gap: 12px;
        }

        /* Enhanced Comment Form Avatar */
        .comment-form-avatar-container {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
            border: 2px solid white;
            background-color: #f0f0f0;
            flex-shrink: 0;
        }

        .comment-form-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .comment-input-container {
            flex: 1;
            position: relative;
        }

        .comment-input {
            width: 100%;
            padding: 14px 50px 14px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            resize: none;
            font-size: 14px;
            transition: all 0.3s;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .comment-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .comment-submit {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--comment-color);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            box-shadow: 0 3px 8px rgba(76, 175, 80, 0.3);
        }

        .comment-submit:hover {
            background: #3d8b40;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .no-recipes {
            text-align: center;
            padding: 50px 20px;
            color: var(--light-text);
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 90;
        }

        .floating-btn:hover {
            transform: translateY(-5px) rotate(90deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 40px 0;
            gap: 8px;
        }

        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background-color: white;
            color: var(--text-color);
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .pagination a:hover {
            background-color: #f5f5f5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }

        .search-results-info {
            text-align: center;
            margin: 20px 0;
            color: var(--light-text);
            font-size: 14px;
            background-color: white;
            padding: 12px 20px;
            border-radius: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .search-highlight {
            background-color: rgba(255, 152, 0, 0.2);
            padding: 0 3px;
            border-radius: 3px;
            font-weight: 500;
        }

        /* Like animation */
        @keyframes heartBeat {
            0% { transform: scale(1); }
            25% { transform: scale(1.3); }
            50% { transform: scale(1); }
            75% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }

        .like-animation {
            animation: heartBeat 0.8s;
            color: var(--heart-color);
        }

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
            
            .recipe-image-container {
                height: 200px;
            }
            
            .recipe-title {
                font-size: 18px;
            }
            
            .ingredients-list {
                grid-template-columns: 1fr;
            }
            
            .floating-btn {
                width: 50px;
                height: 50px;
                font-size: 20px;
                bottom: 20px;
                right: 20px;
            }

            .recipe-meta {
                flex-direction: column;
                gap: 8px;
            }

            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -50px;
            }
        }
    </style>
</head>
<body>

    <!-- Main Navigation Bar -->
    <header class="main-header">
        <a href="index.php" class="logo-container">
           
            <div class="logo-text">
                <span class="zaayka">🍴 Zaayka</span> <span class="junction">Junction</span>
            </div>
        </a>
        <div class="auth-buttons">
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Notification Icon -->
        <div class="notification-icon" id="notificationIcon">
            <a href="notification.php">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge"><?= $notification_count ?></span>
                <?php endif; ?>
            </a>
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
    <nav class="secondary-nav">
        <a href="feed.php" class="active"><i class="fas fa-home"></i> Home</a>
        <a href="trending.php"><i class="fas fa-fire"></i> Trending</a>
        <a href="categories.php"><i class="fas fa-th-large"></i> Categories</a>
        <a href="saved_recipes.php"><i class="fas fa-bookmark"></i> Saved</a>
        <a href="my_recipes.php"><i class="fas fa-utensils"></i> My Recipes</a>
        <a href="add_recipe.php"><i class="fas fa-plus-circle"></i> Add Recipe</a>
    </nav>

    <div class="search-bar">
        <form method="GET" action="feed.php">
            <input type="text" name="search" placeholder="Search recipes, ingredients, or cuisines..." value="<?= $search ?>" />
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <?php if (!empty($search)): ?>
    <div class="search-results-info">
        Showing results for: <strong class="search-highlight"><?= htmlspecialchars($search) ?></strong> 
        (<?= $recipes->num_rows ?> recipes found)
        <a href="feed.php" style="margin-left: 10px; color: var(--accent-color);">Clear search</a>
    </div>
    <?php endif; ?>

    <div class="feed-header">
        <h2 class="feed-title"><i class="fas fa-utensils"></i> Recipe Feed</h2>
        <div class="feed-filter">
            <button class="filter-btn active"><i class="fas fa-sort-amount-down"></i> Latest</button>
            <button class="filter-btn"><i class="fas fa-fire"></i> Popular</button>
        </div>
    </div>

    <div class="recipe-container">
        <?php if ($recipes->num_rows == 0): ?>
            <div class="no-recipes">
                <i class="fas fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                <h3>No recipes found</h3>
                <p>Try a different search term or add your own recipe!</p>
                <a href="add_recipe.php" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: var(--primary-color); color: white; border-radius: 8px; text-decoration: none;">Add Recipe</a>
            </div>
        <?php endif; ?>

        <?php while ($recipe = $recipes->fetch_assoc()): ?>
            <?php
            $recipe_id = $recipe['id'];
            $like_count = $conn->query("SELECT COUNT(*) FROM likes WHERE recipe_id = $recipe_id")->fetch_row()[0];
            $comment_count = $conn->query("SELECT COUNT(*) FROM comments WHERE recipe_id = $recipe_id")->num_rows;
            $liked = $conn->query("SELECT * FROM likes WHERE user_id = $user_id AND recipe_id = $recipe_id")->num_rows > 0;
            $saved = $conn->query("SELECT * FROM saved_recipes WHERE user_id = $user_id AND recipe_id = $recipe_id")->num_rows > 0;
            
            // Format date
            $date = new DateTime($recipe['created_at']);
            $formatted_date = $date->format('M d, Y');

            // Get user avatar
            $avatar = !empty($recipe['avatar']) ? $recipe['avatar'] : 'default.jpg';
            ?>

            <div class="recipe">
                <div class="recipe-header">
                    <div class="recipe-author">
                        <div class="author-avatar-container">
                            <img src="../uploads/avatars/<?= htmlspecialchars($avatar) ?>" alt="User Avatar" class="author-avatar">
                        </div>
                        <div class="author-info">
                            <span class="author-name"><?= htmlspecialchars($recipe['uploader_name']) ?></span>
                            <span class="recipe-date"><?= $formatted_date ?></span>
                        </div>
                    </div>
                    <div class="recipe-options">
                        <i class="fas fa-ellipsis-v"></i>
                    </div>
                </div>
                
                <div class="recipe-image-container">
                    <img src="../uploads/<?= htmlspecialchars($recipe['image']) ?>" class="recipe-image" alt="<?= htmlspecialchars($recipe['name']) ?>">
                    <div class="recipe-difficulty">
                        <i class="fas fa-signal"></i> <?= htmlspecialchars($recipe['difficulty']) ?>
                    </div>
                    <div class="recipe-time">
                        <i class="far fa-clock"></i> <?= htmlspecialchars($recipe['cooking_time']) ?> mins
                    </div>
                </div>
                
                <div class="recipe-content">
                    <h3 class="recipe-title"><?= htmlspecialchars($recipe['name']) ?></h3>
                    
                    <div class="recipe-meta">
                        <span class="recipe-meta-item recipe-origin">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($recipe['famous_in']) ?>
                        </span>
                        <span class="recipe-meta-item recipe-category">
                            <i class="fas fa-utensils"></i> <?= htmlspecialchars($recipe['category']) ?>
                        </span>
                        <span class="recipe-meta-item recipe-cooking-time">
                            <i class="far fa-clock"></i> <?= htmlspecialchars($recipe['cooking_time']) ?> mins
                        </span>
                        <span class="recipe-meta-item recipe-difficulty-tag">
                            <i class="fas fa-signal"></i> <?= htmlspecialchars($recipe['difficulty']) ?>
                        </span>
                        <span class="recipe-meta-item recipe-servings">
                            <i class="fas fa-users"></i> <?= htmlspecialchars($recipe['servings']) ?> servings
                        </span>
                    </div>
                    
                    <div class="recipe-section">
                        <div class="recipe-section-title">
                            <i class="fas fa-shopping-basket"></i> Ingredients
                        </div>
                        <?php
                        $ingredients = explode("\n", $recipe['ingredients']);
                        if (count($ingredients) > 0 && !empty($ingredients[0])):
                        ?>
                        <div class="ingredients-list">
                            <?php foreach($ingredients as $ingredient): ?>
                                <?php if(trim($ingredient) !== ""): ?>
                                <div class="ingredient-item">
                                    <i class="fas fa-circle"></i> <?= htmlspecialchars(trim($ingredient)) ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="recipe-text"><?= nl2br(htmlspecialchars($recipe['ingredients'])) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="recipe-section">
                        <div class="recipe-section-title">
                            <i class="fas fa-list-ol"></i> Instructions
                        </div>
                        <?php
                        $instructions = explode("\n", $recipe['instructions']);
                        if (count($instructions) > 0 && !empty($instructions[0])):
                        ?>
                        <div class="instructions-list">
                            <?php foreach($instructions as $index => $instruction): ?>
                                <?php if(trim($instruction) !== ""): ?>
                                <div class="instruction-step">
                                    <?= htmlspecialchars(trim($instruction)) ?>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="recipe-text"><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="action-buttons">
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
                            <button type="submit" name="like_recipe" class="action-btn like-btn" <?= $liked ? 'disabled' : '' ?> id="like-btn-<?= $recipe_id ?>">
                                <i class="<?= $liked ? 'fas' : 'far' ?> fa-heart"></i> <?= $like_count ?> Likes
                            </button>
                        </form>
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
                            <button type="submit" name="save_recipe" class="action-btn save-btn" <?= $saved ? 'disabled' : '' ?>>
                                <i class="<?= $saved ? 'fas' : 'far' ?> fa-bookmark"></i> <?= $saved ? 'Saved' : 'Save' ?>
                            </button>
                        </form>
                        <button type="button" class="action-btn comment-btn" onclick="document.getElementById('comment-input-<?= $recipe_id ?>').focus()">
                            <i class="far fa-comment"></i> <?= $comment_count ? $comment_count . ' Comments' : 'Add Comment' ?>
                        </button>
                    </div>

                    <div class="comments-section">
                        <div class="comments-title">
                            <i class="fas fa-comments"></i> Comments
                        </div>
                        <?php
                        $comment_result = $conn->query("
                            SELECT c.comment, c.created_at, u.username, u.avatar 
                            FROM comments c 
                            JOIN users u ON c.user_id = u.id 
                            WHERE recipe_id = $recipe_id 
                            ORDER BY c.created_at DESC
                            LIMIT 3
                        ");
                        
                        if ($comment_result->num_rows == 0) {
                            echo '<p style="color: var(--light-text); font-style: italic; text-align: center; padding: 15px; background-color: #f9f9f9; border-radius: 10px;">No comments yet. Be the first to comment!</p>';
                        }
                        
                        while ($comment = $comment_result->fetch_assoc()):
                            // Format comment date
                            $comment_date = new DateTime($comment['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($comment_date);
                            
                            if ($interval->days > 0) {
                                $time_ago = $interval->days . ' days ago';
                            } elseif ($interval->h > 0) {
                                $time_ago = $interval->h . ' hours ago';
                            } elseif ($interval->i > 0) {
                                $time_ago = $interval->i . ' minutes ago';
                            } else {
                                $time_ago = 'Just now';
                            }

                            // Get commenter avatar
                            $comment_avatar = !empty($comment['avatar']) ? $comment['avatar'] : 'default.jpg';
                        ?>
                            <div class="comment">
                                <div class="comment-avatar-container">
                                    <img src="../uploads/avatars/<?= htmlspecialchars($comment_avatar) ?>" alt="User Avatar" class="comment-avatar">
                                </div>
                                <div class="comment-content">
                                    <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                    <div class="comment-text"><?= htmlspecialchars($comment['comment']) ?></div>
                                    <div class="comment-time"><i class="far fa-clock"></i> <?= $time_ago ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <?php if ($comment_result->num_rows > 0): ?>
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="view_recipe.php?id=<?= $recipe_id ?>" style="color: var(--primary-color); text-decoration: none; font-size: 14px; display: inline-block; padding: 8px 15px; background-color: rgba(255, 152, 0, 0.1); border-radius: 20px; transition: all 0.3s;">
                                    <i class="fas fa-comments"></i> View all comments
                                </a>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="comment-form">
                            <div class="comment-form-avatar-container">
                                <img src="../uploads/avatars/<?= htmlspecialchars($current_user_avatar) ?>" alt="Your Avatar" class="comment-form-avatar">
                            </div>
                            <div class="comment-input-container">
                                <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
                                <input type="text" id="comment-input-<?= $recipe_id ?>" name="comment" class="comment-input" required placeholder="Write a comment...">
                                <button type="submit" name="post_comment" class="comment-submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        
        <?php if ($recipes->num_rows > 0): ?>
            <div class="pagination">
                <a href="#"><i class="fas fa-chevron-left"></i></a>
                <a href="#" class="active">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">4</a>
                <a href="#">5</a>
                <a href="#"><i class="fas fa-chevron-right"></i></a>
            </div>
        <?php endif; ?>
    </div>

    <a href="add_recipe.php" class="floating-btn">
        <i class="fas fa-plus"></i>
    </a>

    <script>
        // Notification dropdown
        const notificationIcon = document.getElementById('notificationIcon');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationIcon) {
            notificationIcon.addEventListener('click', function(event) {
                event.stopPropagation();
                notificationDropdown.classList.toggle('active');
            });
        }
        
        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(event) {
            // Close notification dropdown
            if (notificationIcon && !notificationIcon.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.remove('active');
            }
        });
        
        // Mobile menu
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                // Implement mobile menu functionality
                alert('Mobile menu coming soon!');
            });
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
        
        // Filter buttons
        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Highlight search terms in recipe titles and content
        if ('<?= $search ?>'.length > 0) {
            const searchTerm = '<?= $search ?>'.toLowerCase();
            const titles = document.querySelectorAll('.recipe-title');
            
            titles.forEach(title => {
                const text = title.textContent;
                if (text.toLowerCase().includes(searchTerm)) {
                    const regex = new RegExp(searchTerm, 'gi');
                    title.innerHTML = text.replace(regex, match => `<span class="search-highlight">${match}</span>`);
                }
            });
        }

        // Like animation
        document.querySelectorAll('[name="like_recipe"]').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    const heartIcon = this.querySelector('i');
                    heartIcon.classList.add('like-animation');
                    
                    setTimeout(() => {
                        heartIcon.classList.remove('like-animation');
                    }, 800);
                }
            });
        });
    </script>
</body>
</html>