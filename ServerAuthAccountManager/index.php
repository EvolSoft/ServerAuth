<?php
/*
 * ServerAuth Account Manager (v1.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 12/05/2015 01:16 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width'>
		<script src='js/jquery.min.js'></script>
		<script src='js/xweb.js'></script>
		<link rel='stylesheet' href='css/xweb.css'>
		<link rel='stylesheet' href='css/font-awesome.css'>
		<title>ServerAuth Account Manager</title>
	</head>
<?php
session_start();
$cfg_status = file_exists('config.php');
error_reporting(0);
if($cfg_status){
	include 'config.php';
}
?>
	<body style='background: #fdfdfd'>
		<nav class='navbar navbar-static <?php if($cfg_status && $config['dark-theme']) echo 'navbar-dark'; ?>'>
	        <a class='navbar-title' href='index.php'>ServerAuth Account Manager</a>
	        <button type='button' class='navbar-toggle'></button>
	        <ul class='navbar-links navbar-links-right'>
	        	<?php 
	        	if(isset($_SESSION["login"])){
					echo "<li class='menu-group'><a openmenu>Welcome, <b>" . $_SESSION["login"] . "</b> <span class='fa fa-caret-down'></span></a><ul class='menu'><li><a href='account.php'><i class='fa fa-user'></i> Your Account</a></li><li><a href='account.php#chpass'><i class='fa fa-key'></i> Change Password</a></li><li><a href='account.php#delaccount'><i class='fa fa-times'></i> Delete Account</a></li><hr><li><a href='account.php?action=logout'><i class='fa fa-sign-out'></i> Logout</a></li></ul></li>";
				}elseif(isset($_SESSION["admin_login"])){
					echo "<li><a href='admin.php'><i class='fa fa-tachometer'></i> Admin Panel</a></li>";
				}else{
	        	?>
	        	<li><a href='login.php'><i class='fa fa-sign-in'></i> Login</a></li>
	        	<?php 
	        		if($cfg_status && $config["allow-register"]){
						echo "<li><a href='register.php'><i class='fa fa-user'></i> Register</a></li>";
            		}
            	}
	       		?>
	        </ul>
		</nav>
		<div class='content'>
			<div class='col-2'></div>
			<div class='col-8'>
			<?php
			if(!file_exists("admin_config.php")){
				echo "<div class='alert alert-warning square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-warning'></i> You haven't set up admin data yet! It's recommended to do it immediately.<br><br><a href='admin.php'>Click here</a> to do this now.</div>";
			}
	        if(!$cfg_status){
    			echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> ServerAuth Account Manager is not configured yet.<br><br>Please create a configuration file or rename the existing one.</div>";
    			//Default box
    			echo "<h2>ServerAuth Account Manager</h2><br><p>ServerAuth Account Manager is a simple web script that let you to fully manage your ServerAuth accounts.<br>With ServerAuth Account Manager you can:</p><br><ul><li>Manage all registered accounts (admin-only)</li><li>Show your ServerAuth account info</li><li>Change your account password</li><li>Delete your account</li></ul>";
    		}else{
			?>
			<div class='box square'><?php echo $config["main_description"]; ?></div>
			<?php 
			}
			?>
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0. <?php if($config["allow-register"] && !isset($_SESSION["login"]) && !isset($_SESSION["admin_login"])){ echo "<a href='register.php'>Register an account</a>"; } ?></p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
				<br>
			</div>
		</div>
	</body>
</html>
