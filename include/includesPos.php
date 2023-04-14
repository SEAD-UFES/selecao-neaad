<?php
require_once dirname(__FILE__) . "/../config.php";
global $CFG;

// requisitando include padrão
require_once $CFG->rpasta . "/include/includes.php";
?>

<?php
// carregando css padrão
foreach (array('font-awesome') as $arq) {
    carregaCSS($arq);
}

// carregando script padrão
foreach (array('bootstrap', 'jquery.validate', 'localization/messages_pt_BR', 'bootstrap-hover-dropdown', 'util', 'jquery.toastmessage') as $arq) {
    carregaScript($arq);
}
?>

<script type="text/javascript">
    function getIdSelectOutro() {
        return <?php print ID_SELECT_OUTRO; ?>;
    }
    function getIdSelectSelecione() {
        return '<?php print ID_SELECT_SELECIONE; ?>';
    }
    function getDsSelectSelecione() {
        return '<?php print DS_SELECT_SELECIONE; ?>';
    }
    function getIdUsuarioLogado() {
        return '<?php print getIdUsuarioLogado(); ?>';
    }
    function getURLServidor() {
        return  '<?php print $CFG->rwww ?>';
    }
</script>