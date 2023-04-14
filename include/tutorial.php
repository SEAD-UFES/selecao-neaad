<?php
global $CFG;
require_once $CFG->rpasta . "/controle/CTCandidato.php";

if (estaLogado(Usuario::$USUARIO_CANDIDATO)) {
    $urlAtual = $_SERVER['REQUEST_URI'];
    ?>
    <?php if (!preencheuIdentificacaoCT(getIdUsuarioLogado())) { ?>
        <div class="progress">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 5%">
                <span class="sr-only">5% completo</span>
            </div>
        </div>
        <div class="callout callout-success">
            <strong>Bem vindo!</strong> Você ainda não preencheu todo seu perfil. Para utilizar o sistema, você deve fornecer primeiramente seus dados de <a href="<?php print $CFG->rwww . "/visao/candidato/editarIdentificacao.php" ?>" class="callout-link">Identificação</a>.
        </div>
    <?php } elseif (!preencheuEnderecoCT(getIdUsuarioLogado())) { ?>
        <div class="progress">
            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                <span class="sr-only">40% completo</span>
            </div>
        </div>
        <div class="callout callout-warning">
            <strong>Continue preenchendo seu perfil.</strong> Nós precisamos saber também seu Endereço Residencial. Preencha o seu <a href="<?php print $CFG->rwww . "/visao/candidato/editarEndereco.php" ?>" class="callout-link">Endereço</a>.
        </div>
    <?php } elseif (!preencheuContatoCT(getIdUsuarioLogado())) { ?>
        <div class="progress">
            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                <span class="sr-only">60% completo</span>
            </div>
        </div>
        <div class="callout callout-warning">
            <strong>Queremos falar com você!</strong> É muito importante que você nos informe seu contato. Preencha os dados de <a href="<?php print $CFG->rwww . "/visao/candidato/editarContato.php" ?>" class="callout-link">Contato</a> corretamente.
        </div>
    <?php } elseif (!preencheuFormacaoCT(getIdUsuarioLogado())) { ?>
        <div class="progress">
            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                <span class="sr-only">80% completo</span>
            </div>
        </div>
        <div class="callout callout-warning">
            <strong>Está quase acabando!</strong> Para concluir seu cadastro, preencha o seu <a href="<?php print $CFG->rwww . "/visao/formacao/listarFormacao.php" ?>" class="callout-link">Currículo</a>.
        </div>
        <?php
        // inatividade: Apenas para página de início
    } elseif (sessao_isMostrarInatividade() && strpos($urlAtual, "inicio") !== FALSE) {
        // Aviso de inatividade
        ?>
        <div class="progress">
            <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
                <span class="sr-only">100% completo</span>
            </div>
        </div>
        <div class="callout callout-warning">
            <strong>Revise seus dados.</strong> Já faz algum tempo que você não atualiza seu perfil. <a href="<?php echo "$CFG->rwww/visao/candidato/editarIdentificacao.php?" . Candidato::$PARAM_PREENC_REVISAO; ?>" title="Verifique se seus dados estão atualizados...">Clique aqui para verificar se está tudo certo</a>.
        </div>
        <?php
    } else {
        // verificando se tem edital salvo na sessão
        $editalSessao = sessaoDados_getDados("idProcessoInscricao");
        if ($editalSessao != NULL) {
            // recupera descrição
            $dsEditalSessao = sessaoDados_getDados("dsProcessoInscricao");

            $htmlEditalSessao = "<a href='$CFG->rwww/visao/inscricaoProcesso/criarInscProcesso.php?idProcesso=$editalSessao'><strong>$dsEditalSessao</strong></a>";
        } else {
            $htmlEditalSessao = "";
        }


        // Perfil completo: Apenas para a página principal
        if (strpos($urlAtual, "inicio") !== FALSE) {
            ?>
            <div class="callout callout-success">
                <strong>Perfil completo!</strong> Você está habilitado a se inscrever nos editais. Caso tenha dúvidas, <a target="_blank" title="Ir para a ajuda" href="<?php echo "$CFG->rwww/ajuda#inscricao" ?>">clique aqui</a>.
                <?php if (!Util::vazioNulo($htmlEditalSessao)) { ?>
                    <br/>
                    <br/>
                    Retornar ao <?php print $htmlEditalSessao; ?>
                <?php } ?>
            </div>
            <?php
        } elseif (!Util::vazioNulo($htmlEditalSessao)) {
            ?>
            <div class="callout callout-success">
                Continue o <strong>preenchimento do seu currículo</strong>. Quando você terminar, retorne à página de inscrição do <?php print $htmlEditalSessao; ?>.<br/>Não tenha pressa, pois você não poderá alterar seu currículo enquanto estiver inscrito em um processo seletivo.
            </div>
            <?php
        }
    }
}
?>