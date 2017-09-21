<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 12/01/2016 07:37 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;

use ServerAuth\ServerAuth;
use ServerAuth\Tasks\MySQLTask;

class Commands extends PluginBase implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "serverauth":
    			if(isset($args[0])){
    				$args[0] = strtolower($args[0]);
    				if($args[0] == "help"){
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
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    						break;
    					}
    				}elseif($args[0]=="info"){
    					if($sender->hasPermission("serverauth.info")){
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bServerAuth &av" . ServerAuth::VERSION . " &bdeveloped by&a " . ServerAuth::PRODUCER));
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bWebsite &a" . ServerAuth::MAIN_WEBSITE));
    				        break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    						break;
    					}
    				}elseif($args[0]=="reload"){
    					if($sender->hasPermission("serverauth.reload")){
    						$this->plugin->reloadConfig();
    						$this->cfg = $this->plugin->getConfig()->getAll();
    						$this->plugin->chlang = ServerAuth::getAPI()->getConfigLanguage()->getAll();
    						//Restart MySQL
    						ServerAuth::getAPI()->task->cancel();
    						$this->plugin->task = $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLTask($this->plugin), 20);
    						$this->plugin->mysql = false;
    						//Check MySQL
    						if($this->cfg["use-mysql"] == true){
    							$check = $this->plugin->checkDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"]);
    							if($check[0]){
    								$this->plugin->initializeDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"], $this->cfg["mysql"]["database"], $this->cfg["mysql"]["table_prefix"]);
    								Server::getInstance()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["mysql-success"]));
    								$this->mysql = true;
    							}else{
    								Server::getInstance()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->replaceArrays($this->plugin->chlang["mysql-fail"], array("MYSQL_ERROR" => $check[1]))));
    							}
    						}
    						//End MySQL Restart
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["config-reloaded"]));
    				        break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    						break;
    					}
    				}else{
    					if($sender->hasPermission("serverauth")){
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . ServerAuth::getAPI()->replaceArrays($this->plugin->chlang["help"]["error"], array("SUBCMD" => $args[0]))));
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    						break;
    					}
    				}
    				}else{
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
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", $this->plugin->chlang["errors"]["no-permissions"]));
    						break;
    					}
    				}
    			}
    			return true;
    	}
}
?>
