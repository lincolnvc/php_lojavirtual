var baseUri = $('base').attr('href').replace('/app/', '');
$(function () {
    //$(window).load(function () {
        //carrega novos produtos (ultimos cadastrados)
        var url = baseUri + '/index/FillMaisNovosVistos/2/';
        $.getJSON(url, function (data) {
            $('#mais-novos .slides').html('');
            $(data).each(function (k, v) {
                var link = ''
                link += '<div class="span2">';
                link += ' <div class="box-item tips-top" title="ver detalhes">';

                link += '   <a href="' + baseUri + '/produto/' + v.categoria_url + '/' + v.sub_url + '/' + v.item_url + '/' + v.item_id + '/">';
                
                link += '     <div class="box-item-foto">'
                link += '       <img src="' + baseUri + '/app/thumber.php?q=80&zc=2&w=140&h=140&src=fotos/' + v.foto_url + '" style="width:140px !important" width="140" height="140"  class="img-responsive"/>';
                link += '     </div>'

                link += '     <div class="box-item-detalhe">'
                link += '      <h2>' + v.item_short_title + '</h2>'
                                if (v.item_valor_original) {
                                link += '<h4>De R$ ' + v.item_valor_original + '</h4>';
                                }                
                link += '      <h3>' + v.item_preco + '</h3>'
                link += '     </div>'

                link += '</a>'
                link += '</div>';
                link += '</div>';

                $('<li />')
                        .attr('id', v.item_id)
                        .html(link)
                        .appendTo($('#mais-novos .slides'));
            });
            //inicializa o slider
            $('#mais-novos').flexslider({
                animation: "slide",
                animationLoop: true,
                itemWidth: 210,
                      controlNav: false
                //itemMargin: 2                 
            });
        })

        //carrega banner/slide lateral 270x220
        var url = baseUri + '/index/FillBanner/2';
        $.getJSON(url, function (data) {
            $('#banner-left-300 .slides').html('');
            $(data).each(function (k, v) {
                var img = '<img src="' + baseUri + '/app/thumber.php?q=90&zc=2&w=270&h=220&src=fotos/slide/' + v.slide_url + '" width="270" height="220" />';
                if (v.slide_link != 0) {
                    var link = '<a href="' + v.slide_link + '">';
                } else {
                    var link = '<a>';
                }
                link += img;
                link += '</a>';
                $('<li />')
                        .addClass('b-gray')
                        .attr('id', v.slide_id)
                        .html(link)
                        .appendTo($('#banner-left-300 .slides'));
            });
            $('#banner-left-300').flexslider({
                animation: "slide",
                animationLoop: true,
                itemWidth: 270,
                itemMargin: 4
            });
        })

        //carrega banner/slide lateral 270x600
        var url = baseUri + '/index/FillBanner/3';
        $.getJSON(url, function (data) {
            $('#banner-left-600 .slides').html('');
            $(data).each(function (k, v) {
                var img = '<img src="' + baseUri + '/app/thumber.php?q=90&zc=2&w=270&h=600&src=fotos/slide/' + v.slide_url + '" width="270" height="600"/>';
                if (v.slide_link != 0) {
                    var link = '<a href="' + v.slide_link + '">';
                } else {
                    var link = '<a>';
                }
                link += img;
                link += '</a>';
                $('<li />')
                        .addClass('b-gray')
                        .attr('id', v.slide_id)
                        .html(link)
                        .appendTo($('#banner-left-600 .slides'));
            });
            //carrega banners bottom
            $('#banner-left-600').flexslider({
                animation: "fade",
                animationLoop: true,
                itemWidth: 270,
                itemMargin: 4,
                controlNav: false
            });
        })
    //});
    //button remove item
    $('.btn-cart-remove-home').live('click', function () {
        var id = $(this).attr('id');
        var url = baseUri + '/carrinho/remove/' + id + '/no-redirect/';
        $.post(url, {}, function (data) {
            $('#tr_' + id).fadeOut(600, function () {
                $('#tr_' + id).remove();
            });
            if (data == '' || data <= 0) {
                $('#left-cart-total').html('<b>O carrinho está vazio! ;(</b>');
                $('.cart-view').delay(500).fadeOut(700);
                $('#cart-left').delay(700).fadeOut(500);
            } else {
                $('#total_compra').html('Total R$ ' + data);
            }
        })
    })
});  