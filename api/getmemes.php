<?php

require "utils.php";
$conn = getNokuDB();

$dat = json_decode(file_get_contents('php://input'), true);
$valid = false;
$message = "";

if(!isset($data['token']) || !isset($data['uid'])){
	echo getErrorJson(-420, "Invalid request.");
	exit;
}

$res = quick_select(['auth_token'], 'users', 'id', $data['uid'], $conn, false);
if($res->num_rows == 0) return false;
$user = $res->fetch_assoc();

$conn->close();
$valid = str_equal($user['auth_token'], $data['token']);

if(!$valid){
	echo getErrorJson(-69, "Invalid token.");
	exit;
}
/**/

$res = quick_select_desc(['id', 'hash', 'likes', 'dislikes', 'author', 'tags', 'categories'], 'memes', '1', '1', $conn, false);
$memes = [];
while($data = $res->fetch_assoc()){
	$likes = json_decode($data['likes'], true);
	$dislikes = json_decode($data['dislikes'], true);
	
	$data['likes'] = count($likes);
	$data['dislikes'] = count($dislikes);
	$data['liked'] = in_array($dat['uid'], $likes);
	$data['disliked'] = in_array($dat['uid'], $dislikes);
	$memes[] = $data;
}

echo getSuccessJson($memes);

?>