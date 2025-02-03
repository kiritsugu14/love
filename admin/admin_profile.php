<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in as admin, if not redirect to login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user/login.php");  // Correct path to login.php
    exit();
}

// Fetch admin details
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch admin posts (assuming posts belong to admin too)
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../Img/';
        $uploadFile = $uploadDir . basename($_FILES['profile_pic']['name']);
            
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            // Update profile picture in the database
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$uploadFile, $_SESSION['user_id']]);
            // Refresh the page to show the updated profile picture
            header("Location: admin_profile.php");
            exit;
        } else {
            $errorMessage = "Error uploading profile picture.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Profile - OurBook</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff758c, #ff7eb3);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-container {
            width: 80%;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 30px;
            color: #fff;
            font-weight: 500;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }

        .profile-header h1 {
            font-size: 3rem;
            font-weight: bold;
            color: #ff4081;
        }

        .profile-content {
            margin-top: 20px;
        }

        .profile-content h2 {
            font-size: 2rem;
            color: #ff4081;
        }

        .profile-content p {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: #ff4081;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff1c68;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .post {
            background-color: rgba(255, 255, 255, 0.2);
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
        }

        .post-content {
            margin-top: 10px;
        }

        .post-footer {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo !empty($admin['profile_pic']) ? $admin['profile_pic'] : 'Img/default_profile_pic.png'; ?>" alt="Profile Picture" class="profile-pic">
            <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
        </div>
        <div class="profile-content">
            <h2>Profile Details:</h2>
            <p>Email: <?php echo $admin['email']; ?></p>
            <!-- Add more admin profile details here -->
        </div>
        <div class="mt-3">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="profile_pic" class="form-label">Update Profile Picture</label>
                    <input type="file" name="profile_pic" id="profile_pic" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
            <?php if (!empty($errorMessage)) echo "<p class='text-danger'>$errorMessage</p>"; ?>
        </div>

        <!-- Display admin posts -->
        <div class="mt-5">
            <h2>Your Posts:</h2>
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                        <?php if ($post['image']): ?>
                            <img src="<?php echo $post['image']; ?>" alt="Post Image" class="post-image" style="width: 100%; max-height: 300px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="post-footer">
                            <small>Posted on: <?php echo $post['created_at']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have not posted anything yet.</p>
            <?php endif; ?>
        </div>

        <!-- Back button to admin dashboard -->
        <a href="ahome.php" class="btn btn-secondary mt-3">Back to Home</a>

        <a href="../user/logout.php" class="btn btn-danger mt-3">Logout</a>
    </div>
</body>
</html>
