<?php

    SESSION_START();

    include "config/database_conn.php";

    if (isset($_POST['usrname']) && isset($_POST['password'])){

        function validate($data){
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $validate_username = validate($_POST['usrname']);
        $validate_password = validate($_POST['password']);
    
        if (empty($validate_username)){
            echo "<script type='text/javascript'>alert('Username is required'); 
            window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
            </script>";
            exit();
        }

        else if (empty($validate_password)){
            echo "<script type='text/javascript'>alert('Password is required'); 
            window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
            </script>";
            exit();
        }

        else{
            $sql = "SELECT * FROM crisnil_users WHERE USER_NAME = '$validate_username' AND USER_PASSWORD = '$validate_password' AND USER_STATUS = 'ACTIVATED'";

            $res_query = mysqli_query($databaseconn, $sql);

            if (mysqli_num_rows($res_query) === 1){
                $row = mysqli_fetch_assoc($res_query);

                if ($row['USER_NAME'] === $validate_username && $row['USER_PASSWORD']){
                    $_SESSION['USER_ID'] = $row['USER_ID'];
                    $_SESSION['USER_NAME'] = $row['USER_NAME'];

                    require_once __DIR__ . '/models/ActionLogModel.php';
                        $actionLog = new ActionLog($databaseconn);
                        $actionLog->create(
                            (int)$_SESSION['USER_ID'],
                            'login',
                            'User logged in successfully');

                    header('Location: views/dashboard.php');
                }
    
                else{
                    echo "<script type='text/javascript'>alert('Invalid username or password!'); 
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                    </script>";
                    exit();
                }
            }
            
            else{
                echo "<script type='text/javascript'>alert('Invalid username or password!'); 
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                </script>";
                exit();
            }
        }
    }

    else{
        header("Location: index.php");
        exit();
    }

?>