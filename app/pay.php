<?php

class Pay extends PHPFrodo {

    public $_pay = array();

    public function __construct() {
        parent:: __construct();
        $this->select()->from('pay')->where('pay_status = 1')->execute();
        $this->_pay = $this->data;
    }

    public function getPaysOn() {
        foreach ($this->_pay as $pay) {
            $pays[] = $pay['pay_name'];
        }
        $this->assigndata = array();
        if (!in_array('PagSeguro', $pays)) {
            ///$this->assigndata['showPagSeguro'] = 'hide';
            $this->assign('showPagSeguro', 'hide');
        }
        if (!in_array('PayPal', $pays)) {
            $this->assign('showPayPal', 'hide');
            //$this->assigndata['showPayPal'] = 'hide';
        }
        if (!in_array('PayBras', $pays)) {
            $this->assign('showPayBras', 'hide');
            //$this->assigndata['showPayBras'] = 'hide';
        }
        if (!in_array('Deposito', $pays)) {
            $this->assign('showDeposito', 'hide');
            //$this->assigndata['showPayBras'] = 'hide';
        }
        return $this->assigndata;
    }

    public function parcelamento($valor, $parcs) {
        //$fator = array( 1.00000, 0.52255, 0.35347, 0.26898, 0.21830, 0.18453, 0.16044, 0.14240, 0.12838, 0.11717, 0.10802, 0.10040 );
        $fator = explode(",", $this->_pay[0]['pay_fator_juros']);
        if (isset($fator[$parcs - 1]))
            return $this->round_up($valor * $fator[$parcs - 1], $parcs - 1);
    }

    public function parcelamentoTabela($valor, $parcs) {
        $tabela = "<div style='display:block; width:100%'>\n";
        $k = 0;
        $display = '';

        $cielo = false;
        foreach ($this->_pay as $p) {
            if ($p['pay_name'] == 'Cielo') {
                $cielo = true;
            }
        }
        if ($cielo == true) {
            $tabela .= "<div style='margin-top:10px; display:block !important; width:100%'>\n";
            $this->select()->from('pay')->where('pay_name = "Cielo"')->execute();
            $this->map($this->data[0]);
            $this->helper('cielo');
            $master = new Cielo;
            $master->taxa(0);
            $master->juros($this->pay_fator_juros);
            $master->valor($valor);
            $master->num_parcelas(($parcs <= $this->pay_c3) ? $parcs : $this->pay_c3); //configurado no item
            $master->desconto_avista($this->pay_c2); //10%
            $master->parcelas_sem_juros($this->pay_c1);
            $master->parcelamento();
            //$tabela .= "<p style='margin-top:10px; border:0px solid red; width:100%'>\n<a  href='javascript:void(0)' onclick='$(\"#parc_100\").toggle();' title='tabela de parcelamento com Cielo'>Parcelas com Cartão de Crédito</a>\n</p>\n";
            $tabela .= "<div id='parc_100' style='width:100%'>\n";
            $tabela .= $master->tabela_parcelas();
            $tabela .= "</div>\n";
            $tabela .= "</div>\n";
        }

        /*
         * parcelas pagseguro
        foreach ($this->_pay as $k => $v) {
            if ($this->_pay[$k]['pay_fator_juros'] != '') {
                $fator = explode(",", $this->_pay[$k]['pay_fator_juros']);
                if ($this->_pay[$k]['pay_name'] == 'PagSeguro') {
                    $tabela .= "<p style='margin-top:10px; border:0px solid red; width:100%'>\n<a  href='javascript:void(0)' onclick='$(\"#parc_200\").toggle();' title='tabela de parcelamento com Cielo'>Parcelas com PagSeguro </a>\n</p>\n";
                    $tabela .= "<div class='hides' id='parc_200' style='width:100%'>\n";
                    if ($k == 0) {
                        $display = 'hide';
                        $k++;
                    }
                    for ($i = 0; $i <= $parcs - 1; $i++) {
                        if (isset($fator[$i])) {
                            $resultado = $this->round_up($valor * $fator[$i], $i);
                            $tabela .= $resultado['texto'] . "<br>\n";
                        }
                    }
                    $tabela .= "</div>\n";
                    $tabela .= "</div>\n";
                }
            }
        }
        */


        return $tabela;
    }

    public function round_up($value, $num, $places = 2) {
        $mult = pow(10, $places);
        $parcela = number_format(($value >= 0 ? ceil($value * $mult) : floor($value * $mult)) / $mult, 2, ',', '.');
        $total = number_format($parcela * ($num + 1), 2, ',', '.');
        return array('parcela' => $parcela, 'total' => $total, 'texto' => "" . ($num + 1) . "x R$ " . $parcela, 'num' => ($num + 1));
    }

}

?>
