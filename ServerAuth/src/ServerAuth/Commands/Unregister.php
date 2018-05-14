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

class Unregister extends ServerAuthCommand {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		if($sender->hasPermission("serverauth.unregister")){
			if($sender instanceof Player){
				if($this->plugin->isUnregisterEnabled()){
					if($this->plugin->isPlayerAuthenticated($sender)){
						if($this->plugin->cfg["unregister"]["require-password"]){
							if(count($args) == 1){
							    $pdata = $this->plugin->getPlayerData($sender->getName());
							    $hashalg = isset($pdata["hashalg"]) ? $this->plugin->getHashAlgById($pdata["hashalg"]) : $this->plugin->getHashAlg();
							    if(!$hashalg){
							        $hashalg = $this->plugin->getHashAlg();
							    }
							    $params = isset($pdata["hashparams"]) ? $pdata["hashparams"] : $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
						        if($this->plugin->hashPassword($args[0], $hashalg, $params . ",player:" . $sender->getName()) == $pdata["password"]){
									goto unregister;
								}else{
									$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["wrong-password"]));
								}
							}else{
							    $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["unregister"]["command"]));
							}
						}else{
							unregister:
							$cmessage = null;
							switch($this->plugin->unregisterAccount($sender->getName(), $cmessage)){
							    case ServerAuth::SUCCESS:
							        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["unregister"]["unregister-success"]));
							        break;
							    case ServerAuth::ERR_NOT_REG:
							        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["errors"]["user-not-registered"]));
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
						}
					}else{
						$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["login-required"]));
					}
				}else{
					$sender->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . $this->plugin->chlang["unregister"]["disabled"]));
				}
			}else if(isset($args[0])){
					$cmessage = null;
					switch($this->plugin->unregisterAccount($args[0], $cmessage)){
					    case ServerAuth::SUCCESS:
					        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["unregister"]["unregister-success-3rd"]));
					        break;
					    case ServerAuth::ERR_NOT_REG:
					        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["user-not-registered-3rd"]));
					        break;
					    case ServerAuth::CANCELLED:
					        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $cmessage));
					        break;
					    case ServerAuth::ERR_IO:
					        break;
					    default:
					        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["generic"]));
					        break;
					}
			}else{
				$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["unregister"]["command-cons"]));
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
	    return ServerAuth::CMD_UNREGISTER;
	}
}