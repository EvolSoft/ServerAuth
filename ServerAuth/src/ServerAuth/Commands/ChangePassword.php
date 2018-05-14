<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use ServerAuth\ServerAuth;

class ChangePassword extends ServerAuthCommand {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
		if($sender->hasPermission("serverauth.changepassword")){
			if($sender instanceof Player){
				if($this->plugin->isChangePasswordEnabled()){
					if($this->plugin->isPlayerAuthenticated($sender)){
						if($this->plugin->cfg["changepassword"]["confirm-required"]){
							if(count($args) == 2){
								if($args[0] == $args[1]){
									goto chpassw;
								}else{
								    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-no-match"]));
								}
							}else{
							    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["command-conf"]));
							}
						}else if(count($args) == 1){
								chpassw:
								$cmessage = null;
								switch($this->plugin->changeAccountPassword($sender->getName(), $args[0], $cmessage)){
								    case ServerAuth::SUCCESS:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["changepassword-success"]));
								        break;
								    case ServerAuth::ERR_NOT_REG:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-registered"]));
								        break;
								    case ServerAuth::ERR_PASS_SHORT:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-short"]));
								        break;
								    case ServerAuth::ERR_PASS_LONG:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-long"]));
								        break;
								    case ServerAuth::CANCELLED:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $cmessage));
								        break;
								    case ServerAuth::ERR_IO:
								        break;
								    default:
								        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["generic"]));
								        break;
								}
						}else{
						    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["command"]));
						}
					}else{
					    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["login-required"]));
					}
				}else{
				    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["disabled"]));
				}
			}else if(count($args) == 2){
			    $cmessage = null;
			    switch($this->plugin->changeAccountPassword($this->plugin->getServer()->getOfflinePlayer($args[0])->getName(), $args[1], $cmessage)){
			        case ServerAuth::SUCCESS:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->replaceVars($this->plugin->chlang["changepassword"]["changepassword-cons-success"], array("PLAYER" => $args[0], "PASSWORD" => $args[1]))));
			            break;
			        case ServerAuth::ERR_NOT_REG:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-registered-3rd"]));
			            break;
			        case ServerAuth::ERR_PASS_SHORT:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-short"]));
			            break;
			        case ServerAuth::ERR_PASS_LONG:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-long"]));
			            break;
			        case ServerAuth::CANCELLED:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $cmessage));
			            break;
			        case ServerAuth::ERR_IO:
			            break;
			        default:
			            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["generic"]));
			            break;
			    }
			}else{
			    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["changepassword"]["command-cons"]));
			}
		}else{
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
		}
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \ServerAuth\Commands\ServerAuthCommand::getType()
	 */
	public function getType() : int {
	    return ServerAuth::CMD_CHPASSW;
	}
}