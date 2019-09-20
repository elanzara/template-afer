function ErrorHandler(jqXHR, textStatus, text) {
if (jqXHR.readyState == 0) return false;

error = jqXHR.responseText.split("|");
working=false;
loading=false;

switch (error[0])
{
case "401":
case "403":
alert("Informe de error "+error[0]+"\r\n================\r\n\r\n" + error[1]);
window.location.href="/?sessionExpired";
break;
default:
alert("Informe de error "+error[0]+"\r\n================\r\n\r\n" + error[1]);
$('#modal_cargando').modal("hide");
break;
}

$("#addRelation input, #addRelation select").removeAttr ("disabled");loading=false;
}

function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function CheckChildrenComplete(object,fullSearch)
{
if (!fullSearch) fullSearch=false;
isValid = true;
object.find("input[type='text'].obligatorio:visible, input[type='password'].obligatorio:visible").not(".number, .email").each(function(){valor = $(this).val();if (valor == "") {$(this).addClass("badInput");isValid = isValid * false;}else {$(this).removeClass("badInput");isValid = isValid * true;}});
object.find("input[type='text'].number.obligatorio:visible").each(function(){if ($(this).is(".moneda")){valor=$(this).asNumber(totalOptions);}else{valor = parseFloat($(this).val());}if (isNaN(valor) || ($(this).is(".notzero") && valor == 0 )) {$(this).val("").addClass("badInput");isValid = isValid * false;}else {$(this).removeClass("badInput");	isValid = isValid * true;$(this).val(valor);if ($(this).is(".moneda")) $(this).formatCurrency(totalOptions);}});
object.find("select.obligatorio:visible").each(function(){valor = $(this).val();if (valor == "DEFAULT") {$(this).addClass("badInput");isValid = isValid * false;}else {$(this).removeClass("badInput");isValid = isValid * true;}});
object.find("textarea.obligatorio:visible").each(function(){valor = $(this).val();if (valor == "") {$(this).addClass("badInput");isValid = isValid * false;}else {$(this).removeClass("badInput");isValid = isValid * true;}});
object.find("input[type='text'].obligatorio.email:visible").each(function(){var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/; var fieldVal = $(this).val(); if( (emailReg.test( fieldVal ))&& fieldVal != "" ) { isValid = isValid * true; $(this).removeClass("badInput");} else { isValid = false;$(this).addClass("badInput"); };});

if (fullSearch){
if (object.find('input[type=checkbox]').is(':checked') == true) {object.find('input[type=checkbox]').each(function(){$(this).parent().removeClass("badInput");});isValid = isValid * true;} else {object.find('input[type=checkbox]').each(function(){$(this).parent().addClass("badInput");isValid = isValid * false;})};
if (object.find('input[type=radio]').is(':checked') == true) {object.find('input[type=radio]').each(function(){$(this).parent().removeClass("badInput");});isValid = isValid * true;} else {object.find('input[type=radio]').each(function(){$(this).parent().addClass("badInput");isValid = isValid * false;})};
}
return isValid;
}

function CheckObjectComplete(object){isValid = true;valor = object.val();if (valor == "" || valor == "DEFAULT") {object.addClass("badInput");isValid = isValid * false;}else {object.removeClass("badInput");isValid = isValid * true;}return isValid;}

function Reset(object)
{
object.find("input, textarea").each(function(){$(this).val("").removeAttr("readonly");});
object.find("select").each(function(){$(this).val("DEFAULT").removeAttr("readonly");});
}

String.prototype.paddingLeft = function (paddingValue) {
   return String(paddingValue + this).slice(-paddingValue.length);
};