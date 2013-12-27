<!doctype html>
<html>
<head>

</head>
<body>
<?php
include 'libs/vkposter.php';
$poster = new VKPoster('access_token');
$poster->setUserIDVK('');
$poster->setGroupIDVK('');

file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/img/photo.jpg', file_get_contents('http://www.presspixels.com/images/2012/01/TinyURL-Redirection.jpg'));
$img = $_SERVER['DOCUMENT_ROOT'] . '/img/photo.jpg';
$response = $poster->photosSaveWallPhoto($img, false);
$response = $poster->wallPost(array('owner_id' => 2, 'attachments' => $response->id));
print_r($response);
?>

</body>
</html>

