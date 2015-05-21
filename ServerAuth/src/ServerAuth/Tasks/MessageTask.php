<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 14/05/2015 04:45 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Tasks;

use pocketmine\scheduler\PluginTask;

use ServerAuth\ServerAuth;

class MessageTask extends PluginTask {
	
    public function __construct(ServerAuth $plugin){
    	parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->plugin = $this->getOwner();
        $this->players = array();
    }
    
    public function onRun($tick){
    	$cfg = $this->plugin->getConfig()->getAll();
    	foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($player->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($player)){
    			if(!isset($this->players[strtolower($player->getName())])){
    				$this->players[strtolower($player->getName())]["interval"] = 0;
    				$this->players[strtolower($player->getName())]["kick"] = 0;
    			}
    		}
    		if(!ServerAuth::getAPI()->isPlayerRegistered($player->getName())){
    			$this->players[strtolower($player->getName())]["interval"] += 1;
    			$this->players[strtolower($player->getName())]["kick"] += 1;
    			if($this->players[strtolower($player->getName())]["interval"] >= $cfg["register"]["message-interval"]){
    				if(ServerAuth::getAPI()->areRegisterMessagesEnabled()){
    					$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["message"]));
    				}
    				$this->players[strtolower($player->getName())]["interval"] = 0;
    			}
    			if($this->players[strtolower($player->getName())]["kick"] >= $cfg["timeout"]){
    				$player->close("", $this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["register-timeout"]));
    				unset($this->players[strtolower($player->getName())]);
    			}
    		}else{
    			if(!$this->plugin->isPlayerAuthenticated($player)){
    				$this->players[strtolower($player->getName())]["interval"] += 1;
    				$this->players[strtolower($player->getName())]["kick"] += 1;
    				if($this->players[strtolower($player->getName())]["interval"] >= $cfg["login"]["message-interval"]){
    					if(ServerAuth::getAPI()->areLoginMessagesEnabled()){
    						$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["message"]));
    					}
    					$this->players[strtolower($player->getName())]["interval"] = 0;
    				}
    				if($this->players[strtolower($player->getName())]["kick"] >= $cfg["timeout"]){
    					$player->close("", $this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["login-timeout"]));
    					unset($this->players[strtolower($player->getName())]);
    				}
    			}else{
    				unset($this->players[strtolower($player->getName())]);
    			}
    		}
    	}
    	foreach($this->players as $key => $player){
    		if($this->plugin->getServer()->getPlayer($key) == null){
    			$this->players[$key]["kick"] = 0;
    		}
    	}
    }
}
?>
