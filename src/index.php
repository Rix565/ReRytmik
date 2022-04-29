<?php
require_once "inc/db.php";
require_once "packets/user.pcktpak.php";
require_once "packets/song.pcktpak.php";
ob_start();
$hd = apache_request_headers();
if(isset($hd['User-Agent'])){
    echo "Sorry bro, i'll make this fast. No one likes a computer that isn't a 3ds.";
    exit;
}
if($_POST['cmd']=="DOWNLOAD"){
    echo dlSongPacket($db);
}
if($_POST['cmd']=="LOGIN"){
    echo loginPacket($db);
}
if($_GET['cmd']=="UPLOADNOW"){
    echo uploadSongPacket($db);
}
checkUser($db);
if($_POST['cmd']=="SETNAME"){
    echo setNamePacket($db);
}
if($_POST['cmd']=="LIST"){
    $user = getUser($db);
    echo displayListPacket($db, $user);
}
if($_POST['cmd']=="PLIST"){
    $user = getUser($db);
    echo displayPersonalListPacket($db, $user);
}
if($_POST['cmd']=="UPLOAD"){
    $user = getUser($db);
    echo uploadPacket($db, $user);
}
if($_POST['cmd']=="DELETE"){
    $user = getUser($db);
    echo deleteSongPacket($db, $user);
}
header("Content-Length: ".ob_get_length());
ob_end_flush();
?>