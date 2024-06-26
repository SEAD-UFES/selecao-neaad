/*
 * multiselect2side jQuery plugin
 *
 * Copyright (c) 2010 Giovanni Casassa (senamion.com - senamion.it)
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://www.senamion.com
 * 
 * Adaptações: Estevão de Oliveira da Costa (estevao90 [at] gmail [dot] com)
 *
 */

(function ($)
{
    // SORT INTERNAL
    function internalSort(a, b) {
        var compA = $(a).text().toUpperCase();
        var compB = $(b).text().toUpperCase();
        return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
    }
    ;

    var methods = {
        init: function (options) {
            var o = {
                selectedPosition: 'right',
                moveOptions: true,
                sortOptions: true,
                labelTop: 'Primeiro',
                labelBottom: 'Último',
                labelUp: 'Subir',
                labelDown: 'Descer',
                labelSort: 'Ordenar',
                labelsx: 'Disponível',
                labeldx: 'Selecionado',
                maxSelected: -1,
                autoSort: false,
                autoSortAvailable: false,
                search: false,
                caseSensitive: false,
                delay: 200,
                optGroupSearch: false,
                minSize: 6,
                placeHolderSearch: '',
                widthSelect: 250
            };

            return this.each(function () {
                var el = $(this);
                var data = el.data('multiselect2side');

                if (options) {
                    $.extend(o, options);
                }

                if (!data) {
                    el.data('multiselect2side', o);
                }

                var originalName = $(this).attr("name");
                if (originalName.indexOf('[') != -1) {
                    originalName = originalName.substring(0, originalName.indexOf('['));
                }

                var nameDx = originalName + "ms2side__dx";
                var nameSx = originalName + "ms2side__sx";
                var size = $(this).attr("size");
                // SIZE MIN
                if (size < o.minSize) {
                    $(this).attr("size", "" + o.minSize);
                    size = o.minSize;
                }

                // abre div 
                var abreDivUpDown = "<div class='ms2side__updown'>";

                // Botao Sort
                var btSort = "<button style='height:35px;width:35px;' type='button' class='btn btn-default SelSort' title='Ordenar os itens selecionados'><i class='fa fa-sort-alpha-asc'></i></button>";

                // UP AND DOWN
                var divUpDown = "<button style='height:35px;width:35px;' type='button' class='btn btn-default MoveTop' title='Move o item selecionado para a primeira posição'><i class='fa fa-angle-double-up'></i></button>" +
                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default MoveUp' title='Move o item selecionado uma posição acima'><i class='fa fa-angle-up'></i></button>" +
                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default MoveDown' title='Move o item selecionado uma posição abaixo'><i class='fa fa-angle-down'></i></button>" +
                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default MoveBottom' title='Move o item selecionado para a última posição'><i class='fa fa-angle-double-down'></i></button>";

                // fecha div 
                var fechaDiv = "</div>";

                // INPUT TEXT FOR SEARCH OPTION
                var leftSearch = false, rightSearch = false;

                // BOTH SEARCH AND OPTGROUP SEARCH
                if (o.search != false && o.optGroupSearch != false) {
                    var ss =
                            o.optGroupSearch + "<select class='small' ><option value=__null__> </option></select>" +
                            o.search + "<input class='small' type='text' /><a href='#'> </a>";

                    if (o.selectedPosition == 'right') {
                        leftSearch = ss;
                    } else {
                        rightSearch = ss;
                    }
                }
                else if (o.search != false) {
                    var ss = "<div class='completo' style='margin-bottom:1em;'><div class='ms2side__header'><input type='text' placeholder='" + o.placeHolderSearch + "'/><button style='height:35px;width:35px;margin-left:5px;' type='button' class='btn btn-default' title='Limpar'><i class='fa fa-close'></i></button></div></div>";

                    if (o.selectedPosition == 'right') {
                        leftSearch = ss;
                    } else {
                        rightSearch = ss;
                    }
                }
                else if (o.optGroupSearch != false) {
                    var ss = o.optGroupSearch + "<select><option value=__null__> </option></select>";

                    if (o.selectedPosition == 'right') {
                        leftSearch = ss;
                    } else {
                        rightSearch = ss;
                    }
                }

                // Definindo tamanho da div select
                var tamDivSelect = o.moveOptions ? "40%" : "45%";

                // definindo ID da div
                var idDiv = el.attr("id") + "_ms2side";

                // CREATE NEW ELEMENT (AND HIDE IT) AFTER THE HIDDED ORGINAL SELECT
                var htmlToAdd =
                        "<div id='" + idDiv + "' class='ms2side__div'>" +
                        ((o.labelsx || leftSearch != false) ? (leftSearch != false ? leftSearch : o.labelsx) : "") +
                        ((o.labeldx || rightSearch != false) ? (rightSearch != false ? rightSearch : o.labeldx) : "") +
                        ((o.selectedPosition != 'right' && o.moveOptions) ? abreDivUpDown + (o.sortOptions ? btSort : "") + divUpDown + fechaDiv : "") +
                        "<div style='width: " + tamDivSelect + " !important;' class='ms2side__select'>" +
                        "<select title='" + o.labelsx + "' name='" + nameSx + "' id='" + nameSx + "' size='" + size + "' multiple='multiple' ></select>" +
                        "</div>" +
                        "<div class='ms2side__options'>" +
                        ((o.selectedPosition == 'right')
                                ?
                                ("<button style='height:35px;width:35px;' type='button' class='btn btn-default AddOne' title='Adicionar selecionado'><i class='fa fa-angle-right'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default AddAll' title='Adiconar todos'><i class='fa fa-angle-double-right'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default RemoveOne' title='Remover selecionado'><i class='fa fa-angle-left'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default RemoveAll' title='Remover todos'><i class='fa fa-angle-double-left'></i></button>")
                                :
                                ("<button style='height:35px;width:35px;' type='button' class='btn btn-default AddOne' title='Adicionar selecionado'><i class='fa fa-angle-right'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default AddAll' title='Adicionar todos'><i class='fa fa-angle-double-right'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default RemoveOne' title='Remover selecionado'><i class='fa fa-angle-left'></i></button>" +
                                        "<button style='height:35px;width:35px;' type='button' class='btn btn-default RemoveAll' title='Remover todos'><i class='fa fa-angle-double-left'></i></button>")
                                ) +
                        "</div>" +
                        "<div style='width: " + tamDivSelect + " !important;' class='ms2side__select'>" +
                        "<select title='" + o.labeldx + "' name='" + nameDx + "' id='" + nameDx + "' size='" + size + "' multiple='multiple' ></select>" +
                        "</div>" +
                        ((o.selectedPosition == 'right' && o.moveOptions) ? abreDivUpDown + (o.sortOptions ? btSort : "") + divUpDown + fechaDiv : "") +
                        "</div>";
                el.after(htmlToAdd).hide();

                // ELEMENTS
                var allSel = el.next().children(".ms2side__select").children("select");
                //console.log(allSel);
                var leftSel = (o.selectedPosition == 'right') ? allSel.eq(0) : allSel.eq(1);
                //console.log(leftSel);
                var rightSel = (o.selectedPosition == 'right') ? allSel.eq(1) : allSel.eq(0);

                // HEIGHT DIV
                var heightDiv = $(".ms2side__select").eq(0).height();

                // SELECT optgroup
                var searchSelect = $();

                // SEARCH INPUT
                var searchInput = $(this).next().find("input:text");
                var removeFilter = searchInput.next().hide();
                var toid = false;
                var searchV = false;

                // SELECT optgroup - ADD ALL OPTGROUP AS OPTION
                if (o.optGroupSearch != false) {
                    var lastOptGroupSearch = false;

                    searchSelect = $(this).next().find("select").eq(0);

                    el.children("optgroup").each(function () {
                        if (searchSelect.find("[value='" + $(this).attr("label") + "']").size() == 0)
                            searchSelect.append("<option value='" + $(this).attr("label") + "'>" + $(this).attr("label") + "</option>");
                    });
                    searchSelect.change(function () {
                        var sEl = $(this);

                        if (sEl.val() != lastOptGroupSearch) {

                            // IF EXIST SET SEARCH TEXT TO VOID
                            if (searchInput.val() != "") {
                                clearTimeout(toid);
                                removeFilter.hide();
                                searchInput.val("");//.trigger('keyup');
                                searchV = "";
                                // fto();
                            }

                            setTimeout(function () {
                                if (sEl.val() == "__null__") {
                                    var els = el.find("option:not(:selected)");
                                }
                                else
                                    var els = el.find("optgroup[label='" + sEl.val() + "']").children("option:not(:selected)");

                                // REMOVE ORIGINAL ELEMENTS AND ADD OPTION OF OPTGROUP SELECTED
                                leftSel.find("option").remove();
                                els.each(function () {
                                    leftSel.append($(this).clone());
                                });
                                lastOptGroupSearch = sEl.val();
                                leftSel.trigger('change');
                            }, 100);
                        }
                    });
                }


                // SEARCH FUNCTION
                var fto = function () {
                    // var els = leftSel.children();
                    var toSearch = el.find("option:not(:selected)");

                    // RESET OptGroupSearch
                    lastOptGroupSearch = "__null__";
                    searchSelect.val("__null__");

                    if (searchV == searchInput.val()) {
                        return;
                    }

                    searchInput.removeAttr("style");
                    searchV = searchInput.val();

                    // A LITTLE TIMEOUT TO VIEW WAIT CLASS ON INPUT ON IE
                    setTimeout(function () {
                        leftSel.children().remove();
                        if (searchV == "") {
                            toSearch.clone().appendTo(leftSel).prop("selected", false);
                            removeFilter.hide();
                        }
                        else {
                            toSearch.each(function () {
                                var myText = $(this).text();

                                if (o.caseSensitive) {
                                    var find = myText.indexOf(searchV);
                                } else {
                                    find = myText.toUpperCase().indexOf(searchV.toUpperCase());
                                }

                                if (find != -1) {
                                    $(this).clone().appendTo(leftSel).prop("selected", false);
                                }
                            });

                            if (leftSel.children().length == 0) {
                                searchInput.css({'border': '1px red solid'});
                            }

                            removeFilter.show();
                            //leftSel.trigger('change');
                        }

                        leftSel.trigger('change');
                    }, 5);
                };


                // REMOVE FILTER ON SEARCH FUNCTION
                removeFilter.click(function () {
                    clearTimeout(toid);
                    searchInput.val("");
                    fto();
                    return false;
                });

                // ON CHANGE TEXT INPUT
                searchInput.keyup(function () {
                    clearTimeout(toid);
                    toid = setTimeout(fto, o.delay);
                });

                // MOVE SELECTED OPTION TO RIGHT, NOT SELECTED TO LEFT
                $(this).find("option:selected").clone().appendTo(rightSel).prop("selected", false);
                $(this).find("option:not(:selected)").clone().appendTo(leftSel);


                // ON CHANGE SORT SELECTED OPTIONS
                var nLastAutosort = 0;
                if (o.autoSort) {
                    allSel.change(function () {
                        var selectDx = rightSel.find("option");
                        if (selectDx.length != nLastAutosort) {
                            // SORT SELECTED ELEMENT
                            selectDx.sort(internalSort);

                            // Remove todas as opções do select da direita
                            rightSel.find("option").remove();


                            // AFTER ADD ON RIGHT SELECT AND ORIGINAL SELECT
                            selectDx.each(function () {
                                // removendo opção do select original primeiro
                                el.find("option[value='" + $(this).val() + "']").remove();


                                rightSel.append($(this).clone());
                                el.append($(this).clone().prop("selected", true));
                            });

                            nLastAutosort = selectDx.length;
                        }
                    });
                }

                // ON CHANGE SORT AVAILABLE OPTIONS (NOT NECESSARY IN ORIGINAL SELECT)
                var nLastAutosortAvailable = 0;
                if (o.autoSortAvailable) {
                    allSel.change(function () {
                        var selectSx = leftSel.find("option");

                        if (selectSx.length != nLastAutosortAvailable) {
                            // SORT SELECTED ELEMENT
                            selectSx.sort(internalSort);
                            // REMOVE ORIGINAL ELEMENTS AND ADD SORTED
                            leftSel.find("option").remove();
                            selectSx.each(function () {
                                leftSel.append($(this).clone());
                            });
                            nLastAutosortAvailable = selectSx.length;
                        }
                    });
                }


                // ON CHANGE REFRESH ALL BUTTON STATUS
                allSel.change(function () {
                    var div = $(this).parent().parent();
                    var selectSx = leftSel.children();
                    var selectDx = rightSel.children();
                    var selectedSx = leftSel.find("option:selected");
                    var selectedDx = rightSel.find("option:selected");

                    // validando mostrar botão de adicionar item
                    if (selectedSx.size() == 0 || (o.maxSelected >= 0 && (selectedSx.size() + selectDx.size()) > o.maxSelected))
                    {
                        div.find(".AddOne").addClass('ms2side__hide');
                        div.find(".AddOne").attr("disabled", true);
                    }
                    else {
                        div.find(".AddOne").removeClass('ms2side__hide');
                        div.find(".AddOne").attr("disabled", false);
                    }

                    // FIRST HIDE ALL
                    div.find(".RemoveOne, .MoveUp, .MoveDown, .MoveTop, .MoveBottom, .SelSort").addClass('ms2side__hide');
                    div.find(".RemoveOne, .MoveUp, .MoveDown, .MoveTop, .MoveBottom, .SelSort").attr("disabled", true);
                    if (selectDx.size() > 1) {
                        div.find(".SelSort").removeClass('ms2side__hide');
                        div.find(".SelSort").attr("disabled", false);
                    }
                    if (selectedDx.size() > 0) {
                        div.find(".RemoveOne").removeClass('ms2side__hide');
                        div.find(".RemoveOne").attr("disabled", false);

                        // ALL SELECTED - NO MOVE
                        if (selectedDx.size() < selectDx.size()) {	// FOR NOW (JOE) && selectedDx.size() == 1
                            if (selectedDx.val() != selectDx.val()) {	// FIRST OPTION, NO UP AND TOP BUTTON
                                div.find(".MoveUp, .MoveTop").removeClass('ms2side__hide');
                                div.find(".MoveUp, .MoveTop").attr("disabled", false);
                            }
                            if (selectedDx.last().val() != selectDx.last().val()) {	// LAST OPTION, NO DOWN AND BOTTOM BUTTON
                                div.find(".MoveDown, .MoveBottom").removeClass('ms2side__hide');
                                div.find(".MoveDown, .MoveBottom").attr("disabled", false);
                            }
                        }
                    }

                    if (selectSx.size() == 0 || (o.maxSelected >= 0 && (selectSx.size() + selectDx.size()) >= o.maxSelected)) {
                        div.find(".AddAll").addClass('ms2side__hide');
                        div.find(".AddAll").attr("disabled", true);
                    } else {
                        div.find(".AddAll").removeClass('ms2side__hide');
                        div.find(".AddAll").attr("disabled", false);
                    }

                    if (selectDx.size() == 0) {
                        div.find(".RemoveAll").addClass('ms2side__hide');
                        div.find(".RemoveAll").attr("disabled", true);
                    } else {
                        div.find(".RemoveAll").removeClass('ms2side__hide');
                        div.find(".RemoveAll").attr("disabled", false);
                    }
                });

                // DOUBLE CLICK ON LEFT SELECT OPTION
                leftSel.dblclick(function () {
                    $(this).find("option:selected").each(function (i, selected) {

                        if (o.maxSelected < 0 || rightSel.children().size() < o.maxSelected) {
                            $(this).remove().appendTo(rightSel);
                            el.find("option[value='" + $(selected).val() + "']").remove().appendTo(el).prop("selected", true);
                        }
                    });
                    $(this).trigger('change');
                });

                // DOUBLE CLICK ON RIGHT SELECT OPTION
                rightSel.dblclick(function () {
                    $(this).find("option:selected").each(function (i, selected) {
                        $(this).remove().appendTo(leftSel);
                        el.find("[value='" + $(selected).val() + "']").prop("selected", false).remove().appendTo(el);
                    });
                    $(this).trigger('change');

                    // TRIGGER CHANGE AND VALUE NULL FORM OPTGROUP SEARCH (IF EXIST)
                    searchSelect.val("__null__").trigger("change");
                    // TRIGGER CLICK ON REMOVE FILTER (IF EXIST)
                    removeFilter.click();
                });

                // CLICK ON OPTION
                $(this).next().find('.ms2side__options').children().click(function () {
                    if (!$(this).hasClass("ms2side__hide")) {
                        if ($(this).hasClass("AddOne")) {
                            leftSel.find("option:selected").each(function (i, selected) {
                                $(this).remove().appendTo(rightSel);
                                el.find("[value='" + $(selected).val() + "']").remove().appendTo(el).prop("selected", true);
                            });
                        }
                        else if ($(this).hasClass("AddAll")) {	// ALL SELECTED
                            // TEST IF HAVE A FILTER OR A SELECT OPTGROUP
                            if (removeFilter.is(":visible") || (searchSelect.length > 0 && searchSelect.val() != "__null__"))
                                leftSel.children().each(function (i, selected) {
                                    $(this).remove().appendTo(rightSel);
                                    el.find("[value='" + $(selected).val() + "']").remove().appendTo(el).prop("selected", true);
                                });
                            else {
                                leftSel.children().remove().appendTo(rightSel);
                                el.find('option').prop("selected", true);
                                // el.children().prop("selected", true); -- PROBLEM WITH OPTGROUP
                            }
                        }
                        else if ($(this).hasClass("RemoveOne")) {
                            rightSel.find("option:selected").each(function (i, selected) {
                                $(this).remove().appendTo(leftSel);
                                el.find("[value='" + $(selected).val() + "']").remove().appendTo(el).prop("selected", false);
                            });
                            // TRIGGER CLICK ON REMOVE FILTER (IF EXIST)
                            removeFilter.click();
                            // TRIGGER CHANGE AND VALUE NULL FORM OPTGROUP SEARCH (IF EXIST)
                            searchSelect.val("__null__").trigger("change");
                        }
                        else if ($(this).hasClass("RemoveAll")) {	// ALL REMOVED
                            rightSel.children().appendTo(leftSel);
                            rightSel.children().remove();
                            el.find('option').prop("selected", false);
                            //el.children().prop("selected", false); -- PROBLEM WITH OPTGROUP
                            // TRIGGER CLICK ON REMOVE FILTER (IF EXIST)
                            removeFilter.click();
                            // TRIGGER CHANGE AND VALUE NULL FORM OPTGROUP SEARCH (IF EXIST)
                            searchSelect.val("__null__").trigger("change");
                        }
                    }

                    leftSel.trigger('change');
                });

                // CLICK ON UP - DOWN
                $(this).next().find('.ms2side__updown').children().click(function () {
                    var selectedDx = rightSel.find("option:selected");
                    var selectDx = rightSel.find("option");

                    if (!$(this).hasClass("ms2side__hide")) {
                        if ($(this).hasClass("SelSort")) {
                            // SORT SELECTED ELEMENT
                            selectDx.sort(internalSort);
                            // FIRST REMOVE FROM ORIGINAL SELECT
                            el.find("option:selected").remove();
                            // AFTER ADD ON ORIGINAL AND RIGHT SELECT
                            selectDx.each(function () {
                                rightSel.append($(this).clone().prop("selected", true));
                                el.append($(this).prop("selected", true));
                            });
                        }
                        else if ($(this).hasClass("MoveUp")) {
                            var prev = selectedDx.first().prev();
                            var hPrev = el.find("[value='" + prev.val() + "']");

                            selectedDx.each(function () {
                                $(this).insertBefore(prev);
                                el.find("[value='" + $(this).val() + "']").insertBefore(hPrev);	// HIDDEN SELECT
                            });
                        }
                        else if ($(this).hasClass("MoveDown")) {
                            var next = selectedDx.last().next();
                            var hNext = el.find("[value='" + next.val() + "']");

                            selectedDx.each(function () {
                                $(this).insertAfter(next);
                                el.find("[value='" + $(this).val() + "']").insertAfter(hNext);	// HIDDEN SELECT
                            });
                        }
                        else if ($(this).hasClass("MoveTop")) {
                            var first = selectDx.first();
                            var hFirst = el.find("[value='" + first.val() + "']");

                            selectedDx.each(function () {
                                $(this).insertBefore(first);
                                el.find("[value='" + $(this).val() + "']").insertBefore(hFirst);	// HIDDEN SELECT
                            });
                        }
                        else if ($(this).hasClass("MoveBottom")) {
                            var last = selectDx.last();
                            var hLast = el.find("[value='" + last.val() + "']");

                            selectedDx.each(function () {
                                last = $(this).insertAfter(last);	// WITH last = SAME POSITION OF SELECTED OPTION AFTER MOVE
                                hLast = el.find("[value='" + $(this).val() + "']").insertAfter(hLast);	// HIDDEN SELECT
                            });
                        }
                    }

                    leftSel.trigger('change');
                });

                // HOVER ON OPTION
                $(this).next().find('.ms2side__options, .ms2side__updown').children().hover(
                        function () {
                            $(this).addClass('ms2side_hover');
                        },
                        function () {
                            $(this).removeClass('ms2side_hover');
                        }
                );

                // UPDATE BUTTON ON START
                leftSel.trigger('change');

                // Implementando responsividade
                // 
                // função responsiva
                var trataResponsividade = function () {
                    leftSel.find("option").remove();
                    rightSel.find("option").remove();
                    el.find("option:selected").clone().appendTo(rightSel).prop("selected", false);
                    el.find("option:not(:selected)").clone().appendTo(leftSel);
                    leftSel.trigger('change');
                    if ($(window).width() >= 767) {
                        // Tela grande

                        // esconde item padrão e mostra multiselect
                        el.hide();
                        $("#" + idDiv).show();
                    }
                    else {
                        // Tela pequena

                        // esconde multiselect e mostra item padrão
                        $("#" + idDiv).hide();
                        el.show();
                    }
                };

                // gatilho
                $(window).resize(trataResponsividade);
                trataResponsividade();
                //
                // Fim da responsividade

                // SHOW WHEN ALL READY
                //$(this).next().show();
            });
        },
        destroy: function ( ) {
            return this.each(function () {
                var el = $(this);
                var data = el.data('multiselect2side');

                if (!data)
                    return;

                el.show().next().remove();
            });
        },
        addOption: function (options) {
            var oAddOption = {
                name: false,
                value: false,
                selected: false
            };

            return this.each(function () {
                var el = $(this);
                var data = el.data('multiselect2side');

                if (!data)
                    return;

                if (options)
                    $.extend(oAddOption, options);

                var strEl = "<option value='" + oAddOption.value + "' " + (oAddOption.selected ? "selected" : "") + " >" + oAddOption.name + "</option>";

                el.append(strEl);

                // ELEMENTS
                var allSel = el.next().children(".ms2side__select").children("select");
                var leftSel = (data.selectedPosition == 'right') ? allSel.eq(0) : allSel.eq(1);
                var rightSel = (data.selectedPosition == 'right') ? allSel.eq(1) : allSel.eq(0);

                if (oAddOption.selected)
                    rightSel.append(strEl).trigger('change');
                else
                    leftSel.append(strEl).trigger('change');
            });
        }
    };

    $.fn.multiselect2side = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.multiselect2side');
        }
    };

})(jQuery);