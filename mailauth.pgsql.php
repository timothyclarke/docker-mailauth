<?php
/*
NGINX sends headers as
Auth-User: somuser
Auth-Pass: somepass
On my php app server these are seen as
HTTP_AUTH_USER and HTTP_AUTH_PASS
*/
if (!isset($_SERVER["HTTP_AUTH_USER"] ) || !isset($_SERVER["HTTP_AUTH_PASS"] )){
  fail();
}

$username=$_SERVER["HTTP_AUTH_USER"] ;
$userpass=$_SERVER["HTTP_AUTH_PASS"] ;
$protocol=$_SERVER["HTTP_AUTH_PROTOCOL"] ;

class connectionParams {}
$sqlConf = new connectionParams;
include '/config/pgsql.inc';

$pgsqlConf='';
// use an iterator to concatenate a string to connect to PostgreSQL
foreach ($sqlConf as $key => $value) {
// concatenate the connect params with each iteration
$pgsqlConf = $pgsqlConf . $key . "=" . $value . " ";
}

// default backend port
$backend_port=110;

if ($protocol=="imap") {
  $backend_port=143;
}

if ($protocol=="smtp") {
  $backend_port=25;
}

// Authenticate the user or fail
if (!authuser($username,$userpass,$pgsqlConf)){
  fail();
  exit;
}


// Pass!
pass($mail_server_ip, $backend_port, $username);

//END

function authuser($user,$pass,$pgsqlConf){
  // password characters encoded by nginx:
  // " " 0x20h (SPACE)
  // "%" 0x25h
  // see nginx source: src/core/ngx_string.c:ngx_escape_uri(...)
  $pass = str_replace('%20',' ', $pass);
  $pass = str_replace('%25','%', $pass);

  $good = true;
  $result = false;

  $connected = "false";
  $conn = pg_connect($pgsqlConf) or $good=false;
  $connected = "true";

  if ($good) {
    $userQuery  = "{$user}";
    $stmt = pg_prepare($conn, "AuthQuery", "SELECT uid FROM users WHERE id=$1 AND md5=MD5($2) LIMIT 1");
    $result = pg_fetch_row(pg_execute($conn, "AuthQuery", array($user, $pass)));
  }
  if (!$result){
    $good = false;
  }
  // put your logic here to authen the user to any backend
  // you want (datbase, ldap, etc)
  // for example, we will just return true;
  //return true;
  return $good;
}

function fail(){
  header("Auth-Status: Invalid login or password");
  exit;
}

function pass($server,$port, $username){
  header("Auth-Status: OK");
  header("Auth-Server: $server");
  header("Auth-Port: $port");
  exit;
}

?>
