
<?php

include('php-barcode.php');

$con=mysqli_connect("localhost","root","password","inventory");
if (mysqli_connect_errno($con)){
        echo "Failed to connect to MySQL";
}

$scale = 1;
$serial = getserials();
$lastserial = $serial[1];

echo "
<center>
This site generates a sheet of barcodes. If you save them to the database, it picks up where you left off. I'd recommend making sure it prints correctly before saving them.<br /><br />
";

echo " Last Serial: $lastserial <br >";



echo "

<form method='post' action='/barcode/generate.php?serial=$lastserial&size=$scale'>
<input type='checkbox' name='save' value = '1'>Save in database?</input>
<input type='submit' name='submit' value='submit' />
</form>
<p>
To generate a list starting at a specific number, navigate to:<br />
inventory/barcode/generate.php?serial=[serial you want -1]&size=1

<br />
<b> FIREFOX 90% 0.3 margin top 0.0 the rest

</center>
";
?>

