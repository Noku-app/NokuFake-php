<?php
require "config.php";
$_CDN = $GLOBALS['NokuCDN'];

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST");
header('Content-Type: application/json');

function view_errors(){
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

function page_setup($name = "main"){
  session_name($name);
  session_start();
}

function str_equal($str1, $str2){
  if(strcmp($str1, $str2) == 0){
    return true;
  } else {
    return false;
  }
}

function random_string($length, $charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"){
    $max = strlen($charset) - 1;
    $chars = str_split($charset);
    $ret = '';
    for($i = 0; $i < $length; $i++){
        $ret .= $chars[rand(0, $max)];
    }
    
    return $ret;
}

// Database Methods
function limit_select($fields, $table, $start, $length, $conn = null, $close_db = false) {
  if (is_null($conn)) {
    $conn = getNokuDB();
    $close_db = true;
  }

  $names = '';
  if(!is_array($fields)){
	  if(str_equal($fields, '*')){
		  $names = '*';
	  } else {
          $fields = array($fields);
	  }
  }
  if(str_equal($names, '')){
    foreach ($fields as $value) {
      $names .= "`$value`, ";
    }
    $names = substr($names, 0, strlen($names) - 2);
  }

  $query = "SELECT $names FROM $table ORDER BY id DESC LIMIT $start, $length";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->execute();

    $res = $stmt->get_result();

    $stmt->close();
  }
  if($close_db) $conn->close();

  return $res;
}

function cond_limit($fields, $table, $cond_id, $conn_var, $start, $length, $conn = null, $close_db = false) {
  if (is_null($conn)) {
    $conn = getNokuDB();
    $close_db = true;
  }

  $names = '';
  if(!is_array($fields)){
	  if(str_equal($fields, '*')){
		  $names = '*';
	  } else {
          $fields = array($fields);
	  }
  }
  if(str_equal($names, '')){
    foreach ($fields as $value) {
      $names .= "`$value`, ";
    }
    $names = substr($names, 0, strlen($names) - 2);
  }

  $query = "SELECT $names FROM $table WHERE $cond_id = ? LIMIT $start, $length";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $stmt->execute();

    $res = $stmt->get_result();

    $stmt->close();
  }
  if($close_db) $conn->close();

  return $res;
}

function quick_select($fields, $table, $cond_id, $conn_var, $conn = null, $close_db = false) {
  if (is_null($conn)) {
    $conn = getNokuDB();
    $close_db = true;
  }

  $names = '';
  if(!is_array($fields)){
	  if(str_equal($fields, '*')){
		  $names = '*';
	  } else {
          $fields = array($fields);
	  }
  }
  if(str_equal($names, '')){
    foreach ($fields as $value) {
      $names .= "`$value`, ";
    }
    $names = substr($names, 0, strlen($names) - 2);
  }

  $query = "SELECT $names FROM $table WHERE $cond_id = ?";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $stmt->execute();

    $res = $stmt->get_result();

    $stmt->close();
  }
  if($close_db) $conn->close();

  return $res;
}

function quick_select_desc($fields, $table, $cond_id, $conn_var, $conn = null, $close_db = false) {
  if (is_null($conn)) {
    $conn = getNokuDB();
    $close_db = true;
  }

  $names = '';
  if(!is_array($fields)){
	  if(str_equal($fields, '*')){
		  $names = '*';
	  } else {
          $fields = array($fields);
	  }
  }
  if(str_equal($names, '')){
    foreach ($fields as $value) {
      $names .= "`$value`, ";
    }
    $names = substr($names, 0, strlen($names) - 2);
  }

  $query = "SELECT $names FROM $table WHERE $cond_id = ? ORDER BY id DESC";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $stmt->execute();

    $res = $stmt->get_result();

    $stmt->close();
  }
  if($close_db) $conn->close();

  return $res;
}

function quick_delete($table, $cond_id, $conn_var, $conn = null, $close_db = false) {
  if (is_null($conn)) {
    $conn = getNokuDB();
    $close_db = true;
  }

  $ret = false;
  $query = "DELETE FROM $table WHERE $cond_id = ?";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $ret = $stmt->execute();

    $stmt->close();
  }
  if($close_db) $conn->close();

  return $ret;
}

function quick_update($fields, $table, $cond_id, $conn_var, $conn = null, $close_db = false){
  if(is_null($conn)){
    $conn = getNokuDB();
    $close_db = true;
  }

  $names = "";
  foreach ($fields as $col => $value){
    $names .= "`$col` = '$value', ";
  }
  $names = substr($names, 0, strlen($names) - 2);

  $query = "UPDATE $table SET $names WHERE $cond_id = ?";
  if(!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $res = $stmt->execute();
    $stmt->close();
  }
  if($close_db) $conn->close();

  return $res;
}

function quick_insert($fields, $table, $conn = null, $close_db = false){
  if(is_null($conn)){
    $conn = getNokuDB();
    $close_db = true;
  }

  $holder = "";
  $values = [];
  $names = "";
  $types = "";

  foreach ($fields as $col => $value){
    $values[] = $value;
    $names  .= "$col, ";
    $holder .= "?, ";
    $types  .= "s";
  }
  $names = substr($names, 0, strlen($names) - 2);
  $holder = substr($holder, 0, strlen($holder) - 2);

  $query = "INSERT INTO $table ($names) VALUES ($holder)";
  if(!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();
  }
  if($close_db) $conn->close();

  return true;
}

function quick_count($table, $cond_id, $conn_var, $conn = null, $close_db = false){
  if(is_null($conn)){
    $conn = getNokuDB();
    $close_db = true;
  }

  $query = "SELECT id FROM $table WHERE $cond_id = ?";
  if (!($stmt = $conn->prepare($query))) {
    echo $conn->error;
  } else {
    $stmt->bind_param("s", $conn_var);
    $stmt->execute();
    $res = $stmt->get_result();
	
    $stmt->close();
  }
  if($close_db) $conn->close();

  return ($res instanceof mysqli_result) ? $res->num_rows : '0';
}

function getNokuDB(){
  $_CDN = $GLOBALS['NokuCDN'];

  $USER = $_CDN['DB_USER'];
  $PASS = $_CDN['DB_PASS'];

  $HOST = $_CDN['DB_HOST'];
  $NAME = $_CDN['DB_NAME'];

  $conn = mysqli_connect($HOST, $USER, $PASS, $NAME);
  if(!$conn){
    die("Connection failure: ".mysqli_connect_error());
  }
  return $conn;
}

function validateToken($token, $uid){
	$conn = getNokuDB();
	
	$res = quick_select(['auth_token'], 'users', 'id', $uid, $conn, false);
	if($res->num_rows == 0) return false;
	$user = $res->fetch_assoc();
	
	$conn->close();
	return str_equal($user['auth_token'], $token);
}

function generateToken(){
	return random_bytes(128);
}

function getErrorJson($error, $message){
	$response = [];
	$response['error'] = true;
    $response['data'] = [
	    "reason" => $error,
	    "message" => $message
    ];
	
	return json_encode($response);
}

function getSuccessJson($data){
	$response = [];
	$response['error'] = false;
    $response['data'] = $data;
	
	return json_encode($response);
}

// Computed Vars
$useragent=$_SERVER['HTTP_USER_AGENT'];
$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));

?>