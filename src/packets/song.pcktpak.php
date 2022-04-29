<?php
/*
Song packets pack

Handles everything about songs
*/
function dlSongPacket($db){
    error_reporting(0);
    $q = $db->prepare("SELECT * FROM `songs` WHERE `id`=:id");
    $q->execute([
        "id" => $_POST['SID']
    ]);
    $r = $q->fetch();
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=cloudsong.ruf");
    return $r['songcode'];
}
//hell is here, bois
function displayListPacket($db, $r){
    $currentPage = $_POST["page"];
    $itemsPerPage = 13;
    $startItem = $currentPage * $itemsPerPage;
    if($currentPage==1){
        $startItem = 1;
    }
    $q2 = $db->prepare("SELECT * FROM `songs` WHERE `id` >= :id ORDER BY `id` DESC LIMIT 13");
    $q2->execute([
        "id" => $startItem,
    ]);
    $r2 = $q2->fetchAll();
    $lastItem = end($r2);
    $lastPage = $lastItem['id'] / 13;
    $lastPage = strtok($lastPage,'.');
    $q3 = $db->prepare("SELECT COUNT(*) AS nbr FROM `songs`");
    $q3->execute();
    $r3 = $q3->fetch();
    $songdat = "";
    foreach($r2 as $song){
        $qt = $db->prepare("SELECT UNIX_TIMESTAMP(:date)");
        $qt->execute([
            "date" => $song['date_created']
        ]);
        $tmstp = $qt->fetch();
        $tmstp = $tmstp[0];
        $date = date("y\;m\;d\;h\;mi\;s", $tmstp);
        $qu = $db->prepare("SELECT * FROM `users` WHERE `id`=:id");
        $qu->execute([
            "id" => $song['author_id']
        ]);
        $currentuser = $qu->fetch();
        $csongdat = "<br />ITEM;".$song['id'].";".$song['name'].";".$currentuser['name'].";".$date.";".$song['tag1'].";".$song['tag2'].";0;310;310;".$song['id'].";";
        $songdat = $songdat.$csongdat;
    }
    return "=CMD;LIST;page;listsize;sort;updn;ftag1;ftag2;fname;fauth;filtr(=NA12);<br />=ITEM;no;name;author;yy;mm;dd;hh;mi;ss;tag1;tag2;rights;ratingAvg;ratingAvgLow;songID;".$songdat."<br />PAGE;".$currentPage.";<br />PAGECOUNT;".$lastPage.";<br />TOTALITEMCOUNT;".$r3['nbr'].";<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;0;<br />END;0;<br />";
}
function displayPersonalListPacket($db, $r){
    $currentPage = $_POST["page"];
    $itemsPerPage = 13;
    $startItem = $currentPage * $itemsPerPage;
    if($currentPage==1){
        $startItem = 1;
    }
    $q2 = $db->prepare("SELECT * FROM `songs` WHERE `id` >= :id AND `author_id`=:author_id ORDER BY `id` DESC LIMIT 13");
    $q2->execute([
        "id" => $startItem,
        "author_id" => $r['id']
    ]);
    $r2 = $q2->fetchAll();
    $lastItem = end($r2);
    $lastPage = $lastItem['id'] / 13;
    $lastPage = strtok($lastPage,'.');
    $q3 = $db->prepare("SELECT COUNT(*) AS nbr FROM `songs`");
    $q3->execute();
    $r3 = $q3->fetch();
    $songdat = "";
    foreach($r2 as $song){
        $qt = $db->prepare("SELECT UNIX_TIMESTAMP(:date)");
        $qt->execute([
            "date" => $song['date_created']
        ]);
        $tmstp = $qt->fetch();
        $tmstp = $tmstp[0];
        $date = date("y\;m\;d\;h\;mi\;s", $tmstp);
        $qu = $db->prepare("SELECT * FROM `users` WHERE `id`=:id");
        $qu->execute([
            "id" => $song['author_id']
        ]);
        $currentuser = $qu->fetch();
        $csongdat = "<br />ITEM;".$song['id'].";".$song['name'].";".$currentuser['name'].";".$date.";".$song['tag1'].";".$song['tag2'].";0;310;310;".$song['id'].";";
        $songdat = $songdat.$csongdat;
    }
    return "=CMD;LIST;page;listsize;sort;updn;ftag1;ftag2;fname;fauth;filtr(=NA12);<br />=ITEM;no;name;author;yy;mm;dd;hh;mi;ss;tag1;tag2;rights;ratingAvg;ratingAvgLow;songID;".$songdat."<br />PAGE;".$currentPage.";<br />PAGECOUNT;".$lastPage.";<br />TOTALITEMCOUNT;".$r3['nbr'].";<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;0;<br />END;0;<br />";
}
function uploadPacket($db, $r){
    $q = $db->prepare("INSERT INTO `uploadtokens`(token, author, name, tag1, tag2) VALUES(:token, :author, :name, :tag1, :tag2)");
    $token = rand(1, 5000);
    $q->execute([
        "token" => $token,
        "author" => $r['id'],
        "name" => $_POST['name'],
        "tag1" => $_POST['tag1'],
        "tag2" => $_POST['tag2']
    ]);
    return "=CMD;UPLOAD;tag1,tag2,rights,SID(orig.),name,force(1=overwrite);<br />EXIST;0;<br />CLOUDFULL;0;<br />UPLOADREADY;1;<br />TOKEN;".$token.";<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;0;<br />END;0;<br />";
}
function uploadSongPacket($db, $r){
    $q = $db->prepare("SELECT * FROM `uploadtokens` WHERE `token`=:token");
    $q->execute([
        "token" => $_GET['token']
    ]);
    $r2 = $q->fetch();
    $q = $db->prepare("SELECT * FROM `users` WHERE `id`=:id");
    $q->execute([
        "id" => $r2['author']
    ]);
    $r = $q->fetch();
    $q = $db->prepare("DELETE FROM `uploadtokens` WHERE `token`=:token");
    $q->execute([
        "token" => $_GET['token']
    ]);
    $q = $db->prepare("INSERT INTO `songs`(name, author_id, songcode, tag1, tag2) VALUES(:name, :author_id, :songcode, :tag1,:tag2)");
    $q->execute([
        "name" => $r2['name'],
        "author_id" => $r['id'],
        "songcode" => file_get_contents("php://input"),
        "tag1" => $r2['tag1'],
        "tag2" => $r2['tag2']
    ]);
    $q = $db->prepare("SELECT * FROM `songs` ORDER BY `id` DESC LIMIT 1");
    $q->execute();
    $r2 = $q->fetch();
    return "=CMD;UPLOADNOW;token;size;<br />UPLOADED;1;<br />CLOUDID;".$r2['id'].";<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;1;<br />END;0;<br />";
}
function deleteSongPacket($db, $r){
    $q = $db->prepare("SELECT * FROM `songs` WHERE `id`=:id");
    $q->execute([
        "id" => $_POST['SID']
    ]);
    $r2 = $q->fetch();
    if($r2['author_id']!==$r['id']){
        exit;
    }
    $q = $db->prepare("ALTER TABLE `songs` auto_increment = ".$r2['id'].";");
    $q->execute();
    $q =$db->prepare("DELETE FROM `songs` WHERE `id`=:id");
    $q->execute([
        "id" => $_POST['SID']
    ]);
    return "=CMD;DELETE;SID;<br />DELETED;1;<br />=user info;<br />UID;".$r['id'].";<br />PRINCIPALID;;<br />AUTHOR;".$r['name'].";<br />CLOUD;40;0;<br />END;0;<br />";

}