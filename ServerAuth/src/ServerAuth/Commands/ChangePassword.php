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

class ChangePassword implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "changepassword":
    			if($sender->hasPermission("serverauth.changepassword")){
    				//Player Sender
    				if($sender instanceof Player){
    					$cfg = $this->plugin->getConfig()->getAll();
    					//Check if changepassword is enabled
    					if($cfg["changepassword"]["enabled"]){
    						if(ServerAuth::getAPI()->isPlayerAuthenticated($sender)){
    							//Check confirm password
    							if($cfg["changepassword"]["password-confirm-required"]){
    								//Check args
    								if(count($args) == 2){
    									if($args[0] == $args[1]){
    										$status = ServerAuth::getAPI()->changePlayerPassword($sender, $args[0]);
    										if($status == ServerAuth::SUCCESS){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["changepassword-success"]));
    										}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["user-not-registered"]));
    										}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-short"]));
    										}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-long"]));
    										}else{
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    										}
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-no-match"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["command-conf"]));
    								}
    							}else{
    								//Check args
    								if(count($args) == 1){
    									$status = ServerAuth::getAPI()->changePlayerPassword($sender, $args[0]);
    									if($status == ServerAuth::SUCCESS){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["changepassword-success"]));
    									}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["changepassword-success"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["password-too-short"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["password-too-long"]));
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["errors"]["generic"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["command"]));
    								}
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["login-required"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["changepassword"]["disabled"]));
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
