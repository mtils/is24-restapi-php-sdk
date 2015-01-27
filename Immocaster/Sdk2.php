<?php namespace Immocaster;

use Immocaster_Sdk;
use Immocaster\Data\TokenRepositoryInterface;
use Immocaster\Data\MysqlTokenRepository;
use ReflectionClass;
use ReflectionMethod;

require_once(__DIR__.'/Sdk.php');

class Sdk2 extends Immocaster_Sdk{

    /**
     * Singleton instances. (Overwritten because private)
     *
     * @var array
     */
    static protected $instances = array();

    /**
     * Protected access to the rest service. There is no need for private
     * 
     * @var \Immocaster_Immobilienscout_Rest
     **/
    protected $serviceDriver;

    /**
     * Singleton Pattern für die Erstellung
     * der Instanzen von Immocaster_Sdk.
     *
     * @param string Name der Instanz
     * @param string Key des Konsumenten
     * @param string Secret des Konsumenten
     * @param string Name des Service
     * @param string Typ der Authentifizierung
     * @param string Typ des Protokolls
     * @return static
     */
    static public function getInstance($sName,$sKey='',$sSecret='',$sService='immobilienscout',$sAuth='oauth',$sProtocol='rest')
    {
        if(!isset(self::$instances[$sName]))
        {
            self::$instances[$sName] = new static($sKey,$sSecret,$sService,$sAuth,$sProtocol);
        }
        return self::$instances[$sName];
    }

    /**
     * Return the token repository
     *
     * @return \Immocaster\Data\TokenRepositoryInterface
     **/
    public function getTokenRepository(){
        return $this->getServiceDriver()->getTokenRepository();
    }

    /**
     * Set the token repository
     *
     * @param \Immocaster\Data\TokenRepositoryInterface $tokenRepository
     * @return static
     **/
    public function setTokenRepository(TokenRepositoryInterface $tokenRepository){
        $this->getServiceDriver()->setTokenRepository($tokenRepository);
        return $this;
    }

    /**
     * Initialise Token Repository (for 3-legged-oauth).
     * Implemented for backward compatibility
     *
     * @param array $aConnection db credentials (type,host,user,password,database)
     * @param string $sSessionNamespace session variable namespace
     * @param string $sTableName alternative name of db table
     * @param boolean $bSession Für die Zertifizierung wird eine Session benötigt, das automatische laden der Session kann aber per false deaktiviert werden.
     * @return \Immocaster_Data_Mysql
     */
    public function setDataStorage($aConnection,$sSessionNamespace=null,$sTableName=null,$bSession=true)
    {

        $mysql = parent::setDataStorage($aConnection, $sSessionNamespace, $sTableName, $bSession);

        $this->setTokenRepository($mysql);

        return $mysql;

    }

    /**
     * Carefully access the (originally private) service
     *
     * @return \Immocaster_Immobilienscout_Rest
     **/
    protected function getServiceDriver(){

        if(!$this->serviceDriver){

            $refl = new ReflectionClass(get_parent_class());

            // Access private properties harhar
            $oServiceProperty = $refl->getProperty('_oService');
            $oServiceProperty->setAccessible(true);
            $this->serviceDriver = $oServiceProperty->getValue($this);
            $oServiceProperty->setAccessible(false);

        }

        return $this->serviceDriver;

    }

}