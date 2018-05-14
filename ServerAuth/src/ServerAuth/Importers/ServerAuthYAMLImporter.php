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
use pocketmine\utils\Config;

use ServerAuth\ServerAuth;

class ServerAuthYAMLImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "serverauth-yaml";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if($this->plugin->getDataProvider()->getId() == "yaml"){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["importers"]["same-provider"]));
            return;
        }
        foreach(glob($this->plugin->getDataFolder() . "users/*.yml") as $usrfile){
            $usrdata = (new Config($usrfile, Config::YAML))->getAll();
            $usrdata["hashed"] = true;
            $this->plugin->getDataProvider()->registerAccountRaw(strtolower(pathinfo($usrfile, PATHINFO_FILENAME)), $usrdata);
            $count++;
        }
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["yaml"], array("COUNT" => $count, "PLUGIN" => "ServerAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}