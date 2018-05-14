<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Providers;

use pocketmine\Player;

use ServerAuth\ServerAuth;

interface Provider {
    
    public function __construct(ServerAuth $plugin);
    
    /**
     * Initialize provider
     * 
     * @param array $params
     * 
     * @return int
     */
    public function init($params = null);
    
    /**
     * Get provider ID
     * 
     * @return string
     */
    public function getId() : string;
    
    /**
     * Register an account by raw data
     * 
     * @param string $player
     * @param array $data
     * 
     * @return int
     */
    public function registerAccountRaw($player, array $data);
    
    /**
     * Register an account
     * 
     * @param Player $player
     * @param string $password
     * 
     * @return int
     */
    public function registerAccount(Player $player, $password);
    
    /**
     * Unregister an account
     * 
     * @param string $player
     * 
     * @return int
     */
    public function unregisterAccount($player);
    
    /**
     * Check if account is registered
     * 
     * @param string $player
     * 
     * @return int
     */
    public function isAccountRegistered($player);
    
    /**
     * Get account data
     * 
     * @param string $player
     * 
     * @return int
     */
    public function getAccountData($player);
    
    /**
     * Change account password
     * 
     * @param string $player
     * @param string $newpassword
     * 
     * @return int
     */
    public function changeAccountPassword($player, $newpassword);
    
    /**
     * Check player authentication
     * 
     * @param Player $player
     * @param string $password
     * @param bool $hashed
     * 
     * @return int
     */
    public function checkAuthentication(Player $player, $password, bool $hashed = false);
}