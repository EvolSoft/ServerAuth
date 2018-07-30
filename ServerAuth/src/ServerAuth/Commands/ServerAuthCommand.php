<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;

abstract class ServerAuthCommand implements CommandExecutor {
    
    /**
     * Get auth command type
     * 
     * @return int
     */
    public function getType() : int {}
}