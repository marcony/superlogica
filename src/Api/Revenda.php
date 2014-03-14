<?php

namespace Superlogica\Api;

class Revenda
{
    
    /**
     * URL para contrata��o do plano
     * @var type 
     */
    protected $_urlAreadocliente = 'https://superlogica.superlogica.net/areadocliente/atual';
        
    /**
     * Url do site da superl�gica
     * @var string
     */
    protected $_siteSuperlogica = 'http://superlogica.com/';
    
    /**
     * Classe respons�vel por tramitar informa��es para API
     * @var Superlogica_Api
     */
    protected $_apiAreadocliente = null;
    
    /**
     * Respons�vel por conectar na conta recem criada
     * @var Superlogica_Api
     */
    protected $_api=null;
    
    /**
     * E-mail para login
     * @var string
     */
    protected $_credencialEmail = null;
    
    /**
     * Senha para login
     * @var string
     */
    protected $_credencialSenha = null;
    
    /**
     * URL do login do parceiro
     * @var string
     */
    protected $_urlLoginRevenda = null;
    
    /**
     * Construtor
     * @param string $urlRevenda
     */
    public function __construct( $urlLoginRevenda = null ){
        $this->_urlLoginRevenda = $urlLoginRevenda;
    }
        
    /**
     * Seta as credencias para contrata��o
     * @param string $email
     * @param string $senha
     */
    public function setCredencial($email,$senha){
        $this->_credencialEmail = $email;
        $this->_credencialSenha = $senha;
    }
    
    /**
     * Contrata o plano para as credenciais informadas
     * 
     * @param string $identificador Identificador do contrato
     * @param int $trial Indica se � ou n�o TRIAL. 1 ou 0
     * @return array
     * @throws Exception
     */
    public function contratar( $idPlano, $identificador, $emailDoCliente, $trial = 1 ){        
        $api = $this->_getApiAreadocliente();
        $retorno = $api->action('planos/put?confirmado=1',array(
            'ST_IDENTIFICADOR_PLC' => $identificador,
            'idplano' => $idPlano,
            'trial' => $trial
        ));
        if ( $retorno['status'] != 200 ){
            $api->throwException();
        }
        return $this->_iniciarAmbiente( $api->getData(), $emailDoCliente ); 
    }
    
    /**
     * Executa procedimentos para iniciar o ambiente
     * @param array $dadosContrato
     * @param string $emailDoCliente
     * @return string
     * @throws Exception
     */
    protected function _iniciarAmbiente( $dadosContrato, $emailDoCliente ){
        if ( !$dadosContrato['urlcallback'] ){
            throw new Exception("Erro ao contratar trial. " . $dadosContrato['msg'] );
        }        
        $dadosTrial = $this->_criarAmbiente( $dadosContrato['urlcallback'] );        
        $idUsuario = $dadosTrial['data']['idUsuario'];
        $this->_verificarAmbiente( $idUsuario );
        $identificador = $dadosTrial['data']['identificador'];
        $this->_configurarUrlLogin( $identificador );
        $this->_criarUsuario($identificador, $emailDoCliente);        
        return $identificador;
    }
    
    /**
     * Cria o trial no site da superl�gica
     * @param string $url
     * @return array
     * @throws Exception
     */
    protected function _criarAmbiente( $url ){
        $dados = $this->_requestJson($url.'&autoSubmitIdentificador=1&desativarEmailBoasVindas=1');        
        if ( $dados['data']['idUsuario'] <= 0 )
            throw new Exception($dados['msg']);
        return $dados;
    }
    
    /**
     * Verifica se base j� est� dipon�svel para utiliza��o
     * @param int $idUsuario
     * @throws Exception
     */
    protected function _verificarAmbiente( $idUsuario ){
        $urlBaseDisponivel = $this->_getUrlSiteSuperlogica() . 'experimente-gratis/baseDisponivel.php';
        $dadosBaseDisponivel = $this->_requestJson( $urlBaseDisponivel.'?idUsuario='.$idUsuario );
        if ( $dadosBaseDisponivel['sucesso'] <= 0 )
            throw new Exception ( "Erro ao criar base de dados. " . $dadosBaseDisponivel['msg'] );
    }
    
    /**
     * Configurar Url do login da revenda
     * @param string $conta
     * @param string $url
     * @return boolean
     */
    protected function _configurarUrlLogin( $conta ){
        if (!$this->_urlLoginRevenda) {
            return true;
        }
        
        $config = new Configuracoes( $this->_getApi( $conta ) );
        $config->URL_AUTENTICACAO_EXTERNA = $this->_urlLoginRevenda;
        
    }
    
    /**
     * Cria o usu�rio do cliente na base informada
     * 
     * @param string $identificador
     * @param string $emailCliente
     * @return boolean
     */
    protected function _criarUsuario( $conta, $email ){
        $api = $this->_getApi( $conta );
        $retorno = $api->action('usuario/put',array(
            'ST_NOME_USU' => $email,
            'ST_APELIDO_USU' => $email,
            'ACESSO' => array(
                '1000' => '1'
            )            
        ));
        
        if( $retorno['status'] != 200 ){
            $api->throwException();
        }
        
        return true;
        
    }
        
    /**
     * Retorna a url do site da superl�gica;
     * @return string
     */
    protected function _getUrlSiteSuperlogica(){
        return $this->_siteSuperlogica;
    }
    
    /**
     * Fun��o auxiliar para fazer requisi��o json e retornar
     * @param string $url
     * @return array
     */
    protected function _requestJson( $url ){
        $str = stream_context_create(array(
            'http' => array(
                'timeout' => 70
            )
        ));
        $requisicao = file_get_contents( $url, false, $str );        
        return json_decode( $requisicao ,true);
    }
    
    /**
     * Retorna a instancia da API
     * @return Superlogica_Api
     * @throws Exception
     */
    protected function _getApiAreadocliente(){
        
        if ( $this->_apiAreadocliente ) 
            return $this->_apiAreadocliente;
        
        $this->_apiAreadocliente = new Api( $this->_urlAreadocliente );
        
        if( $this->_credencialEmail === null || $this->_credencialSenha === null )
            throw new Exception("Necess�rio informar credenciais.");
            
        $retorno = $this->_apiAreadocliente->action('publico/auth',array(
            'email' => $this->_credencialEmail,
            'senha' => $this->_credencialSenha
        ));
        
        if ( $retorno['status'] != 202 ){
            $this->_apiAreadocliente->throwException();
        }
        
        $this->_apiAreadocliente->setSessionId( $retorno['session'] );
        
        return $this->_apiAreadocliente;
    }
    
    /**
     * Retorna a instancia da API
     * @return Superlogica_Api
     * @throws Exception
     */
    protected function _getApi( $conta = null ){        
        
        if ( $this->_api ) 
            return $this->_api;        
        
        if ( !$conta )
            throw new Exception("Nome da conta necess�rio.");        
        
        if( $this->_credencialEmail === null || $this->_credencialSenha === null )
            throw new Exception("Necess�rio informar credenciais.");        
        
        $this->_api = new Api( 'https://' . $conta.'.superlogica.net/financeiro/atual' );
        $retorno = $this->_api->login( $this->_credencialEmail, $this->_credencialSenha, $conta );
        
        if ( $retorno['status'] != 202 )
            $this->_api->throwException();
        
        return $this->_api;
    }
    
}