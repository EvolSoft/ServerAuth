<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class EventListener implements Listener {
	
	public function __construct(ServerAuth $plugin){
		$this->plugin = $plugin;
	}
	
	/**
	 * @param PlayerPreLoginEvent $event
	 */
	public function onPreLogin(PlayerPreLoginEvent $event){
	    $player = $event->getPlayer();
		if($this->plugin->cfg["force-single-auth"]){
			foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
				if(strtolower($pl->getName()) == strtolower($player->getName())){
					$player->close("", $this->plugin->translateColors("&", $this->plugin->chlang["single-auth"]), false);
					$event->setCancelled(true);
					return;
				}
			}
			if($this->plugin->cfg["uuid-login"] && $this->plugin->isAccountRegistered($player->getName())){
			    $pdata = $this->plugin->getPlayerData($player->getName());
                if(isset($pdata["uuid"]) && $player->getUniqueId()->toString() == $pdata["uuid"]){
                    $this->plugin->authenticatePlayer($player, $pdata["password"], true);
                    return;
                }
                $player->close("", $this->plugin->translateColors("&", $this->plugin->chlang["errors"]["invalid-uuid"]));
                $event->setCancelled(true);
                return;
			}
			if($this->plugin->isPlayerAuthenticated($player)){
				if($this->plugin->cfg["ip-login"]){
					$pdata = $this->plugin->getPlayerData($player->getName());
					if(isset($pdata["ip"]) && $player->getAddress() == $pdata["ip"]){
						$this->plugin->authenticatePlayer($player, $pdata["password"], true);
					}else{
					    $this->plugin->deauthenticatePlayer($player);
					}
				}else{
				    $this->plugin->deauthenticatePlayer($player);
				}
			}
		}
	}
	
	/**
	 * @param PlayerJoinEvent $event
	 */
    public function onPlayerJoin(PlayerJoinEvent $event){
    	$player = $event->getPlayer();
    	if($this->plugin->cfg["show-join-message"]){
    		$player->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["join-message"]));
    	}
    	if($this->plugin->isPlayerAuthenticated($player)){
    	    if($this->plugin->cfg["uuid-login"]){
    	        $player->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["login"]["uuid-login"]));
    	    }else if($this->plugin->cfg["ip-login"]){
    	        $player->sendMessage($this->plugin->translateColors("&", $this->plugin->getPrefix() . $this->plugin->chlang["login"]["ip-login"]));
    	    }
    	}
    	if(!$this->plugin->isAccountRegistered($player->getName()) && $this->plugin->isRegisterMessageEnabled()){
    		$this->plugin->callRegisterMessageHandler($this->plugin, $player);
    	}else if(!$this->plugin->isPlayerAuthenticated($player) && $this->plugin->isLoginMessageEnabled()){
    		$this->plugin->callLoginMessageHandler($this->plugin, $player);
    	}
    }
    
    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event){
    	if(!$this->plugin->cfg["allow-move"] && !$this->plugin->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
			return;
		}
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-chat"]){
    		if(!$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    		$recipients = $event->getRecipients();
    		foreach($recipients as $key => $recipient){
    			if($recipient instanceof Player){
    			    if(!$this->plugin->isPlayerAuthenticated($recipient)){
    					unset($recipients[$key]);
    				}
    			}
    		}
    		$event->setRecipients($recipients);
    	}
    }
    
    /**
     * @param PlayerCommandPreprocessEvent $event
     */
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
        if($this->plugin->getConfig()->getAll()["block-commands"]){
    		if(!$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    			$command = strtolower($event->getMessage());
    			if($command[0] == "/"){
    				$command = explode(" ", $command);
    				/*if($this->plugin->getServer()->getCommandMap()->getCommand(ltrim($command[0], "/"))){
    				    $cmdname = $this->plugin->getServer()->getCommandMap()->getCommand(ltrim($command[0], "/"))->getName();
    				    if($cmdname != "register" && $cmdname != "login"){
        				    $event->setCancelled(true);
        				    return;
    				    }
    				}else{
    				    $event->setCancelled(true);
    				    return;
    				}*/
    				if($command[0] != "/login" && $command[0] != "/register" && $command[0] != "/reg"){
    				    $event->setCancelled(true);
    				    return;
    				}
    			}
    		}
    	}
    }
    
    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event){
    	if(!$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    		return;
    	}
    }
	
    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event){
	if(!$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    		return;
    	}
    }
    
    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player){
        	if(!$this->plugin->isPlayerAuthenticated($player)){
        		$event->setCancelled(true);
        	}
        }
    	if($event instanceof EntityDamageByEntityEvent){
    		$damager = $event->getDamager();
    		if($damager instanceof Player){
    			if(!$this->plugin->isPlayerAuthenticated($damager)){
    				$event->setCancelled(true);
    			}
    		}
    	}
    }
    
    /**
     * @param PlayerDropItemEvent $event
     */
    public function onDropItem(PlayerDropItemEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"] && !$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    	    $event->setCancelled(true);
    	    return;
    	}
    }
    
    /**
     * @param PlayerItemConsumeEvent $event
     */
    public function onItemConsume(PlayerItemConsumeEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"] && !$this->plugin->isPlayerAuthenticated($event->getPlayer())){
    	    $event->setCancelled(true);
    	    return;
    	}
    }

    /**
     * @param CraftItemEvent $event
     */
    public function onCraftItem(CraftItemEvent $event) {
        if($this->plugin->getConfig()->getAll()["block-all-events"] && !$this->plugin->isPlayerAuthenticated($event->getPlayer())){
            $event->setCancelled(true);
            return;
        }
    }
    
    /**
     * @param PlayerAchievementAwardedEvent $event
     */
    public function onAwardAchievement(PlayerAchievementAwardedEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"] && !$this->plugin->isPlayerAuthenticated($event->getPlayer())){
			$event->setCancelled(true);
			return;
    	}
    }
}