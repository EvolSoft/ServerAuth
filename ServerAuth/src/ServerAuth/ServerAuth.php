<?php

/*
 * ServerAuth v3.0 by EvolSoft
 * Developer: Flavius12
 * Website: https://www.evolsoft.tk
 * Copyright (C) 2015-2018 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use ServerAuth\Commands\ServerAuthCommand;
use ServerAuth\Events\ServerAuthAuthenticateEvent;
use ServerAuth\Events\ServerAuthChangePasswordEvent;
use ServerAuth\Events\ServerAuthDeauthenticateEvent;
use ServerAuth\Events\ServerAuthLoadPluginEvent;
use ServerAuth\Events\ServerAuthRegisterEvent;
use ServerAuth\Events\ServerAuthUnregisterEvent;
use ServerAuth\HashAlgs\DefaultHash;
use ServerAuth\HashAlgs\DefaultHashSalted;
use ServerAuth\HashAlgs\HashAlg;
use ServerAuth\HashAlgs\SimpleAuthHash;
use ServerAuth\Importers\HereAuthJSONImporter;
use ServerAuth\Importers\HereAuthMySQLImporter;
use ServerAuth\Importers\Importer;
use ServerAuth\Importers\ServerAuthMySQLImporter;
use ServerAuth\Importers\ServerAuthSQLiteImporter;
use ServerAuth\Importers\ServerAuthYAMLImporter;
use ServerAuth\Importers\SimpleAuthMySQLImporter;
use ServerAuth\Importers\SimpleAuthSQLiteImporter;
use ServerAuth\Importers\SimpleAuthYAMLImporter;
use ServerAuth\Providers\MySQLProvider;
use ServerAuth\Providers\Provider;
use ServerAuth\Providers\SQLiteProvider;
use ServerAuth\Providers\YAMLProvider;

class ServerAuth extends PluginBase {
	
	/** @var string */
	const PREFIX = "&1[ServerAuth] ";
	
	/** @var string */
	const API_VERSION = "2.0.0";
	
	/** @var int */
	const ERR_GENERIC = 0;
	
	/** @var int */
	const SUCCESS = 1;
	
	/** @var int */
	const ERR_NOT_REG = 2;
	
	/** @var int */
	const ERR_ALREADY_REG = 3;
	
	/** @var int */
	const ERR_NOT_AUTH = 4;
	
	/** @var int */
	const ERR_ALREADY_AUTH = 5;
	
	/** @var int */
	const ERR_WRONG_PASS = 6;
	
	/** @var int */
	const ERR_PASS_SHORT = 7;
	
	/** @var int */
	const ERR_PASS_LONG = 8;
	
	/** @var int */
	const ERR_MAX_IP = 9;
	
	/** @var int */
	const ERR_TOO_MANY_ATTEMPTS = 10;
	
	/** @var int */
	const ERR_IO = 11;
	
	/** @var int */
	const CANCELLED = 12;
	
	/** @var int */
	const CMD_REGISTER = 0;
	
	/** @var int */
	const CMD_UNREGISTER = 1;
	
	/** @var int */
	const CMD_LOGIN = 2;
	
	/** @var int */
	const CMD_LOGOUT = 3;
	
	/** @var int */
	const CMD_CHPASSW = 4;
	
	/** @var HashAlg[] */
	private $hashalgs;
	
	/** @var string */
	private $curHa;
	
	/** @var Importer[] */
	private $importers;
	
	/** @var Provider[] */
	private $providers;
	
	/** @var string */
	private $curPr;
	
	/** @var array */
	private $auth_users = array();
	
	/** @var array */
	private $auth_attempts = array();
	
	/** @var ServerAuthCommand[] */
	private $cmds;

	/** @var ServerAuthCommand[] */
	private $defcmds;
	
	/** @var bool */
	private $regmsg = true;
	
	/** @var callable */
	private $regmsghndl;
	
	/** @var bool */
	private $logmsg = true;
	
	/** @var callable */
	private $logmsghndl;
	
	/** @var array */
	public $cfg;
	
	/** @var array */
	public $chlang;
	
	/** @var array */
	public $regcache = array();
	
	/** @var ServerAuth */
	private static $instance = null;
	
	
	/**
	 * Translate Minecraft colors
	 *
	 * @param string $symbol
	 * @param string $message
	 *
	 * @return string
	 */
	public function translateColors($symbol, $message){
	    $message = str_replace($symbol . "0", TextFormat::BLACK, $message);
	    $message = str_replace($symbol . "1", TextFormat::DARK_BLUE, $message);
	    $message = str_replace($symbol . "2", TextFormat::DARK_GREEN, $message);
	    $message = str_replace($symbol . "3", TextFormat::DARK_AQUA, $message);
	    $message = str_replace($symbol . "4", TextFormat::DARK_RED, $message);
	    $message = str_replace($symbol . "5", TextFormat::DARK_PURPLE, $message);
	    $message = str_replace($symbol . "6", TextFormat::GOLD, $message);
	    $message = str_replace($symbol . "7", TextFormat::GRAY, $message);
	    $message = str_replace($symbol . "8", TextFormat::DARK_GRAY, $message);
	    $message = str_replace($symbol . "9", TextFormat::BLUE, $message);
	    $message = str_replace($symbol . "a", TextFormat::GREEN, $message);
	    $message = str_replace($symbol . "b", TextFormat::AQUA, $message);
	    $message = str_replace($symbol . "c", TextFormat::RED, $message);
	    $message = str_replace($symbol . "d", TextFormat::LIGHT_PURPLE, $message);
	    $message = str_replace($symbol . "e", TextFormat::YELLOW, $message);
	    $message = str_replace($symbol . "f", TextFormat::WHITE, $message);
	    
	    $message = str_replace($symbol . "k", TextFormat::OBFUSCATED, $message);
	    $message = str_replace($symbol . "l", TextFormat::BOLD, $message);
	    $message = str_replace($symbol . "m", TextFormat::STRIKETHROUGH, $message);
	    $message = str_replace($symbol . "n", TextFormat::UNDERLINE, $message);
	    $message = str_replace($symbol . "o", TextFormat::ITALIC, $message);
	    $message = str_replace($symbol . "r", TextFormat::RESET, $message);
	    return $message;
	}
	
	/**
	 * Replace variables inside a string
	 *
	 * @param string $str
	 * @param array $vars
	 *
	 * @return string
	 */
	public function replaceVars($str, array $vars){
	    foreach($vars as $key => $value){
	        $str = str_replace("{" . $key . "}", $value, $str);
	    }
	    return $str;
	}
	
	public function onLoad(){
	    if(!self::$instance instanceof ServerAuth){
	        self::$instance = $this;
	    }
	}
	
	public function onEnable(){
	    @mkdir($this->getDataFolder());
	    @mkdir($this->getDataFolder() . "languages/");
	    foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->getFile() . "resources/languages")) as $resource){
	        $resource = str_replace("\\", "/", $resource);
	        $resarr = explode("/", $resource);
	        if(substr($resarr[count($resarr) - 1], strrpos($resarr[count($resarr) - 1], '.') + 1) == "yml"){
	            $this->saveResource("languages/" . $resarr[count($resarr) - 1]);
	        }
	    }
	    $this->saveDefaultConfig();
	    $this->cfg = $this->getConfig()->getAll();
	    $this->chlang = $this->getConfigLanguage()->getAll();
	    $this->getServer()->getPluginManager()->callEvent(new ServerAuthLoadPluginEvent());
	    $this->registerHashAlg(new DefaultHash($this));
	    $this->registerHashAlg(new DefaultHashSalted($this));
	    $this->registerHashAlg(new SimpleAuthHash($this));
	    if(!$this->setHashAlg($this->cfg["password-hash"]["algorithm"])){
	        $this->getServer()->getLogger()->warning($this->translateColors("&", self::PREFIX . $this->chlang["errors"]["invalid-hash-algorithm"]));
	        $this->setDataProvider("yaml");
	    }
	    $this->registerDataProvider(new YAMLProvider($this));
	    $this->registerDataProvider(new MySQLProvider($this));
	    $this->registerDataProvider(new SQLiteProvider($this));
	    if(!$this->setDataProvider($this->cfg["data-provider"])){
	        $this->getServer()->getLogger()->warning($this->translateColors("&", self::PREFIX . $this->chlang["errors"]["invalid-provider"]));
	        $this->setDataProvider("yaml");
	    }
	    if(!$this->getDataProvider()->init()){
	        $this->getServer()->getLogger()->warning($this->translateColors("&", self::PREFIX . $this->chlang["errors"]["provider-error"]));
	        $this->setDataProvider("yaml");
	        $this->getDataProvider()->init();
	    }
	    $this->registerImporter(new ServerAuthYAMLImporter($this));
	    $this->registerImporter(new ServerAuthMySQLImporter($this));
	    $this->registerImporter(new ServerAuthSQLiteImporter($this));
	    $this->registerImporter(new SimpleAuthYAMLImporter($this));
	    $this->registerImporter(new SimpleAuthMySQLImporter($this));
	    $this->registerImporter(new SimpleAuthSQLiteImporter($this));
	    $this->registerImporter(new HereAuthJSONImporter($this));
	    $this->registerImporter(new HereAuthMySQLImporter($this));
	    $this->getCommand("serverauth")->setExecutor(new Commands\Commands($this));
	    $this->defcmds[self::CMD_REGISTER] = new Commands\Register($this);
	    $this->defcmds[self::CMD_UNREGISTER] = new Commands\Unregister($this);
	    $this->defcmds[self::CMD_LOGIN] = new Commands\Login($this);
	    $this->defcmds[self::CMD_LOGOUT] = new Commands\Logout($this);
	    $this->defcmds[self::CMD_CHPASSW] = new Commands\ChangePassword($this);
	    $this->cmds = $this->defcmds;
	    $cmdh = new Commands\ServerAuthCommandHandler($this);
	    $this->getCommand("register")->setExecutor($cmdh);
	    $this->getCommand("login")->setExecutor($cmdh);
	    $this->getCommand("logout")->setExecutor($cmdh);
	    $this->getCommand("changepassword")->setExecutor($cmdh);
	    $this->getCommand("unregister")->setExecutor($cmdh);
	    $reghndl = function(ServerAuth $plugin, Player $player){
            if($plugin->cfg["register"]["confirm-required"]){
                $player->sendMessage($plugin->translateColors("&", $plugin->getPrefix() . $plugin->chlang["register"]["message-conf"]));
            }else{
                $player->sendMessage($plugin->translateColors("&", $plugin->getPrefix() . $plugin->chlang["register"]["message"]));
            }
	    };
	    $this->regmsghndl = $reghndl;
	    $loghndl = function(ServerAuth $plugin, Player $player){
	        $player->sendMessage($plugin->translateColors("&", $plugin->getPrefix() . $plugin->chlang["login"]["message"]));
	    };
	    $this->logmsghndl = $loghndl;
	    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	    $this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\MessageTask($this), 20);
	}
	
	/*** API Functions ***/
	
	/**
	 * Get ServerAuth API
	 *
	 * @return ServerAuth
	 */
	public static function getAPI(){
	    return self::$instance;
	}
	
	/**
	 * Get ServerAuth version
	 *
	 * @return string
	 */
	public function getVersion(){
	    return $this->getDescription()->getVersion();
	}
	
	/**
	 * Get ServerAuth API version
	 *
	 * @return string
	 */
	public function getAPIVersion(){
	    return self::API_VERSION;
	}
	
	/**
	 * Register ServerAuth command
	 * 
	 * @param ServerAuthCommand $cmd
	 * 
	 * @return bool
	 */
	public function registerAuthCommand(ServerAuthCommand $cmd){
	    if($cmd->getType() >= 0 && $cmd->getType() <= 4){
	        $this->cmds[$cmd->getType()] = $cmd;
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Unregister ServerAuth command
	 * 
	 * @param int $id
	 * 
	 * @return bool
	 */
	public function unregisterAuthCommand($id){
	    if($id >= 0 && $id <= 4){
	        $this->cmds[$id] = $this->defcmds[$id];
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Get ServerAuth command
	 * 
	 * @return ServerAuthCommand
	 */
	public function getAuthCommand($id) : ?ServerAuthCommand {
	    if(isset($this->cmds[$id])){
	        return $this->cmds[$id];
	    }
	    return null;
	}
	
	/**
	 * Register hash algorithm
	 *
	 * @param HashAlg $hashalg
	 *
	 * @return bool
	 */
	public function registerHashAlg(HashAlg $hashalg) : bool {
	    $id = strtolower($hashalg->getId());
	    if(isset($this->hashalgs[$id])){
	        return false;
	    }
	    $this->hashalgs[$id] = $hashalg;
	    return true;
	}
	
	/**
	 * Set hash algorithm
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function setHashAlg($id){
	    $id = strtolower($id);
	    if(isset($this->hashalgs[$id])){
	        $this->curHa = $id;
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Get current hash algorithm
	 *
	 * @param string $id
	 *
	 * @return HashAlg|null
	 */
	public function getHashAlg() : ?HashAlg {
	    return $this->hashalgs[$this->curHa];
	}
	
	
	/**
	 * Get hash algorithm by id
	 *
	 * @param string $id
	 *
	 * @return HashAlg|null
	 */
	public function getHashAlgById($id) : ?HashAlg {
	    $id = strtolower($id);
	    if(isset($this->hashalgs[$id])){
	        return $this->hashalgs[$id];
	    }
	    return null;
	}
	
	/**
	 * Register data provider
	 *
	 * @param Provider $provider
	 *
	 * @return bool
	 */
	public function registerDataProvider(Provider $provider) : bool {
	    $id = strtolower($provider->getId());
	    if(isset($this->providers[$id])){
	        return false;
	    }
	    $this->providers[$id] = $provider;
	    return true;
	}
	
	/**
	 * Set data provider
	 * 
	 * @param string $id
	 * 
	 * @return bool
	 */
	public function setDataProvider($id){
	    $id = strtolower($id);
	    if(isset($this->providers[$id])){
	        $this->curPr = $id;
	        return true;
	    }
	    return false;
	}
	
	/**
	 * Get ServerAuth data provider
	 *
	 * @return Provider
	 */
	public function getDataProvider() : Provider {
	    return $this->providers[$this->curPr];
	}
	
	/**
	 * Get ServerAuth data provider by id
	 *
	 * @param string $id
	 *
	 * @return Provider|null
	 */
	public function getDataProviderById($id) : ?Provider {
	    $id = strtolower($id);
	    if(isset($this->providers[$id])){
	        return $this->providers[$id];
	    }
	    return null;
	}
	
	/**
	 * Register importer
	 *
	 * @param Importer $importer
	 *
	 * @return bool
	 */
	public function registerImporter(Importer $importer) : bool {
	    $id = strtolower($importer->getId());
	    if(isset($this->importers[$id])){
	        return false;
	    }
	    $this->importers[$id] = $importer;
	    return true;
	}
	
	/**
	 * Get importer by id
	 *
	 * @param string $id
	 * 
	 * @return Importer|null
	 */
	public function getImpoter($id) : ?Importer {
	    $id = strtolower($id);
	    if(isset($this->importers[$id])){
	        return $this->importers[$id];
	    }
	    return null;
	}
	
	/**
	 * Get config prefix
	 * 
	 * @return string
	 */
	public function getPrefix(){
	    return $this->cfg["prefix"];
	}
	
	/**
	 * Get config language ID
	 * 
	 * @return string
	 */
	public function getLanguageId(){
	    return $this->cfg["language"];
	}
	
	/**
	 * Check if registration is enabled
	 * 
	 * @return bool
	 */
	public function isRegisterEnabled(){
	    return $this->cfg["register"]["enabled"];
	}
	
	/**
	 * Check if unregistration is enabled
	 * 
	 * @return bool
	 */
	public function isUnregisterEnabled(){
	    return $this->cfg["unregister"]["enabled"];
	}
	
	
	/**
	 * Check if change password is enabled
	 * 
	 * @return bool
	 */
	public function isChangePasswordEnabled(){
	    return $this->cfg["changepassword"]["enabled"];
	}
	
	/**
	 * Check if login is enabled
	 * 
	 * @return bool
	 */
	public function isLoginEnabled(){
	    return $this->cfg["login"]["enabled"];
	}
	
	/**
	 * Check if logout is enabled
	 * 
	 * @return bool
	 */
	public function isLogoutEnabled(){
	    return $this->cfg["logout"]["enabled"];
	}
	
	/**
	 * Check if register message is enabled
	 * 
	 * @return bool
	 */
	public function isRegisterMessageEnabled(){
	    return $this->regmsg;
	}
	
	/**
	 * Enable or disable register message
	 * 
	 * @param bool $status
	 */
	public function setRegisterMessageStatus(bool $status = true){
	    $this->regmsg = $status;
	}
	
	/**
	 * Set register message handler
	 * 
	 * @param callable $handler
	 */
	public function setRegisterMessageHandler(callable $handler){
	    $this->regmsghndl = $handler;
	}
	
	/**
	 * @internal
	 * 
	 * Call register message handler
	 * 
	 * @param ServerAuth $plugin
	 * @param Player $player
	 */
	public function callRegisterMessageHandler(ServerAuth $plugin, Player $player){
	    ($this->regmsghndl)($plugin, $player);
	}
	
	/**
	 * Check if login message is enabled
	 * 
	 * @return bool
	 */
	public function isLoginMessageEnabled(){
	    return $this->logmsg;
	}
	
	/**
	 * Enable or disable login message
	 * 
	 * @param bool $status
	 */
	public function setLoginMessage(bool $status = true){
	    $this->logmsg = $status;
	}
	
	/**
	 * Set login message handler
	 * 
	 * @param callable $handler
	 */
	public function setLoginMessageHandler(callable $handler){
	    $this->logmsghndl = $handler;
	}
	
	/**
	 * @internal
	 * 
	 * Call login message handler
	 * 
	 * @param ServerAuth $plugin
	 * @param Player $player
	 */
	public function callLoginMessageHandler(ServerAuth $plugin, Player $player){
	    ($this->logmsghndl)($plugin, $player);
	}
	
	/**
	 * Register an account
	 * 
	 * @param Player $player
	 * @param string $password
	 * @param string $cmessage
	 * 
	 * @return int
	 */
	public function registerAccount(Player $player, $password, &$cmessage = null) : int {
	    if($this->isAccountRegistered($player->getName())){
	        return self::ERR_ALREADY_REG;
	    }
	    $saevent = new ServerAuthRegisterEvent($player, $password);
	    $saevent->setCancelledMessage($this->chlang["operation-cancelled"]);
	    $this->getServer()->getPluginManager()->callEvent($saevent);
	    if($saevent->isCancelled()){
	        $cmessage = $saevent->getCancelledMessage();
	        return self::CANCELLED;
	    }
	    if(mb_strlen($password) <= $this->cfg["min-password"]){
	        return self::ERR_PASS_SHORT;
	    }else if(mb_strlen($password) >= $this->cfg["max-password"]){
	        return self::ERR_PASS_LONG;
	    }
	    $status = $this->getDataProvider()->registerAccount($player, $password);
	    if($status == self::SUCCESS){
	        $this->updateRegistrationCache($player->getName(), true);
	    }
	    return $status;
	}
	
	/**
	 * Unregister an account
	 * 
	 * @param string $player
	 * @param string $cmessage
	 * 
	 * @return int
	 */
	public function unregisterAccount($player, &$cmessage = null) : int {
	    if(!$this->getDataProvider()->isAccountRegistered($player)){
	        $this->updateRegistrationCache($player, false);
	        return self::ERR_NOT_REG;
	    }
	    $saevent = new ServerAuthUnregisterEvent($player);
	    $saevent->setCancelledMessage($this->chlang["operation-cancelled"]);
	    $this->getServer()->getPluginManager()->callEvent($saevent);
	    if($saevent->isCancelled()){
	        $cmessage = $saevent->getCancelledMessage();
	        return self::CANCELLED;
	    }
	    $status = $this->getDataProvider()->unregisterAccount($player);
	    if($status != self::SUCCESS) return $status;
	    if(($opl = $this->getServer()->getPlayer($player))){
	        $this->deauthenticatePlayer($opl);
	    }
	    if($status == self::SUCCESS){
	        $this->updateRegistrationCache($player, false);
	    }
	    return $status;
	}
	
	/**
	 * Change account password
	 * 
	 * @param string $player
	 * @param string $newpassword
	 * @param string $cmessage
	 * 
	 * @return int
	 */
	public function changeAccountPassword($player, $newpassword, &$cmessage = null){
	    if(!$this->getDataProvider()->isAccountRegistered($player)){
	        $this->updateRegistrationCache($player, false);
	        return self::ERR_NOT_REG;
	    }
	    $saevent = new ServerAuthChangePasswordEvent($player, $newpassword);
	    $saevent->setCancelledMessage($this->chlang["operation-cancelled"]);
	    $this->getServer()->getPluginManager()->callEvent($saevent);
	    if($saevent->isCancelled()){
	        $cmessage = $saevent->getCancelledMessage();
	        return self::CANCELLED;
	    }
	    if(mb_strlen($newpassword) <= $this->cfg["min-password"]){
	        return self::ERR_PASS_SHORT;
	    }else if(mb_strlen($newpassword) >= $this->cfg["max-password"]){
	        return self::ERR_PASS_LONG;
	    }
	    return $this->getDataProvider()->changeAccountPassword($player, $newpassword);
	}
	
	/**
	 * Check if account is registered
	 * 
	 * @param string $player
	 * 
	 * @return bool
	 */
	public function isAccountRegistered($player) : bool {
	    if($this->cfg["data-cache-timeout"] > 0 && isset($this->regcache[$player]) && time() - $this->regcache[$player][1] < $this->cfg["data-cache-timeout"]){
	        return $this->regcache[$player][0];
	    }
	    $status = $this->getDataProvider()->isAccountRegistered($player);
	    $this->updateRegistrationCache($player, $status);
	    return $status;
	}
	
	/**
	 * Check if a player is authenticated
	 *
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function isPlayerAuthenticated(Player $player) : bool {
	    return array_key_exists(strtolower($player->getName()), $this->auth_users);
	}
	
	/**
	 * Authenticate player
	 * 
	 * @param Player $player
	 * @param string $password
	 * @param bool $hashed
	 * @param string $cmessage
	 * 
	 * @return int
	 */
	public function authenticatePlayer(Player $player, $password, bool $hashed = false, &$cmessage = null) : int {
	    if(!$this->getDataProvider()->isAccountRegistered($player->getName())){
	        $this->updateRegistrationCache($player->getName(), false);
	        return self::ERR_NOT_REG;
	    }
	    if($this->isPlayerAuthenticated($player)){
	        return self::ERR_ALREADY_AUTH;
	    }
	    $saevent = new ServerAuthAuthenticateEvent($player);
	    $saevent->setCancelledMessage($this->chlang["operation-cancelled"]);
	    $this->getServer()->getPluginManager()->callEvent($saevent);
	    if($saevent->isCancelled()){
	        $cmessage = $saevent->getCancelledMessage();
	        return self::CANCELLED;
	    }
	    $status = $this->getDataProvider()->checkAuthentication($player, $password, $hashed);
	    if($status == self::SUCCESS){
	        $this->auth_users[strtolower($player->getName())] = null;
	        $this->resetAuthAttempts($player);
	    }else if($status == self::ERR_WRONG_PASS && $this->cfg["login"]["max-attempts"] > 0){
	        if(isset($this->auth_attempts[$player->getName()])){
	            if($this->auth_attempts[$player->getName()] >= $this->cfg["login"]["max-attempts"]){
	                return self::ERR_TOO_MANY_ATTEMPTS;
	            }
	            $this->auth_attempts[$player->getName()]++;
	        }else{
	            $this->auth_attempts[$player->getName()] = 1;
	        }
	    }
	    return $status;
	}
	
	/**
	 * Deauthenticate player
	 * 
	 * @param Player $player
	 * @param string $cmessage
	 * 
	 * @return int
	 */
	public function deauthenticatePlayer(Player $player, &$cmessage = null) : int {
        if($this->isPlayerAuthenticated($player)){
            $saevent = new ServerAuthDeauthenticateEvent($player);
            $saevent->setCancelledMessage($this->chlang["operation-cancelled"]);
            $this->getServer()->getPluginManager()->callEvent($saevent);
            if($saevent->isCancelled()){
                $cmessage = $saevent->getCancelledMessage();
                return self::CANCELLED;
            }
            unset($this->auth_users[strtolower($player->getName())]);
            return self::SUCCESS;
        }
        return self::ERR_NOT_AUTH;
	}
	
	/**
	 * Get player account data
	 * 
	 * @param string $player
	 * 
	 * @return int
	 */
	public function getPlayerData($player){
	    if(!$this->getDataProvider()->isAccountRegistered($player)){
	        return self::ERR_NOT_REG;
	    }
	    return $this->getDataProvider()->getAccountData($player);
	}
	
	/**
	 * Reset player authentication attempts
	 *
	 * @param Player $player
	 */
	public function resetAuthAttempts(Player $player){
	    if(isset($this->auth_attempts[$player->getName()])){
	        unset($this->auth_attempts[$player->getName()]);
	    }
	}
	
	/**
	 * @internal
	 *
	 * Update registration cache
	 *
	 * @param string $player
	 * @param bool $val
	 */
	private function updateRegistrationCache($player, bool $val){
	    if($this->cfg["data-cache-timeout"] > 0){
	        if($this->cfg["max-cached-players"] > 0 && count($this->regcache) > $this->cfg["max-cached-players"]){
	            array_shift($this->regcache);
	        }
	        $this->regcache[strtolower($player)][0] = $val;
	        $this->regcache[strtolower($player)][1] = time();
	    }
	}
    
    /**
     * Hash password
     * 
     * @param string $password
     * @param HashAlg $hashalg
     * @param string $params
     * 
     * @return string
     */
    public function hashPassword($password, HashAlg $hashalg = null, $params = null) : string {
        if(!$hashalg){
            $hashalg = $this->getHashAlg();
        }
        return $hashalg->hash($password, $params);
    }
    
    /**
     * Encode array to HashAlg parameters string ("key1:value1,key2:value2..." is the format of the returned string)
     * 
     * @param array $params
     * @return string
     */
    public function encodeParams(array $params){
        $estr = "";
        $i = 0;
        foreach($params as $k => $v){
            $estr .= $k . ":" . $v;
            $i++;
            if($i < count($params)){
                $estr .= ",";
            }
        }
        return $estr;
    }
    
    /**
     * Decode HashAlg parameters string to array
     * 
     * @param string $str
     * 
     * @return string[]
     */
    public function decodeParams($str){
        $array = explode(",", $str);
        $params = array();
        foreach($array as $fp){
            $k = strstr($fp, ':', true);
            $v = substr(strstr($fp, ':'), 1);
            if($k && $v){
                $params[$k] = $v;
            }
        }
        return $params;
    }
    
    /**
     * Get language data
     * 
     * @param string $language
     * 
     * @return Config
     */
    public function getLanguage(string $language) : Config {
    	if(file_exists($this->getDataFolder() . "languages/" . $language . ".yml")){
    		return new Config($this->getDataFolder() . "languages/" . $language . ".yml", Config::YAML);
    	}elseif(file_exists($this->getDataFolder() . "languages/EN_en.yml")){
    		return new Config($this->getDataFolder() . "languages/EN_en.yml", Config::YAML);
    	}else{
    		@mkdir($this->getDataFolder() . "languages/");
    		$this->saveResource("languages/EN_en.yml");
    		return new Config($this->getDataFolder() . "languages/EN_en.yml", Config::YAML);
    	}
    }
    
    /**
     * Get the ServerAuth language specified in config
     * 
     * @return Config
     */
    public function getConfigLanguage() : Config {
    	return $this->getLanguage($this->cfg["language"]);
    }
}