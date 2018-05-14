<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Importers;

use pocketmine\command\CommandSender;

use ServerAuth\ServerAuth;

interface Importer {
    public function __construct(ServerAuth $plugin);
    
    /**
     * Get Importer ID
     * 
     * @return string
     */
    public function getId() : string;

    /**
     * Export data
     * 
     * @param CommandSender $sender
     * @param array $params
     */
    public function export(CommandSender $sender, array $params = null);
}