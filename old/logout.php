<?php
require_once('includes/functions.php');

if (destroySession() == True){
	header( 'Location: http://inventory.lcdi/index.php') ;
}else{
	echo "Failed.";
}
?>

