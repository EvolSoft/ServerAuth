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
	header("Location: account.php");
}
$cfg_status = file_exists('config.php');
error_reporting(0);
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
	        <?php 
	        if($cfg_status && $config["allow-register"] && !isset($_SESSION["login"])){
				echo "<ul class='navbar-links navbar-links-right'><li><a href='register.php'><i class='fa fa-user'></i> Register</a></li></ul>";
            }
	        ?>
		</nav>
		<br>
		<div class='content'>
			<div class='col-3'></div>
			<div class='col-6'>
				<div class='panel'>
					<div class='panel-header'>
					    <?php
	        	    	if(!$cfg_status){
    						echo "<div class='panel-title'><i class='fa fa-warning'></i> Warning</div>";
    					}else{
							if($api_status == ServerAuthWebAPI::ERR_MYSQL){
								echo "<div class='panel-title'><i class='fa fa-exclamation-circle'></i> MySQL Error</div>";
							}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_PLUGIN){
								echo "<div class='panel-title'><i class='fa fa-exclamation-circle'></i> Outdated Plugin Error</div>";
							}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_WEBAPI){
								echo "<div class='panel-title'><i class='fa fa-exclamation-circle'></i> Outdated WebAPI Error</div>";
							}else{
								echo "<div class='panel-title'><i class='fa fa-lock'></i> Login</div>";
							}
						}
						?>
					</div>
					<div class='panel-content'>
					<?php
	        	    if(!$cfg_status){
    					echo "<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> ServerAuth Account Manager is not configured yet.<br><br>Please create a configuration file or rename the existing one.</div>";
    				}else{
						if($api_status == ServerAuthWebAPI::ERR_MYSQL){
							echo "<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> <b>MySQL Error:</b> " . $api->getDatabase()->connect_error . "</div>";
						}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_PLUGIN){
							echo "<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> ServerAuth plugin is outdated. Please update it.</div>";
						}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_WEBAPI){
							echo "<div class='alert alert-error square' style='margin-bottom: 0'><i class='fa fa-exclamation-circle'></i> ServerAuth Web API is outdated. Please update it.</div>";
						}else{
						?>
						<form action='?action=login' method='POST'>
							<div class='form'>
								<div class='col-rs-2'>
									<label class='form-label' style='margin-left: -5px'>Username:</label>
								</div>
							    <div class='col-rs-10 ns'>
							    	<input type='text' class='input' name='username'>
							    </div>
							</div>
							<div class='form'>
								<div class='col-rs-2'>
									<label class='form-label' style='margin-left: -5px'>Password:</label>
								</div>
							    <div class='col-rs-10 ns'>
							    	<input type='password' class='input' name='password'>
							    	<?php
									if(isset($_GET["action"]) && strtolower($_GET["action"]) == "login"){
										if($api->isPlayerRegistered($_POST["username"]) && $api->getPlayerData($_POST["username"])["password"] == hash($api->getPasswordHash(), $_POST["password"])){
											$_SESSION["login"] = $_POST["username"];
											header("Location: account.php");
										}else{
											echo "<p class='hint out-error'>Wrong username or password</p>";
										}
									}
									?>
							    </div>
							</div>
							<br>
							<div class='col-rs-12 ns alignment-right'>
							    <input class='button button-primary' type='submit' value="Login">
							</div>
						</form>
				<?php 
					}
				}
				?>
					</div>
				</div>
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0. <?php if($config["allow-register"] && !isset($_SESSION["login"])){ echo "<a href='register.php'>Register an account</a>"; } ?></p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
				<br>
			</div>
		</div>
	</body>
</html>
