var updatePath = "actualizar/";
var getRecordPath = "consultar/";
var searchPath = "buscar/";
var proccessPath = "procesar/";
var deletePath = "eliminar/";
var activatePath = "activar/";
var GetNotificationsPath = "/estado/notificaciones-controladora-fiscal/";
var dataGet = "get-data/";
var popup = false;
var currencyOptions = {decimalSymbol: ".", digitGroupSymbol: ",", colorize:"true", symbol:"$ ", negativeFormat:"-%s%n", roundToDecimalPlace:4};
var cantidadOptions = {decimalSymbol: ".", digitGroupSymbol: "", colorize:"true", symbol:"", negativeFormat:"-%s%n", roundToDecimalPlace:2};
var totalOptions = {decimalSymbol: ".", digitGroupSymbol: ",", colorize:"true", symbol:"$ ", negativeFormat:"-%s%n", roundToDecimalPlace:2};
   
$(document).ready(function(){
                            try {var options = {clearFiltersControls: [$('#cleanF')]};$('table#tblRegistros').not(".notfilter").tableFilter(options);}catch(e){console.log(e);}
    /* agregar registro */	$("#addRec").click(AddRecordEvents);
    /* agregar registro */	$("#srcRec").click(SearchRecordEvents);
    /* ordenar tabla */		$('table#tblRegistros').not(".notsort").tablesorter();
    /* editar registro */ 	$("td.action.edit").click(EditRecordEvents);
    /* eliminar registro */ $("td.action.delete").click(DeleteRecordEvents);
    /* activar registro */ 	$("td.action.activate").click(ActivateEvents);
    /* formato de moneda */	$("form#abml input.moneda:enabled").change(function(){valor = $(this).formatCurrency(totalOptions);total=0;iva=0;$("form#abml input.moneda:enabled").each(function(){este = $(this).asNumber(totalOptions); total += este;});$("form#abml input.iva").each(function(){este = $(this).asNumber(totalOptions); iva += este;});$("form#abml input#credito_fiscal").val(iva).formatCurrency(totalOptions);$("form#abml input#monto_total").val(total).formatCurrency(totalOptions);});
    /* links con confirm*/  $("a.confirm").not(".etapas").click(function(e){e.preventDefault();if (confirm("Confirme acción")){window.location = ($(this).attr("href"));}})
    /* datepickers */       $("input.datepick").datepicker({format: "yyyy-mm-dd",language: "es",keyboardNavigation: false,todayHighlight: true, autoclose: true});
    /* links con popup */   $("a.popup").click(function(e){e.preventDefault();

        destino = $(this).attr("href");

        if (popup.closed === true || popup === false) {
                altura = 775;
                ancho = 1100;
                altura_link = $(this).attr("data-height");
                ancho_link = $(this).attr("data-width");
                if (altura_link != undefined) altura = altura_link;
                if (ancho_link != undefined) ancho = ancho_link;

                popup = window.open(destino,"popup","status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0,scrollbars=1,height="+altura+",width="+ancho);
                popup.focus();
            }
            else
            {
                alert("Cierre el primero para abrir otro dialogo");
                popup.focus();
            }


    });
    /* enable user */       FormEvents(); $('#modal_cargando').modal("hide");
    /* nofificaciones */   // Notification.requestPermission(function(status){if (status=="granted"){var notif_timeout = setInterval(GetNotifications, 3000);}});                
    /*var notif_timeout = setInterval(GetNotifications, 5000);*/
    $('#modal_cargando').modal("hide");
});

function GetNotifications()
{

    var request = $.ajax({type: "GET",url: GetNotificationsPath});
    request.fail(ErrorHandler);
    request.done(function(data){
        if(data != "") {
    $("#fiscal_printer_errors").html(data);
    }
    });
}

function EditRecordEvents(event)
{
	$("form#abml #cancelar").click();Modal("CARGANDO");registro = $(this).parents("tr").attr("data-id");tabla = $("form#abml #DDBB_table").val();var request = $.ajax({type: "POST",url: getRecordPath, data:"DDBB_table="+tabla+"&id="+registro});request.fail(ErrorHandler);request.done(function(data){datos = JSON.parse(data);for(var clave in datos) { parsed = $('<div />').html(datos[clave]).text();	$("form#abml *[name="+clave+"]").val(parsed);}	$("form#abml").attr("action", updatePath);$("h3.form.panel-title").text("Editar registro #"+registro.paddingLeft("00000")); $("div.form.panel").removeClass("hidden");$("form#abml #DDBB_table").eq(0).trigger("change");

 $('html, body').stop().animate({scrollTop: 0}, 0);

        $('#modal_cargando').modal("hide");});
}

function DeleteRecordEvents(event)
{
	$("form#abml #cancelar").click();registro = $(this).parents("tr").attr("data-id");tabla = $("form#abml #DDBB_table").val();if (confirm("CONFIRME ELIMINAR REGISTRO - NO SE PUEDE DESHACER")){Modal("CARGANDO");var request = $.ajax({type: "POST",url: deletePath, data:"DDBB_table="+tabla+"&id="+registro});request.fail(ErrorHandler);request.done(function(data){$("table tbody tr[data-id='"+registro+"']").remove();$('#modal_cargando').modal("hide");});}
}
function ActivateEvents(event)
{
	$("form#abml #cancelar").click();Modal("CARGANDO");var request = $.ajax({type: "POST",url: activatePath, data:{id_empresa:$(this).parents("tr").attr("data-id")}});request.fail(ErrorHandler);request.done(function(data){alert("REGISTRO ACTIVADO - PUEDE COMENZAR A TRABAJAR");$('#modal_cargando').modal("hide");});
}

function SearchRecordEvents(event)
{
		$("form#abml #cancelar").click();	/* boton de tabla */$("div.search.panel").removeClass("hidden");
}
function AddRecordEvents(event)
{
	$("form#abml #cancelar").click(); /* boton de tabla */$("h3.form.panel-title").text("Agregar nuevo registro");$("form#abml").attr("action",proccessPath);/* boton de tabla */$("div.form.panel").removeClass("hidden");$("form#abml input, form#abml select").eq(1).focus();
}

function FormEvents()
{
$("form#abml").submit(function(eve){eve.preventDefault();})
$("#aceptar").click(function(){if(CheckChildrenComplete($("form#abml"))){Modal("PROCESANDO"); $("form#abml .moneda").each(function(){$(this).val($(this).asNumber(totalOptions))}); var request = $.ajax({type: "POST",url: $("form#abml").not(".search").attr("action"), data:$("form#abml").not(".search, .modal-form").serialize()});request.fail(ErrorHandler);request.done(function(data){datos = data.split("|");if(datos[0] == "201") {location.href=datos[1];}else{location.reload();}});}else{$("#credito_fiscal, #monto_total").attr("disabled", "disabled");alert("Corrija los campos maracados en rojo");$('#modal_cargando').modal("hide");}});
$("#cancelar, #cancelar_busqueda").click(function(){$("div.form.panel, div.search.panel").addClass("hidden");$("form#abml input").not(".fixed").removeClass("badInput").val("");$("form#abml select").not(".fixed").removeClass("badInput").val("DEFAULT");});
$("#buscar").click(function(){
	Modal("CARGANDO");
	registro = $("#search input, #search select").serialize();
	location.href="?buscar&"+registro;
})
}

function Modal(text)
{
	$("#modal_cargando h1").text(text);
	$('#modal_cargando').modal({ backdrop: 'static', keyboard: false});
}