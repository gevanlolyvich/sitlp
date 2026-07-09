<?php

function checkRole($allowedRoles)
{
    if (!isset($_SESSION['role'])) {
        header(
            "Location: ../auth/login.php"
        );
        exit;
    }
    if (!in_array(
        $_SESSION['role'],
        $allowedRoles
    )) {
        $_SESSION['error'] =
            "Anda tidak memiliki hak akses.";
        header(
            "Location: ../dashboard/index.php"
        );
        exit;
    }
}
