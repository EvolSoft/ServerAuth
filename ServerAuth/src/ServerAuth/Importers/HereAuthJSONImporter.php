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
use pocketmine\utils\UUID;

use ServerAuth\ServerAuth;

class HereAuthJSONImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "hereauth-json";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        $path = isset($params[0]) ? $params[0] : $this->plugin->getServer()->getPluginPath() . "HereAuth/accounts";
        if(!is_dir($path)){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-not-found"], array("PLUGIN" => "HereAuth"))));
            return;
        }
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["importers"]["init-import"]));
        foreach(glob($path. "/*.json") as $usrfile){
            $usrdata = json_decode(zlib_decode(file_get_contents($usrfile)), true);
            if($usrdata){
                $cdata["password"] = bin2hex(base64_decode($usrdata["passwordHash"]));
                $cdata["hashed"] = true;
                $cdata["ip"] = $usrdata["lastIp"];
                $cdata["uuid"] = UUID::fromBinary(base64_decode($usrdata["lastUuid"]))->toString();
                $cdata["firstlogin"] = $usrdata["registerTime"];
                $cdata["lastlogin"] = $usrdata["lastLogin"];
                $cdata["hashalg"] = "simpleauth";
                $cdata["hashparams"] = "";
                $this->plugin->getDataProvider()->registerAccountRaw($usrdata["name"], $cdata);
                $count++;
            }else{
                $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "HereAuth"))));
            }
        }
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["json"], array("COUNT" => $count, "PLUGIN" => "HereAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}