<?php

require "utils.php";
$data = json_decode(file_get_contents('php://input'), true);
$valid = false;
$message = "";

if(!isset($data['token']) || !isset($data['uid'])){
	$valid = false;
	$message = "Something was null";
} else {
	$conn = getNokuDB();
	
	$res = quick_select(['auth_token'], 'users', 'id', $data['uid'], $conn, false);
	if($res->num_rows == 0) return false;
	$user = $res->fetch_assoc();
	
	$conn->close();
	$valid = str_equal($user['auth_token'], $data['token']);
	
	$message = $data['token'].'\n'.$user['auth_token'];
}

echo json_encode(["valid" => $valid, "message" => $message]);

?>