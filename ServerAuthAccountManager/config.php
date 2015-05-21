<?php
//Enable or disable dark theme on navbar
$config["dark-theme"] = true;
//Set navbar title
$config["title"] = "ServerAuth Account Manager";
//Set navbar link
$config["title_link"] = "index.php";
//Enable this function to allow people to register their accounts from here
$config["allow-register"] = true;
//Enable this function to allow people to show their account infos
$config["allow-show-info"] = true;
//Set minimum password length
$config["min-password-length"] = 6;
//Set maximum password length
$config["max-password-length"] = 16;
//Set main description (shown on index.php page
$config["main_description"] = "<h2>ServerAuth Account Manager</h2><br><p>ServerAuth Account Manager is a simple web script that let you to fully manage your ServerAuth accounts.<br>With ServerAuth Account Manager you can:</p><br><ul><li>Manage all registered accounts (admin-only)</li><li>Show your ServerAuth account info</li><li>Change your account password</li><li>Delete your account</li></ul>";
//MySQL host of ServerAuth plugin data
$config["db_host"] = "host";
//MySQL port (default 3306)
$config["db_port"] = 3306;
//MySQL username
$config["db_username"] = "username";
//MySQL password (you can leave it blank if your database doesn't need password)
$config["db_password"] = "";
//MySQL ServerAuth database (it must be the one where you stored your ServerAuth plugin data)
$config["db_database"] = "serverauth";
//ServerAuth table prefix (the same of your ServerAuth plugin config)
$config["db_table_prefix"] = "srvauth_";
?>
