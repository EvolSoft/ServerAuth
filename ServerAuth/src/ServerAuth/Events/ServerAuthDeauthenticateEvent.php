<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 10/05/2015 02:19 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;

class ServerAuthDeauthenticateEvent extends PluginEvent {

	public static $handlerList = null;

	/** @var Player $player */
	private $player;

	/**
	 * @param Player $player
	 */
	public function __construct(Player $player){
		$this->player = $player;
	}

	/**
	 * Get event player
	 *
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
}
