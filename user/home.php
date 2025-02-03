<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ensure username is set in session
if (!isset($_SESSION['username'])) {
    // Fetch username and profile_pic from the database
    $stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Store username and profile_pic in session
    $_SESSION['username'] = $user['username'];
    $_SESSION['profile_pic'] = $user['profile_pic'];
}

// Handle new post submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_post'])) {
    $content = $_POST['content'];
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../Img/';
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . uniqid() . '_' . $imageName;  // Unique file name to avoid conflicts
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }

    // Insert post into database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $content, $imagePath]);
    header("Location: home.php");
    exit;
}

// Handle like
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['like_post'])) {
    $postId = $_POST['post_id'];
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $postId]);
    header("Location: home.php");
    exit;
}

// Handle comment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_post'])) {
    $postId = $_POST['post_id'];
    $commentContent = $_POST['comment_content'];
    $stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $postId, $commentContent]);
    header("Location: home.php");
    exit;
}

// Fetch posts
$stmt = $conn->prepare("SELECT posts.id, posts.content, posts.image, posts.created_at, users.username, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch likes and comments count for each post
foreach ($posts as &$post) {
    // Get like count
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$post['id']]);
    $post['like_count'] = $stmt->fetchColumn();

    // Get comments for each post
    $stmt = $conn->prepare("SELECT comments.content, comments.created_at, users.username, users.profile_pic FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
    $stmt->execute([$post['id']]);
    $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($post); // Break the reference
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home - OurBook</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff758c, #ff7eb3);
        }

        .navbar {
            background-color: #ff4081;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar-brand:hover,
        .navbar-nav .nav-link:hover {
            color: #ffe4e1;
        }

        .container {
            margin-top: 20px;
            max-width: 800px;
            /* Limit the maximum width of the content */
        }

        .feed {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            margin-bottom: 20px;
        }

        .post {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .post:last-child {
            border-bottom: none;
        }

        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .post-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .post-content {
            margin-top: 10px;
        }

        .post-image-container {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            /* Ensure the image doesn’t overflow */
            margin-bottom: 10px;
            border-radius: 10px;
            /* Optional, to make the edges rounded */
        }

        .post-image {
            width: 100%;
            /* Ensures image fills container width */
            height: auto;
            /* Maintains the aspect ratio */
            object-fit: cover;
            /* Ensures the image fits without stretching or distorting */
        }

        .post-footer {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .comment-section {
            margin-top: 10px;
        }

        .btn-primary {
            background-color: #ff4081;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff1c68;
        }

        .btn-danger {
            border-radius: 20px;
            padding: 5px 10px;
        }

        .profile-section {
            margin-bottom: 20px;
            color: #fff;
            font-size: 1.5rem;
        }

        .post-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            margin-bottom: 10px;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">OurBook</a>
            <div class="navbar-nav">
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link" href="#">Notifications</a>
                <a href="logout.php" class="btn btn-danger ml-3">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="profile-section text-left">
            <div class="feed">
                <div class="mb-4">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="content" class="form-label text-white">Create a Post</label>
                            <textarea name="content" id="content" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label text-white">Upload Image</label>
                            <input type="file" name="image" id="image" class="form-control">
                        </div>
                        <button type="submit" name="new_post" class="btn btn-primary w-100">Post</button>
                    </form>
                </div>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <img src="<?php echo htmlspecialchars($post['profile_pic']); ?>" alt="User Profile Picture">
                            <strong class="text-white"><?php echo htmlspecialchars($post['username']); ?></strong>
                        </div>
                        <div class="post-content">
                            <?php if ($post['image']): ?>
                                <div class="post-image-container">
                                    <img src="<?php echo $post['image']; ?>" alt="Post Image" class="post-image">
                                </div>
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        <div class="post-footer text-white">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="like_post" class="btn btn-primary">Like</button>
                            </form>
                            <small>Likes: <?php echo $post['like_count']; ?></small>
                            <small>Posted on <?php echo $post['created_at']; ?></small>
                        </div>
                        <div class="comment-section">
                            <?php foreach ($post['comments'] as $comment): ?>
                                <div class="comment text-white">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    <small><?php echo $comment['created_at']; ?></small>
                                </div>
                            <?php endforeach; ?>
                            <form method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="mb-3">
                                    <label for="comment_content_<?php echo $post['id']; ?>" class="form-label text-white">Add a comment</label>
                                    <textarea name="comment_content" id="comment_content_<?php echo $post['id']; ?>" class="form-control" rows="2" required></textarea>
                                </div>
                                <button type="submit" name="comment_post" class="btn btn-primary">Comment</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>