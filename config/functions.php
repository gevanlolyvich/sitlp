<?php

function base_url($path="")
{
    return BASE_URL.'/'.$path;
}

function sanitize($conn,$value)
{
    return mysqli_real_escape_string($conn,trim($value));
}

function createLog($conn,$user_id,$aktivitas,$tabel='',$record_id=0){
    $user_id = (int)$user_id;
    $aktivitas = mysqli_real_escape_string($conn,$aktivitas);
    $tabel = mysqli_real_escape_string($conn,$tabel);
    $record_id = (int)$record_id;
    mysqli_query($conn,"INSERT INTO activity_log(user_id,aktivitas,nama_tabel,record_id)
		VALUES($user_id,'$aktivitas','$tabel',$record_id)");
}

function hasRole($roles)
{
    if(!in_array($_SESSION['role'],$roles)){
	header("Location: ../auth/unauthorized.php");
	exit;
    }
}

function menuActive($folder)
{
    return strpos(
        $_SERVER['REQUEST_URI'],
        '/' . $folder . '/'
    ) !== false
    ? 'active'
    : '';
}

function logActivity($conn,$aktivitas,$tabel_referensi,$referensi_id = null){
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    $user_id = (int)$_SESSION['user_id'];
    $aktivitas = mysqli_real_escape_string($conn,$aktivitas);
    $tabel_referensi = mysqli_real_escape_string($conn,$tabel_referensi);
    $referensi_id =
	$referensi_id
	? (int)$referensi_id
	: "NULL";

    mysqli_query($conn,"INSERT INTO audit_log(user_id,aktivitas,tabel_referensi,referensi_id)
	VALUES('$user_id','$aktivitas','$tabel_referensi',$referensi_id)");
}
