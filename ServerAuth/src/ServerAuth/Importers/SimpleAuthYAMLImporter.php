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

class SimpleAuthYAMLImporter implements Importer {

    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "simpleauth-yaml";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if(!is_dir($this->plugin->getServer()->getPluginPath() . "SimpleAuth/players")){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-not-found"], array("PLUGIN" => "SimpleAuth"))));
            return;
        }
        foreach(glob($this->plugin->getServer()->getPluginPath() . "SimpleAuth/players/*") as $usrdir){
            foreach(glob($usrdir . "/*.yml") as $usrfile){
                $usrdata = (new Config($usrfile, Config::YAML))->getAll();
                $cdata["password"] = $usrdata["hash"];
                $cdata["hashed"] = true;
                $cdata["ip"] = $usrdata["ip"];
                $cdata["firstlogin"] = $usrdata["registerdate"];
                $cdata["lastlogin"] = $usrdata["logindate"];
                $cdata["hashalg"] = "simpleauth";
                $cdata["hashparams"] = "";
                $this->plugin->getDataProvider()->registerAccountRaw(strtolower(pathinfo($usrfile, PATHINFO_FILENAME)), $cdata);
                $count++;
            }
        }
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["yaml"], array("COUNT" => $count, "PLUGIN" => "SimpleAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}