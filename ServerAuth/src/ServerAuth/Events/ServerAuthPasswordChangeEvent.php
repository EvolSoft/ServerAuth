<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 10/05/2015 02:22 PM (UTC)
 * Copyright & License: (C) 2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\Player;
use pocketmine\event\Cancellable;

use ServerAuth\ServerAuth;

class ServerAuthPasswordChangeEvent extends PluginEvent implements Cancellable {

	public static $handlerList = null;

	/** @var Player|OfflinePlayer $player */
	private $player;
	
	/** @var $password */
	private $password;

	/**
	 * @param Player $player
	 * @param $password
	 */
	public function __construct($player, $password){
		$this->player = $player;
		$this->password = $password;
	}

	/**
	 * Get event player
	 *
	 * @return Player|OfflinePlayer
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	/**
	 * Get the new password
	 * 
	 * @return $password
	 */
	public function getPassword(){
		return $this->password;
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
