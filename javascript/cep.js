/* 
 * Busca CEP via AJAX
 */


/**
 * Funçao que realiza a busca de um CEP no servidor
 * 
 * E esperado os seguintes id's, acrescidos de <compCampos>:
 * <nrCep> - Campo de Cep
 * <nmLogradouro> - Logradouro
 * <nrNumero> - Numero
 * <nmBairro> - Bairro
 * <idEstado> - Estado
 * <idCidade> - Cidade
 * <dsComplemento> - Complemento do endereço
 * <cepAnterior> - Input do tipo hidden para o cep anterior: Processamento interno
 * <erroAnterior> - Input do tipo hidden para processamento interno
 * <foco> - Input do tipo hidden para processamento interno
 * 
 * Div de erro para CEP invalido deve ser: <nrCep> + <compCampos> + "Erro"
 *   
 * @param {string} divEspera - Id da div que contem o texto de espera
 * @param {string} divCarga - Id da div que contem os campos de endereço a ser carregado
 * @param {string} compCampos - Complemento de diferenciaçao dos campos de endereço
 * @param {callback function} gatilhoCidade - Funçao que trata o caso da cidade, carregando o select conforme o estado
 * @param {callback function} gatilhoComplemento - Funçao que trata a contagem de caracteres do complemento
 * @returns {void}
 */
function buscarCEP(divEspera, divCarga, compCampos, gatilhoCidade, gatilhoComplemento)
{
    //verificando dupla chamada
    if (isChamadaRepetida(compCampos))
    {
        if (!vazioOuNulo($("#erroAnterior" + compCampos).val()))
        {
            // mostrando div de erro
            $("#nrCep" + compCampos + "Erro").show();
        } else {
            // mostra div de resultado
            if ($("#" + divCarga).is(':hidden'))
            {
                $("#" + divCarga).show();
                $($("#foco" + compCampos).val()).focus();
            }
        }
        return;
    }

    //ocultar div de erro e apagando erro anterior
    $("#nrCep" + compCampos + "Erro").hide();
    $("#erroAnterior" + compCampos).val("");

    // ocultando div de carga
    $("#" + divCarga).hide();

    // mostrando div de espera
    $("#" + divEspera).show();

    $.ajax({
        type: "POST",
        url: getURLServidor() + "/controle/CTAjax.php",
        data: {"buscacep": $("#nrCep" + compCampos).val().replace(/\.|-|_/g, '')},
        dataType: "json",
        success: function (json) {
            if (json != null && json['resultado'] == 1)
            {
                //cep encontrado. carregando dados nos campos
                $("#idEstado" + compCampos).val(unescape(json['uf']));
                gatilhoCidade(unescape(json['id_cidade']));

                if (!vazioOuNulo(json['logradouro'])) {
                    var logradouro = unescape(json['tp_logradouro']) + " " + unescape(json['logradouro']);
                    // separando numero, caso exista
                    var sep = separaNumero(logradouro);
                    $("#nmLogradouro" + compCampos).val(sep[0]);
                    $("#nmBairro" + compCampos).val(unescape(json['bairro']));

                    // incluindo ou nao o numero
                    if (vazioOuNulo(sep[1]))
                    {
                        $("#dsComplemento" + compCampos).val("");
                        $("#foco" + compCampos).val("#nrNumero" + compCampos);
                        $("#nrNumero" + compCampos).val("").focus();

                    } else {
                        // inserindo numero e focando o complemento
                        $("#foco" + compCampos).val("#dsComplemento" + compCampos);
                        $("#nrNumero" + compCampos).val(sep[1]);
                        $("#dsComplemento" + compCampos).val("").focus();

                    }
                } else {
                    $("#foco" + compCampos).val("#nmLogradouro" + compCampos);
                    $("#nrNumero" + compCampos).val("");
                    $("#nmBairro" + compCampos).val("");
                    $("#dsComplemento" + compCampos).val("");
                    $("#nmLogradouro" + compCampos).val("").focus();
                }

                // executando gatilho complemento
                gatilhoComplemento();

                // ocultando div de espera
                $("#" + divEspera).hide();

                // mostrando div de carga
                $("#" + divCarga).show();

            } else {
                // cep nao encontrado.

                // ocultando div de espera
                $("#" + divEspera).hide();

                // ocultando div de carga
                $("#" + divCarga).hide();

                //informando erro na div de erro
                $("#nrCep" + compCampos + "Erro").html("CEP inválido");
                $("#nrCep" + compCampos + "Erro").show();

                // informando que houve erro no hidden
                $("#erroAnterior" + compCampos).val("true");
            }


        },
        error: function (xhr, ajaxOptions, thrownError) {
            var msg = "Desculpe, ocorreu um erro ao tentar uma requisição ao servidor.\nPor favor, recarregue a página.\n\n";
            msg += "Detalhes do erro: " + xhr.status + " - " + thrownError;

            // ocultando divs
            $("#nrCep" + compCampos + "Erro").hide();
            $("#" + divEspera).hide();
            $("#" + divCarga).hide();

            // exibindo mensagem
            alert(msg);
        }
    });
}

/**
 * Funçao que adiciona gatilho para a busca de CEP via AJAX, apos 
 * preenchimento de campo
 * 
 * E esperado os seguintes id's, acrescidos de <compCampos>:
 * <nrCep> - Campo de Cep
 * <nmLogradouro> - Logradouro
 * <nrNumero> - Numero
 * <nmBairro> - Bairro
 * <idEstado> - Estado
 * <idCidade> - Cidade
 * <dsComplemento> - Complemento do endereço
 * <cepAnterior> - Input do tipo hidden para o cep anterior: Processamento interno
 * <erroAnterior> - Input do tipo hidden para processamento interno
 * <foco> - Input do tipo hidden para processamento interno
 *    
 * @param {string} divEspera - Id da div que contem o texto de espera
 * @param {string} divCarga - Id da div que contem os campos de endereço a ser carregado
 * @param {string} compCampos - Complemento de diferenciaçao dos campos de endereço
 * @param {callback function} gatilhoCidade - Funçao que trata o caso da cidade, carregando o select conforme o estado
 * @param {callback function} gatilhoComplemento - Funçao que trata a contagem de caracteres do complemento
 * @returns {void}
 */
function adicionaGatilhoAjaxCep(divEspera, divCarga, compCampos, gatilhoCidade, gatilhoComplemento) {
    // caso do carregamento da pagina
    if (!vazioOuNulo($("#nrCep" + compCampos).val()) && (habilitaBuscaCep($("#nrCep" + compCampos).val())))
    {
        // setando input hidden de controle
        $("#cepAnterior" + compCampos).val($("#nrCep" + compCampos).val());

        // mostrando div com os dados recuperados do BD
        $("#" + divCarga).show();
    }

    //criando gatilho
    gatilho = function () {
        if (!vazioOuNulo($("#nrCep" + compCampos).val()) && (habilitaBuscaCep($("#nrCep" + compCampos).val())))
        {
            buscarCEP(divEspera, divCarga, compCampos, gatilhoCidade, gatilhoComplemento);
        } else {
            //oculta divs
            $("#nrCep" + compCampos + "Erro").hide();
            $("#" + divEspera).hide();
            $("#" + divCarga).hide();
        }
    }

    // adiçao do gatilho
    $("#nrCep" + compCampos).keydown(gatilho);
    $("#nrCep" + compCampos).keyup(gatilho);
}

function habilitaBuscaCep(nrCep)
{
    return nrCep.replace(/\.|-|_/g, '').length == 8;
}

/**
 * Retorna Verdadeiro se e dupla chamada, ou seja, se o CEP em questao ja foi pesquisado.
 * 
 * Essa funçao tambem atualiza o <cepAnterior> se necessario.
 * 
 * @param {string} compCampos
 * @returns {Boolean}
 */
function isChamadaRepetida(compCampos)
{
    if ($("#cepAnterior" + compCampos).val() != $("#nrCep" + compCampos).val())
    {
        //salvar cep atual em anterior
        $("#cepAnterior" + compCampos).val($("#nrCep" + compCampos).val());
        return false;
    }
    return true;

}

/**
 * Separa o numero do logradouro, caso exista.
 * @param {string} logradouro
 * @returns {Array} - Array no seguinte formato: [<Logradouro>, <Numero>];
 * 
 * Caso o numero nao exista na string, <Numero> e vazio. 
 */
function separaNumero(logradouro)
{
    var posVirgula = logradouro.indexOf(",");

    // nao tem numero
    if (posVirgula == -1)
    {
        return [logradouro, ""];
    }

    return [logradouro.substring(0, posVirgula).trim(), logradouro.substring(posVirgula + 1).trim()];
}