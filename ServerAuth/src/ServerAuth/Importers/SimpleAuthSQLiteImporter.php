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

class SimpleAuthSQLiteImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "simpleauth-sqlite";
    }

    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if(!is_dir($this->plugin->getServer()->getPluginPath() . "SimpleAuth")){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-not-found"], array("PLUGIN" => "SimpleAuth"))));
            return;
        }
        $db = @new \SQLite3($this->plugin->getServer()->getPluginPath() . "SimpleAuth/players.db");
        if(!$db){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["sqlite-open-error"], array("ERROR" => $db->lastErrorMsg()))));
            return;
        }
        $res = $db->query("SELECT name, hash, ip, registerdate, logindate FROM players");
        if(!$res){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "SimpleAuth"))));
            return;
        }
        while($usrdata = $res->fetchArray()){
            $cdata["password"] = $usrdata["hash"];
            $cdata["hashed"] = true;
            $cdata["ip"] = $usrdata["ip"];
            $cdata["firstlogin"] = $usrdata["registerdate"];
            $cdata["lastlogin"] = $usrdata["logindate"];
            $cdata["hashalg"] = "simpleauth";
            $cdata["hashparams"] = "";
            $this->plugin->getDataProvider()->registerAccountRaw($usrdata["name"], $cdata);
            $count++;
        }
        $db->close();
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["sqlite"], array("COUNT" => $count, "PLUGIN" => "SimpleAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}