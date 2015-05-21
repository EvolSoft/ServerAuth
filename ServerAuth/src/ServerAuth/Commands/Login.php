<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 10/05/2015 12:14 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\permission\Permission;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

use pocketmine\utils\TextFormat;

use ServerAuth\ServerAuth;

class Login implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "login":
    			if($sender->hasPermission("serverauth.login")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if login is enabled
    					if($cfg["login"]["enabled"]){
    						//Check args
    						if(count($args) == 1){
    							$status = ServerAuth::getAPI()->authenticatePlayer($sender, $args[0]);
    							if($status == ServerAuth::SUCCESS){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["login-success"]));
    							}elseif($status == ServerAuth::ERR_WRONG_PASSWORD){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["wrong-password"]));
    							}elseif($status == ServerAuth::ERR_USER_ALREADY_AUTHENTICATED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["already-login"]));
    							}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered"]));
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["command"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&cYou can only perform this command as a player"));
    					break;
    				}
    			}else{
    				$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    				break;
    			}
    			return true;
    			}
    	}
}
?>
