<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 27/01/2016 02:20 AM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\inventory\PlayerInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class EventListener implements Listener {
	
	public function __construct(ServerAuth $plugin){
		$this->plugin = $plugin;
	}
	
	public function onPreLogin(PlayerPreLoginEvent $event){
		//Restore default messages
		ServerAuth::getAPI()->enableLoginMessages(true);
		ServerAuth::getAPI()->enableRegisterMessages(true);
		$cfg = $this->plugin->getConfig()->getAll();
		if($cfg['force-single-auth']){
			$player = $event->getPlayer();
			foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
				if(strtolower($pl->getName()) == strtolower($player->getName())){
					$player->close("", $this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["single-auth"]), false);
					$event->setCancelled(true);
				}
			}
			if(ServerAuth::getAPI()->isPlayerAuthenticated($player)){
				//IP Authentication
				if($cfg["IPLogin"]){
					$playerdata = ServerAuth::getAPI()->getPlayerData($player->getName());
					if($playerdata["ip"] == $player->getAddress()){
						ServerAuth::getAPI()->authenticatePlayer($player, $playerdata["password"], false);
					}else{
						ServerAuth::getAPI()->deauthenticatePlayer($event->getPlayer());
					}
				}else{
					ServerAuth::getAPI()->deauthenticatePlayer($event->getPlayer());
				}
			}
		}
	}
	
    public function onJoin(PlayerJoinEvent $event){
    	$player = $event->getPlayer();
    	$cfg = $this->plugin->getConfig()->getAll();
    	if($cfg["show-join-message"]){
    		$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["join-message"]));
    	}
    	if(ServerAuth::getAPI()->isPlayerAuthenticated($player) && $cfg["IPLogin"]){
    		$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["ip-login"]));
    	}
    	if(!ServerAuth::getAPI()->isPlayerRegistered($player->getName()) && ServerAuth::getAPI()->areRegisterMessagesEnabled()){
    		if($cfg["register"]["password-confirm-required"]){
    			$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["message-conf"]));
    		}else{
    			$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["register"]["message"]));
    		}
    	}else{
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($player) && ServerAuth::getAPI()->areLoginMessagesEnabled()){
    			$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["message"]));
    		}
    	}
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event){
    	//Free registered users cache
    	if(isset($this->cached_registered_users[strtolower($event->getPlayer()->getName())])){
    		unset($this->cached_registered_users[strtolower($event->getPlayer()->getName())]);
    	}
    }
    
    public function onPlayerMove(PlayerMoveEvent $event){
    	if(!$this->plugin->getConfig()->getAll()["allow-move"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }

    public function onPlayerChat(PlayerChatEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-chat"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true); //Cancel message
    		}
    		$recipients = $event->getRecipients();
    		foreach($recipients as $key => $recipient){
    			if($recipient instanceof Player){
    				if(!ServerAuth::getAPI()->isPlayerAuthenticated($recipient)){
    					unset($recipients[$key]);
    				}
    			}
    		}
    		$event->setRecipients($recipients);
    	}
    }
    
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
        if($this->plugin->getConfig()->getAll()["block-commands"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$command = strtolower($event->getMessage());
    			if($command{0} == "/"){
    				$command = explode(" ", $command);
    				if($command[0] != "/login" && $command[0] != "/register" && $command[0] != "/reg"){
    					$event->setCancelled(true);
    				}
    			}
    		}
    	}
    }
    
    public function onPlayerInteract(PlayerInteractEvent $event){
    	if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
	
    public function onBlockBreak(BlockBreakEvent $event){
	if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onEntityDamage(EntityDamageEvent $event){
    		$player = $event->getEntity();
    		if($player instanceof Player){
    			if(!ServerAuth::getAPI()->isPlayerAuthenticated($player)){
    				$event->setCancelled(true);
    			}
    		}
    	if($event instanceof EntityDamageByEntityEvent){
    		$damager = $event->getDamager();
    		if($damager instanceof Player){
    			if(!ServerAuth::getAPI()->isPlayerAuthenticated($damager)){
    				$event->setCancelled(true);
    			}
    		}
    	}
    }
    
    //Other Events
    
    public function onDropItem(PlayerDropItemEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
   public function onTransaction(InventoryTransactionEvent $event){
		if ($this->plugin->getConfig()->getAll()["block-all-events"]) {
			$transactions = $event->getTransaction()->getTransactions();
			foreach($transactions as $transaction){
				if($transaction->getInventory() instanceof PlayerInventory){
					if($transaction->getInventory()->getHolder() instanceof Player){
						if(!ServerAuth::getAPI()->isPlayerAuthenticated($transaction->getInventory()->getHolder())){
							$event->setCancelled();
						}
					}
				}
			}
		}
	}
    public function onItemConsume(PlayerItemConsumeEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }

    public function onCraftItem(CraftItemEvent $event) {
        if ($this->plugin->getConfig()->getAll()["block-all-events"]) {
            if (!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())) {
                $event->setCancelled(true);
            }
        }
    }
    
    public function onAwardAchievement(PlayerAchievementAwardedEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
}
?>
