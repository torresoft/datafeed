var slides=null;
var curper=0;
var cuhc=0;
$(document).ready(
		function()
		{
			if($("#getonline").length){
				adjustSession();
				tryhovers();
			}else{
				adjustGeneral();
				$("#menuhelper").click(function(){
					togMenu();
				});
			}
			//--menu
			tryhovers();
		}
	);

var intvgen=setTimeout(function(){return false;},0);
function setCurper(numero){
	curper=numero;
}
$.extend($.expr[':'], {
  'Contains': function(elem, i, match, array)
  {
    return (elem.textContent || elem.innerText || '').toLowerCase()
    .indexOf((match[3] || "").toLowerCase()) >= 0;
  }
});

function filtrarValores(filtro,clasedivs){
	valor=$("#"+filtro).val();
	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor+"')").css("display","block");
		$("."+clasedivs+":not(:Contains('"+valor+"'))").css("display","none");
	}else{
		$("."+clasedivs).css("display","block");	
	}
}

function filtrarTr(filtro,clasedivs){
	valor=$("#"+filtro).val();
	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor+"')").css("display","");
		$("."+clasedivs+":not(:Contains('"+valor+"'))").css("display","none");
	}else{
		$("."+clasedivs).css("display","");	
	}
}
function creaTab(retdiv,activo){
	$("#"+retdiv).tabs({active:activo});
}

function setSelect(clase,objetivo){
	$("."+clase).removeClass("ui-state-highlight");
	$("#"+objetivo).addClass("ui-state-highlight");
}


function goShadows(){
	$(".ui-shadow[ins]").each(function(){
		var wi=$(this).width();
		var he=$(this).height();
		var pos=$(this).css("position");
		var le=$(this).position().left;
		var tof=$(this).position().top;
		if($(this).hasClass("ui-corner-all")){
			var allc="ui-corner-all";
		}else{
			allc="";
		}
		$(this).wrap("<div class='ui-semiwhite "+allc+"' style='width:"+(wi+12)+"px;height:"+(he+12)+"px;position:"+pos+";left:"+(le-5)+"px;top:"+(tof-5)+"px;'></div>");
		$(this).css({position:"absolute",left:"5px",top:"5px"});
		$(this).removeAttr("ins");
	})

}

function adjustSession(){
	var navegleft=(($(window).width()-330)/2);
	var navegtop=(($(window).height()-220)/2);
	$("#getonline").css({top:(navegtop+"px"),left:(+navegleft+"px")});
	$("#getonline").css({width:"330px",height:"180px"});
	$("#getonline").show("fade");
//$("#getonline").glass({distance:40,draga:true,base:navegtop+$("#getonline").height()});
}

function adjustGeneral(){
	var wiwi=$(window).width();
	var hewi=$(window).height();
	$("#header").css({width:wiwi+"px",zIndex:"1000"});
	$("#menuleft").css({zIndex:"1000"});
	$("#sg-desarrollo").css({width:(wiwi-10)+"px",position:"absolute",top:"95px",left:"5px",height:(hewi-100)+"px",zIndex:"1"});
	//$("#getonline").glass({distance:40,draga:true,base:navegtop+$("#getonline").height()});
}

function styleTitles(){
	$("*[alt]").each(function(){
							  	var atrib=$(this).attr("alt");
								var iddi=$(this).attr("id");
								if(iddi!=undefined){
									$(this).removeAttr("alt");
									$(this).removeAttr("title");
									$(this).mouseover(function(e){
															   $(".stalts").remove();
															   var sobrax=$(window).width()-e.pageX;
															   var sobray=$(window).height()-e.pageY;
															   	$("body").append("<div id='alt_"+iddi+"' class='stalts ui-widget ui-state-highlight ui-corner-all' style='position:absolute;max-width:200px;display:none;z-index:100000;'><span class='ui-icon ui-icon-info' style='width:16px;float:left;'></span>"+atrib+"</div>");
																var wi=$("#alt_"+iddi).width();
																var he=$("#alt_"+iddi).height();
																if(sobrax>wi+30){
																	$("#alt_"+iddi).css({left:(e.pageX+10)+"px"});
																}else{
																	$("#alt_"+iddi).css({left:(e.pageX-wi-10)+"px"});
																}
																if(sobray>he+30){
																	$("#alt_"+iddi).css({top:(e.pageY+10)+"px"});
																}else if((e.pageY-he)<0){
																	$("#alt_"+iddi).css({top:"0px"});
																}else{
																	$("#alt_"+iddi).css({top:(e.pageY-he-10)+"px"});
																}
															  	$("#alt_"+iddi).css({display:"block"});
																   
															   });
									$(this).mousemove(function(e){
															   	var sobrax=$(window).width()-e.pageX;
															   	var sobray=$(window).height()-e.pageY;
															   	var wi=$("#alt_"+iddi).width();
																var he=$("#alt_"+iddi).height();
																if(sobrax>wi+30){
																	$("#alt_"+iddi).css({left:(e.pageX+10)+"px"});
																}else{
																	$("#alt_"+iddi).css({left:(e.pageX-wi-10)+"px"});
																}
																if(sobray>he+30){
																	$("#alt_"+iddi).css({top:(e.pageY+12)+"px"});
																}else if((e.pageY-he)<0){
																	$("#alt_"+iddi).css({top:"0px"});
																}else{
																	$("#alt_"+iddi).css({top:(e.pageY-he-12)+"px"});
																}
															   //$("#alt_"+iddi).css({top:(e.pageY+20)+"px",left:(e.pageX+5)+"px"});
															   });
									$(this).mouseout(function(){
															    $("#alt_"+iddi).remove();
															  
															  });
								}


							  });
		
}

function tryhovers(){
	$(".stalts").remove();
	try{$('.ui-state-active').mouseover(function(){
									$(this).removeClass('ui-state-active');
           							$(this).addClass('ui-state-hover');
       							});}catch(e){}
	try{$('.ui-state-active').mouseout(function(){
									$(this).removeClass('ui-state-hover');
            						$(this).addClass('ui-state-active');
        						});}catch(e){}	
	try{$('input.jq').button();}catch(e){}
	try{$('input[type=text]').addClass("txt ui-state-highlight ui-corner-all");}catch(e){}
	try{$('input[type=password]').addClass("txt ui-state-highlight ui-corner-all");}catch(e){}
	$("img.imgpreviewed").each(function(){
										var rdm=parseInt(Math.random()*956566666);
										$(this).mouseover(function(e){
										   var wg=$(this).width()*5;
										   var hg=$(this).height()*5;
										   var tx=parseInt(e.pageX);
										   var ty=parseInt(e.pageY);
										   var srd=$(this).attr("src");
										   $("body").append("<img id='img_prev_"+rdm+"' class='ui-widget-content ui-corner-all link' src='"+srd+"' width='"+wg+"' height='"+hg+"' style='position:absolute;left:"+tx+"px;top:"+ty+"px;z-index:999999999999999;display:none;' onclick=\"rmdom('img_prev_"+rdm+"')\" />");
										   $("#img_prev_"+rdm).show("fade");
										});
										$(this).mousemove(function(e){
														$("#img_prev_"+rdm).css({left:e.pageX+4+"px",top:e.pageY+4+"px"});		   
										});
										$(this).mouseout(function(){
														$("#img_prev_"+rdm).hide("fade",function(){$(this).remove()});		   
										});
	});
	//$('select').selectmenu({style:'dropdown'});
	styleTitles();
	$(".sorta:not([instanced])").each(function(){
		var laiddiv=$(this).attr("id");
		genericSort(laiddiv);
		$("#"+laiddiv).attr("instanced","1");
	});
	goShadows();
	searchSelects();
	setTimeout(function(){
		$("div.nice:not([instanced])").each(function(){
			$(this).niceScroll();
			$(this).attr("instanced","1");
		});
	},1000);	/*$("select:not([init]):not([noui])").each(function(){$(this).selectmenu({style:'dropdown',maxHeight:150});
															   $(this).attr("init","1");
															   });*/
}

function showWait(mensaje){
	tempowaiting=0;
	if(mensaje==undefined){
		mensaje='Cargando...';
	}
	var le=($(window).width()/2)-110;
	var to=($(window).height())/2;
	$("#wait_bar").css("display","block");
	$("#imgload").css({position:"absolute",top:to+"px",left:le+"px"});
	$("#msgload").css({position:"absolute",top:(to+20)+"px",left:(le)+"px"});
	$("#msgload").html(mensaje);
	
}

function hideWait(){
	$("#msgload").html("Hecho!...");
	$("#wait_bar").css("display","none");
	$("#reloadwaiting").css("display","none");
	tempowaiting=0;
	$("#msgload").html("Cargando...");
}


function addCalendario(campo){
	$("#"+campo).datepicker({});
}

function addTimePick(divin){
	$("#"+divin).timepicker({});
}

function addDateTimePick(divin){
	$("#"+divin).datetimepicker({});
}

function setTable(){
	try{$("table.ui-widget").dataTable.fnDestroy()}catch(e){}
	try{
		$("table.datatables").dataTable({
						bJQueryUI: true,
						sPaginationType: "full_numbers",
						bRetrieve:true,
						aaSorting: [],
						bDestroy:true
						});

		}catch(e){
		
		}
}

function distroy(divin){
	$("#"+divin).remove();
	
}

function searchSelects(){
	$("select[id].search:not([ins])").each(function(){
		if($(this).attr("readonly")==undefined){
			var laid=$(this).attr("id");
			$(this).attr("ins","1");
			var wi = $(this).attr("w");
			if(wi==undefined){
				wi=250;
			}
			var lbl=$(this).attr("lbl");
			if(lbl!="" && lbl!=undefined){
				var lb="<td>"+lbl+"</td>";
			}else{
				var lb="";
			}
			var rd=parseInt(Math.random()*999999);
			$(this).css("width",(wi-2)+"px")
			$(this).wrap("<div id='sel_"+rd+"' style='width:"+wi+"px;height:40px;position:relative;float:left;margin-right:30px;'><span style='width:"+wi+"px;height:20px;'>"+lb+"</span><img id='laimsea_"+laid+"' src='misc/searchselect.png' alt='Buscar Opción' title='Buscar opción' class='laimsea ui-state-active ui-corner-all' onclick=\"searchOption('"+laid+"')\" style='position:absolute;right:-20px;top:17px;' /></div>");
			//$(this).mouseover(function(){$(".laimsea").css("display","none")});
			//$(this).mouseover(function(){$("#laimsea_"+laid).css("display","")});
		}
		
	});
}


function searchOption(control,callback){
	var rd=parseInt(Math.random()*999999);
	$("body").append("<div id='dialogBox"+rd+"'><input type='text' id='srch_"+rd+"' placeholder='busca' style='width:300px;' onkeyup=\"filtrarValores('srch_"+rd+"','lasopciones"+rd+"')\" /><div id='srchdiv_"+rd+"' class='ui-widget ui-widget-content ui-corner-all' style='width:300px;height:250px;overflow-x:hidden;overflow-y:auto;'></div></div>");
	$("#"+control+" option").each(function(){
		var val=$(this).val();
		if(val!=""){
			$("#srchdiv_"+rd).append("<div id='optval' class='lasopciones"+rd+" ui-state-active' style='width:98%;margin:2px;' onclick=\"searchOptionSet('"+control+"','"+val+"','"+rd+"','"+callback+"')\">"+$(this).html()+"</div>");
		}
	});
	$("#dialogBox"+rd).dialog({
		autoOpen: true,
		zIndex: zinde,
		title: "Buscar",
		width: 330,
		modal:true,
		show:"fade",
		hide:"fade",
		close: function(){
						closeD(rd);
						}
	   
	});
	tryhovers();
}
function searchOptionSet(control,val,rd,callback){
	$("#"+control+" option").removeAttr("selected");
	$("#"+control+" option[value="+val+"]").attr("selected","selected");
	closeD(rd);
	eval(callback);
	$("#"+control).trigger("change");
}

function togItem(item,dimension,orientacion){
	if(orientacion=="vertical"){
		if($("#"+item).height()==0){
			$("#"+item).animate({height:dimension+"px"});
			$("#"+item).css("z-index","99999999999999999999");
			$("#"+item).parent().css("z-index","99999999999999999999");
			$("#"+item+"_arrow").removeClass("ui-icon-arrowthick-1-s");
			$("#"+item+"_arrow").addClass("ui-icon-arrowthick-1-n");
		}else{
			$("#"+item).animate({height:"0px"});
			$("#"+item).css({zIndex:10});
			$("#"+item+"_arrow").removeClass("ui-icon-arrowthick-1-n");
			$("#"+item+"_arrow").addClass("ui-icon-arrowthick-1-s");
		}
	}else{
		if($("#"+item).width()==0){
			$("#"+item).animate({width:dimension+"px"});
			$("#"+item).css("z-index","99999999999999999999");
			$("#"+item).parent().css("z-index","99999999999999999999");
			$("#"+item+"_arrow").removeClass("ui-icon-arrowthick-1-e");
			$("#"+item+"_arrow").addClass("ui-icon-arrowthick-1-w");
		}else{
			$("#"+item).animate({width:"0px"});
			$("#"+item).css({zIndex:10});
			$("#"+item+"_arrow").removeClass("ui-icon-arrowthick-1-w");
			$("#"+item+"_arrow").addClass("ui-icon-arrowthick-1-e");
		}
	}
}

function cargaHTMLvars(divin,url,callback)
{
	var x=$("#"+divin);
	urlfinal=url.replace(/ /gi, "%20");
	showWait("Cargando");
	x.load(urlfinal,function(){
							setTable();
							try{
								tryhovers()
								if(callback!=undefined){
									eval(callback);	
								}
							}catch(e){}
							goAreas();
							hideWait();
							});
	
}

function onOff(cbox,target_cls){
	if($("#"+cbox+":checked").length>0){
		$("."+target_cls).removeAttr("disabled");
		$("."+target_cls).removeAttr("readonly");
	}else{
		$("."+target_cls).attr("disabled","disabled");
		$("."+target_cls).attr("readonly","readonly");
		$("input."+target_cls).val("");
		$("select."+target_cls+" option").removeAttr("selected");
	}
}

function getArea(textarea){
	try{
		if (CKEDITOR.instances[textarea]) {
			CKEDITOR.instances[textarea].destroy(true);
		}
	}catch(e){
	}
	$("#"+textarea).ckeditor(function(){});
}

function jqArea(textarea){
	if(CKEDITOR.instances[textarea]){
		delete CKEDITOR.instances[textarea];
		$("#"+textarea).ckeditor(function(){});
	}else{
		$("#"+textarea).ckeditor(function(){});
	}
}

function saveVal(textarea){
	document.getElementById(textarea).value=CKEDITOR.instances[textarea].getData(); 
	alert("campo actualizado!");
}

function goAreas(){
	$("textarea.wysiwyg").each(function(){
							var iditem=$(this).attr("id");
							jqArea(iditem);
							});
	
}

function goAreasSmp(){
	$("textarea.wysiwyg_simple").each(function(){
							var iditem=$(this).attr("id");
							jqAreaSmp(iditem);
							});
	
}
function jqAreaSmp(textarea){
	if(CKEDITOR.instances[textarea]){
		delete CKEDITOR.instances[textarea];
		$("#"+textarea).ckeditor({toolbar:"Ssimple"},function(){});
	}else{
		$("#"+textarea).ckeditor({toolbar:"Ssimple"},function(){});
	}
}
function msgBox(mensaje,title,icono,tam)
{
	if(title==undefined){
		title="SGD";	
	}else{
		title="SGD - "+title;	
	}
	if(tam==undefined){
		tam=550;	
	}
	var ico="";
	if(icono=="error"){
		ico="misc/stopdlg.png";
	}else if(icono=="quest"){
		ico="misc/questiondlg.png";
	}else{
		ico="misc/infodlg.png";
	}
	var rd=parseInt(Math.random()*999999);
	$("body").append("<div id='dialogBox"+rd+"'><table><tr><td><img src='"+ico+"' /></td><td>"+mensaje+"</td></tr></table></div>");
	$("#dialogBox"+rd).dialog({
							autoOpen: true,
							zIndex: zinde,
							title: title,
							width: tam,
							modal:true,
							show:"fade",
							hide:"explode",
							buttons: [ { text: "Aceptar", click: function() { closeD(rd); } } ],
							close: function(){
											closeD(rd);
											}
						   });
}

//FAC

function loadFCA(activa){
	showWait("Cargando componentes");
	$("#sg-desarrollo").load("fc_arch_main.php",function(){
		recMenu();
		tryhovers();
		hideWait();
		creaTab("module-hc",activa);
	})
}


//hc

function loadHCA(activa){
	showWait("Cargando componentes");
	$("#sg-desarrollo").load("hc_arch_main.php",function(){
		recMenu();
		tryhovers();
		hideWait();
		creaTab("module-hc",activa);
		$("#bshca").buttonset();
	})
}