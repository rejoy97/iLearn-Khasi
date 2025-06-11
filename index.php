<?php
session_start();
if (isset($_GET['error'])):
    echo '<p class="error">';
    if ($_GET['error'] == "invalid_password") echo "Invalid password.";
    elseif ($_GET['error'] == "no_user") echo "No user found with this email.";
    echo '</p>';
endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iLearn Khasi | Register & Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>iLearn Khasi</h1>

        <!-- Authentication Forms -->
        <div id="auth-container">
            <!-- Login Form -->
            <div id="login-form" class="form">
                <h2>Login</h2>
                <form action="login_process.php" method="POST">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login">Login</button>
                    <p class="recover"><a href="forgot-password.php">Forgot Password?</a></p>
                    <p>Don't have an account? <a href="register_process.php" id="show-register">Register</a></p>
                    <p>Are you an Admin? <a href="admin_login.php">Login Here</p>
                </form>
            </div>

            <!-- Register Form -->
            <div id="register-form" class="form hidden">
                <h2>Register</h2>
                <form action="register.php" method="POST">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="register">Register</button>
                    <p>Already have an account? <a href="#" id="show-login">Login</a></p>

                </form>
            </div>
        </div>
    </div>
</body>
</html>
