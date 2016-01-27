<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 17/01/2016 07:02 PM (UTC)
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

class Register implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "register":
    			if($sender->hasPermission("serverauth.register")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if register is enabled
    					if($cfg["register"]["enabled"]){
    						//Check confirm password
    						if($cfg["register"]["password-confirm-required"]){
    							//Check args
    							if(count($args) == 2){
    								if($args[0] == $args[1]){
    									$status = ServerAuth::getAPI()->registerPlayer($sender, $args[0]);
    									if($status == ServerAuth::SUCCESS){
    										ServerAuth::getAPI()->authenticatePlayer($sender, $args[0]);
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["register-success"]));
    									}elseif($status == ServerAuth::ERR_USER_ALREADY_REGISTERED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["already-registered"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-short"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-long"]));
    									}elseif($status == ServerAuth::ERR_MAX_IP_REACHED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["max-ip-reached"]));
    									}elseif($status == ServerAuth::CANCELLED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-no-match"]));
    								}
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["command-conf"]));
    							}
    						}else{
    							//Check args
    							if(count($args) == 1){
    								$status = ServerAuth::getAPI()->registerPlayer($sender, $args[0]);
    								if($status == ServerAuth::SUCCESS){
    									ServerAuth::getAPI()->authenticatePlayer($sender, $args[0]);
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["register-success"]));
    								}elseif($status == ServerAuth::ERR_USER_ALREADY_REGISTERED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["already-registered"]));
    								}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-short"]));
    								}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-long"]));
    								}elseif($status == ServerAuth::ERR_MAX_IP_REACHED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["max-ip-reached"]));
    								}elseif($status == ServerAuth::CANCELLED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    								}
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["command"]));
    							}
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["register"]["disabled"]));
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
    	}
}
?>