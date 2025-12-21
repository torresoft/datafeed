<?php
class dsTables{
	
	//TABLA CON RELACIONES PARA VER CONTENIDO Y MODIFICAR, BORRAR O AGREGAR
	function armaTablaDyRel($tabla,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$fkf=array(),$extrafn=array(),$son=array()){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		$campos=$relaciones[$tabla]["campos"];
		$aliastabla=$relaciones[$tabla]["alias"];
		$rnd_table="tabla_$tabla";
		$html_result="<div class='box box-default'><div class='box-header'>GESTI&Oacute;N DE DATOS: <b>".strtoupper($aliastabla)."</b>
		</div>";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		if(sizeof($campos)==0){
			echo "Falta archivo de relaciones";
			exit;
		}else{
			$camps="";
			$tbheader="";
			$rels=array();
			$condS="";
			foreach($campos as $campo=>$data){
				$camps.=$campo.",";
				$tipocampo=$data["type"];
				$alias=$data["alias"];
				if($campo==$Pname){
					$tbheader.="<td data-priority='1'>$alias</td>";
					
				}else{
					$tbheader.="<td>$alias</td>";
				}
				if($tipocampo=="rel"){
					$rels[$campo]=array($data["table"],$data["name"],$data["fk"]);
					
					if(array_key_exists($data["table"],$fkf)){
						$condS.=" AND $campo IN (SELECT {$data["fk"]} AS $campo FROM {$data["table"]} WHERE";
						foreach($fkf[$data["table"]] as $fku=>$vku){
							$condS.=" $fku='$vku' AND ";
						}
						$condS=substr($condS,0,-5);
						$condS.=")";
					}else{
						$condS="";
					}
				}
			}
			$camps=substr($camps,0,strlen($camps)-1);
			$consultaSQL="SELECT $camps FROM $tabla WHERE $filter $condS ORDER BY $orderby";
		}
		
		$arkeycontent=array();
		foreach($rels as $Fkey=>$df){
			$Ftabla=$df[0];
			$Fname=$df[1];
			$FoK=$df[2];
			$resultFk = $gf->dataSet("SELECT $FoK, $Fname FROM $Ftabla ORDER BY $Fname",array());
		
			$arkeycontent[$Fkey]=array();
			if($resultFk!=false && count($resultFk)>0){
				foreach($resultFk as $rowFk){
					$idFk=$rowFk[$FoK];
					$nameFk=$rowFk[$Fname];
					$arkeycontent[$Fkey][$idFk]=$nameFk;
					$arkeycontent[$Fkey][0]="";
				}
			}
		}

		$fks=array();
		if(count($son)>0){
			$tabson=$son["tabla"];
			$fkson=$son["fk"];
			$condfk=isset($son["cond"]) ? $son["cond"] : "1";
			if($condfk==""){
				$condfk="1";
			}
			$rsson=$gf->dataSet("SELECT $fkson, COUNT($fkson) AS CUENT FROM $tabson WHERE $condfk GROUP BY $fkson ORDER BY 1",array());
			foreach($rsson as $rwson){
				$di=$rwson[$fkson];
				$cu=$rwson["CUENT"];
				$fks[$di]=$cu;
			}
		}
	
		$resultG = $gf->dataSet($consultaSQL,array());

		$html_result.="<div class='box-body'><table class='datatables table table-striped table-bordered table-hover no-margin-bottom no-border-top' width='100%' id='$rnd_table'>";
		if($resultG!=false && count($resultG)>0){
			$html_result.="<thead><tr>$tbheader";
			if(($modify!=0 || $delete!=0)){
				$html_result.="<td  data-priority='1'>Acciones</td>";
			}
			$html_result.="</tr></thead>
			<tbody>";
			foreach($resultG as $rowG){
				$llave=$rowG[$Pkey];
				$html_result.="<tr id='ls_$llave'>";
				$i=0;
				foreach ($campos as $nombre_campo=>$params){
					$tipo_campo=$params["type"];
					
					if(isset($params["mask"])){
						$tipo_campo="password";
					}
					$contenido=$rowG[$nombre_campo];
					if($tipo_campo=="text"){
						$html_result.="<td>$contenido</td>";
					}elseif($tipo_campo=="color"){
						$html_result.="<td style='background-color:#$contenido !important;'>$contenido</td>";
					}elseif($tipo_campo=="password"){
						$html_result.="<td>********</td>";
					}elseif($tipo_campo=="number" || $tipo_campo=="float"){
						$html_result.="<td>$contenido</td>";
					}elseif($tipo_campo=="textarea" || $tipo_campo=="wysiwyg"){
						$html_result.="<td><div style='max-width:180px; max-height:60px; overflow:auto;'>$contenido</div></td>";
					}elseif($tipo_campo=="boolean"){
						$html_result.="<td>";
						if($contenido==1){
								$html_result.="Si";
						}else{
							$html_result.="No";
						}
						$html_result.="</td>";
					}elseif($tipo_campo=="gps"){
						$html_result.="<td><img src='misc/marker.png' style='width:18px;' /></td>";
					}elseif($tipo_campo=="curuser"){
						
						$html_result.="<td><i class='fa fa-user'></i></td>";
					}elseif($tipo_campo=="array"){
						$html_result.="<td>".$params["arraycont"][$contenido]."</td>";
					}elseif($tipo_campo=="rel"){
						$html_result.="<td>".$arkeycontent[$nombre_campo][$contenido]."</td>";
					}else{
						$html_result.="<td>$contenido</td>";
					}

				}
				if(($modify!=0 || $delete!=0)){
					$html_result.="<td>";
					if($modify==1){
						$html_result.="<button class='btn btn-xs btn-warning' onclick=\"getDialog('$sender?flag=editar&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&amp;Vkey=$llave','600','Editar\ Elemento')\"><i class='fa fa-edit'></i></button> | ";
						
					}
					if($delete==1){
						if(!isset($fks[$llave])){
							$html_result.="<button class='btn btn-xs btn-danger' id='img_$Pkey$llave' onclick=\"goErase('$tabla','$Pkey','$llave','ls_$llave','1')\"><i class='fa fa-remove'></i></button>";
						}else{
							$html_result.="<button class='btn btn-xs btn-default' id='img_$Pkey$llave' onclick=\"msgBox('No se puede eliminar por restricci&oacute;n de integridad')\"><i class='fa fa-lock'></i></button>";
						}
					}

					foreach($extrafn as $fnex){
						$btn_name=$fnex["nombre"];
						$btn_icon=$fnex["icono"];
						$btn_clase=$fnex["clase"];
						$btn_fn=$fnex["funcion"];
						$btn_cont=$fnex["contenedor"];
						if($btn_cont==""){
							$clickfn="getDialog('$btn_fn&key=$llave','$btn_name')";
						}else{
							$clickfn="cargaHTMLvars('$btn_cont','$btn_fn&key=$llave')";
						}
						$html_result.=" | <button class='btn btn-xs $btn_clase' id='img_$Pkey$llave' onclick=\"$clickfn\" title='$btn_name'><i class='fa $btn_icon'></i></button>";
					}

					$html_result.="</td>";
				}
				$html_result.="</tr>";
			}
			
	
			
		}
		$html_result.="</tbody></table>";
		if($add==1){
			$newopts="flag=nuevo&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue";
			$html_result.="<div class='panel-footer'><input type='button' class='btn btn-primary' value='Agregar Elemento' onclick=\"getDialog('$sender?$newopts','500','Agregar\ Registro')\" /></div>";
		}
		$html_result.="</div></div>";
		return $html_result;
	}

	
	//TABLA CON RELACIONES PARA VER CONTENIDO Y MODIFICAR, BORRAR O AGREGAR
	function armaTablaPaginate($tabla,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$fkf=array(),$extrafn=array(),$records=10,$page=1,$search="",$flag="ver",$no_results="",$searchcamps=array()){
		$gf=new generalFunctions;
		global $relaciones;
		if(isset($relaciones[$tabla])){
			$Pkey=$relaciones[$tabla]["pkey"];
			$Pname=$relaciones[$tabla]["pname"];
			$campos=$relaciones[$tabla]["campos"];
			$aliastabla=$relaciones[$tabla]["alias"];
			
		}else{
			$aliastabla="DATOS";
			
			$rsRecords=$gf->dataSet("
			SELECT COLUMN_NAME, DATA_TYPE, EXTRA LIKE '%auto_increment%' AS AUTOS FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :table AND table_schema=:db
			",array(":table"=>$tabla,":db"=>SITE_DB));
			$campos=array();
			if(count($rsRecords)>0){
				$Pkey=$rsRecords[0]["COLUMN_NAME"];
				$Pname=$rsRecords[0]["COLUMN_NAME"];
				foreach($rsRecords as $rwRecords){
					$nome=$rwRecords["COLUMN_NAME"];
					$typecam=$rwRecords["DATA_TYPE"];
					$campos[$nome]=array("alias"=>$nome);
					switch($typecam){
						case "int": 
							$campos[$nome]["type"]="number";
							break;
						case "text":
							$campos[$nome]["type"]="text";
							break;
						case "date":
							$campos[$nome]["type"]="date";
							break;
						case "datetime":
							$campos[$nome]["type"]="datetime";
							break;
						case "double":
							$campos[$nome]["type"]="number";
							break;
						case "tinyint":
							$campos[$nome]["type"]="boolean";
							break;
					}
				}
			}else{
				
			}
		}

		if($add==1){
			$newopts="flag=nuevo&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue";
			$addbuttons="
				<a class='pull-right btn btn-xs btn-info' title='Nuevo Registro' href=\"javascript:getDialog('$sender?$newopts','500','Agregar\ Registro')\">
					<i class='ace-icon fa fa-plus blue'></i>
				</a>
			";
		}else{
			$addbuttons="";
		}


		

		$pg=$page;

		$prev = ($pg>1) ? $pg-1 : $pg;
		$next = $pg+1;
		$ini=($pg==1) ? 0 : ($pg-1)*$records;
		$inis=($pg==1) ? 1 : (($pg-1)*$records)+1;


		$rnt=rand(0,999999999999);
		$rnd_table="tabla_$tabla".$rnt;
		$header="
		<div class='box transparent'>
			<div class='box-header'>
			
					GESTI&Oacute;N DE DATOS: <b>".strtoupper($aliastabla)."</b>
		
			
					<a class='pull-right btn btn-warning btn-xs' href='javascript:togHome()'>
						<i class='ace-icon fa fa-remove'></i>
					</a>
			
					$addbuttons
			</div>
			<div class='box-body'>
			<div class='widget-main'>
			";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		$hasmun=false;
		if(sizeof($campos)==0){
			echo "Falta archivo de relaciones";
			exit;
		}else{
			$camps="";
			$tbheader="";
			$rels=array();
			$condS="";
			foreach($campos as $campo=>$data){
				$camps.=$campo.",";
				$tipocampo=$data["type"];
				$alias=$data["alias"];
				if($campo==$Pname){
					$tbheader.="<th data-priority='1' class='bg-primary'>$alias</th>";
					
				}else{
					$tbheader.="<th class='bg-primary'>$alias</th>";
				}
				if($tipocampo=="rel"){
					$rels[$campo]=array($data["table"],$data["name"],$data["fk"]);
					
					if(array_key_exists($data["table"],$fkf)){
						$condS.=" AND $campo IN (SELECT {$data["fk"]} AS $campo FROM {$data["table"]} WHERE";
						foreach($fkf[$data["table"]] as $fku=>$vku){
							$condS.=" $fku='$vku' AND ";
						}
						$condS=substr($condS,0,-5);
						$condS.=")";
					}else{
						$condS="";
					}
				}elseif($tipocampo="municipio"){
					$hasmun=true;
				}
			}


			$busca=$search;
			if(strlen($busca)>3){
				$condsearch="($Pname LIKE '%$busca%' OR $Pkey LIKE '%$busca%'";
				if(count($searchcamps)>0){
					foreach ($searchcamps as $pcamp) {
						$condsearch.=" OR $pcamp LIKE '%$busca%'";
					}
				}
				$sirchin="Buscando \"$busca\":";
				foreach($rels as $idcr=>$incr){
					$tbcr=$incr[0];
					$nmcr=$incr[1];
					$fkcr=$incr[2];
					$condsearch.=" OR $idcr IN(SELECT $fkcr FROM $tbcr WHERE $nmcr LIKE '%$busca%' ORDER BY $fkcr) ";
				}
				$condsearch.=")";
			}else{
				$condsearch="1";
				$sirchin="";
			}


			$camps=substr($camps,0,strlen($camps)-1);
			if(isset($relaciones[$tabla]["sharedKey"])){
				$PKeyC="CONCAT(";
				foreach($relaciones[$tabla]["sharedKey"] as $kk){
					$PKeyC.=$kk.",'-',";
				}
				$PKeyC=substr($PKeyC,0,-5).")";
			}else{
				$PKeyC=$Pkey;
			}
			$consultaSQL="SELECT $PKeyC AS PKEY,$camps FROM $tabla WHERE $filter $condS AND $condsearch ORDER BY $orderby LIMIT $ini, $records";
		}
		
		$consultaLL="SELECT COUNT(*) AS CUENTA FROM $tabla WHERE $filter $condS AND $condsearch ORDER BY $orderby";
		$cuenta=$gf->dataSet($consultaLL);
		$allrecords = count($cuenta)>0 ? $cuenta[0]["CUENTA"] : 0; 

		$npis=$allrecords>0 ? ceil($allrecords/$records) : 1;

		$intermedios="";
		$arkeycontent=array();
		foreach($rels as $Fkey=>$df){
			$Ftabla=$df[0];
			$Fname=$df[1];
			$FoK=$df[2];
			$resultFk = $gf->dataSet("SELECT $FoK, $Fname FROM $Ftabla ORDER BY $Fname");
		
			$arkeycontent[$Fkey]=array();
			if($resultFk!=false && count($resultFk)>0){
				foreach($resultFk as $rowFk){
					$idFk=$rowFk[$FoK];
					$nameFk=$rowFk[$Fname];
					$arkeycontent[$Fkey][$idFk]=$nameFk;
					$arkeycontent[$Fkey][0]="";
				}
			}
		}
		
		$busca=$gf->deutf8($busca);
		$resultG = $gf->dataSet($consultaSQL);
		$html_table="<table class='datatables-simple table table-striped table-bordered table-hover no-margin-bottom no-border-top' width='100%' id='$rnd_table'>";
		if($resultG!=false && count($resultG)>0){
			$html_table.="<thead><tr>$tbheader";
			if(($modify!=0 || $delete!=0)){
				$html_table.="<th  data-priority='1' class='bg-danger'>Acciones</th>";
			}
			$html_table.="</tr></thead>
			<tbody>";
			$ncur=0;
			foreach($resultG as $rowG){
				
				
				
				$llave=$rowG["PKEY"];
				if(isset($relaciones[$tabla]["sharedKey"])){
					$llave="";
					foreach($relaciones[$tabla]["sharedKey"] as $idkk){
						$llave.=$rowG[$idkk]."-";
					}
					$llave=substr($llave,0,-1);
				}
				$html_table.="<tr id='ls_$llave'>";
				$i=0;
				foreach ($campos as $nombre_campo=>$params){
					$tipo_campo=$params["type"];
					
					if(isset($params["mask"])){
						$tipo_campo="password";
					}
					$contenido=$rowG[$nombre_campo];
					if($tipo_campo=="text"){
						$html_table.="<td>$contenido</td>";
					}elseif($tipo_campo=="color"){
						$html_table.="<td bgcolor='$contenido'>$contenido</td>";
					}elseif($tipo_campo=="password"){
						$html_table.="<td>********</td>";
					}elseif($tipo_campo=="number"){
						$html_table.="<td>$contenido</td>";
					}elseif($tipo_campo=="textarea" || $tipo_campo=="wysiwyg"){
						$html_table.="<td><div style='max-width:180px; max-height:60px; overflow:auto;'>$contenido</div></td>";
					}elseif($tipo_campo=="boolean"){
						$html_table.="<td>";
						if($contenido==1){
								$html_table.="Si";
						}else{
							$html_table.="No";
						}
						$html_table.="</td>";
					}elseif($tipo_campo=="img64"){
						$html_table.="<td><img src='misc/img.png' style='width:18px;' /></td>";
					}elseif($tipo_campo=="gps"){
						$html_table.="<td><img src='misc/marker.png' style='width:18px;' /></td>";
					}elseif($tipo_campo=="array"){
						if(isset($params["arraycont"][$contenido])){
							$vall=$params["arraycont"][$contenido];
						}else{
							$vall="No definido";
						}
						$html_table.="<td>".$vall."</td>";
					}elseif($tipo_campo=="municipio"){
						$html_table.="<td>".$armunis[$contenido]."</td>";
					}elseif($tipo_campo=="rel"){
						$valk=isset($arkeycontent[$nombre_campo][$contenido]) ? $arkeycontent[$nombre_campo][$contenido] : "No definido";
						$html_table.="<td>".$valk."</td>";
					}else{
						$html_table.="<td>$contenido</td>";
					}

				}
				if(($modify!=0 || $delete!=0 || count($extrafn)>0)){
					$html_table.="<td>
					<div class='btn-group dropleft'>
					<button type='button' class='btn btn-default dropdown-toggle btn-xs' data-toggle='dropdown' aria-expanded='false'>
					  Opciones <span class='caret'></span> 
					</button>
					<ul class='dropdown-menu pull-right'>
					";
					if($modify==1){
						$html_table.="<li><a onclick=\"getDialog('$sender?flag=editar&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&amp;Vkey=$llave&ent=$tabla','600','Editar\ Elemento')\"><i class='fa fa-edit'></i> Editar </a></li>";
						
					}
					if($delete==1){
						$html_table.="<li><a id='img_$Pkey$llave' onclick=\"goErase('$tabla','$Pkey','$llave','ls_$llave','1')\"><i class='fa fa-trash'></i> Borrar</a></li>";
					}
					foreach($extrafn as $fnex){
						$btn_name=$fnex["nombre"];
						$btn_icon=$fnex["icono"];
						$btn_clase=$fnex["clase"];
						$btn_fn=$fnex["funcion"];
						$btn_cont=$fnex["contenedor"];
						$btn_extra=$fnex["extra"];
						$btn_extra=str_replace("[key]",$llave,$btn_extra);
						if($btn_cont==""){
							$clickfn="getDialog('$btn_fn&key=$llave','$btn_name')";
						}else{
							$clickfn="cargaHTMLvars('$btn_cont','$btn_fn&key=$llave')";
						}
						$html_table.="<li><a class='stoppa' $btn_extra id='img_$Pkey$llave' onclick=\"$clickfn\" title='$btn_name'><i class='fa $btn_icon'></i> $btn_name</a></li>";
					}
					$html_table.="</ul>
				  </div></td>";
				}
				$html_table.="</tr>";
				$ncur++;
			}
			
			$fin=$ini+$ncur;
			$db_next = ($ncur<10) ? "disabled":"";
			$next = ($ncur<10) ? $pg:$next;
			$db_prev = ($pg==1) ? "disabled":"";
			$lastgo=($ncur<10) ? 0 : 1;
			
			$paginate="
			<table cellspacing='0' cellpadding='0' border='0' style='table-layout:auto;' class='ui-pg-table pull-right'>
				<tbody>
					<tr>

					
						<td id='pg_legend' class='pg_first' style='cursor: default;'>
							<span class='pg_range'>$sirchin $inis a $fin de $allrecords</span>
						</td>
						<td class='pg_prev btn btn-sm btn-warning $db_prev' style='cursor: default;margin:1px 5px;' onclick=\"getAux('$sender?flag=$flag&pg=1&last=1&ent=$tabla','','20000','unival_$tabla')\">
							<span class='fa  fa-angle-double-left bigger-140'></span>
						</td>
						<td class='pg_prev btn btn-sm btn-warning $db_prev' style='cursor: default;margin:1px 5px;' onclick=\"getAux('$sender?flag=$flag&pg=$prev&last=1&ent=$tabla','','20000','unival_$tabla')\">
							<span class='fa fa-angle-left bigger-140'></span>
						</td>
						<td>$intermedios</td>
						<td class='pg_next btn btn-sm btn-warning $db_next' style='cursor: default;margin:1px 5px;' onclick=\"getAux('$sender?flag=$flag&pg=$next&last=$lastgo&ent=$tabla','','20000','unival_$tabla')\">
							<span class=' fa fa-angle-right bigger-140'></span>
						</td>
						<td class='pg_next btn btn-sm btn-warning $db_next' style='cursor: default;margin:1px 5px;' onclick=\"getAux('$sender?flag=$flag&pg=$npis&last=$lastgo&ent=$tabla','','20000','unival_$tabla')\">
							<span class=' fa fa-angle-double-right bigger-140'></span>
						</td>
					</tr>
				</tbody>
			</table>
			";
			
		}else{
			$paginate="";
			$searchbar="";
			$colspand=count($campos);
			$html_table.="<tr><td colspan='$colspand'>No hay registros con el criterio seleccionado $search<br />
			$no_results
			</td></tr>";
		}
		$icnsr="fa-search";
		$addfn="dummy()";
		if($busca!=""){
			$icnsr="fa-remove red";
			$addfn="$('#sirisearch').val('')";
		}
		$searchbar="<div class='input-group'>
			<input type='text' name='search' id='sirisearch' placeholder='Buscar...' value='$busca' class='form-control unival_$tabla' onkeyup=\"testIntr(event,'getAux(\'$sender?flag=$flag&ent=$tabla\',\'\',\'10000\',\'unival_$tabla\')','getAux(\'$sender?flag=$flag&ent=$tabla\',\'\',\'10000\',\'unival_$tabla\')')\" />
			<span class='input-group-btn'>
				<button class='btn btn-default' type='button' onclick=\"$addfn;getAux('$sender?flag=$flag&ent=$tabla','','10000','unival_$tabla')\"><i class='fa $icnsr'></i></button>
			</span>
		</div>
		";
		$html_table.="</tbody><tfoot></tfoot></table>";
		
		$footer="</div></div></div>";
		return $header."<div class='row'><div class='col-md-6'>".$searchbar."</div><div class='col-md-6'>".$paginate."</div></div>".$html_table.$paginate.$footer;
	}

	
	//TABLA PARA NUEVO ITEM CON RELACIONES
	function devuelveTablaNewItemDyRel($tabla,$filterKey="null",$filterValue="null",$dialogo="",$callback="null",$fkf=array(),$ob=false,$plusk=array(),$extfilter="",$flts=array()){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		$campos=$relaciones[$tabla]["campos"];
		$html_result="<div class='box box-default'><div class='box-header'>NUEVO ELEMENTO</div>";
		if(sizeof($campos)==0){
			echo "Falta configuracion de relaciones";
			exit;
		}else{
			$rels=array();
			foreach($campos as $campo=>$data){
				$tipocampo=$data["type"];
				if($tipocampo=="rel"){
					$rels[$campo]=array($data["table"],$data["name"],$data["fk"]);
				}
			}
		}
		
		
		
		$arkeycontent=array();
		foreach($rels as $Fkey=>$df){
			$Ftabla=$df[0];
			$Fname=$df[1];
			$FoK=$df[2];
			if(array_key_exists($Ftabla,$fkf)){
				$cond="WHERE ";
				foreach($fkf[$Ftabla] as $fku=>$vku){
					if($fku=="INNER"){
						$cond.="$vku AND ";
					}else{
						$cond.=$fku."='$vku' AND ";
					}
				}
				$cond.="1";
			}else{
				$cond="WHERE 1";
			}
			$resultFk = $gf->dataSet("SELECT $FoK, $Fname FROM $Ftabla $cond ORDER BY $Fname",array());
			//echo "SELECT $FoK, $Fname FROM $Ftabla ORDER BY $Fname";
			$arkeycontent[$Fkey]=array();
			if($resultFk!=false && count($resultFk)>0){
				foreach($resultFk as $rowFk){
					$idFk=$rowFk[$FoK];
					$nameFk=$rowFk[$Fname];
					$arkeycontent[$Fkey][$idFk]=$nameFk;
				}
			}
		}
		
		$html_result.="<div class='box-body'>";
		
		$nameform="form".rand(0,500000);
		$html_result.="<form name='$nameform' class='form form-horizontal' role='form' id='$nameform' method='post' action='Admin/post_form.php?qry=in&ent=$tabla'>";
		$html_result.="";
		
		$filsetted=0;
				
		foreach($campos as $nombre_campo=>$data){
					
			$id_camps=$nombre_campo.$nameform;
			$nombre_alias=$data["alias"];
			$tipo=$data["type"];
			if(isset($data["mask"])){
				$mask=$data["mask"];
			}else{
				$mask=false;
			}
			$contenido="";
			if(isset($data["obl"])){
				if(!$data["obl"]){
					$required="";
					$msgreq="";
					$rq=false;
				}else{
					$required="required";
					$msgreq=" *";
					$rq=true;
				}
			}else{
				$required="";
				$msgreq="";
				$rq=false;
			}
			
			if(isset($data["pattern"])){
				if($data["pattern"]==""){
					$pattern="";
				}else{
					$pattern="pattern='{$data["pattern"]}'";
				}
			}else{
				$pattern="";
			}
			
			if(isset($data["size"])){
				$size="size='{$data["size"]}'";
			}else{
				$size="";
			}
			
			if($ob==false || ($ob && $rq)){
				$bory=false;
				if($tipo=="rel"){
					if($nombre_campo==$filterKey){
						if(!$rq || isset($arkeycontent[$nombre_campo][$filterValue])){
							
						}else{
							$bory=true;
							$html_result.="<div class='form-group'><label for='$id_camps' class='col-md-3 control-label no-padding-right'><small>$nombre_alias</small> $msgreq </label><div  class='col-md-9'>";
						}
					}else{
						$bory=true;
						$html_result.="<div class='form-group'><label for='$id_camps' class='col-md-3 control-label no-padding-right'><small>$nombre_alias</small> $msgreq </label><div  class='col-md-9'>";
					}
				}else{
					$bory=true;
					$html_result.="<div class='form-group'><label for='$id_camps' class='col-md-3 control-label no-padding-right'><small>$nombre_alias</small> $msgreq </label><div  class='col-md-9'>";
				}
				
				
				
				if($tipo=="rel"){
					
					
					if(!$bory){
						$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' id='$id_camps' value='$filterValue' />";
					}else{
						$html_result.="<div id='pl_div_$id_camps'><select class='form-control' name='$nombre_campo' id='$id_camps' tipo='former_$nameform' $required>";
						if(!$rq){
							$html_result.="<option value=''>Ninguno[a]</option>";
						}
						foreach($arkeycontent[$nombre_campo] as $fk=>$nm){
							$html_result.="<option value='$fk'>$nm</option>";
						}
						$html_result.="</select></div>";
						if(array_key_exists($nombre_campo,$plusk)){
							$tbd=$plusk[$nombre_campo]["tabla"];
							if(isset($plusk[$nombre_campo]["fkf"])){
								$fik=$plusk[$nombre_campo]["fkf"]["ky"];
								$fiv=$plusk[$nombre_campo]["fkf"]["kv"];
							}else{
								$fik="";
								$fiv="";
							}
							$html_result.="<span id='plusk_$nombre_campo' style='display:none;' class='btn btn-xs btn-success glyphicon glyphicon-plus-sign' onclick=\"getDialog('Admin/gest_data2.php?flag=nuevo&ent=$tbd&filterKey=$fik&filterVal=$fiv&rnd2=$dialogo&target=$id_camps&required=$required&nc=$nombre_campo')\"></span>
							<script>
							$(function(){
								setTimeout(function(){
									$( '#plusk_$nombre_campo' ).show();
									$( '#plusk_$nombre_campo' ).position({
									  my: 'right top',
									  at: 'right bottom',
									  of: '#$id_camps'
									});
									
								},2000);
							});
							</script>
							
							
							";
						}
					}
				}elseif(array_key_exists($nombre_campo,$flts)){
					$vl=$flts[$nombre_campo];
					$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='Texto' type='text' readonly='readonly' class='form-control' name='$nombre_campo' value='$vl' />";
				}elseif($tipo=="text"){
					if($mask){
						$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='Texto' type='password' class='form-control' name='$nombre_campo' $required $pattern $size />";
						
					}else{
						$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='Texto' type='text' class='form-control' name='$nombre_campo' $required $pattern $size />";
					}
				}elseif($tipo=="number" || $tipo=="position"){
					if(isset($data["min"])){
						$min=$data["min"];
						$max=$data["max"];
						$explain="<small>Valor de $min a $max</small>";
					}else{
						$min="";
						$max="";
						$explain="";
					}
					$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='N&uacute;meros' type='number' min='$min' max='$max' class='form-control' name='$nombre_campo' $required $pattern $size />$explain";
				}elseif($tipo=="float"){
					if(isset($data["min"])){
						$min=$data["min"];
						$max=$data["max"];
						$explain="<small>Valor de $min a $max</small>";
					}else{
						$min="";
						$max="";
						$explain="";
					}
					$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='N&uacute;meros' type='any' min='$min' max='$max' step='any' class='form-control' name='$nombre_campo' $required $pattern $size />$explain";
				}elseif($tipo=="email"){
					$html_result.="<input tipo='former_$nameform' placeholder='xxxx@xxxx.xx' type='email' name='$nombre_campo' class='form-control' id='$id_camps' $required $pattern $size />";
				}elseif($tipo=="password"){
					$html_result.="<input tipo='former_$nameform' type='password' class='form-control' name='$nombre_campo' id='$nombre_campo' $required $size />";						
				}elseif($tipo=="textarea"){
					$html_result.="<textarea tipo='former_$nameform' name='$nombre_campo' class='form-control' id='$id_camps'></textarea>";	
				}elseif($tipo=="wysiwyg"){
					$html_result.="<textarea tipo='former_$nameform' id='$id_camps' name='$nombre_campo' class='wysiwyg form-control' $required $size></textarea>";						
				}elseif($tipo=="color"){
					$html_result.="<input tipo='former_$nameform' type='color' placeholder='Color (#00FF00)' name='$nombre_campo' id='$id_camps' $size $required  class='form-control' />";	
				}elseif($tipo=="date"){
					if(array_key_exists($nombre_campo,$fkf)){
						if(isset($fkf[$nombre_campo]["min"])){
							$min=$fkf[$nombre_campo]["min"];
						}else{
							$min="1900-01-01";
						}
						if(isset($fkf[$nombre_campo]["max"])){
							$max=$fkf[$nombre_campo]["max"];
						}else{
							$max="2100-12-31";
						}
					}else{
						$min="1900-01-01";
						$max="2100-12-31";
					}
					
					$html_result.="
					
						<div class='input-group date' id='$nombre_campo'>
							<input type='text' class='form-control date-picker' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps' $required />
							<span class='input-group-addon'>
								<span class='glyphicon glyphicon-calendar'>
								</span>
							</span>
						</div>
						<script type='text/javascript'>
							$(function () {
								$('#$nombre_campo').datetimepicker({
									viewMode: 'days',
									format: 'YYYY-MM-DD',
									minDate: '$min',
									maxDate: '$max',
									locale:'es'
								});
							});
						</script>
					";	
				}elseif($tipo=="time"){
					$html_result.="
						<div class='input-group date' id='$nombre_campo'>
							<input type='text' class='form-control' tipo='former_$nameform'  name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required/>
							<span class='input-group-addon'>
								<span class='glyphicon glyphicon-calendar'>
								</span>
							</span>
						</div>
						<script type='text/javascript'>
							$(function () {
								$('#$nombre_campo').datetimepicker({
									viewMode: 'days',
									format: 'HH:mm A'
								});
							});
						</script>
					
					";
				}elseif($tipo=="now"){
					$theval=date("Y-m-d h:i:s");
					$html_result.=" <input type='hidden' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required value='$theval'/> <input type='text' readonly='readonly' disabled='disabled' value='$theval' />";
				}elseif($tipo=="gps"){
						$html_result.="
						
							<div class='input-group' id='$nombre_campo'>
								<span id='imgetmap' class='input-group-addon' onclick=\"addPick('$id_camps','imgetmap')\">
									<span class='glyphicon glyphicon-map-marker'>
									</span>
								</span>
								<input type='text' class='form-control' tipo='former_$nameform' name='$nombre_campo' value='' class='$nombre_campo' id='$id_camps'  $required />
								
							</div>
							<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=true'></script>
						";
				}elseif($tipo=="datetime"){
					if(array_key_exists($nombre_campo,$fkf)){
						if(isset($fkf[$nombre_campo]["min"])){
							$min=$fkf[$nombre_campo]["min"];
						}else{
							$min="1900-01-01 00:00:00";
						}
						if(isset($fkf[$nombre_campo]["max"])){
							$max=$fkf[$nombre_campo]["max"];
						}else{
							$max="2100-12-31 23:59:59";
						}
					}else{
						$min="1900-01-01 00:00:00";
						$max="2100-12-31 23:59:59";
					}
					$html_result.="
						<div class='input-group date' id='$nombre_campo'>
							<input type='text' class='form-control' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required/>
							<span class='input-group-addon'>
								<span class='glyphicon glyphicon-calendar'>
								</span>
							</span>
						</div>
						
									
						<script type='text/javascript'>
							$(function () {
								$('#$nombre_campo').datetimepicker({
									viewMode: 'days',
									format: 'YYYY-MM-DD HH:mm:[00] A',
									minDate:'$min',
									maxDate: '$max',
									stepping:30,
									collapse: false,
									sideBySide: true,
									showClose: true,
									locale:'es'
								});
							});
						</script>
					
					
					";
				
				}elseif($tipo=="image"){
					if(isset($data["folda"])){
						$fld=$data["folda"];
					}else{
						$fld="";
					}
					$html_result.="<div class='input-group'><input class='form-control' tipo='former_$nameform' placeholder='Click en el &iacute;cono' type='text' name='$nombre_campo' id='$nombre_campo' $required />
					<span class='input-group-addon ui-state-active' onclick=\"getImage('$nombre_campo','$fld')\"><span class='glyphicon glyphicon-folder-open'  $required></span></span></div>";	


				}elseif($tipo=="icon"){
					$html_result.="<div class='input-group'><input class='form-control' tipo='former_$nameform' placeholder='Click en el &iacute;cono' type='text' name='$nombre_campo' id='$nombre_campo' $required />
					<span class='input-group-addon ui-state-active' onclick=\"faIcon('$nombre_campo')\"><span class='glyphicon glyphicon-folder-open'  $required></span></span></div>";	


					
				}elseif($tipo=="file"){
					$html_result.="<div class='input-group'><input placeholder='Click en el &iacute;cono' class='form-control' tipo='former_$nameform' type='text' name='$nombre_campo' id='$nombre_campo' />
					<span class='input-group-addon ui-state-active'  onclick=\"getFile('$nombre_campo','$extfilter','$imgfolder')\"  $required><span class=' glyphicon glyphicon-folder-open' $ro></span></span></div>
					";					
				}elseif($tipo=="boolean"){
					$html_result.="<div class='input-group'><select class='form-control' tipo='former_$nameform' name='$nombre_campo' $required><option value='1'>Si</option><option value='0'>No</option></select></div>";					
				}elseif($tipo=="array"){
					if(array_key_exists($nombre_campo,$fkf)){
						$vll=$fkf[$nombre_campo];
						$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' id='$nombre_campo' $required value='$vll' /> ".$data["arraycont"][$vll];
					}else{
						$html_result.="<select class='form-control' tipo='former_$nameform' name='$nombre_campo' id='$nombre_campo' $required>";
						foreach($data["arraycont"] as $val=>$show){
							$html_result.="<option value='$val'>$show</option>";
						}
						$html_result.="</select>";
					}
				}elseif($tipo=="curuser"){
					$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' id='$nombre_campo' value='{$_SESSION["restuiduser"]}' />Usuario actual";
				}elseif($tipo=="auto"){
					$html_result.="<input type='text' disabled='disabled' readonly='readonly' value='AUTO' />";
				}
				
				if($bory){
					$html_result.="</div></div>";
				}
				
			}
		}
		$html_result.="<hr /><input class='btn btn-primary' type='submit' value='Nuevo'  id='bt_key_$nameform'  /> | <input class='btn btn-warning' type='button' name='boton' value='Cancelar' onclick=\"closeD('$dialogo')\" />
		<script type='text/javascript'>
		$(function () {
			goForm('$nameform','$dialogo','$callback');
		});
		</script>";
		$html_result.="</form></div></div>";
		return $html_result;
		
	}
	
	
	//TABLA PARA EDITAR ITEM CON RELACIONES
	function devuelveTablaEditItemDyRel($tabla,$Vkey,$filterKey="null",$filterValue="null",$dialogo="",$callback="null",$fkf=array(),$ob=false){
		$gf=new generalFunctions;
		global $relaciones;
		$PKey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		$campos=$relaciones[$tabla]["campos"];
		
		
		$html_result="<div class='box box-default'><div class='box-header'>EDITAR ELEMENTO</div>";
		if(sizeof($campos)==0){
			echo "Falta archivo de relaciones";
			exit;
		}else{
			$camps="";
			$tbheader="";
			$rels=array();
			foreach($campos as $campo=>$data){
				$camps.=$campo.",";
				$tipocampo=$data["type"];
				if($tipocampo=="rel"){
					$rels[$campo]=array($data["table"],$data["name"],$data["fk"]);
				}
			}
			$camps=substr($camps,0,strlen($camps)-1);
			$consultaSQL="SELECT $camps FROM $tabla WHERE $PKey='$Vkey' ORDER BY 1";
			
		}
		
		
		
		$arkeycontent=array();
		foreach($rels as $Fkey=>$df){
			$Ftabla=$df[0];
			$Fname=$df[1];
			$FoK=$df[2];
			if(array_key_exists($Ftabla,$fkf)){
				$cond="WHERE ";
				foreach($fkf[$Ftabla] as $fku=>$vku){
					if($fku=="INNER"){
						$cond.="$vku AND ";
					}else{
						$cond.=$fku."='$vku' AND ";
					}
				}
				$cond.="1";
			}else{
				$cond="WHERE 1";
			}
			$resultFk = $gf->dataSet("SELECT $FoK, $Fname FROM $Ftabla $cond ORDER BY $Fname",array());
		
			$arkeycontent[$Fkey]=array();
			if($resultFk!=false && count($resultFk)>0){
				foreach($resultFk as $rowFk){
					$idFk=$rowFk[$FoK];
					$nameFk=$rowFk[$Fname];
					$arkeycontent[$Fkey][$idFk]=$nameFk;
					
				}
			}
		}
		
		
		$html_result.="<div class='box-body'>";
		
		$nameform="form".rand(0,500000);
		$html_result.="<form name='$nameform' class='form form-horizontal' id='$nameform' method='post' action='Admin/post_form.php?qry=up&ent=$tabla&ky=$PKey&kv=$Vkey'>";
		$html_result.="";

		$resultG = $gf->dataSet($consultaSQL,array());
		if($resultG!=false && count($resultG)>0){
			$rowG=$resultG[0];
			foreach($campos as $nombre_campo=>$data){
				$theval=$rowG[$nombre_campo];
				$id_camps=$nombre_campo.$nameform;
				$nombre_alias=$data["alias"];
				$tipo=$data["type"];
				$contenido="";
				if(isset($data["mask"])){
					$mask=$data["mask"];
				}else{
					$mask=false;
				}
				
				if(isset($data["obl"])){
					if(!$data["obl"]){
						$required="";
						$msgreq="";
						$rq=false;
					}else{
						$required="required";
						$msgreq=" *";
						$rq=true;
					}
				}else{
					$required="";
					$msgreq="";
					$rq=false;
				}
				
				if(isset($data["pattern"])){
					if($data["pattern"]==""){
						$pattern="";
					}else{
						$pattern="pattern='{$data["pattern"]}'";
					}
				}else{
					$pattern="";
				}
				if(isset($data["size"])){
					$size="size='{$data["size"]}'";
				}else{
					$size="";
				}
				if(!$ob || ($ob && $rq)){
					$html_result.="<div class='form-group'><label for='$id_camps' class='col-md-3 control-label no-padding-right'>$nombre_alias $msgreq</label><div class='col-md-9'>";
					if($tipo=="rel"){
						
						
						if(isset($arkeycontent[$nombre_campo][$theval]) && $nombre_campo==$filterKey){
							$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' id='$id_camps'  value='$theval' /> {$arkeycontent[$nombre_campo][$theval]}";
						}else{
							
							$html_result.="<select class='form-control' name='$nombre_campo' id='$id_camps' tipo='former_$nameform' $required>";
							if(!$rq){
								$html_result.="<option value=''>Ninguno[a]</option>";
							}
							foreach($arkeycontent[$nombre_campo] as $fk=>$nm){
								if($fk!=$theval){
									$html_result.="<option value='$fk'>$nm</option>";
								}else{
									$html_result.="<option value='$fk' selected='selected'>$nm</option>";
								}
							}
							$html_result.="</select>";
						}
					}elseif($tipo=="text"){
						if($mask){
							$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='Texto' type='password' class='form-control' name='$nombre_campo' $required $pattern $size value='$theval' />";
						}else{
							$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='Texto' type='text' class='form-control' name='$nombre_campo' $required $pattern $size value='$theval' />";
						}
					}elseif($tipo=="number" || $tipo=="position"){
						if(isset($data["min"])){
							$min=$data["min"];
							$max=$data["max"];
							$explain="<small>Valor de $min a $max</small>";
						}else{
							$min="";
							$max="";
							$explain="";
						}
						$html_result.="<input id='$id_camps' tipo='former_$nameform' placeholder='N&uacute;meros' type='number' min='$min' max='$max' class='form-control' name='$nombre_campo' $required $pattern $size value='$theval' />$explain";
					}elseif($tipo=="float"){
						if(isset($data["min"])){
							$min=$data["min"];
							$max=$data["max"];
							$explain="<small>Valor de $min a $max</small>";
						}else{
							$min="";
							$max="";
							$explain="";
						}
						$html_result.="<input id='$id_camps' step='any' tipo='former_$nameform' placeholder='N&uacute;meros' type='number' min='$min' max='$max' class='form-control' name='$nombre_campo' $required $pattern $size value='$theval' />$explain";
					}elseif($tipo=="email"){
						$html_result.="<input tipo='former_$nameform' placeholder='xxxx@xxxx.xxx' type='email' name='$nombre_campo' class='form-control' id='$id_camps' $required $pattern $size value='$theval' />";
					}elseif($tipo=="password"){
						$html_result.="<input tipo='former_$nameform' type='password' class='form-control' name='$nombre_campo' id='$nombre_campo' $required $size value='$theval' />";						
					}elseif($tipo=="textarea"){
						$html_result.="<textarea tipo='former_$nameform' name='$nombre_campo' class='form-control' id='$id_camps'>$theval</textarea>";	
					}elseif($tipo=="wysiwyg"){
						$html_result.="<textarea tipo='former_$nameform' id='$id_camps' name='$nombre_campo' class='wysiwyg form-control' $required $size>$theval</textarea>";						
					}elseif($tipo=="color"){
						$html_result.="<input tipo='former_$nameform' type='color' placeholder='Color' name='$nombre_campo' id='$id_camps' $size $required  class='form-control' value='$theval' />";	
					}elseif($tipo=="date"){
						if(array_key_exists($nombre_campo,$fkf)){
							if(isset($fkf[$nombre_campo]["min"])){
								$min=$fkf[$nombre_campo]["min"];
							}else{
								$min="1900-01-01";
							}
							if(isset($fkf[$nombre_campo]["max"])){
								$max=$fkf[$nombre_campo]["max"];
							}else{
								$max="2100-12-31";
							}
						}else{
							$min="1900-01-01";
							$max="2100-12-31";
						}
						
						$html_result.="
						
							<div class='input-group date' id='$nombre_campo'>
								<input type='text' value='$theval' class='form-control date-picker' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps' $required value='$theval' />
								<span class='input-group-addon'>
									<span class='glyphicon glyphicon-calendar'>
									</span>
								</span>
							</div>
							<script type='text/javascript'>
								$(function () {
									$('#$nombre_campo').datetimepicker({
										viewMode: 'days',
										format: 'YYYY-MM-DD',
										minDate: '$min',
										maxDate: '$max',
										locale:'es'
									});
								});
							</script>
						";	
					}elseif($tipo=="time"){
						$html_result.="
						
						
							<div class='input-group date' id='$nombre_campo'>
								<input type='text' value='$theval' class='form-control' tipo='former_$nameform'  name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required value='$theval' />
								<span class='input-group-addon'>
									<span class='glyphicon glyphicon-calendar'>
									</span>
								</span>
							</div>
							<script type='text/javascript'>
								$(function () {
									$('#$nombre_campo').datetimepicker({
										viewMode: 'days',
										format: 'HH:mm A'
									});
								});
							</script>
						
						";
					}elseif($tipo=="gps"){
						if($theval==""){
							$theval="4.814575430505841,-75.69820404052734";
						}
						$html_result.="
						
							<div class='input-group' id='$nombre_campo'>
							<span id='imgetmap' class='input-group-addon link' onclick=\"addPick('$id_camps','imgetmap')\">
									<span class='glyphicon glyphicon-map-marker'>
									</span>
								</span>
								<input type='text' value='$theval' class='form-control' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required value='$theval' />
								
							</div>
							<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=true'></script>
						";
					}elseif($tipo=="now"){
						$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required value='$theval'/>$theval";
					}elseif($tipo=="datetime"){
						if(array_key_exists($nombre_campo,$fkf)){
							if(isset($fkf[$nombre_campo]["min"])){
								$min=$fkf[$nombre_campo]["min"];
							}else{
								$min="1900-01-01 00:00:00";
							}
							if(isset($fkf[$nombre_campo]["max"])){
								$max=$fkf[$nombre_campo]["max"];
							}else{
								$max="2100-12-31 23:59:59";
							}
						}else{
							$min="1900-01-01 00:00:00";
							$max="2100-12-31 23:59:59";
						}
						
						$html_result.="
							<div class='input-group date' id='$nombre_campo'>
								<input type='text' value='$theval' class='form-control' tipo='former_$nameform' name='$nombre_campo' class='$nombre_campo' id='$id_camps'  $required value='$theval' />
								<span class='input-group-addon'>
									<span class='glyphicon glyphicon-calendar'>
									</span>
								</span>
							</div>
							<script type='text/javascript'>
								$(function () {
									$('#$nombre_campo').datetimepicker({
										viewMode: 'days',
										format: 'YYYY-MM-DD HH:mm:[00] A',
										minDate:'$min',
										maxDate: '$max',
										stepping:30,
										collapse: false,
										sideBySide: true,
										showClose: true,
										locale:'es'
									});
								});
							</script>
						
						
						";
					
					}elseif($tipo=="image"){
						$html_result.="<div class='input-group'><input class='form-control' tipo='former_$nameform' placeholder='Click en el &iacute;cono' type='text' name='$nombre_campo' id='$nombre_campo' $required value='$theval' />
						<span class='input-group-addon ui-state-active' onclick=\"getImage('$nombre_campo')\"><span class='glyphicon glyphicon-folder-open'  $required></span></span></div>";	
					}elseif($tipo=="icon"){
						$html_result.="<div class='input-group'><input class='form-control' tipo='former_$nameform' placeholder='Click en el &iacute;cono' type='text' name='$nombre_campo' id='$nombre_campo' $required value='$theval' />
						<span class='input-group-addon ui-state-active' onclick=\"faIcon('$nombre_campo')\"><span class='glyphicon glyphicon-folder-open'  $required></span></span></div>";			
					}elseif($tipo=="file"){
						$html_result.="<div class='input-group'><input placeholder='Click en el &iacute;cono' class='form-control' tipo='former_$nameform' type='text' name='$nombre_campo' id='$nombre_campo' value='$theval' />
						<span class='input-group-addon ui-state-active'  onclick=\"getFile('$nombre_campo','')\"  $required><span class=' glyphicon glyphicon-folder-open' $ro></span></span></div>
						";					
					}elseif($tipo=="boolean"){
						if($theval==0){
							$html_result.="<div class='input-group'><select class='form-control' tipo='former_$nameform' name='$nombre_campo' $required><option value='1'>Si</option><option value='0' selected='selected'>No</option></select></div>";		
						}else{
							$html_result.="<div class='input-group'><select class='form-control' tipo='former_$nameform' name='$nombre_campo' $required><option value='1' selected='selected'>Si</option><option value='0'>No</option></select></div>";	
						}
									
					}elseif($tipo=="array"){

						if(array_key_exists($nombre_campo,$fkf)){
							$vll=$fkf[$nombre_campo];
							$html_result.="<input type='hidden' tipo='former_$nameform' name='$nombre_campo' id='$nombre_campo' $required value='$vll' /> ".$data["arraycont"][$vll];
						}else{
							$html_result.="<select class='form-control' tipo='former_$nameform' name='$nombre_campo' id='$nombre_campo' $required>";
							foreach($data["arraycont"] as $val=>$show){
								if($val!=$theval){
									$html_result.="<option value='$val'>$show</option>";
								}else{
									$html_result.="<option value='$val' selected='selected'>$show</option>";
								}
							}
							$html_result.="</select>";
						}
					
					
					
					}elseif($tipo=="auto"){
						$html_result.=" AUTO";
					}
					
					$html_result.="</div></div>";
				}
			}
		}					
		$html_result.="<hr /><input class='btn btn-primary' type='submit' value='Guardar' />";
		$html_result.="<script type='text/javascript'>
		$(function () {
			goForm('$nameform','$dialogo','$callback');
		});
		</script></form></div></div>";
		return $html_result;
			
		
	}
	function armaItems($tabla,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$run="",$runon="",$hnd="",$fkf=array(),$son=array(),$filterPlus="1"){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		if($orderby==1){
			$orderby=$relaciones[$tabla]["pname"];
		}
		if(isset($relaciones[$tabla]["alias"])){
			$aliastabla=$relaciones[$tabla]["alias"];
		}else{
			$aliastabla="GESTI&Oacute;N DE DATOS ".strtoupper($tabla);
		}
		
		
		
		$campos=$relaciones[$tabla]["campos"];
		$ording=false;
		$cppos="";
		foreach($campos as $cmp=>$art){
			if($art["type"]=="position"){
				$ording=true;
				$cppos=$cmp;
			}
		}
		if($add==1){
			$newopts="flag=nuevo&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&hnd=$hnd";
			$btnadd="<button data-toggle='tooltip' title='Agregar Elemento' class='btn btn-xs btn-success pull-right' onclick=\"getDialog('$sender?$newopts','500','Agregar\ Registro')\"><span class='glyphicon glyphicon-plus-sign'></span></button>";
		}else{
			$btnadd="";
		}
		$rnd_table="tabla_$tabla";
		$html_result="<div class='box box-default'><div class='box-header'><small>$aliastabla</small> $btnadd</div>";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		foreach($fkf as $camf=>$valf){
			if($camf!=""){
				$filter.=" AND $camf='$valf'";
			}
		}
		$fks=array();
		if(count($son)>0){
			$tabson=$son["tabla"];
			$fkson=$son["fk"];
			$condfk=isset($son["cond"]) ? $son["cond"] : "1";
			if($condfk==""){
				$condfk="1";
			}
			$rsson=$gf->dataSet("SELECT $fkson, COUNT($fkson) AS CUENT FROM $tabson WHERE $condfk GROUP BY $fkson ORDER BY 1",array());
			foreach($rsson as $rwson){
				$di=$rwson[$fkson];
				$cu=$rwson["CUENT"];
				$fks[$di]=$cu;
			}
		}
		
		
		$consultaSQL="SELECT $Pkey, $Pname FROM $tabla WHERE $filter AND $filterPlus ORDER BY $orderby";

		//echo $consultaSQL;
		$resultG = $gf->dataSet($consultaSQL,array());

		if($ording){
			$clasorder="sorta";
			$orderargs="tbl='$tabla' ky='$Pkey' cmp='$cppos'";
		}else{
			$clasorder="";
			$orderargs="";			
		}
		
		$html_result.="<div class='box-body'><input type='text' class='form-control' id='filt_$rnd_table' onkeyup=\"filtrarValores('filt_$rnd_table','it_$rnd_table')\" placeholder=\"Buscar\" /><br /><div style='height:350px;overflow:auto;'><ul class='list-group $clasorder' id='$rnd_table' $orderargs>";
		if($resultG!=false && count($resultG)>0){
			foreach($resultG as $rowG){
				$llave=$rowG[$Pkey];
				$nllave=$rowG[$Pname];
				if($run!=""){
					$rnon="cargaHTMLvars('$runon','$run&key=$llave')";
				}else{
					$rnon="";
				}
				$html_result.="<li ky='$llave' class='list-group-item item-default clearfix link-cnv it_$tabla it_$rnd_table' id='ls_$tabla$llave' onclick=\"$rnon;sibErase('ls_$tabla$llave');setSelect('it_$tabla','ls_$tabla$llave');\"><span class='lbl inline'>$nllave</span><div class='pull-right action-buttons'>";
				
				if(($modify!=0 || $delete!=0)){
					if($modify==1){
						$html_result.="<a class='orange stoppa' onclick=\"getDialog('$sender?flag=editar&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&amp;Vkey=$llave&hnd=$hnd','600','Editar\ Elemento')\"><i class='ace-icon fa fa-pencil bigger-130'></i></a> ";
						if($ording){
							//$html_result.="<span class='btn btn-default btn-xs glyphicon glyphicon-move pull-right moverc'></span>";
						}
						
					}
					if($delete==1){
						if(!isset($fks[$llave])){
							$html_result.="<a class='red stoppa' id='img_$Pkey$llave' onclick=\"goErase('$tabla','$Pkey','$llave','ls_$tabla$llave','1')\"><i class='ace-icon fa fa-trash-o bigger-130'></i></a>";
						}
					}
				}
				$html_result.="</div></li>";
			}
		}
		
		$html_result.="</ul></div>";
		
		$html_result.="</div></div>";
		return $html_result;
	}


	function armaFilter($tabla,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$run="",$runon="",$hnd="",$fkf=array(),$son=array()){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		if($orderby==1){
			$orderby=$relaciones[$tabla]["pname"];
		}
		if(isset($relaciones[$tabla]["alias"])){
			$aliastabla=$relaciones[$tabla]["alias"];
		}else{
			$aliastabla="GESTI&Oacute;N DE DATOS ".strtoupper($tabla);
		}
		
		
		
		$campos=$relaciones[$tabla]["campos"];
		$ording=false;
		$cppos="";
		foreach($campos as $cmp=>$art){
			if($art["type"]=="position"){
				$ording=true;
				$cppos=$cmp;
			}
		}

		$rnd_table="tabla_$tabla";
		$html_result="";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		foreach($fkf as $camf=>$valf){
			if($camf!=""){
				$filter.=" AND $camf='$valf'";
			}
		}
		$fks=array();
		if(count($son)>0){
			$tabson=$son["tabla"];
			$fkson=$son["fk"];
			$condfk=isset($son["cond"]) ? $son["cond"] : "1";
			if($condfk==""){
				$condfk="1";
			}
			$rsson=$gf->dataSet("SELECT $fkson, COUNT($fkson) AS CUENT FROM $tabson WHERE $condfk GROUP BY $fkson ORDER BY 1",array());
			foreach($rsson as $rwson){
				$di=$rwson[$fkson];
				$cu=$rwson["CUENT"];
				$fks[$di]=$cu;
			}
		}
		
		
		$consultaSQL="SELECT $Pkey, $Pname FROM $tabla WHERE $filter ORDER BY $orderby";

		//echo $consultaSQL;
		$resultG = $gf->dataSet($consultaSQL,array());

		if($ording){
			$clasorder="sorta";
			$orderargs="tbl='$tabla' ky='$Pkey' cmp='$cppos'";
		}else{
			$clasorder="";
			$orderargs="";			
		}
		if($run!=""){
			$rnon="cargaHTMLvars('$runon','$run&key='+this.value)";
		}else{
			$rnon="";
		}
		$html_result.="<select class='form-control input-sm' id='sel_$rnd_table' onchange=\"$rnon\"><option value=''>Selecciona...</option>";
		if($resultG!=false && count($resultG)>0){
			foreach($resultG as $rowG){
				$llave=$rowG[$Pkey];
				$nllave=$rowG[$Pname];
				
				$html_result.="<option value='$llave'>$nllave</option>";
			}
		}
		$html_result.="</select>";
		return $html_result;
	}
	


	function armaItemsFlex($tabla,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$run="",$runon="",$hnd="",$fkf=array(),$son=array(),$icon="fa-cube"){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tabla]["pkey"];
		$Pname=$relaciones[$tabla]["pname"];
		if($orderby==1){
			$orderby=$relaciones[$tabla]["pname"];
		}
		if(isset($relaciones[$tabla]["alias"])){
			$aliastabla=$relaciones[$tabla]["alias"];
		}else{
			$aliastabla="GESTI&Oacute;N DE DATOS ".strtoupper($tabla);
		}
		
		
		
		$campos=$relaciones[$tabla]["campos"];
		$ording=false;
		$cppos="";
		foreach($campos as $cmp=>$art){
			if($art["type"]=="position"){
				$ording=true;
				$cppos=$cmp;
			}
		}
		if($add==1){
			$newopts="flag=nuevo&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&hnd=$hnd";
			$btnadd="<button data-toggle='tooltip' title='Agregar Elemento' class='btn btn-xs btn-success pull-right' onclick=\"getDialog('$sender?$newopts','500','Agregar\ Registro')\"><span class='glyphicon glyphicon-plus-sign'></span></button>";
		}	
		$rnd_table="tabla_$tabla";
		$html_result="<div class='panel panel-default'><div class='panel-heading ui-corner-all'><small>$aliastabla</small> $btnadd</div>";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		foreach($fkf as $camf=>$valf){
			if($camf!=""){
				$filter.="AND $camf='$valf'";
			}
		}
		$fks=array();
		if(count($son)>0){
			$tabson=$son["tabla"];
			$fkson=$son["fk"];
			$condfk=$son["cond"];
			if($condfk==""){
				$condfk="1";
			}
			$rsson=$gf->dataSet("SELECT $fkson, COUNT($fkson) AS CUENT FROM $tabson WHERE $condfk GROUP BY $fkson ORDER BY 1",array());
			foreach($rsson as $rwson){
				$di=$rwson[$fkson];
				$cu=$rwson["CUENT"];
				$fks[$di]=$cu;
			}
		}
		
		
		$consultaSQL="SELECT $Pkey, $Pname FROM $tabla WHERE $filter ORDER BY $orderby";

		//echo $consultaSQL;
		$resultG = $gf->dataSet($consultaSQL,array());

		if($ording){
			$clasorder="sorta";
			$orderargs="tbl='$tabla' ky='$Pkey' cmp='$cppos'";
		}else{
			$clasorder="";
			$orderargs="";			
		}
		
		$html_result.="<div class='panel-body'><input type='text' class='form-control' id='filt_$rnd_table' onkeyup=\"filtrarValores('filt_$rnd_table','it_$rnd_table')\" placeholder=\"Buscar\" /><br /><div class='row flexbox-centro' id='$rnd_table' $orderargs>";
		if($resultG!=false && count($resultG)>0){
			foreach($resultG as $rowG){
				$llave=$rowG[$Pkey];
				$nllave=$rowG[$Pname];
				if($run!=""){
					$rnon="cargaHTMLvars('$runon','$run&key=$llave')";
				}else{
					$rnon="";
				}
				$html_result.="<div ky='$llave' class='btn-lst flexbox-centro link-cnv it_$tabla it_$rnd_table' id='ls_$tabla$llave' onclick=\"$rnon;sibErase('ls_$tabla$llave');setSelect('it_$tabla','ls_$tabla$llave');\">
				<i class='ace-icon fa $icon bigger-230'></i>
				<span class='lbl inline'>$nllave</span>
				<hr />
				<div class='pull-right action-buttons'>";
				
				if(($modify!=0 || $delete!=0)){
					if($modify==1){
						$html_result.="<a class='orange stoppa' onclick=\"getDialog('$sender?flag=editar&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&amp;Vkey=$llave&hnd=$hnd','600','Editar\ Elemento')\"><i class='ace-icon fa fa-pencil bigger-130'></i></a> ";
						if($ording){
							//$html_result.="<span class='btn btn-default btn-xs glyphicon glyphicon-move pull-right moverc'></span>";
						}
						
					}
					if($delete==1){
						if(!isset($fks[$llave])){
							$html_result.="<a class='red stoppa' id='img_$Pkey$llave' onclick=\"goErase('$tabla','$Pkey','$llave','ls_$tabla$llave','1')\"><i class='ace-icon fa fa-trash-o bigger-130'></i></a>";
						}
					}
				}
				$html_result.="</div></div>";
			}
		}
		
		$html_result.="</div>";
		
		$html_result.="</div></div>";
		return $html_result;
	}

	


	function armaRel($tablaItems,$tablaRel,$Vkey,$Fkey,$orderby="1",$add=1,$modify=1,$delete=1,$filterKey="null",$filterValue="null",$sender="",$run="",$runon=""){
		$gf=new generalFunctions;
		global $relaciones;
		$Pkey=$relaciones[$tablaItems]["pkey"];
		$Pname=$relaciones[$tablaItems]["pname"];
		$campos=$relaciones[$tablaItems]["campos"];
		$rnd_table="tabla_$tabla";
		$html_result="<div class='panel panel-default' style='font-size:10px;'><div class='panel-heading ui-corner-all'>SELECCI&Oacute; DE ELEMENTOS</div>";
		$hasfilter=0;
		
		if($filterKey!="null" && $filterKey!=""){
			$filter="$filterKey='$filterValue'";
			$hasfilter=1;
		}else{
			$filter="1";
		}
		
		$consultaSQL="SELECT E.$Pkey, E.$Pname, R.$Fkey FROM $tablaItems AS E LEFT JOIN $tablaRel AS R WHERE E. ORDER BY $orderby";

		//echo $consultaSQL;
		$resultG = $gf->dataSet($consultaSQL,array());

		$html_result.="<div class='panel-body ui-corner-all'><ul class='list-group' id='$rnd_table'>";
		if($resultG!=false && count($resultG)>0){
			foreach($resultG as $rowG){
				$z+=1;
				$llave=$rowG[$Pkey];
				$nllave=$rowG[$Pname];
				if($run!=""){
					$rnon="cargaHTMLvars('$runon','$run&key=$llave')";
				}else{
					$rnon="";
				}
				$html_result.="<li class='list-group-item link-cnv it_$tabla' id='ls_$tabla$llave' onclick=\"$rnon;sibErase('ls_$tabla$llave');setSelect('it_$tabla','ls_$tabla$llave');\"><table style='width:100%;'><tr><td>$nllave</td><td style='width:20%;'>";
				
				if(($modify!=0 || $delete!=0) && $sesi==0){
					if($modify==1){
						$html_result.="<span class='btn btn-xs btn-warning glyphicon glyphicon-pencil pull-right stoppa' onclick=\"getDialog('$sender?flag=editar&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue&amp;Vkey=$llave','600','Editar\ Elemento')\"></span>";
						
					}
					if($delete==1){
						$html_result.="<span class='btn btn-xs btn-danger glyphicon glyphicon-remove pull-right stoppa' id='img_$Pkey$llave' onclick=\"goErase('$tabla','$Pkey','$llave','ls_$tabla$llave','1')\"></span>";
					}
				}
				$html_result.="</td></tr></table></li>";
			}
		}
		if($add==1){
			$colspand=$columnas;
			$newopts="flag=nuevo&amp;ent=$tabla&amp;filterKey=$filterKey&amp;filterVal=$filterValue";
			$html_result.="<li class='list-group-item list-group-item-success link-cnv' onclick=\"getDialog('$sender?$newopts','500','Agregar\ Registro')\"><span class='glyphicon glyphicon-plus-sign'></span>Agregar Elemento</li>";
		}
		$html_result.="</ul>";
		
		$html_result.="</div></div>";
		return $html_result;
	}
}
?>