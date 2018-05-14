<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use ServerAuth\ServerAuth;

class ServerAuthCommandHandler implements CommandExecutor {

    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        switch(strtolower($cmd->getName())){
            case "register":
                return $this->plugin->getAuthCommand(ServerAuth::CMD_REGISTER)->onCommand($sender, $cmd, $label, $args);
            case "unregister":
                return $this->plugin->getAuthCommand(ServerAuth::CMD_UNREGISTER)->onCommand($sender, $cmd, $label, $args);
            case "login":
                return $this->plugin->getAuthCommand(ServerAuth::CMD_LOGIN)->onCommand($sender, $cmd, $label, $args);
            case "logout":
                return $this->plugin->getAuthCommand(ServerAuth::CMD_LOGOUT)->onCommand($sender, $cmd, $label, $args);
            case "changepassword":
                return $this->plugin->getAuthCommand(ServerAuth::CMD_CHPASSW)->onCommand($sender, $cmd, $label, $args);
        }
    }
}