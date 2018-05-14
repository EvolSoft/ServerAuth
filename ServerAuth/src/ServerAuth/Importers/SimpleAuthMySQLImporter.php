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

class SimpleAuthMySQLImporter implements Importer {

    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "simpleauth-mysql";
    }
   
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::export()
     */
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if(count($params) < 3){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["db-usage-basic"], array("ID" => $this->getId()))));
            return;
        }
        $host = isset($params[0]) ? $params[0] : "localhost";
        $user = isset($params[1]) ? $params[1] : "root";
        $database = isset($params[2]) ? $params[2] : "simpleauth";
        $password = isset($params[3]) ? $params[3] : "";
        $port = isset($params[5]) ? $params[5] : 3306;
        $db = @new \mysqli($host, $user, $password, $database, $port);
        if($db->connect_errno){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["mysql-connect-error"], array("ERROR" => $db->connect_error))));
            return;
        }
        $res = $db->query("SELECT name, hash, ip, registerdate, logindate FROM simpleauth_players");
        if(!$res){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "SimpleAuth"))));
            return;
        }
        while($usrdata = $res->fetch_assoc()){
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
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["mysql"], array("COUNT" => $count, "PLUGIN" => "SimpleAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}