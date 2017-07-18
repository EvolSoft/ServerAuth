<?php

/*
 * ServerAuth (v2.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 07/01/2016 07:43 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

namespace ServerAuth;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandExecutor;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\IPlayer;
use pocketmine\Player;
use pocketmine\OfflinePlayer;

class ServerAuth extends PluginBase {
	
	//About Plugin Const
	
	/** @var string PRODUCER Plugin producer */
	const PRODUCER = "EvolSoft";
	
	/** @var string VERSION Plugin version */
	const VERSION = "2.13";
	
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
	
	/** @var int CANCELLED Operation cancelled */
	const CANCELLED = 10;
	
	/** @var int TOO_MANY_ATTEMPTS Too many failed login attempts */
	const TOO_MANY_ATTEMPTS = 11;
	
	/** @var array $auth_users Current authenticated users */
	private $auth_users = array();
	
	/** @var array $auth_attempts Authentication attempts for each username */
	private $auth_attempts = array();
	
	/** @var Config $chlang Cached language file */
	public $chlang;
	
	/** @var array $cached_registered_usrs Cached registered users array */
	public $cached_registered_users = array();
	
	/** @var Task $task MySQL task */
	public $task;

    /** @var bool $mysql Use mysql */
    public $mysql;
    
    /** @var string $canc_message Message on cancelled event */
    public $canc_message;
    
    /** @var \mysqli $datbase MySQLi instance */
    private $database;
    
    /** @var bool $register_message Register Message status */
    private $register_message = true;
    
    /** @var bool $login_message Login Message status */
    private $login_message = true;
    
    /** @var ServerAuth $object Plugin instance */
    private static $object = null;
    
    /**
     * Get ServerAuth instance
     * 
     * @return ServerAuth ServerAuth API instance
     */
    public static function getAPI() : ServerAuth{
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
    public function translateColors($symbol, $message) : string{
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
     * Replace arrays in message
     *
     * @param string $message The message
     * @param array $array The values to replace
     *
     * @return string the message
     */
    public function replaceArrays($message, $array) : string{
    	foreach($array as $key => $value){
    		$message = str_replace("{" . strtoupper($key) . "}", $value, $message);
    	}
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
     * @return bool true on SUCCESS, false on error
     */
    public function initializeDatabase(string $host, int $port, string $username, string $password, string $database, string $table_prefix) : bool{
    	$db = @new \mysqli($host, $username, $password, null, $port);
    	if(!$db->connect_error){
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
		return true;
    	}
	return false;
    }
    
    /**
     * Search string in yml files
     * 
     * @param string $path Search path
     * @param string $str The string to search
     * 
     * @return int $count The number of occurrencies
     */
    private function grep(string $path, string $str) : int{
    	$count = 0;
    	foreach(glob($path . "*.yml") as $filename){
    		foreach(file($filename) as $fli=>$fl){
    			if(strpos($fl, $str) !== false){
    				$count += 1;
    			}
    		}
    	}
    	return $count;
    }
    
    public function onEnable(){
	    @mkdir($this->getDataFolder());
	    @mkdir($this->getDataFolder() . "users/");
	    @mkdir($this->getDataFolder() . "languages/");
	    //Save Languages
	    foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->getFile() . "resources/languages")) as $resource){
	    	$resource = str_replace("\\", "/", $resource);
	    	$resarr = explode("/", $resource);
	    	if(substr($resarr[count($resarr) - 1], strrpos($resarr[count($resarr) - 1], '.') + 1) == "yml"){
	    		$this->saveResource("languages/" . $resarr[count($resarr) - 1]);
	    	}
	    }
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
        $this->getCommand("serverauth")->setExecutor(new Commands\Commands($this));
        $this->getCommand("register")->setExecutor(new Commands\Register($this));
        $this->getCommand("login")->setExecutor(new Commands\Login($this));
        $this->getCommand("logout")->setExecutor(new Commands\Logout($this));
        $this->getCommand("changepassword")->setExecutor(new Commands\ChangePassword($this));
        $this->getCommand("unregister")->setExecutor(new Commands\Unregister($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\MessageTask($this), 20);
        $this->task = $this->getServer()->getScheduler()->scheduleRepeatingTask(new Tasks\MySQLTask($this), 20);
        $this->mysql = false;
        $this->chlang = self::getAPI()->getConfigLanguage()->getAll();
        //Check MySQL
        if($this->cfg["use-mysql"] == true){
        	$check = $this->checkDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"]);
        	if($check[0]){
        		$this->initializeDatabase($this->cfg["mysql"]["host"], $this->cfg["mysql"]["port"], $this->cfg["mysql"]["username"], $this->cfg["mysql"]["password"], $this->cfg["mysql"]["database"], $this->cfg["mysql"]["table_prefix"]);
        		$this->getServer()->getLogger()->info($this->translateColors("&", self::PREFIX . $this->chlang["mysql-success"]));
        		$this->mysql = true;
        	}else{
        		$this->getServer()->getLogger()->info($this->translateColors("&", self::PREFIX . self::getAPI()->replaceArrays($this->chlang["mysql-fail"], array("MYSQL_ERROR" => $check[1]))));
        	}
        }
    }
    
    /*** API Functions ***/
    
    /** @var string API_VERSION ServerAuth API version */
    const API_VERSION = "1.1.1";
    
    /**
     * Get ServerAuth version
     * 
     * @return string ServerAuth version
     */
    public function getVersion() : string{
    	return self::VERSION;
    }
    
    /**
     * Get ServerAuth API version
     * 
     * @return string ServerAuth API version
     */
    public function getAPIVersion() : string{
    	return self::API_VERSION;
    }
    
    /**
     * Get the current MySQL database instance
     * 
     * @return mysqli|bool
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
    public function getDatabaseConfig() : array{
    	return $this->getConfig()->getAll()["mysql"];
    }
    
    /**
     * Get ServerAuth data provider
     *
     * @return bool true if ServerAuth is using MySQL, false if ServerAuth is using YAML config
     */
    public function getDataProvider() : bool{
    	return $this->mysql;
    }
    
    /**
     * Check if register messages are enabled
     * 
     * @return bool
     */
    public function areRegisterMessagesEnabled() : bool{
    	return $this->register_message;
    }
    
    /**
     * Enable\Disable register messages
     * 
     * @param bool $bool
     */
    public function enableRegisterMessages(bool $bool = true){
    	$this->register_message = $bool;
    }
    
    /**
     * Check if login messages are enabled
     *
     * @return bool
     */
    public function areLoginMessagesEnabled() : bool{
    	return $this->login_message;
    }
    
    /**
     * Enable\Disable login messages
     *
     * @param bool $bool
     */
    public function enableLoginMessages(bool $bool = true){
    	$this->login_message = $bool;
    }
    
    /**
     * Get cancelled event message
     * 
     * @return string message
     */
    public function getCancelledMessage(){
    	return $this->canc_message;
    }
    
    /**
     * Get player data
     *
     * @param string $player
     *
     * @return array|int the array of player data on SUCCESS, otherwise the current error
     */
    public function getPlayerData(string $player){
    	if($this->isPlayerRegistered($player)){
    		if($this->getDataProvider()){
    			//Check MySQL connection
    			if($this->getDatabase() && $this->getDatabase()->ping()){
    				$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user=?");
    				$stmt_player = strtolower($player);
    				$stmt->bind_param("s", $stmt_player);
    				if($stmt->execute()){
    					$stmt->bind_result($user, $password, $ip, $firstlogin, $lastlogin);
    					$stmt->fetch();
    					$data = array(
    						"password" => $password,
    						"ip" => $ip,
    						"firstlogin" => $firstlogin,
                            "lastlogin" => $lastlogin
    					);
    					$stmt->close();
    					return $data;
    				}else{
    					$stmt->close();
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
    public function getPasswordHash() : string{
    	return $this->getConfig()->getAll()["passwordHash"];
    }
    
    /**
     * Get language data
     * 
     * @param string $language
     * 
     * @return \pocketmine\utils\Config
     */
    public function getLanguage(string $language) : Config{
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
    public function getConfigLanguage() : Config{
    	$cfg = $this->getConfig()->getAll();
    	return $this->getLanguage($cfg["language"]);
    }
    
    /**
     * Check if a player is registered
     * 
     * @param string $player
     * 
     * @return bool|int true or false on SUCCESS, otherwise the current error
     */
    public function isPlayerRegistered(string $player){
    	if($this->getDataProvider()){
    		//Check MySQL connection
    		if($this->getDatabase() && $this->getDatabase()->ping()){
    			$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user=?");
    			$stmt_player = strtolower($player);
    			$stmt->bind_param("s", $stmt_player);
    			$stmt->execute();
    			$stmt->store_result();
    			if($stmt->num_rows == 0){
    				//Unset User in cached array
    				if(isset($this->cached_registered_users[strtolower($player)])){
    					unset($this->cached_registered_users[strtolower($player)]);
    				}
    				$stmt->close();
    				return false;
    			}else{
    				//Set User in cached array
    				if(!isset($this->cached_registered_users[strtolower($player)])){
    					$this->cached_registered_users[strtolower($player)] = "";
    				}
    				$stmt->close();
    				return true;
    			}
    		}else{
    			return ServerAuth::ERR_GENERIC;
    		}
    	}else{
    		$status = file_exists($this->getDataFolder() . "users/" . strtolower($player . ".yml"));
    		if($status){
    			//Set User in cached array
    			if(!isset($this->cached_registered_users[strtolower($player)])){
    				$this->cached_registered_users[strtolower($player)] = "";
    			}
    		}else{
    			//Unset User in cached array
    			if(isset($this->cached_registered_users[strtolower($player)])){
    				unset($this->cached_registered_users[strtolower($player)]);
    			}
    		}
    		return $status;
    	}
    }
    
    /**
     * Check if a player is authenticated
     * 
     * @param Player $player
     * 
     * @return bool
     */
    public function isPlayerAuthenticated(Player $player) : bool{
    	return isset($this->auth_users[strtolower($player->getName())]);
    }
    
    /**
     * Register a player to ServerAuth
     * 
     * @param Player $player
     * @param string $password
     * 
     * @return int|bool true on SUCCESS, otherwise the current error
     */
    public function registerPlayer(Player $player, string $password){
    	$cfg = $this->getConfig()->getAll();
    	if($this->isPlayerRegistered($player->getName())){
    		return ServerAuth::ERR_USER_ALREADY_REGISTERED;
    	}else{
    		if(strlen($password) <= $cfg["minPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_SHORT;
    		}elseif(strlen($password) >= $cfg["maxPasswordLength"]){
    			return ServerAuth::ERR_PASSWORD_TOO_LONG;
    		}else{
    			//Reset cancelled message
    			$this->canc_message = $this->chlang["operation-cancelled"];
    			$this->getServer()->getPluginManager()->callEvent($event = new Events\ServerAuthRegisterEvent($player, $password));
    			if($event->isCancelled()){
    				return ServerAuth::CANCELLED;
    			}
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					if($cfg["register"]["enable-max-ip"]){
    						$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE ip=?");
    						$stmt_address = $player->getAddress();
    						$stmt->bind_param("s", $stmt_address);
    						$stmt->execute();
    						$stmt->store_result();
    						if($stmt->num_rows + 1 <= $cfg["register"]["max-ip"]){
    							$stmt = $this->getDatabase()->prepare("INSERT INTO " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata (user, password, ip, firstlogin, lastlogin) VALUES (?, ?, ?, ?, ?)");
    							$stmt_player = $player->getName();
    							$stmt_password = hash($this->getPasswordHash(), $password);
    							$stmt_address = $player->getAddress();
    							$stmt_firstlogin = $player->getFirstPlayed();
    							$stmt_lastlogin = $player->getLastPlayed();
    							$stmt->bind_param("sssss", $stmt_player, $stmt_password, $stmt_address, $stmt_firstlogin, $stmt_lastlogin);
    							if($stmt->execute()){
    								//Set User in cached array
    								if(!isset($this->cached_registered_users[strtolower($player->getName())])){
    									$this->cached_registered_users[strtolower($player->getName())] = "";
    								}
    								$stmt->close();
    								return ServerAuth::SUCCESS;
    							}else{
    								$stmt->close();
    								return ServerAuth::ERR_GENERIC;
    							}
    						}else{
    							return ServerAuth::ERR_MAX_IP_REACHED;
    						}
    					}else{
    						$stmt = $this->getDatabase()->prepare("INSERT INTO " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata (user, password, ip, firstlogin, lastlogin) VALUES (?, ?, ?, ?, ?)");
    						$stmt_player = $player->getName();
    						$stmt_password = hash($this->getPasswordHash(), $password);
    						$stmt_address = $player->getAddress();
    						$stmt_firstlogin = $player->getFirstPlayed();
    						$stmt_lastlogin = $player->getLastPlayed();
    						$stmt->bind_param("sssss", $stmt_player, $stmt_password, $stmt_address, $stmt_firstlogin, $stmt_lastlogin);
    						if($stmt->execute()){
    							//Set User in cached array
    							if(!isset($this->cached_registered_users[strtolower($player->getName())])){
    								$this->cached_registered_users[strtolower($player->getName())] = "";
    							}
    							$stmt->close();
    							return ServerAuth::SUCCESS;
    						}else{
    							$stmt->close();
    							return ServerAuth::ERR_GENERIC;
    						}
    					}
    				}else{
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				if($cfg["register"]["enable-max-ip"]){
    					if($this->grep($this->getDataFolder() . "users/", $player->getAddress()) + 1 <= $cfg["register"]["max-ip"]){
    						$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    						$data->set("password", hash($this->getPasswordHash(), $password));
    						$data->set("ip", $player->getAddress());
    						$data->set("firstlogin", $player->getFirstPlayed());
    						$data->set("lastlogin", $player->getLastPlayed());
    						$data->save();
    						//Set User in cached array
    						if(!isset($this->cached_registered_users[strtolower($player->getName())])){
    							$this->cached_registered_users[strtolower($player->getName())] = "";
    						}
    						return ServerAuth::SUCCESS;
    					}else{
    						return ServerAuth::ERR_MAX_IP_REACHED;
    					}
    				}else{
    					$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
    					$data->set("password", hash($this->getPasswordHash(), $password));
    					$data->set("ip", $player->getAddress());
    					$data->set("firstlogin", $player->getFirstPlayed());
    					$data->set("lastlogin", $player->getLastPlayed());
    					$data->save();
    					//Set User in cached array
    					if(!isset($this->cached_registered_users[strtolower($player->getName())])){
    						$this->cached_registered_users[strtolower($player->getName())] = "";
    					}
    					return ServerAuth::SUCCESS;
    				}
    			}
    		}
    	}
    }
    
	/**
	 * Unregister a player
	 * 
	 * @param Player|OfflinePlayer $player
	 * 
	 * @return int|bool true on SUCCESS or false if the player is not registered, otherwise the current error
	 */
    public function unregisterPlayer($player){
    	$pname = $player;
    	if($player instanceof Player || $player instanceof OfflinePlayer){
    		$pname = $player->getName();
    	}
    	if($this->isPlayerRegistered($pname)){
    		//Reset cancelled message
    		$this->canc_message = $this->chlang["operation-cancelled"];
    		$this->getServer()->getPluginManager()->callEvent($event = new Events\ServerAuthUnregisterEvent($player));
    		if($event->isCancelled()){
    			return ServerAuth::CANCELLED;
    		}
    		if($this->getDataProvider()){
    			//Check MySQL connection
    			if($this->getDatabase() && $this->getDatabase()->ping()){
    				$stmt = $this->getDatabase()->prepare("DELETE FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user=?");
    				$stmt_player = strtolower($pname);
    				$stmt->bind_param("s", $stmt_player);
    				if($stmt->execute()){
    					$stmt->close();
    					//Unset User from cache
    					if(isset($this->cached_registered_users[strtolower($pname)])){
    						unset($this->cached_registered_users[strtolower($pname)]);
    					}
    					//Deauthenticate player
    				    if($player instanceof Player){
    						ServerAuth::getAPI()->deauthenticatePlayer($player);
    					}
    					//Restore default messages
    					ServerAuth::getAPI()->enableLoginMessages(true);
    					ServerAuth::getAPI()->enableRegisterMessages(true);
    					return ServerAuth::SUCCESS;
    				}else{
    					$stmt->close();
    					return ServerAuth::ERR_GENERIC;
    				}
    			}else{
    				return ServerAuth::ERR_GENERIC;
    			}
    		}else{
    			@unlink($this->getDataFolder() . "users/" . strtolower($pname . ".yml"));
    			//Unset User from cache
    			if(isset($this->cached_registered_users[strtolower($pname)])){
    				unset($this->cached_registered_users[strtolower($pname)]);
    			}
    			//Deauthenticate player
    			if($player instanceof Player){
    				ServerAuth::getAPI()->deauthenticatePlayer($player);
    			}
    			//Restore default messages
    			ServerAuth::getAPI()->enableLoginMessages(true);
    			ServerAuth::getAPI()->enableRegisterMessages(true);
    			return ServerAuth::SUCCESS;
    		}
    	}else{
    		return ServerAuth::ERR_USER_NOT_REGISTERED;
    	}
    }
    
    /**
     * Authenticate a Player
     * 
     * @param Player $player
     * @param string $password
     * @param bool $hash
     * 
     * @return int|bool true on SUCCESS, otherwise the current error
     */
    public function authenticatePlayer(Player $player, string $password, bool $hash = true){
    	if($hash){
    		$password = hash($this->getPasswordHash(), $password);
    	}
    	if($this->isPlayerRegistered($player->getName())){
    		if(!$this->isPlayerAuthenticated($player)){
    			//Reset cancelled message
    			$this->canc_message = $this->chlang["operation-cancelled"];
    			$this->getServer()->getPluginManager()->callEvent($event = new Events\ServerAuthAuthenticateEvent($player));
    			if($event->isCancelled()){
    				return ServerAuth::CANCELLED;
    			}
    			$cfg = $this->getConfig()->getAll();
    			if($this->getDataProvider()){
    				//Check MySQL connection
    				if($this->getDatabase() && $this->getDatabase()->ping()){
    					$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata WHERE user=?");
    					$stmt_player = strtolower($player->getName());
    					$stmt->bind_param("s", $stmt_player);
    					$stmt->execute();
    					$stmt->bind_result($user, $db_password, $ip, $firstlogin, $lastlogin);
    					$stmt->fetch();
    					$stmt->close();
    					if($db_password){
    						if($password == $db_password){
    							$stmt = $this->getDatabase()->prepare("UPDATE " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata SET ip=?, lastlogin=? WHERE user=?");
    							$stmt_ip = $player->getAddress();
    							$stmt_lastplayed = $player->getLastPlayed();
    							$stmt_player = strtolower($player->getName());
    							$stmt->bind_param("sss", $stmt_ip, $stmt_lastplayed, $stmt_player);
    							if($stmt->execute()){
    								$this->auth_users[strtolower($player->getName())] = "";
    								if($cfg['login']['enable-failed-logins-kick'] && isset($this->auth_attempts[strtolower($player->getName())])){
    									unset($this->auth_attempts[strtolower($player->getName())]);
    								}
    								$stmt->close();
    								return ServerAuth::SUCCESS;
    							}else{
    								$stmt->close();
    								return ServerAuth::ERR_GENERIC;
    							}
    						}else{
    							if($cfg['login']['enable-failed-logins-kick']){
    								if(isset($this->auth_attempts[strtolower($player->getName())])){
    									$this->auth_attempts[strtolower($player->getName())]++;
    								}else{
    									$this->auth_attempts[strtolower($player->getName())] = 1;
    								}
    								if($this->auth_attempts[strtolower($player->getName())] >= $cfg['login']['max-login-attempts']){
    									$player->close("", $this->translateColors("&", $this->chlang["login"]["too-many-attempts"]));
    									unset($this->auth_attempts[strtolower($player->getName())]);
    									return ServerAuth::TOO_MANY_ATTEMPTS;
    								}
    							}
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
    					$this->auth_users[strtolower($player->getName())] = "";
    					if($cfg['login']['enable-failed-logins-kick'] && isset($this->auth_attempts[strtolower($player->getName())])){
    						unset($this->auth_attempts[strtolower($player->getName())]);
    					}
    					return ServerAuth::SUCCESS;
    				}else{
    					if($cfg['login']['enable-failed-logins-kick']){
    						if(isset($this->auth_attempts[strtolower($player->getName())])){
    							$this->auth_attempts[strtolower($player->getName())]++;
    						}else{
    							$this->auth_attempts[strtolower($player->getName())] = 1;
    						}
    						if($this->auth_attempts[strtolower($player->getName())] >= $cfg['login']['max-login-attempts']){
    							$player->close("", $this->translateColors("&", $this->chlang["login"]["too-many-attempts"]));
    							unset($this->auth_attempts[strtolower($player->getName())]);
    							return ServerAuth::TOO_MANY_ATTEMPTS;
    						}
    					}    					
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
    
    /**
     * Deauthenticate a player
     * 
     * @param Player $player
     * 
     * @return int|bool true on SUCCESS, otherwise the current error
     */
    public function deauthenticatePlayer(Player $player){
    	if($this->isPlayerAuthenticated($player)){
    		//Reset cancelled message
    		$this->canc_message = $this->chlang["operation-cancelled"];
    		$this->getServer()->getPluginManager()->callEvent($event = new Events\ServerAuthDeauthenticateEvent($player));
    		if($event->isCancelled()){
    			return ServerAuth::CANCELLED;
    		}
    		//Restore default messages
    		ServerAuth::getAPI()->enableLoginMessages(true);
    		ServerAuth::getAPI()->enableRegisterMessages(true);
    		unset($this->auth_users[strtolower($player->getName())]);
    		return ServerAuth::SUCCESS;
    	}else{
    		return ServerAuth::ERR_USER_NOT_AUTHENTICATED;
    	}
    }
    
	/**
	 * Change player password
	 * 
	 * @param Player|OfflinePlayer $player
	 * @param string $new_password
	 * 
	 * @return int|bool true on SUCCESS or false if the player is not registered, otherwise the current error
	 */
    public function changePlayerPassword($player, string $new_password){
    	if($player instanceof Player || $player instanceof OfflinePlayer){
	    	$cfg = $this->getConfig()->getAll();
	    	if($this->isPlayerRegistered($player->getName())){
	    		if(strlen($new_password) < $cfg["minPasswordLength"]){
	    			return ServerAuth::ERR_PASSWORD_TOO_SHORT;
	    		}elseif(strlen($new_password) > $cfg["maxPasswordLength"]){
	    			return ServerAuth::ERR_PASSWORD_TOO_LONG;
	    		}else{
	    			//Reset cancelled message
	    			$this->canc_message = $this->chlang["operation-cancelled"];
	    			$this->getServer()->getPluginManager()->callEvent($event = new Events\ServerAuthPasswordChangeEvent($player, $new_password));
	    			if($event->isCancelled()){
	    				return ServerAuth::CANCELLED;
	    			}
	    			if($this->getDataProvider()){
	    				//Check MySQL connection
	    				if($this->getDatabase() && $this->getDatabase()->ping()){
	    					$stmt = $this->getDatabase()->prepare("UPDATE " . $this->getDatabaseConfig()["table_prefix"] . "serverauthdata SET password=? WHERE user=?");
	    					$stmt_password = hash($this->getPasswordHash(), $new_password);
	    					$stmt_player = strtolower($player->getName());
	    					$stmt->bind_param("ss", $stmt_password, $stmt_player);
	    					if($stmt->execute()){
	    						$stmt->close();
	    						return ServerAuth::SUCCESS;
	    					}else{
	    						$stmt->close();
	    						return ServerAuth::ERR_GENERIC;
	    					}
	    				}else{
	    					return ServerAuth::ERR_GENERIC;
	    				}
	    			}else{
	    				$data = new Config($this->getDataFolder() . "users/" . strtolower($player->getName() . ".yml"), Config::YAML);
	    				$data->set("password", hash($this->getPasswordHash(), $new_password));
	    				$data->save();
	    				return ServerAuth::SUCCESS;
	    			}	
	    		}
	    	}else{
	    		return $this->isPlayerRegistered($player->getName());
	    	}
    	}else{
    		return ServerAuth::ERR_USER_NOT_REGISTERED;
    	}
    }
    
}
?>
