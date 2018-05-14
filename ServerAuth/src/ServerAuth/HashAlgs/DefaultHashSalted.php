<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth\HashAlgs;

use ServerAuth\ServerAuth;

class DefaultHashSalted implements HashAlg {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\HashAlgs\HashAlg::getID()
     */
    public function getId() : string {
        return "serverauth_salted";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\HashAlgs\HashAlg::hash()
     */
    public function hash($password, $params = null){
        $params = $this->plugin->decodeParams($params);
        $hash = isset($params["hash"]) ? $params["hash"] : "sha256";
        $mhash = isset($params["multi-hash"]) ? $params["multi-hash"] : "md5";
        $salt = isset($params["player"]) ? strtolower($params["player"]) : "";
        $hashpwd = hash($hash, $salt . $password);
        if($mhash){
            $hashpwd = hash($mhash, $hashpwd . $salt);
        }
        return $hashpwd;
    }
}