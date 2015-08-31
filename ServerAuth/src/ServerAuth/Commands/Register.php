<?php

/*
 * ServerAuth (v2.10) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 31/08/2015 11:03 AM (UTC)
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
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["register-success"]));
    									}elseif($status == ServerAuth::ERR_USER_ALREADY_REGISTERED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["already-registered"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-short"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-long"]));
    									}elseif($status == ServerAuth::ERR_MAX_IP_REACHED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["max-ip-reached"]));
    									}elseif($status == ServerAuth::CANCELLED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-no-match"]));
    								}
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["command-conf"]));
    							}
    						}else{
    							//Check args
    							if(count($args) == 1){
    								$status = ServerAuth::getAPI()->registerPlayer($sender, $args[0]);
    								if($status == ServerAuth::SUCCESS){
    									ServerAuth::getAPI()->authenticatePlayer($sender, $args[0]);
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["register-success"]));
    								}elseif($status == ServerAuth::ERR_USER_ALREADY_REGISTERED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["already-registered"]));
    								}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-short"]));
    								}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-long"]));
    								}elseif($status == ServerAuth::ERR_MAX_IP_REACHED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["max-ip-reached"]));
    								}elseif($status == ServerAuth::CANCELLED){
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["operation-cancelled"]));
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    								}
    							}else{
    								$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["command"]));
    							}
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["disabled"]));
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