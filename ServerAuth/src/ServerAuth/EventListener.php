<?php

/*
 * ServerAuth (v2.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 31/08/2015 01:25 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

use ServerAuth\ServerAuth;

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
			$count = 0;
			foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
				if(strtolower($pl->getName()) == strtolower($player->getName())){
					$count++;
				}
			}
			if($count > 1){
				if(ServerAuth::getAPI()->isPlayerAuthenticated($player)){
					$player->close($this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["single-auth"]), $this->plugin->translateColors("&", ServerAuth::getAPI()->getConfigLanguage()->getAll()["single-auth"]), false);
					$event->setCancelled(true);
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
    	if(ServerAuth::getAPI()->isPlayerAuthenticated($player)){
    		//IP Authentication
    		if($cfg["IPLogin"]){
    			$playerdata = ServerAuth::getAPI()->getPlayerData($player->getName());
    			if($playerdata["ip"] == $player->getAddress()){
    				ServerAuth::getAPI()->authenticatePlayer($player, $playerdata["password"], false);
    				$player->sendMessage($this->plugin->translateColors("&", $cfg["prefix"] . ServerAuth::getAPI()->getConfigLanguage()->getAll()["login"]["ip-login"]));
    			}else{
    				ServerAuth::getAPI()->deauthenticatePlayer($event->getPlayer());
    			}
    		}else{
    			ServerAuth::getAPI()->deauthenticatePlayer($event->getPlayer());
    		}
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
    
    public function onPlayerMove(PlayerMoveEvent $event){
    	if(!$this->plugin->getConfig()->getAll()["allow-move"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }

    public function onPlayerChat(PlayerChatEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-chat"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true); //Cancel message
    		}
    		$recipients = $event->getRecipients();
    		for($i = 0; $i < count($recipients); $i++){
    			$player = $recipients[$i];
    			if($player instanceof Player){
    				if(!ServerAuth::getAPI()->isPlayerAuthenticated($player)){
    					$message[] = $i;
    					foreach($message as $messages){
    						unset($recipients[$i]);
    						$event->setRecipients(array_values($recipients));
    					}
    				}
    			}
    		}
    	}
    }
    
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event){
        if($this->plugin->getConfig()->getAll()["block-commands"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
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
    	if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onBlockBreak(BlockBreakEvent $event){
    	if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onBlockPlace(BlockPlaceEvent $event){
    	if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onBucketFill(PlayerBucketFillEvent $event){
    	if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onBucketEmpty(PlayerBucketEmptyEvent $event){
    	if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    		$event->setCancelled(true);
    	}
    }
    
    public function onEntityDamage(EntityDamageEvent $event){
    	if($event instanceof EntityDamageByEntityEvent && (!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer()))){
    		$event->setCancelled(true);
    	}
    }
    
    //Other Events
    
    public function onBedEnter(PlayerBedEnterEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
    
    public function onBedLeave(PlayerBedLeaveEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
    
    public function onDropItem(PlayerDropItemEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
    
    public function onItemConsume(PlayerItemConsumeEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
    
    public function onAwardAchievement(PlayerAchievementAwardedEvent $event){
    	if($this->plugin->getConfig()->getAll()["block-all-events"]){
    		if(!ServerAuth::getAPI()->isPlayerRegistered($event->getPlayer()->getName()) || !ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
    			$event->setCancelled(true);
    		}
    	}
    }
}
?>
