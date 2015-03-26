<?php
require_once('includes/functions.php');
require_once('includes/pickles.php');
if (checkSession($_SESSION['Authenticated']) == False){
	header( 'Location: http://inventory.lcdi/' ) ;
}elseif (getPerms(checkSession($_SESSION['Authenticated'])) != "Admin"){
	header( 'Location: http://inventory.lcdi/' ) ;
}
include('includes/header.php');
?>
		<div class='container-narrow'>
			<div class='pull-left col-md-3'>
				<h2 class='center-text'>Quick Links</h2>
				<table class='table center-text'>
					<tr><td><a href="admin.php">Search<a/></td></tr>
					<tr><td><a href="admin.php?action=noid">Show Users Who Don't Have IDs<a/></td></tr>
					<tr><td><a href="admin.php?action=backup">Download Database<a/></td></tr>
				</table>
			</div>
			<div class='pull-right col-md-8'>
				<?php
					if (isset($_GET['action'])){
						if ($_GET['action'] == 'noid'){
							echo "<h3 class='center-text'>Users without IDs</h3>";
							echo "<p class='text-left'>";
							$usernames = $adldap->user()->all();
							$users = array();
							foreach ($usernames as $username) {
								$userinfo = $adldap->user()->infoCollection($username, array("displayname","employeeid"));
								$id = $userinfo->employeeid;
								if ($id == ""){
									echo "$username <br>";
								}
							}
							echo "</p>";
						}
						if ($_GET['action'] == 'backup'){
							echo "<h3 class='center-text'>Backup Database</h3>";
							echo "<p class='text-left'>";
							$toDay = date('d-m-Y');
							$dbuser = "root"; 
							$dbpass = "password"; 
							$dbhost = "localhost"; 
							$dbname   = "inventory";
							$file = $toDay."_DB.sql";
							exec("mysqldump --user=$dbuser --password='$dbpass' --host=$dbhost $dbname > ".$file);
							sleep(1);
							if (file_exists($file)){
								header('Content-Description: File Transfer');
								header('Content-Type: application/octet-stream');
								header('Content-Disposition: attachment; filename='.basename($file));
								header('Content-Transfer-Encoding: binary');
								header('Expires: 0');
								header('Cache-Control: must-revalidate');
								header('Pragma: public');
								header('Content-Length: ' . filesize($file));
								ob_clean();
								flush();
								readfile($file);
								exec("rm -f ".$file);
								exit;
							}
							
							echo "</p>";
						}
					}else{
						echo "<h2 class='center-text'>Search</h2>";
						echo "<div class='center-text'>";
						echo "<form name='search' action='admin.php' method='post'>";
						echo "<input type='hidden' name='search' value='true'>";
						echo "<input type='text' name='search' placeholder='Search for something'> ";
						echo "<input type='submit' class='btn btn-primary btn-xs' value='Search'><br>";
						echo "<label class=radio-inline'><label class=radio-inline'><input type='radio' checked name='searchtype' id='searchtype' value='student'> Student </label> <label class=radio-inline'><input type='radio' name='searchtype' id='searchtype' value='item'> Item </label>";
						echo "</form>";
						echo "</div>";
						if ($searchcomplete){
							echo "<hr>";
							echo "<h3 class='center-text'>Results For: $user OR $searchterm</h3>";
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
					}
				?>
			</div>
	</div>
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
