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

class HereAuthMySQLImporter implements Importer {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Importers\Importer::getID()
     */
    public function getId() : string {
        return "hereauth-mysql";
    }
    
    public function export(CommandSender $sender, array $params = null){
        $count = 0;
        if(count($params) < 3){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["db-usage-advanced"], array("ID" => $this->getID()))));
            return;
        }
        $host = isset($params[0]) ? $params[0] : "localhost";
        $user = isset($params[1]) ? $params[1] : "root";
        $database = isset($params[2]) ? $params[2] : "hereauth";
        $password = isset($params[3]) ? $params[3] : "";
        $table_prefix = isset($params[4]) ? $params[4] : "ha";
        $port = isset($params[5]) ? $params[5] : 3306;
        $db = @new \mysqli($host, $user, $password, $database, $port);
        if($db->connect_errno){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["mysql-connect-error"], array("ERROR" => $db->connect_error))));
            return;
        }
        $res = $db->query("SELECT name, hash, ip, uuid, register, login FROM " . $table_prefix . "accs");
        if(!$res){
            $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["data-invalid"], array("PLUGIN" => "HereAuth"))));
            return;
        }
        while($usrdata = $res->fetch_assoc()){
            $cdata["password"] = bin2hex($usrdata["hash"]);
            $cdata["hashed"] = true;
            $cdata["ip"] = $usrdata["ip"];
            $cdata["uuid"] = UUID::fromString(bin2hex($usrdata["uuid"]))->toString();
            $cdata["firstlogin"] = $usrdata["register"];
            $cdata["lastlogin"] = $usrdata["login"];
            $cdata["hashalg"] = "simpleauth";
            $cdata["hashparams"] = "";
            $this->plugin->getDataProvider()->registerAccountRaw($usrdata["name"], $cdata);
            $count++;
        }
        $db->close();
        $sender->sendMessage($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["importers"]["mysql"], array("COUNT" => $count, "PLUGIN" => "HereAuth", "PROVIDER" => $this->plugin->getDataProvider()->getId()))));
    }
}