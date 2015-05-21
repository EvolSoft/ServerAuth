<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 14/05/2015 05:19 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

use ServerAuth\ServerAuth;

class MySQLTask extends PluginTask {
	
    public function __construct(ServerAuth $plugin){
    	parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->plugin = $this->getOwner();
    }
    
    public function onRun($tick){
    	$cfg = $this->plugin->getConfig()->getAll();
    	//Check MySQL
    	if($cfg["use-mysql"] == true){
    		if(ServerAuth::getAPI()->getDatabase() == false){
    			$check = ServerAuth::getAPI()->checkDatabase($cfg["mysql"]["host"], $cfg["mysql"]["port"], $cfg["mysql"]["username"], $cfg["mysql"]["password"]);
    			if($check[0]){
    				Server::getInstance()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . "&aMySQL connection restored!"));
    				ServerAuth::getAPI()->initializeDatabase($cfg["mysql"]["host"], $cfg["mysql"]["port"], $cfg["mysql"]["username"], $cfg["mysql"]["password"], $cfg["mysql"]["database"], $cfg["mysql"]["table_prefix"]);
    				ServerAuth::getAPI()->mysql = true;
    			}
    		}elseif(!ServerAuth::getAPI()->getDatabase()->ping()){
    			$check = ServerAuth::getAPI()->checkDatabase($cfg["mysql"]["host"], $cfg["mysql"]["port"], $cfg["mysql"]["username"], $cfg["mysql"]["password"]);
    			if($check[0]){
    				Server::getInstance()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . "&aMySQL connection restored!"));
    				ServerAuth::getAPI()->initializeDatabase($cfg["mysql"]["host"], $cfg["mysql"]["port"], $cfg["mysql"]["username"], $cfg["mysql"]["password"], $cfg["mysql"]["database"], $cfg["mysql"]["table_prefix"]);
    				ServerAuth::getAPI()->mysql = true;
    			}
    		}
    	}else{
    		ServerAuth::getAPI()->mysql = false;
    	}
    }
}
?>
