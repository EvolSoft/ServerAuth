<?php
/*
 * ServerAuth Account Manager (v1.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 13/05/2015 05:09 PM (UTC)
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
$admin_cfg_status = file_exists('admin_config.php');
$result = array();
//error_reporting(0);
include 'ServerAuthWebAPI.php';
if($cfg_status){
	include 'config.php';
}
?>
	<body style='background: #fdfdfd'>
		<nav class='navbar navbar-static <?php if($cfg_status && $config['dark-theme']) echo 'navbar-dark'; ?>'>
	        <a class='navbar-title' href='index.php'>ServerAuth Account Manager</a>
	        <button type='button' class='navbar-toggle'></button>
	        <?php 
	        if(isset($_SESSION["admin_login"])){
				echo "<ul class='navbar-links navbar-links-right'><li><a href='?action=logout'><i class='fa fa-sign-out'></i> Logout</a></li></ul>";
			}
	        ?>
	   	</nav>
			<?php
	 		if(!$admin_cfg_status){
	 		?>
	 	<div class='content'>
			<div class='col-2'></div>
			<div class='col-8'>
				<div class='panel'>
					<div class='panel-header'>
					    <div class='panel-title'><i class='fa fa-lock'></i> Register Admin Account</div>
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
										if($_POST["password"] == $_POST["cpassword"]){
											if(preg_match('/\s/', $_POST["password"]) == 0){
												if(strlen($_POST["password"]) <= $config["min-password-length"]){
													echo "<div class='hint out-error'>Password too short</div>";
												}elseif(strlen($_POST["password"]) >= $config["max-password-length"]){
													echo "<div class='hint out-error'>Password too long</div>";
												}else{
													$_SESSION["admin_login"] = $_POST["username"];
													$admin_cfg = fopen("admin_config.php", "w");
													$data = "<?php\n\$admin[\"username\"] = \"" . $_POST["username"] . "\";\n\$admin[\"password\"] = \"" . hash("sha256", $_POST["password"]) . "\";\n?>";
													fwrite($admin_cfg, $data);
													fclose($admin_cfg);
													$_SESSION["admin_login"] = $_POST["username"];
													header("Location: admin.php");
												}
											}else{
												echo "<div class='hint out-error'>Password can't contain spaces</div>";
											}
										}else{
											echo "<div class='hint out-error'>Password doesn't match confirmation</div>";
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
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0.</p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
				<br>
			</div>
		</div>
	 			<?php
				}else{
					if(!isset($_SESSION["admin_login"])){
				?>
		<div class='content'>
			<div class='col-3'></div>
			<div class='col-6'>
				<div class='panel'>
					<div class='panel-header'>
						<div class='panel-title'><i class='fa fa-lock'></i> Admin Login</div>
					</div>
					<div class='panel-content'>
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
										include "admin_config.php";
										if($admin["username"] == $_POST["username"] && $admin["password"] == hash("sha256", $_POST["password"])){
											$_SESSION["admin_login"] = $_POST["username"];
											header("Location: admin.php");
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
					</div>
				</div>
				<hr>
				<p class='alignment-center'>ServerAuth Account Manager v1.0.</p>
				<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
				<br>
			</div>
		</div>
				<?php
					}else{
						if(isset($_GET["action"]) && strtolower($_GET["action"]) == "logout"){
							session_destroy();
							header("Location: admin.php");
						}
				?>
				<div class='content'>
					<div class='col-2'></div>
					<div class='col-8'>
				<?php
						//Alerts
						if(!$cfg_status){
							echo "<div class='alert alert-error square'><i class='fa fa-exclamation-circle'></i> ServerAuth Account Manager is not configured yet.<br><br>Please create a configuration file or rename the existing one.</div>";
						}else{
							$api = new ServerAuthWebAPI($config["db_host"], $config["db_port"], $config["db_username"], $config["db_password"], $config["db_database"], $config["db_table_prefix"]);
							$api_status = $api->getStatus();
							if($api_status == ServerAuthWebAPI::ERR_MYSQL){
								echo "<div class='alert alert-error square'><i class='fa fa-exclamation-circle'></i> <b>MySQL Error:</b> " . $api->getDatabase()->connect_error . "</div>";
							}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_PLUGIN){
								echo "<div class='alert alert-error square'><i class='fa fa-exclamation-circle'></i> ServerAuth plugin is outdated. Please update it.</div>";
							}elseif($api_status == ServerAuthWebAPI::ERR_OUTDATED_WEBAPI){
								echo "<div class='alert alert-error square'><i class='fa fa-exclamation-circle'></i> ServerAuth Web API is outdated. Please update it.</div>";
							}
						}
						?>
						<?php echo "<div class='alert alert-info square'><i class='fa fa-info-circle'></i> Welcome, <b>" . $_SESSION["admin_login"] . "</b>.</div>";
							if(isset($_GET["action"]) && isset($_GET["account"]) && strtolower($_GET["action"]) == "delete"){
								if($api_status == ServerAuthWebAPI::SUCCESS){
									if($api->isPlayerRegistered($_GET["account"])){
										echo "<div class='alert alert-success square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-info-circle'></i> <b>" . $_GET["account"] . "</b> account deleted!</div>";
										$api->unregisterPlayer($_GET["account"]);
									}else{
										echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Account <b>" . $_GET["account"] . "</b> not found.</div>";
									}
								}else{
									echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Can't delete <b>" . $_GET["account"] . "</b>. An error has occurred.</div>";
								}
							}
							if(isset($_GET["action"]) && strtolower($_GET["action"]) == "chuserpass" && isset($_GET["user"]) && isset($_POST["new_password"])){
								if($api_status == ServerAuthWebAPI::SUCCESS){
									if($api->isPlayerRegistered($_GET["user"])){
										if(preg_match('/\s/', $_POST["new_password"]) == 0){
											if(strlen($_POST["new_password"]) <= $config["min-password-length"]){
												echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Password for <b>" . $_GET["user"] . "</b> account too short!</div>";
											}elseif(strlen($_POST["new_password"]) >= $config["max-password-length"]){
												echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Password for <b>" . $_GET["user"] . "</b> account too long!</div>";
											}else{
												echo "<div class='alert alert-success square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-info-circle'></i> Password for <b>" . $_GET["user"] . "</b> account changed to <b>" . $_POST["new_password"] . "</b></div>";
												$api->changePlayerPassword($_GET["user"], $_POST["new_password"]);
											}
										}else{
											echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Password for <b>" . $_GET["user"] . "</b> account can't contain spaces</div>";
										}
									}else{
										echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Account <b>" . $_GET["user"] . "</b> not found.</div>";
									}
								}else{
									echo "<div class='alert alert-error square'><button class='close'><span class='fa fa-close'></span></button><i class='fa fa-exclamation-circle'></i> Can't change <b>" . $_GET["user"] . "</b> account password. An error has occurred.</div>";
								}
							}
						?>
						<div class='panel' id='chpass'>
						<?php
						if(isset($_GET["action"]) && strtolower($_GET["action"]) == "chpass" && isset($_POST["old-password"]) && isset($_POST["new-password"]) && isset($_POST["cnew-password"])){
							include "admin_config.php";
							if($admin["password"] == hash("sha256", $_POST["old-password"])){
								$old_password = true;
								if($_POST["new-password"] == $_POST["cnew-password"]){
									if(preg_match('/\s/', $_POST["new-password"]) == 0){
										if(strlen($_POST["new-password"]) <= $config["min-password-length"]){
											$new_password = 2;
										}elseif(strlen($_POST["new-password"]) >= $config["max-password-length"]){
											$new_password = 3;
										}else{
											$new_password = 4;
											unlink("admin_config.php");
											$admin_cfg = fopen("admin_config.php", "w");
											$data = "<?php\n\$admin[\"username\"] = \"" . $_SESSION["admin_login"] . "\";\n\$admin[\"password\"] = \"" . hash("sha256", $_POST["new-password"]) . "\";\n?>";
											fwrite($admin_cfg, $data);
											fclose($admin_cfg);
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
								<div class='panel-title'><i class='fa fa-key'></i> Change Admin Panel Password</div>
							</div>
							<div class='panel-content'>
								<form name='chpass' action='?action=chpass#chpass' method='POST'>
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
						<div class='panel panel-primary'>
							<div class='panel-header'>
								<div class='panel-title'><i class='fa fa-users'></i> Account Manager</div>
							</div>
							<div class='panel-content'>
								<table class='table table-bordered table-selectable alignment-center' style='margin-bottom: 0'>
									<thead>
										<tr>
											<th>Username</th>
											<th>IP Address</th>
											<th>Registration date</th>
											<th>Last login</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$query = "SELECT * FROM " . $api->getTablePrefix() . "serverauthdata ORDER BY user";
									$result = $api->getDatabase()->query($query);
									$result = $result->fetch_all(MYSQLI_ASSOC);
									foreach($result as $row){
										echo "<tr><td>" . $row["user"] . "</td><td>" . $row["ip"] . "</td><td>" . date("d/m/Y H:i:s", $row["firstlogin"] / 1000) . "</td><td>" . date("d/m/Y H:i:s", $row["lastlogin"] / 1000) . "</td><td><a><i class='fa fa-trash-o' onclick='toggleModal(delete_" . $row["user"] . ")' tooltip='12ycg9'></i></a> <a><i class='fa fa-key' onclick='toggleModal(chpass_" . $row["user"] . ")' tooltip='2yfsd6'></i></a></td></tr>";
									}
									?>
									</tbody>
								</table>
							</div>
						</div>
						<hr>
						<p class='alignment-center'>ServerAuth Account Manager v1.0.</p>
						<p class='alignment-center'>&copy; 2015 <a href='http://www.evolsoft.tk'>EvolSoft</a>. Licensed under MIT.</p>
						<br>
					</div>
				</div>
					<?php
					}
				}
				foreach($result as $modal){
					echo "<div class='modal' id='delete_" . $modal["user"] . "'><div class='modal-background'></div><div class='modal-window' style='min-width: 555px'><button class='close'></button><h2 style='margin-top: 0'>Are you sure you want to delete " . $modal["user"] . " Account?</h2><br><div class='row'><div class='col-rs-9 alignment-right'><button class='button' onclick='closeModal(\$(this).parent().parent().parent().parent())'>Cancel</button></div><div class='col-rs-3 ns alignment-right'><a class='button button-error full-width alignment-center' href='?action=delete&account=" . $modal["user"] . "'>Delete Account</a></div></div></div></div>";
					echo "<div class='modal' id='chpass_" . $modal["user"] . "'><div class='modal-background'></div><div class='modal-window' style='min-width: 555px'><button class='close'></button><h2 style='margin-top: 0'>Change " . $modal["user"] . " account password</h2><br><form name='chuserpass_" . $modal["user"] . "' action='?action=chuserpass&user=" . $modal["user"] . "' method='POST'><div class='form'><div class='col-rs-3'><label class='form-label' style='margin-left: -5px'>New password:</label></div><div class='col-rs-9 ns'><input type='password' class='input' name='new_password'></div></div><div class='row'><div class='col-rs-8 alignment-right'><button class='button' onclick='closeModal($(this).parent().parent().parent().parent())'>Cancel</button></div><div class='col-rs-4 ns alignment-right'><input type='submit' class='button button-primary full-width alignment-center' value='Change Password'></div></div></form></div></div>";
				}
				echo "<div class='tooltip small tooltip-top' tooltip-id='12ycg9'><div class='tooltip-arrow'></div><div class='tooltip-content'>Delete account</div></div>";
				echo "<div class='tooltip small tooltip-top' tooltip-id='2yfsd6'><div class='tooltip-arrow'></div><div class='tooltip-content'>Change account password</div></div>";
				?>
	</body>
</html>
