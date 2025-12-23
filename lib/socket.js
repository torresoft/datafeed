var datafeed=null;
var tipoio=typeof(io);
console.log("01." + tipoio);
var niwfresh=1;
  $(document).ready(function(){
    if(tipoio!="undefined"){
      
      if(localStorage.getItem("rs_token")==null){
        console.log("02. Token");
        let endpoint="/user/auth";
        let username=$("#username").val();
        let usertoken=$("#usertoken").val();
        let params={username:username,password:usertoken};
        $.ajax({
          url : server+endpoint,
          type: "POST",
          data : params,
          timeout:5000,
          headers:{},
          success: function(data, textStatus, jqXHR)
            {
              if(data.status=="goin"){
                
                localStorage.setItem("sockets",1);
                localStorage.setItem("rs_token",data.token);
                localStorage.setItem("rs_resudi",data.id_user);
                localStorage.setItem("rs_ssubdi",data.id_bus);
                initDF()
                console.log("03. Init");
                $("#alertaimpresora").hide();
              }else{
                console.log(data);
                localStorage.setItem("sockets",0);
                $("#alertaimpresora").show();
              }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
              localStorage.setItem("sockets",0);
              console.log("No se pudo autenticar el servicio de sockets");
              console.log(errorThrown);
              $("#alertaimpresora").show();
            }
        });
      }else{
        initDF();
        console.log("02. Init");
        localStorage.setItem("sockets",1);
        $("#alertaimpresora").hide();
      }
    }
    setInterval(calcTimers,60000);

})



function initDF(){
  
  datafeed = io.connect(server,{'query': 'token=' + localStorage.getItem("rs_token"), secure: true,reconnection:true, transports: ['websocket']});
  datafeed.on('connect', function() {
    console.log("Conectado....");
    datafeed.emit("rest_entra");
    $("#alertaimpresora").hide();
  });


  datafeed.on('disconnect', function() {
    console.log("Desconectado....");
    $("#alertaimpresora").show();
  });

  datafeed.on('ciao', function() {
    alert('Sesion iniciada en otro dispositivo');
    location.href = "./login.php";
  });
  
  datafeed.on('reconnect', function() {
    console.log("Reconectando....");
    $("#alertaimpresora").hide();
  });

  datafeed.on('error', function(error) {
    console.log(error);
    //console.log(localStorage.getItem("rs_token"));
    $("#state_proceso").html("<meta http-equiv='refresh' content='0; url=login.php'>");
  });
  datafeed.on("mesa_ocupada", function(info){
    let id_mesa=info.id_mesa;
    let id_pedido=info.id_pedido;
    let tender=info.tender;
    
    var minutos=0;
    var units="Min";
    if(minutos>60){
      minutos=parseInt(minutos/60);
      units="Hrs";
    }
    var tipo=$("#tbl_"+id_mesa).attr("t");
    if(tipo!="D"){
      var usr=$("#_uau_"+tender).val();
      var nombre=$("#tbl_"+id_mesa).find("p").first().text();
      var mesan=nombre.replace("MESA ","").replace("mesa ","");
      
      $("#tbl_"+id_mesa).attr("timer",moment(new Date()).format("YYYY-MM-DD HH:mm:ss"));
      $("#tbl_"+id_mesa).find(".tdr").html("<i class='fa fa-user'></i> "+usr);
      $("#infoboxa_"+id_mesa).removeClass("bg-grey bg-orange bg-gray");
      $("#infoboxa_"+id_mesa).addClass("bg-red");
      $("#infoboxa_"+id_mesa).find(".inner").css("color","#fff");
      $("#infoboxa_"+id_mesa).find("h3, p, div, span").css("color","#fff !important");
      $("#tbl_"+id_mesa).find("p").first().html(mesan);
      $("#mesa_icon_"+id_mesa).removeClass().addClass("fa fa-cutlery");
      $("#mesa_sillas_"+id_mesa).html("P#"+id_pedido);
      $("#tbl_"+id_mesa).find("span.pull-right").first().html("$0");
      $("#tbl_"+id_mesa).off();
      $("#tbl_"+id_mesa).attr("lnk-tsf","#mesa-"+id_mesa);
      $("#tbl_"+id_mesa).attr("lnk-cont","contenidos-aux");
      $("#tbl_"+id_mesa).attr("onclick","loadMask('mviews.php?flag=opentable&id_mesa="+id_mesa+"&id_pedido="+id_pedido+"&t="+tipo+"')");
    }
    tryhovers();
    notifyMe("Nuevo pedido No."+id_pedido);
  });


  datafeed.on("mesa_liberada", function(info){
    let id_mesa=info.id_mesa; 
    console.log(id_mesa);
    $("#infoboxa_"+id_mesa).removeClass("bg-red bg-orange");
    $("#infoboxa_"+id_mesa).addClass("bg-gray");
    let tipo=$("#tbl_"+id_mesa).attr("t");
    var nombre=$("#tbl_"+id_mesa).find("p").first().text();
    var mesan=nombre.replace("MESA ","").replace("mesa ","");
    var icon="fa-qrcode";
    if(tipo=="E"){
      icon="fa-umbrella";
    }else if(tipo=="D"){
      icon="fa-motorcycle";
    }
    
    $("#tbl_"+id_mesa).find(".tdr").html("");
    $("#infoboxa_"+id_mesa).find(".inner").css("color","#333");
    $("#infoboxa_"+id_mesa).find("h3, p, div, span").css("color","#333 !important");
    $("#tbl_"+id_mesa).find("p").first().html(mesan);
    $("#mesa_icon_"+id_mesa).removeClass().addClass("fa "+icon);
    $("#mesa_sillas_"+id_mesa).html("Libre");
    $("#tbl_"+id_mesa).find("span.pull-right").first().html(tipo=="D" ? "" : "$0");
    $("#pg_description_k_"+id_mesa).text("");
    $("#tbl_"+id_mesa).attr("timer",0);
    $("#tbl_"+id_mesa).off();
    $("#tbl_"+id_mesa).removeAttr("lnk-tsf");
    $("#tbl_"+id_mesa).removeAttr("lnk-cont");
    $("#tbl_"+id_mesa).attr("onclick","getDialog('mviews.php?flag=create_pedido&id_mesa="+id_mesa+"&t="+tipo+"','600','Crear Pedido')");
    if($("#chefprofile_loader").length>0){
      cargaHTMLvars('contenidos','mviews.php?flag=home');
    }
    tryhovers();
  })

  datafeed.on("mesa_enviada", function(info){
    let id_mesa=info.id_mesa; 
    let tipo=$("#tbl_"+id_mesa).attr("t");
    if(tipo!="D"){
      $("#infoboxa_"+id_mesa).removeClass("bg-red bg-grey bg-gray");
      $("#infoboxa_"+id_mesa).addClass("bg-orange");
      $("#mesa_icon_"+id_mesa).removeClass().addClass("fa fa-fire");
      tryhovers();
    }else{
      $("#infoboxa_"+id_mesa).removeClass("bg-red bg-orange");
      $("#infoboxa_"+id_mesa).addClass("bg-gray");
      var nombre=$("#tbl_"+id_mesa).find("p").first().text();
      var mesan=nombre.replace("MESA ","").replace("mesa ","");
      
      $("#tbl_"+id_mesa).find(".tdr").html("");
      $("#infoboxa_"+id_mesa).find(".inner").css("color","#333");
      $("#infoboxa_"+id_mesa).find("h3, p, div, span").css("color","#333 !important");
      $("#tbl_"+id_mesa).find("p").first().html(mesan);
      $("#mesa_icon_"+id_mesa).removeClass().addClass("fa fa-motorcycle");
      $("#mesa_sillas_"+id_mesa).html("Libre");
      $("#tbl_"+id_mesa).find("span.pull-right").first().html("");
      $("#pg_description_k_"+id_mesa).text("");
      $("#tbl_"+id_mesa).attr("timer",0);
      $("#tbl_"+id_mesa).off();
      $("#tbl_"+id_mesa).removeAttr("lnk-tsf");
      $("#tbl_"+id_mesa).removeAttr("lnk-cont");
      $("#tbl_"+id_mesa).attr("onclick","getDialog('mviews.php?flag=create_pedido&id_mesa="+id_mesa+"&t="+tipo+"','600','Crear Pedido')");
      tryhovers();
    }
    if($("#chefprofile_loader").length>0){
      cargaHTMLvars('contenidos','mviews.php?flag=home');
      suena();
    }
    
  })

  datafeed.on("cambios", function(rows){
    let arrayPeds=[];
    for(var i=0;i<rows.length;i++){
      arrayPeds[rows[i].ID_MESA]=rows[i];
    }
    $(".lasmesas").each(function(){
        var idm=$(this).attr("idm");
        var tipo=$(this).attr("t");
        var nombre=$(this).find("p").first().text();
        var mesan=nombre.replace("MESA ","").replace("mesa ","");
        
        if(arrayPeds[idm]==undefined){
          $("#infoboxa_"+idm).removeClass("bg-red bg-orange");
          $("#infoboxa_"+idm).addClass("bg-gray");
          $("#infoboxa_"+idm).find(".inner").css("color","#333");
          $("#infoboxa_"+idm).find("h3, p, div, span").css("color","#333 !important");
          $(this).find(".tdr").html("");
          $(this).find("p").first().html(mesan);
          
          var icon="fa-qrcode";
          if(tipo=="E"){
            icon="fa-umbrella";
          }else if(tipo=="D"){
            icon="fa-motorcycle";
          }
          $("#mesa_icon_"+idm).removeClass().addClass("fa "+icon);
          $("#mesa_sillas_"+idm).html("Libre");
          $(this).find("span.pull-right").first().html(tipo=="D" ? "" : "$0");
          $("#pg_description_k_"+idm).text("");
          $(this).off();
          $(this).attr("onclick","getDialog('mviews.php?flag=create_pedido&id_mesa="+idm+"&t="+tipo+"','600','Crear Pedido')");
          
        }else{
          var infoP=arrayPeds[idm];
          var estado_p=infoP.CHEF;
          var tipo=infoP.TIPO;
          var id_pedido=infoP.ID_PEDIDO;
          var total_ped=infoP.TOTAL_PEDIDO;
          var tender=infoP.ID_TENDER;
          var usr=$("#_uau_"+tender).val();
          if(total_ped==null) total_ped=0;
          var apertura=infoP.APERTURA;
          
          $("#pg_description_k_"+idm).text("");
          $(this).find(".tdr").html("<i class='fa fa-user'></i> "+usr);
          $(this).find("p").first().html(mesan);
          
          if(estado_p=="0000-00-00 00:00:00"){
            $("#infoboxa_"+idm).removeClass("bg-grey bg-orange bg-gray");
            $("#infoboxa_"+idm).addClass("bg-red");
            $("#infoboxa_"+idm).find(".inner").css("color","#fff");
            $("#infoboxa_"+idm).find("h3, p, div, span").css("color","#fff !important");
            $("#mesa_icon_"+idm).removeClass().addClass("fa fa-cutlery");
          }else{
            $("#infoboxa_"+idm).removeClass("bg-grey bg-red bg-gray");
            $("#infoboxa_"+idm).addClass("bg-orange");
            $("#infoboxa_"+idm).find(".inner").css("color","#fff");
            $("#infoboxa_"+idm).find("h3, p, div, span").css("color","#fff !important");
            $("#mesa_icon_"+idm).removeClass().addClass("fa fa-fire");
          }
          
          $("#mesa_sillas_"+idm).html("P#"+id_pedido);
          $(this).find("span.pull-right").first().html("$"+total_ped);

          $(this).attr("timer",apertura);
          $(this).off();
          $(this).attr("lnk-tsf","#mesa-"+idm);
          $(this).attr("lnk-cont","contenidos-aux");
          $(this).attr("onclick","loadMask('mviews.php?flag=opentable&id_mesa="+idm+"&id_pedido="+id_pedido+"&t="+tipo+"')");

        }
    })
    tryhovers();
  });


  datafeed.on("act_mesa", function(rows){
    let id_mesa=rows[0].ID_MESA;
    let total_ped=rows[0].TOTAL_PEDIDO;
    $("#tbl_"+id_mesa).find("span.pull-right").first().html("$"+number_format(total_ped,0));
  });


  datafeed.on("imprime_comanda", function(rs){
    let rows=rs.result;
    let id_pedido=rs.id_pedido;
    let tipocom=rs.tipo;
    var urlfinal=$('#prhs').val();
    console.log(rows);
    if(rows.length>0 && urlfinal!=""){
      $.ajax({
        type: 'POST',
        url: urlfinal,
        data: {contents: rows,tipo:tipocom},
        success: function(data, textStatus, jqXHR)
        {
          console.log(data);
          $.ajax({
            url : 'mviews.php?flag=setpedprint',
            type: 'POST',
            data : {id_pedido:id_pedido},
            timeout:5000,
            dataType: 'json'
          });
          console.log("Comanda-> "+id_pedido);
        },
        error : function(xhr, textStatus, errorThrown ) {
          $('#alertaimpresora').show();
          console.log('Motor de impresion local fuera' + errorThrown);
        }
      });
    }else{
      console.log("Nada para imprimir");
      $("#checobra_"+id_pedido).removeClass("hidden");
      $("#chefin_"+id_pedido).addClass("hidden");
    }
  });

  datafeed.on("imprime_precuenta", function(rs){
    let rows=rs.result;
    let id_pedido=rs.id_pedido;
    var urlfinal=$('#prhs').val();
    var propins=$('#clprp').val();
    if(rows.length>0 && urlfinal!=""){
      $.ajax({
        type: 'POST',
        url: urlfinal,
        data: {contents: rows,rectype:'pref',propina:propins},
        success: function(data, textStatus, jqXHR)
        {
          console.log(data);
          $('#alertaimpresora').hide();
          console.log("Precuenta->" + id_pedido);
        },
        error : function(xhr, textStatus, errorThrown ) {
          $('#alertaimpresora').show();
          console.log('Motor de impresion local fuera' + errorThrown);
        }
      });
    }
  });

  //ABONOS
  datafeed.on("imprime_abono", function(rs){
    let rows=rs.result;
    let id_pedido=rs.id_pedido;
    var urlfinal=$('#prhs').val();
    if(rows.length>0 && urlfinal!=""){
      $.ajax({
        type: 'POST',
        url: urlfinal,
        data: {contents: rows,rectype:'abono'},
        success: function(data, textStatus, jqXHR)
        {
          console.log(data);
          $('#alertaimpresora').hide();
          console.log("Abono->" + id_pedido);
        },
        error : function(xhr, textStatus, errorThrown ) {
          $('#alertaimpresora').show();
          console.log('Motor de impresion local fuera' + errorThrown);
        }
      });
    }
  });

  
  datafeed.on("imprime_factura", function(rs){
    let rows=rs.result;
    let id_factura=rs.id_factura;
    var urlfinal=$('#prhs').val();
    console.log(id_factura);
    if(rows.length>0 && urlfinal!=""){
      $.ajax({
        type: 'POST',
        url: urlfinal,
        data: {contents: rows,rectype:'fact'},
        success: function(data, textStatus, jqXHR)
        {
          console.log(data)
          $('#alertaimpresora').hide();
          console.log("Factura-> "+id_factura);
          $.ajax({
            url : 'mviews.php?flag=setfacprint',
            type: 'GET',
            data : {id_factura:id_factura},
            timeout:5000,
            dataType: 'json'
          });
        },
        error : function(xhr, textStatus, errorThrown ) {
          $('#alertaimpresora').show();
          console.log('Motor de impresion local fuera' + errorThrown);
        }
      });
    }
  });

  datafeed.on("pong", function(rs){
    console.log("---check---")
  })
  var mint=setInterval(sockEmitir('ping',{}),30000);

  datafeed.on("mk_listo", function(rs){
    var id_pedido= rs.id_pedido;
    var item= rs.id_item;
    var val= rs.val;

    if($("#chefprofile_loader").length>0){
      $("#cook_"+item).prop("checked",val);
      if(val==1){
        $("#cook_"+item).parent().removeClass("list-group-item-danger");
        $("#cook_"+item).parent().addClass("list-group-item-success");
      }else{
        $("#cook_"+item).parent().addClass("list-group-item-danger");
        $("#cook_"+item).parent().removeClass("list-group-item-success");
        
      }
      validaEntrega(id_pedido,item,val);

    }
  });

}


function validaEntrega(id_pedido,id_item,valor){
  if($('.itemsped_'+id_pedido+':not(:checked)').length==0){
    $('#dispach_'+id_pedido).css('display','');
  }else{
    $('#dispach_'+id_pedido).css('display','none');
  }
}



function calcTimers(){
  $(".lasmesas").each(function(){
    var id_mesa=$(this).attr("idm");
    var apertura=$(this).attr("timer");
    if(apertura!="0" && apertura!=undefined){
      var now = moment(new Date()); 
      var end = moment(apertura);
      var duration = moment.duration(now.diff(end));
      var minutos=parseInt(duration.asMinutes());
      var units="Min";
      if(minutos>60){
        minutos=parseInt(minutos/60);
        units="Hrs";
      }
      $("#pg_description_k_"+id_mesa).text(minutos+" "+units);
    }else{
      $("#pg_description_k_"+id_mesa).text("");
    }
   
  });
}

function sockEmitir(evento,params){
  datafeed.emit(evento,params);
}



function notifyMe(bodyy,timeout,dta)  {
	if(timeout==undefined || timeout==null){
		timeout=7000;
	}
	//console.log(timeout);
	if(niwfresh>0){
		if  (!("Notification"  in  window))  {   
			
		}  
		else  if  (Notification.permission  ===  "granted")  {
			var  options  =   {
				body:   bodyy,
				icon:   "icon.ico",
				data: dta,
				requireInteraction: true
			};
			var  notification  =  new  Notification("DataFeed", options);
			notification.onclick = function(event) {
				window.focus();
				notification.close();
			};
			setTimeout( function() { notification.close() }, timeout);
			
		}  
		else  if  (Notification.permission  !==  'denied')  {
			Notification.requestPermission(function (permission)  {
				if  (!('permission'  in  Notification))  {
					Notification.permission  =  permission;
				}
				if  (permission  ===  "granted")  {
					var  options  =   {
						body:   bodyy,
						icon:   "icon.ico",
						data: dta,
						requireInteraction: true
					};     
					var  notification  =  new  Notification("DataFeed", options);
					notification.onclick = function(event) {
						window.focus();
						notification.close();
					};
					setTimeout( function() { notification.close() }, timeout);
				}   
			});  
		}
	}
}	
