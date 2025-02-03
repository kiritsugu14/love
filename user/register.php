<?php
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = $_POST['username']; // Username
    $newEmail = $_POST['email']; // New email to update
    $newPassword = $_POST['password']; // New password

    // Validate email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $newEmail)) {
        $errorMessage = "Dapat totoong gmail to ah love dapat may @gmail.";
    } 
    // Validate password
    elseif (strlen($newPassword) < 6 || !preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword)) {
        $errorMessage = "Love 6 characters po dapat. Pakilagyan rin po ng Uppercase, Lowercase at number dapat meron tigisa hehe.";
    } 
    else {
        $newPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the new password

        try {
            // Prepare the insert query
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            // Execute the query with the provided values
            if ($stmt->execute([$username, $newEmail, $newPassword])) {
                // Show success message with an Okay button
                echo "<div id='popup' class='popup'>
                        <div class='popup-content'>
                            <p>Yon may account kana congrats haha!</p>
                            <button onclick='redirectToProfile()' class='btn btn-primary'>Okay Login Mona</button>
                        </div>
                      </div>
                      <script>
                        function redirectToProfile() {
                            window.location.href = 'profile.php';
                        }
                      </script>";
            } else {
                $errorMessage = "aray kopo nag error.";
            }
        } catch (PDOException $e) {
            $errorMessage = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - OurBook</title>
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

        .register-box {
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

        /* Popup styles */
        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .popup-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        }

        .popup-content p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .popup-content .btn-primary {
            background-color: #ff4081;
            border: none;
            padding: 10px 20px;
        }

        .popup-content .btn-primary:hover {
            background-color: #ff1c68;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>
                <img src="../Img/penguin.png" alt="Penguin Logo"> OurBook
            </h1>
            <p>Ay wala ka pa bang account? sorry na! pina LogIn ka kagad eh no gawa kamuna haha!</p>
        </div>

        <div class="register-box">
            <h2 class="text-center text-white">Create an Account</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <?php if (!empty($errorMessage)) echo "<p class='text-danger'>$errorMessage</p>"; ?>
                <?php if (!empty($successMessage)) echo "<p class='text-success'>$successMessage</p>"; ?>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3"><a href="login.php">Already have an account?</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
