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

class Register extends ServerAuthCommand {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		if($sender->hasPermission("serverauth.register")){
			if($sender instanceof Player){
				if($this->plugin->isRegisterEnabled()){
					if($this->plugin->cfg["register"]["confirm-required"]){
						if(count($args) == 2){
							if($args[0] == $args[1]){
							    goto register;
							}else{
							    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-no-match"]));
							}
						}else{
						    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["register"]["command-conf"]));
						}
					}else if(count($args) == 1){
					    register:
					    $cmessage = null;
					    switch($this->plugin->registerAccount($sender, $args[0], $cmessage)){
					        case ServerAuth::SUCCESS:
					            ServerAuth::getAPI()->authenticatePlayer($sender, $args[0]);
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["register"]["register-success"]));
					            break;
					        case ServerAuth::ERR_ALREADY_REG:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["register"]["already-registered"]));
					            break;
					        case ServerAuth::ERR_PASS_SHORT:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-short"]));
					            break;
					        case ServerAuth::ERR_PASS_LONG:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["password-too-long"]));
					            break;
					        case ServerAuth::ERR_MAX_IP:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["max-ip-reached"]));
					            break;
					        case ServerAuth::ERR_IO:
					            break;
					        case ServerAuth::CANCELLED:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $cmessage));
					            break;
					        default:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["generic"]));
					            break;
					    }
					}else{
					    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["register"]["command"]));
					}
				}else{
				    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["register"]["disabled"]));
				}
			}else{
				$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["player-only"]));
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
	    return ServerAuth::CMD_REGISTER;
	}
}