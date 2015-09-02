<?php

/*
 * ServerAuth (v2.11) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 31/08/2015 12:49 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\event\Cancellable;

class ServerAuthUnregisterEvent extends PluginEvent implements Cancellable {

	public static $handlerList = null;

	/** @var Player|OfflinePlayer $player */
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
	 * @return Player|OfflinePlayer
	 */
	public function getPlayer(){
		return $this->player;
	}
}
