<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

class ServerAuthChangePasswordEvent extends ServerAuthEvent {

	public static $handlerList = null;

	/** @var string */
	private $player;
	
	/** @var string */
	private $password;

	public function __construct($player, $password){
		$this->player = $player;
		$this->password = $password;
	}

	/**
	 * Get player
	 *
	 * @return string
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
}