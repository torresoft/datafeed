<?php
class generalComponents{
	function panel($class="default",$header="",$footer="",$header_buttons=array(),$contents=""){
		$html="<div class='box box-$class'>";
		if($header!="" || count($header_buttons)>0){
			$html.="<div class='box-header'>".$header;
			foreach($header_buttons as $btn){
				$btn_class=$btn["class"];
				$btn_action=$btn["action"];
				$btn_icon=$btn["icon"];
				$btn_caption=$btn["caption"];
				$btn_showcaption=$btn["show_caption"];
				$html.=$this->button($btn_class,$btn_action,$btn_icon,$btn_caption,$btn_showcaption);
			}
			$html.="</div>";
		}
		$html.="<div class='box-body'>".$contents."</div>";
		if($footer){
			$html.="<div class='box-footer'>".$footer."</div>";
		}
		$html.="</div>";
		return $html;
	}
	
	function form_input($type="text",$name="",$id="",$onchange="",$placeholder="Texto",$addclasses="unival_ds"){
		$html="<input type='$type' name='$name' id='$id' onchange=\"$onchange\" placeholder='$placeholder' class='form-control $addclasses' />";
		return $html;
	}
	
	function itemlist($rs,$itemId="ID",$itemName="NOMBRE",$action="",$action_buttons=array(),$filter=false,$sort=false,$tb="",$ky="",$cmp="",$erase_control=""){
		if($filter){
			$html=$this->form_input("text","buscar","search_item_$itemId","filtrarValores('search_item_$itemId','filtrableitems_$itemId')");
		}else{
			$html="";
		}
		$rnd=rand(0,1000);
		if($sort==true){
			$claseadd="sorta";
			$paramsadd="tbl='$tb' ky='$ky' cmp='$cmp'";
		}else{
			$claseadd="";
			$paramsadd="";
		}
		$html.="<ul class='list-group $claseadd' $paramsadd id='ksorting_$rnd'>";
		
		foreach($rs as $li){
			$li_id=$li[$itemId];
			$li_name=$li[$itemName];
			if($erase_control!=""){
				$ctr=$li[$erase_control];
			}else{
				$ctr=0;
			}
			$li_action=str_replace("[KEY]",$li_id,$action);
			if($li_action!=""){
				$classadd="link-cnv";
			}else{
				$classadd="";
			}
			if($action!=""){
				$selector="setSelect('filtrableitems_$itemId','li_$itemId$li_id');";
			}else{
				$selector="";
			}
			$html.="<li ky='$li_id' id='li_$itemId$li_id' class='list-group-item $classadd filtrableitems_$itemId' onclick=\"$selector$li_action\">$li_name";
			foreach($action_buttons as $btn){
				$btn_class=$btn["class"];
				$btn_action=$btn["action"];
				$btn_icon=$btn["icon"];
				$btn_caption=$btn["caption"];
				$btn_showcaption=$btn["show_caption"];
				$btn_action=str_replace("[KEY]",$li_id,$btn_action);
				$btn_condition=isset($btn["condition"]) ? $btn["condition"] : "";
				if($btn_condition!=$erase_control){
					$html.=$this->button($btn_class." stoppa",$btn_action,$btn_icon,$btn_caption,$btn_showcaption);
				}else{
					if($ctr==0){
						$html.=$this->button($btn_class." stoppa",$btn_action,$btn_icon,$btn_caption,$btn_showcaption);
					}
				}
				
			}
			$html.="</li>";
		}
		$html.="</ul>";
		return $html;
	}
	
	function button($btn_class="btn-default",$btn_action="",$btn_icon="",$btn_caption="",$showcap=true){
		
		if($showcap){
			$btn_caption=$btn_caption;
			$title=$btn_caption;
		}else{
			$title=$btn_caption;
			$btn_caption="";
		}
		$html="<button title='$title' alt='$title' onclick=\"$btn_action\" class='btn btn-minier btn-xs $btn_class pull-right'><i class='$btn_icon'></i> $btn_caption</button>";
		return $html;
	}
	
	function row($cols=array()){
		$html="<div class='row'>";
		foreach($cols as $col){
			$id=$col["id"];
			$extend=$col["extend"];
			$content=$col["content"];
			$html.="<div class='col col-md-$extend' id='$id'>";
			$html.=$content;
			$html.="</div>";
		}
		return $html;
	}
	
}
?>