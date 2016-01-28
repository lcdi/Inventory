<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link href="labels.css" rel="stylesheet" type="text/css" >
    <style>
    body {
        width: 8.5in;
        margin: 0in .1875in;
        }
    .label{
        /* Avery 5160 labels -- CSS and HTML by MM at Boulder Information Services */
        width: 2.025in; /* plus .6 inches from padding */
        /*height: .875in; /* plus .125 inches from padding */
	height: 1.0in;
        padding: .125in .3in 0;
        margin-right: .125in; /* the gutter */

        float: left;

        text-align: center;
        overflow: hidden;

       /* outline: 1px dotted;  outline doesn't occupy space like border does */
        }
    .page-break  {
        clear: left;
        display:block;
        page-break-after:always;
        }
    </style>

</head>
<body>


<?php

include('php-barcode.php');

$con=mysqli_connect("localhost","root","password","inventory");
if (mysqli_connect_errno($con)){
        echo "Failed to connect to MySQL";
}


$save = 0;

if(isset($_POST['save'])) {
	$save = 1;
}

$lastserial 	= $_GET['serial'];
$size 		= $_GET['size'];


$number = preg_replace("/[^0-9,.]/", "", $lastserial);
$number++;
for ($x=0; $x <30; $x++) {
	
	$number = str_pad($number, 5, '0', STR_PAD_LEFT);
	$code = "LCDI".$number;
	echo "
	<div class='label'><img src='http://inventory/barcode/barcode.php?code=$code&encoding=128B&scale=$size' /></div>
	";
	if ($save == 1) {
	addserial($code);
	}
	$number++;
}	
	?>
</body>
