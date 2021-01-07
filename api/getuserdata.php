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
$res = quick_select(['username', 'pfp', 'bg', 'title', 'color', 'created'], 'users', 'id', $data['user_id'], $conn, false);
if($res->num_rows == 0){
	echo getErrorJson(404, "User not found.");
	exit;
}

echo getSuccessJson($res->fetch_assoc());


?>