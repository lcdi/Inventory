<?php
require_once('includes/functions.php');
?>
<html>
<head>
<link rel=stylesheet type="text/css" href="style.css">
<title>Inventory - Login</title>

</head>
<body>
<br />

<br />

<div id="wrapper">

<div id="centered">
<h1>Login</h1>

<?php

if (isset($_POST['user'])) {
	$username = $_POST['user'];
	$password = $_POST['password'];
	if ($adldap->authenticate($username,$password)){
		createSession($username,$password);
		header( 'Location: http://inventory.lcdi/index.php' ) ;
	}else{
		echo '<b>Incorrect Credentials<br>
		<form name="input" action="login.php" method="post">
			Username: <input type="text" name="user"> <br>
			Password: <input type="password" name="password"><br>
			<input type="submit" value="Submit">
		</form>
		';
	}
}else{
	if (checkSession($_SESSION['Authenticated']) == False){
		echo '	<form name="input" action="login.php" method="post">
					Username: <input type="text" name="user"> <br>
					Password: <input type="password" name="password"><br>
					<input type="submit" value="Slubmit">
				</form>
		';
	}else{
		header( 'Location: http://inventory.lcdi/index.php' ) ;
	}
}
?>
</div>
<br />
<br />
<div style="margin:auto; text-align:center;">
</div>
</div>
</body>
</html>
