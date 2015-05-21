<?php
/*
 * ServerAuth Account Manager (v1.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 12/05/2015 01:17 PM (UTC)
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
if(isset($_SESSION["login"])){
	header("Location: login.php");
}
$cfg_status = file_exists('config.php');
error_reporting(E_NOTICE);
include 'ServerAuthWebAPI.php';
if($cfg_status){
	include 'config.php';
	$api = new ServerAuthWebAPI($config["db_host"], $config["db_port"], $config["db_username"], $config["db_password"], $config["db_database"], $config["db_table_prefix"]);
	$api_status = $api->getStatus();
}
?>
	<body style='background: #fdfdfd'>
		<nav class='navbar navbar-static <?php if($cfg_status && $config['dark-theme']) echo 'navbar-dark'; ?>'>
	        <a class='navbar-title' href='index.php'>ServerAuth Account Manager</a>
	        <button type='button' class='navbar-toggle'></button>
			<ul class='navbar-links navbar-links-right'>
				<li><a href='login.php'><i class='fa fa-sign-in'></i> Login</a></li>
			</ul>
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
					</div>
				</div>
				<?php 
				}else{
					if(!$config["allow-register"]){
				?>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-exclamation-circle'></i> Registration disabled</div>
					</div>
					<div class='panel-content'>
						<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> Registration disabled</div>
					</div>
				</div>
				<?php
					}elseif($api_status == ServerAuthWebAPI::ERR_MYSQL){
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
					?>
					<div class='panel'>
					<div class='panel-header'>
					    <div class='panel-title'><i class='fa fa-lock'></i> Register</div>
					</div>
					<div class='panel-content'>
						<form action='?action=register' method='POST'>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>Username:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='text' class='input' name='username'>
							    </div>
							</div>
							<br>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>Password:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='password' class='input' name='password'>
							    </div>
							 </div>
							<div class='form'>
								<div class='col-rs-3'>
									<label class='form-label' style='margin-left: -5px'>Confirm password:</label>
								</div>
							    <div class='col-rs-9 ns'>
							    	<input type='password' class='input' name='cpassword'>
							    	<?php
									if(isset($_GET["action"]) && strtolower($_GET["action"]) == "register" && isset($_POST["username"]) && isset($_POST["password"])){
										if(!$api->isPlayerRegistered($_POST["username"])){
											if($_POST["password"] == $_POST["cpassword"]){
												if(preg_match('/\s/', $_POST["password"]) == 0){
													if(strlen($_POST["password"]) <= $config["min-password-length"]){
														echo "<div class='hint out-error'>Password too short</div>";
													}elseif(strlen($_POST["password"]) >= $config["max-password-length"]){
														echo "<div class='hint out-error'>Password too long</div>";
													}else{
														$api->registerPlayer($_POST["username"], $_POST["password"], $_SERVER['REMOTE_ADDR'], time() * 1000, time() * 1000);
														$_SESSION["login"] = $_POST["username"];
														header("Location: account.php");
													}
												}else{
													echo "<div class='hint out-error'>Password can't contain spaces</div>";
												}
											}else{
												echo "<div class='hint out-error'>Password doesn't match confirmation</div>";
											}
										}else{
											echo "<p class='hint out-error'>Username already registered</p>";
										}
									}
									?>
							    </div>
							</div>
							<br>
							<div class='col-rs-12 ns alignment-right'>
							    <input class='button button-success' type='submit' value="Register">
							</div>
						</form>
					</div>
				</div>
					<?php			
					}
				}
				?>
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0.</p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
			</div>
		</div>
	</body>
</html>
