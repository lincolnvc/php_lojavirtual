<?php

class Menu extends PHPFrodo {

    public $menulist = array();
    public $config_site_menu = array();

    public function __construct() {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        $this->config_site_menu = (int) $sid->getNode('config_site_menu');
    }

    public function getAll() {
        $this->method = "select";
        $this->query = "SELECT categoria_id, categoria_title,categoria_url, sub_title  FROM categoria INNER JOIN sub ON (sub_categoria = categoria_id) ";
        $this->query .= " INNER JOIN item ON (item_categoria = categoria_id) ";
        $this->query .= "GROUP BY categoria_id  ORDER BY categoria_title ASC, sub_title ASC ";
        $this->execute();
        $data = array();
        $menu = array();
        $depto = array('sub' => array(''));
        if ($this->result()) {
            $data = $this->data;
            $depto = $this->data;
            foreach ($data as $k => $v) {
                $cid = $data[$k]['categoria_id'];
                $this->method = "select";
                $this->query = "SELECT  sub_id,sub_url, sub_title FROM sub INNER JOIN item ON (item_sub = sub_id) WHERE sub_categoria = $cid";
                $this->query .= " GROUP BY sub_id  ORDER BY sub_title ASC ";
                $this->execute();
                $this->addkey('itens', '');
                $data[$k]['sub'] = $this->data;
                $depto[$k]['sub'] = $this->data;
            }
            $data_mobile = array_slice($data, 7, count($data) -1);
            $data = array_slice($data, 0, 7);
            return array(0 => $data, 1 => $depto , 2 => $data_mobile);
        }
    }
    
    
    /*
    public function get() {
        $this->select()
                ->from('categoria')
                ->join('item', 'item_categoria = categoria_id', 'INNER')
                ->where("item_show = 1")
                ->groupby('categoria_id')
                ->orderby('categoria_title asc')
                ->execute();
        if ($this->result()) {
            $data = $this->data;
            $this->addkey('categoria_small', '', 'categoria_title');
            foreach ($data as $k => $v) {
                $categoria_id = $v['categoria_id'];
                $this->select()
                        ->from('sub')
                        ->join('item', 'item_sub = sub_id', 'INNER')
                        ->where("sub_categoria = $categoria_id")
                        ->groupby('sub_id')
                        ->orderby('sub_title asc')
                        ->execute();
                if ($this->result()) {
                    $this->addkey('itens', '');
                    $data[$k]['sub'] = $this->data;
                }
            }
            return $data;
        }
    }

    public function get_top() {
        $this->select()
                ->from('categoria')
                ->join('item', 'item_categoria = categoria_id', 'INNER')
                ->where("item_show = 1")
                ->groupby('categoria_id')
                ->orderby('categoria_title asc')
                ->execute();
        if ($this->result()) {
            $data = $this->data;
            $this->addkey('categoria_small', '', 'categoria_title');
            foreach ($data as $k => $v) {
                $categoria_id = $v['categoria_id'];
                $this->select()
                        ->from('sub')
                        ->join('item', 'item_sub = sub_id', 'INNER')
                        ->where("sub_categoria = $categoria_id")
                        ->groupby('sub_id')
                        ->orderby('sub_title asc')
                        ->execute();
                if ($this->result()) {
                    $data[$k]['sub'] = $this->data;
                }
            }
            return $data;
        }
    }
    */
    public function getFooter() {
        $this->select()
                ->from('area')
                ->orderby('area_title asc')
                ->execute();
        if ($this->result()) {
            $aux = $this->data;
            foreach ($aux as $k => $v) {
                $v = (object) $v;
                $aid = $v->area_id;
                $this->select()
                        ->from('page')
                        ->where("page_area = $aid")
                        ->orderby('page_title asc')
                        ->execute();
                if ($this->result()) {
                    $aux[$k]['p'] = $this->data;
                }
            }
            return $aux;
        }
    }

}

/* end file */