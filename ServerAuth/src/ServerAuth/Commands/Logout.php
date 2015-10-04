<?php

/*
 * ServerAuth (v2.11) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 31/08/2015 10:56 AM (UTC)
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

class Logout implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "logout":
    			if($sender->hasPermission("serverauth.logout")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if logout is enabled
    					if($cfg["logout"]["enabled"]){
    						$status = ServerAuth::getAPI()->deauthenticatePlayer($sender);
    						if($status == ServerAuth::SUCCESS){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["logout"]["logout-success"]));
    						}elseif($status == ServerAuth::ERR_USER_NOT_AUTHENTICATED){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-authenticated"]));
    						}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered"]));
    						}elseif($status == ServerAuth::CANCELLED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["logout"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["in-game"]));
    					break;
    				}
    			}else{
    				$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["no-permission"]));
    				break;
    			}
    			return true;
    			}
    	}
}
?>
