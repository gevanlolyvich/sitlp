<?php
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_SESSION['last_activity'])){
    if(time() - $_SESSION['last_activity'] > SESSION_TIMEOUT){
      session_destroy();
      header("Location: ../auth/login.php?expired=1");
      exit;
    }
}

$_SESSION['last_activity']=time();
