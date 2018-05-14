<?php

/*
 * ServerAuth (v3.0) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: https://www.evolsoft.tk
 * Date: 02/05/2018 01:46 PM (UTC)
 * Copyright & License: (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\plugin\PluginEvent;

class ServerAuthLoadPluginEvent extends PluginEvent {
    
    public static $handlerList = null;
    
    public function __construct(){}
}