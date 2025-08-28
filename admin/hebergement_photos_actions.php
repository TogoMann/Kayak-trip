<?php
require_once __DIR__ . '/_guard.php';
$action = $_POST['action'] ?? '';
if ($action === 'upload') {
    $hid = (int)($_POST['hebergement_id'] ?? 0);
    if ($hid>0 && isset($_FILES['photos'])) {
        $dir = dirname(__DIR__).'/uploads/hebergements';
        if (!is_dir($dir)) mkdir($dir,0777,true);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($_FILES['photos']['tmp_name'] as $i=>$tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $mime = $finfo->file($tmp);
            $ext = '';
            if ($mime==='image/jpeg') $ext='.jpg';
            elseif ($mime==='image/png') $ext='.png';
            elseif ($mime==='image/webp') $ext='.webp';
            else continue;
            $name = bin2hex(random_bytes(8)).$ext;
            $dest = $dir.'/'.$name;
            if (move_uploaded_file($tmp,$dest)) {
                $stmt=$pdo->prepare("INSERT INTO hebergement_photo (hebergement_id,chemin) VALUES (?,?)");
                $stmt->execute([$hid,$name]);
            }
        }
    }
    header('Location: /admin/hebergement_photos.php?id='.$hid); exit;
}
if ($action === 'set_cover') {
    $id = (int)($_POST['id'] ?? 0);
    $hid = (int)($_POST['hebergement_id'] ?? 0);
    if ($id>0 && $hid>0) {
        $pdo->prepare("UPDATE hebergement_photo SET is_cover=0 WHERE hebergement_id=?")->execute([$hid]);
        $pdo->prepare("UPDATE hebergement_photo SET is_cover=1 WHERE id=? AND hebergement_id=?")->execute([$id,$hid]);
    }
    header('Location: /admin/hebergement_photos.php?id='.$hid); exit;
}
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $hid = (int)($_POST['hebergement_id'] ?? 0);
    if ($id>0) {
        $q = $pdo->prepare("SELECT chemin FROM hebergement_photo WHERE id=?");
        $q->execute([$id]);
        $path = $q->fetchColumn();
        $pdo->prepare("DELETE FROM hebergement_photo WHERE id=?")->execute([$id]);
        if ($path) {
            $file = dirname(__DIR__).'/uploads/hebergements/'.$path;
            if (is_file($file)) @unlink($file);
        }
    }
    header('Location: /admin/hebergement_photos.php?id='.$hid); exit;
}
header('Location: /admin/hebergements.php');
