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

class ServerAuthMySQLImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "serverauth-mysql";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if(count($params) < 3){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["db-usage-advanced"], array("ID" => $this->getID()))));
            return;
        }
        $host = isset($params[0]) ? $params[0] : "localhost";
        $user = isset($params[1]) ? $params[1] : "root";
        $database = isset($params[2]) ? $params[2] : "serverauth";
        $password = isset($params[3]) ? $params[3] : "";
        $table_prefix = isset($params[4]) ? $params[4] : "srvauth_";
        $port = isset($params[5]) ? $params[5] : 3306;
        if($this->plugin->getDataProvider()->getId() == "mysql"){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["importers"]["same-provider"]));
            return;
        }
        $db = @new \mysqli($host, $user, $password, $database, $port);
        if($db->connect_errno){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["mysql-connect-error"], array("ERROR" => $db->connect_error))));
            return;
        }
        $res = $db->query("SELECT user, password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams FROM " . $table_prefix . "accounts");
        if(!$res){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "ServerAuth"))));
            return;
        }
        while($usrdata = $res->fetch_assoc()){
            $usrdata["hashed"] = true;
            $this->plugin->getDataProvider()->registerAccountRaw($usrdata["user"], $usrdata);
            $count++;
        }
        $db->close();
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["mysql"], array("COUNT" => $count, "PLUGIN" => "ServerAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}