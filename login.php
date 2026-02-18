<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles/login.css">
    <link rel="icon" href="imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>

    <div class="container">
        <div class="left_half">
        <img src="imgs/imgsroles/cpt3.jpg" alt="Admin Image">
        </div>

        <header>
            <div class="header">
                <!--<a href="index.php">Back</a>-->
            </div>
        </header>

        <div class="adminlogin">
            <form class="admin_login_form" action="login_action.php" method="POST">
            <img src="imgs/imgsroles/logocrisnil.png" alt="Logo" class="logo">
            
                <h1>LOGIN</h1>
                <p>Name</p>
                <input type="text" name="name" placeholder="Enter name">
                <p>Username</p>
                <input type="text" name="usrname" placeholder="Enter Username">
                <p>Password</p>
                <input type="password" name="password" id="password_visible" placeholder="Enter Password">
                <br>

                <div class="forgot_password">
                    <label class="show_password_label">
                    <input type="checkbox" onclick="pass_show()"> Show Password
                    </label>
                    <a href="password_reset.php">Forgot Password?</a>
                </div>
               
                <script type="text/javascript" src="scripts/password_script.js"></script>

                
                <br><br>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>

</body>
</html>
