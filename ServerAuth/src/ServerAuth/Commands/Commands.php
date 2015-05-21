<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 10/05/2015 12:14 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
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

class Commands extends PluginBase implements CommandExecutor {

	public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    		case "serverauth":
    			if(isset($args[0])){
    				$args[0] = strtolower($args[0]);
    				if($args[0]=="help"){
    					if($sender->hasPermission("serverauth.help")){
    					    $sender->sendMessage($this->plugin->translateColors("&", "&b=> &aAvailable Commands &b<="));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/changepassword &b=>&a Change the account password"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/login &b=>&a Do login"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/logout &b=>&a Do logout"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/register &b=>&a Register an account"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth help &b=>&a Show help about this plugin"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth info &b=>&a Show info about this plugin"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth reload &b=>&a Reload the config"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/unregister &b=>&a Unregister your account"));
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    						break;
    					}
    				}elseif($args[0]=="info"){
    					if($sender->hasPermission("serverauth.info")){
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bServerAuth &av" . ServerAuth::VERSION . " &bdeveloped by&a " . ServerAuth::PRODUCER));
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&bWebsite &a" . ServerAuth::MAIN_WEBSITE));
    				        break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    						break;
    					}
    				}elseif($args[0]=="reload"){
    					if($sender->hasPermission("serverauth.reload")){
    						$this->plugin->reloadConfig();
    						$sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . "&aConfiguration Reloaded."));
    				        break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    						break;
    					}
    				}else{
    					if($sender->hasPermission("serverauth")){
    						$sender->sendMessage($this->plugin->translateColors("&",  ServerAuth::PREFIX . "&cSubcommand &a" . $args[0] . " &cnot found. Use &a/serverauth help &cto show available commands"));
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    						break;
    					}
    				}
    				}else{
    					if($sender->hasPermission("serverauth.help")){
    						$sender->sendMessage($this->plugin->translateColors("&", "&b=> &aAvailable Commands &b<="));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/changepassword &b=>&a Change the account password"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/login &b=>&a Do login"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/logout &b=>&a Do logout"));
    					    $sender->sendMessage($this->plugin->translateColors("&", "&a/register &b=>&a Register an account"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth help &b=>&a Show help about this plugin"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth info &b=>&a Show info about this plugin"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/serverauth reload &b=>&a Reload the config"));
    						$sender->sendMessage($this->plugin->translateColors("&", "&a/unregister &b=>&a Unregister your account"));
    						break;
    					}else{
    						$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    						break;
    					}
    				}
    			}
    	}
}
?>
