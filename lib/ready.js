var slides=null;
var zinde=10000;
var curper=0;
var cuhc=0;
var srchinih=0;
var indexLoc=null;
var forwarding=0;
var arcols={0:"#FF0000",10:"#FF0000",20:"#FF4000",30:"#FF4000",40:"#FF8000",50:"#FFBF00",60:"#FFFF00",70:"#BFFF00",80:"#80FF00",90:"#40FF00",100:"#01DF01"};
sessionStorage.setItem("geoloc",-1);
collections=null;
var route=[];
NProgress.start();
$(document).ready(
		function()
		{
			setTable();
			$("#wait_bar").fadeOut(300,function(){$("#loaderomg").css("visibility","hidden");});
			NProgress.done();
			if(window.location.hash!="" && window.location.hash!="#"){
				if(window.location.hash) {
					if(route[window.location.hash]!=undefined){
						if(route[window.location.hash]["link"]!=undefined){
							eval(route[window.location.hash]["link"]);
						}else{
							togHome();
						}
					}else{
						togHome();
					}
				}
			}
			tryhovers();
			if($("#callbackeval").length>0){
				var vlee=$("#callbackeval").val();
				
				if($("#callbackeval").attr("lnk-tsf")!=undefined){
					var ruta=$("#callbackeval").attr("lnk-tsf");
					var contes=$("#callbackeval").attr("lnk-cont");
					route[ruta]=[];
					route[ruta]["link"]=vlee;
					route[ruta]["cont"]=contes;
					window.location.hash=ruta;
					eval(vlee);
				}else{
					eval(vlee);
				}
				$("#callbackeval").remove();
				//alert(vlee);
			}

			inter=setInterval(function(){
				if($("#boxviewbox").length>0){
					cargaHTMLvars('contenidos','Admin/site_box.php?flag=fact_start');
				}
			},15000);

			inter2=setInterval(function(){
				cargaHTMLvars('state_proceso','kal.php');
			},150000);

		}
	);

$.extend($.expr[':'], {
  'Contains': function(obj, index, meta, stack){ return accentFold((obj.textContent || obj.innerText || jQuery(obj).text() || '').toLowerCase()).indexOf(accentFold(meta[3].toLowerCase())) >= 0; }
});

function reloaHash(){
	if(window.location.hash!="" && window.location.hash!="#"){
		if(window.location.hash) {
			if(route[window.location.hash]!=undefined){
				if(route[window.location.hash]["link"]!=undefined){
					eval(route[window.location.hash]["link"]);
				}
			}
		}
	}
}

function routeRegister(){
	$("a[lnk]").each(function(){
		var haxhe=$(this).attr("href");
		var fn=$(this).attr("lnk");
		route[haxhe]=[];
		route[haxhe]["link"]="getAux(\'"+fn+"\')";
		route[haxhe]["cont"]="contenidos";
	});
	
}

var ajaxData = function(endpoint,querytype,params,timeou){
	if(timeou==undefined) timeou=5000;
	if(params==undefined) params={};
	return new Promise(function(resolve, reject) {
		var _self=this;
		$.ajax({
			url : endpoint,
			type: querytype,
			data : params,
			timeout:timeou,
			headers:{"authorization":'Bearer ' + localStorage.getItem("ts_token")},
			success: function(data, textStatus, jqXHR)
			{
				_self.resolve(data);
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
				_self.reject("error");
			}
		});
	});
}




var ajaxDataPHP = function(endpoint,querytype,params,timeou){
	if(timeou==undefined) timeou=5000;
	if(params==undefined) params={};
	return new Promise(function(resolve, reject) {
		$.ajax({
			url : endpoint,
			type: querytype,
			data : params,
			timeout:timeou,
			headers:{"authorization":'Bearer ' + localStorage.getItem("ts_token")},
			success: function(data, textStatus, jqXHR)
			{
				resolve(data);
			},
			error: function (jqXHR, textStatus, errorThrown)
			{
				reject("error");
			}
		});
	});
}



function accentFold(inStr) {
  return inStr.replace(/([àá])|([ç])|([èé])|([ìí])|([ñ])|([òó])|([ß])|([ùú])|([ÿ])|([æ])/g, function(str,a,c,e,i,n,o,s,u,y,ae) { if(a) return 'a'; else if(c) return 'c'; else if(e) return 'e'; else if(i) return 'i'; else if(n) return 'n'; else if(o) return 'o'; else if(s) return 's'; else if(u) return 'u'; else if(y) return 'y'; else if(ae) return 'ae'; });
}

function goHome(){
	$("#contenidos").removeClass("hidden");
	$("#contenidos-aux").addClass("hidden");
}

function showPosition2(position) {
	//alert(position.coords.latitude);
	$("#__lat__").val(position.coords.latitude);
	$("#__lng__").val(position.coords.longitude);
}
window.onhashchange = function(e) {
	
	if(window.location.hash!="" && window.location.hash!="#" && window.location.hash!="#home"){
		if(window.location.hash) {
			if(route[window.location.hash]["link"]!=undefined){
				//console.log(route[window.location.hash]);
				var container=route[window.location.hash]["cont"];
				if($("#"+container).length>0){
					//$("#"+container).attr("jash","#"+container);
					eval(route[window.location.hash]["link"]);
					if($("a[href='"+window.location.hash+"']").parent("li.grupopciones").length>0){
						$("li.grupopciones").removeClass("active");
						$("li.grupopciones > a").removeClass("orange");
						$("a[href="+window.location.hash+"]").parent("li.grupopciones").addClass("active");
						$("a[href="+window.location.hash+"]").addClass("orange");
					}
					if(container=="contenidos"){
						$("#contenidos-aux").addClass("hidden");
						$("#contenidos").removeClass("hidden");

						if($(window).width()<=600){
							$('body').removeClass('sidebar-open');
						}
					}else if(container=="contenidos-aux"){
						$("#contenidos-aux").removeClass("hidden");
						$("#contenidos").addClass("hidden");
					}
				}else{
					history.back();
				}
			}
		}
	}else{
		if(window.location.hash=="#home"){
			if($("#contenidos").hasClass("hidden")){
				$("#contenidos").removeClass("hidden");
				$("#contenidos-aux").addClass("hidden");
			}
		}else{
			window.location.hash="";
			togHome();
		}
		
	}
};

var intvgen=setTimeout(function(){return false;},0);
function setCurper(numero){
	curper=numero;
}

function upperText(e){
	$(e.target).val($(e.target).val().toUpperCase());
}

function scrollToo(elemento){
	$('html, body').animate({
        scrollTop: $("#"+elemento).offset().top
    }, 500);
	$('body, #main-container').scrollTo('#'+elemento);
}

var ret=0;

function loader(url,callback){
	getAux(url,callback);
}


function getAux(url,callback,timeou,vl)
{
	urlfinal=url.replace(/ /gi, "%20");

	var rd=parseInt(Math.random()*999999999);
	flagi=true;
	params={rd:rd};
	var comple=true;
	if(vl==undefined || vl==""){
		var validate="unival";
	}else{
		var validate=vl;
	}
	
	$("."+validate).each(function(){
		var nval=$(this).attr("name");
		if($(this).prop("tagName")=="SELECT"){
			var valu=$(this).val();
		}else if($(this).attr("type")=="checkbox"){
			var valu=$(this).prop("checked");
			if(valu==true){valu=1}else{valu=0}
		}else if($(this).attr("type")=="radio"){
			var nme=$(this).attr("name");
			var valu=$("input[name="+nme+"]:checked").val();
			if(valu==undefined){valu=0}
		}else{
			var valu=$(this).val();
		}
		params[[nval]]=valu;

		if($(this).attr("required")!=undefined && valu==""){
			comple=false
		}
	});

	if(timeou==undefined || timeou==""){
		timeou=20000;
	}
	var retry=0;
	if(comple){
		showWait("Cargando");
		var divin="sg-desarrollo-aux";
		$.ajax({
			url : urlfinal,
			type: "POST",
			data : params,
			timeout:timeou,
			tryCount : 0,
			retryLimit : 3,
			dataType: "html",
			success: function(data, textStatus, jqXHR)
				{

				$("#contenidos").html(data);
				try{
					tryhovers()
					if(callback!=undefined && callback!=""){
						eval(callback);
					}
				}catch(e){}
					if($("#callbackhtt").length>0){
					var eldv=$("#callbackhtt").parent().attr("id");
					var str=$("#callbackhtt").val();
					cargaHTMLvars(eldv,str);
				}

				if($("#callbackeval").length>0){
					var vlee=$("#callbackeval").val();
					
					if($("#callbackeval").attr("lnk-tsf")!=undefined){
						var ruta=$("#callbackeval").attr("lnk-tsf");
						var contes=$("#callbackeval").attr("lnk-cont");
						route[ruta]=[];
						route[ruta]["link"]=vlee;
						route[ruta]["cont"]=contes;
						window.location.hash=ruta;
					}else{
						eval(vlee);
					}
					$("#callbackeval").remove();
					//alert(vlee);
				}

				if($("#callbackeval2").length>0){
					var vlee2=$("#callbackeval2").val();
					eval(vlee2);
					$("#callbackeval2").remove();
					//alert(vlee);
				}

				$(".bootree").each(function(){
					$(this).treed();
				});
				$(".bootreefold").each(function(){
					$(this).treed({openedClass:"glyphicon-folder-open",closedClass:"glyphicon-folder-close"});
				});

				hideWait();
				setTable();
				setTimeout(function(){flagi=false},1000);
				ret=0;

				},
			 error : function(xhr, textStatus, errorThrown ) {
				if (xhr.status == 500) {
					hideWait();
					console.log("Parece que tienes problemas de conexi&oacute;n: status="+textStatus+"<br />, Puedes reintentar la petici&oacute;n");
					
				} else {
					hideWait();
					console.log("Parece que tienes problemas de conexi&oacute;n: status="+textStatus+"<br />, Puedes reintentar la petici&oacute;n");
					
				}
			}
		});

	}else{
		alert("Faltan datos");
	}

}



function loadMask(url,callback,timeou,vl)
{
	urlfinal=url.replace(/ /gi, "%20");
	$("#contenidos").addClass("hidden");
	$("#contenidos-aux").removeClass("hidden");
	var rd=parseInt(Math.random()*999999999);
	flagi=true;
	params={rd:rd};
	var comple=true;
	if(vl==undefined || vl==""){
		var validate="unival";
	}else{
		var validate=vl;
	}
	$("."+validate).each(function(){
		var nval=$(this).attr("name");
		if($(this).prop("tagName")=="SELECT"){
			var valu=$(this).val();
		}else if($(this).attr("type")=="checkbox"){
			var valu=$(this).prop("checked");
			if(valu==true){valu=1}else{valu=0}
		}else if($(this).attr("type")=="radio"){
			var nme=$(this).attr("name");
			var valu=$("input[name="+nme+"]:checked").val();
			if(valu==undefined){valu=0}
		}else{
			var valu=$(this).val();
		}
		params[[nval]]=valu;

		if($(this).attr("required")!=undefined && valu==""){
			comple=false
		}
	});

	if(timeou==undefined || timeou==""){
		timeou=20000;
	}
	var retry=0;
	if(comple){
		$.ajax({
			url : urlfinal,
			type: "POST",
			data : params,
			timeout:timeou,
			dataType: "html",
			success: function(data, textStatus, jqXHR)
				{

				$("#contenidos-aux").html(data);
				try{
					tryhovers()
					if(callback!=undefined && callback!=""){
						eval(callback);
					}
				}catch(e){}
					if($("#callbackhtt").length>0){
					var eldv=$("#callbackhtt").parent().attr("id");
					var str=$("#callbackhtt").val();
					cargaHTMLvars(eldv,str);
				}

				if($("#callbackeval").length>0){
					var vlee=$("#callbackeval").val();
					
					if($("#callbackeval").attr("lnk-tsf")!=undefined){
						var ruta=$("#callbackeval").attr("lnk-tsf");
						var contes=$("#callbackeval").attr("lnk-cont");
						route[ruta]=[];
						route[ruta]["link"]=vlee;
						route[ruta]["cont"]=contes;
						route[ruta]["params"]=[];
						window.location.hash=ruta;
					}else{
						eval(vlee);
					}
					$("#callbackeval").remove();
					//alert(vlee);
				}

				if($("#callbackeval2").length>0){
					var vlee2=$("#callbackeval2").val();
					eval(vlee2);
					$("#callbackeval2").remove();
				}

				hideWait();
				setTable();
				setTimeout(function(){flagi=false},1000);
				ret=0;

				},
			 error : function(xhr, textStatus, errorThrown ) {
					console.log("error" + textStatus)
				}
		});

	}else{
		alert("Faltan datos");
	}

}


function validaVar(event,btnSend){
	var text_value = $(event.target).val();
	if (text_value.match(/^[A-Za-z]{1}[A-Za-z0-9_]{4,32}/) && text_value!="")
    {
		var text_value = $(event.target).val();
		$(event.target).css("background-color","white");
		$("#"+btnSend).show();
    }else{
		$(event.target).css("background-color","red");
		$("#"+btnSend).hide();
	}
	
}  


function cargaHTMLvars(divin,url,callback,timeou,vl,nv,waite)
{
	if(divin=="contenidos"){
		$("#contenidos").removeClass("hidden");
		$("#contenidos-aux").addClass("hidden");
	}else if(divin=="contenidos-aux"){
		$("#contenidos-aux").removeClass("hidden");
		$("#contenidos").addClass("hidden");

	}
	urlfinal=url.replace(/ /gi, "%20");
	var rd=parseInt(Math.random()*999999999);
	flagi=true;
	params={rd:rd};
	var comple=true;
	if(vl==undefined){
		var validate="unival";
	}else{
		var validate=vl;
	}
	$("."+validate).each(function(){
		var nval=$(this).attr("name");
		if($(this).prop("tagName")=="SELECT"){
			var valu=$(this).val();
		}else if($(this).attr("type")=="checkbox"){
			var valu=$(this).prop("checked");
			if(valu==true){valu=1}else{valu=0}
		}else if($(this).attr("type")=="radio"){
			var nme=$(this).attr("name");
			var valu=$("input[name="+nme+"]:checked").val();
			if(valu==undefined){valu=0}
		}else{
			var valu=$(this).val();
		}
		params[[nval]]=valu;

		if($(this).attr("required")!=undefined && valu==""){
			comple=false
		}
	});

	if(timeou==undefined || timeou==""){
		timeou=20000;
	}
	if(comple || nv==1){
		saving();
		$.ajax({
			url : urlfinal,
			type: "POST",
			data : params,
			timeout:timeou,
			success: function(data, textStatus, jqXHR)
				{
					
				$("#"+divin).html(data);
				//$("#sg-desarrollo-aux").html(data);
				
				try{
					tryhovers()
					if(callback!=undefined){
						eval(callback);
					}
				}catch(e){}
				if($("#callbackhtt").length>0){
					var eldv=$("#callbackhtt").parent().attr("id");
					var str=$("#callbackhtt").val();
					cargaHTMLvars(eldv,str);
				}

				
				if($("#callbackeval").length>0){
					var vlee=$("#callbackeval").val();
					if($("#callbackeval").attr("lnk-tsf")!=undefined){
						var ruta=$("#callbackeval").attr("lnk-tsf");
						var contes=$("#callbackeval").attr("lnk-cont");
						route[ruta]=[];
						route[ruta]["link"]=vlee;
						route[ruta]["cont"]=contes;
						window.location.hash=ruta;
					}else{
						eval(vlee);
						//console.log(vlee);
					}
					$("#callbackeval").remove();
				}

				if($("#callbackeval2").length>0){
					var vlee2=$("#callbackeval2").val();
					eval(vlee2);
					$("#callbackeval2").remove();
					//alert(vlee);
				}

				$(".bootree").each(function(){
					$(this).treed();
				});
				$(".bootreefold").each(function(){
					$(this).treed({openedClass:"glyphicon-folder-open",closedClass:"glyphicon-folder-close"});
				});

				saved();

				setTable();
				setTimeout(function(){flagi=false},1000);
				ret=0;
				$("#"+divin).addClass("grada");
				$("#"+divin).fadeIn(300,function(){
					$("#testereffect").remove();
				});
				$("#"+divin).removeClass("grada");


				},
				error: function (jqXHR, textStatus, errorThrown)
				{
					saved();
					$("#"+divin).html("Error al procesar la petici&oacute;n");

				}
		});

	}else{
		alert("Faltan datos");
	}
}

function togHome(){
	window.location.hash = "#home";
	
}

function loadHash(elhash){
	window.location.hash = elhash;
}
function togHash(){
	$("#sg-desarrollo").addClass("hidden");
	$("#sg-desarrollo-aux").removeClass("hidden");
	MsgXrS();
}



function filterOption(padre,hijo){
	var valp=$("#"+padre).val();
	$("#"+hijo+" > option").hide();
	$("#"+hijo+" > option[secval="+valp+"]").show();
}

function drop(ev)
{
	ev.preventDefault();
	var dt    = ev.dataTransfer;
	var files = dt.files;
}

function filtrarValores(filtro,clasedivs){
	valor=$("#"+filtro).val();

	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor+"')").css("display","block");
		$("."+clasedivs+":Contains('"+valor+"')").children("i").addClass("glyphicon-minus-sign");
		$("."+clasedivs+":Contains('"+valor+"')").children("i").removeClass("glyphicon-plus-sign");

		$("."+clasedivs+":not(:Contains('"+valor+"'))").css("display","none");
		$("."+clasedivs+":not(:Contains('"+valor+"'))").children("i").removeClass("glyphicon-plus-sign");
		$("."+clasedivs+":not(:Contains('"+valor+"'))").children("i").addClass("glyphicon-minus-sign");
	}else{
		$("."+clasedivs).css("display","block");
		$("."+clasedivs+" > i").addClass("glyphicon-minus-sign");
		$("."+clasedivs+" > i").removeClass("glyphicon-plus-sign");
	}
}


function filtrar(filtro,clasedivs){
	valor=$("#"+filtro).val();
	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor+"')").show(300);
		$("."+clasedivs+":not(:Contains('"+valor+"'))").hide(300);
	}else{
		$("."+clasedivs).show(300);
	}
}



function filterClass(combo,oners){
	$("."+combo).hide();
	$("."+oners).show();
}


function filtrarVerif(k1,k2,clasedivs){
	valor=$("#"+k1).val()+" "+$("#"+k2).val();
	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor.trim()+"')").show();
		$("."+clasedivs+":not(:Contains('"+valor.trim()+"'))").hide();
	}else{
		$("."+clasedivs).show();
	}
}


function filtrarTree(filtro){
	valor=$("#"+filtro).val();
	if(valor!=""){
		$(".pliegue:Contains('"+valor+"')").each(function(){
			$(this).css("display","block");
			if($(this).find(".pliegue:Contains('"+valor+"')").length>0){
				$(this).children("i").addClass("glyphicon-minus-sign");
				$(this).children("i").removeClass("glyphicon-plus-sign");
			}else{
				$(this).children("i").removeClass("glyphicon-minus-sign");
				$(this).children("i").addClass("glyphicon-plus-sign");
			}


		})

		$(".pliegue:not(:Contains('"+valor+"'))").css("display","none");
		$(".pliegue:not(:Contains('"+valor+"'))").children("i").addClass("glyphicon-plus-sign");
		$(".pliegue:not(:Contains('"+valor+"'))").children("i").removeClass("glyphicon-minus-sign");
	}else{
		$(".pliegue").css("display","none");
		$(".pliegue").children("i").removeClass("glyphicon-minus-sign");
		$(".pliegue").children("i").addClass("glyphicon-plus-sign");
		$(".father").css("display","");
		$(".father > i").removeClass("glyphicon-minus-sign");
		$(".father > i").addClass("glyphicon-plus-sign");
	}
}


function refreshadm(){
	location.reload(true);
}

function setSelect(clase,objetivo){
	$("."+clase).removeClass("list-group-item-warning bolder");
	$("."+clase).addClass("list-group-item-default ");
	$("#"+objetivo).removeClass("list-group-item-default");
	$("#"+objetivo).addClass("list-group-item-warning bolder");
}
function setSelectTd(clase,objetivo){
	$("."+clase).removeClass("bg-danger");
	$("#"+objetivo).addClass("bg-danger");
}
function showAllOpt(idcontrol){
	$("#"+idcontrol+" > option").show();
}

function selInterc(clase,objeto){
	$("."+clase).hide();
	$("."+clase).prop("selected",false);
	$("."+objeto).show();
}

function dvInterClass(muestra,esconde){
	$("."+esconde).hide();
	$("."+muestra).show();
}

function setSel(clase,event){
	$("."+clase).removeClass("list-group-item-danger bg-danger");
	$(event.target).addClass("list-group-item-danger bg-danger");
}
function postLevels(n){
	for(var i=n;i<=6;$i++){
		$("#level"+i).html("");
	}

}

function sibErase(targ){
	$("#"+targ).parent().parent().parent().parent().next().html("").next().html("").next().html("").next().html("").next().html("").next().html("");
}

function setSelected(clase,e){
	$("."+clase).removeClass("bg-danger");
	$(e.target).addClass("bg-danger");
}

function setSelectedBt(clase,e){
	$("."+clase).removeClass("btn-danger");
	$(e.target).addClass("btn-danger");
}


function selGrpOn(){
	for(var i=0;i<group_select.length;i++){
		var idcr=group_select[i];
		$("input[iduse="+idcr+"]").prop("checked",true);
	}
}
var group_select=[];
function tmpCheck(control){
	var idcur=$(control).attr("iduse");
	if($(control).prop("checked")==true){
		if(group_select.indexOf(idcur)==-1){
			group_select.push(idcur);
		}
	}else{
		var ind=group_select.indexOf(idcur);
		if(ind!=-1){
			group_select.splice(ind,1);
		}
	}

	$("#selitemsgroup").html("");
	var sels="Selección: ";
	for(var i=0;i<group_select.length;i++){
		var idcr=group_select[i];
		$("input[iduse="+idcr+"]").prop("checked",true);
		sels+="<span class='badge'><small>"+$("#lius_"+idcr+" > small").text().split(" ")[0]+" "+$("#lius_"+idcr+" > small").text().split(" ")[1]+"</small></span> ";
	}
	$("#selitemsgroup").html(sels);
}

function goSelTempGrp(clase,goin,id_ing){
	var idcr=0;
	var usel="";
	for(var i=0;i<group_select.length;i++){
		idcr=group_select[i];
		usel+=group_select[i]+"|";
		$("input."+clase+"[idu="+idcr+"]").prop("checked",true);
		$("input."+clase+"[idu="+idcr+"]").parent().parent().parent().prependTo($("input."+clase+"[idu="+idcr+"]").parent().parent().parent().parent());
		$("input."+clase+"[idu="+idcr+"]").parent().parent().parent().show();
	}
	$("input."+clase+"[idu="+idcr+"]").parent().parent().parent().parent().parent().scrollTop(0);
	if(goin!=undefined && goin!=""){
		$("#state_proceso").load("Admin/special_queries.php?flag=go_tmp_sel",{usel:usel,id_ing:id_ing},function(){

		});
	}
}

function cuentaCheck(clase,contador){

	var sels="";
	var nC=0;
	$("."+clase+":checked").each(function(){
		if($(this).attr("idu")!=undefined){
			nC++;
			var idcr=$(this).attr("idu");
			sels+=$("#lius_"+idcr+" > small").text().split(" ")[0]+" "+$("#lius_"+idcr+" > small").text().split(" ")[1]+", ";
		}
	});

	$("#"+contador).text(nC);
	$("#"+contador).attr("title",sels);
	$("#"+contador).attr("alt",sels);
}
function chkGroups(intestring,ctrl){
	var chester=$(ctrl).prop("checked");
	var usuas=intestring.split(",");
	for(var i=0;i<usuas.length;i++){
		if(usuas[i]!=undefined && usuas[i]!=""){
			$("input[idu="+usuas[i]+"]:not([readonly])").prop("checked",chester);
			$("input[idu="+usuas[i]+"]").parent().parent().parent().prependTo($("input[idu="+usuas[i]+"]").parent().parent().parent().parent());
		}
	}

}

function cleanDiv(div){
	$("#"+div).html("");

}
function dvCont(div,cont){
	$("#"+div).html(cont);

}

function filbyClass(container,clase,control){
	if($("#"+control+":checked").length>0){
		$("#"+container+" > *").hide();
		$("#"+container+" > ."+clase).show();
	}else{
		$("#"+container+" > *").show();
	}
}

function genericSort(divpa){
	if($("#"+divpa).attr("handler")!=undefined){
		
		$("#"+divpa).sortable({ 
			opacity: 0.6,
			'ui-floating': true,
			handle:"."+$("#"+divpa).attr("handler"),
			helper:"clone",
			containment: "parent",
			tolerance: "pointer",
			update:function(){
				updateListGeneric(divpa);
			}});
	}else{
		$("#"+divpa).sortable({ 
			opacity: 0.6,
			containment: "parent",
			tolerance: "pointer",
			helper:"clone",
			update:function(){
				updateListGeneric(divpa);
			}});
	}

}
function updateListGeneric(divpa){
	var tbl=$("#"+divpa).attr("tbl");
	var ky=$("#"+divpa).attr("ky");
	var cmp=$("#"+divpa).attr("cmp");
	var arreglo="";
	$("#"+divpa).children("*[ky]").each(function(){
			 	arreglo+= $(this).attr("ky")+"|";
			 });
	var x=$("#state_proceso");
	x.load("Admin/get_lst.php?tabla="+tbl+"&key="+ky+"&cmp="+cmp+"&order="+arreglo);
}


function tryhovers(){
	$(".sorta:not([instanced])").each(function(){
		var laiddiv=$(this).attr("id");
		genericSort(laiddiv);
		$("#"+laiddiv).attr("instanced","1");
	});

	$('[data-toggle="tooltip"]').tooltip({container: 'body',trigger : 'hover'});
	$(".stoppa").click(function(event) {
		event.stopPropagation();
	});
	$('.emescroll').each(function(){
		if($(this).attr("size-scroll")==undefined){
			var sizescroll=300;
		}else{
			var sizescroll=$(this).attr("size-scroll");
		}
		$(this).ace_scroll({
			size: sizescroll
		});
	});
	

	
	$(".unk").unbind("click");
	$(".unk").click(function(e){
		e.preventDefault();
		$(this).parent().trigger("click");
		return false;
	});
	
	
	$("*[killerson]").each(function(){
		if($("."+$(this).attr("killerson")).length>0){
			$(this).hide();
		}
	});
	
	$("*[onclick]:not([hidefocus])").each(function(){
		var fn=$(this).attr("onclick");
		$(this).removeAttr("onclick");
		if($(this).attr("lnk-tsf")!=undefined){
			var contenedor=$(this).attr("lnk-cont");
			route[$(this).attr("lnk-tsf")]=[];
			route[$(this).attr("lnk-tsf")]["link"]=fn;
			route[$(this).attr("lnk-tsf")]["cont"]=contenedor;
			$(this).on("click",function(event){
				window.location.hash=$(this).attr("lnk-tsf");
			});
		}else{
			$(this).on("click",function(event){
				eval(fn)
			});
		}
		
		
	});

	$("*[data-filter]").click(function(){

		var fl=$(this).attr("data-filter");
		if(fl!="all"){
			$(".data-filtrable").hide();
			$(fl).show();
			sessionStorage.setItem("curfilter",fl);
			$(".btnfiltershome").removeClass("btn-success");
			$(this).addClass("btn-success");
		}else{
			$(".data-filtrable").show();
			sessionStorage.setItem("curfilter",fl);
			$(".btnfiltershome").removeClass("btn-success");
			$(this).addClass("btn-success");
		}

	});
	$("input.iquicksy_uya").each(function(){
		var iduqk=$(this).val();
		var nmuqk=$("#lius_"+iduqk+" > small > span").html();
		if(nmuqk!=undefined){
			$(this).replaceWith("<span>"+nmuqk+" </span>");
		}
	})
	$("input.iquicksy_uya_avt").each(function(){
		var iduqk=$(this).val();
		var imgthis=$("#avtus_"+iduqk).clone();
		$(this).replaceWith(imgthis);
	})

	$(".btn").addClass("material-ripple");
	$("a").addClass("material-ripple");
	$(".tritems_cats").addClass("material-ripple");

	$(".panel-heading > h4").parent().addClass("material-ripple");
	$("input.uppercase").on("keyup",function(){
		$(this).val($(this).val().toUpperCase())
	});
	$("li.list-group-item").addClass("material-ripple");
	
	$(".knob").knob();
	$(".knob").children().off('mousewheel DOMMouseScroll');

	// $(".material-ripple").click(function(a){$(".material-ink.animate").remove();var i=$(this);0==i.find(".material-ink").length&&i.prepend("<div class='material-ink'></div>");var t=i.find(".material-ink");if(t.removeClass("animate"),!t.height()&&!t.width()){var e=Math.max(i.outerWidth(),i.outerHeight());t.css({height:e,width:e})}var r=a.pageX-i.offset().left-t.width()/2,h=a.pageY-i.offset().top-t.height()/2,l=i.data("ripple-color");t.css({top:h+"px",left:r+"px",background:l}).addClass("animate")});

	//searchSelects();
	$('form input.form-control[type=text]').keydown(function(event){
		if(event.keyCode == 13) {
		  event.preventDefault();
		  return false;
		}
	});
	$('[data-rel=popover]').popover({container:'body',html:true});

	$('.easy-pie-chart.percentage').each(function(){
		var $box = $(this).closest('.infobox');
		var barColor = $(this).data('color') || (!$box.hasClass('infobox-dark') ? $box.css('color') : 'rgba(255,255,255,0.95)');
		var trackColor = barColor == 'rgba(255,255,255,0.95)' ? 'rgba(255,255,255,0.25)' : '#E2E2E2';
		var size = parseInt($(this).data('size')) || 50;
		$(this).easyPieChart({
			barColor: barColor,
			trackColor: trackColor,
			scaleColor: false,
			lineCap: 'butt',
			lineWidth: parseInt(size/10),
			animate: /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase()) ? false : 1000,
			size: size
		});
	});
	$("td.disabled").off();
	
	$("._counter_").each(function(){
		var dtatye=$(this).attr("data-counter");
		$(this).text($("."+dtatye).length);
		//console.log($("."+dtatye).length);
	})
	$("._adder_").each(function(){
		var dtatye=$(this).attr("data-adder");
		var adder=$(this);
		var suma=0;
		$("."+dtatye).each(function(){
			var nitem=parseInt($(this).text());
			suma+=nitem;
			if(isNaN(suma)){
				suma=0;
			}
			adder.text(suma);
		});
		
	})
	$("._imitter").each(function(){
		var padre=$(this).attr("id");
		var hijos=$(this).attr("sons");
		$(this).click(function(){
			imitaCheck(padre,hijos);
			eval($("#"+padre).attr("fn"));
		});
	})
	
	
	
	//listenTitler();
	routeRegister();
}
function listenTitler(){
	var suma=0;
	$(".countertittlerr").each(function(){
		var nitem=parseInt($(this).text());
		if(isNaN(nitem)){nitem=0}
		suma+=nitem;
		if(isNaN(suma)){
			suma=0;
		}
	});
}
function addCls(id,remc,addc){
	$("#"+id).removeClass(remc);
	$("#"+id).addClass(addc);
}

function conteo(n){
	var xx=parseInt(($(window).width()/2)-75);
	var yy=parseInt(($(window).height()/2)-75);
	$("#conteocount").css({display:"block",position:"fixed",top:yy+"px",left:xx+"px"});
	$("#conteocount").html("<table class='table'><tr><td align='center'>"+n+"</td></tr></table>");
}

function showWait(mensaje){
	NProgress.start();

}

function hideWait(){
	NProgress.done();
}


function getDialog(url,tam,title,callback,params,callclose,unv,rd)
{
	if(title==undefined){
		title="Torresoft";
	}else{
		title="Torresoft - "+title;
	}
	if(tam==undefined){
		tam=500;
	}
	if(tam>=600){
		var classmod="modal-lg";
	}else if(tam<200){
		var classmod="modal-sm";
	}else{
		var classmod="";
	}
	showWait("Cargando");
	urlfinal=url.replace(/ /gi, "%20");
	if(rd==undefined){
		var rd=parseInt(Math.random()*999999);
	}
	
	if(params==undefined || params==""){
		params={rd:rd};
	}
	if(unv==undefined || unv==""){
		unv="univaldlg";
	}

	$("."+unv).each(function(){
		var nval=$(this).attr("name");
		if($(this).prop("tagName")=="SELECT"){
			var valu=$(this).val();
		}else if($(this).attr("type")=="checkbox"){
			var valu=$(this).prop("checked");
			if(valu==true){valu=1}else{valu=0}
		}else if($(this).attr("type")=="radio"){
			var nme=$(this).attr("name");
			var valu=$("input[name="+nme+"]:checked").val();
			if(valu==undefined){valu=0}
		}else{
			var valu=$(this).val();
		}
		params[[nval]]=valu;
	})
	$("body").append("<div class='modal fade'  data-backdrop='static' id='Modal_"+rd+"'><div class='modal-dialog "+classmod+"'><div class='modal-content'><div class='modal-header'><input type='hidden' class='moddldl' name='moddldl' /><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button><h4 class='modal-title'>"+title+"</h4></div><div class='modal-body' id='ModalContent_"+rd+"'></div><div class='modal-footer'></div></div></div></div>");
	$('#Modal_'+rd).on('hidden.bs.modal', function () {
		tryhovers();
	})

	$.ajax({
	type: 'POST',
	url: urlfinal+"&rnd="+rd,
	data: params,
	timeout:30000,
	success: function(data){
		$('#ModalContent_'+rd).html(data);
		$("#Modal_"+rd).modal({
			backdrop: 'static',
			keyboard: false
		});
		$("#Modal_"+rd).on('hidden.bs.modal', function (e) {
			$("#Modal_"+rd).remove();
			setTimeout(function(){
				if($("input.moddldl").length>0){
					$("body").addClass("modal-open");
				}
			},500);
			eval(callclose);
		})
		tryhovers();
		setTable();
		goAreas();
		if($("#chart_div").length>0){
			//drawLineChartdrawLineChart();
		}
		setTimeout(function(){
			eval(callback);
		},500);
		hideWait();
		if($("#callbackevaldlg").length>0){
			var vlee=$("#callbackevaldlg").val();
			
			if($("#callbackevaldlg").attr("lnk-tsf")!=undefined){
				var ruta=$("#callbackevaldlg").attr("lnk-tsf");
				var contes=$("#callbackevaldlg").attr("lnk-cont");
				route[ruta]=[];
				route[ruta]["link"]=vlee;
				route[ruta]["cont"]=contes;
				window.location.hash=ruta;
				eval(vlee);
			}else{
				eval(vlee);
			}
			$("#callbackevaldlg").remove();
			//alert(vlee);
		}

	},
	error:function(){
		console.log("Dialog Error");
	}
	});


}


function closeD(rd){
	$("#Modal_"+rd).modal( 'hide' ).data( 'bs.modal', null );
	$("#Modal_"+rd).on('hidden.bs.modal', function () {
		$(this).data('bs.modal', null);

	});
	setTimeout(function(){
		if($("input.moddldl").length>0){
			$("body").addClass("modal-open");
		}
		$(".tooltip").remove();
	},500);
	tryhovers();
}

function msgBox(texto,rd,title,tam,callback,callclose)
{
	if(title==undefined){
		var title="Torresoft";
	}
	if(rd==undefined){
		var rd=parseInt(Math.random()*999999);
	}
	if(tam==undefined){
		var classtam="";
	}else{
		if(tam<500){
			classtam="modal-sm";
		}else if(tam>500 && tam<1000){
			classtam="modal-md";
		}else{
			classtam="modal-lg";
		}
	}
	$("body").append("<div class='modal fade' id='Modal_"+rd+"'><div class='modal-dialog "+classtam+"'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button><h4 class='modal-title'>"+title+"</h4></div><div class='modal-body' id='ModalContent_"+rd+"'>"+texto+"<input type='hidden' id='modalboxid' value='"+rd+"' /></div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button></div></div></div></div>");
	setTimeout(function(){
		eval(callback);
	},1000);
	$("#Modal_"+rd).modal();
	$("#Modal_"+rd).on('hidden.bs.modal', function () {
		setTimeout(function(){
			eval(callclose);
		},100);

		$(this).data('bs.modal', null);
		$("#Modal_"+rd).remove();

	});

}

function togItems(iditems){
	var its=iditems.split(",");
	for(var i=0;i<its.length;i++){
		$("#"+its[i]).slideToggle(200);
		$("."+its[i]).slideToggle(200);
	}
}


function togClass(event,clase,callback){
	$(event.target).toggleClass(clase);
	eval(callback);
}



function togMask(){
	$("#sg-desarrollo").addClass("hidden");
	$("#sg-desarrollo-aux").removeClass("hidden");
	$("#sg-desarrollo-mask").addClass("hidden");
}

function closeBox(e){
	$(e.target).parents(".modal.fade").modal().hide();
	tryhovers();
}

function trigClick(clase){
	$("."+clase).each(function(){$(this).trigger("click");});
}
function trigCl(clase,timeout){
	if(timeout==undefined){timeout=100}
	setTimeout(function(){
		$("#"+clase).trigger("click");
		
	},timeout);
}

function goForm(id_item,dlg,callback){
	$('form#'+id_item).submit(function(e) {
		e.preventDefault();
		var x=$(document.createElement('div'));
		showWait("Enviando datos...");
		$(this).ajaxSubmit({
			target:   "#state_proceso",
			success:function (){
				var respon=$("#state_proceso").html().replace(/ /gi,"");
				if(respon!="ok"){
					hideWait();
					alert("Error, el servidor dice: "+respon);
				}else{
					hideWait();
					closeD(dlg);

					eval(callback);

				}

			}
			//resetForm: true
		});
		return false;

	});
}

function getFile(controlto,extfilter,folder,callback){
	var rd=parseInt(Math.random()*999999999);
	var url="";
	if(extfilter!=undefined){
		var filtro=$("#"+extfilter).val();
	}else{
		var filtro="";
	}
	if(filtro=="NA"){filtro=""}
	if(filtro==undefined){
		filtro=extfilter;
	}
	if(folder==undefined){
		folder="";
	}
	url="Admin/getfilepw.php?controlto="+controlto+"&extfiltro="+filtro+"&callback="+callback;
	getDialog(url,700);
}

function uploadFile(formu,divin,controlarchivo,rd,controlto,callback){
	$('#'+formu).submit(function(e) {
		if($('#'+controlarchivo).val()) {
			e.preventDefault();
			$('#progress-div').show();
			$(this).ajaxSubmit({
				target:   '#elifmni',
				beforeSubmit: function() {
				  $("#progress-bar").width('0%');
				},
				uploadProgress: function (event, position, total, percentComplete){
					$("#progress-bar").width(percentComplete + '%');
					$("#progress-bar").html('<div id="progress-status">' + percentComplete +' %</div>')
				},
				success:function (){
					var respon=$("#elifmni").html().replace(/ /gi,"");
					if(respon==""){
						$('#progress-div').hide();
						alert("Error no especificado, no se pudo subir el archivo");
					}else if(respon=="-3"){
						$('#progress-div').hide();
						alert("Error, permisos insuficientes en carpeta destino");
					}else if(respon=="-2"){
						$('#progress-div').hide();
						alert("Error, el archivo ya existe en el servidor, \ncambie el nombre o use el existente");
					}else if(respon=="-1"){
						$('#progress-div').hide();
						alert("Error, formato no permitido");
					}else if(respon=="0"){
						$('#progress-div').hide();
						alert("Error, el archivo excede el limite permitido");
					}else{
						$('#progress-div').hide();
						var fold=$("#lacarpeta").val();
						var fil=respon.split("/")[respon.split("/").length-1];
						var exte=fil.split(".")[fil.split(".").length-1];
						var rdup=parseInt(Math.random()*999);
						$(".losfils").removeClass("list-group-item-danger");
						$("#"+divin+" ul").prepend("<li id='filenew_"+rdup+"' class='losfils list-group-item list-group-item-danger link' ondblclick=\"setFilepw('"+controlto+"','filenew_"+rdup+"','"+rd+"','"+callback+"')\" onclick=\"setSelect('losfils',event)\" value='"+fold+fil+"'><img src='iconos/"+exte+".png' style='width:25px;height:25px;' align='left' />"+fil+"</li>");
						$("#"+divin).animate({scrollTop:0},500);
					}

				},
				resetForm: true
			});
			return false;
		}
	});

	$("#"+formu).submit();

	try{
		$("#RUTA_ARCHIVO").val($("#RUTA_ARCHIVO").val()+carpeta+"/"+archivo+";")
	}catch(e){

	}
}
function setFilepw(controlto,filediv,rd,callback){
	var elvalor=$("#"+filediv).attr("value");
	$("#"+controlto).val(elvalor).trigger("change");
	setTimeout(function(){closeD(rd);eval(callback)},200);
}


function goErase(tabla,pkey,valor,ref_element,direct,callback){
	if(confirm("En realidad desea borrar 1 elemento?, \n tambien se borrara todo lo que dependa de este")){
		var urlfinal="Admin/special_queries.php?flag=sdl";
		var rdm=parseInt(Math.random()*999999);
		$("#state_proceso").load(urlfinal,{tb:tabla,ky:pkey,kv:valor,rdm:rdm},function(){
			var rsu=parseInt($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,""));
			if(rsu==1){
				if(callback!=undefined && callback!="null" && callback!=""){
					setTimeout(callback,20);
				}else{
					$("#"+ref_element).fadeOut(700,function(){$(this).remove()});
					$("."+ref_element).fadeOut(700,function(){$(this).remove()});
				}
			}else{
				alert("Error al borrar");
			}

		  });
	}

}



function showPosition(position) {
	sessionStorage.setItem("geoloc",position.coords.latitude + "," + position.coords.longitude);
}


function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            sessionStorage.setItem("geoloc","0,0");
            break;
        case error.POSITION_UNAVAILABLE:
            sessionStorage.setItem("geoloc","0,0");
            break;
        case error.TIMEOUT:
             sessionStorage.setItem("geoloc","0,0");
            break;
        case error.UNKNOWN_ERROR:
            sessionStorage.setItem("geoloc","0,0");
            break;
    }
}


function addPick(campoll,ref){
	showWait("Cargando Mapa...");
	actual=$("#"+campoll).val();
	sessionStorage.setItem("geoloc",-1);
	var dlt=0;
	var dlg=0;
	if(actual!=undefined && actual!=""){
		var dlt=actual.split(",")[0];
		var dlg=actual.split(",")[1];
		sessionStorage.setItem("geoloc",dlt+","+dlg);
		addPick2(campoll,ref);
	}else{
		 if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(showPosition,showError,{timeout:10000});
			var indef=setInterval(function(){
				if(sessionStorage.getItem("geoloc")!=-1){
					$("#"+campoll).val(sessionStorage.getItem("geoloc"));
					addPick2(campoll,ref);
					clearInterval(indef);
				}
			},100);
		}else{
			sessionStorage.setItem("geoloc","0,0");
			addPick2(campoll,ref);
		}
	}

}


function closepick(){
	$("#map_pocker").remove();
}



function setTable(){
	try{$("table.datatables").dataTable.fnDestroy()}catch(e){}
	try{
		$("table.datatables").each(function(){
			var idtabla=$(this).attr("id");
			var colas=[{ "bSortable": false }];
			var i=$("#"+idtabla+" > thead > tr > th").each(function(){
				colas.push(null);
			});
			colas.push({ "bSortable": false });
			var tabla=$("#"+idtabla).dataTable({
							sPaginationType: "full_numbers",
							aaSorting: [],
							bDestroy:true,
							responsive:true,
							bAutoWidth: false,
							"aaSorting": [],
							drawCallback : function() {
							   //processInfo(this.api().page.info(),idtabla);
							},
							"language": {
								"lengthMenu": "Mostrar _MENU_ registros por pagina",
								"zeroRecords": "No hay datos coincidentes",
								"info": "Mostrando pagina _PAGE_ de _PAGES_",
								"infoEmpty": "No hay datos en la tabla",
								"infoFiltered": "(Filtrando de _MAX_ total registros)",
								"sSearch": "Buscar:",
								"sNext": "Sig",
								"sPrevious": "Ant",
								"sFirst": "Primera",
								"sLast": "Ultima",
								"paginate": {
									"previous": "<",
									"next":">",
									"first":"<<",
									"last":">>"
								}
							}
			});

			tabla.on( 'search.dt', function () {
				var flt=$('#'+idtabla+'_filter input').val();
				localStorage.setItem("filtersearch_"+idtabla, flt);
			});

			tabla.on( 'page.dt', function () {
				processInfo(tabla.api().page.info(),idtabla);
				//localStorage.setItem("filtersearch_"+idtabla, flt);
			});


			if(localStorage.getItem(idtabla+"_pageN")){
				tabla.fnPageChange(parseInt(localStorage.getItem(idtabla+"_pageN")));
			}
		})


	}catch(e){

	}
	
	
	
	try{$("table.datatables-simple").dataTable.fnDestroy()}catch(e){}
	try{
		$("table.datatables-simple").each(function(){
			var idtabla=$(this).attr("id");
			var tabla=$("#"+idtabla).dataTable({
				bDestroy:true,
				responsive:true,
				bSortCellsTop:false,
				bFilter:false,
				bPaginate:false,
				bSort:false,
				drawCallback : function() {
				 // $("#"+idtabla+"_wrapper").children("div.row").eq(0).remove();
				}
			});

		})


	}catch(e){

	}
	
}

function processInfo(info,tabla) {
	localStorage.setItem(tabla+"_pageN", info.page);
}

function distroy(divin){
	$("#"+divin).remove();

}

function searchSelects(){
	$("select[id]:not([ins]):not([disabled]).chosen").each(function(){
		if($(this).children("option").length>3){

			$(this).attr("ins","1");
			//$(this).addClass("chosen-select");
			$(this).chosen({disable_search_threshold: 10,no_results_text: "No hay resultados"});
		}
	});
}


function searchOption(control,callback){
	var rd=parseInt(Math.random()*999999);
	$("body").append("<div class='modal fade' id='Modal_"+rd+"'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button><h4 class='modal-title'><input type='text' id='srch_"+rd+"' placeholder='BUSCAR OPCI&Oacute;N' class='form-control' onkeyup=\"filtrarValores('srch_"+rd+"','lasopciones"+rd+"')\" /></h4></div><div class='modal-body' id='ModalContent_"+rd+"'><ul id='srchdiv_"+rd+"' class='list-group'></ul></div><div class='modal-footer'></div></div></div></div>");
	$("#"+control+" option").each(function(){
		var val=$(this).val();
		if(val!=""){
			$("#srchdiv_"+rd).append("<li id='optval' class='lasopciones"+rd+" list-group-item link' onclick=\"searchOptionSet('"+control+"','"+val+"','"+rd+"','"+callback+"')\">"+$(this).html()+"</li>");
		}
	});
	$("#Modal_"+rd).modal();
	$("#Modal_"+rd).on('hidden.bs.modal', function (e) {
		$("#Modal_"+rd).remove();
	});

}
function searchOptionSet(control,val,rd,callback){
	$("#"+control+" option").removeAttr("selected");
	$("#"+control+" option[value="+val+"]").attr("selected","selected");
	closeD(rd);
	eval(callback);
	$("#"+control).trigger("change");
	$("#"+control).trigger("chosen:updated");
}


function waitDiv(div){
	$("#wait_bar").show();
}



function validaSuma(total,aviso){
	var totl=parseInt($("#"+total).val());
	var suma=0;
	$("."+total).each(function(){
		var subt=parseInt($(this).val())
		suma+=subt;
	});
	if(totl==suma){
		$("#"+aviso).addClass("alert-success");
		$("#"+aviso).removeClass("alert-danger");
		$("#"+aviso).html("Distribuci&oacute;n correcta");
	}else{
		$("#"+aviso).html("Distribuci&oacute;n incorrecta <br />Valor a repartir: " + totl + "<br />Suma actual: " + suma);
		$("#"+aviso).removeClass("alert-success");
		$("#"+aviso).addClass("alert-danger");
	}
}


function remDm(id){
	$("#"+id).hide( "slow", function() {
		$(this).remove();
		tryhovers()
	});
}

function remSelector(id){
	$(id).hide( "slow", function() {
		$(this).remove();
	});
}


function stDvC(id,cnt){
	$("#"+id).html(cnt);
}

function clnDvC(ori,des){
	$("#"+des).html($("#"+ori).html());
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
	if($("#"+textarea).attr("h")!=undefined){
		var he=$("#"+textarea).attr("h");
	}else{
		var he=300;
	}
	$("#"+textarea).attr("instanced",1);
	if(CKEDITOR.instances[textarea]){
		delete CKEDITOR.instances[textarea];
		$("#"+textarea).ckeditor(function(TEXTA){

			this.on('blur', function(){
				if(this.checkDirty())$("#"+textarea).trigger("change");
			});
		},{height:he+"px"});
		setTimeout(function(){},2000);

	}else{
		$("#"+textarea).ckeditor(function(TEXTA){

			this.on('blur', function(){
				if(this.checkDirty())$("#"+textarea).trigger("change");
			});
		},{height:he+"px"});
		setTimeout(function(){},2000);
	}
}


function jqAreaSmp(textarea){

	if($("#"+textarea).attr("h")!=undefined){
		var he=$("#"+textarea).attr("h");
	}else{
		var he=300;
	}
	$("#"+textarea).attr("instanced",1);
	if(CKEDITOR.instances[textarea]){
		delete CKEDITOR.instances[textarea];
		$("#"+textarea).ckeditor(function(){
			this.on('blur', function(){
				if(this.checkDirty())$("#"+textarea).trigger("change");
			});
		},{height:he+"px",toolbarGroups:[{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'paragraph',   groups: [ 'list','align', 'bidi' ] },{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },{ name: 'insert', groups: [ 'Table', 'PageBreak' ] },{ name: 'links', groups : [ 'Link','Unlink','Anchor' ] }]});

	}else{
		$("#"+textarea).ckeditor(function(){

			this.on('blur', function(){
				if(this.checkDirty())$("#"+textarea).trigger("change");
			});
		},{height:he+"px",toolbarGroups:[{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'paragraph',   groups: [ 'list','align', 'bidi' ] },{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },{ name: 'insert', groups: [ 'Table', 'PageBreak' ]},{ name: 'links', groups : [ 'Link','Unlink','Anchor' ] }]});
	}


}




function saveVal(textarea){
	document.getElementById(textarea).value=CKEDITOR.instances[textarea].getData();
	alert("campo actualizado!");
}

function goAreas(){
	$("textarea.wysiwyg:NOT([instanced])").each(function(){
							var iditem=$(this).attr("id");
							jqArea(iditem);
							});


	$("textarea.wysiwyg-smp:NOT([instanced])").each(function(){
							var iditem=$(this).attr("id");
							jqAreaSmp(iditem);
							});
}


function goUpTRD(){
	var fl=$("#btupcsv_").attr("nfile");
	showWait();
	$("#content").load("Admin/load_trd.php?flag=gouppromo&fl="+fl,function(){
		$("#btupcsv_").css("display","none");
		hideWait();
	});

}

function goUpTRD2(){
	var fl=$("#btupcsv_").attr("nfile");
	showWait();
	$("#content").load("Admin/load_trd_series.php?flag=gouppromo&fl="+fl,function(){
		$("#btupcsv_").css("display","none");
		hideWait();
	});

}

function uploadCsv(){
	$('#formcsv').submit(function(e) {
		if($('#archivo').val()) {
			e.preventDefault();
			showWait();
			$(this).ajaxSubmit({
				target:   '#state_proceso',
				beforeSubmit: function() {
				  $("#progress-div").attr("aria-valuenow",0);
				},
				uploadProgress: function (event, position, total, percentComplete){
					$("#progress-div").attr("aria-valuenow",percentComplete);
					$("#progress-div").width(percentComplete + '%');
					$("#progress-div").html(percentComplete +' %');
				},
				success:function (){
					hideWait();
					var respon=$("#state_proceso").html().replace(/ /gi,"");
					if(respon==""){
						$('#progress-div').hide();
						alert("Error no especificado, no se pudo subir el archivo");
					}else if(respon=="-3"){
						$('#progress-div').hide();
						alert("Error, permisos insuficientes en carpeta destino");
					}else if(respon=="-2"){
						$('#progress-div').hide();
						alert("Error, el archivo ya existe en el servidor, \ncambie el nombre o use el existente");
					}else if(respon=="-1"){
						$('#progress-div').hide();
						alert("Error, formato no permitido");
					}else if(respon=="0"){
						$('#progress-div').hide();
						alert("Error, el archivo excede el limite permitido");
					}else{
						var fil=respon.split("/")[respon.split("/").length-1];
						$('#formcsv').append("<input type='hidden' class='univl_aa' name='uppedfile' id='uppedfile' value='"+fil+"' />");
						$("#btupcsv_").css("display","");
						$("#formcsv").css("display","none");
						$("#btupcsv_").attr("nfile",fil);

					}

				},
				resetForm: true
			});
			return false;
		}
	});

	$("#formcsv").submit();
}


function delVl(tb,kc,kv,idel){
	if(confirm("Desea elimiar un registro?")){
		showWait("Eliminando");
		var rdm=parseInt(Math.random()*999999);
		$("#state_proceso").load("Admin/special_queries.php?flag=tbl_delete",{tb:tb,ky:kc,vl:kv,rdm:rdm},function(){
			if($("#state_proceso").html().replace(/ /gi,"")=="1"){
				$("#"+idel).remove();
				hideWait();
			}
		});
	}
}

function upVl(tb,kc,kv,cp,cv,callback){
	var valor=$("#"+cv).val();
	saving(cv);
	var rdm=parseInt(Math.random()*999999);
	$("#state_proceso").load("Admin/special_queries.php?flag=tbl_update",{tb:tb,ky:kc,vl:kv,cp:cp,cv:valor,rdm:rdm},function(){
		if($("#state_proceso").html().replace(/ /gi,"")=="1"){
			eval(callback);
			hideWait();
			saved(cv);
		}
	});
}

function saving(control){
	NProgress.start();
}

function saved(control){
	NProgress.done();
}

function nextMonth(control){
	var cury = parseInt($("#"+control).val().split("-")[0]);
	var curm = parseInt($("#"+control).val().split("-")[1]);
	if(curm==12){
		var ne="01";
		cury++;
	}else{
		var nea=curm+1;
		if(nea<10){
			ne="0"+nea;
		}else{
			ne=nea;
		}
	}
	$("#"+control).val(cury+"-"+ne).delay(200).trigger("change");
}

function prevMonth(control){
	var cury = parseInt($("#"+control).val().split("-")[0]);
	var curm = parseInt($("#"+control).val().split("-")[1]);
	if(curm==1){
		var ne="12";
		cury--;
	}else{
		var nea=curm-1;
		if(nea<10){
			ne="0"+nea;
		}else{
			ne=nea;
		}
	}
	$("#"+control).val(cury+"-"+ne).delay(200).trigger("change");
}

function onOffCheck(checkb,clase){
	if($("#"+checkb+":checked").length>0){
		$("."+clase).removeAttr("checked").trigger("click");
	}else{
		$("."+clase).attr("checked","checked").trigger("click");
	}
}

function validaParents(event){
	var plipa=$(event.target).attr("parent");
	if($(".plid_"+plipa+" > i").hasClass("glyphicon-folder-close")){
		$(".plid_"+plipa).trigger("click");
	}
}

function addSon(parente,soni,nome){
	$("#subm_"+parente).append("<li class='pliegue pli_"+soni+"  plid_"+soni+"' parent='"+parente+"' onclick=\"validaParents(event)\"><span class='plieguebt pli_"+soni+"' onclick=\"cargaHTMLvars('loadfolder','Admin/sgd_docsapoyo.php?flag=folder&item="+soni+"');setSelected('plieguebt',event)\">"+nome+"</span><ul id='subm_"+soni+"'></ul></li>");
}

function faIcon(control){
	var licon="";
	var aric=['fa-glass','fa-music','fa-search','fa-envelope-o','fa-heart','fa-star','fa-star-o','fa-user','fa-film','fa-th-large','fa-th','fa-th-list','fa-check','fa-times','fa-search-plus','fa-search-minus','fa-power-off','fa-signal','fa-cog','fa-trash-o','fa-home','fa-file-o','fa-clock-o','fa-road','fa-download','fa-arrow-circle-o-down','fa-arrow-circle-o-up','fa-inbox','fa-play-circle-o','fa-repeat','fa-refresh','fa-list-alt','fa-lock','fa-flag','fa-headphones','fa-volume-off','fa-volume-down','fa-volume-up','fa-qrcode','fa-barcode','fa-tag','fa-tags','fa-book','fa-bookmark','fa-print','fa-camera','fa-font','fa-bold','fa-italic','fa-text-height','fa-text-width','fa-align-left','fa-align-center','fa-align-right','fa-align-justify','fa-list','fa-outdent','fa-indent','fa-video-camera','fa-picture-o','fa-pencil','fa-map-marker','fa-adjust','fa-tint','fa-pencil-square-o','fa-share-square-o','fa-check-square-o','fa-arrows','fa-step-backward','fa-fast-backward','fa-backward','fa-play','fa-pause','fa-stop','fa-forward','fa-fast-forward','fa-step-forward','fa-eject','fa-chevron-left','fa-chevron-right','fa-plus-circle','fa-minus-circle','fa-times-circle','fa-check-circle','fa-question-circle','fa-info-circle','fa-crosshairs','fa-times-circle-o','fa-check-circle-o','fa-ban','fa-arrow-left','fa-arrow-right','fa-arrow-up','fa-arrow-down','fa-share','fa-expand','fa-compress','fa-plus','fa-minus','fa-asterisk','fa-exclamation-circle','fa-gift','fa-leaf','fa-fire','fa-eye','fa-eye-slash','fa-exclamation-triangle','fa-plane','fa-calendar','fa-random','fa-comment','fa-magnet','fa-chevron-up','fa-chevron-down','fa-retweet','fa-shopping-cart','fa-folder','fa-folder-open','fa-arrows-v','fa-arrows-h','fa-bar-chart','fa-twitter-square','fa-facebook-square','fa-camera-retro','fa-key','fa-cogs','fa-comments','fa-thumbs-o-up','fa-thumbs-o-down','fa-star-half','fa-heart-o','fa-sign-out','fa-linkedin-square','fa-thumb-tack','fa-external-link','fa-sign-in','fa-trophy','fa-github-square','fa-upload','fa-lemon-o','fa-phone','fa-square-o','fa-bookmark-o','fa-phone-square','fa-twitter','fa-facebook','fa-github','fa-unlock','fa-credit-card','fa-rss','fa-hdd-o','fa-bullhorn','fa-bell','fa-certificate','fa-hand-o-right','fa-hand-o-left','fa-hand-o-up','fa-hand-o-down','fa-arrow-circle-left','fa-arrow-circle-right','fa-arrow-circle-up','fa-arrow-circle-down','fa-globe','fa-wrench','fa-tasks','fa-filter','fa-briefcase','fa-arrows-alt','fa-users','fa-link','fa-cloud','fa-flask','fa-scissors','fa-files-o','fa-paperclip','fa-floppy-o','fa-square','fa-bars','fa-list-ul','fa-list-ol','fa-strikethrough','fa-underline','fa-table','fa-magic','fa-truck','fa-pinterest','fa-pinterest-square','fa-google-plus-square','fa-google-plus','fa-money','fa-caret-down','fa-caret-up','fa-caret-left','fa-caret-right','fa-columns','fa-sort','fa-sort-desc','fa-sort-asc','fa-envelope','fa-linkedin','fa-undo','fa-gavel','fa-tachometer','fa-comment-o','fa-comments-o','fa-bolt','fa-sitemap','fa-umbrella','fa-clipboard','fa-lightbulb-o','fa-exchange','fa-cloud-download','fa-cloud-upload','fa-user-md','fa-stethoscope','fa-suitcase','fa-bell-o','fa-coffee','fa-cutlery','fa-file-text-o','fa-building-o','fa-hospital-o','fa-ambulance','fa-medkit','fa-fighter-jet','fa-beer','fa-h-square','fa-plus-square','fa-angle-double-left','fa-angle-double-right','fa-angle-double-up','fa-angle-double-down','fa-angle-left','fa-angle-right','fa-angle-up','fa-angle-down','fa-desktop','fa-laptop','fa-tablet','fa-mobile','fa-circle-o','fa-quote-left','fa-quote-right','fa-spinner','fa-circle','fa-reply','fa-github-alt','fa-folder-o','fa-folder-open-o','fa-smile-o','fa-frown-o','fa-meh-o','fa-gamepad','fa-keyboard-o','fa-flag-o','fa-flag-checkered','fa-terminal','fa-code','fa-reply-all','fa-star-half-o','fa-location-arrow','fa-crop','fa-code-fork','fa-chain-broken','fa-question','fa-info','fa-exclamation','fa-superscript','fa-subscript','fa-eraser','fa-puzzle-piece','fa-microphone','fa-microphone-slash','fa-shield','fa-calendar-o','fa-fire-extinguisher','fa-rocket','fa-maxcdn','fa-chevron-circle-left','fa-chevron-circle-right','fa-chevron-circle-up','fa-chevron-circle-down','fa-html5','fa-css3','fa-anchor','fa-unlock-alt','fa-bullseye','fa-ellipsis-h','fa-ellipsis-v','fa-rss-square','fa-play-circle','fa-ticket','fa-minus-square','fa-minus-square-o','fa-level-up','fa-level-down','fa-check-square','fa-pencil-square','fa-external-link-square','fa-share-square','fa-compass','fa-caret-square-o-down','fa-caret-square-o-up','fa-caret-square-o-right','fa-eur','fa-gbp','fa-usd','fa-inr','fa-jpy','fa-rub','fa-krw','fa-btc','fa-file','fa-file-text','fa-sort-alpha-asc','fa-sort-alpha-desc','fa-sort-amount-asc','fa-sort-amount-desc','fa-sort-numeric-asc','fa-sort-numeric-desc','fa-thumbs-up','fa-thumbs-down','fa-youtube-square','fa-youtube','fa-xing','fa-xing-square','fa-youtube-play','fa-dropbox','fa-stack-overflow','fa-instagram','fa-flickr','fa-adn','fa-bitbucket','fa-bitbucket-square','fa-tumblr','fa-tumblr-square','fa-long-arrow-down','fa-long-arrow-up','fa-long-arrow-left','fa-long-arrow-right','fa-apple','fa-windows','fa-android','fa-linux','fa-dribbble','fa-skype','fa-foursquare','fa-trello','fa-female','fa-male','fa-gittip','fa-sun-o','fa-moon-o','fa-archive','fa-bug','fa-vk','fa-weibo','fa-renren','fa-pagelines','fa-stack-exchange','fa-arrow-circle-o-right','fa-arrow-circle-o-left','fa-caret-square-o-left','fa-dot-circle-o','fa-wheelchair','fa-vimeo-square','fa-try','fa-plus-square-o','fa-space-shuttle','fa-slack','fa-envelope-square','fa-wordpress','fa-openid','fa-university','fa-graduation-cap','fa-yahoo','fa-google','fa-reddit','fa-reddit-square','fa-stumbleupon-circle','fa-stumbleupon','fa-delicious','fa-digg','fa-pied-piper','fa-pied-piper-alt','fa-drupal','fa-joomla','fa-language','fa-fax','fa-building','fa-child','fa-paw','fa-spoon','fa-cube','fa-cubes','fa-behance','fa-behance-square','fa-steam','fa-steam-square','fa-recycle','fa-car','fa-taxi','fa-tree','fa-spotify','fa-deviantart','fa-soundcloud','fa-database','fa-file-pdf-o','fa-file-word-o','fa-file-excel-o','fa-file-powerpoint-o','fa-file-image-o','fa-file-archive-o','fa-file-audio-o','fa-file-video-o','fa-file-code-o','fa-vine','fa-codepen','fa-jsfiddle','fa-life-ring','fa-circle-o-notch','fa-rebel','fa-empire','fa-git-square','fa-git','fa-hacker-news','fa-tencent-weibo','fa-qq','fa-weixin','fa-paper-plane','fa-paper-plane-o','fa-history','fa-circle-thin','fa-header','fa-paragraph','fa-sliders','fa-share-alt','fa-share-alt-square','fa-bomb','fa-futbol-o','fa-tty','fa-binoculars','fa-plug','fa-slideshare','fa-twitch','fa-yelp','fa-newspaper-o','fa-wifi','fa-calculator','fa-paypal','fa-google-wallet','fa-cc-visa','fa-cc-mastercard','fa-cc-discover','fa-cc-amex','fa-cc-paypal','fa-cc-stripe','fa-bell-slash','fa-bell-slash-o','fa-trash','fa-copyright','fa-at','fa-eyedropper','fa-paint-brush','fa-birthday-cake','fa-area-chart','fa-pie-chart','fa-line-chart','fa-lastfm','fa-lastfm-square','fa-toggle-off','fa-toggle-on','fa-bicycle','fa-bus','fa-ioxhost','fa-angellist','fa-cc','fa-ils','fa-meanpath'];
	var rd=parseInt(Math.random()*999999);
	for(var i=0;i<aric.length;i++){
		elic=aric[i];
		licon+="<button style='margin:2px;width:20px;' class='btn btn-white btn-primary btn-minier' onclick=\"$('#"+control+"').val('fa "+elic+"');closeD('"+rd+"')\"><i class='ace-icon fa "+elic+"'></i></button>";
	}

	$("body").append("<div class='modal fade' id='Modal_"+rd+"'><div class='modal-dialog'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button><h4 class='modal-title'>Torresoft</h4></div><div class='modal-body' id='ModalContent_"+rd+"'>"+licon+"<input type='hidden' id='modalboxid' value='"+rd+"' /></div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Cerrar</button></div></div></div></div>");
	$("#Modal_"+rd).modal();
	$("#Modal_"+rd).on('hidden.bs.modal', function () {
		$(this).data('bs.modal', null);
		$("#Modal_"+rd).remove();
	});

}

function pagiNate(papa,sons,mandos,shows){
	if(shows==undefined){
		shows=20;
	}
	var itemTot = $("#"+papa+"> ."+sons).length;
	var nP=$("."+mandos+"_page").val();
	var resto=(itemTot%shows)>0;
	var pages=parseInt(itemTot/shows)+resto;
	var curPag=sessionStorage.getItem("pagInbox");
	if(curPag==undefined || curPag===null || curPag==0){
		curPag=1;

	}
	if(pages==0){pages=1};
	$("."+mandos+"_page").val(curPag);
	$("#"+mandos+"_page").off();
	$("#"+mandos+"_page").change(function(){
		var clvar=parseInt($("#"+mandos+"_page").val());
		$("."+sons).hide();
		$("."+mandos+"_dec_"+clvar+"."+sons).show();
		validCtrls(mandos,clvar,pages);
		sessionStorage.setItem("pagInbox",clvar);
	});
	validCtrls(mandos,curPag,pages);
	$("."+mandos+"_totpages").val(pages);
	$("."+mandos+"_totpage").text(pages);
}
function validCtrls(mandos,nP,pages){
	$("."+mandos+"_first").off();
	$("."+mandos+"_prev").off();
	$("."+mandos+"_next").off();
	$("."+mandos+"_last").off();
	if(nP==1 && nP==pages){
		$("."+mandos+"_first").off();
		$("."+mandos+"_prev").off();
		$("."+mandos+"_next").off();
		$("."+mandos+"_last").off();
	}else if(nP==1 && nP<pages){
		$("."+mandos+"_first").off();
		$("."+mandos+"_prev").off();
		$("."+mandos+"_next").click(function(){
			var cur=$("#"+mandos+"_page").val();
			$("."+mandos+"_page").val(parseInt(cur)+1).trigger("change");
		});
		$("."+mandos+"_last").click(function(){
			$("."+mandos+"_page").val(pages).trigger("change");
		});

	}else if(nP>1 && nP<pages){
		$("."+mandos+"_first").click(function(){
			$("."+mandos+"_page").val(1).trigger("change");

		});
		$("."+mandos+"_prev").click(function(){
			var cur=$("#"+mandos+"_page").val();
			$("."+mandos+"_page").val(parseInt(cur)-1).trigger("change");
		});
		$("."+mandos+"_next").click(function(){
			var cur=$("."+mandos+"_page").val();
			$("."+mandos+"_page").val(parseInt(cur)+1).trigger("change");
		});
		$("."+mandos+"_last").click(function(){
			$("."+mandos+"_page").val(pages).trigger("change");
		});
	}else if(nP>1 && nP==pages){
		$("."+mandos+"_first").click(function(){
			$("."+mandos+"_page").val(1).trigger("change");

		});
		$("."+mandos+"_prev").click(function(){
			var cur=$("."+mandos+"_page").val();
			$("."+mandos+"_page").val(parseInt(cur)-1).trigger("change");
		});
		$("."+mandos+"_last").off();
		$("."+mandos+"_next").off();
	}
}

function imitaCheck(id_pa,class_chi){
	//console.log("uhh"+id_pa);
	if($("#"+id_pa+":checked").length>0){
		$("."+class_chi+":not([readonly])").each(function(){
			$(this).prop("checked",true);
			eval($(this).attr("fn"));
		});
	}else{
		$("."+class_chi+":not([readonly])").each(function(){
			$(this).prop("checked",false);
			eval($(this).attr("fn"));
		});
	}
}


function setSy(clase_items,targets,li){
	var vl="";
	var tms="";
	$("."+clase_items).each(function(){
		if($(this).prop("checked")==true){
			vl+=$(this).attr("value")+"|";
			if(li==undefined){
				tms+=$(this).siblings(".lbl").text()+"<br />";
			}else{
				tms+="<li class='item item-default clearfix'>"+$(this).siblings(".lbl").text()+"</li>";
			}
		}
	});
	if(li==undefined){
		$("#"+targets+"_selected").html("<b>Seleccionados: </b><br /><small>"+tms+"</small>");
	}else{
		$("#"+targets+"_selected").html("<ul class='item-list'>"+tms+"</ul>");
	}
	$("#"+targets+"_val").val(vl);

}

function selectStep(asistente,paso){
	$("."+asistente+"_ass_steps").hide();
	$("#"+asistente+"_step_"+paso).show();
	$("ul.steps > li").removeClass("active");
	$("ul.steps > li").removeClass("complete");
	for(var n=1;n<paso;n++){
		$("ul.steps > li[data-step="+n+"]").addClass("complete");
	}
	$("ul.steps > li[data-step="+paso+"]").addClass("active");
}

function printPage(diva){
	var contentprint=$("#"+diva).html();
	$("body").append("<div id='printing' style='background:white;position:fixed;top:0;left:0;width:100%;height:100%;'>"+contentprint+"</div>");
	window.print();

}

//---------------->FORMS


function sortGrps(){
	$(".sortGrp").each(function(){
		var divpa=$(this).attr("id");
		$("#"+divpa).sortable({ 
			opacity: 0.6, 
			cursor: 'move', 
			connectWith: ".sortGrp",
			handle: ".moverc",
			placeholder: "bt-selected",
			helper:"clone",
			update: function(){
						var tbl=$("#"+divpa).attr("tbl");
						var arreglo="";
						$("#"+divpa+" > [ky]").each(function(){
							arreglo+= $(this).attr("ky")+"|";
						 });
						var x=$("#state_proceso");
						x.load("Admin/special_queries.php?flag=sortgrp&tabla="+tbl+"&order="+arreglo);
					},
			receive: function(event,ui){
						var tbl=$("#"+divpa).attr("tbl");
						var sortgrp=$("#"+divpa).attr("grp");
						var ky=$(ui.item).attr("ky");
						var x=$("#state_proceso");
						x.load("Admin/special_queries.php?flag=receive&tabla="+tbl+"&grp="+sortgrp+"&ky="+ky);
					}
																		
																		});
	})
	
}

function stQR64(tabla,c_valor,campo,vkey,callback)
{
	saving(campo);
	if(campo!=undefined){
		if($("#"+campo).prop("tagName")=="SELECT"){
			var valor=$("#"+campo+" option:selected").val();
		}else if($("#"+campo).attr("type")=="checkbox"){
			var valor=$("#"+campo+":checked").length;
		}else{
			var valor=$("#"+campo).val();
		}
	}
	var urlfinal="Admin/special_queries.php?flag=qupcamp64";
	var x=$("#state_proceso");
	x.load(urlfinal,{tabla:tabla,vkey:vkey,cval:c_valor,valor:valor},function(){
		if(callback!=undefined){
			eval(callback);	
		}
		saved(campo);
	});
}


function imgBase64(event,preview,controlto) {
    var reader = new FileReader();
	var fileTypes = ['data:image/jpeg']; 
    reader.onload = function(){
		var valr=reader.result;
		var okEx = valr.indexOf("data:image/jpeg") > -1;
		//valr=valr.replace("data:image/jpeg;base64,","");
		//valr=valr.replace("data:image/png;base64,","");
		//$("#"+controlto).val(valr).trigger("change");
		if(okEx){
			var img = new Image;
			img.onload = function() {
				var wii=img.width;
				var hei=img.height;
				var wiwi=$(window).width();
				var hewi=$(window).height();
				var pox=parseInt((wiwi/2)-165);
				var poh=parseInt((hewi/2)-125);
				if(wiwi>wii){
					if(wii!=330 || hei!=250){
						$("body").append("<div class='panel-content' id='todofirma' style='width:"+wiwi+"px;height:"+hewi+"px;position:absolute;top:0px;left:0px;background-color:rgba(255,255,255,0.8);z-index:99999999;'><small style='position:absolute;top:"+(poh-30)+"px;left:"+(pox)+"px;'>Arrastre la imagen para ajustarla en el recuadro rojo</small><img src='"+valr+"' id='imgdraga' style='position:absolute;left:"+pox+"px;top:"+poh+"px;' /><div id='imgover' style='width:330px;height:250px;position:absolute;left:"+pox+"px;top:"+poh+"px;border:2px solid red;z-index:10 !important;pointer-events: none;'></div><button class='btn btn-success' onclick=\"goFirma('"+controlto+"','"+preview+"')\" style='position:absolute;top:"+(poh+270)+"px;left:"+(pox)+"px;z-index:30;'>Hecho</button><button class='btn btn-default' onclick=\"minuss()\" style='position:absolute;top:"+(poh+270)+"px;left:"+(pox+250)+"px;z-index:30;'><span class='glyphicon glyphicon-zoom-out'></span></button><button class='btn btn-default' onclick=\"masss()\" style='position:absolute;top:"+(poh+270)+"px;left:"+(pox+290)+"px;z-index:30;'><span class='glyphicon glyphicon-zoom-in'></span></button><canvas id='signaprev' width='330px' height='250px' style='border:2px solid #000000;cursor:crosshair;display:none;'></canvas><canvas id='signaprev2' width='"+wii+"px' height='"+hei+"px' style='border:2px solid #000000;cursor:crosshair;display:none;'></canvas></div>");
						setTimeout(function(){
							goDraw(valr,preview);
							$("#imgdraga").draggable({stop:function(){
								goReDraw(valr,preview);
							}});
							
						},300);
					}else{
						valr=valr.replace("data:image/jpeg;base64,","");
						$("#"+controlto).val(valr).trigger("change");
					}
				}else{
					alert("La imagen excede las dimensiones permitidas");
				}
			};
			img.src = reader.result;
		}else{
			alert("Formato no permitido, (Solo JPG)");
		}
    };
    reader.readAsDataURL(event.target.files[0]);
}


function goDraw(imagen,preview){
	var canvas = document.getElementById("signaprev");
	var ctx = canvas.getContext("2d");
	ctx.beginPath();
	ctx.rect(0, 0, canvas.width, canvas.height);
	ctx.fillStyle = "white";
	ctx.fill();
	var myImage = new Image();
	myImage.src = imagen;
	ctx.drawImage(myImage, 0, 0);
	
	var output = document.getElementById(preview);
	output.src = canvas.toDataURL("image/jpeg");
	
}

function minuss(){
	var wima=parseInt($("#imgdraga").width()*0.9);
	var hema=parseInt($("#imgdraga").height()*0.9);
	//$("#imgdraga").width(wima);
	//$("#imgdraga").height(hema);
	var canvas = document.getElementById("signaprev2");
	var ctx = canvas.getContext("2d");

	ctx.clearRect(0, 0, canvas.width, canvas.height);
	
	ctx.beginPath();
	ctx.rect(0, 0, canvas.width, canvas.height);
	ctx.fillStyle = "white";
	ctx.fill();
	var myImage = new Image();
	myImage.src = $("#imgdraga").attr("src");
	ctx.drawImage(myImage, 0, 0, wima, hema);
	var conte2=canvas.toDataURL("image/jpeg");
	$("#imgdraga").attr("src",conte2);
	$("#imgdraga").trigger("create");
	//goReDraw()
	
}


function masss(){
	var wima=parseInt($("#imgdraga").width()*1.1);
	var hema=parseInt($("#imgdraga").height()*1.1);
	$("#imgdraga").width(wima);
	$("#imgdraga").height(hema);
	var canvas = document.getElementById("signaprev2");
	var ctx = canvas.getContext("2d");

	ctx.clearRect(0, 0, canvas.width, canvas.height);
	
	ctx.beginPath();
	ctx.rect(0, 0, canvas.width, canvas.height);
	ctx.fillStyle = "white";
	ctx.fill();
	var myImage = new Image();
	myImage.src = $("#imgdraga").attr("src");
	ctx.drawImage(myImage, 0, 0, wima, hema);
	var conte2=canvas.toDataURL("image/jpeg");
	$("#imgdraga").attr("src",conte2);
	//goReDraw()
	
}


function goReDraw(imagen,preview){
	var canvas = document.getElementById("signaprev");
	var imagen = $("#imgdraga").attr("src");
	var ctx = canvas.getContext("2d");
	var myImage = new Image();
	myImage.src = imagen;
	ctx.clearRect(0, 0, canvas.width, canvas.height);
	ctx.beginPath();
	ctx.rect(0, 0, canvas.width, canvas.height);
	ctx.fillStyle = "white";
	ctx.fill();	
	var left = $('#imgover').offset().left - $("#imgdraga").offset().left,
        top =  $('#imgover').offset().top - $("#imgdraga").offset().top,
        width = $('#imgover').width(),
        height = $('#imgover').height();
 
    ctx.drawImage(myImage, left, top, width, height, 0, 0, width, height);
	var output = document.getElementById(preview);
	output.src = canvas.toDataURL("image/jpeg");
	
}


function goFirma(controlto,preview){
	var canvas = document.getElementById("signaprev");
	var valr = canvas.toDataURL("image/jpeg");
	$("#"+preview).attr("src",valr);
	valr=valr.replace("data:image/jpeg;base64,","");
	valr=valr.replace("data:image/png;base64,","");
	$("#"+controlto).val(valr).trigger("change");
	$("#todofirma").remove();
}

function dynamicHide(class_hide,object_show,class_rm,class_add){
	$("."+class_hide).hide();
	$("#"+object_show).removeClass(class_rm);
	$("#"+object_show).addClass(class_add);
	$("#"+object_show).show();
}

function goTree(lev,item,ev){
	var n=$(".item_"+lev+"_"+item+":checked").length;
	var pl=parseInt(lev)+1;
	var nl=parseInt(lev)-1;
	if(n==0){
		$(ev.target).siblings().find(".level_"+pl+".parent_"+item).prop('checked', false);
	}else{
		$(ev.target).siblings().find(".level_"+pl+".parent_"+item).prop('checked', true);

	}
	$(ev.target).siblings().find(".level_"+pl+".parent_"+item).trigger("change");
}

function checkRecursive(item){
	if($("#"+item).prop('checked')==1){
		$("#"+item).parent().parent().parent().children("input[type=checkbox]").prop('checked', $("#"+item).prop('checked')).trigger("change");
		var itm=$("#"+item).parent().parent().parent().children("input[type=checkbox]").attr("id");
		if(itm!=undefined){
			checkRecursive(itm);
		}
	}
	//$("#"+item).parent().find("input[type=checkbox]").prop('checked', $("#"+item).prop('checked')).trigger("change");
}

function generaXLS(control){
	var contents=$("#"+control).submit();
}
function cleanDiv(dle){
	$("#"+dle).html("");
}

function testIntro(e,funcion){
	if(e!=undefined){
		tecla = (window.event) ? e.keyCode :e.which ? e.which : e.charCode;
		if(tecla==13){
			eval(funcion);
			return (tecla!=13);
		}
	}
}


function changePass(dialog,id_usuario){
	var	oldpass=$("#oldpass").val();
	var	newpass1=$("#newpass1").val();
	var	newpass2=$("#newpass2").val();
	var rd=parseInt(Math.random()*999999);
	showWait("Procesando");
	if(newpass1==newpass2){
		$("#state_proceso").load("Admin/special_queries.php?flag=changepass",{oldpass:oldpass,newpass:newpass1,id_usuario:id_usuario,rd:rd},function(){
			hideWait();
			if($("#state_proceso").html().replace(/ /gi,"").replace(/(\r\n|\n|\r)/gm,"")=="ok"){
				alert("Se ha cambiado la clave");
				closeD(dialog);
			}else{
				alert("Hubo un problema al cambiar la clave, verifique la antigua clave");
			}
		});
	}else{
		alert("Las claves no coinciden");
	}
}


function testIntr(e,fun,funull) {
  	tecla = (document.all) ? e.keyCode :e.which;
  	if(tecla==13){
		eval(fun);
	}else if(tecla==8 || tecla==46){
		if($(e.target).val()==""){
			eval(funull);
		}
	}
  	return (tecla!=13);
}

function testIntrobt(e,bt) {
  	tecla = (document.all) ? e.keyCode :e.which;
  	if(tecla==13){
		$("#"+bt).trigger("click");
	}
  	return (tecla!=13);
}

function cleanSearchCR(){
	$("#by_code").val("");
	$("#by_from_val").val(0);
	$("#by_to_val").val(0);
	$("#by_subject").val("");
	$("#by_msg").val("");
	$("#by_fecha_in").val("");
	$("#by_fecha_out").val("");
	$("#by_tipo_val").val(0);
	$("#by_serie_val").val(0);

}

function descarMl(id_ml){
	if(confirm("Desea archivar/descartar este E-mail?")){
		$("#state_proceso").load("Admin/sgd_get_my_imap.php?flag=descartar",{id:id_ml},function(){
			if($("#state_proceso").html().replace(/ /gi,"")=="1"){
				$("#elmail_"+id_ml).fadeOut(500,function(){$(this).remove()});
			}else{
				alert("Error al descartar");
			}
		});
	}
}

function setAreaContent(eldiv,latxt){
	setTimeout(function(){$("#"+latxt).val($("#"+eldiv+" span").html())},1000);
}


function setQR(tabla,c_valor,campo,vkey,callback)
{
	saving(campo);
	if(campo!=undefined){
		try{
			var valor=CKEDITOR.instances[campo].getData();
		}catch(e){

			if($("#"+campo).prop("tagName")=="SELECT"){
				var valor=$("#"+campo+" option:selected").val();
			}else if($("#"+campo).attr("type")=="checkbox"){
				var valor=$("#"+campo+":checked").length;
			}else{
				var valor=$("#"+campo).val();
			}

		}
	}
	vali=true;
	if($("#"+campo).attr("min")!=undefined && $("#"+campo).attr("max")!=undefined){
		var mini=parseInt($("#"+campo).attr("min"));
		var maxi=parseInt($("#"+campo).attr("max"));
		valor=parseInt(valor);
		if(valor<mini){
			$("#"+campo).val(mini);
			valor=mini;
		}
		if(valor>maxi){
			$("#"+campo).val(maxi);
			valor=maxi;
		}
	}
	if(vali){
	
				$.ajax({
					url : "Admin/special_queries.php?flag=qupcamp",
					type: "POST",
					data:{tabla:tabla,vkey:vkey,cval:c_valor,valor:valor},
					timeout:60000,
					success: function(data, textStatus, jqXHR){
						if(callback!=undefined){
							eval(callback);
						}
						saved(campo);
						$( "#pgtmp_vl" ).remove();
						if($("#"+campo).prop("tagName")=="SELECT"){

						}
					},
					error: function (jqXHR, textStatus, errorThrown)
					{
						alert("Error al guardar");
					}
				});
	}else{
		saved(campo);
		alert("Fuera de rango");
	}
}


function srcImgPic(imgc,control){
	showWait();
	$("#"+imgc).attr("src","readfl.php?filename="+$("#"+control).val());
	hideWait();
}


function selectAllMessages(ref,clase){
	$("input."+clase).attr("checked",$("#"+ref).attr("checked"));
	verifySelected(clase);
}

function verifySelected(clase){
	if($("input."+clase+":checked").length>0){
		$("#"+clase).css("display","block");
	}else{
		$("#"+clase).css("display","none");
	}
}


function sbArjGen(ctrl,fld,callback,div,nfle){
	
	showWait("Cargando archivos");
	var rd=parseInt(Math.random()*999999);
	$("#state_proceso").html("");
	$("#state_proceso").append("<form id='atachformer_"+rd+"' name='atachform' method='post' enctype='multipart/form-data'></form>");
	$('#atachformer_'+rd).append($('#'+ctrl));
	$('#atachformer_'+rd).append($('#'+fld));
	var folder=$('#bs_'+fld).val();
	$('#atachformer_'+rd).submit(function(e) {
		var formData = new FormData(this);
		$("#pb_archivos_").show();
		$.ajax({
			type:'POST',
			url: 'Admin/files/files_upload_gen.php?ctrl='+ctrl+'&namefile='+nfle,
			data:formData,
			xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if(myXhr.upload){
						myXhr.upload.addEventListener('progress',progresss, false);
					}
					return myXhr;
			},
			cache:false,
			contentType: false,
			processData: false,
			success:function(data){
				var res=data.split("|");
				var erro=false;
				for(var i=0;i<res.length;i++){
					var sr=parseInt(res[i]);
					if(sr<1){
						erro=true;
					}
				}
				hideWait();
				if(erro){
					//console.log(data);
					alert("Hubo errores al cargar los archivos, verifique los formatos y tamaños:"+data);
				}
				//console.log(data);
				$.ajax({
					type:'POST',
					url: 'Admin/special_queries.php?flag=callbackup',
					data:{path:folder,title:$("#titile_"+div).text(),div:div,callback:callback},
					success:function(data2){
						$("#"+div).html(data2);
						tryhovers();
					}
				});
				eval(callback);
				$("#"+ctrl).val([]);
				$("#state_proceso").html("");
				$("#pb_archivos_").hide();
				hideWait();
			},

			error: function(data){
				console.log(data);
				hideWait();
			}
		});

		e.preventDefault();

	});

	$("#atachformer_"+rd).submit();
}

function progresss(event){
	if(event.lengthComputable){
		var max = event.total;
		var current = event.loaded;
		var Percentage = parseInt((current/max)*100);
		$("#pb_archivos_ .progress-bar").attr("aria-valuenow",Percentage);
		$("#pb_archivos_ .progress-bar").css("width",Percentage+"%");
		$("#pb_archivos_ .progress-bar").text(Percentage+"%");

		if(Percentage >= 100)
		{
		   // process completed
		}
	}

}

function upFilesGen(ctrl,fld,callback,nfle){

	//showWait("Cargando archivos");
	var rd=parseInt(Math.random()*999999);
	$("#state_proceso").html("");
	$("#state_proceso").append("<form id='atachformer_"+rd+"' name='atachform' method='post' enctype='multipart/form-data'></form>");
	$('#atachformer_'+rd).append($('#'+ctrl));
	$('#atachformer_'+rd).append($('#'+fld));
	$('#atachformer_'+rd).submit(function(e) {
		var formData = new FormData(this);
		$("#pbfm").show();
		$.ajax({
			type:'POST',
			url: 'Admin/files/files_upload_gen.php?ctrl='+ctrl+'&namefile='+nfle,
			data:formData,
			xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if(myXhr.upload){
						myXhr.upload.addEventListener('progress',progressgen, false);
					}
					return myXhr;
			},
			cache:false,
			contentType: false,
			processData: false,
			success:function(data){
				var res=data.split("|");
				var erro=false;
				for(var i=0;i<res.length;i++){
					var sr=parseInt(res[i]);
					if(sr<1){
						erro=true;
					}
				}
				hideWait();
				if(erro){
					//console.log(data);
					alert("Hubo errores al cargar los archivos, verifique los formatos y tamaños:"+data);
				}
				//console.log(data);
				eval(callback);
				$("#"+ctrl).val([]);
				$("#state_proceso").html("");
			},

			error: function(data){
				console.log(data);
				hideWait();
			}
		});

		e.preventDefault();

	});

	$("#atachformer_"+rd).submit();
}


function upFiles(callback){

	//showWait("Cargando archivos");
	$("#state_proceso").html("");
	$("#state_proceso").append("<form id='atachformer' name='atachform' method='post' enctype='multipart/form-data'></form>");
	$('#atachformer').append($('#archivo'));
	$('#atachformer').append($('#folder'));
	$('#atachformer').submit(function(e) {
		var formData = new FormData(this);
		$("#pbfm").show();
		$.ajax({
			type:'POST',
			url: 'Admin/files/files_upload.php',
			data:formData,
			xhr: function() {
					var myXhr = $.ajaxSettings.xhr();
					if(myXhr.upload){
						myXhr.upload.addEventListener('progress',progressgen, false);
					}
					return myXhr;
			},
			cache:false,
			contentType: false,
			processData: false,
			success:function(data){
				var res=data.split("|");
				var erro=false;
				for(var i=0;i<res.length;i++){
					var sr=parseInt(res[i]);
					if(sr<1){
						erro=true;
					}
				}
				hideWait();
				if(erro){
					//console.log(data);
					alert("Hubo errores al cargar los archivos, verifique los formatos y tamaños:"+data);
				}
				//console.log(data);
				eval(callback);
				$("#archivo").val([]);
				$("#state_proceso").html("");
			},

			error: function(data){
				console.log(data);
				hideWait();
			}
		});

		e.preventDefault();

	});

	$("#atachformer").submit();
}



function progress(e){

    if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;

        var Percentage = parseInt((current/max)*100);
		console.log(Percentage);
        $("#pbf .progress-bar").attr("aria-valuenow",Percentage);
		$("#pbf .progress-bar").css("width",Percentage+"%");
		$("#pbf .progress-bar").text(Percentage+"%");

        if(Percentage >= 100)
        {
           // process completed
        }
    }
 }


function progressgen(e){
    if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;

        var Percentage = parseInt((current/max)*100);
        $("#pbfm .progress-bar").attr("aria-valuenow",Percentage);
		$("#pbfm .progress-bar").css("width",Percentage+"%");
		$("#pbfm .progress-bar").text(Percentage+"%");

        if(Percentage >= 100)
        {
           // process completed
        }
    }
 }


function dfs(fl,msg){
	$("#flsv").remove();
	$("body").append("<form id='flsv' method='POST' target='_self' action='dwt.php'><input type='hidden' name='fl' value='"+fl+"' /><input type='hidden' name='idmsg' value='"+msg+"' /></form>");
	setTimeout(function(){
		$("#flsv").submit();
	},100);
}

function dfe(fl,sal){
	$("#flsv").remove();
	$("body").append("<form id='flsv' method='POST' target='_self' action='dwe.php'><input type='hidden' name='fl' value='"+fl+"' /><input type='hidden' name='idmsg' value='"+sal+"' /></form>");
	setTimeout(function(){
		$("#flsv").submit();
	},100);
}

function dfg(event){
	var target = $( event.target ).attr("value");
	$("#flsv").remove();
	$("body").append("<form id='flsv' method='POST' target='_self' action='dwg.php'><input type='hidden' name='fl' value='"+target+"' /></form>");
	setTimeout(function(){
		$("#flsv").submit();
	},100);
}

function dfa(fl,t){
	$("#flsv").remove();
	$("body").append("<form id='flsv' method='POST' target='_self' action='dwa.php?t="+t+"'><input type='hidden' name='fl' value='"+fl+"' /><input type='hidden' name='idmsg' value='"+t+"' /></form>");
	setTimeout(function(){
		$("#flsv").submit();
	},100);
}



function dFK(event){
	if(confirm("Borrar archivo?")){
		var target = $( event.target );
		var fl = target.attr("value");
		var todel = target.attr("itm");
		$("#state_proceso").load("Admin/special_queries.php?flag=delfig",{fl:fl},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")==1){
				$("#"+todel).fadeOut(600,function(){$(this).remove()});
			}
		});
	}
}

function dFk(event,io){
	if(confirm("Borrar archivo?")){
		var target = $( event.target );
		var ing = target.attr("ing");
		var fl = target.attr("value");
		var todel = target.attr("i");
		$("#state_proceso").load("Admin/special_queries.php?flag=delfi&io="+io,{ing:ing,fl:fl},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")==1){
				$("#file_"+todel).fadeOut(300,function(){$(this).remove()});
			}
		});
	}
}

function dFl(event){
	if(confirm("Borrar archivo?")){
		var target = $( event.target );
		var fl = target.attr("value");
		var todel = target.attr("i");
		$("#state_proceso").load("Admin/special_queries.php?flag=delfo",{fl:fl},function(){
			if($("#state_proceso").html().replace(/ /gi,"").replace(/\n/gi,"")==1){
				$("#file_"+todel).fadeOut(300,function(){$(this).remove()});
				target.parent().fadeOut(300,function(){$(this).remove()});
			}
		});
	}
}

function prvFl(event,io){
	var target = $( event.target );
	var ing = target.attr("ing");
	var fl =target.attr("value");

	var rd=parseInt(Math.random()*999999);
	var laurl='pfl.php?rd='+rd+'&ing='+ing+'&fl='+urlencode(fl)+'&io='+io;
	var law=window.open("./"+laurl,"ts-filepreview");

	law.onload = function(){
		setTimeout(function(){
			var title = document.createElement("title");
			title.innerHTML="Torresoft/Archivo";
			var header = document.createElement("head");
			header.appendChild(title);
			law.document.getElementsByTagName("html").appendChild(header);
		}, 500);
	}
}

function utf8_encode(s){
	return encodeURIComponent(s);
}

function pvFl(event){
	var target = $( event.target );
	var dir = target.attr("dir");
	var fl = utf8_encode(target.attr("value"));

	var rd=parseInt(Math.random()*999999);
	var laurl='pfl.php?rd='+rd+'&fl='+urlencode(dir+fl);
	var law=window.open("./"+laurl,"ts-filepreview");

	law.onload = function(){
		setTimeout(function(){
			var title = document.createElement("title");
			title.innerHTML="Torresoft/Archivo";
			var header = document.createElement("head");
			header.appendChild(title);
			law.document.getElementsByTagName("html").appendChild(header);
		}, 500);
	}

}



function clsPrv(){
	$("#prvfl").hide(function(){$(this).remove();});
}

function urlencode(str) {
  str = (str + '')
    .toString();
  return encodeURIComponent(str)
    .replace(/!/g, '%21')
    .replace(/'/g, '%27')
    .replace(/\(/g, '%28')
    .
  replace(/\)/g, '%29')
    .replace(/\*/g, '%2A')
    .replace(/%20/g, '+');
}



function removerTildes(cadena){
	return cadena;
}



var prevH="";

function defineImg(rnd){
	if($("#laimg_ok").length){
		val=$("#laimg_ok").attr("src");
		$("#state_proceso").load("Admin/special_queries.php?flag=upimg",{val:val},function(){
			closeD(rnd);
			$("#laimagen").html("<img id='laimnw' src='"+val+"' align='left' />");
		})
	}
}


function validaFile(){
	var fil=$("#elarchivo").val();
	var ext=fil.split(".")[this.length];
	if(ext==undefined){
		ext=fil.split(".")[this.length-1];
	}
	//alert(ext);
	if(ext!="jpg" && ext!="JPG" && ext!="pdf" && ext!="PDF"){
		alert("("+ext+") Archivos permitidos: Imagenes JPG o archivos PDF");
		$("#elarchivo").val("");

	}
}

function getNewPass(div){
	var correo=$("#correo_recuperar").val();
	if(correo==undefined){
		try{correo=document.getElementById("correo_recuperar").value;}catch(e){}
	}
	var identificacion=$("#user_identificacion").val();
	if(identificacion==undefined){
		try{correo=document.getElementById("user_identificacion").value;}catch(e){}
	}
	if(correo!="" && identificacion!=""){
		$("#"+div).load("recpass.php?flag=recp&correo="+correo+"&identificacion="+identificacion,function(){
							 tryhovers();
							 });
	}else{
		alert("Faltan datos!");
	}


}

function getImage(controlto,folder,callback){
	url="Admin/getimgpw.php?controlto="+controlto+"&callback="+callback+"&folder="+folder;
	getDialog(url,700);
}

function setImagepw(controlto,imagendiv,rd,callback){
	var elvalor=$("#"+imagendiv).attr("value");
	$("#"+controlto).val(elvalor).trigger("change");
	setTimeout(function(){closeD(rd);eval(callback)},200);
}
function srcImg(){
	if($("#IMAG_TERC").val()!=""){
		val=$("#IMAG_TERC").val();
		$("#atatarok").attr("src","readfl.php?filename="+val);
		$(".nav-user-photo").attr("src","readfl.php?filename="+val);

	}
}


function uploadImage(formu,divin,controlfoto,rd,controlto,callback){
	$('#'+formu).submit(function(e) {
		if($('#'+controlfoto).val()) {
			e.preventDefault();
			$('#progress-div').show();
			$(this).ajaxSubmit({
				target:   '#elifmni',
				beforeSubmit: function() {
				  $("#progress-bar").width('0%');
				},
				uploadProgress: function (event, position, total, percentComplete){
					$("#progress-bar").width(percentComplete + '%');
					$("#progress-bar").html('<div id="progress-status">' + percentComplete +' %</div>')
				},
				success:function (){
					var respon=$("#elifmni").html().replace(/ /gi,"");
					if(respon==""){
						$('#progress-div').hide();
						alert("Error no especificado, no se pudo subir el archivo");
					}else if(respon=="-3"){
						$('#progress-div').hide();
						alert("Error, permisos insuficientes en carpeta destino");
					}else if(respon=="-2"){
						$('#progress-div').hide();
						alert("Error, el archivo ya existe en el servidor, \ncambie el nombre o use el existente");
					}else if(respon=="-1"){
						$('#progress-div').hide();
						alert("Error, formato no permitido");
					}else if(respon=="0"){
						$('#progress-div').hide();
						alert("Error, el archivo excede el limite permitido");
					}else{
						$('#progress-div').hide();

						var rdup=parseInt(Math.random()*999);
						$(".lasimgs").removeClass("bg-danger");
						$("#"+divin).prepend("<div id='imgnew_"+rdup+"' class='lasimgs bg-danger link' style='width:100px;height:100px;float:left;position:relative;overflow:hidden;margin:1px;border-color:red !important;' value='"+respon+"' ondblclick=\"setImagepw('"+controlto+"','imgnew_"+rdup+"','"+rd+"','"+callback+"')\" onclick=\"setSelect('lasimgs',event)\"><img id='newimg_"+rdup+"' foto='"+respon+"' src='"+respon+"' style='width:100px;' align='left' /><div style='position:absolute;bottom:0px;width:100px;font-size:9px !important;'>"+respon.split("/")[respon.split("/").length-1]+"</div><div>");
						$("#"+divin).animate({scrollTop:0},500);
					}

				},
				resetForm: true
			});
			return false;
		}
	});
	$("#"+formu).submit();
	try{
		$("#RUTA_ARCHIVO").val($("#RUTA_ARCHIVO").val()+carpeta+"/"+foto+";")
	}catch(e){

	}
}


//Bootstrap 3.3.0 Snippet by xaradebz

$.fn.extend({
    treed: function (o) {

      var openedClass = 'glyphicon-minus-sign';
      var closedClass = 'glyphicon-plus-sign';

      if (typeof o != 'undefined'){
        if (typeof o.openedClass != 'undefined'){
        openedClass = o.openedClass;
        }
        if (typeof o.closedClass != 'undefined'){
        closedClass = o.closedClass;
        }
      };

        var tree = $(this);
        tree.addClass("tree");
		if(!tree.hasClass("instance")){
			tree.addClass("instance");

			tree.find('li').has("ul").each(function () {
				var branch = $(this); //li with children ul
				branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
				branch.addClass('branch');
				branch.on('click', function (e) {
					if (this == e.target) {
						var icon = $(this).children('i:first');
						icon.toggleClass(openedClass + " " + closedClass);
						$(this).children().children().toggle();
					}
				})
				branch.children().children().toggle();
			});

		  tree.find('.branch .indicator').each(function(){
			$(this).on('click', function () {
				$(this).closest('li').click();
			});
		  });

			tree.find('.branch>a').each(function () {
				$(this).on('click', function (e) {
					$(this).closest('li').click();
					e.preventDefault();
				});
			});

			tree.find('.branch>button').each(function () {
				$(this).on('click', function (e) {
					$(this).closest('li').click();
					e.preventDefault();
				});
			});
		}
    }
});


function sFtS(formData,status,folder,path,callback,div,namefile,levels)
{
	if(namefile==undefined){namefile=""}
    var uploadURL ="Admin/files/files_upload_gen.php?ctrl=file&namefile="+namefile;
    var extraData ={};
	formData.append('folder',folder);
	
	
	formData.append('namefile',namefile);
    var jqXHR=$.ajax({
            xhr: function() {
            var xhrobj = $.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
        url: uploadURL,
        type: "POST",
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success: function(data){
			status.setProgress(100);
			callBackCont(path,div,callback,levels);
        }
    });
    status.setAbort(jqXHR);
}


function callBackCont(path,div,callback,levels){
	$.ajax({
		type:'POST',
		url: 'Admin/special_queries.php?flag=callbackup',
		data:{path:path,title:$("#titile_"+div).text(),div:div,callback:callback,levels:levels},
		success:function(data2){
			$("#"+div).html(data2);
			tryhovers();
		}
	});
}


function getAjax(endpoint,params){
	return new Promise(function(resolve, reject) {
		$.ajax({
		type:'GET',
		url: localStorage.getItem("cfg_svr")+endpoint,
		timeout:10000,
		headers:{"authorization":'Bearer ' + localStorage.getItem("ts_token")},
		data:params,
		success:function(data){
			resolve(data);
		},
		error:function(){
			resolve([]);
		}
	});
		
	});
}




var rowCount=0;
function createStatusbar(obj)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 == 0) row = "even";
     this.statusbar = $("<div class='progress progress-striped active' style='position:relative;border-radius:8px;'></div>");
     this.filename = $("<div class='filename' style='position:absolute;left:100px;top:0px;font-size:10px;'></div>").appendTo(this.statusbar);
     this.size = $("<div class='filesize' style='position:absolute;left:0px;top:0px;font-size:10px;'></div>").appendTo(this.statusbar);
     this.progressBar = $("<div class='progress-bar progress-bar-warning' style='width:0%;'></div>").appendTo(this.statusbar);
     this.abort = $("<div class='btn btn-minier btn-danger pull-right abort' style='position:absolute;right:0px;top:0px;border-radius:8px;height:19px;font-size:10px;'>Cancela</div>").appendTo(this.statusbar);
     $("#status1").after(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {       
        this.progressBar.animate({ width: progress+"%" }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
        });
    }
}


function supportSend(){
	showWait("Enviando...");
	var sto=$("#amb_g:checked").length;
	if(sto>0){
		var msgTo=$("#supportforg option:selected").val();
	}else{
		var msgTo=$("#supportforu option:selected").val();
	}
	
	var subject=$("#id_tipo_solicitud").val();
	var msgBody=$("#supporttosend").val();
	var id_proceso=$("#fromproceso").val();
	var fecha_limite=$("#fecha_limite").val();
	var adjuntos="";
	var copias="";
	$(".upfiles").each(function(){adjuntos+=$(this).attr("fl")+"|";});
	$(".copias_sop:checked").each(function(){copias+=$(this).attr("u")+"|";});
		
	if(msgBody!="" && msgTo!="" && id_proceso!=""){
		$("#salida").load("mod/spt/supportsend.php",{para:msgTo,id_tipo:subject,cuerpo:msgBody,sto:sto,adjuntos:adjuntos,copias:copias,id_proceso:id_proceso,fecha_limite:fecha_limite},function(){
			tryhovers();
			hideWait();
			emitir_notice(msgTo);
		});
	}else{
		hideWait();
		alert("Faltan Datos");
	}
}

function goMsP(id_r,id_p){
	$("")
}




function setUgru(){
	var sto=$("#amb_g:checked").length;
	if(sto>0){
		$("#supportforg").css("display","");
		$("#supportforu").css("display","none");
	}else{
		$("#supportforg").css("display","none");
		$("#supportforu").css("display","");
	}
}

function goBack(){
	history.back();
}
function goAddReplySupport(divcont,id_solicitud){
	var verbo=$("#state_request").val();
	var new_state=$("#estado_seguimiento option:selected").val();
	
	var adjuntos="";
	$("#setfilesend_seg > .upfiles").each(function(){adjuntos+=$(this).attr("fl")+"|";});
	
	var x=$(document.createElement("div"));
	x.load("mod/spt/supportreplysend.php",{id_solicitud:id_solicitud,content:verbo,state:new_state,adjuntos:adjuntos},function(){
		if(x.html().replace(/ /gi,"")=="data"){
			alert("Complete los datos");
		}else if(x.html().replace(/ /gi,"")=="bad"){
			alert("Error, no se puede procesar la solicitud");	
		}else{
			readSupport(divcont,id_solicitud);
			tryhovers();
			loadNotis();
		}
	});
	
	
}


function upF(folder,div,control){

	showWait("Cargando archivos");
	if(control==undefined){
		control="archivo";
	}
	var formData = new FormData($("#upload-forma")[0]);
	//loop for add $_FILES["upload"+i] to formData
	var nf=0;
	for (var i = 0, len = document.getElementById(control).files.length; i < len; i++) {
		formData.append(control+(i+1), document.getElementById(control).files[i]);
		nf++;
	}
	
	//send formData to server-side
	$.ajax({
		url : "publicses.php?flag=upf&nfiles="+nf+"&folder="+folder+"&control="+control,
		type : 'post',
		data : formData,
		dataType : 'html',
		async : true,
		processData: false,  // tell jQuery not to process the data
		contentType: false,   // tell jQuery not to set contentType
		error : function(request){
			alert("Error, "+request.responseText);
		},
		success: function(data){
			//Create jQuery object from the response HTML.
			//alert(data);
			console.log(data);
			var grupos=data.split("|");
			var alt="";
			var erro=false;
			for(n=1;n<=grupos.length;n++){
				var archivo=grupos[n-1].split("*")[0];
				archivo=archivo.replace(/ /gi,"");
				var estado=grupos[n-1].split("*")[1];
				var exto=archivo.split(".");
				var ext=exto[exto.length-1];
				var namef=archivo.substring(0,10)+"..."
				if(estado==1){
					$("#"+div).append("<li id='nf_"+n+"'  alt='"+archivo+"' fl='"+folder+archivo+"' class='dd-item dd2-item'><div class='dd-handle dd2-handle  stoppa'><img src='iconos/"+ext+".png' align='center' style='width:30px;' ></div><div class='dd2-content'><span style='list-style-position:inside;white-space: nowrap;overflow: hidden;  text-overflow: ellipsis;width:80%;position:relative;float:left;'>"+namef+"</span><div class='pull-right action-buttons'><a class='orange link-smp' onclick=\"godelFl('"+folder+"','"+archivo+"','nf_"+n+"')\" ><i class='ace-icon fa fa-remove bigger-130'></i></a></div></div></li>");
					
					
					if($("#UPFILES").length==0){
						$("#"+div).append("<input type='hidden' class='"+control+"' name='UPFILES' id='UPFILES' value='"+folder+archivo+"' />");
					}else{
						$("#UPFILES").val($("#UPFILES").val()+"|"+folder+archivo);
					}
					
				}else if(estado==0){
					alt+="Archivo: "+archivo+": Error, archivo no validado \n";
					erro=true;
				}else if(estado==-1){
					erro=true;
					alt+="Archivo: "+archivo+": Error, formato no permitido \n";
				}else if(estado==-2){
					erro=true;
					alt+="Archivo: "+archivo+": Error, excede el tamano permitido por el servidor \n";
				}
				$("#"+control).ace_file_input('reset_input');
				
			}
			if(erro){
				alert(alt);
			}
			tryhovers();
			hideWait();
		}
	}); 
}

function godelFl(folder,arch,div){
	$("#state_proceso").load("publicses.php?flag=delf",{folder:folder,archivo:arch},function(){
		$("#"+div).remove();
	})
}


function addAtCursor(textarea,val,e){
	CKEDITOR.instances[textarea].insertText(val);
}

function dummyFoo(faa){
	alert("Agenda de solo lectura");
}


function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
function calcCaja(id_pedido){
	var tot_ped=$("#tot_ped_cax_"+id_pedido).val();
	var tot_desc=$("#discunt_"+id_pedido).val();
	var total=tot_ped-tot_desc;
	var oka=number_format(total,0,",",".");
	$("#receip_cax_"+id_pedido).text("$" + oka);
}


function dragaDrop(items,contenedores){
	setTimeout(function(){
		$("."+items).draggable({revert:false,containment: "window",appendTo: "body",scroll: false, helper: 'clone',zIndex: 1000000000000,cursorAt: { left: 100,top:10 }});
		$("."+contenedores).droppable({
			hoverClass: "bg-red",
			accept: "."+items,
			drop: function(ev, ui) {
				var iditem=$(ui.draggable).attr("idi");
				var n=parseInt($(this).attr("n"));
				
				$("#pareto_"+iditem).val(n);
				$(ui.draggable).appendTo($(this));
				$(this).find("li.empty").remove();
				var allhave=1;
				calcPrize(contenedores,items);
				if(allhave==0){
					$("#nextbtn_pedi_facpart").hide();
				}else{
					if($("#grupoinicial_pro > li").length==0){
						$("#nextbtn_pedi_facpart").show();
					}else{
						$("#nextbtn_pedi_facpart").hide();
					}
					
				}
				
			}
		});
	},1000);
}




function dragaDrop2(items,contenedores){
	setTimeout(function(){
		$("."+items).draggable({revert:false,containment: "window",appendTo: "body",scroll: false, helper: 'clone',zIndex: 1000000000000,cursorAt: { left: 100,top:10 }});
		$("."+contenedores).droppable({
			hoverClass: "bg-red",
			accept: "."+items,
			drop: function(ev, ui) {
				var iditem=$(ui.draggable).attr("idi");
				var n=parseInt($(this).attr("n"));
				
				$("#pareto_"+iditem).val(n);
				$(ui.draggable).appendTo($(this));
				$(this).find("li.empty").remove();
				var allhave=1;
				calcPrize(contenedores,items);
				if(allhave==0){
					$("#nextbtn_pedi_facpart").hide();
				}else{
					if($("."+contenedores+" > li").length>0){
						$("#nextbtn_pedi_facpart").show();
					}else{
						$("#nextbtn_pedi_facpart").hide();
					}
					
				}
				
			}
		});
	},1000);
}

function calcPrize(contenedores,items){
	var propini=0;
	if($("#propini").length>0){
		propini=$("#propini").val();
	}
	console.log(propini);
	$("."+contenedores).each(function(){
		var z=parseInt($(this).attr("n"));
		var total=0;
		var propii=0;
		$(this).find("li > small.prz").each(function(){
			total+=parseInt($(this).text());
		});
		if($("#incluserv_"+z+":checked").length>0){
			propii=parseInt($("#propini_"+z).val());
			if(propii===NaN || propii==undefined || propii==""){
				propii=0;
			}
			total=total+propii;
		}
		$("#totalprz_"+z).text("$ " + total);
		if($(this).find("li."+items).length==0){
			allhave=0;
		}
	})
}

function calcPropi(n,contenedores,items){
	var total=0;
	var propini=0;
	if($("#propini").length>0){
		propini=$("#propini").val();
	}
	$('#ulaula_'+n).find("li > small.prz").each(function(){
		total+=parseInt($(this).text());
	});
	var propii=parseInt(total*(propini/100));
	$("#propini_"+n).val(propii);
	calcPrize(contenedores,items);
}


function queryClient(k){
	if(k==undefined){
		var docu=$("#num_doc").val();
	}else{
		var docu=$("#num_doc_"+k).val();
	}
	
	$.ajax({
		url : "Admin/site_box.php?flag=get_client",
		type: "POST",
		data : {doc:docu},
		timeout:5000,
		success: function(data, textStatus, jqXHR)
		{
			var jdata=JSON.parse(data);
			if(jdata.IDENTIFICACION!=undefined){
				var IDENTIFICACION=jdata.IDENTIFICACION;
				var TIPO_ID=jdata.TIPO_ID;
				var IDENTIFICACION=jdata.IDENTIFICACION;
				var NOMBRE=jdata.NOMBRE;
				var DIRECCION=jdata.DIRECCION;
				var TELEFONO=jdata.TELEFONO;
				var CORREO=jdata.CORREO;
				var PUNTOS=jdata.PUNTOS;
				var BILLEGAS=jdata.BILLEGAS;
				if(k==undefined){
					$("#tipo_doc").val(TIPO_ID);
					$("#nombre_cliente").val(NOMBRE);
					$("#dir_cliente").val(DIRECCION);
					$("#tel_cliente").val(TELEFONO);
					$("#mail_cliente").val(CORREO);
					if(PUNTOS>0){
						$("#puntos").val(PUNTOS);
						$("#punta").text("PUNTOS: "+PUNTOS+" ($"+BILLEGAS+")");
						$("#reeedime").removeClass("hidden");
						$("#punta").removeClass("hidden");
						$("#billegas").val(BILLEGAS);
					}else{
						$("#puntos").val(PUNTOS);
						$("#billegas").val(BILLEGAS);
						$("#punta").text("");
						$("#punta").addClass("hidden");
						$("#reeedime").addClass("hidden");
					}
					
					
				}else{
					$("#tipo_doc_"+k).val(TIPO_ID);
					$("#nombre_cliente_"+k).val(NOMBRE);
					$("#dir_cliente_"+k).val(DIRECCION);
					$("#tel_cliente_"+k).val(TELEFONO);
					$("#mail_cliente_"+k).val(CORREO);
				}
				console.log(jdata);
				
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log("No se pudo encontrar el cliente");
		}
	});

}

function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}

function addItemDlg(){
	
}

function queryPrinter(){
	var docu=$("#num_doc").val();
	$.ajax({
		url : "mviews.php?flag=comandas",
		type: "POST",
		data : {},
		datatype:"json",
		timeout:5000,
		success: function(data, textStatus, jqXHR)
		{
			
			var jdata=JSON.parse(data);
			for(var i=0;i<jdata.data.length;i++){
				var idpedido=jdata.data[i].ID_PEDIDO;
				console.log(idpedido);
				printFact(idpedido);
			}
		
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
			console.log("Motor de impresion no disponible");
		}
	});

}


function printFact(curorden){
	var myWindow = window.open("./mviews.php?flag=print_mesa&idp="+curorden, "Imprimir", "width=100,height=100");
	myWindow.print();
	myWindow.close();
}
function printFact3(curorden){
	
	var canvas = this.__canvas = new fabric.StaticCanvas('canvasfact');
	canvas.renderOnAddRemove = false;
	canvas.stateful = false;
	var nprod=curorden.length;
	var h=parseInt(nprod*100);
	canvas.setHeight(parseInt(1300+h));
	canvas.setWidth(750);
	canvas.backgroundColor = 'rgba(255,255,255,1)';
	/*
	fabric.Image.fromURL(sessionStorage.getItem("bus_logo"), function(img) {
		canvas.add(img.set({ left: 3, top: 3, angle: 0 , width:742, height:110}).scale(1));
	});
	*/
	var busnom=sessionStorage.getItem("bus_nombre");
	var busnit="Nit. "+sessionStorage.getItem("bus_nit");
	var busnoma0=busnom.split("-");
	
	var cnr1=0;
	var lines=1;
	var busnomcomplete="";
	for(s0=0;s0<busnoma0.length;s0++){
		busnoma=busnoma0[s0].split(" ");
		cnr1=0;
		for(s1=0;s1<busnoma.length;s1++){
			var pal=busnoma[s1];
			var len=busnoma[s1].length;
			cnr1+=len;
			if(cnr1>38){
				busnomcomplete+="\n";
				busnomcomplete+=pal;
				cnr1=0;
				lines++;
			}else{
				busnomcomplete+=" "+pal;
			}
		}
		busnomcomplete+="\n";
		lines++;
	}
	
	var elnombus = new fabric.Text(busnomcomplete, { left: 20, top: 10, fontFamily: 'Lucida Console',fontSize: 32});
	canvas.add(elnombus);
	
	var busnita=busnit.split(" ");
	
	
	var cnr1=0;
	//var lines=1;
	var busnitcomplete="";
	for(s1=0;s1<busnita.length;s1++){
		var pal=busnita[s1];
		var len=busnita[s1].length;
		cnr1+=len;
		if(cnr1>38){
			busnitcomplete+="\n";
			busnitcomplete+=pal;
			cnr1=0;
			lines++;
		}else{
			busnitcomplete+=" "+pal;
		}
	}
	var cury=(lines*45);
	var elnombus = new fabric.Text(busnitcomplete, { left: 20, top: cury, fontFamily: 'Lucida Console',fontSize: 32});
	canvas.add(elnombus);
	
	cury+=60;
	
	
	var direc="DIR. "+sessionStorage.getItem("bus_direccion")+", "+sessionStorage.getItem("bus_ciudad");
	var text = new fabric.Text(direc, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30 });
	canvas.add(text);
	cury+=40;
	var tels="TELS. "+sessionStorage.getItem("bus_telefono");
	var text = new fabric.Text(tels, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30 });
	canvas.add(text);
	cury+=40;
	var facta="FACTURA No. "+sessionStorage.getItem("prefix")+"-"+sessionStorage.getItem("fopen");
	var text = new fabric.Text(facta, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30, fontWeight:"bold" });
	canvas.add(text);
	
	
	var d = new Date();
	var cier=d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds();
	cury+=40;
	var fecha="FECHA: "+cier;
	var text = new fabric.Text(fecha, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(text);
	
	var linea1 = new fabric.Line([0,cury+35,750,cury+35],{stroke:"black"});
	canvas.add(linea1);
	
	var nitcliente = $("#nit_cliente_fact").text();
	var nomecliente = $("#nombre_cliente_fact").text();
	var dirclientee = $("#dir_cliente_fact").val();
	var telclientee = $("#tel_cliente_fact").val();
	var cityclientee = $("#city_cliente_fact").val();
	var estclientee = $("#est_cliente_fact").val();
	var repclientee = $("#rep_cliente_fact").val();
	
	cury+=70;
	
	var cli_nombre="Señor(a): "+repclientee;
	var client_nombre = new fabric.Text(cli_nombre, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(client_nombre);
	
	cury+=40;
	var cli_est="Establ: "+estclientee;
	var client_est = new fabric.Text(cli_est, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30, fontWeight:"bold" });
	canvas.add(client_est);
	cury+=40;
	var cli_nit="CC-Nit: "+nitcliente;
	var client_nit = new fabric.Text(cli_nit, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(client_nit);
	cury+=40;
	var cli_direccion="Dirección: "+dirclientee;
	var client_direccion = new fabric.Text(cli_direccion, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(client_direccion);
	cury+=40;
	var cli_telefono="Teléfono: "+telclientee;
	var client_telefono = new fabric.Text(cli_telefono, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(client_telefono);
	cury+=40;
	var cli_ciudad="Ciudad: "+thecities[cityclientee];
	var client_ciudad = new fabric.Text(cli_ciudad, { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30});
	canvas.add(client_ciudad);
	
	cury+=70;
	var linea2 = new fabric.Line([0,cury+35,750,cury+35],{stroke:"black"});
	canvas.add(linea2);
	
	

	var titulo_prod = new fabric.Text("DETALLE DE PRODUCTOS", { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30, fontWeight:"bold" });
	canvas.add(titulo_prod);
	
	var linea3 = new fabric.Line([0,cury+35,750,cury+35],{stroke:"black"});
	canvas.add(linea3);
	cury+=40;
	var titulo_prod = new fabric.Text("CANT.   IMP    VR/UNIT.   TOTAL PROD", { left: 15, top: cury, fontFamily: 'Lucida Console',fontSize: 30, fontWeight:"bold" });
	canvas.add(titulo_prod);
	
	var linea3 = new fabric.Line([0,cury+35,750,cury+35],{stroke:"black"});
	canvas.add(linea3);

	cursory=cury;
	var ttaxi=0;
	var tbrutox=0;
	
	
	
	cursory+=40;
	for(i=0;i<curorden.length;i++){
		cursorx=0;
		nombre_pr=curorden[i].nombre;
		cantidad=curorden[i].cantidad;
		impuesto=curorden[i].impuesto;
		impuesto_vb=curorden[i].impuesto_vb;
		precio=curorden[i].precio;
		brutus=curorden[i].bruto;
		tbrutox+=brutus;
		bruto=formatter.format(brutus);
		tax=curorden[i].tax;
		ttaxi+=tax;
		cursory+=10;
		
		var elprod = new fabric.Text(nombre_pr, { left: 15, top: cursory, fontFamily: 'Lucida Console',fontSize: 30});
		canvas.add(elprod);
		var linetwo="";
		linetwo+=cantidad+" ";
		for(k=linetwo.length;k<8;k++){
			linetwo+=" ";
		}
		linetwo+=impuesto_vb+" ";
		for(k=linetwo.length;k<16;k++){
			linetwo+=" ";
		}
		linetwo+=precio+" ";
		for(k=linetwo.length;k<16;k++){
			linetwo+=" ";
		}

		for(k=linetwo.length;k<(38-bruto.length);k++){
			linetwo+=" ";
		}
		
		linetwo+=bruto+" ";
		cursory+=40;
		var lainfo = new fabric.Text(linetwo, { left: 15, top: cursory, fontFamily: 'Lucida Console',fontSize: 30});
		canvas.add(lainfo);
		cursory+=40
		var lineadin = new fabric.Line([0,cursory,750,cursory],{stroke:"black"});
		canvas.add(lineadin);
		
	}
	cursory+=30
	var lineadin = new fabric.Line([0,cursory,750,cursory],{stroke:"black"});
	canvas.add(lineadin);
	cursory+=30
	tbrutof=formatter.format(tbrutox);
	
	var subtot = new fabric.IText("Total Parcial: "+tbrutof, {top: cursory, fontFamily: 'Lucida Console',fontSize: 30, originX: 'right', textAlign: "right", left: 730});
	canvas.add(subtot);
	
	cursory+=40
	ttaxf=formatter.format(ttaxi);
	var timpu = new fabric.Text("IVA: "+ttaxf, {top: cursory, fontFamily: 'Lucida Console',fontSize: 30, originX: 'right', textAlign: "right", left: 730});
	canvas.add(timpu);
	
	cursory+=40
	var totgen=ttaxi+tbrutox;
	totgenf=formatter.format(totgen);
	var tgral = new fabric.Text("Valor a Pagar: $ "+totgenf, {top: cursory, fontFamily: 'Lucida Console',fontSize: 30, originX: 'right', textAlign: "right", left: 730});
	canvas.add(tgral);
	
	cursory+=30
	var lineadin = new fabric.Line([0,cursory,750,cursory],{stroke:"black"});
	canvas.add(lineadin);
	
	cursory+=70
	var resola=sessionStorage.getItem("ruta_rs");
	var resol=resola.split(" ");
	var resolok="";
	var cnr=0;
	for(s=0;s<resol.length;s++){
		var let=resol[s];
		var len=resol[s].length;
		cnr+=len;
		if(cnr>32){
			resolok+="\n";
			resolok+=let;
			cnr=0;
		}else{
			resolok+=" "+let;
		}
	}
	
	
	totgenf=formatter.format(totgen);
	var resold = new fabric.Text(resolok, { left: 20, top: cursory, fontFamily: 'Lucida Console',fontSize: 28});
	canvas.add(resold);
	
	cursory+=110;

	var tgral = new fabric.Text("*** Gracias por su compra ***", {top: cursory, fontFamily: 'Lucida Console',fontSize: 30, originX: 'right', textAlign: "center", left: 580});
	canvas.add(tgral);
	
	canvas.renderAll();
	setTimeout(function(){
		$.mobile.changePage("#printfact");
		var dataURL = canvas.toDataURL('image/jpeg');
		var filenm='INSTAFACT_'+sessionStorage.getItem('prefix')+'-'+sessionStorage.getItem('fopen')+'.jpg';
		//dataURL = dataURL.replace(/^data:image\/[^;]*/, 'data:application/octet-stream');
		//dataURL = dataURL.replace(/^data:application\/octet-stream/, 'data:application/octet-stream;headers=Content-Disposition%3A%20attachment%3B%20filename=factura.jpg');
		
		var link=document.getElementById("linkprint");
		link.href = dataURL;
		link.download = "INSTAFACT_"+sessionStorage.getItem("prefix")+"-"+sessionStorage.getItem("fopen")+".jpg";
		$.mobile.loading("hide");
	},2000);
}



function filtrarTr(filtro,clasedivs){
	valor=$("#"+filtro).val();
	if(valor!=""){
		$("."+clasedivs+":Contains('"+valor+"')").css("display","");
		$("."+clasedivs+":Contains('"+valor+"') > h4 > a[aria-expanded]").attr("aria-expanded","true");
		$("."+clasedivs+":Contains('"+valor+"') > div.panel-collapse").addClass("in");
		$("."+clasedivs+":not(:Contains('"+valor+"')) > div.panel-collapse").removeClass("in");
		$("."+clasedivs+":not(:Contains('"+valor+"')) > h4 > a[aria-expanded]").attr("aria-expanded","false");
		$("."+clasedivs+":not(:Contains('"+valor+"'))").css("display","none");
		
	}else{
		$("."+clasedivs).css("display","");
		$("."+clasedivs+" > div.panel-collapse").removeClass("in");
		$("."+clasedivs+" > h4 > a[aria-expanded]").attr("aria-expanded","false");
	}
}

function downNumber(control){
	var valor=parseInt($("#"+control).val());
	if(valor>1){
		$("#"+control).val(valor-1);
		$("#"+control).trigger("change");
	}
}

function upNumber(control){
	var valor=parseInt($("#"+control).val());
	$("#"+control).val(valor+1);
	$("#"+control).trigger("change");
	
}

function toggleCat(id_cat){
	$("#chevy_"+id_cat).toggleClass("fa-chevron-circle-right fa-chevron-circle-down");
	$(".cattie_"+id_cat).toggleClass("hidden");
}


function goTop(event){
	$('html, body').stop().animate({
		'scrollTop': parseInt($(event.target).offset().top-90)
	}, 900, 'swing', function () {
		//window.location.hash = target;
	});
}