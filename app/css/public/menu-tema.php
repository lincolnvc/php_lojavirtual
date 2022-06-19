<?php header( "Content-type: text/css" ); ?>
<?php

/*
 * Para customizar o menu, acesse:  http://smarchal.com/twbscolor/?bd=3e3e3e&bh=4687ce&cd=f4f7f7&ch=dbdbdb 
 * altere as cores e copie os valores para as variaveis abaixo
 */
$bd = '3e3e3e';
$bh = '3f6ab1';
$cd = 'ecf0f1';
$ch = 'dbdbdb';

/*
$bd = '9b59b6';
$bh = '8e44ad';
$cd = 'ecf0f1';
$ch = 'ecdbff';
 */


  ( isset( $_GET['bd'] ) ) ? $bd = strip_tags( $_GET['bd'] ) : '';
  ( isset( $_GET['bh'] ) ) ? $bh = strip_tags( $_GET['bh'] ) : '';
  ( isset( $_GET['cd'] ) ) ? $cd = strip_tags( $_GET['cd'] ) : '';
  ( isset( $_GET['ch'] ) ) ? $ch = strip_tags( $_GET['ch'] ) : '';


$style = "
/* CORES SLIDER RANGE PREÇO */
  .slider-handle {background-color: #$bh !important;}
  .slider-handle {background: #$bh !important;}
  .slider-track{background-color: #$bh !important;}
  .slider-track{background: #$bh !important;}
  .slider-selection{background-color: #$ch !important;}
  .slider-selection{background: #$ch !important;}
/*FIM CORES SLIDER RANGE PREÇO */

/*CORES TOOLTIPS HOVER BOOTSTRAP*/
.tooltip-inner {
    max-width: 200px;
    padding: 3px 8px;
    color: #$ch;
    text-align: center;
    text-decoration: none;
    background-color: #$bh;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
}
.tooltip-arrow {
    position: absolute;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
}
.tooltip.top .tooltip-arrow {
    bottom: 0;
    left: 50%;
    margin-left: -5px;
    border-top-color: #$bh;
    border-width: 5px 5px 0;
}
.tooltip.right .tooltip-arrow {
    top: 50%;
    left: 0;
    margin-top: -5px;
    border-right-color: #$bh;
    border-width: 5px 5px 5px 0;
}
.tooltip.left .tooltip-arrow {
    top: 50%;
    right: 0;
    margin-top: -5px;
    border-left-color: #$bh;
    border-width: 5px 0 5px 5px;
}
.tooltip.bottom .tooltip-arrow {
    top: 0;
    left: 50%;
    margin-left: -5px;
    border-bottom-color: #$bh;
    border-width: 0 5px 5px;
}
/*FIM CORES TOOLTIPS HOVER BOOTSTRAP*/
.box-item:hover{
    -webkit-transition: all 0.4s ease-in-out;
    -moz-transition: all 0.4s ease-in-out;
    -o-transition: all 0.4s ease-in-out;
    -ms-transition: all 0.4s ease-in-out;
    transition: all 0.4s ease-in-out;
    box-shadow:0 0 0 0px #$bh inset;
    border-bottom: 2px solid #$bh !important;
}
.btn-default{
    background: #$bh !important;
    color:#$ch !important;
}
.btn-default:hover{
    background: #$bd !important;
}
.bootstrap-select.btn-group .no-results {
   background: #$bc !important;
}
.bootstrap-select.btn-group .dropdown-menu .notify {
   background: #$bc !important;
}
.bootstrap-select .selected a{
background: #$bh !important;
}

/*CSS PRELOADER CARREGAR/ORDENAR ITENS*/
#movingBallG{
    position:relative;
    width:256px;
    height:20px;
    margin:0 auto !important;
}
.movingBallLineG{
    position:absolute;
    left:0px;
    top:8px;
    height:4px;
    width:256px;
    background-color:#$cd;
}
.movingBallG{
    background-color:#$bh;
    position:absolute;
    top:0;
    left:0;
    width:20px;
    height:20px;
    -moz-border-radius:10px;
    -moz-animation-name:bounce_movingBallG;
    -moz-animation-duration:0.5s;
    -moz-animation-iteration-count:infinite;
    -moz-animation-direction:linear;
    -webkit-border-radius:10px;
    -webkit-animation-name:bounce_movingBallG;
    -webkit-animation-duration:0.5s;
    -webkit-animation-iteration-count:infinite;
    -webkit-animation-direction:linear;
    -ms-border-radius:10px;
    -ms-animation-name:bounce_movingBallG;
    -ms-animation-duration:0.5s;
    -ms-animation-iteration-count:infinite;
    -ms-animation-direction:linear;
    -o-border-radius:10px;
    -o-animation-name:bounce_movingBallG;
    -o-animation-duration:0.5s;
    -o-animation-iteration-count:infinite;
    -o-animation-direction:linear;
    border-radius:10px;
    animation-name:bounce_movingBallG;
    animation-duration:0.5s;
    animation-iteration-count:infinite;
    animation-direction:linear; 
}
@-moz-keyframes bounce_movingBallG{
    0%{left:0px;}
    50%{left:236px;}
    100%{left:0px;}
}
@-webkit-keyframes bounce_movingBallG{
    0%{left:0px;}
    50%{left:236px;}
    100%{left:0px;}
}
@-ms-keyframes bounce_movingBallG{
    0%{left:0px;}
    50%{left:236px;}
    100%{left:0px;}
}
@-o-keyframes bounce_movingBallG{
    0%{left:0px;}
    50%{left:236px;}
    100%{left:0px;}
}
@keyframes bounce_movingBallG{
    0%{left:0px;}
    50%{left:236px;}
    100%{left:0px;
    }
}
/*FIM PRELOADER*/

/*DIV TODAS A LOJA / CATEGORIAS*/
#lista-categorias{
    width: 100%;
    position: absolute;
    z-index: 600;
    margin-top: 0px;
    background-color: #fafafa;
    padding-top: 5px;
    padding-bottom: 5px;
    min-height:200px;
    border: 0px solid #000;
    border-top: 3px solid #$bh;	
    border-bottom: 2px solid #$bh;
}
#lista-categorias a{
    color:#333;
}
#lista-categorias a:hover{
    color: #$bh !important;
    text-decoration:underline;
}
#lista-categorias p b{
    color:#$bh !important;    
    font-size:12px;
}
#lista-categorias p{
    padding: 1px;
    margin: 0px;
    font-size: 12px;
    font-family: 'Open Sans';
    font-weight: normal;
}
.depto{
    min-width: 120px;
    margin-right: 10px;
    min-height: 80px;
    float:left;	
}
#top-depto{
    border-bottom: 3px solid #$bh;	
    height:54px;
    opacity:1 !important;
}
/*FIM DIV TODAS A LOJA / CATEGORIAS*/

/*DIV MENU LATERAL FILTROS */
.menu-filtro b, .menu-filtro strong {
    color: #$bh !important;
}
.menu-filtro .filtro-categoria a.active{
    font-weight: bold;
    border-bottom:1px solid #$bh !important;
}
.menu-filtro a{
    color: #$bh;
}
.menu-filtro h5{
    font-family: 'Ubuntu',Helvetica,sans-serif,Arial;
    line-height:30px;
    font-size: 17px;
}
.menu-filtro{
    font-size:13px;
    color:#333;
}
.menu-filtro .filtro-sub-categoria a{
    /*padding-left:6px !important;*/
    font-size:12px;
    color:#555;
}
.menu-filtro  .filtro-sub-categoria a:hover{
    color:#$bh;
}
.menu-filtro  .filtro-sub-categoria a.active{
    color:#$bh;
    font-style:italic;    
    border-bottom:1px dotted #$bh !important;
}
.menu-filtro #p-range-preco{
    z-index: 100 !important;
    position: relative !important;
}
/*FIM MENU LATERAL FILTROS */

/*CORES CUSTOM DA NAVBAR BOOTSTRAP*/

.dropdown-menu {background-color: #$bd;}
.dropdown-menu a { color: #$cd !important;}
.dropdown-menu a:hover {background-color: #$bh !important;}
/*.dropdown-toggle {border-right: 1px solid #000 !important;border-left: 0px solid #fff !important;}*/
.dropdown-toggle{padding-left:10px !important;padding-right:10px !important;}
.dropdown-toggle:hover {background-color: #$bh !important;}
.navbar-default {
    background-color: #$bd;
    border-color: #$bh;
    border: 0px solid #$bh;
    border-bottom: 3px solid #$bh;
}
.navbar-default .navbar-brand {
    color: #$cd;
}
.navbar-default .navbar-brand:hover, .navbar-default .navbar-brand:focus {
    color: #$ch;
}
.navbar-default .navbar-text {
    color: #$cd;
}
.navbar-default .navbar-nav > li > a {
    color: #$cd;
}
.navbar-default .navbar-nav > li > a:hover, .navbar-default .navbar-nav > li > a:focus {
   color: #$ch;
}
.navbar-default .navbar-nav > .active > a, .navbar-default .navbar-nav > .active > a:hover, .navbar-default .navbar-nav > .active > a:focus {
    color: #$ch;
    background-color: #$bh;
}
.navbar-default .navbar-nav > .open > a, .navbar-default .navbar-nav > .open > a:hover, .navbar-default .navbar-nav > .open > a:focus {
    color: #$ch;
    background-color: #$bh;
}
.navbar-default .navbar-toggle {
   border-color: #$bh;
}
.navbar-default .navbar-toggle:hover, .navbar-default .navbar-toggle:focus {
   background-color: #$bh;
}
.navbar-default .navbar-toggle .icon-bar {
   background-color: #$cd;
}
.navbar-default .navbar-collapse,
.navbar-default .navbar-form {
   border-color: #$cd;
}
.navbar-default .navbar-link {
   color: #$cd;
}
.navbar-default .navbar-link:hover {
    color: #$ch;
}
.box-item .label-important{
   background-color: #$bh !important;
   border-radius: 5px !important;
   padding:10px !important;
   font-size: 12px ;
}
@media (max-width: 767px) {
    .navbar-default .navbar-nav .open .dropdown-menu > li > a {
       color: #$cd;
    }
    .navbar-default .navbar-nav .open .dropdown-menu > li > a:hover, .navbar-default .navbar-nav .open .dropdown-menu > li > a:focus {
       color: #$ch;
    }
    .navbar-default .navbar-nav .open .dropdown-menu > .active > a, .navbar-default .navbar-nav .open .dropdown-menu > .active > a:hover, .navbar-default .navbar-nav .open .dropdown-menu > .active > a:focus {
        color: #$ch;
        background-color: #$bh;
    }
}
.navbar a{
    font-size: 13px !important;
}
.navbar .glyphicon{font-size: 15px !important;}
.navbar{
    border-radius:0px;
    -moz-border-radius:0px;
    -webkit-border-radius:0px;
}
.menu-fixed-border{
    border-bottom: 1px solid #$bh;
}
.navbar-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index:900;
    margin-top: 20px;
}
.navbar-wrapper .container {
    padding-left: 0;
    padding-right: 0;
}
.navbar-wrapper .navbar {
    padding-left: 15px;
    padding-right: 15px;
}
.navbar-content{
    padding: 15px;
    padding-bottom:0px;
    color:#$ch !important; 
}
.navbar-content:before, .navbar-content:after{
    display: table;
    content: \"\";
    line-height: 0;
}
.navbar-nav.navbar-right:last-child {
    margin-right: 15px !important;
}
.navbar-footer{
    background-color:#$ch !important;
}
.navbar-footer-content { padding:15px 15px 15px 15px; }
.dropdown-menu {
    padding: 0px;
    overflow: hidden;
}
.navbar-content *{
    text-shadow: none !important;
    -moz-text-shadow: none #fff !important;
    -moz-box-shadow:none !important;
    -webkit-box-shadow:none !important;		
    background: none;
    text-decoration: none !important; 
    color:#$ch !important; 
}
/*FIM CORES CUSTOM DA NAVBAR BOOTSTRAP*/
.box-item h3, .box-item h5{
    color: #$bh !important;
}
.transition-timer-carousel .transition-timer-carousel-progress-bar {
    height: 5px;
    background-color: #$bh;
}
";
echo preg_replace( '/\s+/', ' ', $style );
