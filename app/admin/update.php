<?php

class Update extends PHPFrodo
{

    public function __construct()
    {
        parent::__construct();
    }

    public function welcome()
    {

	$this->query = "ALTER TABLE pedido ADD COLUMN  pedido_comprovante varchar(200);";
	$this->objBanco->query( "$this->query" );

	$this->query = "ALTER TABLE pay ADD COLUMN  pay_texto text;";
	$this->objBanco->query( "$this->query" );

	$this->query = "INSERT INTO `pay` (`pay_id`, `pay_name`, `pay_key`, `pay_user`, `pay_pass`, `pay_retorno`, `pay_status`, `pay_url_redir`, `pay_fator_juros`, `pay_texto`) VALUES (4, 'Deposito', '6253', 'Itaú, Caixa', '33300.6', '', 1, '', 'Rafael Clares Diniz', 'Banco Caixa Econômica Federal\r\nAgência: 7589\r\nConta Corrente: 12457-6\r\nTitular: Rafael Clares Diniz');";
	$this->objBanco->query( "$this->query" );

	$this->select()->from('pay')->where("pay_name = 'Deposito'")->execute();
	if($this->result()){
                echo '<h1>Atualização concluída!</h1>';
                echo "<br /> <a href='$this->baseUri/admin/pagamento/deposito/'>Ir para o módulo</a>";
	}else{
                echo '<h1>Falha na instalação do módulo, entre em contato com falecom@phpstaff.com.br</h1>';
                echo "<br /> <a href='$this->baseUri/admin/'>Voltar</a>";
	}

        $this->delete()->from( 'versao' )->execute();
        $this->insert( 'versao' )
                ->fields( array( 'versao_num', 'versao_data', 'versao_update' ) )
                ->values( array( '30', '24-09-2014', '1.6.1' ) )
                ->execute();
        $this->select()->from( 'versao' )->execute();
    }

}
?>
