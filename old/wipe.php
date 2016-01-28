<?php
require_once('includes/functions.php');
if (checkSession($_SESSION['Authenticated']) == False){
		header( 'Location: http://inventory.lcdi/login.php' ) ;
}
?>
<html>
<head>
<link rel=stylesheet type="text/css" href="style.css">
<title>Inventory</title>
<?php require_once('includes/pickles.php'); ?>

</head>
<body>
<div id="wrapper">

<div id="centered">
<h1>Inventory</h1>

<?php
	if (checkSession($_SESSION['Authenticated']) == True){
		$username = checkSession($_SESSION['Authenticated']);
		$userinfo = $adldap->user()->info($username, array("displayname"));
		$displayname = $userinfo[0]["displayname"][0];
		echo "Welcome back $displayname!<br>";		
		if ($adldap->user()->ingroup($username,"Domain Admins")){
			echo "You are an admin.<br>";
			echo "<a href='wipe.php'><b>Wipe Drives</b></a> - <a href='inventory.php'>Inventory</a> - <a href='admin.php'>Admin Stuff</a> - <a href='logout.php'>Logout</a> <br>
			";
		}else if($adldap->user()->ingroup($username,"Lab Monitors")){
			echo "You are a lab monitor.<br>";
			echo "<b><a href='wipe.php'>Wipe Drives</a></b> - <a href='inventory.php'>Inventory</a> - <a href='logout.php''>Logout</a> <br>
			";
		}else{
			//No perms but authenticated
		}
	}
?>
<br />
<br />

<form name="input" action="wipe.php" method="GET">
<input type="hidden" name="action" value="getItem">
S/N<input type="text" name="serialNum" autofocus><br>
<input type="submit">
</form>

</div>
<?php
if (isset($_GET['serialNum'])) {
	$serialnum = mysql_real_escape_string($_GET['serialNum']);
	getItem($serialnum);
	echo "<br />
		<br />
		<span id='centered'>
		<div id ='box'>
		<table class='list'> $items </table>
		</div>
		</span>
	";
}

?>


<div style="margin:auto; text-align:center;">
</div>
</div>
</body>
</html>
