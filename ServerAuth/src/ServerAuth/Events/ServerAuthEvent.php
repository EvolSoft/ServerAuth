<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;

abstract class ServerAuthEvent extends PluginEvent implements Cancellable {
    
    /** @var string */
    private $cmessage;
    
    /**
     * Get event cancelled message
     *
     * @return string
     */
    public function getCancelledMessage(){
        return $this->cmessage;
    }
    
    /**
     * Set event cancelled message
     *
     * @param string $message
     */
    public function setCancelledMessage($cmessage){
        $this->cmessage = $cmessage;
    }
}