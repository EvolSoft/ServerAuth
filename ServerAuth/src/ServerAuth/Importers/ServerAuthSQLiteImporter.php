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

class ServerAuthSQLiteImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "serverauth-sqlite";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if($this->plugin->getDataProvider()->getId() == "sqlite"){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["importers"]["same-provider"]));
            return;
        }
        $db = @new \SQLite3($this->plugin->getDataFolder() . "data.db");
        if(!$db){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["sqlite-open-error"], array("ERROR" => $db->lastErrorMsg()))));
            return;
        }
        $res = $db->query("SELECT user, password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams FROM accounts");
        if(!$res){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "ServerAuth"))));
            return;
        }
        while($usrdata = $res->fetchArray()){
            $usrdata["hashed"] = true;
            $this->plugin->getDataProvider()->registerAccountRaw($usrdata["user"], $usrdata);
            $count++;
        }
        $db->close();
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["sqlite"], array("COUNT" => $count, "PLUGIN" => "ServerAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}