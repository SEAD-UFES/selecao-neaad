/* 
 * Scripts úteis para o sistema
 */
function mostrarMensagem()
{
    $("#divBotoes").css("display", 'none');
    $("#divMensagem").css("display", 'block');
}

function mostrarMensagemInline()
{
    $("#divBotoes").css("display", 'none');
    $("#divMensagem").css("display", 'inline');
}

function mostrarBotoes()
{
    $("#divMensagem").css("display", 'none');
    $("#divBotoes").css("display", 'block');
}

function bloquearCopiarColar(idInput)
{
    $('#' + idInput).bind("cut copy paste", function (e) {
        e.preventDefault();
    });
}

// adicionando funçao capitalize ao JQuery
$.fn.capitalize = function () {

    // iterando nos elementos
    $.each(this, function () {

        // dividindo por espaço
        var split = this.value.split(' ');
        // realizando o capitalize 
        for (var i = 0, len = split.length; i < len; i++) {
            split[i] = split[i].charAt(0).toUpperCase() + split[i].slice(1);
        }

        // juntando os valores
        this.value = split.join(' ');
    });
    return this;
};
// adicionando funçao lowercase ao JQuery
$.fn.lowercase = function () {

    // iterando nos elementos
    $.each(this, function () {

        // alterando valor
        this.value = this.value.toLowerCase();
    });
    return this;
};
// adicionando funçao uppercase ao JQuery
$.fn.uppercase = function () {

    // iterando nos elementos
    $.each(this, function () {

        // alterando valor
        this.value = this.value.toUpperCase();
    });
    return this;
};

function retiraAcentos(palavra) {
    var com_acento = 'áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÖÔÚÙÛÜÇ';
    var sem_acento = 'aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC';
    var nova = '';
    for (i = 0; i < palavra.length; i++) {
        if (com_acento.search(palavra.substr(i, 1)) >= 0) {
            nova += sem_acento.substr(com_acento.search(palavra.substr(i, 1)), 1);
        }
        else {
            nova += palavra.substr(i, 1);
        }
    }
    return nova;
}

/**
 * Essa função retorna o parâmetro get. Se o parâmetro não existir, retona string vazia.
 * 
 * @param {string} param
 * @returns {string}
 */
function obterParametroGet(param) {
    var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < url.length; i++) {
        var urlparam = url[i].split('=');
        if (urlparam[0] == param) {
            return urlparam[1];
        }
    }
    return "";
}


function setValorRadio(radioObj, valor) {
    if (!radioObj)
        return;
    var radioLength = radioObj.length;
    if (radioLength === undefined) {
        radioObj.checked = (radioObj.value == valor);
        return;
    }
    for (var i = 0; i < radioLength; i++) {
        radioObj[i].checked = false;
        if (radioObj[i].value == valor) {
            radioObj[i].checked = true;
        }
    }
}

function getValorRadio(radioObj) {
    if (!radioObj)
        return "";
    var radioLength = radioObj.length;
    if (radioLength === undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return "";
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function limparFormulario(elem) {

    $(elem).find(':input').each(function () {
        if ($(this).data("naoedicao") !== true) {
            var padrao = $(this).data("padrao") === undefined ? "" : $(this).data("padrao");
            switch (this.type) {
                case 'password':
                case 'select-multiple':
                case 'select-one':
                case 'text':
                case 'textarea':
                    $(this).val(padrao);
                    break;
                case 'checkbox':
                case 'radio':
                    this.checked = padrao === "" ? false : padrao;
            }
        }
    });
}


/**
 * Funçao que adiciona gatilho para exibiçao de div quando um checkbox e marcado
 * @param {string} idcheckbox - Id do checkbox a ser analisado
 * @param {string} idDivExibicao - Div a ser aberta caso o checkbox esteja maracdo
 * @returns {gatilho}
 */
function adicionaGatilhoAddDivCheckbox(idcheckbox, idDivExibicao)
{
    var gatilho = function () {
        if ($("#" + idcheckbox).is(':checked'))
        {
            $("#" + idDivExibicao).css('display', 'inline');
        } else {
            $("#" + idDivExibicao).hide();
        }
    };
    $("#" + idcheckbox).change(gatilho);
    // retornando gatilho
    return gatilho;
}

/**
 * Funçao que adiciona gatilho para exibiçao de div quando um radio é marcado
 * @param {string} nmGrupoRadio - Nome do grupo de radio a ser analisado
 * @param {array} arrayIdDivExibicao - Array no seguinte formato: [valorRadioSelecionado;nomeDivAbrir], ou seja, cada item do array contém o valor do 
 * radio selecionado, seguido de ponto e vírgula, mais o nome da div que deve ser aberta, caso o valor selecionado seja o valor informado. 
 * @returns {gatilho}
 */
function adicionaGatilhoAddDivRadio(nmGrupoRadio, arrayIdDivExibicao)
{
    var gatilho = function () {

        // necessidade de processamento
        if (arrayIdDivExibicao.length === 0)
        {
            return;
        }

        // separando array para processamento
        var arrayVlComp = [];
        var arrayDivAbrir = [];
        for (var i = 0; i < arrayIdDivExibicao.length; i++)
        {
            var temp = arrayIdDivExibicao[i].split(";");
            arrayVlComp[i] = temp[0];
            arrayDivAbrir[i] = temp[1];
        }

        // desmarcando todos
        $("input[name='" + nmGrupoRadio + "']:not(:checked)").each(function () {
            var posComp = arrayVlComp.indexOf($(this).val());
            if (posComp !== -1)
            {
                $("#" + arrayDivAbrir[posComp]).hide();
            }
        });

        // mostra quem ta selecionado
        var posSel = arrayVlComp.indexOf($("input[name='" + nmGrupoRadio + "']:checked").val());
        if (posSel !== -1)
        {
            $("#" + arrayDivAbrir[posSel]).css('display', 'inline');
        }
    };
    $("input[name='" + nmGrupoRadio + "']").click(gatilho);


    // retornando gatilho
    return gatilho;
}


/**
 * Funçao que adiciona gatilho para exibiçao de div de acordo com o valor selecionado em um select
 * Note: Se nao houver valor selecionado, nenhuma div e exibida
 * @param {string} idSelect - Id do select a ser analisado
 * @param {callback function} valExibeGatilho - Funçao de callback que recebe o valor do select e retorna um boolean informando
 *  a exibiçao ou nao da div. Assinatura esperada: boolean valExibeGatilho(valSelect);
 * @param {string} idDivExibicao - Div a ser aberta caso o valor selecionado no select seja valExibeGatilho 
 * @param {string} idDivOculta - Parametro opcional. Div a ser ocultada caso o valor selecionado no select seja valExibeGatilho
 * @returns {gatilho}
 */
function adicionaGatilhoAddDivSelect(idSelect, valExibeGatilho, idDivExibicao, idDivOculta)
{
    var gatilho = function () {
        if (valExibeGatilho($("#" + idSelect).val()))
        {
            $("#" + idDivExibicao).show();
            if (typeof (idDivOculta) != "undefined") {
                $("#" + idDivOculta).hide();
            }
        } else {
            $("#" + idDivExibicao).hide();
            if (typeof (idDivOculta) != "undefined") {
                if ($("#" + idSelect).val() != "" && $("#" + idSelect).val() != getIdSelectSelecione())
                {
                    $("#" + idDivOculta).show();
                } else {
                    $("#" + idDivOculta).hide();
                }
            }
        }
    };
    $("#" + idSelect).change(gatilho);
    // retornando gatilho
    return gatilho;
}

/**
 * Funçao que adiciona gatilho para contar e limitar os caracteres de um textArea
 * @param {int} maxCaracteres - Quantidade maxima de caracteres
 * @param {string} idTextArea - Id do TextArea a ser limitado
 * @param {string} idContador - Id do elemento contador
 * @param {callback function} funcExec - Ponteiro para funçao a ser executada
 * apos verificaçao da contagem. E passado o numero de caracteres que faltam
 * como parametro. Parametro opcional.
 * @returns {function pointer} - Ponteiro para a funçao gatilho criada
 */
function adicionaContadorTextArea(maxCaracteres, idTextArea, idContador, funcExec)
{
    conta = function () {
        var nrFalta = caracteresRestantes(maxCaracteres, "#" + idTextArea, "#" + idContador);
        // tratando caso de funçao
        if (typeof (funcExec) != "undefined")
        {
            // chamando funçao
            funcExec(nrFalta);
        }
    };
    // incluindo gatilho para o textArea
    $("#" + idTextArea).keyup(conta);
    $("#" + idTextArea).keydown(conta);
    // executando funçao
    conta();
    return conta;
}

/**
 * 
 * @param {int} maxCaracteres
 * @param {objTextArea} textArea
 * @param {objContador} contador
 * @returns {int} - Quantidade de caracteres que ainda falta
 */
function caracteresRestantes(maxCaracteres, textArea, contador) {
    //recuperando item contado
    var qtEscrito = $(textArea).val().length;
    var qtFalta = maxCaracteres - qtEscrito;
    if (qtFalta < 0)
    {
        $(textArea).val($(textArea).val().substr(0, maxCaracteres));
        qtFalta = 0;
    }
    $(contador).html(qtFalta + " caracteres restantes");
    return qtFalta;
}

function vazioOuNulo(val) {
    return (val == null || val == "" || val == 0);
}

/**
 * Recebe um vetor com o id dos campos a analisar
 * @param {Array} vetCampos
 * @returns {Boolean}
 */
function camposVazios(vetCampos)
{
    for (var i = 0; i < vetCampos.length; i++)
    {
        if (!vazioOuNulo($("#" + vetCampos[i]).val()))
        {
            return false;
        }
    }
    return true;
}

function addMascaraDecimal(idCampo)
{
    $('#' + idCampo).priceFormat({
        prefix: '',
        centsSeparator: '.',
        thousandsSeparator: '',
        limit: 5,
        centsLimit: 2
    });
}


//$('#arqEdital').change(function() {
//    //check whether browser fully supports all File API
//    if (window.File && window.FileReader && window.FileList && window.Blob)
//    {
//        //get the file size and file type from file input field
//        var fsize = $('#arqEdital')[0].files[0].size;
//        var ftype = $('#arqEdital')[0].files[0].type;
//        var fname = $('#arqEdital')[0].files[0].name;
//
//        if (fsize > 1048576) //do something if file size more than 1 mb (1048576)
//        {
//            alert("Type :" + ftype + " | " + fsize + " bites\n(File: " + fname + ") Too big!");
//        } else {
//            alert("Type :" + ftype + " | " + fsize + " bites\n(File :" + fname + ") You are good to go!");
//        }
//    } else {
//        alert("Please upgrade your browser, because your current browser lacks some new features we need!");
//    }
//});