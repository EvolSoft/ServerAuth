<?php

/*
 * ServerAuthWebAPI (v2.1) by EvolSoft 
 * Developer: EvolSoft (Flavius12) 
 * Website: http://www.evolsoft.tk 
 * Date: 16/01/2016 04:52 PM (UTC) 
 * Copyright & License: (C) 2015-2017 EvolSoft 
 * Licensed under MIT (https://github.com/EvolSoft/ServerAuth/blob/master/LICENSE)
 */

class ServerAuthWebAPI {
	
	/** @var string WEBAPI_VERSION Current ServerAuthWebAPI version */
	const WEBAPI_VERSION = "2.1";
	
	/** @var string CURRENT_API Current ServerAuth plugin API version */
	const CURRENT_API = "1.1.1";
	
	/** @var int SUCCESS Success */
	const SUCCESS = 1;
	
	/** @var int ERR_OUTDATED_WEBAPI Outdated ServerAuthWebAPI error */
	const ERR_OUTDATED_WEBAPI = 2;
	
	/** @var int ERR_OUTDATED_PLUGIN Outdated ServerAuth plugin error */
	const ERR_OUTDATED_PLUGIN = 3;
	
	/** @var int ERR_MYSQL MySQL error */
	const ERR_MYSQL = 4;
	
	/** @var int $status */
	private $status;
	
	/** @var int $api_version */
	private $api_version;
	
	/** @var int $version */
	private $version;
	
	/** @var string $password_hash */
	private $password_hash;
	
	/** @var string $host */
	private $host;
	
	/** @var int $port */
	private $port;
	
	/** @var string $username */
	private $username;
	
	/** @var string $password */
	private $password;
	
	/** @var string $dbname */
	private $dbname;
	
	/** @var string $table_prefix */
	private $table_prefix;
	
	/**
	 * Initialize a new ServerAuthWebAPI instance
	 * 
	 * @param string $host MySQL host
	 * @param int $port MySQL port
	 * @param string $username MySQL username
	 * @param string $password MySQL password
	 * @param string $database MySQL ServerAuth database
	 * @param string $table_prefix ServerAuth MySQL table prefix
	 */
	public function __construct($host, $port, $username, $password, $database, $table_prefix){
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->dbname = $database;
		$this->table_prefix = $table_prefix;
		$this->database = @new \mysqli($host, $username, $password, $database, $port);
		if($this->database->connect_error){
			$this->status = ServerAuthWebAPI::ERR_MYSQL;
		}else{
			$query = "SELECT api_version, version, password_hash FROM " . $table_prefix . "serverauth LIMIT 1";
			if($this->database->query($query)){
				$this->api_version = $this->getDatabase()->query($query)->fetch_assoc()["api_version"];
				$this->version = $this->getDatabase()->query($query)->fetch_assoc()["version"];
				$this->password_hash = $this->getDatabase()->query($query)->fetch_assoc()["password_hash"];
			    if($this->api_version > ServerAuthWebAPI::CURRENT_API){
					$this->status = ServerAuthWebAPI::ERR_OUTDATED_WEBAPI;
				}elseif($this->api_version < ServerAuthWebAPI::CURRENT_API){
					$this->status = ServerAuthWebAPI::ERR_OUTDATED_PLUGIN;
				}else{
					$this->status = ServerAuthWebAPI::SUCCESS;
				}
			}else{
				$this->status = ServerAuthWebAPI::ERR_MYSQL;
			}
		}
	}

	/**
	 * Get the current ServerAuthWebAPI instance status
	 * 
	 * @return int (SUCCESS|ERR_OUTDATED_WEBAPI|ERR_OUTDATED_PLUGIN|ERR_MYSQL)
	 */
	public function getStatus(){
		return $this->status;
	}
	
	/**
	 * Get ServerAuth plugin version
	 * 
	 * @return string|int the current version string on SUCCESS, otherwise the current status
	 */
	public function getVersion(){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			return $this->version;
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Get ServerAuth plugin API version
	 * 
	 * @return string|int the current API version string on SUCCESS, otherwise the current status
	 */
	public function getAPIVersion(){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			return $this->api_version;
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Get ServerAuthWebAPI version
	 * 
	 * @return string
	 */
	public static function getWebAPIVersion(){
		return ServerAuthWebAPI::WEBAPI_VERSION;
	}
	
	/**
	 * Get ServerAuth password hash
	 * 
	 * @return string
	 */
	public function getPasswordHash(){
		return $this->password_hash;
	}
	
	/**
	 * Get MySQL host
	 * 
	 * @return string
	 */
	public function getHost(){
		return $this->host;
	}
	
	/**
	 * Get MySQL port
	 * 
	 * @return int
	 */
	public function getPort(){
		return $this->port;
	}
	
	/**
	 * Get MySQL username
	 * 
	 * @return string
	 */
	public function getUsername(){
		return $this->username;
	}
	
	/**
	 * Get MySQL password
	 * 
	 * @return string
	 */
	public function getPassword(){
		return $this->password;
	}
	
	/**
	 * Get MySQL ServerAuth database name
	 * 
	 * @return string
	 */
	public function getDatabaseName(){
		return $this->dbname;
	}
	
	/**
	 * Get ServerAuth MySQL table prefix
	 * 
	 * @return string
	 */
	public function getTablePrefix(){
		return $this->table_prefix;
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
	 * Check if a player is registered to ServerAuth
	 * 
	 * @param string $player
	 * 
	 * @return boolean|int true or false on SUCCESS, otherwise the current status
	 */
	public function isPlayerRegistered($player){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			//Check MySQL connection
			if($this->getDatabase() && $this->getDatabase()->ping()){
				$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getTablePrefix() . "serverauthdata WHERE user=?");
				$stmt_player = strtolower($player);
				$stmt->bind_param("s", $stmt_player);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->num_rows == 0){
					$stmt->close();
					return false;
				}else{
					$stmt->close();
					return true;
				}
			}else{
				return ServerAuthWebAPI::ERR_MYSQL;
			}
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Get player data
	 * 
	 * @param string $player
	 * 
	 * @return array|int the array of player data on SUCCESS, otherwise the current status
	 */
	public function getPlayerData($player){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			if($this->isPlayerRegistered($player)){
				//Check MySQL connection
				if($this->getDatabase() && $this->getDatabase()->ping()){
					$stmt = $this->getDatabase()->prepare("SELECT user, password, ip, firstlogin, lastlogin FROM " . $this->getTablePrefix() . "serverauthdata WHERE user=?");
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
						return ServerAuthWebAPI::ERR_GENERIC;
					}
				}else{
					return ServerAuthWebAPI::ERR_GENERIC;
				}
			}else{
				return $this->isPlayerRegistered($player);
			}
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Register a player to ServerAuth
	 * 
	 * @param string $player
	 * @param string $password
	 * @param string $ip
	 * @param int|double $firstlogin (UNIX timestamp)
	 * @param int|double $lastlogin (UNIX timestamp)
	 * 
	 * @return int|boolean true on SUCCESS or false if the player is already registered, otherwise the current status
	 */
	public function registerPlayer($player, $password, $ip, $firstlogin, $lastlogin){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			if(!$this->isPlayerRegistered($player)){
				//Check MySQL connection
				if($this->getDatabase() && $this->getDatabase()->ping()){
					$stmt = $this->getDatabase()->prepare("INSERT INTO " . $this->getTablePrefix() . "serverauthdata (user, password, ip, firstlogin, lastlogin) VALUES (?, ?, ?, ?, ?)");
    				$password = hash($this->getPasswordHash(), $password);
    				$stmt->bind_param("sssss", $player, $password, $ip, $firstlogin, $lastlogin);
					if($stmt->execute()){
						$stmt->close();
						return ServerAuthWebAPI::SUCCESS;
					}else{
						$stmt->close();
						return ServerAuthWebAPI::ERR_MYSQL;
					}
				}else{
					return ServerAuthWebAPI::ERR_MYSQL;
				}
			}else{
				return $this->isPlayerRegistered($player);
			}
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Unregister a player
	 * 
	 * @param string $player
	 * 
	 * @return int|boolean true on SUCCESS or false if the player is not registered, otherwise the current status
	 */
	public function unregisterPlayer($player){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			if($this->isPlayerRegistered($player)){
				//Check MySQL connection
				if($this->getDatabase() && $this->getDatabase()->ping()){
					$stmt = $this->getDatabase()->prepare("DELETE FROM " . $this->getTablePrefix() . "serverauthdata WHERE user=?");
					$stmt_player = strtolower($player);
					$stmt->bind_param("s", $stmt_player);
					if($stmt->execute()){
						$stmt->close();
						return ServerAuthWebAPI::SUCCESS;
					}else{
						$stmt->close();
						return ServerAuthWebAPI::ERR_MYSQL;
					}
				}else{
					return ServerAuthWebAPI::ERR_MYSQL;
				}
			}else{
				return $this->isPlayerRegistered($player);
			}
		}else{
			return $this->getStatus();
		}
	}
	
	/**
	 * Change player password
	 * 
	 * @param string $player
	 * @param string $new_password
	 * 
	 * @return int|boolean true on SUCCESS or false if the player is not registered, otherwise the current status
	 */
	public function changePlayerPassword($player, $new_password){
		if($this->getStatus() == ServerAuthWebAPI::SUCCESS){
			if($this->isPlayerRegistered($player)){
				//Check MySQL connection
				if($this->getDatabase() && $this->getDatabase()->ping()){
					$stmt = $this->getDatabase()->prepare("UPDATE " . $this->getTablePrefix() . "serverauthdata SET password=? WHERE user=?");
					$stmt_password = hash($this->getPasswordHash(), $new_password);
					$stmt_player = strtolower($player);
					$stmt->bind_param("ss", $stmt_password, $stmt_player);
					if($stmt->execute()){
						$stmt->close();
						return ServerAuthWebAPI::SUCCESS;
					}else{
						$stmt->close();
						return ServerAuthWebAPI::ERR_MYSQL;
					}
				}
			}else{
				return $this->isPlayerRegistered($player);
			}
		}else{
			return $this->getStatus();
		}
	}
}
