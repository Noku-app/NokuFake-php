<?php

require "utils.php";
$conn = getNokuDB();

$data = json_decode(file_get_contents('php://input'), true);

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
$res = quick_select(['likes', 'dislikes'], 'memes', 'id', $data['id'], $conn, false);
if($res->num_rows == 0){
	echo getErrorJson(-102, "Invalid meme ID: '".$data['id']."'");
	exit();
}
$meme = $res->fetch_assoc();
$likes = json_decode($meme['likes'], true);
$dislikes = json_decode($meme['dislikes'], true);
$state = $data['state'];

if(($key = array_search($data['uid'], $likes)) !== false) {
	unset($likes[$key]);
}

if($state && !in_array($data['uid'], $dislikes)){
	$dislikes[] = $data['uid'];
}

if(!$state){
	if(($key = array_search($data['uid'], $dislikes)) !== false) {
		unset($dislikes[$key]);
	}
}

quick_update(['likes' => json_encode($likes), 'dislikes' => json_encode($dislikes)], 'memes', 'id', $data['id'], $conn, true);

echo getSuccessJson(['likes' => count($likes), 'dislikes' => count($dislikes), 'disliked' => $state]);
exit();
?>