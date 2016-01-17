<?php

/*
 * ServerAuth (v2.12) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 16/01/2016 08:13 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
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
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["unregister-success"]));
    										}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["user-not-registered"]));
    										}elseif($status == ServerAuth::CANCELLED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    										}else{
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    										}
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["wrong-password"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["command"]));
    								}
    							}else{
    								$status = ServerAuth::getAPI()->unregisterPlayer($sender);
    								if($status == ServerAuth::SUCCESS){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["unregister-success"]));
    								}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["user-not-registered"]));
    								}elseif($status == ServerAuth::CANCELLED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    								}
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["login-required"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					if(isset($args[0])){
    						$player = $this->plugin->getServer()->getPlayer($args[0]);
    						if(!$player instanceof Player){
    							$player = $args[0];
    						}
    						$status = ServerAuth::getAPI()->unregisterPlayer($player);
    						if($status == ServerAuth::SUCCESS){
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["unregister"]["unregister-success-3rd"]));
    						}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["user-not-registered-3rd"]));
    						}elseif($status == ServerAuth::CANCELLED){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["generic"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["unregister"]["command-cons"]));
    					}
    					break;
    				}
    			}else{
    				$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    				break;
    			}
    			return true;
    			}
    	}
}
?>
