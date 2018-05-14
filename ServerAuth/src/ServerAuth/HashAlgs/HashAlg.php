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

interface HashAlg {
    
    public function __construct(ServerAuth $plugin);
    
    /**
     * Get hash algorithm ID
     * 
     * @return string
     */
    public function getId() : string;
    
    /**
     * Hash password
     * 
     * @param string $password
     * @param string $params
     * 
     * @return string
     */
    public function hash($password, $params = null);
}