<?php

/*
 * ServerAuth (v1.00) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 22/05/2015 11:50 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;

class ServerAuth extends PluginBase {
	
	//About Plugin Const
	
	/** @var string PRODUCER Plugin producer */
	const PRODUCER = "EvolSoft";
	
	/** @var string VERSION Plugin version */
	const VERSION = "1.00";
	
	/** @var string MAIN_WEBSITE Plugin producer website */
	const MAIN_WEBSITE = "http://www.evolsoft.tk";
	
	//Other Const
	
	/** @var string PREFIX Plugin prefix */
	const PREFIX = "&1[ServerAuth] ";
	
	//Error const
	
	/** @var int ERR_USER_NOT_REGISTERED User not registered */
	const ERR_USER_NOT_REGISTERED = 0;

	/** @var int SUCCESS Success */
	const SUCCESS = 1;
	
	/** @var int ERR_WRONG_PASSWORD Wrong password error */
	const ERR_WRONG_PASSWORD = 2;
	
	/** @var int ERR_USER_NOT_AUTHENTICATED User not authenticated error */
	const ERR_USER_NOT_AUTHENTICATED = 3;
	
	/** @var int ERR_USER_ALREADY_AUTHENTICATED User already authenticated error */
	const ERR_USER_ALREADY_AUTHENTICATED = 4;
	
	/** @var int ERR_USER_ALREADY_REGISTERED User already registered error */
	const ERR_USER_ALREADY_REGISTERED = 5;
	
	/** @var int ERR_PASSWORD_TOO_SHORT Password too short error */
	const ERR_PASSWORD_TOO_SHORT = 6;
	
	/** @var int ERR_PASSWORD_TOO_LONG Password too long error */
	const ERR_PASSWORD_TOO_LONG = 7;
	
	/** @var int ERR_MAX_IP_REACHED Max number of same IPs reached error */
	const ERR_MAX_IP_REACHED = 8;
	
	/** @var int ERR_GENERIC A generic error */
	const ERR_GENERIC = 9;
	
	/** @var array $auth_users Current authenticated users */
	private $auth_users = array();

    /** @var boolean $mysql Use mysql */
    public $mysql;
    
    /** @var \mysqli $datbase MySQLi instance */
    private $database;
    
    /** @var boolean $register_message Register Message status */
    private $register_message = true;
    
    /** @var boolean $login_message Login Message status */
    private $login_message = true;
    
    /** @var ServerAuth $object Plugin instance */
    private static $object = null;
    
    /**
     * Get ServerAuth instance
     * 
     * @return ServerAuth ServerAuth API instance
     */
    public static function getAPI(){
    	return self::$object;
    }
    
    public function onLoad(){
    	if(!(self::$object instanceof ServerAuth)){
    		self::$object = $this;
    	}
    }
    
    /**
     * Translate Minecraft colors
     * 
     * @param char $symbol Color symbol
     * @param string $message The message to be translated
     * 
     * @return string The translated message
     */
    public function translateColors($symbol, $message){
    
    	$message = str_replace($symbol."0", TextFormat::BLACK, $message);
    	$message = str_replace($symbol."1", TextFormat::DARK_BLUE, $message);
    	$message = str_replace($symbol."2", TextFormat::DARK_GREEN, $message);
    	$message = str_replace($symbol."3", TextFormat::DARK_AQUA, $message);
    	$message = str_replace($symbol."4", TextFormat::DARK_RED, $message);
    	$message = str_replace($symbol."5", TextFormat::DARK_PURPLE, $message);
    	$message = str_replace($symbol."6", TextFormat::GOLD, $message);
    	$message = str_replace($symbol."7", TextFormat::GRAY, $message);
    	$message = str_replace($symbol."8", TextFormat::DARK_GRAY, $message);
    	$message = str_replace($symbol."9", TextFormat::BLUE, $message);
    	$message = str_replace($symbol."a", TextFormat::GREEN, $message);
    	$message = str_replace($symbol."b", TextFormat::AQUA, $message);
    	$message = str_replace($symbol."c", TextFormat::RED, $message);
    	$message = str_replace($symbol."d", TextFormat::LIGHT_PURPLE, $message);
    	$message = str_replace($symbol."e", TextFormat::YELLOW, $message);
    	$message = str_replace($symbol."f", TextFormat::WHITE, $message);
    
    	$message = str_replace($symbol."k", TextFormat::OBFUSCATED, $message);
    	$message = str_replace($symbol."l", TextFormat::BOLD, $message);
    	$message = str_replace($symbol."m", TextFormat::STRIKETHROUGH, $message);
    	$message = str_replace($symbol."n", TextFormat::UNDERLINE, $message);
    	$message = str_replace($symbol."o", TextFormat::ITALIC, $message);
    	$message = str_replace($symbol."r", TextFormat::RESET, $message);
    
    	return $message;
    }
    
    /**
     * Check MySQL database status
     * 
     * @param string $host MySQL host
     * @param string $port MySQL port
     * @param string $username MySQL username
     * @param string $password MySQL password
     * 
     * @return array true on success or false on error + error details
     */
    public function checkDatabase($host, $port, $username, $password){
    	$status = array();
    	$db = @new \mysqli($host, $username, $password, null, $port);
    	if($db->connect_error){
    		$status[0] = false;
    		$status[1] = $db->connect_error;
    		return $status;
    	}else{
    		$db->close();
    		$status[0] = true;
    		$status[1] = "Success!";
    		return $status;
    	}
    }
	
    /**
     * Initialize MySQL database connection
     * 
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string $table_prefix
     * 
     * @return boolean true on SUCCESS, false on error
     */
    public function initializeDatabase($host, $port, $username, $password, $database, $table_prefix){
    	$db = @new \mysqli($host, $username, $password, null, $port);
    	if($db->connect_error){
    		return false;
    	}else{
    		$query = "CREATE DATABASE " . $database;
    		if ($db->query($query) == true) {
    			$db->select_db($database);
    			//Create Tables
    			if(\mysqli_num_rows($db->query("SHOW TABLES LIKE '" . $table_prefix . "serverauth'")) == 0){
    				$query = "CREATE TABLE " . $table_prefix . "serverauth (version VARCHAR(50), api_version VARCHAR(50), password_hash VARCHAR(50))";
    				$db->query($query);
    			}
    			if(\mysqli_num_rows($db->query("SHOW TABLES LIKE '" . $table_prefix . "serverauthdata'")) == 0){
    				$query = "CREATE TABLE " . $table_prefix . "serverauthdata (user VARCHAR(50), password VARCHAR(200), ip VARCHAR(50), firstlogin VARCHAR(50), lastlogin VARCHAR(50))";
    				$db->query($query);
    			}
    		    //Initialize Tables
    		    if(\mysqli_num_rows($db->query("SELECT version, api_version FROM " . $table_prefix . "serverauth")) == 0){
    				$query = "INSERT INTO " . $table_prefix . "serverauth (version, api_version, password_hash) VALUES ('" . $this->getVersion() . "', '" . $this->getAPIVersion() . "', '" . $this->getPasswordHash() . "')";
    				$db->query($query);
    			}else{
    				$query = "UPDATE " . $table_prefix . "serverauth SET version='" . $this->getVersion() . "', api_version='" . $this->getAPIVersion() . "', password_hash='" . $this->getPasswordHash() . "' LIMIT 1";
    				$db->query($query);
    			}
    		}else{
    			$db->select_db($database);
    			//Create Tables
    			if(\mysqli_num_rows($db->query("SHOW TABLES LIKE '" . $table_prefix . "serverauth'")) == 0){
    				$query = "CREATE TABLE " . $table_prefix . "serverauth (version VARCHAR(50), api_version VARCHAR(50), password_hash VARCHAR(50))";
    				$db->query($query);
    			}
    			if(\mysqli_num_rows($db->query("SHOW TABLES LIKE '" . $table_prefix . "serverauthdata'")) == 0){
    				$query = "CREATE TABLE " . $table_prefix . "serverauthdata (user VARCHAR(50), password VARCHAR(200), ip VARCHAR(50), firstlogin VARCHAR(50), lastlogin VARCHAR(50))";
    				$db->query($query);
    			}
    		    //Initialize Tables
    		    if(\mysqli_num_rows($db->query("SELECT version, api_version FROM " . $table_prefix . "serverauth")) == 0){
                    $query = "INSERT INTO " . $table_prefix . "serverauth (version, api_version, password_hash) VALUES ('" . $this->getVersion() . "', '" . $this->getAPIVersion() . "', '" . $this->getPasswordHash() . "')";
    				$db->query($query);
    			}else{
    				$query = "UPDATE " . $table_prefix . "serverauth SET version='" . $this->getVersion() . "', api_version='" . $this->getAPIVersion() . "', password_hash='" . $this->getPasswordHash() . "' LIMIT 1";
    				$db->query($query);
    			}
    		}
    		$this->database = $db;
    	}
    }
    
    public function onEnable(){
	    @mkdir($this->getDataFolder());
	    @mkdir($this->getDataFolder() . "users/");
	    @mkdir($this->getDataFolder() . "languages/");
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
        $this->saveResource("languages/EN_en.yml");
        $this->saveResource("languages/IT_it.yml");
        $this->saveResource("languages/ES_es.yml");
        $this->getCommand("serverauth")->setExecutor(new Commands\Commands($this));
        $this->getCommand("register")->setExecutor(new Commands\Register($this));
        $this->getCommand("login")->setExecutor(new Commands\Login($this));
        $this->getCommand("logout")->setExecutor(new Commands\Logout($this));
        $this->getCommand("changepassword")->setExecutor(new Commands\ChangePassword($this));
        $this->getCommand("unregister")->setExecutor(new Commands\Unregister($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\MessageTask($this), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\MySQLTask($this), 20);
        $this->mysql = false;
        //Check MySQL
        if($this->cfg["use-mysql"] == true){
        	$check = $this->checkDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"]);
        	if($check[0]){
        		$this->initializeDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"], $this->cfg["mysql"]["database"], $this->cfg["mysql"]["table_prefix"]);
        		Server::getInstance()->getLogger()->info($this->translateColors("&", ServerAuth::PREFIX . "&aServerAuth successfully connected to the MySQL database!"));
        		$this->mysql = true;
        	}else{
        		Server::getInstance()->getLogger()->info($this->translateColors("&", ServerAuth::PREFIX . "&cServerAuth can't connect to the MySQL database. Data will be saved locally. Error: " . $check[1]));
        	}
        }
    }
    
    //API Functions
    
    /** @var string API_VERSION ServerAuth API version */
    const API_VERSION = "1.0.0";
    
    /**
     * Get ServerAuth version
     * 
     * @return string ServerAuth version
     */
    public function getVersion(){
    	return ServerAuth::VERSION;
    }
    
    /**
     * Get ServerAuth API version
     * 
     * @return string ServerAuth API version
     */
    public function getAPIVersion(){
    	return ServerAuth::API_VERSION;
    }
    
    /**
     * Get the current MySQL database instance
     * 
     * @return mysqli|boolean
     */
    public function getDatabase(){
    	if($this->database instanceof \mysqli){
    		return $this->database;
    	}else{
    		return false;
    	}
    }
    
    /**
     * Get ServerAuth database configuration
     * 
     * @return array
     */
    public function getDatabaseConfig(){
    	return $this->getConfig()->getAll()["mysql"];
    }
    
    /**
     * Get ServerAuth data provider
     *
     * @return boolean true if ServerAuth is using MySQL, false if ServerAuth is using YAML config
     */
    public function getDataProvider(){
    	return $this->mysql;
    }
    
    /**
     * Check if register messages are enabled
     * 
     * @return boolean
     */
    public function areRegisterMessagesEnabled(){
    	return $this->register_message;
    }
    
    /**
     * Enable\Disable register messages
     * 
     * @param boolean $bool
     */
    public function enableRegisterMessages($bool = true){
    	if(is_bool($bool)){
    		$this->register_message = $bool;
    	}else{
    		$this->register_message = true;
    	}
    }
    
    /**
     * Check if login messages are enabled
     *
     * @return boolean
     */
    public function areLoginMessagesEnabled(){
    	return $this->login_message;
    }
    
    /**
     * Enable\Disable login messages
     *
     * @param boolean $bool
     */
    public function enableLoginMessages($bool = true){
    	if(is_bool($bool)){
    		$this->login_message = $bool;
    	}else{
    		$this->login_message = true;
    	}
    }
    
    /**
     * Get player data
     *
     * @param string $player
     *
     * @return array|int the array of player data on SUCCESS, otherwise the current error
     */
    public function getPlayerData($player){
    	if($this->isPlayerRegistered($player)){
    		if($this->getDataProvider()){
    			//Check MySQL connection
    			if($this->getDatabase() && $this->getDatabase()->ping()){
    				$query = "SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user='" . strtolower($player) . "'";
    				if($this->getDatabase()->query($query)){
    					$data = array(
    						"password" => $this->getDatabase()->query($query)->fetch_assoc()["password"],
    						"ip" => $this->getDatabase()->query($query)->fetch_assoc()["ip"],
    						"firstlogin" => $this->getDatabase()->query($query)->fetch_assoc()["firstlogin"],
                            "lastlogin" => $this->getDatabase()->query($query)->fetch_assoc()["lastlogin"]
    					);
    					return $data;
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				return ServerAuth::ERR_GENERIC;
    			}
    		}else{
    			$cfg = new Config($this->getDataFolder() . "users/" . strtolower($player . ".yml"), Config::YAML);
    			return $cfg->getAll();
    		}
    	}else{
    		return $this->isPlayerRegistered($player);	
    	}
    }
    
    /**
     * Get ServerAuth password hash
     * 
     * @return string
     */
    public function getPasswordHash(){
    	$cfg = $this->getConfig()->getAll();
    	return $cfg["passwordHash"];
    }
    
    /**
     * Get language data
     * 
     * @param string $language
     * 
     * @return \pocketmine\utils\Config
     */
    private function getLanguage($language){
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
     * @return \pocketmine\utils\Config
     */
    public function getConfigLanguage(){
    	$cfg = $this->getConfig()->getAll();
    	return $this->getLanguage($cfg["language"]);
    }
    
    /**
     * Check if a player is registered
     * 
     * @param string $player
     * 
     * @return boolean|int true or false on SUCCESS, otherwise the current error
     */
    public function isPlayerRegistered($player){
    	if($this->getDataProvider()){
    		//Check MySQL connection
    		if($this->getDatabase() && $this->getDatabase()->ping()){
    			if(\mysqli_num_rows($this->getDatabase()->query("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user='" . strtolower($player) . "'")) == 0){
    				return false;
    			}else{
    				return true;
    			}
    		}else{
    			return ServerAuth::ERR_GENERIC;
    		}
    	}else{
    		return file_exists($this->getDataFolder() . "users/" . strtolower($player . ".yml"));
    	}
    }
    
    /**
     * Check if a player is authenticated
     * 
     * @param Player $player
     * 
     * @return boolean
     */
    public function isPlayerAuthenticated(Player $player){
    	return isset($this->auth_users[array_search(strtolower($player->getName()), $this->auth_users)]);
    }
    
    /**
     * Register a player to ServerAuth
     * 
     * @param Player $player
     * @param string $password
     * 
     * @return true on SUCCESS, otherwise the current error
     */
    public function registerPlayer(Player $player, $password){
    	$cfg = $this->getConfig()->getAll();
    	if($this->isPlayerRegistered($player->getName())){
    		return ServerAuth::ERR_USER_ALREADY_REGISTERED;
    	}else{
    		if(strlen($password) <= $cfg["minPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_SHORT;
    		}elseif(strlen($password) >= $cfg["maxPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_LONG;
    		}else{
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					$query = "INSERT INTO " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata (user, password, ip, firstlogin, lastlogin) VALUES ('" . $player->getName() . "', '" . hash($this->getPasswordHash(), $password) . "', '" . $player->getAddress() . "', '" . $player->getFirstPlayed() . "', '" . $player->getLastPlayed() . "')";
    					if($this->getDatabase()->query($query)){
    						$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthRegisterEvent($player, $password));
    						return ServerAuth::SUCCESS;
    					}else{
    						return ServerAuth::ERR_GENERIC;
    					}
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    				$data->set("password", hash($this->getPasswordHash(), $password));
    				$data->set("ip", $player->getAddress());
    				$data->set("firstlogin", $player->getFirstPlayed());
    				$data->set("lastlogin", $player->getLastPlayed());
    				$data->save();
    				$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthRegisterEvent($player, $password));
    				return ServerAuth::SUCCESS;
    			}
    		}
    	}
    }
    
	/**
	 * Unregister a player
	 * 
	 * @param Player $player
	 * 
	 * @return int|boolean true on SUCCESS or false if the player is not registered, otherwise the current error
	 */
    public function unregisterPlayer(Player $player){
    	if($this->isPlayerRegistered($player->getName())){
    		if($this->getDataProvider()){
    			//Check MySQL connection
    			if($this->getDatabase() && $this->getDatabase()->ping()){
    				$query = "DELETE FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user='" . strtolower($player->getName()) . "'";
    				if($this->getDatabase()->query($query)){
    					//Restore default messages
    					ServerAuth::getAPI()->enableLoginMessages(true);
    					ServerAuth::getAPI()->enableRegisterMessages(true);
    					$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthUnregisterEvent($player));
    					return ServerAuth::SUCCESS;
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				return ServerAuth::ERR_GENERIC;
    			}
    		}else{
    			@unlink($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"));
    			//Restore default messages
    			ServerAuth::getAPI()->enableLoginMessages(true);
    			ServerAuth::getAPI()->enableRegisterMessages(true);
    			$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthUnregisterEvent($player));
    			return ServerAuth::SUCCESS;
    		}
    	}else{
    		return $this->isPlayerRegistered($player->getName());
    	}
    }
    
    /**
     * 
     * 
     * @param Player $player
     * @param unknown $password
     * @param string $hash
     * @return number|Ambigous <boolean, number>
     */
    public function authenticatePlayer(Player $player, $password, $hash = true){
    	if($hash){
    		$password = hash($this->getPasswordHash(), $password);
    	}
    	if($this->isPlayerRegistered($player->getName())){
    		if(!$this->isPlayerAuthenticated($player)){
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					$query = "SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user='" . strtolower($player->getName()) . "'";
    					$db_password = $this->getDatabase()->query($query)->fetch_assoc()["password"];
    					if($db_password){
    						if($password == $db_password){
    							$query = "UPDATE " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata SET ip='" . $player->getAddress() . "', lastlogin='" . $player->getLastPlayed() . "' WHERE user='" . strtolower($player->getName()) . "'";
    							if($this->getDatabase()->query($query)){
    								array_push($this->auth_users, strtolower($player->getName()));
    								$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthAuthenticateEvent($player));
    								return ServerAuth::SUCCESS;
    							}else{
    								return ServerAuth::ERR_GENERIC;
    							}
    						}else{
    							return ServerAuth::ERR_WRONG_PASSWORD;
    						}
    					}else{
    						return ServerAuth::ERR_GENERIC;
    					}
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    				if($password == $data->get("password")){
    					$data->set("ip", $player->getAddress());
    					$data->set("lastlogin", $player->getLastPlayed());
    					$data->save();
    					array_push($this->auth_users, strtolower($player->getName()));
    					$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthAuthenticateEvent($player));
    					return ServerAuth::SUCCESS;
    				}else{
    					return ServerAuth::ERR_WRONG_PASSWORD;
    				}
    			}
    		}else{
    			return ServerAuth::ERR_USER_ALREADY_AUTHENTICATED;
    		}
    	}else{
    		return $this->isPlayerRegistered($player->getName());
    	}
    }
    
    public function deauthenticatePlayer(Player $player){
    	if($this->isPlayerRegistered($player->getName())){
    		if($this->isPlayerAuthenticated($player)){
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					$query = "UPDATE " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata SET ip='" . $player->getAddress() .  "' WHERE user='" . strtolower($player->getName()) . "'";
    					//Restore default messages
    					ServerAuth::getAPI()->enableLoginMessages(true);
    					ServerAuth::getAPI()->enableRegisterMessages(true);
    					unset($this->auth_users[array_search(strtolower($player->getName()), $this->auth_users)]);
    					if($this->getDatabase()->query($query)){
    						$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthDeauthenticateEvent($player));
    						return ServerAuth::SUCCESS;
    					}else{
    						return ServerAuth::ERR_GENERIC;
    					}
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    				$data->set("ip", $player->getAddress());
    				$data->save();
    				//Restore default messages
    				ServerAuth::getAPI()->enableLoginMessages(true);
    				ServerAuth::getAPI()->enableRegisterMessages(true);
    				unset($this->auth_users[array_search(strtolower($player->getName()), $this->auth_users)]);
    				$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthDeauthenticateEvent($player));
    				return ServerAuth::SUCCESS;
    			}
    		}else{
    			return ServerAuth::ERR_USER_NOT_AUTHENTICATED;
    		}
    	}else{
    		return $this->isPlayerRegistered($player->getName());
    	}
    }
    
	/**
	 * Change player password
	 * 
	 * @param Player $player
	 * @param string $new_password
	 * 
	 * @return int|boolean true on SUCCESS or false if the player is not registered, otherwise the current error
	 */
    public function changePlayerPassword(Player $player, $new_password){
    	$cfg = $this->getConfig()->getAll();
    	if($this->isPlayerRegistered($player->getName())){
    		if(strlen($new_password) <= $cfg["minPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_SHORT;
    		}elseif(strlen($new_password) >= $cfg["maxPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_LONG;
    		}else{
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					$query = "UPDATE " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata SET password='" . hash($this->getPasswordHash(), $new_password) . "' WHERE user='" . strtolower($player->getName()) . "'";
    					if($this->getDatabase()->query($query)){
    						$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthPasswordChangeEvent($player, $new_password));
    						return ServerAuth::SUCCESS;
    					}else{
    						return ServerAuth::ERR_GENERIC;
    					}
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    				$data->set("password", hash($this->getPasswordHash(), $new_password));
    				$data->save();
    				$this->getServer()->getPluginManager()->callEvent(new Events\ServerAuthPasswordChangeEvent($player, $new_password));
    				return ServerAuth::SUCCESS;
    			}	
    		}
    	}else{
    		return $this->isPlayerRegistered($player->getName());
    	}
    }
    
}
?>
