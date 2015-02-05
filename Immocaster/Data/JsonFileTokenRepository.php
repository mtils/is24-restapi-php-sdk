<?php namespace Immocaster\Data;

use stdClass;
use DateTime;
use DateInterval;

class JsonFileTokenRepository implements TokenRepositoryInterface{

    const REQUEST = 'REQUEST';

    const APPLICATION = 'APPLICATION';

    const KEY_DELIMITER = '|-|';

    protected $filePath;

    protected $fileData;

    /**
     * Allows manual adding of application tokens without saving them
     *
     * @var array
     **/
    protected $manualApplicationTokens = array();

    /**
     * Time after request token will expire in minutes
     *
     * @var int
     **/
    protected $defaultTokenExpiration = 60;

    public function __construct($filePath=NULL){

        $this->setFilePath($filePath !== NULL ? $filePath : sys_get_temp_dir() . '/is24_api_tokens.json');

    }

    public function getFilePath(){
        return $this->filePath;
    }

    public function setFilePath($path){
        $this->filePath = $path;
        return $this;
    }

    /**
     * Load and return the application token
     *
     * @param string $username
     * @return object
     **/
    public function getApplicationToken($username){

        if(isset($this->manualApplicationTokens[$username])){
            return $this->manualApplicationTokens[$username];
        }

        $arrayKey = $this->getApplicationArrayKey($userName);
        $data = $this->getFileData();

        return isset($data->$arrayKey) ? $data->$arrayKey : false;

    }

    /**
     * Persists the application token
     *
     * @param string $token
     * @param string $secret
     * @param string $user
     * @return bool
     **/
    public function saveApplicationToken($token, $secret, $user){

        $arrayKey = $this->getApplicationArrayKey($user);

        // Trigger loading
        $data = $this->getFileData();

        $tokenData = new stdClass();

        $tokenData->ic_desc = self::APPLICATION;
        $tokenData->ic_key = $token;
        $tokenData->ic_secret = $secret;
        $tokenData->ic_expire = '0000-00-00 00:00:00.000000';
        $tokenData->ic_username = $user;

        $this->fileData->$arrayKey = $tokenData;

        $this->saveFile();

    }

    /**
     * Allow manual setting of application token. Does not get saved
     * This is for people (like me) who gets an certified application and never
     * more needs request keys.
     * 
     * @param string $token
     * @param string $secret
     * @param string $user
     * @return self
     **/
    public function setApplicationToken($token, $secret, $user){

        $tokenData = new stdClass;
        $tokenData->ic_desc = self::APPLICATION;
        $tokenData->ic_key = $token;
        $tokenData->ic_secret = $secret;
        $tokenData->ic_expire = '0000-00-00 00:00:00.000000';
        $tokenData->ic_username = $user;

        $this->manualApplicationTokens[$user] = $tokenData;

        return $this;
    }

    /**
     * Load and return a requesttoken without a session
     *
     * @return string
     **/
    public function getRequestTokenWithoutSession(){

        $data = $this->getFileData();

        $requestKeys = array();

        foreach($data as $arrayKey=>$value){
            if($this->isRequestArrayKey($arrayKey)){
                $requestKeys[] = $arrayKey;
            }
        }

        if(!count($requestKeys)){
            return new stdClass;
        }

        sort($requestKeys);

        return $data->$requestKeys[0];

    }

    /**
     * Load and return request token
     *
     * @param string $token (optional)
     * @return object
     **/
    public function getRequestToken($token=null){

        $data = $this->getFileData();
        $arrayKey = $this->getRequestArrayKey($token);

        if(isset($data[$arrayKey])){
            return $data[$arrayKey];
        }

        return new stdClass;

    }

    /**
     * Save the request token
     *
     * @param string $token The OAuth token
     * @param string $secret The matching secret
     * @return bool
     **/
    public function saveRequestToken($token, $secret){

        // Trigger loading
        $data = $this->getFileData();

        $expire = new DateTime;
        $expire->add(new DateInterval("PT{$this->defaultTokenExpiration}M"));

        $tokenData = new stdClass;

        $tokenData->ic_desc = self::REQUEST;
        $tokenData->ic_key = $token;
        $tokenData->ic_secret = $secret;
        $tokenData->ic_expire = $expire->format('Y-m-d H:i:s');

        $arrayKey = $this->getRequestArrayKey($token);

        $this->fileData[$arrayKey] = $tokenData;

        return $this->saveFile();
    }

    /**
     * Return all access tokens
     *
     * @return array
     **/
    public function getAllApplicationUsers(){

        $data = $this->getFileData();
        $users = array();

        foreach($data as $arrayKey=>$value){
            if($this->isApplicationArrayKey($arrayKey)){
                $users[] = $value->ic_username;
            }
        }

        return $users;

    }

    protected function getFileData(){

        if(!$this->fileData){
            if(!file_exists($this->getFilePath())){
                file_put_contents($this->getFilePath(),'');
            }
            $json = file_get_contents($this->getFilePath());
            $this->fileData = json_decode($json);
        }

        return $this->fileData;

    }

    protected function saveFile(){
        return (bool)file_put_contents($this->getFilePath(),json_encode($this->getFileData()));
    }

    protected function getRequestArrayKey($token){
        return self::REQUEST . '|-|' . (string)$token;
    }

    protected function getApplicationArrayKey($username){
        return self::APPLICATION . '|-|' . (string)$username;
    }

    protected function isApplicationArrayKey($key){
        return (strpos($arrayKey, self::APPLICATION.self::KEY_DELIMITER) === 0);
    }

    protected function isRequestArrayKey($key){
        return (strpos($arrayKey, self::REQUEST.self::KEY_DELIMITER) === 0);
    }
}
