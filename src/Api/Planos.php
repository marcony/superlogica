<?php

namespace Superlogica\Api;

class Planos extends AbstractApi
{

    /**
     * Utilizado para alterar dados da contrata��o
     * 
     * @param string $identificadorContrato Identificador do contrato
     * @param array $dados Dados a serem alterados
     * @return boolean
     * @throws  Exception
     */
    public function alterar( $identificadorContrato, $dados ){
        
        $dados['identificadorContrato'] = $identificadorContrato;
        $retorno = $this->_api->action("planosclientes/post", $dados );
        if ( $retorno['status'] == 200 )
            return true;
        
        $this->_api->throwException($retorno);
        
    }
    
}