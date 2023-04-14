/* 
 * Trata as requisiçoes AJAX em geral do lado do cliente
 */

/**
 * Funçao que realiza a carga de um select em uma pagina qualquer. 
 * @param {string} divEspera - Id da div que contem o texto de espera
 * @param {string} divCarga - Id da div que contem o select a ser carregado
 * @param {string} idSelect - Id do select a ser carregado
 * @param {string} idSelecionado - Id da opçao selecionada, se houver
 * @param {array} params - Parametros da requisiçao na forma {id: valor}
 * @param {string} displayDivSel - Forma de exibiçao da Div do select. Por padrao e block
 * @param {callback function} funcaoPosCarga - Funçao a ser chamada apos o carregamento do select
 * @param {boolean} mostraVazio - Diz se e para exibir o select mesmo se ele estiver vazio. Padrão: FALSE
 * @returns {void}
 */
function carregaSelect(divEspera, divCarga, idSelect, idSelecionado, params, displayDivSel, funcaoPosCarga, mostraVazio)
{
    // ocultando div de carga
    $("#" + divCarga).hide();

    // mostrando div de espera
    $("#" + divEspera).show();

    $.ajax({
        type: "POST",
        url: getURLServidor() + "/controle/CTAjax.php",
        data: params,
        dataType: "json",
        success: function(json) {
            // incluindo "selecione"
            var options = '<option value=' + getIdSelectSelecione() + '>' + getDsSelectSelecione() + '</option>';

            // carregando demais elementos
            $.each(json, function(key, value) {
                if (key == idSelecionado)
                {
                    options += '<option value="' + key + '" selected>' + value + '</option>';
                } else {
                    options += '<option value="' + key + '">' + value + '</option>';
                }
            });

            $("#" + idSelect).html(options);

            // ocultando div de espera
            $("#" + divEspera).hide();

            // mostrando div de carga
            var qt = $("#" + idSelect + " option").length;
            if (qt > 1 || mostraVazio)
            {
                $("#" + divCarga).css("display", displayDivSel);
            }

            //executando funçao pos carga
            if (typeof (funcaoPosCarga) !== 'undefined' && funcaoPosCarga !== null && funcaoPosCarga !== "") {
                funcaoPosCarga();
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nPor favor, recarregue a página.\n\n";
            msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

            // ocultando divs
            $("#" + divEspera).hide();
            $("#" + divCarga).hide();

            // exibindo mensagem
            alert(msg);
        }
    });
}

/**
 * @param {string} idSelectGatilho - Id do select no qual sera inserido o gatilho
 * @param {string} valInvalidoGatilho - Valor para o qual o gatilho nao deve ser disparado
 * @param {string} divEspera - Id da div que contem o texto de espera
 * @param {string} divCarga - Id da div que contem o select a ser carregado
 * @param {string} idSelect - Id do select a ser carregado
 * @param {string} idSelecionado - Id da opçao selecionada, se houver
 * @param {callback function} params - Funçao de callback para obter os parametros da requisiçao na forma {id: valor}
 * @param {string} displayDivSel - Forma de exibiçao da Div do select. Por padrao e block
 * @param {boolean} addGatilho - Diz se e para adicionar gatilho no select. Padrao: TRUE
 * @returns {function pointer} - Ponteiro para a funçao gatilho criada
 */
function adicionaGatilhoAjaxSelect(idSelectGatilho, valInvalidoGatilho, divEspera, divCarga, idSelect, idSelecionado, params, displayDivSel, addGatilho) {
    // definindo padrao de exibiçao de div
    displayDivSel = typeof displayDivSel !== 'undefined' ? displayDivSel : "block";

    // caso do carregamento da pagina
    if (!vazioOuNulo($("#" + idSelectGatilho).val()) && ($("#" + idSelectGatilho).val() != valInvalidoGatilho))
    {
        carregaSelect(divEspera, divCarga, idSelect, idSelecionado, params(), displayDivSel);
    }

    gatilho = function(idSel) {
        if (typeof (idSel) === 'undefined') {
            idSel = idSelecionado;
        }
        if (!vazioOuNulo($("#" + idSelectGatilho).val()) && ($("#" + idSelectGatilho).val() != valInvalidoGatilho))
        {
            carregaSelect(divEspera, divCarga, idSelect, idSel, params(), displayDivSel);
        } else {
            // limpa select e oculta divs
            $("#" + idSelect).empty();

            //oculta divs
            $("#" + divEspera).hide();
            $("#" + divCarga).hide();
        }
    };

    // adiçao do gatilho
    addGatilho = typeof addGatilho !== 'undefined' ? addGatilho : true;
    if (addGatilho)
    {
        $("#" + idSelectGatilho).change(gatilho);
    }

    return gatilho;
}

/**
 * Essa função diferencia da funcao adicionaGatilhoAjaxSelect no seguinte aspecto: 
 * Ela permite a execução de uma função pós carga, após o processamento do select. Isso é importante quando há vários selects filhos
 * a serem processados após a mudança de um select.
 * 
 * @param {string} idSelectGatilho - Id do select no qual sera inserido o gatilho
 * @param {string} valInvalidoGatilho - Valor para o qual o gatilho nao deve ser disparado
 * @param {string} divEspera - Id da div que contem o texto de espera
 * @param {string} divCarga - Id da div que contem o select a ser carregado
 * @param {string} idSelect - Id do select a ser carregado
 * @param {string} idSelecionado - Id da opçao selecionada, se houver
 * @param {callback function} params - Funçao de callback para obter os parametros da requisiçao na forma {id: valor}
 * @param {callback function} funcaoPosCarga - Funçao a ser chamada apos o carregamento do select
 * @param {boolean} mostraVazio - Diz se e para exibir o select mesmo se ele estiver vazio. Padrao: true
 * @param {string} displayDivSel - Forma de exibiçao da Div do select. Por padrao é inline
 * @returns {function pointer} - Ponteiro para a funçao gatilho criada
 */
function adicionaGatilhoAjaxSelectIn(idSelectGatilho, valInvalidoGatilho, divEspera, divCarga, idSelect, idSelecionado, params, funcaoPosCarga, mostraVazio, displayDivSel) {
    // definindo padrao de mostrar vazio
    mostraVazio = typeof mostraVazio !== 'undefined' ? mostraVazio : true;

    // definindo padrao de exibiçao de div
    displayDivSel = typeof displayDivSel !== 'undefined' ? displayDivSel : "inline";


    // caso do carregamento da pagina
    if (!vazioOuNulo($("#" + idSelectGatilho).val()) && ($("#" + idSelectGatilho).val() != valInvalidoGatilho))
    {
        carregaSelect(divEspera, divCarga, idSelect, idSelecionado, params(), displayDivSel, funcaoPosCarga, mostraVazio);
    }

    gatilho = function(idSel) {
        if (typeof (idSel) === 'undefined') {
            idSel = idSelecionado;
        }
        if (!vazioOuNulo($("#" + idSelectGatilho).val()) && ($("#" + idSelectGatilho).val() != valInvalidoGatilho))
        {
            carregaSelect(divEspera, divCarga, idSelect, idSel, params(), displayDivSel, funcaoPosCarga, mostraVazio);
        } else {
            // limpa select e oculta divs
            $("#" + idSelect).empty();

            //oculta divs
            $("#" + divEspera).hide();
            $("#" + divCarga).hide();

            //executando funçao pos carga
            if (typeof (funcaoPosCarga) !== 'undefined' && funcaoPosCarga !== null && funcaoPosCarga !== "") {
                funcaoPosCarga();
            }
        }
    };

    // adiçao do gatilho
    $("#" + idSelectGatilho).change(gatilho);

    return gatilho;
}
