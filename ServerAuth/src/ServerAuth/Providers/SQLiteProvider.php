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

use ServerAuth\ServerAuth;

class SQLiteProvider implements Provider {
    
    /** @var string */
    const DATA_VERSION = "1.0";
    
    /** @var \SQLite3 */
    private $db;
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::init()
     */
    public function init($params = null){
        $this->db = @new \SQLite3($this->plugin->getDataFolder() . "data.db");
        if(!$this->db){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->replaceVars($this->plugin->chlang["errors"]["sqlite-open-error"], array("ERROR" => $db->lastErrorMsg()))));
            return false;
        }
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS info (version TEXT, data_version TEXT)")){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["sqlite-tables-error"]));
            return false;
        }
        $res = $this->db->query("SELECT version, data_version FROM info");
        if(!$res){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["sqlite-query-error"]));
            return false;
        }
        if(!$res->fetchArray(SQLITE3_NUM)){
            $this->db->query("INSERT INTO info (version, data_version) VALUES (" . $this->plugin->getVersion() . ", " . self::DATA_VERSION . ")");
        }else{
            $this->db->query("UPDATE info SET version='" . $this->plugin->getVersion() . "', data_version='" . self::DATA_VERSION . "' LIMIT 1");
        }
        if(!$this->db->query("CREATE TABLE IF NOT EXISTS accounts (user TEXT, password TEXT, ip TEXT, uuid TEXT, firstlogin INT, lastlogin INT, hashalg TEXT, hashparams TEXT)")){
            $this->plugin->getServer()->getLogger()->error($this->plugin->translateColors("&", ServerAuth::PREFIX . $this->plugin->chlang["errors"]["sqlite-tables-error"]));
            return false;
        }
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::getID()
     */
    public function getId() : string {
        return "sqlite";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::checkAuthentication()
     */
    public function checkAuthentication(Player $player, $password, bool $hashed = false){
        $p = strtolower($player->getName());
        $stmt = $this->db->prepare("SELECT password, hashalg, hashparams FROM accounts WHERE user=:user");
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
        $res = $stmt->execute();
        if(!$res){
            $stmt->close();
            return ServerAuth::ERR_GENERIC;
        }
        $arr = $res->fetchArray(SQLITE3_NUM);
        $stmt->close();
        $stmt_password = $arr[0];
        $stmt_hashalg = $arr[1];
        $stmt_hashparams = $arr[2];
        if(!$hashed){
            $hashalg = $this->plugin->getHashAlgById($stmt_hashalg);
            $params = $stmt_hashparams . ",player:" . $player->getName();
            if(!$hashalg){
                $hashalg = $this->plugin->getHashAlg();
            }
            $password = $this->plugin->hashPassword($password, $hashalg, $params);
        }
        if(strcasecmp($stmt_password, $password) == 0){
            $stmt = $this->db->prepare("UPDATE accounts SET ip=:ip, lastlogin=:lastlogin WHERE user=:user");
            $stmt->bindValue(":user", $p, SQLITE3_TEXT);
            $stmt->bindValue(":ip", $player->getAddress(), SQLITE3_TEXT);
            $stmt->bindValue(":lastlogin", $player->getLastPlayed(), SQLITE3_INTEGER);
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
        $params = isset($data["hashparams"]) ? $data["hashparams"] : $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
        if(!isset($data["hashed"])){
            $password = $this->plugin->hashPassword($password, $hashalg, $params . ",player:" . $player);
        }
        $stmt = $this->db->prepare("INSERT INTO accounts (user, password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams) VALUES (:user, :password, :ip, :uuid, :firstlogin, :lastlogin, :hashalg, :hashparams)");
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
        $stmt->bindValue(":password", $password, SQLITE3_TEXT);
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $stmt->bindValue(":uuid", $uuid, SQLITE3_TEXT);
        $stmt->bindValue(":firstlogin", $firstlogin, SQLITE3_INTEGER);
        $stmt->bindValue(":lastlogin", $lastlogin, SQLITE3_INTEGER);
        $stmt->bindValue(":hashalg", $hashalg->getId(), SQLITE3_TEXT);
        $stmt->bindValue(":hashparams", $params, SQLITE3_TEXT);
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
        $p = strtolower($player);
        $params = $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
        $stmt = $this->db->prepare("UPDATE accounts SET password=:password, hashalg=:hashalg, hashparams=:hashparams WHERE user=:user");
        $hashpwd = $this->plugin->hashPassword($newpassword, null, $params . ",player:" . $player);
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
        $stmt->bindValue(":password", $hashpwd, SQLITE3_TEXT);
        $stmt->bindValue(":hashalg", $this->plugin->getHashAlg()->getId(), SQLITE3_TEXT);
        $stmt->bindValue(":hashparams", $params, SQLITE3_TEXT);
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
        $p = strtolower($player);
        $stmt = $this->db->prepare("SELECT user FROM accounts WHERE user=:user");
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
        $res = $stmt->execute();
        if(!$res->fetchArray(SQLITE3_NUM)){
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
        $p = strtolower($player);
        $stmt = $this->db->prepare("DELETE FROM accounts WHERE user=:user");
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
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
        $p = strtolower($player);
        $data = array();
        $stmt = $this->db->prepare("SELECT password, ip, uuid, firstlogin, lastlogin, hashalg, hashparams FROM accounts WHERE user=:user");
        $stmt->bindValue(":user", $p, SQLITE3_TEXT);
        $res = $stmt->execute();
        if(!$res){
            $stmt->close();
            return ServerAuth::ERR_GENERIC;
        }
        $data = $res->fetchArray(SQLITE3_ASSOC);
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
        $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM accounts WHERE ip=:ip");
        $stmt->bindValue(":ip", $ip, SQLITE3_TEXT);
        $res = $stmt->execute();
        if(!$res){
            $stmt->close();
            return 0;
        }
        $row = $res->fetchArray();
        $stmt->close();
        return $row["count"];
    }
}