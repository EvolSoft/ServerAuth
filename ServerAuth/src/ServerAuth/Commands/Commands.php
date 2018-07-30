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
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

use ServerAuth\ServerAuth;

class Commands implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        if(isset($args[0])){
        	$args[0] = strtolower($args[0]);
        	switch($args[0]){
        	    case "help":
        	        goto help;
        	    case "import":
        	        if($sender->hasPermission("serverauth.import")){
        	            if(isset($args[1])){
        	                $imp = $this->plugin->getImpoter($args[1]);
        	                if($imp){
        	                    $imp->export($sender, array_slice($args, 2));
        	                    break;
        	                }
        	                $sender->sendMessage($this->plugin->translateColors("&", "&cInvalid importer specified!"));
        	                break;
        	            }
        	            $sender->sendMessage($this->plugin->translateColors("&", "&cPlease specify an importer!"));
        	            break;
        	        }
        	        $sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
        	        break;
        	    case "info":
            		if($sender->hasPermission("serverauth.info")){
            			$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bServerAuth &av" . $this->plugin->getDescription()->getVersion() . " &bdeveloped by &aEvolSoft"));
            			$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bWebsite &a" . $this->plugin->getDescription()->getWebsite()));
            	        break;
            		}
        			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
        			break;
        	    case "reload":
            		if($sender->hasPermission("serverauth.reload")){
            			$this->plugin->reloadConfig();
            			$this->cfg = $this->plugin->getConfig()->getAll();
            			$this->plugin->chlang = ServerAuth::getAPI()->getConfigLanguage()->getAll();
            			$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["config-reloaded"]));
            	        break;
            		}
        			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
        			break;		
        	    default:
            		if($sender->hasPermission("serverauth")){
            			$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["help"]["invalid"], array("SUBCMD" => $args[0]))));
            			break;
            		}
        			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
        			break;
        	}
        	return true;
    	}
    	help:
		if($sender->hasPermission("serverauth.help")){
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["1"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["2"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["3"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["4"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["5"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["6"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["7"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["8"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["9"]));
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["help"]["10"]));
		}else{
			$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
		}
        return true;
    }
}