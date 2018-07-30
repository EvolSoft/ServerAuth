<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;

use ServerAuth\ServerAuth;
use ServerAuth\Providers\MySQLProvider;

class MySQLTask extends Task {
    
    private $mysqlp;
    
    private $plugin;
    
    private $cfg;
    
    public function __construct(ServerAuth $plugin, MySQLProvider $mysqlp){
        parent::__construct($plugin);
        $this->mysqlp = $mysqlp;
        $this->plugin = $this->getOwner();
        $this->cfg["host"] = $this->mysqlp->cfg->get("host");
        $this->cfg["user"] = $this->mysqlp->cfg->get("user");
        $this->cfg["password"] = $this->mysqlp->cfg->get("password");
        $this->cfg["port"] = $this->mysqlp->cfg->get("port");
        $this->cfg["database"] = $this->mysqlp->cfg->get("database");
    }
    
    public function onRun(int $tick){
        if($this->plugin->getDataProvider()->getId() == "mysql"){
            if($this->mysqlp->db){
                if(@!$this->mysqlp->db->ping()){
                    $this->mysqlp->db = @new \mysqli($this->cfg["host"], $this->cfg["user"], $this->cfg["password"], null, $this->cfg["port"]);
                    if(!$this->mysqlp->db->connect_errno){
                        $this->mysqlp->db->select_db($this->cfg["database"]);
                        Server::getInstance()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["mysql-restored"]));
                        $this->mysqlp->status = true;
                    }else{
                        $this->mysqlp->status = false;
                    }
                }
            }
        }
    }
}