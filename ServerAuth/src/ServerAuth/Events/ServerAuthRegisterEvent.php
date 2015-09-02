<?php

/*
 * ServerAuth (v2.11) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 10/05/2015 02:22 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\event\Cancellable;

class ServerAuthRegisterEvent extends PluginEvent implements Cancellable {

	public static $handlerList = null;

	/** @var Player $player */
	private $player;
	
	/** @var $password */
	private $password;

	/**
	 * @param Player $player
	 * @param $password
	 */
	public function __construct(Player $player, $password){
		$this->player = $player;
		$this->password = $password;
	}

	/**
	 * Get event player
	 *
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	/**
	 * Get password (it can be hashed or not)
	 * 
	 * @return $password
	 */
	public function getPassword(){
		return $this->password;
	}
}
