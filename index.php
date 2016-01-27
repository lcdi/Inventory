<?php
	require_once('includes/functions.php');
	require_once('includes/pickles.php');
	include('includes/header.php'); 
?>
		<?php
			if (!isset($_SESSION['Authenticated']) || checkSession($_SESSION['Authenticated']) == False){
				if (isset($_POST['user'])) {
					$username = $_POST['user'];
					$password = $_POST['password'];
					if ($adldap->authenticate($username,$password)){
						createSession($username,$password);
						header( 'Location: http://inventory.lcdi/index.php' ) ;
					}else{
							echo "<div class='login'>
									<div class='alert alert-danger'>Invalid Credentials!</div>
									<form name='input' action='index.php' method='post' class='form-signin'>
										<h2 class='form-signin-heading'>Please sign in: </h2>
										<input name='user' type='text' class='form-control' placeholder='LCDI Username' autofocus>
										<input name='password' type='password' class='form-control' placeholder='Password'><br>
										<button class='btn btn-lg btn-primary btn-block' type='submit'>Sign in</button>
									</form>
								</div>";
					}
				}else{
		?>	
		<div class="login">
			<form name="input" action="index.php" method="post" class="form-signin">
				<h2 class="form-signin-heading">Please sign in: </h2>
				<input name="user" type="text" class="form-control" placeholder="LCDI Username" autofocus>
				<input name="password" type="password" class="form-control" placeholder="Password"><br>
				<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
			</form>
		</div>
		<?php
				}
			}else{
				$userinfo = $adldap->user()->info(checkSession($_SESSION['Authenticated']), array("displayname"));
		?>
		  
		  <!-- Old main page body -->

		  <div class="jumbotron">
			<h2>Welcome back <?php echo $userinfo[0]["displayname"][0]; ?>,</h2>
			<p class="lead">
				<?php
				// This section pulls database specific information to display statistics on the homepage.
					$stats = getStats();
					echo "There are currently <span class='text-info'><a href='inventory.php?action=viewall'>" . $stats['Total-Items'] . "</a></span> items in inventory and <span class='text-info'><a href='inventory.php?action=viewall&filter=yes&SignedIn=Out'>" . $stats['Current-SignedOut'] ."</a></span> currently signed out item[s].";
				?>
			</p>
		  </div>

		  <!-- New page body -->

		  

		  <!-- End new page body -->

		<?php
			}
		?>

		<hr>

		<div class="footer">
			<p><center>&copy; Champlain College LCDI 2013</center></p>
		</div>

	</div> <!-- /container -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//code.jquery.com/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

	</body>

</html>
