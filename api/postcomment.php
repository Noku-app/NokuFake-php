<?php

require "utils.php";
$_POST = json_decode(file_get_contents('php://input'), true);

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

$uid = $_POST['uid'];
$auth = $_POST[''];

$conn = getNokuDB();

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