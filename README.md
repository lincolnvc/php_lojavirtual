# php_lojavirtual
Ecommerce Loja Virtual em PHP

Requisitos para o funcionamento do script:
Não tem como garantir o funcionamento fora desses requisitos abaixo: 
• Servidor Linux com cPanel da (cpanel.net) 
• PHP 5.3 a 5.4 
• MySQL 
• Apache 
• Habilite o short_tags no php.ini (local) 
• Habilite o mod_rewrite e .htaccess no servidor(local) 

Configuração do banco de dados
- Crie o banco de dados no MySQL e configure com os dados abaixo que aparecem em negrito: 
- abrir o arquivo app/database/database.conf.php 

Alterar os dados conforme seu servidor 
 'default' => array 
 ( 
 'driver'=>'mysql', 
 'host'=>'localhost', 
 'port'=>3306, 
 'dbname'=>'usuarioCpanel_nomeDoBanco', 
 'user'=>'usuariocpanel_usuarioBanco', 
 'password'=>'senhaBanco', 
 'emailAdmin' => 'seuEmailParaNotificacoes@site.com' 
 ) 

Importar a base de dados que está dentro da pasta /INSTALACAO 
- Importe pelo phpmyadmin a base de dados Banco-de-Dados.sql que está dentro da pasta 
/INSTALACAO para o banco que criou pelo MySQL. 

Estrutura de diretórios
- app/ *diretório raiz da aplicação 
- app/admin * arquivos da área admin 
- app/views/public * arquivos HTML da área do usuário 
- app/views/admin * arquivos HTML da área admin 
- app/css/admin * css da área admin 
- app/css/public * css da área do usuário 
- app/images * imagens do sistema 
- app/js/admin * javascripts da área admin 
- app/js/public * javascript da área do usuário 
- app/userfiles * diretório onde são enviados arquivos via upload 
- app/class * classes do framework

Área Administrativa: 
Acesse www.seusite.com.br/admin ou www.seusite.com.br/PASTA/admin 
Usuário padrão: admin 
Senha padrão: admin 

Altere a senha após acessar o painel do sistema! 
O sistema trabalha com URL amigáveis o que significa que não é possivel ter links como: 
site.com.br/teste.html 

Para funcionar você precisa alterar o .htaccess contido na raiz do sistema 
exemplo: 
#diretorios extras 
RewriteRule ^exemplo - [L,NC] 
RewriteRule ^teste - [L,NC] 
Nesse caso acima você está configurando um diretório novo na raiz do sistema, ficaria assim: 
site.com.br/exemplo/meuarquivo.html 

Ou seja, os links referenciados devem estar dentro de um diretório para funcionar, 
Nenhum link .html ou .php dentro da pasta /app pode ser feito, você deve 
“linkar” para uma pasta na raiz do sistema, ficaria assim 
/exemplo/ 
 - dentro do diretório exemplo você coloca seu arquivo, imagens, css, etc... 

No pacote enviado tem um exemplo na pasta teste que poderá ser acessado 
exemplo de acesso na localhost 
//localhost/pastaDoSistema/teste/index.html 
Os uploads ficam por padrão no diretório /app/fotos/ e /app/banners/ 
É necessário dar permissão de gravação neste diretório (chmod 777) 

Nunca altere o arquivo index.php da raiz, qualquer alteração implica em erro no sistema. 
Configuração do Painel Admin
Opções da Pamento: 

Obtenha a API KEY no pagseguro (no site pagseguro, acesse sua conta e siga as instruções abaixo) 
- Configure o retorno automático no pagseguro (no site do pagseguro, acesse sua conta e siga as 
instruções abaixo). 

No Painel Admin da loja, vá até o menu “Opções de Pagamento -> Configurar Opções de 
Pagamento”. Em seguida informe o E-mail e API KEY do pagseguro, clique em “Atualizar Dados”. 

Opções de Frete da Loja
- No Painel Admin vá até o menu “Opções de Frete -> Configurar Opções de Frete”, marque as 
opções que deseja oferecer (SEDEX, PAC, SEDEX 10); 
Configuração do Envio de E-mail
No Painel Admin vá até o menu “Configurações -> Configuração de e-mail”. 
Preencha os campos com os dados solicitados (se não tiver os dados solicite ao seu host / 
hospedagem). 

Após informar todos os dados, clique em “testar configurações” para se certificar de que o sistema 
está enviando e-mails normalmente. 
Caso ocorra ou seja exibida alguma mensagem de erro, confirme junto ao seu host os dados 
necessários (endereço smtp, porta, etc...)
