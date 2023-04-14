<!DOCTYPE html>
<html>
    <head>     
        <title>Consultar Edital - Seleção EAD</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="Edital de seleção para cursos EAD - SEAD/UFES.">

        <?php
        require_once '../../config.php';
        global $CFG;
        ?>

        <?php
        // carregando arquivos
        require_once ($CFG->rpasta . "/controle/CTProcesso.php");
        require_once ($CFG->rpasta . "/controle/CTManutencaoProcesso.php");
        require_once ($CFG->rpasta . "/negocio/TipoCargo.php");

        //verificando passagem por get
        if (!isset($_GET['idProcesso']) && (!isset($_GET['nmCurso']) && !isset($_GET['nmTipoCargo']) && !isset($_GET['id']))) {
            new Mensagem("Chamada incorreta.", Mensagem::$MENSAGEM_ERRO);
            return;
        }

        // definindo id do processo
        $idProcesso = isset($_GET['idProcesso']) ? $_GET['idProcesso'] : buscarIdProcessoPorUrlAmigavelCT($_GET['nmCurso'], $_GET['nmTipoCargo'], $_GET['id']);

        if ($idProcesso == NULL) {
            // URL amigável errada
            $novaUrl = str_replace($_GET['id'], "", $_SERVER['REQUEST_URI']);
            $nmServidor = $_SERVER['SERVER_NAME'];
            header("Location: http://$nmServidor{$novaUrl}err");
            return;
        }

        //verificando permissão e recuperando dados para processamento
        $processo = buscarProcessoComPermissaoCT($idProcesso, TRUE, TRUE);
        $chamada = buscarChamadaPorIdCT($processo->PCH_ID_ULT_CHAMADA);

        // salvando rastreio
        RAT_criarRastreioEditalCT(getIdUsuarioLogado(), $processo->getPRC_ID_PROCESSO());
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
            <div id="container" class="clearfix">

                <div id="breadcrumb">
                    <h1><a href="<?php print $CFG->rwww ?>/editais">Editais</a> > <strong>Edital <?php print $processo->getNumeracaoEdital(); ?> | <?php print $processo->TIC_NM_TIPO_CARGO; ?> | <?php print $processo->CUR_NM_CURSO; ?></strong></h1>
                </div>

                <?php include ($CFG->rpasta . "/visao/processo/fragmentoConsultaProcesso.php"); ?>

                <div class="col-full m04 pull-left">
                    <input type="button" class="btn btn-default" onclick="javascript: window.location = '<?php print "$CFG->rwww/editais"; ?>';" value="Ver outros editais">
                </div>
            </div>
        </div> 
    </div>
    <?php include ($CFG->rpasta . "/include/rodape.php"); ?>
</body>
</html>

