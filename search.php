<?php
require_once('includes/functions.php');
require_once('includes/pickles.php');
if (checkSession($_SESSION['Authenticated']) == False){
	header( 'Location: http://inventory.lcdi/' ) ;
}
$searchcomplete = False; 
if (isset($_POST['search'])){
	$searchtype = mysqli_real_escape_string($con,$_POST['searchtype']);
	$searchterm = mysqli_real_escape_string($con,$_POST['search']);
	if ($searchterm != ""){
		if ($searchtype == "student"){
			if (is_numeric($searchterm)){
				$username = id2name($searchterm);
				$userid = $searchterm;
			}else{
				$userid = id2name($searchterm);
				$username = $searchterm;
			}
			
			$signedoutsql = "SELECT * FROM `Inout` WHERE (`DateIn`='0000-00-00 00:00:00' AND `UserIn` is NULL) AND (`StudentID`='$username' OR `StudentID`='$userid');";
			$allsql = "SELECT * FROM `Inout` WHERE (`DateIn`!='0000-00-00 00:00:00') AND (`StudentID`='$username' OR `StudentID`='$userid');";
			$result = mysqli_query($con, $signedoutsql);
			if (mysqli_num_rows($result) > 0){	
				$signedout = "<tr><th class='center-text'>Serial</th><th class='center-text'>Date Out</th><th class='center-text'>Signed Out By</th><th class='center-text'>Use</th></tr>";	
				while($row = $result->fetch_array()) {
					$ID	= $row['ID'];
					$serial = id2serial($ID);
					$DateOut = $row['DateOut'];
					$UserOut = $row['UserOut'];
					$Use = $row['Use'];
					if ($Use == ""){
						$Use = "Not Reported";
					}
					$signedout .= "<tr><td class='center-text'><a href='inventory.php?action=getItem&serialNum=$serial'>$serial</a></td><td class='center-text'>$DateOut</td><td class='center-text'>$UserOut</td><td class='center-text'>$Use</td></tr>";
				}		
			}
			
			$result = mysqli_query($con, $allsql);
			if (mysqli_num_rows($result) > 0){	
				$allitems = "<tr><th class='center-text'>Serial</th><th class='center-text'>Date Out</th><th class='center-text'>Date In</th><th class='center-text'>Issues Reported</th></tr>";	
				while($row = $result->fetch_array()) {
					$ID	= $row['ID'];
					$serial = id2serial($ID);
					$DateIn	= $row['DateIn'];
					$DateOut = $row['DateOut'];
					$Issues	= $row['Issues'];
					if ($Issues == ""){
						$Issues = "No Issues Reported";
					}
					$allitems .= "<tr><td class='center-text'><a href='inventory.php?action=getItem&serialNum=$serial'>$serial</a></td><td class='center-text'>$DateOut</td><td class='center-text'>$DateIn</td><td class='center-text'>$Issues</td></tr>";
				}
			}
			
			$searchcomplete = True;
		}
	}else{
		$searchcomplete = False;
	}
}
include('includes/header.php');

echo "
<div class='container-narrow'>
	<h2 class='center-text'>Search</h2>
	<div class='form-group center-text'>
		<div class='center-text'>
			<form name='search' action='search.php' method='post'>
				<input type='hidden' name='search' value='true'>
				<input type='text' name='search' placeholder='Search for something'> 
				<input type='submit' class='btn btn-primary btn-xs' value='Search'><br>
				<label class=radio-inline'><label class=radio-inline'><input type='radio' checked name='searchtype' id='searchtype' value='student'> Student </label>
			</form>
		</div>
	</div>
";
if ($searchcomplete){
	echo "<hr>";
	echo "<h3 class='center-text'>Results For: $username OR $userid</h3>";
	
	if (getPerms(checkSession($_SESSION['Authenticated'])) == "Admin"){
		
		$perms = getPerms($username);
		if ($perms == "None"){
			echo "<div class='center-text'><b>This user has no permissions</b>";
		}else{
			echo "<div class='center-text'><b>This user is a $perms</b>";
		}
		echo "</div>";
	}
	
	if (isset($signedout)){
		echo "<h4 class='center-text'>Items Currently Signed Out</h4>";
		echo "<table width='100%'>";
		echo $signedout;
		echo "</table>";
	}else{
		echo "<h5 class='center-text'>No items are currently signed out by this user</h5>";
	}
	if (isset($allitems)){
		echo "<h4 class='center-text'>All Previous Items</h4>";
		echo "<table width='100%'>";
		echo $allitems;
		echo "</table>";
	}
	
}
echo "</div>";

?>
		<div style="clear: both;" class='footer'>
		<hr>
			<p><center>&copy; Champlain College LCDI 2013</center></p>
		</div>

	</div> <!-- /container -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src='//code.jquery.com/jquery.js'></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src='js/bootstrap.min.js'></script>

	</body>

</html>
