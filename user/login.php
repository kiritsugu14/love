<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Store user details in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role']; // Store role in session

        // Check if the user is an admin
        if ($user['role'] == 'admin') {
            header("Location: ../admin/ahome.php"); // Redirect to admin home page
        } else {
            header("Location: home.php"); // Redirect to user home page
        }
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - OurBook</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff758c, #ff7eb3);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            display: flex;
            width: 80%;
            max-width: 1200px;
        }

        .left-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-right: 50px;
            color: white;
        }

        .left-section h1 {
            font-size: 3rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            color: #ff4081;
        }

        .left-section img {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }

        .left-section p {
            font-size: 1.2rem;
            margin-top: 10px;
        }

        .login-box {
            flex: 0.5;
            padding: 30px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-label {
            color: #fff;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #ff4081;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff1c68;
        }

        a {
            color: #fff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>
                <img src="../Img/penguin.png" alt="Penguin Logo"> OurBook
            </h1>
            <p>Hi My love Mag LogIn kana!!!</p>
        </div>

        <div class="login-box">
            <h2 class="text-center text-white">Login</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3"><a href="register.php">Create an account</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
