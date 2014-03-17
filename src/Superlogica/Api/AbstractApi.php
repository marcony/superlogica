<?php

namespace Superlogica\Api;
use Superlogica\Api;

abstract class AbstractApi
{
    
    /**
     * Instancia da api utilizada para realizar as requisi��es
     * @var Superlogica_Api
     */
    protected $_api = null;
    
    /**
     * Armazena o flag que informa se ser� utilizado identificador ou n�o
     * @var boolean
     */
    protected static $_utilizarIdentificadores = true;
    
    /**
     * Construtor
     * @param Superlogica_Api $api
     */
    public function __construct( Api $api ) {
        $this->_api = $api;
    }
    
    /**
     * Seta o flag para utiliza��o de identificador ou id do recurso
     * @param boolean $flag
     */
    public static function setUtilizarIdentificador( $flag ){
        self::$_utilizarIdentificadores = $flag; 
    }
    
    /**
     * Retorna true caso tenha que ser utilizado identificador ou o id do recurso requererido
     * @return boolean
     */
    public static function getUtilizarIdentificador(){
        return self::$_utilizarIdentificadores;
    }
}