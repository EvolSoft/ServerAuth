<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 17/01/2016 01:10 PM (UTC)
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

class ChangePassword implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "changepassword":
    			if($sender->hasPermission("serverauth.changepassword")){
    				$cfg = $this->plugin->getConfig()->getAll();
    				//Player Sender
    				if($sender instanceof Player){
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
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["changepassword-success"]));
    										}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["user-not-registered"]));
    										}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-short"]));
    										}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-long"]));
    										}elseif($status == ServerAuth::CANCELLED){
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    										}else{
    											$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    										}
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-no-match"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["command-conf"]));
    								}
    							}else{
    								//Check args
    								if(count($args) == 1){
    									$status = ServerAuth::getAPI()->changePlayerPassword($sender, $args[0]);
    									if($status == ServerAuth::SUCCESS){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["changepassword-success"]));
    									}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["changepassword-success"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["password-too-short"]));
    									}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-long"]));
    									}elseif($status == ServerAuth::CANCELLED){
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    									}else{
    										$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    									}
    								}else{
    									$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["command"]));
    								}
    							}
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["login-required"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["disabled"]));
    					}
    					break;
    				}else{ //Console Sender
    					if(count($args) == 2){
    						$status = ServerAuth::getAPI()->changePlayerPassword($this->plugin->getServer()->getOfflinePlayer($args[0]), $args[1]);
    						if($status == ServerAuth::SUCCESS){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->replaceArrays($this->plugin->chlang["changepassword"]["changepassword-cons-success"], array("PLAYER" => $args[0], "PASSWORD" => $args[1]))));
    						}elseif($status == ServerAuth::ERR_USER_NOT_REGISTERED){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["user-not-registered-3rd"]));
    						}elseif($status == ServerAuth::ERR_PASSWORD_TOO_SHORT){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-short"]));
    						}elseif($status == ServerAuth::ERR_PASSWORD_TOO_LONG){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["password-too-long"]));
    						}elseif($status == ServerAuth::CANCELLED){
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getCancelledMessage()));
    						}else{
    							$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["errors"]["generic"]));
    						}
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["changepassword"]["command-cons"]));
    					}
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
