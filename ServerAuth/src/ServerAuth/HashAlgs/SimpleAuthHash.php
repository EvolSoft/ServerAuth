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

class SimpleAuthHash implements HashAlg {
    
    public function __construct(ServerAuth $plugin){
        $this->plugin = $plugin;
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\HashAlgs\HashAlg::getID()
     */
    public function getId(): string {
        return "simpleauth";
    }
    
    /**
     * {@inheritDoc}
     * @see \ServerAuth\HashAlgs\HashAlg::hash()
     */
    public function hash($password, $params = null){
        $params = $this->plugin->decodeParams($params);
        $salt = isset($params["player"]) ? strtolower($params["player"]) : "";
        return bin2hex(hash("sha512", $password . $salt, true) ^ hash("whirlpool", $salt . $password, true));
    }
}