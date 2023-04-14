<!DOCTYPE html>
<html>
    <head>
        <title>Ajuda - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>

        <?php
        require_once '../config.php';
        global $CFG;
        ?>

        <?php
        require($CFG->rpasta . "/include/includes.php");
        ?>
    </head>

    <body>
        <?php
        include ($CFG->rpasta . "/include/cabecalho.php");
        ?>
        <div id="main">
            <div id="container" class="clearfix ajuda">

                <div id="breadcrumb">
                    <h1>Você está em: <b>Ajuda</b></h1>
                </div>

                <div class="completo">
                    <div class="callout callout-info">
                        <b>Qual sua dúvida?</b> 
                        <span class="campoDesktop">Selecione um dos tópicos à esquerda, para visualizar a resposta.</span>
                        <span class="campoMobile">Selecione abaixo um tópico, para visualizar a resposta.</span>
                    </div>
                </div>

                <div class="tabbable completo m01">
                    <div class="col-xs-12 col-sm-4 col-md-4">
                        <ul class="nav nav-pills tabs-left campoDesktop">
                            <li class="active">
                                <a data-toggle="tab" href="#apresentacao">
                                    Apresentação
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#acesso">
                                    Acessar o sistema/criar conta
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#cadastro">
                                    Preencher cadastro
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#inscricao">
                                    Inscrever-se em um edital
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#minhas-inscricoes">
                                    Gerenciar inscrições correntes
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#configuracoes">
                                    Configurações/personalização
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#pagina-inicial">
                                    Sobre a página inicial
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#outros">
                                    Outros - Fale conosco
                                </a>
                            </li>
                        </ul>
                        <select id="selectAjuda" class="campoMobile seletorMobile">
                            <option value="#apresentacao">Apresentação</option>
                            <option value="#acesso">Acessar o sistema/criar conta</option>
                            <option value="#cadastro">Preencher cadastro</option>
                            <option value="#inscricao">Inscrever-se em um edital</option>
                            <option value="#minhas-inscricoes">Gerenciar inscrições correntes</option>
                            <option value="#configuracoes">Configurações/personalizações</option>
                            <option value="#pagina-inicial">Sobre a página inicial</option>
                            <option value="#outros">Outros - Fale Conosco</option>
                        </select>
                        <script type="text/javascript" class="campoMobile">
                            $(document).ready(function () {
                                $("#selectAjuda").change(function () {
                                    acessaAba(this.value);
                                });
                            });
                        </script>
                    </div>
                    <div class="col-xs-12 col-sm-8 col-md-8">
                        <div class="tab-content m02-mob">
                            <hr class="campoMobile">
                            <div id="apresentacao" class="tab-pane active">
                                <div class="completo m01">
                                    <div class="icone">
                                        <i class="fa fa-user" style="padding-top:25px;"></i>
                                    </div>
                                    <div class="texto">
                                        <h4>O que preciso para me inscrever em um edital?</h4>
                                        <p>Você deve <a onclick="javascript:acessaAba('#cadastro');">completar seu perfil</a>, antes de <a onclick="javascript:acessaAba('#inscricao');">inscrever-se em um edital</a>.</p>
                                    </div>
                                </div>
                                <div class="completo m01">
                                    <div class="icone">
                                        <i class="fa fa-file-text-o" style="padding-top:25px;"></i>
                                    </div>
                                    <div class="texto">
                                        <h4>Como acompanhar minha inscrição?</h4>
                                        <p>Você pode <a onclick="javascript:acessaAba('#minhas-inscricoes');">gerenciar suas inscrições no sistema</a> e ainda optar por <a onclick="javascript:acessaAba('#configuracoes');">receber atualizações por e-mail</a>.</p>
                                    </div>
                                </div>
                            </div>
                            <div id="acesso" class="tab-pane">
                                <h4>Acessar o sistema/criar conta</h4>
                                <p>Caso você seja um servidor ou aluno da UFES, sua credencial de acesso é a <a title='Identificação Única' id="link_login_unico" target="_blank" href='http://senha.ufes.br'>Identificação Única</a>. Os demais usuários são denominados Comunidade Externa e, caso ainda não tenham feito cadastro, podem fazê-lo <a title='Cadastrar novo usuário' target="_blank" href="<?php print $CFG->rwww ?>/cadastre-se">clicando aqui</a>.</p>
                                <p>Atenção! Se você já tiver cadastro como Comunidade Externa e vier a se tornar servidor ou aluno da UFES, seu perfil será automaticamente transferido para sua Identificação Única. Se acontecer o contrário e você perder sua I.U., seu cadastro será convertido novamente em Comunidade Externa.</p>
                                <h4 class="m02">Esqueci minha senha</h4>
                                <p>Você pode recuperá-la de acordo com seu tipo de credencial: <a target="_blank" href="https://senha.ufes.br/">Identificação Única</a> ou <a id="recSenha" target="_blank" title="Recuperar Senha" href="<?php print $CFG->rwww ?>/recuperar-senha">Comunidade Externa</a>.                                
                                </p>
                            </div>
                            <div id="cadastro" class="tab-pane">
                                <h4>Preencher cadastro</h4>
                                <?php imprimeAvisoNecessidadeLogin(); ?>
                                <div class="m02">
                                    <h4>1) Identificação</h4>
                                    <p>Para começar a utilizar o sistema, você deve preencher dados para Identificação. Isso pode ser feito clicando no menu <b>Candidato</b> acima a direita, selecionando a opção <a target="blank" title='Gerenciar suas informações de identificação' href=<?php print $CFG->rwww . "/visao/candidato/editarIdentificacao.php" ?>>Identificação</a>. </p>
                                </div>
                                <hr>
                                <div class="m01">
                                    <h4>2) Endereço</h4>
                                    <p>O próximo passo é informar-nos o seu <b>Endereço Residencial</b>.</p>
                                    <p>No meu acima, clique em <b>Candidato</b> e selecione a opção <a title='Gerenciar suas informações de endereço' target="blank" href=<?php print $CFG->rwww . "/visao/candidato/editarEndereco.php" ?>>Endereço</a>. Preencha os campos necessários e salve.</p>
                                </div>
                                <hr>
                                <div class="m01">
                                    <h4>3. Contato</h4>
                                    <p>Continue preenchendo seu perfil, agora na sessão <b>Contato</b>.</p>
                                    <p>Basta clicar novamente em <b>Candidato</b> e então na opção <a title='Gerenciar suas informações de contato' target="blank" href=<?php print $CFG->rwww . "/visao/candidato/editarContato.php" ?>>Contato</a>. É só preencher os campos e salvar.</p>
                                </div>
                                <hr>
                                <div class="m01">
                                    <h4>4. Currículo</h4>
                                    <p>Por último, preencha os dados relativos ao seu <b>Currículo</b>. </p>
                                    <p>Acesse o cadastro clicando em <b>Candidato</b> e selecionando a opção <a title='Gerenciar seu currículo' target="blank" href=<?php print $CFG->rwww . "/visao/formacao/listarFormacao.php" ?>>Currículo</a>. </p>
                                </div>
                                <hr>
                                <div class="m01">
                                    <h4>Pronto! Agora você já pode se inscrever nos editais...</h4>
                                    <p>Você cadastrou todos os itens referentes ao seu perfil no nosso sistema! Agora você já está pronto para se candidatar para qualquer edital disponível. </p>
                                </div>
                            </div>
                            <div id="inscricao" class="tab-pane">
                                <h4>Inscrever-se em um Edital</h4>
                                <?php imprimeAvisoNecessidadeLogin(); ?>
                                <div class="m01">
                                    <h4>1) Escolhendo um Edital</h4>
                                    <p>Para ver os editais disponíveis, clique no menu <b>Editais</b> acima a direita, e selecione a opção <a title='Visualizar editais' target="blank" href=<?php print $CFG->rwww . "/editais" ?>>Lista</a>.</p>
                                </div>
                                <hr>
                                <div class="m01 ajuda">
                                    <h4>2) Iniciando sua Inscrição</h4>
                                    <p>Na tela de <b>Editais</b>, escolha o de sua preferência e clique no botão <span class="fa fa-book"></span> da linha correspondente ao edital escolhido, para visualizar a página do mesmo.</p>
                                    <div class="callout callout-info m01">Você pode optar por filtrar a lista de editais, selecionando suas opções na área <b>Filtro</b>.</div>
                                    <p>Leia os dados do edital e verifique se este é o edital desejado. Tendo verificado, clique em <strong>Inscreva-se aqui</strong>, na área de Inscrição (atente-se ao período de inscrição).</p>
                                </div>
                                <hr>
                                <div class="m01">
                                    <h4>3) Preenchendo sua Inscrição</h4>
                                    <p>Cada edital tem seus dados específicos a serem preenchidos. Leia com atenção e preencha os campos.</p>
                                    <p>No fim do formulário, clique em <strong>Inscreva-me</strong> para enviar e finalizar sua inscrição.</p>
                                </div>
                            </div>
                            <div id="minhas-inscricoes" class="tab-pane">
                                <h4>Gerenciar inscrições correntes</h4>
                                <?php imprimeAvisoNecessidadeLogin(); ?>
                                <p>Os candidatos têm uma página chamada Minhas Inscrições, basta clicar no menu <b>Editais</b>, acima a direita, e selecionar a opção <a title='Visualizar editais que eu me inscrevi' href=<?php print $CFG->rwww . "/visao/inscricaoProcesso/listarInscProcessoUsuario.php" ?>>Minhas Inscrições</a>.</p>
                                <p>Nesta página, você também pode ver mais detalhes do edital em que está inscrito apenas clicando em <span class="fa fa-eye"></span>.</p>
                            </div>
                            <div id="configuracoes" class="tab-pane">
                                <h4>Configurações/personalização</h4>
                                <?php imprimeAvisoNecessidadeLogin(); ?>
                                <p>Você pode gerenciar algumas preferências do sistema na página Configurações. Para acessá-la, basta clicar no menu <b>Sistema</b> e escolher a opção <a target="blank" href=<?php print $CFG->rwww . "/visao/usuario/manterConfiguracao.php" ?>>Configurações</a>. Atualmente os seguintes itens são ajustáveis:</p>
                                <ul>
                                    <li>Resultados por página</li>
                                    <li>Atualizações de editais por email</li>
                                    <li>Salvar filtro</li>
                                </ul>
                                <p>Na página haverá um ícone <i class="fa fa-question-circle"></i>, que explica cada opção. Para ler, basta passar o mouse sobre o ícone.</p>
                            </div>
                            <div id="pagina-inicial" class="tab-pane">
                                <h4>Sobre a página inicial</h4>
                                <?php imprimeAvisoNecessidadeLogin(); ?>
                                <p>A página inicial foi pensada para ser um painel com todas as informações pertinentes para você.</p>
                                <p>Nela, notificamos se o seu perfil está apto ou não para se inscrever em editais, além de apresentar de forma rápida a lista de inscrições em andamento, os últimos editais que você visitou e os últimos editais publicados no sistema. Apresentamos também uma sessão de Notícias, com atualizações dos editais. Qualquer dúvida ou sugestão, envie-nos sua opinião <i class="fa fa-smile-o"></i>.</p>
                            </div>
                            <div id="outros" class="tab-pane">
                                <h4>Outros - Fale conosco</h4>
                                <p>Não encontrou sua dúvida em nenhum dos tópicos ao lado? Entre em contato conosco:</p>
                                <p>Recepção: (27) 4009-2208</p>
                                <p>Suporte: (27) 4009-2061</p>
                                <p><a href="<?php print $CFG->rwww . "/contato" ?>">Formulário de contato <i class="fa fa-external-link"></i></a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include ($CFG->rpasta . "/include/rodape.php");
        carregaCSS("bootstrap.vertical-tabs");
        carregaScript('metodos-adicionaisBR');
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            // Javascript to enable link to tab
            var url = document.location.toString();
            if (url.match('#')) {
                $('.nav-pills a[href=#' + url.split('#')[1] + ']').tab('show');
            }

            // Change hash for page-reload
            $('.nav-pills a').on('shown.bs.tab', function (e) {
                window.location.hash = e.target.hash;
            })

            function acessaAba(nome) {
                $('.nav-pills a[href=' + nome + ']').tab('show');
            }
        });
    </script>
</html>

<?php

function imprimeAvisoNecessidadeLogin() {
    if (!estaLogado()) {
        ?>
        <div class="callout callout-warning">
            Atenção, você deve estar logado no Sistema para seguir o roteiro abaixo.
        </div>
        <?php
    }
}
?>

