<?php
require_once('includes/functions.php');
require_once('includes/pickles.php');
if (checkSession($_SESSION['Authenticated']) == False){
	header( 'Location: http://inventory.lcdi/' ) ;
}

if (isset($_POST['inout'])){
	if ((isset($_POST['SerialNumber'])) && ($_POST['inout'] == 'out')){
			$SerialNumber = mysql_real_escape_string($_POST['SerialNumber']);
			$StudentID = mysql_real_escape_string($_POST['StudentID']);
			if (!is_numeric($StudentID)){
				$id = id2name($StudentID);
				if ($id != "Failed"){
					$StudentID = $id;
				}
			}
			$DateOut = $_POST['Date'];
			$Use = mysql_real_escape_string($_POST['Use']);
			$id = mysql_real_escape_string($_POST['id']);
			$username = checkSession($_SESSION['Authenticated']);
			$sql = "INSERT INTO `Inout` (`ID`,`StudentID`,`Use`,`DateOut`, `UserOut`) VALUES('$id', '$StudentID', '$Use', '$DateOut', '$username');";
			if (!mysqli_query($con,$sql)) {
				die('Error: ' . mysqli_error($con));
			}else{
				header( "Location: http://inventory.lcdi/inventory.php?action=getItem&serialNum=$SerialNumber" ) ;
			}
	}elseif ((isset($_POST['SerialNumber'])) && ($_POST['inout'] == 'in')){
			$SerialNumber = mysql_real_escape_string($_POST['SerialNumber']);
			$StudentID = mysql_real_escape_string($_POST['StudentID']);
			
			if (!is_numeric($StudentID)){
				$id = id2name($StudentID);
				if ($id != "Failed"){
					$StudentID = $id;
				}
			}
			
			$DateIn = mysql_real_escape_string($_POST['Date']);
			$Issues = mysql_real_escape_string($_POST['Issues']);
			$id = mysql_real_escape_string($_POST['id']);
			$username = checkSession($_SESSION['Authenticated']);
			$sql = "UPDATE  `Inout` SET  `DateIn` =  '$DateIn', `UserIn` = '$username', `Issues` = '$Issues' WHERE  `Inout`.`ID` =$id AND  `Inout`.`DateIn` =  '0000-00-00 00:00:00' LIMIT 1";
			if (!mysqli_query($con,$sql)){
				die('Error: ' . mysqli_error($con));
			}else{
				header( "Location: http://inventory.lcdi/inventory.php?action=getItem&serialNum=$SerialNumber" ) ;
			}
	}
}
if (isset($_GET['action']) && $_GET['action'] == 'wipe'){
	$wipedstatus = 0;
	if (isset($_GET['serialNum'])){
		$serial = mysql_real_escape_string($_GET['serialNum']);
		if (getItem($serial) != "Invalid Serial"){
			$id = $item[0];
			$username = checkSession($_SESSION['Authenticated']);
			$sql = "INSERT INTO `Wiped` (`ID`, `DeviceID`, `UserName`,`Date`) VALUES('NULL', '$id', '$username', '$datetime');";
			if (!mysqli_query($con,$sql)) {
				$wipedstatus = 0;
				die('Error: ' . mysqli_error($con));
			}else{
				$wipedstatus = 1;
			}
		}
	}
}

include('includes/header.php'); 
?>
		<div class='container-narrow'>
<?php
if (isset($_GET['action'])){
	if ($_GET['action'] == 'viewall'){
		echo "<h4>Filters:</h4>";
		echo "<form class='form' name='input' action='inventory.php?action=viewall' method='GET'>";
		echo "<input type='hidden' name='action' value='viewall'>";
		echo "<input type='hidden' name='filter' value='yes'>";
		echo "<table class='table table-condensed'><tr>";
		echo "<th width='1%'>Item Type:</th><th width='1%'>Current State</th><th >Signed In/Out</th></tr>";
		
		echo "<tr><td><select style='width: 150px' multiple name='Type'> ";
		$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
		$result = mysqli_query($con, $query);
		while($row = $result->fetch_array()) {
			$type	= $row['Type'];	
			echo "<option value='$type'>$type</option>";
		}
		echo "</select></td>  ";
		
		echo "<td><select style='width: 150px' multiple name='State'> ";
		$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
		$result = mysqli_query($con, $query);
		while($row = $result->fetch_array()) {
			$state	= $row['State'];	
			echo "<option value='$state'>$state</option>";
		}
		echo "</select>";
		echo "</td>";
		
		echo "<td><select style='width: 100px' multiple name='SignedIn'> ";
			echo "<option value='In'>In</option>
				<option value='Out'>Out</option>
			";
		echo "</select>
		
		</td></tr></table>";
		echo "<input type='submit' value='Filter'>";
		echo "</form>";
		if (isset($_GET['filter'])){
			$query  = explode('&', $_SERVER['QUERY_STRING']);
			$params = array();

			foreach( $query as $param )
			{
			  list($name, $value) = explode('=', $param);
			  $params[urldecode($name)][] = urldecode($value);
			}
			
			if (isset($_GET['Type'])){
				$type = implode(',',$params['Type']);
			}else{
				$type = "%";
			}
			
			if (isset($_GET['State'])){
				$state = implode(',',$params['State']);
			}else{
				$state = "%";
			}
			
			if (isset($_GET['SignedIn'])){
				$SignedIn = implode(',',$params['SignedIn']);
			}else{
				$SignedIn = "%";
			}
			viewall($type, $state, $SignedIn, 'SerialNumber ASC');
		}else{
			viewall();
		}
		echo "<br>
			<table class='table'>
				$items
			</table>
			";
	
	//WIPE SECTION
	}elseif ($_GET['action'] == 'wipe'){
		if (isset($_GET['serialNum'])){
			$serial = mysql_real_escape_string($_GET['serialNum']);
			if (getItem($serial) != "Invalid Serial"){
				echo "
					<div class='form-group center-text'>
						<center>
							<form class='form' name='input' action='inventory.php' method='GET'>
								<input type='hidden' name='action' value='wipe'> 
								<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control center-text form-small' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Wipe'> 
							</form>
						</center>
					</div>
				";
			
				echo "
					<hr>
					<div class='center-text'>	
						<h2>$item[1]</h2>
					</div>
					";
				if ($wipedstatus == 1){
					echo '<div class="alert alert-success">Drive marked as wiped!</div>';
				}else{
					echo '<div class="alert alert-danger">Something Bad Happened</div>';
				}
				echo "
					<br>
					<div id='box' class='col-md-3 pull-left center-text'>
						
						<img src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' width='200px' height='200px'> <br>
						<label>Type:</label> $item[2] <br>
						<label>State: </label> $item[6] <br>
						<label>Description:</label> <br>
						$item[3]<br>
						<label>Notes:</label> <br>
						$item[4]<br>
					</div>
					
				<div class='col-md-9 pull-right'>
					<div>
						<div class='center-text'>
							";
							$history = wipe($item[0], 5);
								echo "
								<table class='table table-striped table-hover' width='100%'>
								<tr><th class='text-center'>User Name</th><th class='text-center'>Date Wiped</th></tr>
								";
								foreach($history as $entry){
									echo "<tr><td>$entry[1]</td><td>$entry[2]</td>";
								}
							echo "
								</table>
							</div>
						</div>";
			}else{
				echo '<div class="alert alert-danger">Invalid Serial Number</div>';
				echo "
					<div class='form-group center-text'>
						<center>
							<form class='form' name='input' action='inventory.php' method='GET'>
								<input type='hidden' name='action' value='wipe'> 
								<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control center-text form-small' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Wipe'> 
							</form>
						</center>
					</div>
				";
				
			}
		}else{
			echo "
			<div class='form-group center-text'>
				<center>
					<form class='form' name='input' action='inventory.php' method='GET'>
						<input type='hidden' name='action' value='wipe'> 
						<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control center-text form-small' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Wipe'> 
					</form>
				</center>
			</div>
			";
		}
	//END OF WIPE SECTION
	
	//ADD ITEM SECTION
	}elseif ($_GET['action'] == 'additem'){
		if ((isset($_POST['SerialNumber'])) && ($_POST['SerialNumber'] != '')){
			$SerialNumber = $_POST['SerialNumber'];
			$DevSerialNumber = $_POST['DevSerialNumber'];
			$Type = $_POST['Type'];
			if ($Type == 'other'){
				$Type = $_POST['otherType'];
			}
			$Desc = $_POST['Description'];
			$Issues = $_POST['Issues'];
			$State = $_POST['State'];
			if ($State == 'other'){
				$State = $_POST['otherState'];
			}
			if ((isset($_FILES['file']['name'])) && ($_FILES['file']['name'] != "")){
				$Photo = $_FILES['file']['name'];
				uploadPicture();
			}else{
				$Photo = "None.gif";
				$uploadstatus = "Success";
			}
			if (!checkSerial($SerialNumber)){
				if ($uploadstatus != "Failed"){
					$sql = "INSERT INTO `inventory`.`Inventory` (`ID`, `SerialNumber`, `DeviceSerial`, `Type`, `Description`, `Issues`, `PhotoName`, `State`) VALUES (NULL, '$SerialNumber', '$DevSerialNumber', '$Type', '$Desc', '$Issues', '$Photo', '$State');";
					if (!mysqli_query($con,$sql)) {
						die('Error: ' . mysqli_error($con));
					}else{
						echo '<div class="alert alert-success center-text">Item Added Successfully! <br> <a href="inventory.php?action=additem">Add Another Item</a></div> <br>';
					}
				}else{
					echo '<div class="alert alert-danger center-text">Invalid File.</div>';
					echo "
					
						<h2 class='center-text'>Add an Item</h2><br>
						<form name='additem' action='inventory.php?action=additem' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>LCDI Serial Number:</label> 
							<div class='col-lg-9'>	
								<input name='SerialNumber' value='$SerialNumber' autofocus>
							</div>
						</div>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>Device Serial Number:</label> 
								<div class='col-lg-9'>	
									<input name='DevSerialNumber' value='$DevSerialNumber'>
								</div>
						</div>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>Type: </label> 
							<div class='col-lg-9'>
							<select name='Type'> ";
					$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
					$result = mysqli_query($con, $query);
					while($row = $result->fetch_array()) {
						$type	= $row['Type'];	
						echo "<option value='$type'>$type</option>";
					}
					echo "
							<option value='other'>Other</option>
							</select>
							If Other: <input name='otherType' type='text' />
							</div>
						</div>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>Device Description: </label>
							<div class='col-lg-9'>
								<textarea name='Description' cols='30' class='form-control form-med input-sm' rows='4'>$Desc</textarea>
							</div>
						</div>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>Notes:</label>
							<div class='col-lg-9'>
								<textarea name='Issues' cols='30' class='form-control form-med input-sm' rows='5'>$Issues</textarea>
							</div>
						</div>
						
						<div class='form-group'>
							<label class='col-lg-3 control-label'>State: </label> 
							<div class='col-lg-9'>
							<select name='State'> ";
					$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
					$result = mysqli_query($con, $query);
					while($row = $result->fetch_array()) {
						$state	= $row['State'];	
						echo "<option value='$state'>$state</option>";
					}
					echo "
						<option value='other'>Other</option>
						</select>
						
						If Other: <input name='otherState' type='text' />
							</div>
						</div>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>Photo: </label>
							<div class='col-lg-9'>
								<input type='file' name='file' id='file'>
							</div>
						
						</div>
						<div class='form-group col-lg-9 center-text'>
							<input type='submit' class='btn btn-primary' value='Add Item'>
						</div>
						</form>
						</div>
					";
				}
			}else{
				echo '<div class="alert alert-danger center-text">Invalid Informations</div>';
				echo "
				
					<h2 class='center-text'>Add an Item</h2><br>
					<form name='additem' action='inventory.php?action=additem' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>LCDI Serial Number:</label> 
						<div class='col-lg-9'>	
							<input name='SerialNumber' value='$SerialNumber' autofocus>
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Device Serial Number:</label> 
							<div class='col-lg-9'>	
								<input name='DevSerialNumber' value='$DevSerialNumber'>
							</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Type: </label> 
						<div class='col-lg-9'>
						<select name='Type'> ";
				$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
				$result = mysqli_query($con, $query);
				while($row = $result->fetch_array()) {
					$type	= $row['Type'];	
					echo "<option value='$type'>$type</option>";
				}
				echo "
						<option value='other'>Other</option>
						</select>
						If Other: <input name='otherType' type='text' />
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Device Description: </label>
						<div class='col-lg-9'>
							<textarea name='Description' cols='30' class='form-control form-med input-sm' rows='4'>$Desc</textarea>
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Notes:</label>
						<div class='col-lg-9'>
							<textarea name='Issues' cols='30' class='form-control form-med input-sm' rows='5'>$Issues</textarea>
						</div>
					</div>
					
					<div class='form-group'>
						<label class='col-lg-3 control-label'>State: </label> 
						<div class='col-lg-9'>
						<select name='State'> ";
				$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
				$result = mysqli_query($con, $query);
				while($row = $result->fetch_array()) {
					$state	= $row['State'];	
					echo "<option value='$state'>$state</option>";
				}
				echo "
					<option value='other'>Other</option>
					</select>
					
					If Other: <input name='otherState' type='text' />
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Photo: </label>
						<div class='col-lg-9'>
							<input type='file' name='file' id='file'>
						</div>
					
					</div>
					<div class='form-group col-lg-9 center-text'>
						<input type='submit' class='btn btn-primary' value='Add Item'>
					</div>
					</form>
					</div>
				";
			}
		}else{
			echo "
				
					<h2 class='center-text'>Add an Item</h2><br>
					<form name='additem' action='inventory.php?action=additem' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>LCDI Serial Number:</label> 
						<div class='col-lg-9'>	
							<input name='SerialNumber' placeholder='LCDI-' autofocus>
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Device Serial Number:</label> 
							<div class='col-lg-9'>	
								<input name='DevSerialNumber'>
							</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Type: </label> 
						<div class='col-lg-9'>
						<select name='Type'> ";
				$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
				$result = mysqli_query($con, $query);
				while($row = $result->fetch_array()) {
					$type	= $row['Type'];	
					echo "<option value='$type'>$type</option>";
				}
			echo "
						<option value='other'>Other</option>
						</select>
						If Other: <input name='otherType' type='text' />
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Device Description: </label>
						<div class='col-lg-9'>
							<textarea name='Description' cols='30' class='form-control form-med input-sm' rows='4'></textarea>
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Notes:</label>
						<div class='col-lg-9'>
							<textarea name='Issues' cols='30' class='form-control form-med input-sm' rows='5'></textarea>
						</div>
					</div>
					
					<div class='form-group'>
						<label class='col-lg-3 control-label'>State: </label> 
						<div class='col-lg-9'>
						<select name='State'> ";
				$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
				$result = mysqli_query($con, $query);
				while($row = $result->fetch_array()) {
					$state	= $row['State'];	
					echo "<option value='$state'>$state</option>";
				}
			echo "
					<option value='other'>Other</option>
					</select>
					
					If Other: <input name='otherState' type='text' />
						</div>
					</div>
					<div class='form-group'>
						<label class='col-lg-3 control-label'>Photo: </label>
						<div class='col-lg-9'>
							<input type='file' name='file' id='file'>
						</div>
					
					</div>
					<div class='form-group col-lg-9 center-text'>
						<input type='submit' class='btn btn-primary' value='Add Item'>
					</div>
					</form>
				</div>
			";
		}
	//END OF ADD ITEM SECTION
	
	//EDIT ITEM SECTION
	}elseif ($_GET['action'] == 'edititem'){
		if ((isset($_GET['serialNum'])) && ($_GET['serialNum'] != '')){
			if (isset($_POST['update'])){
				$serialnum = mysql_real_escape_string($_GET['serialNum']);
				if (getItem($serialnum) != "Invalid Serial"){
				
					
					$sql = "UPDATE `Inventory` SET ";
					
					$SerialNumber = $_POST['SerialNumber'];
					if ($item[1] != $SerialNumber){
						$sql .= "`SerialNumber` =  '$SerialNumber', ";
					}
					
					$DevSerialNumber = $_POST['DevSerialNumber'];
					if ($item[9] != $DevSerialNumber){
						$sql .= "`DeviceSerial` =  '$DevSerialNumber', ";
					}
					
					$Type = $_POST['Type'];
					if ($item[2] != $Type){
						if ($Type == 'other'){
							$Type = $_POST['otherType'];
						}
						$sql .= "`Type` =  '$Type', ";
					}
					
					$Desc = $_POST['Description'];
					if ($item[3] != $Desc){
						$sql .= "`Description` =  '$Desc', ";
					}
					
					$Issues = $_POST['Issues'];
					if ($item[4] != $Issues){
						$sql .= "`Issues` =  '$Issues', ";
					}
					
					$State = $_POST['State'];
					if ($item[6] != $State){
						if ($State == 'other'){
							$State = $_POST['otherState'];
						}
						$sql .= "`State` =  '$State', ";
					}
					
					if ((isset($_FILES['file']['name'])) && ($_FILES['file']['name'] != "")){
						$Type = $_POST['Type'];
						if ($Type == 'other'){
							$Type = $_POST['otherType'];
						}
						$Photo = $_FILES['file']['name'];
						uploadPicture();
						$sql .= "`PhotoName` =  '$Photo', ";
					}else{
						$uploadstatus = "Success";
					}
				
					if ($sql == "UPDATE `Inventory` SET "){
						echo '<div class="alert alert-warning center-text">No changes have been made.</div>';
					}else{
						$sql = rtrim($sql,', ');
						$sql .= " WHERE `ID` = '$item[0]';";
						if ($uploadstatus != "Failed"){
							if (!mysqli_query($con,$sql)) {
								echo '<div class="alert alert-danger center-text">Error: ' . mysqli_error($con) . "</div><br>$sql";
							}else{
								echo '<div class="alert alert-success center-text">Item has been updated.</div>';
							}
							
						}else{
							echo '<div class="alert alert-danger center-text">Changes failed.</div>';
						}
					}
					getItem($serialnum);
						if ($item[5] != "None.gif"){
							echo "
							<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
							  <div class='modal-dialog'>
								<div class='modal-content'>
								  <div class='modal-body'>
									<img class='responsive-image' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' >
								  </div>
								  <div class='modal-footer'>
									<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
								  </div>
								</div><!-- /.modal-content -->
							  </div><!-- /.modal-dialog -->
							</div><!-- /.modal -->
							";
						}
					echo "		
						<div class='form-group'>
						<center>
							<form class='form' name='input' action='inventory.php' method='GET'>
								<input type='hidden' name='action' value='edititem'> 
								<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
							</form>
						</center>
						</div>
					";
					echo "
						<hr>
						<div class='center-text'>	
						<form name='editItem' action='inventory.php?action=edititem&serialNum=$serialnum' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>

							<h2>Editing <input name='SerialNumber' type='text' value='$item[1]'></h2>
						</div>
						<br>
						
						";
					echo "
						<div id='box' class='col-md-3 pull-left center-text'>
						
						<br><br>";
						if ($item[5] == "None.gif"){
							echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos"; echo "/$item[5]' width='200px' height='200px'></a> <br>";
						}else{
							echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' width='200px' height='200px'></a> <br>";
						}
						echo "
							<label>Change Photo:</label>
								
									<input type='file' name='file' id='file'>
									<input type='hidden' name='photoname' value='$item[5]'>
								<br>
						</div>
						<div class='col-md-8 pull-right'>
							<div>
								<div class='center-text'>
									This item is currently: ";
									$history = inOut($item[0], 2);
									$firstitem = $history[0];
									if ($history[0][6] == False){
										echo "<b>available</b>";
									}else{
										//The item is signed out.
										
										echo "<b>In Use</b> by <b>$firstitem[1]</b><br />";
									}
						

							echo "
									<br>	
									Last Wiped By: <b>$item[7]</b> on <b>$item[8]</b>
								</div>
							</div>
							<div class='center-text'>		
								<hr>
								<label>Device Serial Number: </label> <input type='text' name='DevSerialNumber' value='$item[9]'><br>
								<label>Type: </label><select name='Type'> 
								<option value='$item[2]' selected>$item[2]</option>";
								$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$type	= $row['Type'];
									if ($type != $item[2]){
										echo "<option value='$type'>$type</option>";
									}
								}
								echo "
								<option value='other'>Other</option>
								</select> 
								If Other: <input name='otherType' type='text' /><br>
								
								<label>State: </label>
									<select name='State'> 
									<option value='$item[6]' selected>$item[6]</option>";
								$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$state	= $row['State'];	
									if ($state != $item[6]){
										echo "<option value='$state'>$state</option>";
									}
								}
								echo "
									<option value='other'>Other</option>
									</select> 
									If Other: <input name='otherType' type='text' /><br>
								
								<br><label>Description:</label> <br>
								<textarea name='Description' cols='30' rows='4'>$item[3]</textarea><br>
								<label>Notes:</label> <br>
								<textarea name='Issues' cols='30' rows='4'>$item[4]</textarea><br>
							</div>							
						</div>
						<div style='clear: both;' class='center-text'>
						<br>
							<input type='submit' class='btn btn-danger' value='Submit Changes'>
						</div>
						<input type='hidden' name='update' value='true'>
						</form>
						";
				}
			}else{
				$serialnum = mysql_real_escape_string($_GET['serialNum']);
				if (getItem($serialnum) != "Invalid Serial"){
					if ($item[5] != "None.gif"){
						echo "
						<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
						  <div class='modal-dialog'>
							<div class='modal-content'>
							  <div class='modal-body'>
								<img class='responsive-image' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' >
							  </div>
							  <div class='modal-footer'>
								<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
							  </div>
							</div><!-- /.modal-content -->
						  </div><!-- /.modal-dialog -->
						</div><!-- /.modal -->
						";
					}
					echo "		
						<div class='form-group'>
						<center>
							<form class='form' name='input' action='inventory.php' method='GET'>
								<input type='hidden'name='action' value='edititem'> 
								<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
							</form>
						</center>
						</div>
					";
					echo "
						<hr>
						<div class='center-text'>	
						<form name='editItem' action='inventory.php?action=edititem&serialNum=$serialnum' method='post' enctype='multipart/form-data'>

							<h2>Editing <input name='SerialNumber' type='text' value='$item[1]'></h2>
						</div>
						<br>
						
						";
					echo "
						<div id='box' class='col-md-3 pull-left center-text'>
						
						<br><br>";
						if ($item[5] == "None.gif"){
							echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos"; echo "/$item[5]' width='270px' height='270px'></a> <br>";
						}else{
							echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' width='200px' height='200px'></a> <br>";
						}
						echo "
							<label>Change Photo:</label>
								
									<input type='file' name='file' id='file'>
									<input type='hidden' name='photoname' value='$item[5]'>
								<br>
						</div>
						<div class='col-md-8 pull-right'>
							<div>
								<div class='center-text'>
									This item is currently: ";
									$history = inOut($item[0], 2);
									$firstitem = $history[0];
									if ($history[0][6] == False){
										echo "<b>available</b>";
									}else{
										//The item is signed out.
										
										echo "<b>Signed Out</b> by <b>$firstitem[1]</b>";						
									}
						

							echo "
									<br>	
									Last Wiped By: <b>$item[7]</b> on <b>$item[8]</b>
								</div>
							</div>
							<div class='center-text'>		
								<hr>
								<label>Device Serial Number: </label> <input type='text' name='DevSerialNumber' value='$item[9]'><br>
								<label>Type: </label><select name='Type'> 
								<option value='$item[2]' selected>$item[2]</option>";
								$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$type	= $row['Type'];
									if ($type != $item[2]){
										echo "<option value='$type'>$type</option>";
									}
								}
								echo "
								<option value='other'>Other</option>
								</select> 
								If Other: <input name='otherType' type='text' /><br>
								
								<label>State: </label>
									<select name='State'> 
									<option value='$item[6]' selected>$item[6]</option>";
								$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$state	= $row['State'];	
									if ($state != $item[6]){
										echo "<option value='$state'>$state</option>";
									}
								}
								echo "
									<option value='other'>Other</option>
									</select> 
									If Other: <input name='otherType' type='text' /><br>
								
								<br><label>Description:</label> <br>
								<textarea name='Description' cols='30' rows='4'>$item[3]</textarea><br>
								<label>Notes:</label> <br>
								<textarea name='Issues' cols='30' rows='4'>$item[4]</textarea><br>
							</div>							
						</div>
						<div style='clear: both;' class='center-text'>
						<br>
							<input type='submit' class='btn btn-danger' value='Submit Changes'>
						</div>
						<input type='hidden' name='update' value='true'>
						</form>
						";
				}
			}
		}else{
			echo "		
				<div class='form-group'>
				<center>
					<form class='form' name='input' action='inventory.php' method='GET'>
						<input type='hidden'name='action' value='edititem'> 
						<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
					</form>
				</center>
				</div>
			";
		}
	//END OF EDIT ITEM SECTION
	
	
	//ITEM LOOK UP SECTION
	} elseif (isset($_GET['serialNum'])){
		$serialnum = mysql_real_escape_string($_GET['serialNum']);
		if (getItem($serialnum) != "Invalid Serial"){	
			if ($item[5] != "None.gif"){
				echo "
				<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
				  <div class='modal-dialog'>
					<div class='modal-content'>
					  <div class='modal-body'>
						<img class='responsive-image' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' >
					  </div>
					  <div class='modal-footer'>
						<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
					  </div>
					</div><!-- /.modal-content -->
				  </div><!-- /.modal-dialog -->
				</div><!-- /.modal -->
				";
			}
			echo "		
				<div class='form-group'>
				<center>
					<form class='form' name='input' action='inventory.php' method='GET'>
						<input type='hidden'name='action' value='getItem'> 
						<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
					</form>
				</center>
				</div>
			";
			echo "
				<hr>
				<div class='center-text'>	
					<h2>$item[1] ";
					if (getPerms(checkSession($_SESSION['Authenticated'])) == "Admin"){
						echo "<span class='text-muted'><a href='inventory.php?action=edititem&serialNum=$item[1]'><span class='glyphicon glyphicon-pencil'> </span></a></span>";
					}
			echo "
					</h2>
				</div>
				<br>
				<div id='box' class='col-md-3 pull-left center-text'>
				";
				if ($item[5] == "None.gif"){
					echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos"; echo "/$item[5]' width='200px' height='200px'></a> <br>";
				}else{
					echo "<a data-toggle='modal' data-target='#myModal'><img class='img-rounded' src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' width='200px' height='200px'></a> <br>";
				}
				echo "
					<label>Type:</label> $item[2] <br>
					<label>State: </label> $item[6] <br>
					<label>Description:</label> <br>
					$item[3]<br>
					<label>Notes:</label> <br>
					$item[4]<br>
				</div>
				<div class='col-md-9 pull-right'>
					<div>
						<div class='center-text'>
							This item is currently: ";
							$history = inOut($item[0], 25);
							$firstitem = $history[0];
							if ($history[0][6] == False){
								echo "<b>available</b>";
									
								echo "
								  <!-- Modal -->
								  <div class='modal fade' id='SignOut' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
									<div class='modal-dialog'>
									  <div class='modal-content'>
										<div class='modal-header'>
										  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
										  <h4 class='modal-title'>Sign Out $item[1]</h4>
										</div>
										<div class='modal-body'>
										
										
											 <form name='additem' action='inventory.php?action=getItem&serialNum=$item[1]' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
																	<div class='form-group'>
																			<label class='col-lg-3 control-label'>Serial Number:</label>
																			 <div class='col-lg-9'>
																					<input name='SerialNumber' value='$item[1]' required>
																					<input type='hidden' name='id' value='$item[0]'>
																			</div>
																	</div>
																	 
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Student Name:</label>
																				<div class='col-lg-9'>
																						<input name='StudentID' autofocus required>
																				</div>
																	</div>
									
																
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Use:</label>
																				<div class='col-lg-9'>
																						<input name='Use'>
																				</div>
																	</div>
																				
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Date:</label>
																				<div class='col-lg-9'>
																						<input name='Date' value='"; echo date("Y-m-d H:i:s");  echo"' required>
																				</div>
																	</div>
										
										
										
										
										
										</div>
										<div class='modal-footer'>
										  <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
										  <input type='hidden' name='inout' value='out'>
										  <button type='submit' class='btn btn-primary'>Save changes</button>
										</div>
									  </div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								  </div><!-- /.modal -->
								";
							}else{
								//The item is signed out.
								
								echo "<b>In use</b> by <b>$firstitem[1]</b><br />";						
										echo " 
											  <!-- Modal -->
											  <div class='modal fade' id='SignIn' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
												<div class='modal-dialog'>
												  <div class='modal-content'>
													<div class='modal-header'>
													  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
													  <h4 class='modal-title'>Sign In $item[1]</h4>
													</div>
													<div class='modal-body'>
				
											 <form name='additem' action='inventory.php?action=getItem&serialNum=$item[1]' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
																	<div class='form-group'>
																			<label class='col-lg-3 control-label'>Serial Number:</label>
																			 <div class='col-lg-9'>
																					<input name='SerialNumber' value='$item[1]' required>
																					<input type='hidden' name='id' value='$item[0]'>
																			</div>
																	</div>
																	 
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Student Name:</label>
																				<div class='col-lg-9'>
																						<input name='StudentID' autofocus required>
																				</div>
																	</div>
									
																
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Notes:</label>
																				<div class='col-lg-9'>
																						<input name='Issues'>
																				</div>
																	</div>
																				
												<div class='form-group'>
																			<label class='col-lg-3 control-label'>Date:</label>
																				<div class='col-lg-9'>
																						<input name='Date' value='"; echo date("Y-m-d H:i:s");  echo"' required>
																				</div>
																	</div>

												</div>
												<div class='modal-footer'>
												  <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
												  <input type='hidden' name='inout' value='in'>
												  <button type='submit' class='btn btn-primary'>Save changes</button>
												</div>
										
									
											</form>
											  </div><!-- /.modal-content -->
											</div><!-- /.modal-dialog -->
										  </div><!-- /.modal -->
										";
							}
				
					echo "
					</div>

					<div class='center-text'>
						";
						if (!$history[0][6]){
							echo "<button type='button'  data-toggle='modal' href='#SignOut' class='btn btn-success'>Sign Out</button> - <button type='button' class='btn btn-danger disabled'>Sign In</button>";
						}else{
							echo "<button type='button' class='btn btn-success disabled'>Sign Out</button> - <button type='button' data-toggle='modal' href='#SignIn' class='btn btn-danger'>Sign In</button>";
						}
					echo "
								</div>
								<br>
								<div class='center-text'>Last Wiped By: <b>$item[7]</b> on <b>$item[8]</b></div>
							</div>
							<hr>
							<div>
								<h3 class='center-text'>History</h3>
							";
							if ($history[0][0] == "No History"){
								echo "<div class='center-text'>No History Available</div>";
							}else{
								echo "
								<table class='table table-striped table-hover' width='100%'>
								<tr><th>Student</th><th>Use</th><th>Date Out</th><th>Date In</th><th>Issues Reported</th><th>Approved by</th></tr>
								";
								foreach($history as $entry){
									echo "<tr><td>$entry[1]</td> <td>$entry[2]</td><td>$entry[3]</td> <td>$entry[4]</td><td>$entry[5]</td><td>"; if ($entry[6] == 1 || $entry[6] == ""){ echo $entry[7];}else{echo $entry[6];}echo "</td></tr> ";
								}
							}
							echo "
								</table>
							</div>
						</div>
					";
		}else{
		
			echo "
			<div class='alert alert-danger center-text'>Serial Number Not Found!</div>
			<div class='form-group'>
				<center>
					<form class='form' name='input' action='inventory.php' method='GET'>
						<input type='hidden'name='action' value='getItem'> 
						<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
					</form>
				</div>
			";
		}
// END OF ITEM LOOKUP
	
	}else{
		echo "
			<div class='form-group'>
			<center>
				<form class='form' name='input' action='inventory.php' method='GET'>
					<input type='hidden'name='action' value='getItem'> 
					<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
				</form>
			</center>
			</div>
			<br>
		";
	}
	
}else{
	echo "
		<div class='form-group'>
		<center>
			<form class='form' name='input' action='inventory.php' method='GET'>
				<input type='hidden'name='action' value='getItem'> 
				<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
			</form>
		</center>
		</div>
	";
}
?>
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
<script src='js/bootstrap-lightbox.min.js'></script>
	</body>

</html>
