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

class Logout extends ServerAuthCommand {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
    	if($sender->hasPermission("serverauth.logout")){
    		if($sender instanceof Player){
    			$cmessage = null;
    			if($this->plugin->isLogoutEnabled()){
    				switch($this->plugin->deauthenticatePlayer($sender, $cmessage)){
    				    case ServerAuth::SUCCESS:
    				        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["logout"]["logout-success"]));
    				        break;
    				    case ServerAuth::ERR_NOT_REG:
    				        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-registered"]));
    				        break;
    				    case ServerAuth::ERR_NOT_AUTH:
    				        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-authenticated"]));
    				        break;
    				    case ServerAuth::CANCELLED:
    				        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $cmessage));
    				        break;
    				    default:
    				        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["generic"]));
    				        break;
    				}
    			}else{
    				$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["logout"]["disabled"]));
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
	    return ServerAuth::CMD_LOGOUT;
	}
}