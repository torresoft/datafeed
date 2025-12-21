var slides=null;
var zinde=10000;
var curper=0;
var cuhc=0;
var inttender=null;
$(document).ready(
		function()
		{
			tryhovers();
			if(localStorage.getItem("sockets")==0){
				inttender=setInterval(function(){
					if($("#homeviewer").length>0){
						
						$.ajax({
							//url : "mviews.php?flag=infomesas",
							url : "mviews.php?flag=home",
							type: "POST",
							data : {},
							timeout:5000,
							dataType: "html",
							success: function(data, textStatus, jqXHR)
								{
									$("#contenidos").html(data);
									tryhovers();
									if(sessionStorage.getItem("curfilter")!=undefined){
										$(".btnfiltershome").removeClass("btn-success");
										var fl=sessionStorage.getItem("curfilter");
										if(fl!="all"){
											$(".data-filtrable").hide();
											$(fl).show();
											$("button[data-filter='"+fl+"']").addClass("btn-success");
										}else{
											$(".data-filtrable").show();
											$("button[data-filter='"+fl+"']").addClass("btn-success");
										}
										
									}
									
								},
							error : function(xhr, textStatus, errorThrown ) 
								{
								console.log("Notificador fuera de linea");
								}
						});
					}else if($("#hometablek").length>0){
						/*var id_table=$("#hometablek").val();
						var id_ped=$("#hometablek").attr("id_ped");
						$.ajax({
							//url : "mviews.php?flag=infomesas",
							url : "mviews.php?flag=opentable&id_mesa="+id_table+"&id_pedido="+id_ped,
							type: "POST",
							data : {},
							timeout:5000,
							dataType: "html",
							success: function(data, textStatus, jqXHR)
								{
									$("#contenidos").html(data);
									tryhovers();
								},
							error : function(xhr, textStatus, errorThrown ) 
								{
								console.log("Notificador fuera de linea");
								}
						});*/
					}
					if(localStorage.getItem("sockets")==1){
						clearInterval(inttender);
					}
				},10000);
			}
			setTimeout(function(){
				if(sessionStorage.getItem("curfilter")!=undefined){
					//$(".btnfiltershome").removeClass("btn-success");
					var fl=sessionStorage.getItem("curfilter");
					if(fl!="all"){
						$(".data-filtrable").hide();
						$(fl).show();
						$("button[data-filter='"+fl+"']").addClass("btn-success");
					}else{
						$(".data-filtrable").show();
						$("button[data-filter='"+fl+"']").addClass("btn-success");
						console.log(fl);
					}
					
				}
			},1000);
			
			
		}
	);


function upNumber(control){
	var valor=parseInt($("#"+control).val());
	$("#"+control).val(valor+1);
	$("#"+control).trigger("change");
	
}
function binder(id_mesa,pedido){
	$("#tbl_"+id_mesa).attr("lnk-tsf","#mesa-"+id_mesa);
	$("#tbl_"+id_mesa).attr("lnk-cont","contenidos");
	$("#tbl_"+id_mesa).off();
	$("#tbl_"+id_mesa).attr("onclick",'cargaHTMLvars("contenidos","mviews.php?flag=opentable&id_mesa='+id_mesa+'&id_pedido='+pedido+'")');
	tryhovers();
}

function binderDlg(id_mesa){
	$("#tbl_"+id_mesa).off();
	$("#tbl_"+id_mesa).removeAttr("lnk-tsf");
	$("#tbl_"+id_mesa).removeAttr("lnk-cont");
	$("#tbl_"+id_mesa).click(function(){
		getDialog("mviews.php?flag=create_pedido&id_mesa="+id_mesa,'300','Crear\ Pedido');
		tryhovers();
	});
	
}
function downNumber(control){
	var valor=parseInt($("#"+control).val());
	if(valor>1){
		$("#"+control).val(valor-1);
		$("#"+control).trigger("change");
	}
}

function openTable(id_mesa,id_pedido,op){
	if(op==0){
		if(confirm("Desea abrir un pedido para esta mesa?")){
			var rd=parseInt(Math.random()*999999);
			$("body").append("<div id='dialogBox"+rd+"' style='text-align:center;'><table width='100%'><tr><td colspan='3'>Sillas</td></tr><tr><td><img src='misc/don.png' onclick=\"downNumber('nchairs')\" /></td><td><input type='number' id='nchairs'  min='1' max='20' style='width:40px;height:32px;font-size:20px!important;text-align:center;' value='1' /></td><td><img  src='misc/upn.png' onclick=\"upNumber('nchairs')\" /></td></tr><tr><td colspan='3'><input type='button' class='jq' value='Abrir Pedido' onclick=\"goOpenPedido('"+id_mesa+"','"+rd+"')\" /></td></tr></table></div>");
			$("#dialogBox"+rd+"").dialog({
				autoOpen: true,
				zIndex: zinde,
				title: "Pedido",
				width: 300,
				modal:true,
				show:"fade",
				hide:"fade",
				close: function(){
								closeD(rd);
								}
			   
			   });
			tryhovers();
		}
	}else{
		$.ajax({
			url : "mviews.php?flag=opentable",
			type: "POST",
			data : {id_mesa:id_mesa,id_pedido:id_pedido},
			timeout:5000,
			success: function(data, textStatus, jqXHR)
				{
					$("#contenidos").html(data);
					tryhovers();
				},
				error: function (jqXHR, textStatus, errorThrown)
				{
					saved();
					msgBox("Parece que tienes problemas de conexi&oacute;n: status="+textStatus+"<br />, Puedes reintentar la petici&oacute;n");

				}
		});
	}

}

function erasePedido(id_mesa,id_pedido){
	if(confirm("Se borrar\u00e1 todo el pedido y la mesa quedar\u00e1 disponible, continuar?")){
		$("#state_proceso").load("mviews.php?flag=del_pedido",{id_pedido:id_pedido},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")=="ok"){
				location.reload(true);
			}else{
				alert("Error al borrar");
			}
		});
	}
	
}

function confirmPedido(id_mesa,id_pedido){
	if(confirm("Se el pedido va a ser enviado al CHEF, continuar?")){
		$("#state_proceso").load("mviews.php?flag=confirm_pedido",{id_pedido:id_pedido},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")=="ok"){
				openTable(id_mesa,id_pedido,1);
			}else{
				alert("Error al enviar");
			}
		});
	}
	
}

function confirmPago(id_mesa,id_pedido){
	if(confirm("Se el pedido va a ser enviado al CAJA, continuar?")){
		$("#state_proceso").load("mviews.php?flag=confirm_pago",{id_pedido:id_pedido},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")=="ok"){
				openTable(id_mesa,id_pedido,1);
			}else{
				alert("Error al enviar a caja");
			}
		});
	}
	
}

function checkPlato(id_plato){
	if($("#ch_plato_"+id_plato).attr("sel")==0){
		$("#ch_plato_"+id_plato).attr("sel",1);
		$("#lacheck_"+id_plato).attr("src","misc/chfull.png");
		$("#ch_plato_"+id_plato).addClass("ui-state-error");
	}else{
		$("#ch_plato_"+id_plato).attr("sel",0);
		$("#lacheck_"+id_plato).attr("src","misc/chempty.png");
		$("#ch_plato_"+id_plato).removeClass("ui-state-error");
	}
}

function heWi(div){
	$("#"+div).css("height",$(window).height()-130+"px");
}

function exitTable(){
	location.reload(true);
}


function eraseChair(id_mesa,id_pedido,id_silla){
	$("#state_proceso").load("mviews.php?flag=del_chair",{id_silla:id_silla,id_pedido:id_pedido},function(){
		if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")=="ok"){
			openTable(id_mesa,id_pedido,1);
		}else{
			alert("Error al borrar");
		}
	});
}
function delItem(id_item){
	if(confirm("Borrar elemento, continuar?")){
		$("#state_proceso").load("mviews.php?flag=delitemchair",{id_item:id_item},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")=="ok"){
				$("#tritem_"+id_item).remove();
			}else{
				alert("Error al borrar");
			}
		});
	}
}




function goPideSeleccionados(id_silla,nch,dialogo){
	if($(".losplatos[sel=1]").length>0){
		var losplatos="";
		$(".losplatos[sel=1]").each(function(){
			var pl=$(this).attr("id_pl");
			losplatos+=pl+"|";
		});
		$("#state_proceso").load("mviews.php?flag=additems",{id_silla:id_silla,nch:nch,platos:losplatos},function(){
			closeD(dialogo);
			openChair(id_silla,nch);
			tryhovers();
		})
	}else{
		alert("Seleccione al menos un item");
	}
}

function goOpenPedido(id_mesa,dialogo){
	var nchairs=$("#nchairs").val();
	if(nchairs>0 && nchairs!=undefined){
		$("#state_proceso").load("mviews.php?flag=create_pedido",{id_mesa:id_mesa,nchairs:nchairs},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")!="bad"){
				var id_pedido=$("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"");
				openTable(id_mesa,id_pedido,1);
				closeD(dialogo)
			}else{
				alert("Error al abrir pedido");
			}
		});
	}
}

function toggleCat(id_cat){
	$("#chevy_"+id_cat).toggleClass("fa-chevron-circle-right fa-chevron-circle-down");
	$(".cattie_"+id_cat).toggleClass("hidden");
}