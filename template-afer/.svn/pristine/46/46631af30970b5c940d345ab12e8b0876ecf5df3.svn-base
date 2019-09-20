var updatePath = "../"; /* _modulo_/editar/ */
var newCommentPath = "../../agregar-comentario/"; /* _modulo_/agregar-comentario/ */
var newAlertPath = "../../agregar-alerta/"; /* _modulo_/agregar-alerta/ */
var ubicacion;

$(document).ready(function(){
 /* editar registro */   	$("#guardar_pedido").click(EditRecordEvents);
 /* nuevo comentario */ 	$("#nuevo_comentario").click(NewComment);
 /* nueva alerta */		 	$("#nueva_alerta").click(NewAlert);
 /* avanzar y retroceder */	$("a.btn.etapas").click(function(e){

 	e.preventDefault();
 	if (confirm("Confirme accion - Se guardarán los cambios hechos"))
 	{
 	ubicacion = $(this).attr("href");
 	Modal("CARGANDO");
 	var request = $.ajax({type: "POST",url: updatePath, data:$("form#pedido").serialize()});
 	request.fail(function(jqXHR, textStatus, text) {if (jqXHR.readyState == 0) return false;error = jqXHR.responseText.split("|");alert("Informe de error "+error[0]+"\r\n================\r\n\r\n" + error[1]);location.href= ubicacion;});
 	request.done(function(){location.href = ubicacion;});
 	}
 });

});

function EditRecordEvents(event)
{
	
	Modal("CARGANDO");var request = $.ajax({type: "POST",url: updatePath, data:$("form#pedido").serialize()});request.fail(ErrorHandler);request.done(function(){location.reload();});
}

function NewComment(event)
{
	event.preventDefault();
	if (CheckChildrenComplete($("form#agregar_comentario")))
	{

    Modal("CARGANDO");var request = $.ajax({type: "POST",url: newCommentPath, data:$("form#agregar_comentario").serialize()});request.fail(ErrorHandler);
    request.done(function(){location.reload();});
	}
	else
	{
		alert("Ingrese un comentario");
	}
}
function NewAlert(event)
{
	event.preventDefault();
	if (CheckChildrenComplete($("form#agregar_comentario")))
	{
    CustomModal("modal_nueva_alerta");
    }
	else
	{
		alert("Ingrese un comentario");
	}
}



function Modal(text)
{
	$("#cargando p").text(text);
	$('#cargando').modal({ backdrop: 'static', keyboard: false});
}


function CustomModal(id_div)
{
	$("div#"+id_div).modal({ backdrop: 'static', keyboard: false});
}