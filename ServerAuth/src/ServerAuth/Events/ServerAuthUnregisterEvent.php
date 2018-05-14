<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;


class ServerAuthUnregisterEvent extends ServerAuthEvent {

	public static $handlerList = null;

	/** @var string */
	private $player;

	public function __construct($player){
		$this->player = $player;
	}

	/**
	 * Get player
	 *
	 * @return string
	 */
	public function getPlayer(){
		return $this->player;
	}
}