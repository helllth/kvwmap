<? 
include(LAYOUTPATH.'languages/generic_search_'.$this->user->rolle->language.'.php');
include(SNIPPETS.'/sachdatenanzeige_functions.php');
?>

<script src="funktionen/selectformfunctions.js" language="JavaScript"  type="text/javascript"></script>
<script src="funktionen/tooltip.js" language="JavaScript"  type="text/javascript"></script>
<script type="text/javascript">
<!--


Text1=['<? echo $strHelp; ?>:','<? echo $strAndOrHint; ?>'];

<!-- wird fuer das Absenden bei Enter benoetigt -->
document.onkeydown = function(ev){
	var key;
	ev = ev || event;
	key = ev.keyCode;
	if (key == 13) {
		document.GUI.suchen.click();
	}
}

function changeInputType(object, oType) {
	if(object != undefined){
		object.type = oType;
	}
}

function operatorchange(layer_id, attributname, searchmask_number){
	if(searchmask_number > 0){						// es ist nicht die erste Suchmaske, sondern eine weitere hinzugefügte
		prefix = searchmask_number+'_';
	}
	else prefix = '';
	if(document.getElementById(prefix+"operator_"+attributname).value == "IS NULL" || document.getElementById(prefix+"operator_"+attributname).value == "IS NOT NULL"){
		changeInputType(document.getElementById(prefix+"value_"+attributname), "hidden");
	}
	else{
		changeInputType(document.getElementById(prefix+"value_"+attributname), "text");
	}
	if(document.getElementById(prefix+"operator_"+attributname).value == "between"){
		changeInputType(document.getElementById(prefix+"value2_"+attributname), "text");
		document.getElementById(prefix+"value_"+attributname).style.width = '144px';
	}
	else{
		if(document.getElementById(prefix+"value2_"+attributname) != undefined){
			changeInputType(document.getElementById(prefix+"value2_"+attributname), "hidden");
			document.getElementById(prefix+"value2_"+attributname).value = "";
			document.getElementById(prefix+"value_"+attributname).style.width = '293px';
		}
	}
	if(document.getElementById(prefix+"_avf_"+attributname) != undefined){
		if(document.getElementById(prefix+"operator_"+attributname).value == "LIKE" || document.getElementById(prefix+"operator_"+attributname).value == "NOT LIKE"){
			document.getElementById(prefix+"_avf_"+attributname).style.display = 'none';
			document.getElementById(prefix+"_text_"+attributname).style.display = 'inline';
			document.getElementById(prefix+"text_value_"+attributname).value = '';
			document.getElementById(layer_id+"_"+attributname+"_"+prefix).disabled = true;
			document.getElementById(prefix+"text_value_"+attributname).disabled = false;
		}
		else{
			document.getElementById(prefix+"_avf_"+attributname).style.display = 'inline';
			document.getElementById(prefix+"_text_"+attributname).style.display = 'none';			
			document.getElementById(attributname+"_"+prefix).value = '';
			document.getElementById(attributname+"_"+prefix).disabled = false;
			document.getElementById(prefix+"text_value_"+attributname).disabled = true;
		}
	}
}

function suche(){
	var nogo = '';
	<?
	for($i = 0; $i < count($this->attributes['type']); $i++) {
		if($this->attributes['mandatory'][$i] == '' or $this->attributes['mandatory'][$i] > -1){
			if($this->attributes['type'][$i] != 'geometry' AND $this->attributes['form_element_type'][$i] != 'SubFormFK' AND $this->attributes['form_element_type'][$i] != 'dynamicLink') {
				if($this->attributes['mandatory'][$i] == 1){
					if($this->attributes['alias'][$i] == ''){
						$this->attributes['alias'][$i] = $this->attributes['name'][$i];
					}		?>
					if(document.GUI.value_<? echo $this->attributes['name'][$i]; ?>.value == ''){
						if('<? echo $this->attributes['form_element_type'][$i]; ?>' != 'Autovervollständigungsfeld'
						|| (document.GUI.value_<? echo $this->attributes['name'][$i]; ?>[0].value == '' && document.GUI.value_<? echo $this->attributes['name'][$i]; ?>[0].disabled == false)
						|| (document.GUI.value_<? echo $this->attributes['name'][$i]; ?>[1].value == '' && document.GUI.value_<? echo $this->attributes['name'][$i]; ?>[1].disabled == false)
						){
							nogo = 'Das Feld <? echo $this->attributes['alias'][$i]; ?> ist ein Such-Pflichtfeld und muss ausgefüllt werden.';
						}
					}
		<?	} ?>
				test = document.GUI.value_<? echo $this->attributes['name'][$i]; ?>.value + '';
				if(test.search(/%/) > -1 && document.GUI.operator_<? echo $this->attributes['name'][$i]; ?>.value == 'IN'){
					nogo = 'Der Platzhalter % darf nur bei der Suche mit ähnlich oder nicht ähnlich verwendet werden.';
				}
		<? 	if(strpos($this->attributes['type'][$i], 'time') !== false OR $this->attributes['type'][$i] == 'date'){ ?>
					test = document.GUI.value_<? echo $this->attributes['name'][$i]; ?>.value + '';
					if(test != ''){
						if(!checkDate(test)){
							nogo = 'Das Datum hat das falsche Format';
						}
					}
		<?	} 
			}
		}
	}?>
	if(document.GUI.map_flag.value == 1){
		if(document.GUI.newpathwkt.value == ''){
			if(document.GUI.newpath.value == ''){
				nogo = 'Geben Sie ein Polygon an.';
			}
			else{
				document.GUI.newpathwkt.value = buildwktpolygonfromsvgpath(document.GUI.newpath.value);
			}
		}
	}
	if(nogo != ''){
		alert(nogo);
	}
	else{
		document.getElementById('loader').style.display = '';
		setTimeout('document.getElementById(\'loaderimg\').src=\'graphics/ajax-loader.gif\'', 50);
		document.GUI.go_plus.value = 'Suchen';
		document.GUI.submit();
	}
}


function buildwktpolygonfromsvgpath(svgpath){
	var koords;
	var wkt = '';
	if(svgpath != '' && svgpath != undefined){
		wkt = "POLYGON((";
		parts = svgpath.split("M");
		for(j = 1; j < parts.length; j++){
			if(j > 1){
				wkt = wkt + "),("
			}
			koords = ""+parts[j];
			coord = koords.split(" ");
			wkt = wkt+coord[1]+" "+coord[2];
			for(var i = 3; i < coord.length-1; i++){
				if(coord[i] != ""){
					wkt = wkt+","+coord[i]+" "+coord[i+1];
				}
				i++;
			}
		}
		wkt = wkt+"))";
	}
	return wkt;
}

function update_require_attribute(attributes, layer_id, attributenamesarray, searchmask_number){
	// attributes ist eine Liste von zu aktualisierenden Attributen und attributenamesarray ein Array aller Attribute im Formular
	if(searchmask_number > 0){						// es ist nicht die erste Suchmaske, sondern eine weitere hinzugefügte
		prefix = searchmask_number+'_';
	}
	else prefix = '';
	var attributenames = '';
	var attributevalues = '';
	for(i = 0; i < attributenamesarray.length; i++){
		if(document.getElementById(prefix+'value_'+attributenamesarray[i]) != undefined){
			attributenames += attributenamesarray[i] + '|';
			attributevalues += document.getElementById(prefix+'value_'+attributenamesarray[i]).value + '|';
		}
	}
	attribute = attributes.split(',');
	for(i = 0; i < attribute.length; i++){
		ahah("index.php", "go=get_select_list&layer_id="+layer_id+"&attribute="+attribute[i]+"&attributenames="+attributenames+"&attributevalues="+attributevalues+"&type=select-one", new Array(document.getElementById(prefix+'value_'+attribute[i])), new Array('sethtml'));
	}
}


function showsearches(){
	if(document.getElementById('searches2').style.display == 'none'){
		document.getElementById('searches1').style.borderTop="1px solid #C3C7C3";
		document.getElementById('searches1').style.borderLeft="1px solid #C3C7C3";
		document.getElementById('searches1').style.borderRight="1px solid #C3C7C3";
		document.getElementById('searches2').style.display = '';
	}
	else{
		document.getElementById('searches1').style.border="none";
		document.getElementById('searches2').style.display = 'none';
	}
}

function showmap(){
	if(document.GUI.map_flag.value == 0){
		document.GUI.map_flag.value = 1;
	}
	else{
		document.GUI.map_flag.value = '';
	}
	document.GUI.submit();
}

function save_search(){
	if(document.GUI.search_name.value != ''){
		document.GUI.go_plus.value = 'Suchabfrage_speichern';
		document.GUI.submit();
	}
	else{
		alert('Bitte geben Sie einen Namen für die Suchabfrage an.');
	}
}

function delete_search(){
	if(document.GUI.searches.value != ''){
		document.GUI.go_plus.value = 'Suchabfrage_löschen';
		document.GUI.submit();
	}
	else{
		alert('Es wurde keine Suchabfrage ausgewählt.');
	}
}

function add_searchmask(layer_id){
	document.GUI.searchmask_count.value = parseInt(document.GUI.searchmask_count.value) + 1;
	newdiv = document.createElement('div');
	document.getElementById('searchmasks').appendChild(newdiv);
	ahah("index.php", "go=Layer-Suche_Suchmaske_generieren&selected_layer_id="+layer_id+"&searchmask_number="+document.GUI.searchmask_count.value, new Array(newdiv), new Array('sethtml'));
}
  
//-->
</script>
<br><h2><? if($this->titel != '')echo $this->titel;else echo $strLayerSearch; ?></h2><?php
	if (!$this->user->rolle->visually_impaired) {
		include(SNIPPETS.'/generic_search_layer_selector.php');
	}

?><table border="0" cellpadding="5" cellspacing="2"><?php
	if(!in_array($this->selected_search[0]['name'], array('', '<last_search>'))){echo '<script type="text/javascript">showsearches();</script>';} ?>
  <tr> 
    <td id="searchmasks">

<? if(count($this->attributes) > 0){
		for($m = 0; $m <= $this->formvars['searchmask_count']; $m++){ 
			$searchmask_number = $m; 		?>
			<div>
			<? include(SNIPPETS.'generic_search_mask.php'); ?>
			</div>
<? 	}
	} ?>
		</td>
  </tr>
	<tr> 
    <td colspan="5">
<? if(count($this->attributes) > 0){ ?>
			<table width="100%" align="center" border="0" cellspacing="0" cellpadding="3">
<?php if ($this->user->rolle->visually_impaired) { ?>
					<tr>
						<td align="center"><br>
							<input type="button" name="suchen" onclick="suche();" value="<? echo $this->strSearch; ?>">
						</td>
					</tr>
<?php } ?>
			<? if($this->layerset[0]['connectiontype'] == MS_POSTGIS){ ?>
					<tr>
						<td><a href="javascript:add_searchmask(<? echo $this->formvars['selected_layer_id']; ?>);"><? echo $strAndOr; ?></a></td>
					</tr>
			<? } ?>
					<tr>
						<td><br><? echo $strLimit; ?>&nbsp;<input size="2" onkeyup="checknumbers(this, 'int2', '', '');" type="text" name="anzahl" value="<? echo $this->formvars['anzahl']; ?>"></td>
					</tr>
					<tr>
						<td><br><em><? echo $strLikeSearchHint; ?></em></td>
					</tr>
					<tr>
						<td><br><em><? echo $strDateHint; ?></em></td>
					</tr>
<?php if (!$this->user->rolle->visually_impaired) { ?>
					<tr>
						<td align="center"><br>
							<input type="button" name="suchen" onclick="suche();" value="<? echo $this->strSearch; ?>">
						</td>
					</tr>
<?php } ?>
					<tr>
						<td height="30" valign="bottom" align="center" id="loader" style="display:none"><img id="loaderimg" src="graphics/ajax-loader.gif"></td>
					</tr>
				</table><?
      }
      ?>
		</td>
  </tr>
</table>
<?php
	if ($this->user->rolle->visually_impaired) {
		include(SNIPPETS.'/generic_search_layer_selector.php');
	}
 ?>
<input type="hidden" name="go_plus" value="">
<input type="hidden" name="go" value="Layer-Suche">
<input type="hidden" name="titel" value="<? echo value_of($this->formvars, 'titel'); ?>">
<input type="hidden" name="map_flag" value="<? echo value_of($this->formvars, 'map_flag'); ?>">
<input type="hidden" name="area" value="">
<INPUT TYPE="HIDDEN" NAME="columnname" VALUE="<? echo $this->formvars['columnname']; ?>">
<INPUT TYPE="HIDDEN" NAME="fromwhere" VALUE="<? echo $this->formvars['fromwhere']; ?>">
<INPUT TYPE="HIDDEN" NAME="orderby" VALUE="<? echo $this->formvars['orderby']; ?>">
<input type="hidden" name="always_draw" value="<? echo $always_draw; ?>">
<input type="hidden" name="searchmask_count" value="<? echo $this->formvars['searchmask_count']; ?>">

