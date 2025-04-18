<?php
require_once('../includes/db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch likes on the user's recipes
$likeQuery = "
    SELECT l.*, u.username, u.avatar, r.name AS recipe_name, r.id AS recipe_id
    FROM likes l
    JOIN recipes r ON l.recipe_id = r.id
    JOIN users u ON l.user_id = u.id
    WHERE r.user_id = ?
    ORDER BY l.created_at DESC
";

$likeStmt = $conn->prepare($likeQuery);
$likeStmt->bind_param("i", $user_id);
$likeStmt->execute();
$likesResult = $likeStmt->get_result();

// Fetch comments on the user's recipes
$commentQuery = "
    SELECT c.*, u.username, u.avatar, r.name AS recipe_name, r.id AS recipe_id
    FROM comments c
    JOIN recipes r ON c.recipe_id = r.id
    JOIN users u ON c.user_id = u.id
    WHERE r.user_id = ?
    ORDER BY c.created_at DESC
";

$commentStmt = $conn->prepare($commentQuery);
$commentStmt->bind_param("i", $user_id);
$commentStmt->execute();
$commentsResult = $commentStmt->get_result();

// Count unread notifications
$unreadLikes = 0;
$unreadComments = 0;

// You would need to add a 'read' column to your likes and comments tables
// This is just a placeholder for the concept
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Zaayka Junction</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        
        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 30px 0;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }
        
        .notification-container {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .notification-header h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .notification-header h4 i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .notification-count {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 3px 10px;
            font-size: 0.8rem;
            margin-left: 10px;
        }
        
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 3px solid var(--light-color);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .notif-box {
            background: var(--light-color);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
            position: relative;
        }
        
        .notif-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .notif-box.like-notif {
            border-left-color: var(--heart-color);
        }
        
        .notif-box.comment-notif {
            border-left-color: var(--comment-color);
        }
        
        .notif-box.unread {
            background-color: rgba(255, 152, 0, 0.05);
        }
        
        .notif-box.unread::after {
            content: '';
            position: absolute;
            top: 20px;
            right: 20px;
            width: 10px;
            height: 10px;
            background-color: var(--primary-color);
            border-radius: 50%;
        }
        
        .notif-content {
            flex: 1;
        }
        
        .notif-time {
            font-size: 0.8rem;
            color: var(--light-text);
            margin-top: 5px;
            display: flex;
            align-items: center;
        }
        
        .notif-time i {
            margin-right: 5px;
            font-size: 0.9em;
        }
        
        .notif-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .like-icon {
            background-color: rgba(233, 30, 99, 0.1);
            color: var(--heart-color);
        }
        
        .comment-icon {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--comment-color);
        }
        
        .notif-username {
            font-weight: 600;
            color: var(--accent-color);
        }
        
        .notif-recipe {
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .notif-recipe:hover {
            color: var(--accent-color);
        }
        
        .comment-text {
            background-color: rgba(0,0,0,0.02);
            border-radius: 10px;
            padding: 10px 15px;
            margin-top: 10px;
            font-style: italic;
            color: var(--light-text);
            position: relative;
        }
        
        .comment-text::before {
            content: '"';
            font-size: 2em;
            color: rgba(0,0,0,0.1);
            position: absolute;
            left: 5px;
            top: -5px;
        }
        
        .comment-text::after {
            content: '"';
            font-size: 2em;
            color: rgba(0,0,0,0.1);
            position: absolute;
            right: 5px;
            bottom: -20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--light-text);
        }
        
        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            color: rgba(0,0,0,0.1);
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            padding: 5px;
            box-shadow: var(--box-shadow);
        }
        
        .tab-button {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border-radius: var(--border-radius);
            transition: all 0.3s;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tab-button i {
            margin-right: 8px;
        }
        
        .tab-button.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .mark-all-read {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .mark-all-read:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .action-btn {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .view-recipe-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .view-recipe-btn:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .time-ago {
            color: var(--light-text);
            font-size: 0.8rem;
        }
        
        @media (max-width: 768px) {
            .notification-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .mark-all-read {
                margin-top: 15px;
            }
            
            .tab-button {
                font-size: 0.9rem;
                padding: 10px;
            }
            
            .tab-button i {
                margin-right: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="fas fa-bell me-3"></i>Notifications</h1>
                <button class="mark-all-read" id="markAllRead">
                    <i class="fas fa-check-double me-2"></i>Mark all as read
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="tab-buttons">
            <div class="tab-button active" data-tab="all">
                <i class="fas fa-list"></i> All
            </div>
            <div class="tab-button" data-tab="likes">
                <i class="fas fa-heart"></i> Likes
                <?php if ($likesResult->num_rows > 0): ?>
                <span class="notification-count"><?= $likesResult->num_rows ?></span>
                <?php endif; ?>
            </div>
            <div class="tab-button" data-tab="comments">
                <i class="fas fa-comment"></i> Comments
                <?php if ($commentsResult->num_rows > 0): ?>
                <span class="notification-count"><?= $commentsResult->num_rows ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Notifications Tab -->
        <div class="tab-content active" id="all">
            <div class="notification-container">
                <?php 
                // Reset result sets
                $likesResult->data_seek(0);
                $commentsResult->data_seek(0);
                
                // Combine likes and comments
                $allNotifications = [];
                
                while ($like = $likesResult->fetch_assoc()) {
                    $like['type'] = 'like';
                    $allNotifications[] = $like;
                }
                
                while ($comment = $commentsResult->fetch_assoc()) {
                    $comment['type'] = 'comment';
                    $allNotifications[] = $comment;
                }
                
                // Sort by created_at
                usort($allNotifications, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                
                if (count($allNotifications) > 0):
                    foreach ($allNotifications as $index => $notification):
                        $isUnread = $index < 3; // Just for demo, first 3 are unread
                        $timeAgo = time_elapsed_string($notification['created_at']);
                        
                        if ($notification['type'] == 'like'):
                ?>
                <div class="notif-box like-notif d-flex align-items-start <?= $isUnread ? 'unread' : '' ?>">
                    <div class="notif-icon like-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <img src="<?= $notification['avatar'] ? '../uploads/avatar/' . htmlspecialchars($notification['avatar']) : '../assets/default-avatar.png' ?>" class="avatar" alt="avatar">
                    <div class="notif-content">
                        <div>
                            <span class="notif-username"><?= htmlspecialchars($notification['username']) ?></span> 
                            liked your recipe 
                            <a href="../recipes/view.php?id=<?= $notification['recipe_id'] ?>" class="notif-recipe">"<?= htmlspecialchars($notification['recipe_name']) ?>"</a>
                        </div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> <span class="time-ago"><?= $timeAgo ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="../recipes/view.php?id=<?= $notification['recipe_id'] ?>" class="action-btn view-recipe-btn">
                                <i class="fas fa-eye"></i> View Recipe
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="notif-box comment-notif d-flex align-items-start <?= $isUnread ? 'unread' : '' ?>">
                    <div class="notif-icon comment-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <img src="<?= $notification['avatar'] ? '../uploads/avatar/' . htmlspecialchars($notification['avatar']) : '../assets/default-avatar.png' ?>" class="avatar" alt="avatar">
                    <div class="notif-content">
                        <div>
                            <span class="notif-username"><?= htmlspecialchars($notification['username']) ?></span> 
                            commented on your recipe 
                            <a href="../recipes/view.php?id=<?= $notification['recipe_id'] ?>" class="notif-recipe">"<?= htmlspecialchars($notification['recipe_name']) ?>"</a>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($notification['comment_text'])) ?>
                        </div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> <span class="time-ago"><?= $timeAgo ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="../recipes/view.php?id=<?= $notification['recipe_id'] ?>" class="action-btn view-recipe-btn">
                                <i class="fas fa-eye"></i> View Recipe
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                        endif;
                    endforeach;
                else:
                ?>
                <div class="empty-state">
                    <i class="far fa-bell-slash"></i>
                    <p>You don't have any notifications yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Likes Tab -->
        <div class="tab-content" id="likes">
            <div class="notification-container">
                <div class="notification-header">
                    <h4><i class="fas fa-heart" style="color: var(--heart-color);"></i> Likes on Your Recipes</h4>
                </div>
                
                <?php 
                $likesResult->data_seek(0);
                if ($likesResult->num_rows > 0):
                    while ($like = $likesResult->fetch_assoc()):
                        $timeAgo = time_elapsed_string($like['created_at']);
                ?>
                <div class="notif-box like-notif d-flex align-items-start">
                    <div class="notif-icon like-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <img src="<?= $like['avatar'] ? '../uploads/avatar/' . htmlspecialchars($like['avatar']) : '../assets/default-avatar.png' ?>" class="avatar" alt="avatar">
                    <div class="notif-content">
                        <div>
                            <span class="notif-username"><?= htmlspecialchars($like['username']) ?></span> 
                            liked your recipe 
                            <a href="../recipes/view.php?id=<?= $like['recipe_id'] ?>" class="notif-recipe">"<?= htmlspecialchars($like['recipe_name']) ?>"</a>
                        </div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> <span class="time-ago"><?= $timeAgo ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="../recipes/view.php?id=<?= $like['recipe_id'] ?>" class="action-btn view-recipe-btn">
                                <i class="fas fa-eye"></i> View Recipe
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="empty-state">
                    <i class="far fa-heart"></i>
                    <p>No likes on your recipes yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comments Tab -->
        <div class="tab-content" id="comments">
            <div class="notification-container">
                <div class="notification-header">
                    <h4><i class="fas fa-comment" style="color: var(--comment-color);"></i> Comments on Your Recipes</h4>
                </div>
                
                <?php 
                $commentsResult->data_seek(0);
                if ($commentsResult->num_rows > 0):
                    while ($comment = $commentsResult->fetch_assoc()):
                        $timeAgo = time_elapsed_string($comment['created_at']);
                ?>
                <div class="notif-box comment-notif d-flex align-items-start">
                    <div class="notif-icon comment-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <img src="<?= $comment['avatar'] ? '../uploads/avatar/' . htmlspecialchars($comment['avatar']) : '../assets/default-avatar.png' ?>" class="avatar" alt="avatar">
                    <div class="notif-content">
                        <div>
                            <span class="notif-username"><?= htmlspecialchars($comment['username']) ?></span> 
                            commented on your recipe 
                            <a href="../recipes/view.php?id=<?= $comment['recipe_id'] ?>" class="notif-recipe">"<?= htmlspecialchars($comment['recipe_name']) ?>"</a>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
                        </div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> <span class="time-ago"><?= $timeAgo ?></span>
                        </div>
                        <div class="action-buttons">
                            <a href="../recipes/view.php?id=<?= $comment['recipe_id'] ?>" class="action-btn view-recipe-btn">
                                <i class="fas fa-eye"></i> View Recipe
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="empty-state">
                    <i class="far fa-comment-dots"></i>
                    <p>No comments on your recipes yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Mark all as read functionality
            document.getElementById('markAllRead').addEventListener('click', function() {
                const unreadNotifications = document.querySelectorAll('.notif-box.unread');
                unreadNotifications.forEach(notification => {
                    notification.classList.remove('unread');
                });
                
                // Here you would also make an AJAX call to update the database
                // For example:
                // fetch('mark_all_read.php', {
                //     method: 'POST',
                //     headers: {
                //         'Content-Type': 'application/json',
                //     },
                //     body: JSON.stringify({
                //         user_id: <?= $user_id ?>
                //     }),
                // })
                // .then(response => response.json())
                // .then(data => {
                //     console.log('Success:', data);
                // })
                // .catch((error) => {
                //     console.error('Error:', error);
                // });
                
                // Update notification counts
                const notificationCounts = document.querySelectorAll('.notification-count');
                notificationCounts.forEach(count => {
                    count.style.display = 'none';
                });
            });
            
            // Add animation to notification boxes
            const notifBoxes = document.querySelectorAll('.notif-box');
            notifBoxes.forEach((box, index) => {
                setTimeout(() => {
                    box.style.opacity = '0';
                    box.style.transform = 'translateY(20px)';
                    box.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    
                    setTimeout(() => {
                        box.style.opacity = '1';
                        box.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
    
    <?php
    // Helper function to format time
    function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        // Removed the week calculation
        // $diff->w = floor($diff->d / 7);
        // $diff->d -= $diff->w * 7;
        
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
        
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    ?>
</body>
</html>