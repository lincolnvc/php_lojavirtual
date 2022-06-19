<?php

//@header("Cache-Control: max-age=604800");
//@header('Content-Type: text/html; charset=UTF-8');

class Feed extends PHPFrodo {

    public $config = array();
    public $smtp = array();
    public $page_url;

    public function __construct() {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ($sid->check() && $sid->getNode('cliente_id') >= 1) {
            $this->cliente_email = ( string ) $sid->getNode( 'cliente_email' );
            $this->cliente_id = ( string ) $sid->getNode( 'cliente_id' );
            $this->cliente_nome = (string) $sid->getNode('cliente_nome');
            $this->cliente_fullnome = (string) $sid->getNode('cliente_fullnome');
            $this->assign('cliente_nome', $this->cliente_nome);
            $this->assign('cliente_email', $this->cliente_email);
            $this->assign('cliente_msg', 'acesse aqui sua conta.');
            $this->assign('logged', 'true');
            $this->getCliente();
        } else {
            $this->assign('cliente_nome', 'visitante');
            $this->assign('cliente_msg', 'faça seu login ou cadastre-se.');
            $this->assign('logged', 'false');
        }
        $qtdeITem = 0;
        if (isset($_SESSION['cart']) && count($_SESSION['cart']) >= 1) {
            $qtdeITem = count($_SESSION['cart']);
            $cart = new Carrinho;
            $cart->getTotal();
            $cart->total_compra = @number_format($cart->total_compra, 2, ",", ".");
            $this->assign('cartTotal', "R$ " . $cart->total_compra);
        }
        $this->assign('qtdeItem', $qtdeITem);

        $this->select()
                ->from('config')
                ->execute();
        if ($this->result()) {
            $this->config = (object) $this->data[0];
            $this->assignAll();
        }

        $this->select()->from('smtp')->execute();
        if ($this->result()) {
            $this->assignAll();
            $this->smtp = (object) $this->data[0];
        }
    }

    public function welcome() {
        $this->tpl('public/feed.php');
        $this->getMenu();
        //redes sociais footer
        $plug = new Social;
        $this->assign('social_fb', $plug->social_fb);
        $this->assign('social_tw', $plug->social_tw);
        $this->render();
    }

    public function getMenu() {
        $this->menu = new Menu;
        $menu = $this->menu->getAll();
        $this->fetch('cat', $menu[0]);
        $this->fetch('depto', $menu[1]);
        $this->fetch('depto-full', $menu[1]);
        $this->fetch('f', $this->menu->getFooter());
    }

    public function getCliente() {
        $this->select()
                ->from('cliente')
                ->where("cliente_id = $this->cliente_id")
                ->execute();
        if ($this->result()) {
            $this->map($this->data[0]);
            $this->config = (object) $this->data[0];
            $this->assignAll();
        }
    }

}

/*end file*/
