<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\Providers;

use pocketmine\Player;
use pocketmine\utils\Config;

use ServerAuth\ServerAuth;
use ServerAuth\Tasks\MySQLTask;

class MySQLProvider implements Provider {
    
    /** @var string */
    const DATA_VERSION = "1.0";
    
    /** @var Config */
    public $cfg;
    
    /** @var \mysqli */
    public $db;
    
    public $status = false;
    
    public function __construct(ServerAuth $plugin){
        $c = array(
            "host" => "localhost",
            "port" => 3306,
            "user" => "root",
            "password" => "",
            "database" => "serverauth",
            "table-prefix" => "srvauth_"
        );
        $this->plugin = $plugin;
        $this->cfg = new Config($this->plugin->getDataFolder() . "mysql.yml", Config::YAML, $c);
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::init()
     */
    public function init($params = null){
        $this->db = @new \mysqli($this->cfg->get("host"), $this->cfg->get("user"), $this->cfg->get("password"), null, $this->cfg->get("port"));
        if($this->db->connect_errno){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["mysql-connect-error"], array("ERROR" => $this->db->connect_error))));
            return false;
        }
        $this->plugin->getServer()->getLogger()->info($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["mysql-success"]));
        if(!$this->db->select_db($this->cfg->get("database"))){
            if($this->db->query("CREATE DATABASE " . $this->cfg->get("database"))){
                $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["mysql-database-error"]));
                return false;
            }
        }
        $this->db->select_db($this->cfg->get("database"));
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS " . $this->cfg->get("table-prefix") . "info (version VARCHAR(10), data_version VARCHAR(10))")){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["mysql-tables-error"]));
            return false;
        }
        $res = $this->db->query("SELECT version, data_version FROM " . $this->cfg->get("table-prefix") . "info");
        if(!$res){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["mysql-query-error"]));
            return;
        }
        if($res->num_rows == 0){
            $this->db->query("INSERT INTO " . $this->cfg->get("table-prefix") . "info (version, data_version) VALUES (" . $this->plugin->getVersion() . ", " . self::DATA_VERSION . ")");
        }else{
            $this->db->query("UPDATE " . $this->cfg->get("table-prefix") . "info SET version='" . $this->plugin->getVersion() . "', data_version='" . self::DATA_VERSION . "' LIMIT 1");
        }
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS " . $this->cfg->get("table-prefix") . "accounts (user VARCHAR(50), password VARCHAR(200), ip VARCHAR(50), uuid VARCHAR(50), firstlogin BIGINT, lastlogin BIGINT, hashalg VARCHAR(20), hashparams VARCHAR(100))")){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["mysql-tables-error"]));
            return false;
        }
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask(new MySQLTask($this->plugin, $this), 600);
        $this->status = true;
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::getID()
     */
    public function getId() : string {
        return "mysql";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::checkAuthentication()
     */
    public function checkAuthentication(Player $player, $password, bool $hashed = false){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player->getName());
        $stmt = $this->db->prepare("SELECT password, hashalg, hashparams FROM " . $this->cfg->get("table-prefix") . "accounts WHERE user=?");
        $stmt->bind_param("s", $p);
        if(!$stmt->execute()){
            $stmt->close();
            return ServerAuth::ERR_GENERIC;
        }
        $stmt->bind_result($stmt_password, $stmt_hashalg, $stmt_hashparams);
        $stmt->fetch();
        $stmt->close();
        if(!$hashed){
            $hashalg = $this->plugin->getHashAlgById($stmt_hashalg);
            $params = $stmt_hashparams . ",player:" . $player->getName();
            if(!$hashalg){
                $hashalg = $this->plugin->getHashAlg();
            }
            $password = $this->plugin->hashPassword($password, $hashalg, $params);
        }
        if(strcasecmp($stmt_password, $password) == 0){
            $stmt = $this->db->prepare("UPDATE " . $this->cfg->get("table-prefix") . "accounts SET ip=?, lastlogin=? WHERE user=?");
            $stmt_ip = $player->getAddress();
            $stmt_lastplayed = $player->getLastPlayed();
            $stmt->bind_param("sis", $stmt_ip, $stmt_lastplayed, $p);
            if(!$stmt->execute()){
                $stmt->close();
                return ServerAuth::ERR_GENERIC;
            }
            $stmt->close();
            return ServerAuth::SUCCESS;
        }
        return ServerAuth::ERR_WRONG_PASS;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::registerAccountRaw()
     */
    public function registerAccountRaw($player, array $data){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player);
        $password = isset($data["password"]) ? $data["password"] : "";
        $ip = isset($data["ip"]) ? $data["ip"] : "";
        $uuid = isset($data["uuid"]) ? $data["uuid"] : "";
        $firstlogin = isset($data["firstlogin"]) ? $data["firstlogin"] : 0;
        $lastlogin = isset($data["lastlogin"]) ? $data["lastlogin"] : 0;
        $hashalg = isset($data["hashalg"]) ? $this->plugin->getHashAlgById($data["hashalg"]) : $this->plugin->getHashAlg();
        if(!$hashalg){
            $hashalg = $this->plugin->getHashAlg();
        }
        $hashalgid = $hashalg->getId();
        $params = isset($data["hashparams"]) ? $data["hashparams"] : $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
        if(!isset($data["hashed"])){
            $password = $this->plugin->hashPassword($password, $hashalg, $params . ",player:" . $player);
        }
        $stmt = $this->db->prepare("INSERT INTO " . $this->cfg->get("table-prefix") . "accounts (user, password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiss", $p, $password, $ip, $uuid, $firstlogin, $lastlogin, $hashalgid, $params);
        if($stmt->execute()){
            $stmt->close();
            return ServerAuth::SUCCESS;
        }
        $stmt->close();
        return ServerAuth::ERR_GENERIC;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::registerAccount()
     */
    public function registerAccount(Player $player, $password){
        if(!$this->status) return ServerAuth::ERR_IO;
        if($this->plugin->cfg["register"]["max-ip"] > 0){
            if($this->countIP($player->getAddress()) + 1 > $this->plugin->cfg["register"]["max-ip"]){
                return ServerAuth::ERR_MAX_IP;
            }
        }
        return $this->registerAccountRaw($player->getName(), array(
            "password" => $password,
            "ip" => $player->getAddress(),
            "uuid" => $player->getUniqueId()->toString(),
            "firstlogin" => $player->getFirstPlayed(),
            "lastlogin" => $player->getLastPlayed()
        ));
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::changeAccountPassword()
     */
    public function changeAccountPassword($player, $newpassword){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player);
        $params = $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
        $hashalgid = $this->plugin->getHashAlg()->getId();
        $stmt = $this->db->prepare("UPDATE " . $this->cfg->get("table-prefix") . "accounts SET password=?, hashalg=?, hashparams=? WHERE user=?");
        $hashpwd = $this->plugin->hashPassword($newpassword, null, $params . ",player:" . $player);
        $stmt->bind_param("ssss", $hashpwd, $hashalgid, $params, $p);
        if(!$stmt->execute()){
            $stmt->close();
            return ServerAuth::ERR_GENERIC;
        }
        $stmt->close();
        return ServerAuth::SUCCESS;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::isAccountRegistered()
     */
    public function isAccountRegistered($player){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player);
        $stmt = $this->db->prepare("SELECT user FROM " . $this->cfg->get("table-prefix") . "accounts WHERE user=?");
        $stmt->bind_param("s", $p);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows == 0){
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::unregisterAccount()
     */
    public function unregisterAccount($player){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player);
        $stmt = $this->db->prepare("DELETE FROM " . $this->cfg->get("table-prefix") . "accounts WHERE user=?");
        $stmt->bind_param("s", $p);
        if($stmt->execute()){
            $stmt->close();
            return ServerAuth::SUCCESS;
        }
        $stmt->close();
        return ServerAuth::ERR_GENERIC;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::getAccountData()
     */
    public function getAccountData($player){
        if(!$this->status) return ServerAuth::ERR_IO;
        $p = strtolower($player);
        $data = array();
        $stmt = $this->db->prepare("SELECT password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams FROM " . $this->cfg->get("table-prefix") . "accounts WHERE user=?");
        $stmt->bind_param("s", $p);
        if(!$stmt->execute()){
            $stmt->close();
            return ServerAuth::ERR_GENERIC;
        }
        $stmt->bind_result($data["password"], $data["ip"], $data["uuid"], $data["firstlogin"], $data["lastlogin"], $data["hashalg"], $data["hashparams"]);
        $stmt->fetch();
        $stmt->close();
        return $data;
    }
    
    /**
     * @internal
     * 
     * Count accounts with the same IP address
     * 
     * @param string $ip
     * 
     * @return int
     */
    private function countIP($ip) : int {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->cfg->get("table-prefix") . "accounts WHERE ip=?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->store_result();
        $n = $stmt->num_rows;
        $stmt->close();
        return $n;
    }
}