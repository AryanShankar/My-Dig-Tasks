<?php

    function emptyInputSignup($name, $email, $uid, $pwd, $pwdRepeat){
        $result = null;
        if(empty($name) || empty($email) || empty($uid) || empty($pwd) || empty($pwdRepeat)){
            $result = true;
        }
        else{
            $result = false;
        }
        return $result;
    }

    function invalidEmail($email){
        $result = null;
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $result = true; 
        }
        else{
            $result = false;
        }
        return $result;
    }

    function invalidUid($username){
        $result = null;
        if(!preg_match("/^[a-zA-Z0-9]*$/", $username)){
            $result = true; 
        }
        else{
            $result = false;
        }
        return $result;
    }

    function pwdMatch($pwd, $pwdRepeat){
        $result = null;
        if($pwd !== $pwdRepeat){
            $result = true; 
        }
        else{
            $result = false;
        }
        return $result;
    }

    function uidExists($conn, $username, $email){
        $sql = "SELECT * FROM users WHERE usersUid = ? OR usersEmail = ?;";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)){
            header("location: ../signup.php?error=stmtfailed");
            exit();
        }

        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);

        $resultData = mysqli_stmt_get_result($stmt);

        if($row = mysqli_fetch_assoc($resultData)){
            return $row;
        }
        else{
            $result = false;
            return $result;
        }

        mysqli_stmt_close($stmt);
    }

    function createUser($conn, $name, $email, $username, $pwd){
        $sql = "INSERT INTO users (usersName, usersEmail, usersUid, usersPwd) VALUES (?, ?, ?, ?);";
        $stmt = mysqli_stmt_init($conn);
        if(!mysqli_stmt_prepare($stmt, $sql)){
            header("location: ../signup.php?error=stmtfailed");
            exit();
        }

        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

        mysqli_stmt_bind_param($stmt, "ssss",$name, $email, $username, $hashedPwd);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        header("location: ../signup.php?error=none");
        exit();
    }

    function emptyInputLogin($username, $pwd){
        $result = null;
        if(empty($username) || empty($pwd)){
            $result = true;
        }
        else{
            $result = false;
        }
        return $result;
    }

    function loginUser($conn, $username, $pwd){
        $uidExists = uidExists($conn, $username, $username);

        if($uidExists === false){
            header("location: ../login.php?error=userdoesnotexist");
            exit();
        }

        $hashedPwd = $uidExists["usersPwd"];
        $checkPwd = password_verify($pwd, $hashedPwd);

        if($checkPwd === false){
            header("location: ../login.php?error=wrongpassword");
            exit();
        }
        else if($checkPwd === true){
            session_start();
            $_SESSION["userid"] = $uidExists["usersId"];
            $_SESSION["useruid"] = $uidExists["usersUid"];
            $_SESSION["userName"] = $uidExists["usersName"];
            header("location: ../home.php");
            exit();
        }
    }

?>