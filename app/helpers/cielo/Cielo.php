
<?php
@header( 'Content-Type: text/html; charset=iso-8859-1' );

$server = preg_replace('/www\./', '', $_SERVER['SERVER_NAME']);
if (!in_array($server, array('acquaperfumaria.com.br', 'localhost', '127.0.0.1'))) {
 //exit;
}

class Fluxshop_Payment {

    public function moeda($valor, $moeda = 'brl', $mostrar_zero = false) {
        if ($moeda == 'brl') {
            $valor = $valor ? number_format($valor, 2, '.', '') : ($mostrar_zero ? '0.00' : '');
        } else {
            $valor = $valor ? number_format($valor, 2, '', '') : ($mostrar_zero ? '0.00' : '');
        }
        if (strlen($valor) <= 3) {
            $valor .= "00";
        }
        return $valor;
    }

    public function moeda_real($valor) {
        $valor = number_format($valor, 2, ',', '.');
        return $valor;
    }

    public function caracteresEsquerda($string, $num) {
        return substr($string, 0, $num);
    }

    public function caracteresDireita($string, $num) {
        return substr($string, strlen($string) - $num, $num);
    }

}

class Cielo extends Fluxshop_Payment {
    public $total_real;
    public $total_cielo;
    public $valor;
    public $taxa;
    public $juros;
    public $desconto_avista = 0;
    public $num_parcelas = 1;
    public $parcelas_sem_juros = 0;
    public $valor_parcela;
    public $lista_parcelas = array();
    public $lista_bandeiras = array();

    public function valor($valor) {
        $this->valor = $valor;
    }

    public function add_bandeira($b) {
        $this->lista_bandeiras[] = $b;
    }

    public function add_bandeira_array($b) {
        $b = explode(",", $b);
        $this->lista_bandeiras = $b;
    }

    public function juros($juros) {
        $this->juros = ($juros / 100);
    }

    public function taxa($taxa) {
        $this->taxa = $taxa;
    }

    public function get_total_real() {
        return ($this->total_real);
    }

    public function get_total_cielo() {
        $this->total_cielo = preg_replace('/([[:punct:]]|[[:alpha:]]| )/', '', $this->total_cielo);
        return $this->total_cielo;
    }

    public function num_parcelas($num_parcelas) {
        $this->num_parcelas = $num_parcelas;
    }

    public function parcelas_sem_juros($parcelas_sem_juros) {
        $this->parcelas_sem_juros = $parcelas_sem_juros;
    }

    public function desconto_avista($desconto_avista) {
        $this->desconto_avista = $desconto_avista;
    }

    public function parcelamento() {
        for ($i = 1; $i <= $this->num_parcelas; $i++) {
            $this->total_cielo = 0;
            if ($this->parcelas_sem_juros >= 1 && $this->parcelas_sem_juros >= $i) {
                $this->valor_parcela = round(($this->valor / $i), 2);
                if ($this->desconto_avista > 0 && $i == 1) {
                    $this->valor_parcela = $this->valor_parcela - ($this->valor_parcela / 100) * $this->desconto_avista;
                }
                $this->total_cielo = round(($this->valor_parcela * $i), 2);
                $this->total_cielo = ($this->total_cielo < $this->valor) ? $this->valor : $this->total_cielo;
                $this->total_cielo = ($this->total_cielo > $this->valor) ? $this->valor : $this->total_cielo;
            } else {
                $this->valor_parcela = ($this->valor * $this->juros) / (1 - (1 / pow(1 + $this->juros, $i)));
                $this->valor_parcela = round($this->valor_parcela, 2);
                $this->valor_parcela = $this->valor_parcela + $this->taxa;
                $this->total_cielo = round(($this->valor_parcela * $i), 2);
                $this->total_cielo = ($this->total_cielo < $this->valor) ? $this->valor : $this->total_cielo;
            }
            $this->total_real = $this->moeda($this->total_cielo);
            if ($this->parcelas_sem_juros >= 1 && $this->parcelas_sem_juros >= $i) {
                if ($this->desconto_avista > 0 && $i == 1) {
                   $this->total_real = $this->moeda($this->total_cielo - (($this->total_cielo / 100) * $this->desconto_avista));
                }
            } 
            $this->valor_parcela_text = $this->moeda_real($this->valor_parcela);
            $this->valor_parcela = $this->moeda($this->valor_parcela);
            //$this->lista_parcelas[] = "$i x $this->valor_parcela = $this->total_real ";
            //$this->lista_parcelas_text[] = "$i x " . $this->moeda_real($this->valor_parcela) . " = " . $this->moeda_real($this->total_real);
            $this->lista_parcelas[] = "$i x $this->valor_parcela ";
            $this->lista_parcelas_text[] = "$i x " . $this->moeda_real($this->valor_parcela);
        }
    }

    public function tabela_parcelas() {
        if ($this->parcelas_sem_juros >= 1) {
            for ($i = 0; $i <= $this->parcelas_sem_juros - 1; $i++) {
                if (isset($this->lista_parcelas[$i])) {
                    if ($this->desconto_avista > 0 && $i == 0) {
                        $this->lista_parcelas[$i] .= " - $this->desconto_avista% desc à  vista";
                    } else {
                        $this->lista_parcelas[$i] .= " - s/ Juros";
                    }
                }
            }
        }
        return implode('<br>', $this->lista_parcelas);
    }

    public function combo_parcelas() {
        $combo_list = "<select name=\"cielo_parcelas\" id=\"cielo_parcelas\" class=\"span7\" required>";
        //$combo_list .= utf8_decode("<option value=''>NÃºmero de parcelas...</option>\n");
        $combo_list .= "<option value=''>Número de parcelas...</option>\n";
        if ($this->parcelas_sem_juros >= 1) {
            for ($i = 0; $i <= $this->parcelas_sem_juros - 1; $i++) {
                
                if ($this->desconto_avista > 0 && $i == 0) {
                    $this->lista_parcelas_text[$i] .= " - $this->desconto_avista% desc a vista";
                } else {
                    $this->lista_parcelas_text[$i] .= " - s/ juros";
                }
           
                $total_parcelado = explode("x", $this->lista_parcelas[$i]);
                $total_parcelado = $this->moeda($total_parcelado[0] * $total_parcelado[1]);
                $combo_list .= "<option value='" . ($i + 1) . "' total='" . $total_parcelado . "'
			    total_real='" . $this->moeda_real($total_parcelado) . "'>" . $this->lista_parcelas_text[$i] . "</option>\n";
            }
            
            for ($i = $this->parcelas_sem_juros; $i <= count($this->lista_parcelas) - 1; $i++) {
                if ($this->desconto_avista > 0 && $i == 0) {
                    $this->lista_parcelas_text[$i] .= " - $this->desconto_avista% desc a vista";
                } else {
                    $this->lista_parcelas_text[$i] .= "";
                }
                $total_parcelado = explode("x", $this->lista_parcelas[$i]);
                $total_parcelado = $this->moeda($total_parcelado[0] * $total_parcelado[1]);
                $combo_list .= "<option value='" . ($i + 1) . "' total='" . $total_parcelado . "' total_real='" . $this->moeda_real($total_parcelado) . "'>" . $this->lista_parcelas_text[$i] . "</option>\n";
            }
        } else {
            for ($i = 0; $i <= count($this->lista_parcelas) - 1; $i++) {
                if ($this->desconto_avista > 0 && $i == 0) {
                    $this->lista_parcelas[$i] .= " - $this->desconto_avista% desc a vista";
                } else {
                    $this->lista_parcelas[$i] .= "";
                }
                $total_parcelado = explode("x", $this->lista_parcelas[$i]);
                $total_parcelado = $this->moeda($total_parcelado[0] * $total_parcelado[1]);
                $combo_list .= "<option value='" . ($i + 1) . "' total='" . $total_parcelado . "' total_real='" . $this->moeda_real($total_parcelado) . "'>" . $this->lista_parcelas_text[$i] . "</option>\n";
            }
        }
        $total_parcelado = explode("x", $this->lista_parcelas[0]);
        $total_parcelado = $this->moeda($total_parcelado[0] * $total_parcelado[1]);
        $combo_list .= "</select>";
        $combo_list .= "<input type=\"hidden\" name=\"parcela_valor\" id=\"parcela_valor\" value=\"" . $this->lista_parcelas[0] . "\" />";
        $combo_list .= "<input type=\"hidden\" name=\"total_parcelado\" id=\"total_parcelado\" value=\"" . $total_parcelado . "\" />";
        //echo $combo_list;exit;
        return $combo_list;
    }

    public function combo_bandeiras() {
        $combo_list = "<select name=\"cielo_bandeira\"  id=\"cielo_bandeira\" class=\"span7\" required>";
        $combo_list .= "<option value=''>Selecione a bandeira...</option>\n";
        for ($i = 0; $i <= count($this->lista_bandeiras) - 1; $i++) {
            $combo_list .= "<option value='" . $this->lista_bandeiras[$i] . "'>" . ucfirst($this->lista_bandeiras[$i]) . "</option>\n";
        }
        $combo_list .= "</select>";
        return $combo_list;
    }

    public function header_info() {
        $info = "<p class='theme-color'><strong>Você selecionou o método de pagamento: Cartão de Crédito</strong></p>";
        //return utf8_decode($info);
        return $info;
    }

    public function get_event_start() {
        $evt = "$('#btn-finaliza').hide();\n";
        $evt .= "$('#cielo_bandeira').on('change',function(){ \n";
        $evt .= "$('#cielo_parcelas').trigger('change'); \n";
        $evt .= "}); \n";

        $evt .= "$('#cielo_parcelas').on('change',function(){ \n";
        $evt .= "$('#parcela_valor').val(  $('#cielo_parcelas option:selected').text()  ); \n";
        $evt .= "$('#total_parcelado').val(  $('#cielo_parcelas option:selected').attr('total')  ); \n";
        $evt .= "$('.total_compra  b').html(  $('#cielo_parcelas option:selected').attr('total_real')  ); \n";
        $evt .= "if ($('#cielo_bandeira option:selected').val() != '' && $('#cielo_parcelas option:selected').val() != '' ){ \n";
        $evt .= "$('#btn-finaliza').show(); \n";
        $evt .= "}else{ $('#btn-finaliza').hide(); } \n";
        $evt .= "}); \n";
        //return utf8_decode($evt);
        return $evt;
    }

    public function _formataCielo($v) {
        return preg_replace('/,/', '.', $v);
    }

    public function instalar() {
        
    }

}

/*
echo '<h3>VISA</h3>';
$visa = new Cielo;
$visa->taxa(0);
$visa->juros(2);
$visa->valor(89.5);
$visa->num_parcelas(12);
$visa->desconto_avista(0); //10%
$visa->parcelas_sem_juros(3);
$visa->parcelamento();
echo $visa->combo_parcelas();
echo '<Br><Br><Br>';
echo '<h3>MASTER</h3>';
$master = new Cielo;
$master->taxa(0);
$master->juros(2);
$master->valor(1500);
$master->num_parcelas(10);
$master->desconto_avista(10); //10%
$master->parcelas_sem_juros(6);
$master->parcelamento();
echo $master->tabela_parcelas();
*/
