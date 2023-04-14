<div class="dados-usuario pull-right">
    <?php
    global $CFG;
    require_once ($CFG->rpasta . "/util/sessao.php");
    require_once ($CFG->rpasta . "/negocio/NGUtil.php");
    //caso do usuário estar logado
    if (estaLogado() != null) {
        //recuperando dados do login
        $vetDados = getDadosLogin();
        ?>
        <span class="usuTitulo texto-com-sombra"><?php print mensagemBoasVindas(); ?>, </span>
        <span class='usuDescricao texto-com-sombra'><?php print $vetDados['dsNome']; ?></span>
        <span class="usuTitulo texto-com-sombra"> Último acesso: </span>
        <span class='usuDescricao texto-com-sombra'><?php print $vetDados['dtUltLogin']; ?></span>
    <?php } else {
        ?>
        <span class="usuTitulo texto-com-sombra"><?php print mensagemBoasVindas(); ?>,</span>
        <span class='usuDescricao texto-com-sombra'>Visitante.</span>
        <span class="usuTitulo texto-com-sombra"></span>
    <?php }
    ?>
</div>