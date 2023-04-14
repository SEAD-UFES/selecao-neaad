/* 
 * Scripts para filtros
 */


function filtro_BtMaisMenos() {
    // mais filtros e menos filtros
    $("#maisFiltros").click(function () {
        $("#maisFiltros").hide();
        $("#menosFiltros").show();
        $("#filtroInterno").show();
    });
    $("#menosFiltros").click(function () {
        $("#maisFiltros").show();
        $("#menosFiltros").hide();
        $("#filtroInterno").hide();
    });
}

/**
 * 
 * @param {string} nmCookie
 * @returns {void} Executa os scripts para filtro de processo
 */
function filtroProcesso(nmCookie) {
    filtro_BtMaisMenos();

    // gerando gatilhos para curso e formacao
    gatilhoCurso = function () {
        if ($("#idCurso").data("naoedicao") !== true) {
            if ($("#idCurso").val() == "")
            {
                //habilita formacao
                $("#tpFormacao").attr("disabled", false);
            } else {
                // desabilita formação
                $("#tpFormacao").val("");
                $("#tpFormacao").attr("disabled", true);
            }
        }
    };
    $("#idCurso").change(gatilhoCurso);
    gatilhoCurso();


    gatilhoFormacao = function () {
        if ($("#idCurso").data("naoedicao") !== true) {
            if ($("#tpFormacao").val() == "")
            {
                //habilita curso
                $("#idCurso").attr("disabled", false);
            } else {
                // desabilita curso
                $("#idCurso").val("");
                $("#idCurso").attr("disabled", true);
            }
        }
    };
    $("#tpFormacao").change(gatilhoFormacao);
    gatilhoFormacao();


    $("#limpar").click(function () {
        // destroi cookie
        $.removeCookie(nmCookie);

        limparFormulario($("#formBuscaProcesso"));
        gatilhoCurso();
        gatilhoFormacao();
    });

    //adicionando máscaras
    $("#nrEdital").mask("9?99", {placeholder: ""});

    $("#formBuscaProcesso").validate({
        submitHandler: function (form) {
            //evitar repetiçao do botao
            mostrarMensagemInline();
            form.submit();
        }, rules: {
            nrEdital: {
                digits: true
            }
        }
    });
}