<?php

require "utils.php";
$_POST = json_decode(file_get_contents('php://input'), true);

$response = [];

$conn = getNokuDB();
$res = quick_select(['id', 'password', 'auth_token'], 'users', 'username', $_POST['username'], $conn, false);
if($res->num_rows == 0){
	echo getErrorJson(-1, "Username or password incorrect.");
    exit();
}

$user = $res->fetch_assoc();
if(!password_verify($_POST['password'], $user['password'])){
    echo getErrorJson(-1, "Username or password incorrect.");
    exit();
}

if(str_equal('', $user['auth_token'])){
	$token = base64_encode(generateToken());
	$user['auth_token'] = $token;
	quick_update(['auth_token' => $token], 'users', 'id', $user['id']);
}

$response['error'] = false;
$response['data'] = [
	"message" => [
	    "token" => $user['auth_token'],
		"uid" => $user['id']
	]
];

echo json_encode($response);
exit();

?>