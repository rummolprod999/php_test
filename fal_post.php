<?php
include('../SETTING/setting.php');
ini_set("display_errors",1);
error_reporting(E_ALL);
$query = $pdo->prepare('
		SELECT MAX(fal.id) AS mxid
		FROM il_find_a_link_items AS fal');
$query->execute();
$result = $query->fetch(PDO::FETCH_LAZY);
$inc = $result['mxid'];
$arr_image = array();
for ($i=0; $i<=count($_FILES['uploadfile']['name']); $i++)
{

    $uploadfile = "../TEMP/".$_FILES['uploadfile']['name'][$i];
    move_uploaded_file($_FILES['uploadfile']['tmp_name'][$i], $uploadfile);
    // echo $i;
    $size=GetImageSize ($uploadfile);
    $src=ImageCreateFromJPEG ($uploadfile);
    $iw=$size[0];
    $ih=$size[1];
    $koe=$ih/197;
    $new_w=ceil ($iw/$koe);
    $dst=ImageCreateTrueColor ($new_w, 197);
    ImageCopyResampled ($dst, $src, 0, 0, 0, 0, $new_w, 197, $iw, $ih);
    $file_name = $inc."_".rand(0,10000).".jpg";
    $arr_image[] = $file_name;
    $NewFilPath = "../../images/".$file_name;
    ImageJPEG ($dst,$NewFilPath , 100);
    imagedestroy($src);
}
//$uploadfile = "../TEMP/".$_FILES['somename']['name'];
//move_uploaded_file($_FILES['somename']['tmp_name'], $uploadfile);
$difficult = $_POST["difficult"];
$ranswer = $_POST["ranswer"];
$info = $_POST["info"];


$query = $pdo->prepare('INSERT INTO il_find_a_link_items (difficult, fimg_url, simg_url, timg_url, 	ranswer) VALUES (:difficult, :fimg_url, :simg_url, timg_url, :ranswer)');
$query->bindParam(':difficult', $difficult);
$query->bindParam(':fimg_url', $arr_image[0]);
$query->bindParam(':simg_url', $arr_image[1]);
$query->bindParam(':timg_url', $arr_image[2]);
$query->bindParam(':ranswer', $ranswer);
$query->execute();

$pdo = NULL;
?>