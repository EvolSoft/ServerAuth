<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 17/01/2016 09:20 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\event\Cancellable;

use ServerAuth\ServerAuth;

class ServerAuthUnregisterEvent extends PluginEvent implements Cancellable {

	public static $handlerList = null;

	/** @var Player|OfflinePlayer|string $player */
	private $player;

	/**
	 * @param Player $player
	 */
	public function __construct($player){
		$this->player = $player;
	}

	/**
	 * Get event player
	 *
	 * @return Player|OfflinePlayer|string
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	/**
	 * Set cancelled message
	 *
	 * @param string $message
	 */
	public function setCancelledMessage(string $message){
		ServerAuth::getAPI()->canc_message = $message;
	}
}
