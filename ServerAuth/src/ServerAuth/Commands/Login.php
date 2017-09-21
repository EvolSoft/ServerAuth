<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 15/01/2016 06:41 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
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
    
public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
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
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["login"]["login-success"]));
    							}elseif($status == ServerAuth::ERR_WRONG_PASSWORD){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["wrong-password"]));
    							}elseif($status == ServerAuth::ERR_USER_ALREADY_AUTHENTICATED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["login"]["already-login"]));
    							}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["user-not-registered"]));
    							}elseif($status == ServerAuth::CANCELLED){
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["login"]["command"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["login"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["player-only"]));
    					break;
    				}
    			}else{
    				$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    				break;
    			}
    			return true;
    		}
    		return true;
    	}
}
?>
