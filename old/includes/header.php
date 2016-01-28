<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>LCDI Inventory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
	<link href="css/bootstrap-lightbox.min.css" rel="stylesheet">
    <style type="text/css">
      html, body {
        padding-top: 20px;
        padding-bottom: 40px;
		
      }

      /* Custom container */
      .container-narrow {
		padding: 10px;
		background: #fff;
        margin: 0 auto;
        max-width: 1000px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

	  .login{
		margin: auto;
		max-width: 300px;
	}
      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
	.responsive-image { max-width:100%; height:auto; }
	.navbar .dropdown-menu {
	 margin-top: 0px;
	}
	  
	  .form-small {
		width: 200px;
	}
	  .form-med {
		width: 300px;
	}
	.center-text {
		text-align: center;
	}
	
	ul.nav li.dropdown:hover > ul.dropdown-menu {
		display: block;
margin-top: 0px;		
	}
	
    </style>
  <!--  <link href="css/bootstrap-responsive.css" rel="stylesheet"> -->

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.js"></script>
    <![endif]-->
  </head>

	<body>

	<div class="container-narrow">
		<div class="masthead">
			<?php
			$directoryURI = $_SERVER['REQUEST_URI'];
			$path = parse_url($directoryURI, PHP_URL_PATH);
			$components = explode('/', $path);
			$first_part = $components[1];
			if ($first_part == "index.php"){
				$first_part = "";
			}
			if (isset($_SESSION['Authenticated'])){
				if (checkSession($_SESSION['Authenticated']) == True){
					if (getPerms(checkSession($_SESSION['Authenticated'])) == "Admin"){
						echo '
							<ul class="nav nav-pills pull-right">
								<li><a href="http://home.lcdi/">LCDI Home Page</a></li>
								<li class="dropdown active">
									<a href="index.php" class="dropdown-toggle active">Inventory</a>
									<ul class="dropdown-menu active">
										<li><a href="inventory.php">Sign In/Out</a></li>
										<li><a href="search.php">Search Students</a></li>
										<li><a href="inventory.php?action=wipe">Wipe Drives</a></li>
										<li><a href="inventory.php?action=viewall">View All Items</a></li>
										<li><a href="inventory.php?action=additem">Add Items</a></li>
										<li><a href="inventory.php?action=edititem">Edit Items</a></li>
										<li><a href="barcode/index.php">Generate Barcodes</a></li>
										<li><a href="admin.php">Admin</a></li>
									</ul>
								</li>
								<li><a href="logout.php">Logout</a></li>
							</ul>';
					}elseif (getPerms(checkSession($_SESSION['Authenticated'])) == "Office Assistant"){
						echo '
							<ul class="nav nav-pills pull-right">
								<li><a href="http://home.lcdi/">LCDI Home Page</a></li>
								<li class="dropdown active">
									<a href="index.php" class="dropdown-toggle active">Inventory</a>
									<ul class="dropdown-menu active">
										<li><a href="inventory.php">Sign In/Out</a></li>
										<li><a href="search.php">Search Students</a></li>
										<li><a href="inventory.php?action=wipe">Wipe Drives</a></li>
										<li><a href="inventory.php?action=viewall">View All Items</a></li>
										<li><a href="inventory.php?action=additem">Add Items</a></li>
										<li><a href="inventory.php?action=edititem">Edit Items</a></li>
									</ul>
								</li>
								<li><a href="logout.php">Logout</a></li>
							</ul>';
					}else{
						echo '
						<ul class="nav nav-pills pull-right">
							<li><a href="http://home.lcdi/">LCDI Home Page</a></li>
							<li class="active"><a href="index.php">Inventory</a></li>
							<li><a href="logout.php">Logout</a></li>

						</ul>
						';
					}
				}else{
					echo '
					<ul class="nav nav-pills pull-right">
						<li><a href="http://home.lcdi/">LCDI Home Page</a></li>
						<li class="active"><a href="index.php">Inventory</a></li>
					</ul>
					';
				}
			}else{
				echo '
				<ul class="nav nav-pills pull-right">
					<li><a href="http://home.lcdi/">LCDI Home Page</a></li>
					<li class="active"><a href="index.php">Inventory</a></li>
				</ul>
				';
			}
			?>
			<img src = "lcdi.jpg" height=50px />
			<h3 class="muted"></h3>
		</div>
		<!-- END OF HEADER -->
		<hr>
