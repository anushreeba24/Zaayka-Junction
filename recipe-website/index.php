<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: pages/feed.php"); // Redirect logged-in users to the feed page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaayka Junction – Flavors Meet Here</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap">
    <style>
        :root {
            --primary-color: #FF6B35; /* Vibrant Orange */
            --secondary-color: #FFC107; /* Yellow */
            --accent-color: #FF3D00; /* Deep Orange */
            --text-color: #1A202C;
            --light-text: #4A5568;
            --light-color: #FFFFFF;
            --background-color: #FFFFFF;
            --light-bg: #F9FAFB;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }
        
        /* Header Styles */
        header {
            padding: 20px 0;
            background-color: var(--light-color);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Updated Logo Styles */
        .logo {
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            text-decoration: none;
            position: relative;
            transition: var(--transition);
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        .logo-icon {
            display: flex;
            align-items: center;
            margin-right: 10px;
            opacity: 0.7;
        }
        
        .logo-text {
            display: flex;
            align-items: center;
        }
        
        .logo-text-zaayka {
            color: #FFA500; /* Orange color from the logo */
            font-size: 24px;
            margin-right: 5px;
        }
        
        .logo-text-junction {
            color: #FF4500; /* Deeper orange for "Junction" */
            font-size: 24px;
        }
        
        .nav-links {
            display: flex;
            gap: 40px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            padding: 5px 0;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            transition: var(--transition);
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .login-btn {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: var(--transition);
            padding: 10px 20px;
            border-radius: 30px;
            border: 1px solid transparent;
        }
        
        .login-btn:hover {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background-color: rgba(255, 107, 53, 0.05);
        }
        
        .login-btn svg {
            margin-left: 8px;
            transition: var(--transition);
        }
        
        .login-btn:hover svg {
            transform: translateX(4px);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
        }
        
        /* Hero Section */
        .hero {
            padding: 80px 0;
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background-color: rgba(255, 107, 53, 0.05);
            border-radius: 50%;
            z-index: -1;
            animation: float 15s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0);
            }
            25% {
                transform: translate(-30px, 30px);
            }
            50% {
                transform: translate(-15px, -15px);
            }
            75% {
                transform: translate(30px, -30px);
            }
        }
        
        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background-color: rgba(255, 193, 7, 0.05);
            border-radius: 50%;
            z-index: -1;
            animation: float 12s infinite ease-in-out reverse;
        }
        
        .hero .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 60px;
        }
        
        .hero-content {
            flex: 1;
            max-width: 550px;
            animation: fadeInUp 0.8s ease forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 30px;
            color: var(--text-color);
            position: relative;
        }
        
        .hero-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
            animation: expandWidth 1.5s 0.5s forwards;
        }
        
        @keyframes expandWidth {
            from { width: 0; }
            to { width: 80px; }
        }
        
        .hero-description {
            font-size: 1.1rem;
            color: var(--light-text);
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeIn 1s 0.5s forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .hero-images {
            flex: 1;
            position: relative;
            height: 550px;
            transform: scale(0.95);
            opacity: 0;
            animation: scaleIn 1s 0.3s forwards;
        }
        
        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .image-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 15px;
            height: 100%;
            width: 100%;
        }
        
        .grid-item {
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .grid-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0) 70%, rgba(0,0,0,0.3) 100%);
            z-index: 1;
            opacity: 0;
            transition: var(--transition);
        }
        
        .grid-item:hover {
            transform: translateY(-5px);
        }
        
        .grid-item:hover::before {
            opacity: 1;
        }
        
        .grid-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .grid-item:hover img {
            transform: scale(1.05);
        }
        
        /* Grid item positioning - improved layout */
        .item1 {
            grid-column: 1 / 6;
            grid-row: 1 / 5;
            animation: fadeInLeft 0.8s 0.2s forwards;
            opacity: 0;
        }
        
        .item2 {
            grid-column: 6 / 9;
            grid-row: 1 / 4;
            animation: fadeInRight 0.8s 0.4s forwards;
            opacity: 0;
        }
        
        .item3 {
            grid-column: 1 / 4;
            grid-row: 5 / 9;
            animation: fadeInLeft 0.8s 0.6s forwards;
            opacity: 0;
        }
        
        .item4 {
            grid-column: 4 / 6;
            grid-row: 5 / 9;
            animation: fadeInUp 0.8s 0.8s forwards;
            opacity: 0;
        }
        
        .item5 {
            grid-column: 6 / 9;
            grid-row: 4 / 9;
            animation: fadeInRight 0.8s 1s forwards;
            opacity: 0;
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* About Us Section */
        .about-us {
            padding: 100px 0;
            background-color: var(--light-bg);
            position: relative;
            overflow: hidden;
        }
        
        .about-us::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 107, 53, 0.05);
            border-radius: 50%;
            z-index: 0;
        }
        
        .about-container {
            display: flex;
            align-items: center;
            gap: 80px;
            position: relative;
            z-index: 1;
        }
        
        .about-image {
            flex: 1;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            position: relative;
            opacity: 0;
            transform: translateX(-30px);
            transition: var(--transition);
        }
        
        .about-image.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .about-image::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background-color: var(--secondary-color);
            opacity: 0.1;
            border-radius: 50%;
            z-index: -1;
        }
        
        .about-image::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 150px;
            height: 150px;
            background-color: var(--primary-color);
            opacity: 0.1;
            border-radius: 50%;
            z-index: -1;
        }
        
        .about-image img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .about-image:hover img {
            transform: scale(1.03);
        }
        
        .about-content {
            flex: 1;
            opacity: 0;
            transform: translateX(30px);
            transition: var(--transition);
        }
        
        .about-content.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-color);
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 0;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 2px;
            transition: width 1s ease;
        }
        
        .section-title.visible::after {
            width: 60px;
        }
        
        .section-description {
            margin-bottom: 30px;
            color: var(--light-text);
        }
        
        /* Featured Recipes Section */
        .featured-recipes {
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .featured-recipes::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background-color: rgba(255, 193, 7, 0.05);
            border-radius: 50%;
            z-index: -1;
        }
        
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .recipe-card {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .recipe-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .recipe-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .recipe-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
        }
        
        .recipe-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }
        
        .recipe-card:hover .recipe-image {
            transform: scale(1.08);
        }
        
        .recipe-content {
            padding: 24px;
        }
        
        .recipe-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-color);
        }
        
        .recipe-info {
            display: flex;
            justify-content: space-between;
            color: var(--light-text);
            font-size: 0.9rem;
            align-items: center;
        }
        
        .recipe-time {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .recipe-rating {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background-color: var(--light-bg);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 107, 53, 0.05);
            border-radius: 50%;
            z-index: 0;
        }
        
        .cta-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 193, 7, 0.05);
            border-radius: 50%;
            z-index: 0;
        }
        
        .cta-content {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            opacity: 0;
            transform: translateY(30px);
            transition: var(--transition);
        }
        
        .cta-content.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .cta-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-color);
        }
        
        .cta-description {
            font-size: 1.1rem;
            color: var(--light-text);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Footer */
        footer {
            background-color: #F7F7F7;
            padding: 80px 0 30px;
            position: relative;
        }
        
        .footer-container {
            display: grid;
            grid-template-columns: 1.5fr repeat(3, 1fr);
            gap: 60px;
        }
        
        .footer-logo {
            display: flex;
            flex-direction: column;
        }
        
        .footer-logo a {
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 20px;
            position: relative;
        }
        
        .footer-logo span {
            position: relative;
        }
        
        .footer-logo span::before {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            background: var(--secondary-color);
            border-radius: 50%;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            z-index: -1;
            opacity: 0.2;
        }
        
        .footer-description {
            color: var(--light-text);
            font-size: 0.95rem;
            line-height: 1.7;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .social-link:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px) rotate(8deg);
        }
        
        .footer-links {
            display: flex;
            flex-direction: column;
        }
        
        .footer-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 25px;
            color: var(--text-color);
            position: relative;
            display: inline-block;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 30px;
            height: 2px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }
        
        .footer-links a {
            text-decoration: none;
            color: var(--light-text);
            margin-bottom: 12px;
            transition: var(--transition);
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 50px;
            color: var(--light-text);
            font-size: 0.9rem;
            border-top: 1px solid #E2E8F0;
            margin-top: 50px;
        }
        
        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            z-index: -1;
            transition: all 0.4s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--light-color);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 400px;
            height: 100vh;
            background-color: white;
            z-index: 1000;
            padding: 80px 40px;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .mobile-menu.active {
            right: 0;
        }
        
        .close-menu {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
        }
        
        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .mobile-nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            font-size: 18px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            transition: var(--transition);
        }
        
        .mobile-nav-links a:hover {
            color: var(--primary-color);
            padding-left: 5px;
        }
        
        .mobile-login-btn {
            margin-top: auto;
            display: inline-block;
            text-align: center;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 3rem;
            }
            
            .hero-images {
                height: 500px;
            }
        }
        
        @media (max-width: 992px) {
            .hero .container {
                flex-direction: column;
            }
            
            .hero-content {
                max-width: 100%;
                margin-bottom: 60px;
                text-align: center;
            }
            
            .hero-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .hero-images {
                width: 100%;
                height: 500px;
            }
            
            .about-container {
                flex-direction: column;
            }
            
            .about-image {
                width: 100%;
                margin-bottom: 40px;
            }
            
            .about-content {
                text-align: center;
            }
            
            .section-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .hero-images {
                height: auto;
            }
            
            .image-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(3, 200px);
                gap: 15px;
            }
            
            .item1 {
                grid-column: 1 / 2;
                grid-row: 1 / 2;
            }
            
            .item2 {
                grid-column: 2 / 3;
                grid-row: 1 / 2;
            }
            
            .item3 {
                grid-column: 1 / 3;
                grid-row: 2 / 3;
            }
            
            .item4 {
                grid-column: 1 / 2;
                grid-row: 3 / 4;
            }
            
            .item5 {
                grid-column: 2 / 3;
                grid-row: 3 / 4;
            }
            
            .recipes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .cta-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .recipes-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .footer-logo, .footer-links {
                text-align: center;
            }
            
            .footer-logo a {
                justify-content: center;
            }
            
            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <div class="logo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#888888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
                            <path d="M7 2v20"></path>
                            <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="logo-text-zaayka">Zaayka</span>
                        <span class="logo-text-junction">Junction</span>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="#">Recipes</a>
                    <a href="#">Categories</a>
                    <a href="#">Resources</a>
                    <a href="#about">About Us</a>
                </div>
                <a href="pages/login.php" class="login-btn">
                    Log in
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"></path>
                    </svg>
                </a>
                <button class="mobile-menu-btn" id="menuBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </nav>
        </div>
    </header>

    <div class="mobile-menu" id="mobileMenu">
        <button class="close-menu" id="closeMenu">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="mobile-nav-links">
            <a href="error.html">Recipes</a>
            <a href="error.html">Categories</a>
            <a href="error.html">Resources</a>
            <a href="#about">About Us</a>
        </div>
        <a href="pages/login.php" class="btn btn-primary mobile-login-btn">Log in</a>
    </div>
    <div class="overlay" id="overlay"></div>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">Discover Flavors from Every "State" </h1>
                    <p class="hero-description">Zaayka Junction is where culinary traditions meet modern tastes. Explore recipes from around the world, share your creations, and join a community of food enthusiasts passionate about authentic flavors.</p>
                    <a href="pages/register.php" class="btn btn-primary">Start cooking today</a>
                </div>
                <div class="hero-images">
                    <div class="image-grid">
                        <div class="grid-item item1">
                            <img src="assets/images/fp.jpg" alt="Food preparation">
                        </div>
                        <div class="grid-item item2">
                            <img src="assets/images/ingr.jpg" alt="Cooking ingredients">
                        </div>
                        <div class="grid-item item3">
                            <img src="assets/images/cook.jpg" alt="Chef cooking">
                        </div>
                        <div class="grid-item item4">
                            <img src="assets/images/foodplating.jpg" alt="Food plating">
                        </div>
                        <div class="grid-item item5">
                            <img src="assets/images/family-enjoying-meal.jpg" alt="Family enjoying meal">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="about-us">
            <div class="container">
                <div class="about-container">
                    <div class="about-image">
                        <img src="assets/images/os.jpeg" alt="Our story" />
                    </div>
                    <div class="about-content">
                        <h2 class="section-title">About Us</h2>
                        <p class="section-description">Zaayka Junction was born from a passion for authentic flavors and the joy of sharing meals. Our mission is to connect food lovers across cultures and traditions, creating a global community where recipes are shared, stories are told, and culinary skills are celebrated.</p>
                        <p class="section-description">Founded in 2025, we've grown into a vibrant platform where both amateur cooks and professional chefs come together to explore the rich tapestry of global cuisine. From traditional family recipes to innovative fusion dishes, Zaayka Junction is where flavors truly meet.</p>
                        <a href="error.html" class="btn btn-secondary">Learn more about us</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="featured-recipes">
            <div class="container">
                <h2 class="section-title">Featured Recipes</h2>
                <p class="section-description">Explore our most loved recipes from around the world</p>
                <div class="recipes-grid">
                    <div class="recipe-card">
                        <div class="recipe-image-container">
                            <img src="assets/images/bc.jpeg" alt="Butter Chicken" class="recipe-image">
                        </div>
                        <div class="recipe-content">
                            <h3 class="recipe-title">Butter Chicken</h3>
                            <div class="recipe-info">
                                <span class="recipe-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    30 mins
                                </span>
                                <span class="recipe-rating">4.8 ★</span>
                            </div>
                        </div>
                    </div>
                    <div class="recipe-card">
                        <div class="recipe-image-container">
                            <img src="assets/images/biryani.jpeg" alt="Vegetable Biryani" class="recipe-image">
                        </div>
                        <div class="recipe-content">
                            <h3 class="recipe-title">Vegetable Biryani</h3>
                            <div class="recipe-info">
                                <span class="recipe-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    45 mins
                                </span>
                                <span class="recipe-rating">4.6 ★</span>
                            </div>
                        </div>
                    </div>
                    <div class="recipe-card">
                        <div class="recipe-image-container">
                            <img src="assets/images/dosa.jpeg" alt="Masala Dosa" class="recipe-image">
                        </div>
                        <div class="recipe-content">
                            <h3 class="recipe-title">Masala Dosa</h3>
                            <div class="recipe-info">
                                <span class="recipe-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    40 mins
                                </span>
                                <span class="recipe-rating">4.7 ★</span>
                            </div>
                        </div>
                    </div>
                    <div class="recipe-card">
                        <div class="recipe-image-container">
                            <img src="assets/images/gj.jpg" alt="Gulab Jamun" class="recipe-image">
                        </div>
                        <div class="recipe-content">
                            <h3 class="recipe-title">Gulab Jamun</h3>
                            <div class="recipe-info">
                                <span class="recipe-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    25 mins
                                </span>
                                <span class="recipe-rating">4.9 ★</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2 class="cta-title">Join our culinary community</h2>
                    <p class="cta-description">Connect with food enthusiasts, share your recipes, and discover new flavors from around the world.</p>
                    <a href="pages/register.php" class="btn btn-primary">Sign up for free</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-container">
                <div class="footer-logo">
                    <a href="index.php">
                        <div class="logo-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#888888" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
                                <path d="M7 2v20"></path>
                                <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"></path>
                            </svg>
                        </div>
                        <div class="logo-text">
                            <span class="logo-text-zaayka">Zaayka</span>
                            <span class="logo-text-junction">Junction</span>
                        </div>
                    </a>
                    <p class="footer-description">Flavors Meet Here - Your destination for culinary exploration and recipe sharing. Join our community of food enthusiasts from around the world.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect x="2" y="9" width="4" height="12"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-title">Explore</h3>
                    <a href="#">Recipes</a>
                    <a href="#">Categories</a>
                    <a href="#">Popular</a>
                    <a href="#">Latest</a>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-title">Company</h3>
                    <a href="#about">About Us</a>
                    <a href="#">Contact</a>
                    <a href="#">Careers</a>
                    <a href="#">Press</a>
                </div>
                
                <div class="footer-links">
                    <h3 class="footer-title">Legal</h3>
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 Zaayka Junction. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu functionality
        const menuBtn = document.getElementById('menuBtn');
        const closeMenu = document.getElementById('closeMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('overlay');
        
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        function closeMenuFunction() {
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        closeMenu.addEventListener('click', closeMenuFunction);
        overlay.addEventListener('click', closeMenuFunction);
        
        // Close mobile menu when clicking on a link
        const mobileLinks = document.querySelectorAll('.mobile-nav-links a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMenuFunction);
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Scroll animations
        function handleScrollAnimations() {
            const elements = {
                aboutImage: document.querySelector('.about-image'),
                aboutContent: document.querySelector('.about-content'),
                sectionTitles: document.querySelectorAll('.section-title'),
                recipeCards: document.querySelectorAll('.recipe-card'),
                ctaContent: document.querySelector('.cta-content')
            };
            
            const isInViewport = (element) => {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                    rect.bottom >= 0
                );
            };
            
            if (elements.aboutImage && isInViewport(elements.aboutImage)) {
                elements.aboutImage.classList.add('visible');
            }
            
            if (elements.aboutContent && isInViewport(elements.aboutContent)) {
                elements.aboutContent.classList.add('visible');
            }
            
            elements.sectionTitles.forEach(title => {
                if (isInViewport(title)) {
                    title.classList.add('visible');
                }
            });
            
            elements.recipeCards.forEach((card, index) => {
                if (isInViewport(card)) {
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, index * 150);
                }
            });
            
            if (elements.ctaContent && isInViewport(elements.ctaContent)) {
                elements.ctaContent.classList.add('visible');
            }
        }
        
        // Run on initial load
        document.addEventListener('DOMContentLoaded', handleScrollAnimations);
        
        // Run on scroll
        window.addEventListener('scroll', handleScrollAnimations);
    </script>
</body>
</html>