<?php

class Finalizar extends PHPFrodo {

    public $config = array();
    public $page_url;
    public $logged = false;
    public $total_compra;
    public $pedido_id = 0;
    public $pedido_total_frete;
    public $pedido_frete;
    public $cliente_id;
    public $cliente_nome;
    public $cliente_email;
    public $fatura_link;
    public $itens_da_fatura;
    public $pedido_endereco;
    public $pedido_entrega;
    public $frete_prazo;

    public function __construct() {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ($sid->check() && $sid->getNode('cliente_id') >= 1) {
            $this->cliente_email = (string) $sid->getNode('cliente_email');
            $this->cliente_id = (string) $sid->getNode('cliente_id');
            $this->cliente_nome = (string) $sid->getNode('cliente_nome');
            $this->cliente_fullnome = (string) $sid->getNode('cliente_fullnome');
            $this->assign('cliente_nome', $this->cliente_nome);
            $this->assign('cliente_email', $this->cliente_email);
            $this->assign('cliente_msg', 'acesse aqui sua conta.');
            $this->assign('logged', 'true');
            $this->logged = true;
        } else {
            $this->assign('cliente_nome', 'visitante');
            $this->assign('cliente_msg', 'faï¿½a seu login ou cadastre-se.');
            $this->assign('logged', 'false');
        }
        $this->select()
                ->from('config')
                ->execute();
        if ($this->result()) {
            $this->map($this->data[0]);
            $this->config = (object) $this->data[0];
            $this->assignAll();
        }
        $this->select()->from('frete')->execute();
        $this->map($this->data[0]);
        //FORCE HTTPS
        /*
          if( $_SERVER['HTTPS'] != "on" ) {
          $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
          header("Location:$redirect");
          }
         */
    }

    public function welcome() {
        if ($this->logged == true) {
            $this->redirect("$this->baseUri/finalizar/entrega/");
        }
        if ($this->postIsValid(array('cliente_cadastrado' => 'string'))) {
            $cadastrado = $this->postGetValue('cliente_cadastrado');
            if ($cadastrado == 'nao') {
                $_SESSION['referer'] = "$this->baseUri/finalizar/";
                $_SESSION['email_cadastro'] = $this->postGetValue('cliente_email');
                $url_retorno = (string) $_SESSION['referer'];
                $this->redirect("$this->baseUri/cliente/cadastro/");
            }
        }
        $this->tpl('public/finalizar_identificacao.html');
        if ($this->postIsValid(array('cliente_email' => 'email', 'cliente_password' => 'string'))) {
            $cliente = new Cliente();
            $cliente->proccess();
            if ($cliente->login_status == false) {
                $msg_login = '<p class="alert alert-danger">';
                $msg_login .= 'Foram encontrados os seguintes problemas: <br>';
                $msg_login .= $cliente->message_login;
                $msg_login .= '</p>';
                $this->assign('message_login', "$msg_login");
            } else {
                $this->redirect("$this->baseUri/finalizar/entrega/");
            }
        }
        $this->getMenu();
        $this->render();
    }

    public function entrega() {
        $this->getItens();
        if ($this->logged == true) {
            $this->tpl('public/finalizar_entrega.html');
            $this->getMenu();
            $this->assignAll();
            if ($this->frete_opcoes == 1) {
                $this->getClienteAddr();
                $this->getRetiradaAddr();
            } elseif ($this->frete_opcoes == 2) {
                $this->getClienteAddr();
                $this->assign('evt_onload', 'ocultaRetirada()');
            } elseif ($this->frete_opcoes == 3) {
                $this->getRetiradaAddr();
                $this->assign('evt_onload', 'ocultaEntrega()');
            }
            $this->render();
        } else {
            $this->redirect("$this->baseUri/finalizar/");
        }
    }

    public function pagamento() {
        if (isset($_SESSION['finaliza-pagamento'])) {
            unset($_SESSION['finaliza-pagamento']);
        }
        if ($this->logged == true) {
            $_SESSION['finaliza-entrega'] = $_POST;
            $this->tpl('public/finalizar_pagamento.html');
            $this->select()->from('pay')->execute();
            if ($this->result()) {
                $disableMod = '';
                foreach ($this->data as $k => $v) {
                    if ($this->data[$k]['pay_status'] == '2') {
                        $mod = $this->data[$k]['pay_name'];
                        $disableMod .= "oculta$mod();\n";
                    }
                    //$this->data[$k]['pay_texto'] = nl2br( $this->data[$k]['pay_texto'] );
                }
                $this->assignAll();
                $this->assign('disableMod', $disableMod);
            }
            $this->getMenu();
            $this->render();
        } else {
            $this->redirect("$this->baseUri/finalizar/");
        }
    }

    public function confirmar() {
        $r = new Route;
        $r->set("FINALIZAR");
        if ($this->logged == true) {
            if (isset($_SESSION['finaliza-entrega']['entrega_selecionada_tipo'])) {
                if ($_SESSION['finaliza-entrega']['entrega_selecionada_tipo'] == 1) {
                    $_SESSION['mycep'] = (string) $_SESSION['finaliza-entrega']['entrega_selecionada_id'];
                } else {
                    $_SESSION['mycep_frete'] = "0";
                    $_SESSION['mycep_prazo'] = "Retirada no local";
                    $_SESSION['mycep_tipo_frete'] = "";
                }
                $_SESSION['mycep_entrega'] = (string) $_SESSION['finaliza-entrega']['entrega_selecionada'];
            } else {
                $this->redirect("$this->baseUri/finalizar/entrega/");
            }

            $this->local_entrega = "";
            if (isset($_POST['pagamento'])) {
                $_SESSION['finaliza-pagamento'] = (string) $_POST['pagamento'];
            }
            if (!isset($_SESSION['finaliza-pagamento'])) {
                $this->redirect("$this->baseUri/finalizar/entrega/");
            }
            $this->pay_gw = $_SESSION['finaliza-pagamento'];
            global $btn_popup;
            $btn_popup = false;

            $this->assign('pay_gw_url', "$this->baseUri/finalizar/checkout/");
            $this->tpl('public/finalizar_confirmar.html');

            if (isset($_SESSION['mycep_frete'])) {
                $frete_valor = $this->_money($_SESSION['mycep_frete']);
                $frete_valor_unformat = $_SESSION['mycep_frete'];
                $frete_prazo = (string) $_SESSION['mycep_prazo'] . "<br />" . $_SESSION['mycep_tipo_frete'];
                $local_entrega = (string) $_SESSION['finaliza-entrega']['entrega_selecionada_desc'];
                //( $frete_valor <= 0 ) ? $frete_valor = '<b>Grï¿½tis</b>' : $frete_valor = "R$  $frete_valor ";
                ( $frete_valor <= 0 ) ? $frete_valor = '<b></b>' : $frete_valor = "R$  $frete_valor ";
                $this->assign('frete_valor', $frete_valor);
                $this->assign('frete_prazo', $frete_prazo);
                $this->assign('local_entrega', $local_entrega);
            }
            if (isset($_SESSION['cupom']['alfa'])) {
                $this->assign('cupom_alfa', $_SESSION['cupom']['alfa']);
            }
            $this->getCarrinho();
            if ($this->pay_gw == 'cielo') {
                $this->select()->from('pay')->where('pay_name = "Cielo"')->execute();
                $this->map($this->data[0]);
                $this->helper('cielo');
                $visa = new Cielo;
                $visa->taxa(0);
                $visa->juros($this->pay_fator_juros);
                $visa->valor($this->total_compra + $frete_valor_unformat);
                $visa->num_parcelas($this->pay_c3);
                $visa->desconto_avista($this->pay_c2);
                $visa->parcelas_sem_juros($this->pay_c1);
                $visa->parcelamento();
                $visa->add_bandeira_array($this->pay_c4);
                //$visa->add_bandeira('Visa');
                //$visa->add_bandeira('Mastercard');
                //$visa->add_bandeira('Elo');
                //$this->printr($visa->combo_bandeiras());exit;
                $this->assign('cielo_parcelas', $visa->combo_parcelas());
                $this->assign('cielo_bandeiras', $visa->combo_bandeiras());
                $this->assign('cielo_info', $visa->header_info());
                $this->assign('evt_pay_module_start', $visa->get_event_start());
            } else {
                $this->assign('evt_pay_module_start', '');
                $this->assign('parcelamento-cartao', 'hide');                
            }
            $this->getMenu();
            $this->render();
        } else {
            $this->redirect("$this->baseUri/finalizar/");
        }
    }

    public function checkout() {
        if (isset($_SESSION['finaliza-pagamento'])) {
            $this->incluirPedido();
        } else {
            $this->redirect("$this->baseUri/finalizar/");
        }
    }

    public function incluirPedido() {
        //recupera valor total do pedido
        if (!isset($_SESSION['finaliza-entrega']) || !isset($_SESSION['mycep_entrega'])) {
            $this->redirect("$this->baseUri/finalizar/");
        }
        $cart = new Carrinho;
        $cart->getTotal();

        $this->pedido_cupom_desconto = 0;
        $this->pedido_cupom_alfa = $cart->cupom_alfa;
        $this->pedido_cupom_info = $cart->cupom_desconto_info;
        if ($cart->valor_desconto >= 1) {
            $this->pedido_cupom_desconto = $cart->valor_desconto;
        }

        $this->pedido_entrega = (string) $_SESSION['finaliza-entrega']['entrega_selecionada_tipo'];
        $this->pedido_endereco = (string) $_SESSION['mycep_entrega'];
        $this->prazo_frete = (string) $_SESSION['mycep_prazo'] . " - " . $_SESSION['mycep_tipo_frete'] . " ";
        $this->valor_frete = ( $cart->valor_frete );
        //$this->pedido_tipo_frete = $_SESSION['mycep_tipo_frete'];

        $this->pedido_total_frete = ( $cart->total_com_frete - $this->pedido_cupom_desconto );
        $this->pedido_total_produto = ( $cart->total_produtos );
        /*
          $k = array(
          'pedido_total_produto',
          'pedido_cupom_desconto',
          'pedido_sub_total',
          'pedido_frete',
          'pedido_total_frete'
          );
          $y = array(
          "$this->pedido_total_produto",
          "$this->pedido_cupom_desconto",
          ($this->pedido_total_produto - $this->pedido_cupom_desconto),
          "$this->valor_frete",
          "$this->pedido_total_frete"
          );
          $this->printr( array_combine( $k, $y) );exit;
         */


        //insere pedido               
        $f = array(
            'pedido_cliente',
            'pedido_data',
            'pedido_total_produto',
            'pedido_total_frete',
            'pedido_frete',
            'pedido_prazo',
            'pedido_entrega',
            'pedido_endereco',
            'pedido_cupom_desconto',
            'pedido_cupom_alfa',
            'pedido_cupom_info',
            'pedido_status'
        );

        $v = array(
            $this->cliente_id,
            date('d/m/Y h:i'),
            $this->_moneyUS($this->pedido_total_produto),
            $this->_moneyUS($this->pedido_total_frete),
            $this->_moneyUS($this->valor_frete),
            "$this->prazo_frete",
            "$this->pedido_entrega",
            "$this->pedido_endereco",
            $this->_moneyUS($this->pedido_cupom_desconto),
            "$this->pedido_cupom_alfa",
            "$this->pedido_cupom_info",
            1
        );

        if (!isset($_SESSION['FLUX_PEDIDO_ID'])) {
            $this->insert('pedido')->fields($f)->values($v)->execute();
            $this->pedido_id = mysql_insert_id();
            $_SESSION['FLUX_PEDIDO_ID'] = $this->pedido_id;
        } else {
            $this->pedido_id = $_SESSION['FLUX_PEDIDO_ID'];
            $this->update('pedido')->set($f, $v)->where("pedido_id = $this->pedido_id")->execute();
        }

        /*
         * TESTE INSERCAO
          $this->select()->from( 'pedido' )->where( "pedido_id = $this->pedido_id" )->execute();
          $this->printr( $this->data );
          $this->printr( $f );
          $this->printr( $v );
          //echo $this->_money(($this->data[0]['pedido_total_produto'] + $this->data[0]['pedido_cupom_desconto'] ) + $this->data[0]['pedido_frete']);
          exit;
         */

        //insere itens do pedido
        $this->itens_da_fatura = "";
        $itens = $_SESSION['cart'];
        sort($itens);
        foreach ($itens as $item) {
            $i = (object) $item;
            $i->item_preco = number_format($i->item_preco, 2, '.', '');
            //if ( !isset( $_SESSION['FLUX_PEDIDO_ID'] ) ){
            $f = array('lista_pedido', 'lista_item', 'lista_preco', 'lista_title', 'lista_qtde', 'lista_foto', 'lista_atributos', 'lista_atributo_ped');
            $v = array("$this->pedido_id", "$i->item_id", "$i->item_preco", "$i->item_title", "$i->item_qtde", "$i->item_foto", "$i->atributos", "$i->atributo_ped");
            $this->insert('lista')->fields($f)->values($v)->execute();
            //}
            //baixa nos atributos
            if (isset($i->atributos) && !empty($i->atributos)) {
                $attrs = explode("|", $i->atributos);
                foreach ($attrs as $attr) {
                    $attr = explode(",", $attr);
                    if (count($attr) >= 2) {
                        $iattr_id = explode("|", $attr[3]);
                        $iattr_id = $iattr_id[0];
                        $iattr_atributo = $attr[2];
                        $cond = "relatrr_atributo = $iattr_atributo AND relatrr_iattr = $iattr_id AND relatrr_item  = $i->item_id";
                        $this->decrement('relatrr', 'relatrr_qtde', $i->item_qtde, "$cond");
                    }
                }
            }
            //baixa no estoque
            $this->decrement('item', 'item_estoque', $i->item_qtde, "item_id = $i->item_id");
            $i->item_qtde_preco = $i->item_qtde * $i->item_preco;
            $this->itens_da_fatura .= "Item: $i->item_title $i->atributo_ped <br/> Qtde: $i->item_qtde <br />Valor: R$ $i->item_preco <br/>  <br />";
        }

        $this->local_entrega = (string) $_SESSION['finaliza-entrega']['entrega_selecionada_desc'];

        if (isset($_SESSION['finaliza-pagamento'])) {
            if ($_SESSION['finaliza-pagamento'] == 'cielo') {
                //inclui fatura cielo
                $this->incluirFaturaCielo();
            }

            if ($_SESSION['finaliza-pagamento'] == 'pagseguro') {
                //inclui fatura pagSeguro
                $this->incluirFaturaPagSeguro();
            }
            if ($_SESSION['finaliza-pagamento'] == 'paypal') {
                //inclui fatura paypal
                $this->incluirFaturaPayPal();
            }

            if ($_SESSION['finaliza-pagamento'] == 'deposito') {
                //inclui fatura deposito
                $this->incluirFaturaDeposito();
            }
        } else {
            $this->redirect("$this->baseUri/finalizar/");
        }
    }

    public function incluirFaturaCielo() {
        $descricao = "";
        $this->select()->from('pay')->where('pay_name = "Cielo"')->execute();
        $this->map($this->data[0]);
        if ($this->pedido_id >= 1) {
            $this->select()
                    ->from('cliente')
                    ->join('endereco', 'endereco_cliente = cliente_id', 'INNER')
                    ->where("cliente_id = $this->cliente_id and endereco_tipo = 1")
                    ->execute();
            $this->encode('endereco_uf', 'strtoupper');
            $this->map($this->data[0]);
            $this->cliente_telefone = preg_replace('/\W/', '', $this->cliente_telefone);
            $this->cliente_ddd = substr($this->cliente_telefone, 0, 2);
            $this->cliente_telefone = substr($this->cliente_telefone, 2, -1);
            $this->select()
                    ->from('pedido')
                    ->where("pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id")
                    ->execute();
            if ($this->result()) {
                $this->map($this->data[0]);
                $pedidos = $this->data;
                foreach ($pedidos as $ped) {
                    $this->select()
                            ->from('lista')
                            ->where("lista_pedido = $this->pedido_id")
                            ->execute();
                    if ($this->result()) {
                        $this->cut('lista_title', 60, '...');
                        $itens = $this->data;
                        foreach ($itens as $i) {
                            $this->map($i);
                            $this->lista_preco = preg_replace('/\,/', '', $this->lista_preco);
                            $descricao .= "$this->lista_qtde x $this->lista_title - $this->lista_atributo_ped (#$this->lista_item)";
                        }
                    }
                }
                //atualiza cupom
                if ($this->pedido_cupom_desconto != 0) {
                    $this->pedido_cupom_desconto = $this->_moneyUS($this->pedido_cupom_desconto);
                    //Atualiza cupom como usado
                    $this->cupom_update = date('d/m/Y H:i:s');
                    $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                    $f = array('cupom_status', 'cupom_pedido', 'cupom_update');
                    $v = array(1, $this->pedido_id, $this->cupom_update);
                    $this->update('cupom')->set($f, $v)->where("cupom_alfa = '$this->cupom_alfa'")->execute();
                }
                if ($this->pedido_frete <= 0) {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }
                $pedido_id = $this->pedido_id;
                $url_retorno = "$this->baseUri/notificacao/cielo/$pedido_id/";
                $valor_total = ((($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->valor_frete));

                $valor_total = preg_replace(array('/\,/', '/\./'), array('', ''), $valor_total);
                @header('Content-Type: text/html; charset=UTF-8');
                $id = $pedido_id;
                $bandeira = strtolower($_POST['cielo_bandeira']);
                $agora = date('Y-m-d\TH:i:s');
                $cartao_nome_titular = $_POST['cartao_nome'];
                //Nome do dono do cartï¿½o exatamente como impresso no mesmo.
                $numero_cartao = $_POST['cartao_num'];
                $cartao_codigo = $_POST['cartao_cod'];
                $indicador = ($cartao_codigo != '') ? '1' : '0'; //Se o cartão não tiver código de segurança o indicaro é zero, caso contrário 1
                $qtd_parcelas = $_POST['cielo_parcelas']; //Quantidade total de parcelas
                $parcela_valor = $_POST['parcela_valor']; //valor por parcela
                $data_vencimento = $_POST['cartao_ano'] . "" . $_POST['cartao_mes'];
                $total_parcelado = $_POST['total_parcelado']; //valor parcelado
                $total_parcelado_cielo = preg_replace('/\./', '', $total_parcelado); //valor parcelado
                $produto = ($qtd_parcelas == '1') ? '1' : '3';
                $autorizar = '3';
                $captura = 'true'; //A captura ï¿½ quando apï¿½s aprovada a transaï¿½ï¿½o
                $cielo_numero = "$this->pay_user"; //Nï¿½mero de filiaï¿½ï¿½o da cielo, neste caso ï¿½ o exemplo da homologaï¿½ï¿½o
                $chave_cielo = "$this->pay_key"; // Chave de filiaï¿½ï¿½o da cielo exemplo da homologaï¿½ï¿½o                

                $pedido_pay_obs = "Parcelado em $parcela_valor com " . strtoupper($bandeira);
                $pedido_obs = "Nome do Titular do Cartão:  $cartao_nome_titular \n";
                $pedido_obs .= "Bandeira do Cartão: $bandeira\n";

                if ($this->pay_pass == 2) {
                    $cielo_numero = '1006993069'; //Número de filiação da cielo
                    $chave_cielo = '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3';
                    $url = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';
                } else {
                    $cielo_numero = "$this->pay_user";
                    $chave_cielo = "$this->pay_key";
                    $url = 'https://ecommerce.cbmp.com.br/servicos/ecommwsec.do';
                }

                $string = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?> 
<requisicao-transacao id="$this->pedido_id" versao="1.1.1">
    <dados-ec>
          <numero>$cielo_numero</numero>
          <chave>$chave_cielo</chave>
    </dados-ec>
    <dados-portador>
        <numero>$numero_cartao</numero>
        <validade>$data_vencimento</validade>
        <indicador>$indicador</indicador>
        <codigo-seguranca>$cartao_codigo</codigo-seguranca>
        <nome-portador>$cartao_nome_titular</nome-portador>
    </dados-portador>
    <dados-pedido>
	    <numero>$id</numero>
	    <valor>$valor_total</valor>
	    <moeda>986</moeda>
	    <data-hora>$agora</data-hora>
                  <descricao><!--[CDATA[teste]]--></descricao>
	    <idioma>PT</idioma>
    </dados-pedido>
    <forma-pagamento>
        <bandeira>$bandeira</bandeira>
        <produto>$produto</produto>
        <parcelas>$qtd_parcelas</parcelas>
    </forma-pagamento>
    <autorizar>$autorizar</autorizar>
    <capturar>$captura</capturar>
</requisicao-transacao>
XML;
                //echo $string;exit;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, 'mensagem=' . $string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_TIMEOUT, 40);
                curl_setopt($ch, CURLOPT_CAINFO, "app/helpers/cielo/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
                curl_setopt($ch, CURLOPT_SSLVERSION, 4); //em producao alterar de 4 para 1
                $string = curl_exec($ch);
                $xml = simplexml_load_string($string);
                $erro_curl =  curl_error($ch);
                curl_close($ch);
                $_SESSION['__CIELO_MSG__'] = '';
                $flag_pass = false;
                $this->url_code = "";
                $this->url = "";

                if (isset($xml->tid)) {
                    if ($xml->captura->codigo == '6' AND $xml->autorizacao->codigo == '6') {
                        $this->pedido_status = 3;
                        $this->url_code = $xml->tid;
                        $this->url = "";
                        $flag_pass = true;
                    } else {
                        // echo '<p class="alert alert-danger">Transação não autorizada: ' . $xml->autorizacao->mensagem . '</p>';exit;
                        $_SESSION['__CIELO_MSG__'] = $xml->autorizacao->mensagem;
                        $flag_pass = false;
                        $this->pedido_status = 7;
                    }
                } else {
                    //echo '<p class="alert alert-danger">Transação não autorizada: ' . $xml->mensagem . '</p>';exit;
                    $_SESSION['__CIELO_MSG__'] = "Transação não autorizada";//$xml->mensagem;
                    $flag_pass = false;
                    $this->pedido_status = 7;
                }

                //$this->printr($xml);echo $_SESSION['__CIELO_MSG__']; echo $erro_curl;exit;

                //atualiza pedido com url e codigo pagseguro
                $this->update('pedido')
                        //->set(array('pedido_pay_code', 'pedido_pay_url', 'pedido_total_frete', 'pedido_total_produto', 'pedido_pay_gw', 'pedido_status'), array($this->url_code, $this->url, $total_parcelado, $total_parcelado - $this->pedido_frete, 3, $this->pedido_status))
                        ->set(array('pedido_pay_code', 'pedido_pay_url', 'pedido_total_frete', 'pedido_total_produto', 'pedido_pay_gw', 'pedido_status', 'pedido_pay_obs', 'pedido_obs'), array($this->url_code, $this->url, $total_parcelado, $total_parcelado - $this->pedido_frete, 3, $this->pedido_status, $pedido_pay_obs, $pedido_obs))
                        ->where("pedido_id = $this->pedido_id")
                        ->execute();
                $this->cieloCheck($this->pedido_id);
                $this->notificarAdmin();
                $this->notificarFaturaCliente();
                $this->clear();
                $this->redirect("$this->baseUri/cliente/pedido/$this->pedido_id/show/");
            }
        }
    }

    public function cieloCheck($pedido_id) {
        if (isset($pedido_id) && $pedido_id >= 1) {
            $this->select()->from('pay')->where('pay_name = "Cielo"')->execute();
            $this->map($this->data[0]);

            $this->select()->from('pedido')->where("pedido_id = $pedido_id")->execute();
            $this->map($this->data[0]);

            $this->pedido_id = $pedido_id;
            $tid = $this->data[0]['pedido_pay_code']; //TID que retornou quando a transacao foi criada.
            $cielo_numero = "$this->pay_user"; //NÃºmero de filiaÃ§Ã£o da cielo
            $chave_cielo = "$this->pay_key"; // Chave de filiaÃ§Ã£oo da cielo 

            $string = <<<XML
<?xml version="1.0" encoding="ISO-8859-1"?> 
<requisicao-consulta id="$pedido_id" versao="1.1.1">
<tid>$tid</tid>
<dados-ec>
<numero>$cielo_numero</numero>
<chave>$chave_cielo</chave>
</dados-ec>
</requisicao-consulta>
XML;

            $ambiente = 1;
            if ($ambiente == '2') {
                $url = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do'; // url homologaï¿½ï¿½o
            } else {
                $url = 'https://ecommerce.cbmp.com.br/servicos/ecommwsec.do'; // url producao
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'mensagem=' . $string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 40);
            curl_setopt($ch, CURLOPT_CAINFO, "app/helpers/cielo/ssl/VeriSignClass3PublicPrimaryCertificationAuthority-G5.crt");
            curl_setopt($ch, CURLOPT_SSLVERSION, 1); //alterar de 4 para 1

            $string = curl_exec($ch);
            curl_close($ch);
            $xml = @simplexml_load_string($string);

            if (isset($xml->tid) AND $xml->captura->codigo == '6' AND $xml->autorizacao->codigo == '6') {
                $node = 'forma-pagamento';
                $node = $xml->$node;

                $node_1 = 'dados-pedido';
                $node_1 = $xml->$node_1;

                $this->helper('cielo');

                $visa = new Cielo;
                $visa->taxa(0);
                $visa->juros($this->pay_fator_juros);
                $visa->valor($this->pedido_total_frete);
                $visa->num_parcelas($this->pay_c3);
                $visa->desconto_avista($this->pay_c2);
                $visa->parcelas_sem_juros($this->pay_c1);
                $visa->parcelamento();
                $visa->add_bandeira_array($this->pay_c4);
                //$visa->add_bandeira('Visa');
                //$visa->add_bandeira('Mastercard');
                //$visa->add_bandeira('Elo');
                //$total = $node_1->{'valor'}[0];
                $total = $visa->moeda($this->pedido_total_frete);
                $visa->valor_parcela = $visa->moeda($total / $node->{'parcelas'});
                $bandeira = ucfirst($node->{'bandeira'}[0]);

                $obs = "<strong>Forma de pagamento:</strong> $bandeira <Br>";
                $obs .= "<strong>Parcelas:</strong> " . $node->{'parcelas'} . " x " . ($visa->valor_parcela) . " = " . $total . " ***<Br>";
                $obs .= utf8_decode("<strong>Autorização:</strong> " . ($xml->autorizacao->{'mensagem'}[0]) . "<Br>");
                $obs .= "<strong>Captura:</strong>  " . ($xml->captura->{'mensagem'}[0]) . "<Br>";
                $obs .= "<strong>TID:</strong>  $xml->tid ";

                $this->update('pedido')
                        ->set(array('pedido_status', 'pedido_pay_obs'), array(3, ($obs)))
                        ->where("pedido_id = $pedido_id")
                        ->execute();
            } else {
                $node = 'forma-pagamento';
                if (isset($xml->$node->bandeira)) {
                    $xml->$node->bandeira;
                }
                if ($this->pedido_status <> 3) {
                    $this->update('pedido')
                            ->set(array('pedido_status'), array(7))
                            ->where("pedido_id = $pedido_id")
                            ->execute();
                }
            }
        }
    }

    public function incluirFaturaDeposito() {
        $this->select()->from('pay')->where('pay_name = "Deposito"')->execute();
        $this->map($this->data[0]);
        $this->pay_texto = nl2br($this->pay_texto);
        if ($this->pedido_id >= 1) {
            $this->select()
                    ->from('cliente')
                    ->join('endereco', 'endereco_cliente = cliente_id', 'INNER')
                    ->where("cliente_id = $this->cliente_id and endereco_tipo = 1")
                    ->execute();
            $this->encode('endereco_uf', 'strtoupper');
            $this->map($this->data[0]);
            $this->cliente_telefone = preg_replace('/\W/', '', $this->cliente_telefone);
            $this->cliente_ddd = substr($this->cliente_telefone, 0, 2);
            $this->cliente_telefone = substr($this->cliente_telefone, 2, -1);
            $this->select()
                    ->from('pedido')
                    ->where("pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id")
                    ->execute();
            if ($this->result()) {
                $this->map($this->data[0]);
                $pedidos = $this->data;
                //atualiza cupom
                if ($this->pedido_cupom_desconto != 0) {
                    $this->pedido_cupom_desconto = $this->_moneyUS($this->pedido_cupom_desconto);
                    //Atualiza cupom como usado
                    $this->cupom_update = date('d/m/Y H:i:s');
                    $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                    $f = array('cupom_status', 'cupom_pedido', 'cupom_update');
                    $v = array(1, $this->pedido_id, $this->cupom_update);
                    $this->update('cupom')->set($f, $v)->where("cupom_alfa = '$this->cupom_alfa'")->execute();
                }
                if ($this->pedido_frete <= 0) {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }

                $this->update('pedido')
                        ->set(array('pedido_pay_code', 'pedido_pay_url', 'pedido_pay_gw'), array('deposito', '', 4))
                        ->where("pedido_id = $this->pedido_id")
                        ->execute();
                $this->fatura_link = "";
                $this->notificarAdmin();
                $this->notificarFaturaCliente();
                $this->clear();
                $this->redirect("$this->baseUri/cliente/pedido/$this->pedido_id/show/");
            }
        }
    }

    public function incluirFaturaPayPal() {
        
    }

    public function incluirFaturaPagSeguro() {
        $this->select()->from('pay')->where('pay_name = "PagSeguro"')->execute();
        $this->map($this->data[0]);
        $this->helper('pagseguro');
        if ($this->pedido_id >= 1) {
            $this->select()
                    ->from('cliente')
                    ->join('endereco', 'endereco_cliente = cliente_id', 'INNER')
                    ->where("cliente_id = $this->cliente_id and endereco_tipo = 1")
                    ->execute();
            $this->encode('endereco_uf', 'strtoupper');
            $this->map($this->data[0]);
            $this->cliente_telefone = preg_replace('/\W/', '', $this->cliente_telefone);
            $this->cliente_ddd = substr($this->cliente_telefone, 0, 2);
            $this->cliente_telefone = substr($this->cliente_telefone, 2, -1);
            $this->select()
                    ->from('pedido')
                    ->where("pedido_cliente = $this->cliente_id AND pedido_id = $this->pedido_id")
                    ->execute();
            if ($this->result()) {
                $this->map($this->data[0]);
                $pedidos = $this->data;
                //start req
                $this->pReq = new PagSeguroPaymentRequest();
                $this->pReq->setCurrency("BRL");
                //add Itens to req
                foreach ($pedidos as $ped) {
                    $this->select()
                            ->from('lista')
                            ->where("lista_pedido = $this->pedido_id")
                            ->execute();
                    if ($this->result()) {
                        $this->cut('lista_title', 60, '...');
                        $itens = $this->data;
                        foreach ($itens as $i) {
                            $this->map($i);
                            $this->lista_preco = preg_replace('/\,/', '', $this->lista_preco);
                            $this->pReq->addItem($this->lista_item, "$this->lista_title - $this->lista_atributo_ped", $this->lista_qtde, $this->lista_preco);
                        }
                    }
                }
                //atualiza cupom
                if ($this->pedido_cupom_desconto != 0) {
                    $this->pedido_cupom_desconto = $this->_moneyUS($this->pedido_cupom_desconto);
                    //Atualiza cupom como usado
                    $this->cupom_update = date('d/m/Y H:i:s');
                    $this->cupom_alfa = $_SESSION['cupom']['alfa'];
                    $f = array('cupom_status', 'cupom_pedido', 'cupom_update');
                    $v = array(1, $this->pedido_id, $this->cupom_update);
                    $this->update('cupom')->set($f, $v)->where("cupom_alfa = '$this->cupom_alfa'")->execute();
                    $this->pReq->setExtraAmount(-$this->pedido_cupom_desconto);
                }
                if ($this->pedido_frete <= 0) {
                    $this->pedido_frete = "0.00";
                    $this->valor_frete = "0.00";
                }
                $this->pReq->setReference("$this->pedido_id");
                //frete
                $shipping = new PagSeguroShipping();
                $type = new PagSeguroShippingType($this->pedido_tipo_frete);
                $shipping->setType($type);
                $shipping->setCost($this->pedido_frete);
                $address = new PagSeguroAddress(array(
                    $this->endereco_cep,
                    $this->endereco_rua,
                    $this->endereco_num,
                    $this->endereco_complemento,
                    $this->endereco_bairro,
                    $this->endereco_cidade,
                    $this->endereco_uf,
                    'BRA'));
                $shipping->setAddress($address);
                $this->pReq->setShipping($shipping);
                $this->pReq->setSender($this->cliente_fullnome, $this->cliente_email, $this->cliente_ddd, $this->cliente_telefone);
                //$this->pReq->setRedirectUrl( "$this->pay_url_redir" ); 
                //registrando no pagseguro
                try {
                    $credentials = new PagSeguroAccountCredentials("$this->pay_user", "$this->pay_key");
                    $this->url = $this->pReq->register($credentials);
                    $this->url_code = explode('=', $this->url);
                    $this->url_code = trim($this->url_code[1]);
                } catch (PagSeguroServiceException $e) {
                    $this->_rollback();
                    die($e->getMessage());
                }
                //retorno da req
                //atualiza pedido com url e codigo pagseguro
                $this->update('pedido')
                        ->set(array('pedido_pay_code', 'pedido_pay_url', 'pedido_pay_gw'), array($this->url_code, $this->url, 1))
                        ->where("pedido_id = $this->pedido_id")
                        ->execute();
                $this->fatura_link = "$this->url";
                //Notifica Cliente / Admin
                $this->notificarAdmin();
                $this->notificarFaturaCliente();
                $this->clear();
                $this->redirect("$this->baseUri/cliente/pedido/$this->pedido_id/show/");
            }
        }
    }

    public function notificarAdmin() {
        $body = '<html><body>';
        $body .= '<h1 style="font-size:15px;">Novo Pedido Criado</h1>';
        $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Pedido ID:</strong> </td><td style="color:#333">' . $this->pedido_id . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Data:</strong> </td><td>' . date('d/m/Y h:s') . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Cliente:</strong> </td><td style="color:#333">' . $this->cliente_fullnome . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Email:</strong> </td><td style="color:#333">' . $this->cliente_email . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Local de entrega:</strong> </td><td style="color:#333">' . $this->local_entrega . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Itens:</strong> </td><td>' . $this->itens_da_fatura . '</td></tr>';
        if ($this->pedido_cupom_desconto != 0) {
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Desconto:</strong> </td><td style="color:#333"> -' . $this->_money($this->pedido_cupom_desconto) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Subtotal:</strong> </td><td>' . $this->_money($this->pedido_total_produto - $this->pedido_cupom_desconto) . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Frete:</strong> </td><td style="color:#333">' . $this->_money($this->valor_frete) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Valor Total:</strong> </td><td>' . $this->_money(($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->valor_frete) . '</td></tr>';
        } else {
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Frete:</strong> </td><td style="color:#333">' . $this->_money($this->valor_frete) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Total a Pagar:</strong> </td><td>' . $this->_money(($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->valor_frete) . " - $this->prazo_frete " . '</td></tr>';
        }
        $body .= '</table>';
        $body .= '</body></html>';
        $n = array(
            'subject' => "Novo Pedido Nº $this->pedido_id",
            'body' => $body
        );
        $m = new sendmail;
        $m->sender($n);
    }

    public function notificarFaturaCliente() {
        $body = '<html><body>';
        $body .= '<h1 style="font-size:15px;">Olá ' . $this->cliente_nome . ', recebemos seu pedido nº' . $this->pedido_id . '</h1>';
        $body .= '<table style="border-color: #666; font-size:11px" cellpadding="10">';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Pedido ID:</strong> </td><td style="color:#333">' . $this->pedido_id . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Data:</strong> </td><td>' . date('d/m/Y h:s') . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Cliente:</strong> </td><td style="color:#333">' . $this->cliente_nome . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Email:</strong> </td><td style="color:#333">' . $this->cliente_email . '</td></tr>';
        $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Local de entrega:</strong> </td><td style="color:#333">' . $this->local_entrega . '</td></tr>';
        $body .= '<tr style="background: #eee;"><td><strong>Itens:</strong> </td><td>' . $this->itens_da_fatura . '</td></tr>';
        if ($this->pedido_cupom_desconto != 0) {
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Desconto:</strong> </td><td style="color:#333"> -' . $this->_money($this->pedido_cupom_desconto) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Subtotal:</strong> </td><td>' . $this->_money($this->pedido_total_produto - $this->pedido_cupom_desconto) . '</td></tr>';
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Frete:</strong> </td><td style="color:#333">' . $this->_money($this->valor_frete) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Valor Total:</strong> </td><td>' . $this->_money(($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->valor_frete) . '</td></tr>';
        } else {
            $body .= '<tr style="background: #fff;"><td style="color:#333"><strong>Frete:</strong> </td><td style="color:#333">' . $this->_money($this->valor_frete) . '</td></tr>';
            $body .= '<tr style="background: #eee;"><td><strong>Total a Pagar:</strong> </td><td>' . $this->_money(($this->pedido_total_produto - $this->pedido_cupom_desconto) + $this->valor_frete) . " - $this->prazo_frete " . '</td></tr>';
        }
        $body .= '<br/><br/>';
        $body .= "<a href=\"$this->baseUri/cliente/pedido/$this->pedido_id/\">Acompanhe o status de seu pedido em nosso site.</a>";
        $body .= '</body></html>';



        $n = array(
            'email' => $this->cliente_email,
            'subject' => "Detalhes do pedido #$this->pedido_id",
            'body' => $body
        );
        $m = new sendmail;
        $m->sender($n);
    }

    public function clear() {
        $_SESSION['cart'] = null;
        unset($_SESSION['cart']);

        $_SESSION['mycep_prazo'] = null;
        unset($_SESSION['mycep_prazo']);

        $_SESSION['mycep_frete'] = null;
        unset($_SESSION['mycep_frete']);

        $_SESSION['mycep_entrega'] = null;
        unset($_SESSION['mycep_entrega']);

        $_SESSION['mycep'] = null;
        unset($_SESSION['mycep']);

        $_SESSION['referer'] = null;
        unset($_SESSION['referer']);

        $_SESSION['finaliza-pagamento'] = null;
        unset($_SESSION['finaliza-pagamento']);

        $_SESSION['finaliza-entrega'] = null;
        unset($_SESSION['finaliza-entrega']);

        $_SESSION['cupom'] = null;
        unset($_SESSION['cupom']);

        $_SESSION['FLUX_PEDIDO_ID'] = null;
        unset($_SESSION['FLUX_PEDIDO_ID']);
    }

    public function novoendereco() {
        $_SESSION['referer'] = "$this->baseUri/finalizar/entrega/";
        $this->redirect("$this->baseUri/cliente/enderecoNovo/");
    }

    public function getClienteAddr() {
        $this->select()
                ->from('endereco')
                ->where("endereco_cliente = $this->cliente_id")
                ->orderby('endereco_title asc')
                ->execute();
        if ($this->result()) {
            $this->fetch('addr', $this->data);
        }
    }

    //retorna enderecos de retirada
    public function getRetiradaAddr() {
        $this->select()
                ->from('retirada')
                ->orderby('retirada_local asc')
                ->execute();
        if ($this->result()) {
            foreach ($this->data as $k => $v) {
                if (strlen($this->data[$k]['retirada_complemento']) >= 2) {
                    $this->data[$k]['retirada_num'] = $this->data[$k]['retirada_num'] . ", " . $this->data[$k]['retirada_complemento'];
                }
            }
            $this->fetch('raddr', $this->data);
        } else {
            $this->assign('evt_onload', 'ocultaRetirada()');
        }
    }

    public function getMenu() {
        $this->menu = new Menu;
        $menu = $this->menu->getAll();
        $this->fetch('cat', $menu[0]);
        $this->fetch('depto', $menu[1]);
        $this->fetch('depto-full', $menu[1]);
        $this->fetch('f', $this->menu->getFooter());
    }

    public function getItens() {
        $cart = new Carrinho;
        $cart->getTotal();
        if (isset($_SESSION['cart'])) {
            $this->qtde_item = count($_SESSION['cart']);
            if ($this->qtde_item <= 0) {
                $this->redirect("$this->baseUri/carrinho/");
            }
        } else {
            $this->redirect("$this->baseUri/carrinho/");
        }
    }

    //em casos de erro no gateway e refresh na conclusï¿½o, evita a duplicaï¿½ï¿½o de itens no pedido
    public function _rollback() {
        //remove pedido
        $this->delete()->from('pedido')->where("pedido_id = $this->pedido_id")->execute();
        if (isset($_SESSION['FLUX_PEDIDO_ID'])) {
            unset($_SESSION['FLUX_PEDIDO_ID']);
        }
        //reverte cupom
        if (isset($_SESSION['cupom']['id'])) {
            $this->cupom_id = $_SESSION['cupom']['id'];
            $f = array('cupom_status', 'cupom_pedido', 'cupom_update');
            $v = array(0, 0, '');
            $this->update('cupom')->set($f, $v)->where("cupom_id = $this->cupom_id")->execute();
        }
    }

    public function getCarrinho() {
        $cart = new Carrinho;
        $cart->getTotal();
        if (count($_SESSION['cart']) <= 0) {
            $this->redirect("$this->baseUri/carrinho/");
        }
        $this->data = $_SESSION['cart'];
        $this->money('item_preco');
        $this->money('valor_total');
        $this->cut('item_title', 75, '...');
        $this->fetch('cart', $this->data);

        $this->total_compra = $cart->valor_total;
        $this->assign('cartTotal', $cart->valor_total);

        if (isset($_SESSION['mycep_frete'])) {
            $frete_valor = (string) $_SESSION['mycep_frete'];
            $frete_prazo = (string) $_SESSION['mycep_prazo'];
            $this->assign('valor_frete', $frete_valor);
            $this->assign('valor_prazo', $frete_prazo);
        }

        $this->assign('total_sem_desconto', $this->_money($cart->total_sem_desconto));
        $this->assign('total_com_desconto', $this->_money($cart->total_com_desconto));

        $this->assign('valor_desconto', $this->_money($cart->valor_desconto));
        $this->assign('total_com_frete', $this->_money($cart->total_com_frete));

        $this->assign('cupom_desconto_info', $cart->cupom_desconto_info);
        $this->assign('cupom_msg', $cart->cupom_msg);

        if ($cart->cupom_desconto > 1) {
            $this->assign('valor_total', $this->_money($cart->valor_total));
            $this->assign('desconto_ext', $cart->cupom_desconto_ext);
            $this->assign('btn-cupom-valida', 'hide');
        } else {
            $this->assign('btn-cupom-remove', 'hide');
        }
    }

    public function val2bd($str) {
        $str = preg_replace('/\./', '', $str);
        $str = preg_replace('/\,/', '', $str);
        return $str;
    }

    public function _money($val) {
        return @number_format($val, 2, ",", ".");
    }

    public function _moneyUS($val) {
        return @number_format($val, 2, ".", "");
    }

    public function _double($val) {
        return @number_format($val, 2, ".", ",");
    }

    public function _float($val) {
        return @number_format($this->val2bd($val), 2, ",", "");
    }

}

/*end file*/
