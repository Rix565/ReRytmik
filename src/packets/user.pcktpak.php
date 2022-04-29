<?php
/*
User packets pack

Handles user modification, login, registration, more
*/
function checkUser($db){
    $q = $db->prepare("SELECT * FROM `users` WHERE `passcode`=:passcode");
    $q->execute([
        "passcode" => $_POST['passkey']
    ]);
    if(!$q){
        // also known as crash
        exit;
    }
}
function getUser($db){
    $q = $db->prepare("SELECT * FROM `users` WHERE `passcode`=:passcode");
    $q->execute([
        "passcode" => $_POST['passkey']
    ]);
    return $q->fetch();
}
function loginPacket($db){
    $q = $db->prepare("SELECT * FROM `users` WHERE `principal`=:principal");
    $q->execute([
        "principal" => $_POST['principal']
    ]);
    $r = $q->fetch();
    if(!$r){
        $q = $db->prepare("INSERT INTO `users`(name, passcode, principal) VALUES(:name, :passcode, :principal)");
        $q->execute([
            "name" => "user".rand(1, 2147483647),
            "passcode" => rand(1, 2147483647),
            "principal" => $_POST['principal']
        ]);
    }
    $q = $db->prepare("SELECT * FROM `users` WHERE `principal`=:principal");
    $q->execute([
        "principal" => $_POST['principal']
    ]);
    $r = $q->fetch();
    return "=LOGIN;UID;TOKEN;<br />=MSG;txt message;<br />=PLAYLIST;song name;author;<br />PASSKEY;".$r['passcode'].";<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;".$_POST['principal'].";<br />AUTHOR;".$r['name'].";<br />CLOUD;1000;0;<br />END;0;<br />";
}
function setNamePacket($db){
    $q2 = $db->prepare("UPDATE `users` SET `name`=:name WHERE `passcode`=:passcode");
    $q2->execute([
        "name" => $_POST['author'],
        "passcode" => $_POST['passkey']
    ]);
    $r = getUser($db);
    return "=SETNAME;UID;author;<br />=SETNAMERESULT;value(0=ok,1=no change,2=wrong name,3=too short,4=UID not exist);<br />SETNAMERESULT;0;<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;0;<br />END;0;<br />";
}