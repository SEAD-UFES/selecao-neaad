/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function validaDataBR(value, element) {
    //caso vazio é válido
    if (value == "" || value == "__/__/____")
    {
        return true;
    }
    var check = false;
    var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
    if (re.test(value)) {
        var adata = value.split('/');
        var gg = parseInt(adata[0], 10);
        var mm = parseInt(adata[1], 10);
        var aaaa = parseInt(adata[2], 10);
        var xdata = new Date(aaaa, mm - 1, gg);
        if ((xdata.getFullYear() === aaaa) && (xdata.getMonth() === mm - 1) && (xdata.getDate() === gg)) {
            check = true;
        } else {
            check = false;
        }
        if (aaaa < 1900)
        {
            check = false;
        }
    } else {
        check = false;
    }
    return check;
}

function validaLoginUFES(element) {
    /**
     * Primeira Tentativa: (nome.sobrenome)
     * Segunda tentativa: (nome.a.sobr, nome.b.sobr, ...)
     * Terceira tentativa: (nome.ab.sobr, nome.abc.sobr, ...)
     * Tentativa final: Adicionar ultimos 2 dígitos do CPF ou o CPF inteiro.
     * 
     * @type RegExp
     * 
     */
    var exp = /(^[a-z]{3,}(\.[a-z]{1,3})?\.[a-z]{3,}(\.[0-9]{2}|\.[0-9]{11})?$)/;
    return exp.test(element.value);
}


jQuery.validator.addMethod("emailUfes", function (value, element) {
    return this.optional(element) || /^[a-zA-Z0-9._-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(value);

}, "Informe um email válido."); // Mensagem padrão


jQuery.validator.addMethod("nomeCompleto", function (value, element) {
    return this.optional(element) || /^[^ -@\[-`{-~}][^ -&(-,./:-@[-^`{-~}]{2,}(( [^ -@\[-`{-~}][^ -&(-,./:-@[-^`{-~}]{1,})|( e))+$/.test(value);

}, "Informe seu nome completo corretamente."); // Mensagem padrão

jQuery.validator.addMethod("nome", function (value, element) {
    return this.optional(element) || /^[^ -@\[-`{-~}][^ -&(-,./:-@[-^`{-~}]{2,}(( [^ -@\[-`{-~}][^ -&(-,./:-@[-^`{-~}]{1,})|( e))+$/.test(value);

}, "Informe seu nome corretamente."); // Mensagem padrão


jQuery.validator.addMethod("tamMaxArq", function (value, element, params) {
    var tamMax;
    var baseMB = 1024 * 1024; // MB
    tamMax = params;
    var tamArq = 0;


    // verifica se o navegador suporta
    if (window.File && window.FileReader && window.FileList && window.Blob)
    {
        //get the file size
        if (element.files && element.files.length)
        {
            tamArq = element.files[0].size;
        }
    } else {
        // nada a fazer
    }

    return this.optional(element) || tamArq < baseMB * tamMax;

}, "Arquivo maior que o tamanho máximo permitido."); // Mensagem padrão


jQuery.validator.addMethod("qtdeMaxSelect", function (value, element, params) {
    var tamMax = params;
    var qtSelecionado = $(element).find('option:selected').length;

    return this.optional(element) || qtSelecionado <= tamMax;

}, "Número de selecionados maior que o permitido."); // Mensagem padrão


function getObjData(params, isParametro) {
    var dataP;
    if (typeof params === 'string')
    {
        var temp = isParametro ? $(params).val() : params;
        if (temp === null)
        {
            return null;
        }
        var ret = validaDataBR(temp, null);
        if (!ret) {
            return null;
        }
        dataP = new Date(temp.substr(6, 4), temp.substr(3, 2) - 1, temp.substr(0, 2));
    } else {
        dataP = params;
        dataP.setHours(0, 0, 0, 0);
    }
    return dataP;
}

jQuery.validator.addMethod("CPF", function (value, element) {
    value = value.replace('.', '');
    value = value.replace('.', '');
    var cpf = value.replace('-', '');
    var valido = false;
    while (cpf.length < 11)
        cpf = "0" + cpf;
    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
    var a = [];
    var b = new Number;
    var c = 11;
    for (i = 0; i < 11; i++) {
        a[i] = cpf.charAt(i);
        if (i < 9)
            b += (a[i] * --c);
    }
    if ((x = b % 11) < 2) {
        a[9] = 0
    } else {
        a[9] = 11 - x
    }
    b = 0;
    c = 11;
    for (y = 0; y < 10; y++)
        b += (a[y] * c--);
    if ((x = b % 11) < 2) {
        a[10] = 0;
    } else {
        a[10] = 11 - x;
    }
    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) {
        valido = false;
    } else {
        return true;
    }
    return this.optional(element) || valido;

}, "CPF inválido."); // Mensagem padrão

jQuery.validator.addMethod("dataBR", validaDataBR, "Data inválida"); // Mensagem padrão

jQuery.validator.addMethod("dataBRMenor", function (value, element, params) {
    var dataP, dataE;
    dataE = getObjData(value, false);
    dataP = getObjData(params, true);
    if (!(dataE instanceof Date && isFinite(dataE)) || !dataP instanceof Date && isFinite(dataP))
    {
        return true;
    }
    return this.optional(element) || dataE < dataP;

}, "Campo deve ser menor."); // Mensagem padrão

jQuery.validator.addMethod("dataBRMenorIgual", function (value, element, params) {
    var dataP, dataE;
    dataE = getObjData(value, false);
    dataP = getObjData(params, true);
    if (!(dataE instanceof Date && isFinite(dataE)) || !dataP instanceof Date && isFinite(dataP))
    {
        return true;
    }
    return this.optional(element) || dataE <= dataP;

}, "Campo deve ser menor ou igual."); // Mensagem padrão


jQuery.validator.addMethod("dataBRMaior", function (value, element, params) {
    var dataP, dataE;
    dataE = getObjData(value, false);
    dataP = getObjData(params, true);
    if (!(dataE instanceof Date && isFinite(dataE)) || !dataP instanceof Date && isFinite(dataP))
    {
        return true;
    }
    return this.optional(element) || dataE > dataP;

}, "Campo deve ser maior."); // Mensagem padrão

jQuery.validator.addMethod("dataBRMaiorIgual", function (value, element, params) {
    var dataP, dataE;
    dataE = getObjData(value, false);
    dataP = getObjData(params, true);
    if (!(dataE instanceof Date && isFinite(dataE)) || !dataP instanceof Date && isFinite(dataP))
    {
        return true;
    }
    return this.optional(element) || dataE >= dataP;

}, "Campo deve ser maior ou igual."); // Mensagem padrão