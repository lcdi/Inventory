<?php


function viewall($type = "%", $state = "%", $SignedIn = "%", $sort = 'ID' ) {
	global $items;
	global $con;
	$stringtype = '';
	$stringstate = '';
	$SignedInState = '';
	
	//Type Selection
	if ($type != "%"){
		$types = explode(',', $type);
		$stringtype = "WHERE Type='";
		$total = count($types);
		$counter = 0;
		foreach($types as $type){
			$counter++;
			if ($counter == $total){
				$stringtype .= $type . "' ";
			}else{
				$stringtype .= $type . "' OR Type='";
			}
		}
	}
	
	//State Selection
	if ($state != "%"){
		$states = explode(',', $state);
		
		if ($type != '%'){
			$stringstate = "AND State='";
		}else{
			$stringstate = "WHERE State='";
		}
		
		$total = count($states);
		$counter = 0;
		foreach($states as $state){
			$counter++;
			if ($counter == $total){
				$stringstate .= $state . "' ";
			}else{
				$stringstate .= $state . "' OR State='";
			}
		}
	}
	
	//Signed In/Out Selection
	if ($SignedIn != "%"){
		
		if (($state != '%') || ($type != '%')){
			$thing = "AND ";
		}else{
			$thing = "WHERE ";
		}
		
		if ($SignedIn == "Out"){
			$SignedInState = $thing."Inventory.ID = Inout.ID AND Inout.DateIn='0000-00-00 00:00:00'";
		}else{
			$SignedInState = '';
		}
	}
	
	if ($sort == 'ID'){
		$sort =  "GROUP BY Inventory.ID ORDER BY `Inventory`.ID ASC";
	}else{
		$sorts = explode(',', $sort);
		$stringsort = "GROUP BY Inventory.ID ORDER BY ";
		$total = count($sorts);
		$counter = 0;
		foreach($sorts as $sort){
			$counter++;
			if ($counter == $total){
				$stringsort .= $sort . " ";
			}else{
				$stringsort .= $sort . ",";
			}
		}
		$sort = $stringsort;
	}
	
	if (($stringtype == '') && ($stringstate == '') && ($SignedInState == '')){
		$sql = "SELECT Inout.DateIn, Inout.DateOut, Inout.ID, Inventory.* FROM `Inout`, `Inventory` $sort" or die("im dumb" . mysqli_error($con));
	}else{
		$string = $stringtype.$stringstate.$SignedInState;
		$sql = "SELECT Inout.DateIn, Inout.DateOut, Inout.ID, Inventory.* FROM `Inout`, `Inventory` $string $sort" or die("im dumb" . mysqli_error($con));
	}
	$result = mysqli_query($con, $sql); 
	$items .= "<tr><th>Serial</th><th>Type</th><th>Description</th><th>Notes</th><th>State</th></tr>";	
	while($row = $result->fetch_array()) {
		$id	= $row['ID'];
		$serial	= $row['SerialNumber'];
		$type	= $row['Type'];
		$desc	= $row['Description'];
		$issues	= $row['Use'];
		$state	= $row['State'];
		$items .= "<tr><td><a href='inventory.php?action=getItem&serialNum=$serial'>$serial</a></td><td>$type</td><td>$desc</td><td>$issues</td><td>$state</td></tr>";
	}
}

function id2serial($id){
	global $con;
	$sql = "SELECT SerialNumber FROM Inventory WHERE ID = '$id';";
	$getID = mysqli_fetch_assoc(mysqli_query($con, $sql));
	$result = $getID['SerialNumber'];
	return $result;
}

function id2name($value){
	global $adldap;
	$result = "";
	$usernames = $adldap->user()->all();
	$users = array();
	if (is_numeric($value)){
		foreach ($usernames as $username) {
			$userinfo = $adldap->user()->infoCollection($username, array("displayname","employeeid"));
			$id = $userinfo->employeeid;
			$displayname = $userinfo->displayname;
			if ($id == $value){
				$result = $displayname;
				break;
			}else{
				$result = "Failed";
			}
		}
	}else{
		foreach ($usernames as $username) {
			$userinfo = $adldap->user()->infoCollection($username, array("displayname","employeeid"));
			$id = $userinfo->employeeid;
			$displayname = $userinfo->displayname;
			if ($username == $value){
				$result = $id;
				break;
			}elseif($displayname == $value){
				$result = $id;
				break;
			}else{
				$result = "Failed";
			}
		}
	}
	return $result;
}
		
function oldviewall() {
	global $items;
	global $con;	
	$query = "SELECT * FROM Inventory ORDER BY ID AND Type" or die("im dumb" . mysqli_error($con));
	//eventually we'll just use the mysql fetch and parse into table and make $itemsf equal that
	
	$items = "<h2><center>All Inventory</center></h2>";

	$query = "SELECT COUNT(ID) FROM Inventory" or die("im dumb" . mysqli_error($con));
	//eventually we'll just use the mysql fetch and parse into table and make $itemsf equal that
	$result = mysqli_query($con, $query);
	$row = mysqli_fetch_row($result); 
	$total_records = $row[0]; 
	$total_pages = ceil($total_records / 10); 
	
	if (isset($_GET["pagenum"])) {
		$page  = $_GET["pagenum"]; 
	}else{ 
		$page=1; 
	}
	
	echo "<div class='center-text'><ul class='center-text pagination pagination-large'>";
	for ($i=1; $i<=$total_pages; $i++) { 
		if ($i == $page){
			echo "<li class='active'><a href='#'>".$i."</a></li>"; 
		}else{
			echo "<li><a href='inventory.php?action=viewall&pagenum=".$i."'>".$i."</a></li>"; 
		} 
	}
	echo "</ul></div>";
	
	if (isset($_GET["pagenum"])) {
		$page  = $_GET["pagenum"]; 
	}else{ 
		$page=1; 
	} 
	
	$start_from = ($page-1) * 10; 
	$sql = "SELECT * FROM Inventory ORDER BY ID ASC LIMIT $start_from, 10;";
	$result = mysqli_query($con, $sql); 
		
	$items .= "<tr><th>Serial</th><th>Type</th><th>Description</th><th>Notes</th><th>State</th></tr>";	
	while($row = $result->fetch_array()) {
		$id	= $row['ID'];
		$serial	= $row['SerialNumber'];
		$type	= $row['Type'];
		$desc	= $row['Description'];
		$issues	= $row['Issues'];
		$state	= $row['State'];
		$items .= "<tr><td><a href='inventory.php?action=getItem&serialNum=$serial'>$serial</a></td><td>$type</td><td>$desc</td><td>$issues</td><td>$state</td></tr>";
	}
}

function checkSerial($serial) {
	global $con;
	if (($serial) && ($serial != "")){
		$query = "SELECT * FROM Inventory WHERE SerialNumber = '$serial'" or die("im dumb" . mysqli_error($con));
		$result = mysqli_query($con, $query);
		if (mysqli_num_rows($result) > 0){
			return True;
		}else{
			return False;
		}
	}else{
		return False;
	}
}

function getStats(){
	global $con;
	$stats = array();
	//Count total items in inventory
	$query = "SELECT * FROM Inventory" or die("im dumb" . mysqli_error($con));
	$result = mysqli_query($con, $query);
	$count = mysqli_num_rows($result);
	if ($count > 0){
		$stats['Total-Items'] = $count;
	}
	
	//Count total items that are signed out.
	$query = "SELECT * FROM `Inout` WHERE `DateIn` = '0000-00-00 00:00:00'" or die("im dumb" . mysqli_error($con));
	$result = mysqli_query($con, $query);
	$stats['Current-SignedOut'] = mysqli_num_rows($result);
	
	return $stats;
}

function uploadPicture(){
	global $uploadstatus;
	global $Type;
	$Type = addslashes($Type);
	$allowedExts = array("gif", "jpeg", "jpg", "JPG", "png");
	$temp = explode(".", $_FILES['file']['name']);
	$extension = end($temp);
	if (in_array($extension, $allowedExts)){
		if (!file_exists("uploads/photos/$Type")){
			mkdir("uploads/photos/$Type", 0777, true);
		}
		if ($_FILES["file"]["error"] > 0){
			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
		}else{
			if (file_exists("uploads/photos/$Type/" . $_FILES["file"]["name"]))	  {
				echo $_FILES["file"]["name"] . " already exists. <br>";
				$uploadstatus = "Failed";
			}else{
				move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/photos/$Type/" . $_FILES["file"]["name"]);
				$uploadstatus = "Success";
			}
		}
	}else{
		$uploadstatus = "Failed";
	}
	return $uploadstatus;
}


function inOut($id, $limit = 5) {
	global $con;
	global $inOut;
	$history = array();
	$item = array();
	if ($limit == "*"){
		$query = "SELECT * FROM `Inout` WHERE ID = '$id' ORDER BY DateOut DESC;";
	}else{
		$query = "SELECT * FROM `Inout` WHERE ID = '$id' ORDER BY DateOut DESC LIMIT $limit;";
	}
	$result = mysqli_query($con, $query);
	$counter = 0;
	if (mysqli_num_rows($result) > 0){
		while($row = $result->fetch_array()) {
			$item = array();
			array_push($item, $row['ID']);
			$theid = id2name($row['StudentID']);
			if ($theid == "Failed"){
				$studentid = $row['StudentID'];
			}else{
				$studentid = $theid;
			}
			array_push($item, $studentid);
			array_push($item, $row['Use']);
			array_push($item, $row['DateOut']);
			
			// Display "signed out" instead of the string of 0s
			if ($row['DateIn'] == "0000-00-00 00:00:00"){
				array_push($item, "Signed Out");
			}else{
				array_push($item, $row['DateIn']);
			}
			
			array_push($item, $row['Issues']);
			// Set the in/out of the drive to array spot 6
			if ($counter == 0){
				if ($row['DateIn'] == "0000-00-00 00:00:00"){
					array_push($item, True);
				}else{
					array_push($item, False);
				}
			}
            array_push($item, $row['UserOut']);
			array_push($history, $item);
			$counter++;
		}
	}else{
		$item = array();
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, False);
		array_push($history, $item);
	}
	//check if returned or not
	return $history;
}

function wipe($id, $limit = 5) {
	global $con;
	global $inOut;
	$history = array();
	$item = array();
	if ($limit == "*"){
		$query = "SELECT * FROM `Wiped` WHERE DeviceID = '$id' ORDER BY Date DESC;";
	}else{
		$query = "SELECT * FROM `Wiped` WHERE DeviceID = '$id' ORDER BY Date DESC LIMIT $limit;";
	}
	$result = mysqli_query($con, $query);
	$counter = 0;
	if (mysqli_num_rows($result) > 0){
		while($row = $result->fetch_array()) {
			$item = array();
			array_push($item, $row['DeviceID']);
			array_push($item, $row['UserName']);
			array_push($item, $row['Date']);
			array_push($history, $item);
			$counter++;
		}
	}else{
		$item = array();
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($item, "No History");
		array_push($history, $item);
	}
	//check if returned or not
	return $history;
}

function getItem($serial) {
	global $items;
	global $con;
	global $item;
	global $history;
	$items = "";
	$item['History'] = "";
	
	if (checkSerial($serial)){
		//first we grab the item from the inventory	
		$query = "SELECT * FROM Inventory WHERE SerialNumber = '$serial'" or die("im dumb" . mysqli_error($con));
		$result = mysqli_query($con, $query);
		$items .= "<h2>$serial</h2>";
		while($row = $result->fetch_array()) {
			$item[0]	= $row['ID'];
			$item[1]	= $row['SerialNumber'];
			$item[2]	= $row['Type'];
			$item[3]	= $row['Description'];
			$item[4]	= $row['Issues'];
			$item[5]	= $row['PhotoName'];
			$item[6]	= $row['State'];
			$item[9]	= $row['DeviceSerial'];
		}
		//now grab from the signed in out table
		$query = "SELECT * FROM `Inout` WHERE ID = '$item[0]' ORDER BY DateOut DESC;";
		$result = mysqli_query($con, $query) or die(mysqli_error($con));
		while($row = $result->fetch_array()) {
			$item['History'] = "";
			$id     = $row['ID'];
			$theid = id2name($row['StudentID']);
			if ($theid == "Failed"){
				$stuid = $row['StudentID'];
			}else{
				$stuid = $theid;
			}
			$use    = $row['Use'];
			$din    = $row['DateIn'];
			if ($din == "0000-00-00 00:00:00"){
				$din = "Signed Out";
			}
			$dout   = $row['DateOut'];
			$history .= "$stuid,$use,$dout,$din";
		}		
		
		$query = "SELECT * FROM `Wiped` WHERE DeviceID = '$item[0]' ORDER BY Date DESC LIMIT 1;";
		$result = mysqli_query($con, $query) or die(mysqli_error($con));
		if (mysqli_num_rows($result) != 0){
			while($row = $result->fetch_array()) {
				$item[7]     = $row['UserName'];
				$item[8]     = $row['Date'];
			}
		}else{
			$item[7] = "Nobody";
			$item[8] = "Never";
		}
		return $item;
	}else{
		return "Invalid Serial";
	}
}
?>
