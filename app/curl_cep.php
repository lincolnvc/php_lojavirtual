<?php

/*
 * Consulta WS base de cep utilizando Curl
 * Author: Rafael Clares <rafael@clares.com.br>
 * 15/01/2014
 */

class curl_cep
{
    public $result = array( );
    public $key;
    public $data;
    //base local de cep caso possua (está disponível para venda no phpstaff.com.br)
    #public $url = "http://seusite.com.br/cep/ws/serverCurl.php"; 
    //base remota 
    public $url = "http://clareslab.com.br/cep2013/serverCurl.php";

    public function __construct()
    {
        if ( !function_exists( 'curl_init' ) )
        {
            die( 'A função Curl não está disponível em seu PHP.' );
        }
        $this->key = base64_encode( md5( $_SERVER['SERVER_NAME'] ) );
    }

    public function teste()
    {
        echo $this->_getcep( array( "cep" => "11701090" ) );
        echo '<br>';
        echo $this->_getEndereco( array( "endereco" => "Av Brasil,Boqueirão,Praia Grande,SP" ) );
    }

    public function _getcep( $data )
    {
        $this->data = $data;
        $this->data['chave'] = $this->key;
        $this->data['metodo'] = 'getCep';
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $this->url );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));//send multi array
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $this->data );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1" );
        $this->result = curl_exec( $curl );
        curl_close( $curl );
        return $this->result;
    }

    public function _getEndereco( $data )
    {
        $this->data = $data;
        $this->data['chave'] = $this->key;
        $this->data['metodo'] = 'getEndereco';
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $this->url );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));//send multi array
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $this->data );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1" );
        $this->result = curl_exec( $curl );
        curl_close( $curl );
        return $this->result;
    }

    public function getcep()
    {
        echo $this->_getcep( array( "cep" => $_REQUEST['cep'] ) );
    }

    public function getend()
    {
        echo $this->_getEndereco( array( "endereco" => $_REQUEST['endereco'] ) );
    }
}
?>