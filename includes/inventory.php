<?php
require_once('includes/functions.php');
require_once('includes/pickles.php');
if (checkSession($_SESSION['Authenticated']) == False){
		header( 'Location: http://inventory/' ) ;
}
include('includes/header.php'); 
?>
		<div class='container-narrow'>
<?php
if (isset($_GET['action'])){
	if ($_GET['action'] == 'viewall'){
		viewall();
		echo "
			<table class='table'>
				$items
			</table>
			";
		}elseif ($_GET['action'] == 'wipe'){
			if ((isset($_POST['SerialNumber'])) && ($_POST['SerialNumber'] != '')){
				$serialnum = mysql_real_escape_string($_POST['SerialNumber']);
				getItem($serialnum);
			}else{
				echo "
					<div class='form-group center-text'>
						<center>
							<form class='form' name='input' action='inventory.php?action=wipe' method='POST'>
								<input type='hidden'name='action' value='getItem'> 
								<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control center-text form-small' placeholder='LCDI-00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
							</form>
						</center>
					</div>
				";
			}
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
				$Photo = $_FILES['file']['name'];
				uploadPicture();
				if ($uploadstatus != "Failed"){
					$sql = "INSERT INTO `inventory`.`Inventory` (`ID`, `SerialNumber`, `DeviceSerial`, `Type`, `Description`, `Issues`, `PhotoName`, `State`) VALUES (NULL, '$SerialNumber', '$DevSerialNumber', '$Type', '$Desc', '$Issues', '$Photo', '$State');";
					if (!mysqli_query($con,$sql)) {
						die('Error: ' . mysqli_error($con));
					}else{
						echo 'Item Added';
					}
				}
			}else{
				echo "
					
						<h2 class='center-text'>Add an Item</h2><br>
						<form name='additem' action='inventory.php?action=additem' method='post' enctype='multipart/form-data' class='form-horizontal' role='form'>
						<div class='form-group'>
							<label class='col-lg-3 control-label'>LCDI Serial Number:</label> 
							<div class='col-lg-9'>	
								<input name='SerialNumber' placeholder='LCDI-'>
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
							<label class='col-lg-3 control-label'>Known Issues:</label>
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
		} elseif (isset($_GET['serialNum'])){
			$serialnum = mysql_real_escape_string($_GET['serialNum']);
			getItem($serialnum);		
			echo "		
				<div class='form-group'>
				<center>
					<form class='form' name='input' action='inventory.php' method='GET'>
						<input type='hidden'name='action' value='getItem'> 
						<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI-00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
					</form>
				</center>
				</div>
			";
			echo "
				<hr>
				<div id='box' class='col-md-5 pull-left center-text'>
					<h2>$item[1]</h2>
					<img src='uploads/photos/"; echo addslashes($item[2]); echo "/$item[5]' width='200px' height='200px'> <br>
					<label>Type:</label> $item[2] <br>
					<label>State: </label> $item[6] <br>
					<label>Description:</label> <br>
					$item[3]<br>
					<label>Issues:</label> <br>
					$item[4]<br>
				</div>
				<div class='col-md-7 pull-right'>
					<div>
						<div class='center-text'>
							This item is currently: ";
							if (inOut($item[0])){
								echo "<b>available</b>";
								if ((isset($_POST['StudentID'])) && ($_POST['StudentID'] != '')){
												$SerialNumber = $_POST['SerialNumber'];
												$StudentID = $_POST['StudentID'];
												$DateOut = $_POST['Date'];
												$Use = $_POST['Use'];
												$sql = "INSERT INTO `Inout` (`ID`,`StudentID`,`Use`,`DateOut`) VALUES('$item[0]', '$StudentID', '$Use', '$DateOut');";
												if (!mysqli_query($con,$sql)) {
													die('Error: ' . mysqli_error($con));
												}else{
													echo '<br/ >Signed back in';
													}
											}else{
								
								
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
                                                        							<input name='SerialNumber' value='$item[1]'>
                                                							</div>
                                        							</div>
                                       								 
												<div class='form-group'>
                                                							<label class='col-lg-3 control-label'>Student ID:</label>
                                                        						<div class='col-lg-9'>
                                                                						<input name='StudentID'>
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
                                                                						<input name='Date' value='"; echo date("Y-m-d H:i:s");  echo"'>
                                                        						</div>
                                        							</div>
										
										
										
										
										
										</div>
										<div class='modal-footer'>
										  <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
										  <button type='submit' class='btn btn-primary'>Save changes</button>
										</div>
									  </div><!-- /.modal-content -->
									</div><!-- /.modal-dialog -->
								  </div><!-- /.modal -->
								";
								}
							}else{
								echo "<b>Signed Out</b> by <b>$inOut[1]</b>";
							

					 						if ((isset($_POST['StudentID'])) && ($_POST['StudentID'] != '')){
												$SerialNumber = $_POST['SerialNumber'];
												$StudentID = $_POST['StudentID'];
												$DateIn = $_POST['Date'];
												$Issues = $_POST['Issues'];
												$sql = "UPDATE  `Inout` SET  `DateIn` =  '$DateIn' WHERE  `Inout`.`ID` =$item[0] AND  `Inout`.`DateIn` =  '0000-00-00 00:00:00' LIMIT 1";
												if (!mysqli_query($con,$sql)) {
													die('Error: ' . mysqli_error($con));
												}else{
													echo '<br/ >Signed back in';
													}
											}else{
						

	
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
                                                        							<input name='SerialNumber' value='$item[1]'>
                                                							</div>
                                        							</div>
                                       								 
												<div class='form-group'>
                                                							<label class='col-lg-3 control-label'>Student ID:</label>
                                                        						<div class='col-lg-9'>
                                                                						<input name='StudentID'>
                                                        						</div>
                                        							</div>
									
																
												<div class='form-group'>
                                                							<label class='col-lg-3 control-label'>Issues:</label>
                                                        						<div class='col-lg-9'>
                                                                						<input name='Issues'>
                                                        						</div>
                                        							</div>
																				
												<div class='form-group'>
                                                							<label class='col-lg-3 control-label'>Date:</label>
                                                        						<div class='col-lg-9'>
                                                                						<input name='Date' value='"; echo date("Y-m-d H:i:s");  echo"'>
                                                        						</div>
                                        							</div>
										





												</div>
												<div class='modal-footer'>
												  <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
												  <button type='submit' class='btn btn-primary'>Save changes</button>
												</div>
										
									
											</form>
											  </div><!-- /.modal-content -->
											</div><!-- /.modal-dialog -->
										  </div><!-- /.modal -->
										";}
							}
				
					echo "
					</div>
					<br>
					<div class='center-text'>
						";
						if (inOut($item[0])){
							echo "<button type='button' class='btn btn-success disabled'>Sign In</button> - <button type='button'  data-toggle='modal' href='#SignOut' class='btn btn-danger'>Sign Out</button>";
						}else{
							echo "<button type='button' data-toggle='modal' href='#SignIn' class='btn btn-success'>Sign In</button> - <button type='button' class='btn btn-danger disabled'>Sign Out</button>";
						}
		echo "
					</div>
				</div>
				<hr>
				<div>
					<h3 class='center-text'>History</h3>
					<table class='table table-striped table-hover' width='100%'>
					<tr><th>Student ID</th><th>Use</th><th>Date Out</th><th>Date In</th></tr>
					$history
					</table>
				</div>
			</div>
		";
		
	}else{
		echo "
			<div class='form-group'>
			<center>
				<form class='form' name='input' action='inventory.php' method='GET'>
					<input type='hidden'name='action' value='getItem'> 
					<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small' placeholder='LCDI-00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
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
				<label>Item Serial Number: </label> <input type='text' name='serialNum' class='form-control form-small center-text' placeholder='LCDI-00000' autofocus> <br> <input type='submit' class='btn btn-default' value='Lookup'> 
			</form>
		</center>
		</div>
	";
}
?>
	</div>
		<br>
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
