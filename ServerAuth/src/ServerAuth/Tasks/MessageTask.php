<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Tasks;

use pocketmine\scheduler\PluginTask;

use ServerAuth\ServerAuth;

class MessageTask extends PluginTask {
    
    /** @var array */
    private $players = array();
	
    public function __construct(ServerAuth $plugin){
    	parent::__construct($plugin);
    }
    
    public function onRun(int $tick){
        $plugin = $this->getOwner();
    	foreach($plugin->getServer()->getOnlinePlayers() as $player){
    	    if(!$plugin->isPlayerAuthenticated($player)){
    	        if(!isset($this->players[strtolower($player->getName())])){
    				$this->players[strtolower($player->getName())]["interval"] = 0;
    				$this->players[strtolower($player->getName())]["kick"] = 0;
    	        }else{
    	            $this->players[strtolower($player->getName())]["interval"]++;
    	            $this->players[strtolower($player->getName())]["kick"]++;
    	        }
    		}
    		if(!$plugin->isAccountRegistered($player->getName())){
    		    if($this->players[strtolower($player->getName())]["interval"] >= $plugin->cfg["register"]["message-interval"]){
    		        if($plugin->isRegisterMessageEnabled()){
    		            $plugin->callRegisterMessageHandler($plugin, $player);
    		        }
    		        $this->players[strtolower($player->getName())]["interval"] = 0;
    		    }
    		    if($this->players[strtolower($player->getName())]["kick"] >= $plugin->cfg["timeout"]){
    		        $player->close("", $plugin->translateColors("&", $plugin->chlang["register"]["register-timeout"]));
    		        unset($this->players[strtolower($player->getName())]);
    		    }
    		}else if(!$plugin->isPlayerAuthenticated($player)){
				if($this->players[strtolower($player->getName())]["interval"] >= $plugin->cfg["login"]["message-interval"]){
					if($plugin->isLoginMessageEnabled()){
					    $plugin->callLoginMessageHandler($plugin, $player);
					}
					$this->players[strtolower($player->getName())]["interval"] = 0;
				}
				if($this->players[strtolower($player->getName())]["kick"] >= $plugin->cfg["timeout"]){
					$player->close("", $plugin->translateColors("&", $plugin->chlang["login"]["login-timeout"]));
					unset($this->players[strtolower($player->getName())]);
				}
    		}else if(isset($this->players[strtolower($player->getName())])){
				unset($this->players[strtolower($player->getName())]);
			}
    	}
    }
}