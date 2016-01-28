<?php
//oyt functions here
require_once(dirname(__FILE__) . '/adLDAP/src/adLDAP.php');

try {
    $adldap = new adLDAP(array('base_dn'=>'DC=c3di,DC=local', 'account_suffix'=>'@c3di.local'));
}
catch (adLDAPException $e) {
    echo $e;
    exit();   
}

$con=mysqli_connect("localhost","root","password","inventory");
if (mysqli_connect_errno($con)){
	echo "Failed to connect to MySQL";
}

session_start();

//Setting Variables
$datetime = date( 'Y-m-d h:i:s', time() );
$ClientIP = $_SERVER['REMOTE_ADDR'];

function random($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

function getPerms($username){
	global $adldap;
	$user = $adldap->user()->infoCollection($username, array('*'));
	$groupArray = $adldap->user()->groups($username);
	if (in_array("Domain Admins", $groupArray)){
		return "Admin";
	}else if (in_array("Office Assistant", $groupArray)){
		return "Office Assistant";
	}else{
		return "None";
	}
}

function checkSession($SessionID){
	global $con;
	#Check if they even have a session
	if (isset($_SESSION['Authenticated'])){
		$SessionID = mysqli_real_escape_string($con,$_SESSION['Authenticated']);
		$query = "SELECT * FROM Sessions WHERE SessionID = '$SessionID'";
		#Check if their session exists in the database, if it doesn't return false, if it does return their username.
		if ($result =  $con->query($query)){
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$username = $row['UserName'];
			return $username;	
		}else{
			return False;
		}
	}else{
		return False;
	}
}
function destroySession(){
	global $con;
	if (isset($_SESSION['Authenticated'])){
		$SessionID = mysqli_real_escape_string($con,$_SESSION['Authenticated']);
		mysqli_query($con,"DELETE FROM Sessions WHERE SessionID='$SessionID'");	
		$_SESSION['Authenticated'] = "";
		return True;
	}
}
function createSession($username,$password){
	global $con;
	global $ClientIP;
	global $datetime;
	global $adldap;
	$username = mysqli_real_escape_string($con,$username);
	$token = random(15);
	$token = md5($username.$password.$token.$ClientIP);
	$SessionID = substr(md5($username.$datetime),0,15);
	$sql = "INSERT INTO `inventory`.`Sessions` (`ID`, `SessionID`, `UserName`, `IP`, `Token`, `Date`) VALUES (NULL, '$SessionID', '$username', '$ClientIP', '$token', '$datetime');";
	mysqli_query($con,$sql);
	$_SESSION['Authenticated'] = $SessionID;
	return $username;
}
?>
