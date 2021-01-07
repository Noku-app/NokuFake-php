<?php

require "api/utils.php";
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
$CDN_API_KEY = $_CDN['AUTH_TOKEN'];

$uid = $data['uid'];
$token = $data['token'];

// First, get content upload request data

$upload = [];
$upload['uid'] = $uid;
$upload['file'] = $data['data'];
$upload['original'] = false;

$imgdata = base64_decode($upload['file']);
$f = finfo_open();
$upload['mime_type'] = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);

$options = array(
  'http' => array(
    'method'  => 'POST',
    'content' => json_encode($upload),
    'header'=>  "Content-Type: application/json\r\n".
                "Accept: application/json\r\n".
                "Authorization: ".$CDN_API_KEY."\r\n"
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($_CDN['CDN_ENDPOINT'], false, $context);
$response = json_decode($result, true);

$upload = null;

if($response == null){
	echo getErrorJson(-199, "CDN Error, contact Noku staff: ".json_encode($result));
	exit();
}

$hash = $response['data'];

$worked = quick_insert(['hash' => $hash, 'likes' => '[]', 'dislikes' => '[]', 'author' => $uid, 'tags' => json_encode([]), 'categories' => json_encode([])], 'memes', $conn, false);
if(!$worked){
	echo getErrorJson(-100, "An unknown error occured: ".$conn->error);
    exit();
}

$last_id = $conn->insert_id;

$response['error'] = false;
$response['data'] = [
	"id" => $last_id,
	"hash" => $hash
];
echo json_encode($response);
exit();
?>