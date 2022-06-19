<?php

class Pagina extends PHPFrodo
{
    public $config = array( );
    public $page_url;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( $sid->check() && $sid->getNode( 'cliente_id' ) >= 1 )
        {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = ( string ) $sid->getNode( 'cliente_id' );
            $this->cliente_nome = (string) $sid->getNode('cliente_nome');
            $this->cliente_fullnome = (string) $sid->getNode('cliente_fullnome');
            $this->assign('cliente_nome', $this->cliente_nome);
            $this->assign( 'cliente_email', $this->cliente_email );
            $this->assign( 'cliente_msg', 'acesse aqui sua conta.' );
            $this->assign( 'logged', 'true' );
        }
        else
        {
            $this->assign( 'cliente_nome', 'visitante' );
            $this->assign( 'cliente_msg', 'faça seu login ou cadastre-se.' );
            $this->assign( 'logged', 'false' );
        }
        $qtdeITem = 0;
        if ( isset( $_SESSION['cart'] ) && count( $_SESSION['cart'] ) >= 1 )
        {
            $qtdeITem = count( $_SESSION['cart'] );
            $cart = new Carrinho;
            $cart->getTotal();
            $cart->total_compra = @number_format( $cart->total_compra, 2, ",", "." );
            $this->assign( 'cartTotal', "R$ " . $cart->total_compra );
        }
        $this->assign( 'qtdeItem', $qtdeITem );

        $this->select()
                ->from( 'config' )
                ->execute();
        if ( $this->result() )
        {
            $this->config = ( object ) $this->data[0];
            $this->assignAll();
        }
    }

    public function welcome()
    {
        $this->tpl( 'public/pagina.html' );
        $this->page_url = $this->uri_segment[1];
        $this->select()->from( 'page' )->where( "page_url = '$this->page_url'" )->execute();
        if ( $this->result() )
        {
            $this->getMenu();
            $this->assignAll();
            $this->render();
        }
        else
        {
            $this->redirect( "$this->baseUri/" );
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
}
/*end file*/
