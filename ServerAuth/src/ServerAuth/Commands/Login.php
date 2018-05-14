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

class Login extends ServerAuthCommand {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		if($sender->hasPermission("serverauth.login")){
			if($sender instanceof Player){
				if($this->plugin->isLoginEnabled()){
					if(count($args) == 1){
					    $cmessage = null;
					    /*if($this->plugin->cfg["log-multiple-accounts"]){ //TODO in future versions
					        foreach($this->getServer()->getOnlinePlayers() as $pl){
					            if($pl->isOp()){
					                $pl->sendMessage($this->plugin->translateColors("&", $sender->getName() . " has (n) accounts: "));
					            }
					        }
					    }*/
					    switch($this->plugin->authenticatePlayer($sender, $args[0], false, $cmessage)){
					        case ServerAuth::SUCCESS:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["login"]["login-success"]));
					            break;
					        case ServerAuth::ERR_WRONG_PASS:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["wrong-password"]));
					            break;
					        case ServerAuth::ERR_ALREADY_AUTH:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix(). $this->plugin->chlang["login"]["already-login"]));
					            break;
					        case ServerAuth::ERR_NOT_REG:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-registered"]));
					            break;
					        case ServerAuth::ERR_TOO_MANY_ATTEMPTS:
					            $sender->close("", $this->plugin->translateColors("&", $this->plugin->chlang["login"]["too-many-attempts"]));
					            $this->plugin->resetAuthAttempts($sender);
					            break;
					        case ServerAuth::CANCELLED:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $cmessage));
					            break;
					        default:
					            $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["generic"]));
					            break;
					    }
					}else{
					    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["login"]["command"]));
					}
				}else{
				    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["login"]["disabled"]));
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
	    return ServerAuth::CMD_LOGIN;
	}
}