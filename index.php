<!doctype html>
<html>
<head>

</head>
<body>
<?php
//if(isset($_POST['key'])){
include 'libs/vkposter.php';
$poster = new VKPoster('98811a73d2665bf32f9cc057b1c95a9fb5236c25ae6014cbcc854fae7a4f57c31ecbd588d278871e0e144');
$poster->setUserIDVK('1428162');
$poster->setGroupIDVK('39218194');

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/img/photo.jpg', file_get_contents('http://www.presspixels.com/images/2012/01/TinyURL-Redirection.jpg'));
$img = $_SERVER['DOCUMENT_ROOT'] . '/img/photo.jpg';
$response = $poster->photosSaveWallPhoto($img, false);
$response = $poster->wallPost(array('owner_id' => 2, 'attachments' => $response->id));
print_r($response);
//}
?>

</body>
</html>

