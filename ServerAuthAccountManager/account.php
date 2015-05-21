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
if(isset($_SESSION["admin_login"])){
	header("Location: admin.php");
}
if(!isset($_SESSION["login"])){
	header("Location: login.php");
}
$cfg_status = file_exists('config.php');
error_reporting(0);
include 'ServerAuthWebAPI.php';
if($cfg_status){
	include 'config.php';
	$api = new ServerAuthWebAPI($config["db_host"], $config["db_port"], $config["db_username"], $config["db_password"], $config["db_database"], $config["db_table_prefix"]);
	$api_status = $api->getStatus();
}
if(isset($_GET["action"]) && strtolower($_GET["action"]) == "logout"){
	session_destroy();
	header("Location: login.php");
}
?>
	<body style='background: #fdfdfd'>
		<nav class='navbar navbar-static <?php if($cfg_status && $config['dark-theme']) echo 'navbar-dark'; ?>'>
	        <a class='navbar-title' href='index.php'>ServerAuth Account Manager</a>
	        <button type='button' class='navbar-toggle'></button>
	        <?php
			echo "<ul class='navbar-links navbar-links-right'><li class='menu-group'><a openmenu>Welcome, <b>" . $_SESSION["login"] . "</b> <span class='fa fa-caret-down'></span></a><ul class='menu'><li><a href='account.php'><i class='fa fa-user'></i> Your Account</a></li><li><a href='#chpass'><i class='fa fa-key'></i> Change Password</a></li><li><a href='#delaccount'><i class='fa fa-times'></i> Delete Account</a></li><hr><li><a href='?action=logout'><i class='fa fa-sign-out'></i> Logout</a></li></ul></li></ul>";
	        ?>
		</nav>
		<div class='content'>
			<div class='col-2'></div>
			<div class='col-8'>
			<?php
			if(!$cfg_status){
			?>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-warning'></i> Warning</div></div>
					</div>
					<div class='panel-content'>
						<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Registration disabled</div>
					</div>
				</div>
		<?php 
		}else{
			if($api_status == ServerAuthWebAPI::ERR_MYSQL){
		?>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-exclamation-circle'></i> MySQL Error</div>
					</div>
					<div class='panel-content'>
					<?php 
					echo "<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> <b>MySQL Error:</b> " . $api->getDatabase()->connect_error . "</div>";
					?>
					</div>
				</div>
		<?php
			}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_PLUGIN){
			?>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-exclamation-circle'></i> Outdated Plugin Error</div>
					</div>
					<div class='panel-content'>
						<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> ServerAuth plugin is outdated. Please update it.</div>
					</div>
				</div>
			<?php
			}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_WEBAPI){
			?>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-exclamation-circle'></i> Outdated WebAPI Error</div>
					</div>
					<div class='panel-content'>
						<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> ServerAuth Web API is outdated. Please update it.</div>
					</div>
				</div>
			<?php
			}else{
				if(!$api->isPlayerRegistered($_SESSION["login"])){
					session_destroy();
					header("Location: index.php");
				}
				if($config["allow-show-info"]){
			?>
				<div class='panel panel-info'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-info-circle'></i> Account Info</div>
					</div>
					<div class='panel-content'>
						<?php
						echo "<h4><b>Username:</b> " . $_SESSION["login"] . "</h4>";
						echo "<h4><b>IP Address:</b> " . $api->getPlayerData($_SESSION["login"])["ip"] . "</h4>";
						echo "<h4><b>Registration date:</b> " . date("d/m/Y H:i:s", $api->getPlayerData($_SESSION["login"])["firstlogin"] / 1000) . "</h4>";
						echo "<h4><b>Last login:</b> " . date("d/m/Y H:i:s", $api->getPlayerData($_SESSION["login"])["lastlogin"] / 1000) . "</h4>";
						?>
					</div>
				</div>
				<?php
				}
				?>
				<div class='panel' id='chpass'>
				<?php
				if(isset($_GET["action"]) && strtolower($_GET["action"]) == "chpass" && isset($_POST["old-password"]) && isset($_POST["new-password"]) && isset($_POST["cnew-password"])){
					if($api->getPlayerData($_SESSION["login"])["password"] == hash($api->getPasswordHash(), $_POST["old-password"])){
						$old_password = true;
						if($_POST["new-password"] == $_POST["cnew-password"]){
							if(preg_match('/\s/', $_POST["new-password"]) == 0){
								if(strlen($_POST["new-password"]) <= $config["min-password-length"]){
									$new_password = 2;
								}elseif(strlen($_POST["new-password"]) >= $config["max-password-length"]){
									$new_password = 3;
								}else{
									$new_password = 4;
									$api->changePlayerPassword($_SESSION["login"], $_POST["new-password"]);
								}
							}else{
								$new_password = 1;
							}
						}else{
							$new_password = 0;
						}
					}else{
						$old_password = false;
					}
				}
				?>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-key'></i> Change Password</div>
					</div>
					<div class='panel-content'>
						<form action='?action=chpass#chpass' method='POST'>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>Old password:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='password' class='input' name='old-password'>
							    	<?php 
									if(isset($old_password) && isset($_POST["old-password"]) && isset($_POST["new-password"]) && isset($_POST["cnew-password"])){
										if(!$old_password){
											echo "<div class='hint out-error'>The password doesn't match the old password</div>";
										}
									}
									?>
							    </div>
							</div>
							<br>
							<br>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>New password:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='password' class='input' name='new-password'>
							    </div>
							</div>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>Confirm new password:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='password' class='input' name='cnew-password'>
							    	<?php 
									if(isset($new_password) && isset($_POST["old-password"]) && isset($_POST["new-password"]) && isset($_POST["cnew-password"])){
										if($new_password == 0){
											echo "<div class='hint out-error'>Password doesn't match confirmation</div>";
										}elseif($new_password == 1){
											echo "<div class='hint out-error'>Password can't contain spaces</div>";
										}elseif($new_password == 2){
											echo "<div class='hint out-error'>Password too short</div>";
										}elseif($new_password == 3){
											echo "<div class='hint out-error'>Password too long</div>";
										}elseif($new_password == 4){
											echo "<div class='hint out-success'>Password changed!</div>";
										}
									}
									?>
							    </div>
							</div>
							<div class='col-rs-12 ns alignment-right'>
								<input type='submit' class='button' value='Change Password'>
							</div>
						</form>
					</div>
				</div>
				<div class='panel panel-error' id='delaccount'>
				<?php 
				if(isset($_GET["action"]) && strtolower($_GET["action"]) == "delaccount"){
					$api->unregisterPlayer($_SESSION["login"]);
					session_destroy();
					header("Location: index.php");
				}
				?>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-times-circle'></i> Delete Account</div>
					</div>
					<div class='panel-content'>
						<a class='button button-error full-width alignment-center' href='?action=delaccount'>Click here to delete your account (no undo)</a>
					</div>
				</div>
		<?php
			}
		}
		?>
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0.</p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
				<br>
			</div>
		</div>
	</body>
</html>
