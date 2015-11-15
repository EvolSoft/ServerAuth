<?php

/*
 * ServerAuth (v2.11) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 31/08/2015 11:09 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use ServerAuth\ServerAuth;

class Unregister implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "unregister":
    			if($sender->hasPermission("serverauth.unregister")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if unregister is enabled
    					if($cfg["unregister"]["enabled"]){
    						if(ServerAuth::getAPI()->isPlayerAuthenticated($sender)){
    							//Check if password is required
    							if($cfg["unregister"]["require-password"]){
    							    //Check args
    								if(count($args) == 1){
    									if(hash(ServerAuth::getAPI()->getPasswordHash(), $args[0]) == ServerAuth::getAPI()->getPlayerData($sender->getName())["password"]){
    										$status = ServerAuth::getAPI()->unregisterPlayer($sender);
    										if($status == ServerAuth::SUCCESS){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["unregister-success"]));
    										}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered"]));
    										}elseif($status == ServerAuth::CANCELLED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    										}else{
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    										}
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["wrong-password"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["command"]));
    								}
    							}else{
    								$status = ServerAuth::getAPI()->unregisterPlayer($sender);
    								if($status == ServerAuth::SUCCESS){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["unregister-success"]));
    								}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered"]));
    								}elseif($status == ServerAuth::CANCELLED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    								}
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["login-required"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					if(isset($args[0])){
    						$status = ServerAuth::getAPI()->unregisterPlayer($this->plugin->getServer()->getOfflinePlayer($args[0]));
    						if($status == ServerAuth::SUCCESS){
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["unregister-success-3rd"]));
    						}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered-3rd"]));
    						}elseif($status == ServerAuth::CANCELLED){
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->getConfigLanguage()->getAll()["unregister"]["command-cons"]));
    					}
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
