
<script type="text/javascript">
	
	function schnellsuche(){
		save = document.GUI.go.value;
		document.GUI.go.value = 'SchnellSuche_Suchen';
		if(document.GUI.legendtouched == undefined || document.GUI.legendtouched.value == 0){		// nur wenn die Legende nicht angefasst wurde, per ajax-Request (wenn Anzeige in extra Fenster eingestellt) laden, ansonsten kompletter submit
			overlay_submit(document.GUI, true);
			document.GUI.go.value = save;
		}
		else{
			document.GUI.submit();
		}
	}

	function keydown(event){
		var key = event.keyCode;
		if (key == 13) {
			value = event.target.value;
			if(document.getElementById('operator_'+event.target.id).value == 'LIKE'){
				event.target.value = '%'+value+'%';
			}
			schnellsuche();
			event.target.value = value;
			preventDefault(event);		// form-submit unterdruecken
		}		
	}
	
	function load_search_attributes(layer_id){
		if(layer_id != undefined){
			ahah("index.php", "go=get_quicksearch_attributes&layer_id="+layer_id, new Array(document.getElementById('quick_search_div')), new Array('sethtml'));
		}
	}
	
	function update_require_attribute_(attributes, layer_id, attributenamesarray){
		// attributes ist eine Liste von zu aktualisierenden Attributen und attributenamesarray ein Array aller Attribute im Formular
		var attributenames = '';
		var attributevalues = '';
		for(i = 0; i < attributenamesarray.length; i++){
			if(document.getElementById('value_'+attributenamesarray[i]) != undefined){
				attributenames += attributenamesarray[i] + '|';
				attributevalues += document.getElementById('value_'+attributenamesarray[i]).value + '|';
			}
		}
		attribute = attributes.split(',');
		for(i = 0; i < attribute.length; i++){
			ahah("index.php", "go=get_select_list&layer_id="+layer_id+"&attribute="+attribute[i]+"&attributenames="+attributenames+"&attributevalues="+attributevalues+"&type=select-one", new Array(document.getElementById('value_'+attribute[i])), new Array('sethtml'));
		}
	}
	
</script>

<?
	global $quicksearch_layer_ids;
	
	if(count($quicksearch_layer_ids) > 0){
		$quicksearch_layerdaten = $this->Stelle->getqueryableVectorLayers(NULL, NULL, NULL, $quicksearch_layer_ids);
		if(count($quicksearch_layerdaten['ID']) > 0){
?>
		<table>
			<tr>
				<td>
					<select size="1"  name="quicksearch_layer_id" onchange="load_search_attributes(this.value);">
						<option value="">-- Schnellsuche --</option>
						<?
						for($i = 0; $i < count($quicksearch_layerdaten['ID']); $i++){         
							echo '<option';
							if($quicksearch_layerdaten['ID'][$i] == $this->formvars['quicksearch_layer_id']){
								echo ' selected';
							}
							echo ' value="'.$quicksearch_layerdaten['ID'][$i].'">'.$quicksearch_layerdaten['Bezeichnung'][$i].'</option>';
						}
						$i = 0;		// $i wieder zur�cksetzen
					?>
					</select>
				</td>
				<td>	
					<div id="quick_search_div"></div>
				</td>
			</tr>
		</table>
<? 	} 
		if($this->formvars['quicksearch_layer_id'] != ''){ ?>
		<script type="text/javascript">
			load_search_attributes(<? echo $this->formvars['quicksearch_layer_id']; ?>);
		</script>
<? 	}
	} ?>