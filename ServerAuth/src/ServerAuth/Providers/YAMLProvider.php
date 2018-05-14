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

class YAMLProvider implements Provider {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }

    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::init()
     */
    public function init($params = null){
        @mkdir($this->plugin->getDataFolder() . "users/");
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::getID()
     */
    public function getId() : string {
        return "yaml";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::checkAuthentication()
     */
    public function checkAuthentication(Player $player, $password, bool $hashed = false){
        $user = new Config($this->plugin->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
        if(!$hashed){
            $hashalg = $this->plugin->getHashAlgById($user->get("hashalg"));
            $params = $user->get("hashparams") . ",player:" . $player->getName();
            if(!$hashalg){
                $hashalg = $this->plugin->getHashAlg();
            }
            $password = $this->plugin->hashPassword($password, $hashalg, $params);
        }
        if(strcmp($user->get("password"), $password) == 0){
            $user->set("ip", $player->getAddress());
            $user->set("lastlogin", $player->getLastPlayed());
            return ServerAuth::SUCCESS;
        }
        return ServerAuth::ERR_WRONG_PASS;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::registerAccountRaw()
     */
    public function registerAccountRaw($player, array $data){
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
        $user = new Config($this->plugin->getDataFolder() . "users/" . strtolower($player . ".yml"), Config::YAML); 
        if(!isset($data["hashed"])){
            $password = $this->plugin->hashPassword($password, $hashalg, $params . ",player:" . $player);
        }
        $user->set("password", $password);
        $user->set("ip", $ip);
        $user->set("uuid", $uuid);
        $user->set("firstlogin", $firstlogin);
        $user->set("lastlogin", $lastlogin);
        $user->set("hashalg", $hashalg->getId());
        $user->set("hashparams", $params);
        $user->save();
        return ServerAuth::SUCCESS;
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
        $params = $this->plugin->encodeParams($this->plugin->cfg["password-hash"]["parameters"]);
        $user = new Config($this->plugin->getDataFolder() . "users/" . strtolower($player . ".yml"), Config::YAML);
        $user->set("hashalg", $this->plugin->getHashAlg()->getId());
        $user->set("hashparams", $params);
        $user->set("password", $this->plugin->hashPassword($newpassword, null, $params . ",player:" . $player));
        $user->save();
        return ServerAuth::SUCCESS;
    }

    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::isAccountRegistered()
     */
    public function isAccountRegistered($player){
        return file_exists($this->plugin->getDataFolder() . "users/" . strtolower($player . ".yml"));
    }

    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::unregisterAccount()
     */
    public function unregisterAccount($player){
        if(@unlink($this->plugin->getDataFolder() . "users/" . strtolower($player . ".yml"))){
            return ServerAuth::SUCCESS;
        }
        return ServerAuth::ERR_IO;
    }

    /**
     * {@inheritDoc}
     * @see \ServerAuth\Providers\Provider::getAccountData()
     */
    public function getAccountData($player){
        return (new Config($this->plugin->getDataFolder() . "users/" . strtolower($player . ".yml"), Config::YAML))->getAll();
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
        $count = 0;
        foreach(glob($this->plugin->getDataFolder() . "users/" . "*.yml") as $filename){
            foreach(file($filename) as $fli=>$fl){
                if(strpos($fl, $ip) !== false){
                    $count += 1;
                }
            }
        }
        return $count;
    }
}