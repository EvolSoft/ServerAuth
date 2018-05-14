<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\Player;

class ServerAuthAuthenticateEvent extends ServerAuthEvent {

	public static $handlerList = null;

	/** @var Player */
	private $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	/**
	 * Get player
	 *
	 * @return Player
	 */
	public function getPlayer() : Player {
		return $this->player;
	}
}