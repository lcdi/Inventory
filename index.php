<?php
	require_once('php/session.php');
	require_once('php/header.php');
?>
		<table class='table'>
			<tr>
				<th>Serial Number</th>
				<th>Item Type</th>
				<th>Description</th>
				<th>Notes/Issues</th>
				<th>Quality/State</th>
				<th>In/Out</th>
				<th></th>
			</tr>
			<tr>
				<form class='form' name='input' action='index.php' method='GET'>
					<!-- Serial Number -->
					<td>
						<input type='text' name='serialNumber'
							class='form-control form-small center-text'
							placeholder='LCDI-1234567' autofocus>
					</td>
					<!-- Item Type -->
					<td>
						<select style='width: 150px;' multiple name='ItemType'>
							<option value='TEST Type'>TEST Type</option>
							<?php
								// Get values from database
								/*
								$query = 'SELECT DISTINCT Type FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$type = $row['Type'];	
									echo "<option value='$type'>$type</option>";
								}
								*/
							?>
						</select>
					</td>
					<!-- Description -->
					<td></td>
					<!-- Notes/Issues -->
					<td></td>
					<!-- Quality/State -->
					<td>
						<select style='width: 150px;' multiple name='State'>
							<?php
								/*
								$query = 'SELECT DISTINCT State FROM Inventory;' or die('im dumb' . mysqli_error($con));
								$result = mysqli_query($con, $query);
								while($row = $result->fetch_array()) {
									$state	= $row['State'];	
									echo "<option value='$state'>$state</option>";
								}
								*/
							?>
						</select>
					</td>
					<!-- In/Out -->
					<td>
						<select style='width: 100px;' multiple name='SignedIn'>
							<option value='In'>In</option>
							<option value='Out'>Out</option>
						</select>
					</td>
					<!-- Sign In/Out -->
					<td></td>
					<input type='submit' value='Filter'>
				</form>
				<?php
					// Selectors
				?>
			</tr>
			<?php
				// Items
			?>
		</table>
<?php
	require_once('php/footer.php');
?>