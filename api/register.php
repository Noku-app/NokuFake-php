<?php

require "utils.php";
$_POST = json_decode(file_get_contents('php://input'), true);

$response = [];

$conn = getNokuDB();
$res = quick_select(['id'], 'users', 'username', $_POST['username'], $conn, false);
if($res->num_rows > 0){
	echo getErrorJson(-2, "Username already in use.");
    exit();
}

$res = quick_select(['id'], 'users', 'email', $_POST['email'], $conn, false);
if($res->num_rows > 0){
	echo getErrorJson(-3, "Email already in use.");
    exit();
}

if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\!\@\#\$\%\^\&\*])(?=.{12,})/', $_POST['password'])){
	echo getErrorJson(-4, "Password not strong enough.");
    exit();
}

$found = false;
$token = base64_encode(generateToken());
$hash = password_hash($_POST['password'], PASSWORD_ARGON2I);

$worked = quick_insert([
    'username' => $_POST['username'], 
    'password' => $hash,
    'email' => $_POST['email'],
    'auth_token' => $token,
    'subs' => json_encode([]),
    'subTo' => json_encode([])
], 'users', $conn, false);
echo $conn->error;

if(!$worked){
	echo getErrorJson(-100, "An unknown error occured: ".$conn->error);
    exit();
}

$last_id = $conn->insert_id;

$response['error'] = false;
$response['data'] = [
	"message" => [
	    "token" => $token,
		"uid" => $last_id
	]
];
echo json_encode($response);
exit();

?>