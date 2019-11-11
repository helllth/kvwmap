<?php
	global $supportedLanguages;
  include(LAYOUTPATH.'languages/layer_formular_'.$this->user->rolle->language.'.php');
 ?><script language="JavaScript" src="funktionen/selectformfunctions.js" type="text/javascript"></script>
<script src="funktionen/tooltip.js" language="JavaScript"  type="text/javascript"></script>
<script type="text/javascript">

Text[0]=["Hilfe:","Wendet eine Prozessierungsanweisung für den Layer an. Die unterstützten Anweisungen hängen vom Layertyp und dem verwendeten Treiber ab. Es gibt Anweisungen für Attribute, Connection Pooling, OGR Styles und Raster. siehe Beschreibung zum Layerattribut PROCESSING unter: http://www.mapserver.org/mapfile/layer.html. Mehrere Prozessinganweisungen werden hier eingegeben getrennt durch Semikolon. z.B. CHART_SIZE=60;CHART_TYPE=pie für die Darstellung eines Tortendiagramms des Typs MS_LAYER_CHART"]
Text[1]=["Hilfe:","Die Haupttabelle ist diejenige der im Query-SQL-Statement abgefragten Tabellen, die die oid liefern soll.<br><br>Die Haupttabelle muss oids besitzen, diese müssen allerdings nicht im SQL angegeben werden.<br><br>Ist das Feld Haupttabelle leer, wird der Name der Haupttabelle automatisch eingetragen. Bei einer Layerdefinition über mehrere Tabellen hinweg kann es sein, dass kvwmap die falsche Tabelle als Haupttabelle auswählt. In diesem Fall kann hier händisch die gewünschte Tabelle eingetragen werden. Achtung: Wenn die Tabellennamen im Query-SQL geändert werden, muss auch der Eintrag im Feld Haupttabelle angepasst werden!"]
Text[2]=["Hilfe:","Das Query-SQL ist das SQL-Statement, welches für die Sachdatenabfrage verwendet wird. Es kann eine beliebige Abfrage auf Tabellen oder Sichten sein, eine WHERE-Bedingung ist aber erforderlich. Der Schemaname wird hier nicht angegeben, sondern im Feld 'Schema'"]
Text[3]=["Hilfe:","Das Data-Feld wird vom Mapserver für die Kartendarstellung verwendet (siehe Mapserver-Doku). Etwaige Schemanamen müssen hier angegeben werden."]
Text[4]=["Hilfe:","Bei Punktlayern kann durch Angabe dieses Wertes die Clusterbildung aktiviert werden. Der Wert ist der Radius in Pixeln, in dem Punktobjekte zu einem Cluster zusammengefasst werden. <br>Damit die Cluster dargestellt werden können, muss es eine Klasse mit der Expression \"('[Cluster:FeatureCount]' != '1')\" geben. Cluster:FeatureCount kann auch als Labelitem verwendet werden, um die Anzahl der Punkte pro Cluster anzuzeigen."]

  function testConnection() {
    if (document.getElementById('connectiontype').value == 7) {
      getCapabilitiesURL=document.getElementById('connection').value+'&service=WMS&request=GetCapabilities';    
      getMapURL = document.getElementById('connection').value+'&service=WMS&request=GetMap&srs=epsg:<?php echo $this->layerdata['epsg_code']; ?>&BBOX=<?php echo $this->user->rolle->oGeorefExt->minx; ?>,<?php echo $this->user->rolle->oGeorefExt->miny; ?>,<?php echo $this->user->rolle->oGeorefExt->maxx; ?>,<?php echo $this->user->rolle->oGeorefExt->maxy; ?>&width=<?php echo $this->user->rolle->nImageWidth; ?>&height=<?php echo $this->user->rolle->nImageHeight; ?>';
      document.getElementById('test_img').src = getMapURL;
      document.getElementById('test_img').style.display='block';
      document.getElementById('test_link').href=getCapabilitiesURL;
      document.getElementById('test_link').innerHTML=getCapabilitiesURL;
    }
    else {
      getCapabilitiesURL=document.getElementById('connection').value+'&service=WFS&request=GetCapabilities';
      document.getElementById('test_link').href=getCapabilitiesURL;
      document.getElementById('test_link').innerHTML=getCapabilitiesURL;
    }
  }
  
</script>						

<table border="0" cellpadding="5" cellspacing="0" bgcolor="<?php echo $bgcolor; ?>">
  <tr align="center"> 
    <td><h2><?php echo $strTitle; ?></h2></td>
  </tr>
  <tr>
    <td align="center"><?php   
if ($this->Meldung=='Daten der Stelle erfolgreich eingetragen!' OR $this->Meldung=='') {
  $bgcolor=BG_FORM;
}
else {
  $this->Fehlermeldung=$this->Meldung;
  include('Fehlermeldung.php');
  $bgcolor=BG_FORMFAIL;
}
 ?>
		<table border="0" cellspacing="0" cellpadding="3" style="border:1px solid <?php echo BG_DEFAULT ?>">
			<tr align="center">
		    	<th bgcolor="<?php echo BG_DEFAULT ?>" width="670" colspan="3" class="fetter" style="border-bottom:1px solid #C3C7C3"><?php echo $strCommonData; ?></th>
		    </tr><?php if ($this->formvars['selected_layer_id']>0) {?>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLayerID; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		    		<input name="id" type="text" value="<?php echo $this->formvars['selected_layer_id']; ?>" size="25" maxlength="11">
		    	</td>
		  	</tr><?php } ?>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strName; ?>*</th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="Name" type="text" value="<?php echo $this->layerdata['Name']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
	<?		foreach($supportedLanguages as $language){
					if($language != 'german'){	?>
						<tr>
							<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strName.' '.$language; ?></th>
							<td colspan=2 style="border-bottom:1px solid #C3C7C3">
									<input name="Name_<? echo $language; ?>" type="text" value="<?php echo $this->layerdata['Name_'.$language]; ?>" size="25" maxlength="100">
							</td>
						</tr>	
	<?			}
				}			?>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strAlias; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="alias" type="text" value="<?php echo $this->layerdata['alias']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strDataType; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="Datentyp">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<option <? if($this->layerdata['Datentyp'] == '0'){echo 'selected ';} ?>value="0">MS_LAYER_POINT</option>
		      			<option <? if($this->layerdata['Datentyp'] == 1){echo 'selected ';} ?>value="1">MS_LAYER_LINE</option>
		      			<option <? if($this->layerdata['Datentyp'] == 2){echo 'selected ';} ?>value="2">MS_LAYER_POLYGON</option>
		      			<option <? if($this->layerdata['Datentyp'] == 3){echo 'selected ';} ?>value="3">MS_LAYER_RASTER</option>
		      			<option <? if($this->layerdata['Datentyp'] == 4){echo 'selected ';} ?>value="4">MS_LAYER_ANNOTATION</option>
		      			<option <? if($this->layerdata['Datentyp'] == 5){echo 'selected ';} ?>value="5">MS_LAYER_QUERY</option>
		      			<option <? if($this->layerdata['Datentyp'] == 6){echo 'selected ';} ?>value="6">MS_LAYER_CIRCLE</option>
		      			<option <? if($this->layerdata['Datentyp'] == 7){echo 'selected ';} ?>value="7">MS_LAYER_TILEINDEX</option>
		      			<option <? if($this->layerdata['Datentyp'] == 8){echo 'selected ';} ?>value="8">MS_LAYER_CHART</option>
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strGroup; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="Gruppe">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<? 
		      			for($i = 0; $i < count($this->Groups['ID']); $i++){
		      				if($this->layerdata['Gruppe'] == $this->Groups['ID'][$i]){
		      					echo '<option selected';
		      				}
		      				else{
		      					echo '<option';
		      				}
		      				echo ' value="'.$this->Groups['ID'][$i].'">'.$this->Groups['ID'][$i].' - '.$this->Groups['Bezeichnung'][$i].'</option>';
		      			}
		      			?>	      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		  		<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strPath; ?></th>
		  		<td colspan=2 valign="top" style="border-bottom:1px solid #C3C7C3">
		  			<textarea name="pfad" cols="33" rows="4"><? echo $this->layerdata['pfad'] ?></textarea>&nbsp;&nbsp;<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text[2], Style[0], document.getElementById('TipLayer3'))" onmouseout="htm()">
						<div id="TipLayer3" style="visibility:hidden;position:absolute;z-index:1000;"></div>
		  		</td>
		  	</tr>
		  	<tr>
		  		<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strData; ?></th>
		  		<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		  			<textarea name="Data" cols="33" rows="4"><? echo $this->layerdata['Data'] ?></textarea>&nbsp;&nbsp;<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text[3], Style[0], document.getElementById('TipLayer4'))" onmouseout="htm()">
						<div id="TipLayer4" style="visibility:hidden;position:absolute;z-index:1000;"></div>
		  		</td>
		  	</tr>
				<tr>
					<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strMaintable; ?></th>
					<td colspan=2 style="border-bottom:1px solid #C3C7C3">
						<input name="maintable" type="text" value="<?php echo $this->layerdata['maintable']; ?>" size="25" maxlength="100">&nbsp;&nbsp;<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text[1], Style[0], document.getElementById('TipLayer1'))" onmouseout="htm()">
						<div id="TipLayer1" style="visibility:hidden;position:absolute;z-index:1000;"></div>
					</td>
				</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strSchema; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="schema" type="text" value="<?php echo $this->layerdata['schema']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strDocument_path; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="document_path" type="text" value="<?php echo $this->layerdata['document_path']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strTileIndex; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="tileindex" type="text" value="<?php echo $this->layerdata['tileindex']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strTileItem; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="tileitem" type="text" value="<?php echo $this->layerdata['tileitem']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLabelAngleItem; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="labelangleitem" type="text" value="<?php echo $this->layerdata['labelangleitem']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLabelItem; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="labelitem" type="text" value="<?php echo $this->layerdata['labelitem']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLabelMaxScale; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="labelmaxscale" type="text" value="<?php echo $this->layerdata['labelmaxscale']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLabelMinScale; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="labelminscale" type="text" value="<?php echo $this->layerdata['labelminscale']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strLabelRequires; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="labelrequires" type="text" value="<?php echo $this->layerdata['labelrequires']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		  		<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strConnection; ?></th>
		  		<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		  			<textarea id="connection" name="connection" cols="33" rows="2"><? echo $this->layerdata['connection'] ?></textarea>
		  			<input type="button"  onclick="testConnection();" value="Test"
		  			<? if(in_array($this->layerdata['connectiontype'], array(7,9))){ ?>
						style="display: inline;"
						<? }else{ ?>style="display: none;"<? } ?>><br>
						<img border="1" id ="test_img" src="" style="display: none;"><br>
						<a id="test_link" href="" target="_blank"></a>
						
		  		</td>
		  	</tr>
		  	<tr>
		  		<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strPrintConnection; ?></th>
		  		<td style="border-bottom:1px solid #C3C7C3">
		  			<textarea name="printconnection" cols="33" rows="2"><? echo $this->layerdata['printconnection'] ?></textarea>
		  		</td>
		  	</tr>
    		<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strConnectionType; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select id="connectiontype" name="connectiontype">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<option <? if($this->layerdata['connectiontype'] == '0'){echo 'selected ';} ?>value="0">MS_INLINE</option>
		      			<option <? if($this->layerdata['connectiontype'] == 1){echo 'selected ';} ?>value="1">MS_SHAPEFILE</option>
		      			<option <? if($this->layerdata['connectiontype'] == 2){echo 'selected ';} ?>value="2">MS_TILED_SHAPEFILE</option>
		      			<option <? if($this->layerdata['connectiontype'] == 3){echo 'selected ';} ?>value="3">MS_SDE</option>
		      			<option <? if($this->layerdata['connectiontype'] == 4){echo 'selected ';} ?>value="4">MS_OGR</option>
		      			<option <? if($this->layerdata['connectiontype'] == 5){echo 'selected ';} ?>value="5">MS_TILED_OGR</option>
		      			<option <? if($this->layerdata['connectiontype'] == 6){echo 'selected ';} ?>value="6">MS_POSTGIS</option>
		      			<option <? if($this->layerdata['connectiontype'] == 7){echo 'selected ';} ?>value="7">MS_WMS</option>
		      			<option <? if($this->layerdata['connectiontype'] == 8){echo 'selected ' ;} ?>value="8">MS_ORACLESPATIAL</option>
		      			<option <? if($this->layerdata['connectiontype'] == 9){echo 'selected ';} ?>value="9">MS_WFS</option>
		      			<option <? if($this->layerdata['connectiontype'] == 10){echo 'selected ';} ?>value="10">MS_GRATICULE</option>
		      			<option <? if($this->layerdata['connectiontype'] == 11){echo 'selected ';} ?>value="11">MS_MYGIS</option>		      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strClassItem; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="classitem" type="text" value="<?php echo $this->layerdata['classitem']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strFilterItem; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="filteritem" type="text" value="<?php echo $this->layerdata['filteritem']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strClusterMaxdistance; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="cluster_maxdistance" type="text" value="<?php echo $this->layerdata['cluster_maxdistance']; ?>" size="25" maxlength="11">&nbsp;<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text[4], Style[0], document.getElementById('TipLayer5'))" onmouseout="htm()">
						<div id="TipLayer5" style="visibility:hidden;position:absolute;z-index:1000;"></div>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strTolerance; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="tolerance" type="text" value="<?php echo $this->layerdata['tolerance']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strToleranceUnits; ?>*</th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="toleranceunits">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<option <? if($this->layerdata['toleranceunits'] == 'pixels'){echo 'selected';} ?> value="pixels">pixels</option>
		      			<option <? if($this->layerdata['toleranceunits'] == 'meters'){echo 'selected';} ?> value="meters">meters</option>		      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strEpsgCode; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="epsg_code">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<? 
		      			foreach($this->epsg_codes as $epsg_code){
									echo '<option ';
		      				if($this->layerdata['epsg_code'] == $epsg_code['srid'])echo 'selected ';
		      				echo ' value="'.$epsg_code['srid'].'">'.$epsg_code['srid'].': '.$epsg_code['srtext'].'</option>';
		      			}
		      			?>	      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strSelectionType; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="selectiontype" type="text" value="<?php echo $this->layerdata['selectiontype']; ?>" size="25" maxlength="20">
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strQueryMap; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="querymap">
		      			<option <? if($this->layerdata['querymap'] == '0'){echo 'selected ';} ?>value="0"><?php echo $this->strNo; ?></option>
		      			<option <? if($this->layerdata['querymap'] == 1){echo 'selected ';} ?>value="1"><?php echo $this->strYes; ?></option>		      			
		      		</select>
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strProcessing; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		    	  <input name="processing" type="text" value="<?php echo $this->layerdata['processing']; ?>" size="25" maxlength="255">
						<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text[0], Style[0], document.getElementById('TipLayer2'))" onmouseout="htm()">
						<div id="TipLayer2" style="visibility:hidden;position:absolute;z-index:1000;"></div>
		    	</td>
		  	</tr>
		  	<tr>
		  		<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strDescribtion; ?></th>
		  		<td style="border-bottom:1px solid #C3C7C3">
		  			<textarea name="kurzbeschreibung" cols="33" rows="2"><? echo $this->layerdata['kurzbeschreibung'] ?></textarea>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strDataOwner; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="datenherr" type="text" value="<?php echo $this->layerdata['datenherr']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strMetaLink; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="metalink" type="text" value="<?php echo $this->layerdata['metalink']; ?>" size="25" maxlength="255">
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strStatus; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="status" type="text" value="<?php echo $this->layerdata['status']; ?>" size="25" maxlength="255">
		  		</td>
		  	</tr>
		 	</table>
		  <br>
		  	
		  <table border="0" cellspacing="0" cellpadding="3" style="border:1px solid <?php echo BG_DEFAULT ?>">
		  	<tr align="center">
		  		<th class="fetter" bgcolor="<?php echo BG_DEFAULT ?>" width="670" style="border-bottom:1px solid #C3C7C3" colspan="3"><?php echo $strDefaultValues; ?></th>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strTemplate; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="template" type="text" value="<?php echo $this->layerdata['template']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strQueryable; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="queryable">
		      			<option <? if($this->layerdata['queryable'] == '0'){echo 'selected ';} ?>value="0"><?php echo $this->strNo; ?></option>
		      			<option <? if($this->layerdata['queryable'] == 1){echo 'selected ';} ?>value="1"><?php echo $this->strYes; ?></option>		      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strtransparency; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="transparency" type="text" value="<?php echo $this->layerdata['transparency']; ?>" size="25" maxlength="3">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strdrawingorder; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="drawingorder" type="text" value="<?php echo $this->layerdata['drawingorder']; ?>" size="25" maxlength="15">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strminscale; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="minscale" type="text" value="<?php echo $this->layerdata['minscale']; ?>" size="25" maxlength="15">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strmaxscale; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="maxscale" type="text" value="<?php echo $this->layerdata['maxscale']; ?>" size="25" maxlength="15">
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strsymbolscale; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="symbolscale" type="text" value="<?php echo $this->layerdata['symbolscale']; ?>" size="25" maxlength="15">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $stroffsite; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="offsite" type="text" value="<?php echo $this->layerdata['offsite']; ?>" size="25" maxlength="11">
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strPostlabelcache; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="postlabelcache">
		      			<option <? if($this->layerdata['postlabelcache'] == '0'){echo 'selected ';} ?>value="0"><?php echo $this->strNo; ?></option>
		      			<option <? if($this->layerdata['postlabelcache'] == 1){echo 'selected ';} ?>value="1"><?php echo $this->strYes; ?></option>		      			
		      		</select>
		  		</td>
		  	</tr>
				<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strrequires; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
							<select name="requires">
								<option value="">--- Auswahl ---</option>
								<?
									for($i = 0; $i < count($this->grouplayers['ID']); $i++){
										echo '<option value="'.$this->grouplayers['ID'][$i].'" ';
										if($this->layerdata['requires'] == $this->grouplayers['ID'][$i])echo 'selected="true"';
										echo ' >'.$this->grouplayers['Bezeichnung'][$i].'</option>';
									}
								?>
							</select>
		  		</td>
		  	</tr>
		  </table>
		  <br>
    	<a name="stellenzuweisung"></a>
      <table border="0" cellspacing="0" cellpadding="3" style="border:1px solid <?php echo BG_DEFAULT ?>">
		  	<tr align="center">
		  		<th class="fetter" bgcolor="<?php echo BG_DEFAULT ?>" width="670" style="border-bottom:1px solid #C3C7C3" colspan="3"><?php echo $strTasks; ?></th>
		  	</tr>
        <tr valign="top"> 
          <td align="right">Zugeordnete<br>
            <select name="selectedstellen" size="4" multiple style="width:300px">
            <? 
            for($i=0; $i < count($this->formvars['selstellen']["Bezeichnung"]); $i++){
              	echo '<option value="'.$this->formvars['selstellen']["ID"][$i].'" title="'.$this->formvars['selstellen']["Bezeichnung"][$i].'">'.$this->formvars['selstellen']["Bezeichnung"][$i].'</option>';
               }
            ?>
            </select>
          </td>
          <td align="center" valign="middle" width="1"> 
            <input type="button" name="addPlaces" value="&lt;&lt;" onClick=addOptions(document.GUI.allstellen,document.GUI.selectedstellen,document.GUI.selstellen,'value')>
            <input type="button" name="substractPlaces" value="&gt;&gt;" onClick=substractOptions(document.GUI.selectedstellen,document.GUI.selstellen,'value')>
          </td>
          <td>verfügbare<br>
            <select name="allstellen" size="4" multiple style="width:300px">
            <? for($i=0; $i < count($this->stellen["Bezeichnung"]); $i++){
              	echo '<option value="'.$this->stellen["ID"][$i].'" title="'.$this->stellen["Bezeichnung"][$i].'">'.$this->stellen["Bezeichnung"][$i].'</option>';
               }
            ?>
            </select>
          </td>
        </tr>
      </table>
      <br>
      
      <table cellspacing="0" cellpadding="5" border="0">
			<tr>
	  		<td align="right">
	  			<input type="button" name="dummy" onclick="location.href='index.php?go=Layerattribut-Rechteverwaltung&selected_layer_id=<? echo $this->formvars['selected_layer_id']; ?>'" value="<?php echo $strPrivileges; ?>">
	  		</td>
	  	</tr>
			</table>
      		  
		  <br>
		  <table border="0" cellspacing="0" cellpadding="3" style="border:1px solid <?php echo BG_DEFAULT ?>">
		  	<tr align="center">
		  		<th class="fetter" bgcolor="<?php echo BG_DEFAULT ?>" width="670" style="border-bottom:1px solid #C3C7C3" colspan="3"><?php echo $strOWSParameter; ?></th>
		  	</tr>	
		  	<tr>
		    	<th class="fetter" width="200" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strOwsSrs; ?></th>
		    	<td width="370" colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="ows_srs" type="text" value="<?php echo $this->layerdata['ows_srs']; ?>" size="25" maxlength="255">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSName; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="wms_name" type="text" value="<?php echo $this->layerdata['wms_name']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSServerVersion; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="wms_server_version">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<option <? if($this->layerdata['wms_server_version'] == '1.0.0'){echo 'selected';} ?> value="1.0.0">1.0.0</option>
		      			<option <? if($this->layerdata['wms_server_version'] == '1.1.0'){echo 'selected';} ?> value="1.1.0">1.1.0</option>
		      			<option <? if($this->layerdata['wms_server_version'] == '1.1.1'){echo 'selected';} ?> value="1.1.1">1.1.1</option>     			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSFormat; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<select name="wms_format">
		      			<option value=""><?php echo $this->strPleaseSelect; ?></option>
		      			<option <? if($this->layerdata['wms_format'] == 'image/png'){echo 'selected';} ?> value="image/png">image/png</option>
		      			<option <? if($this->layerdata['wms_format'] == 'image/jpg'){echo 'selected';} ?> value="image/jpg">image/jpg</option>
		      			<option <? if($this->layerdata['wms_format'] == 'image/gif'){echo 'selected';} ?> value="image/gif">image/gif</option>		      			
		      		</select>
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSConnectionTimeout; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="wms_connectiontimeout" type="text" value="<?php echo $this->layerdata['wms_connectiontimeout']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSAuthUsername; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="wms_auth_username" type="text" value="<?php echo $this->layerdata['wms_auth_username']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWMSAuthPassword; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="wms_auth_password" type="text" value="<?php echo $this->layerdata['wms_auth_password']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		  	<tr>
		    	<th class="fetter" align="right" style="border-bottom:1px solid #C3C7C3"><?php echo $strWFS_geom; ?></th>
		    	<td colspan=2 style="border-bottom:1px solid #C3C7C3">
		      		<input name="wfs_geom" type="text" value="<?php echo $this->layerdata['wfs_geom']; ?>" size="25" maxlength="100">
		  		</td>
		  	</tr>
		</table>
		<table cellspacing="0" cellpadding="5" border="0">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
	  		<td align="right">
	  			<input type="button" name="dummy" onclick="submitWithValue('GUI','go_plus','erweiterte Einstellungen')" value="<?php echo $strOtherExtentions; ?>">
	  		</td>
	  	</tr>
		</table>
		
		<br>
		<? if($this->formvars['selected_layer_id']){ ?>
		<table border="0" cellspacing="0" cellpadding="3" style="border:1px solid <?php echo BG_DEFAULT ?>">
			<tr>
				<th class="fetter" bgcolor="<?php echo BG_DEFAULT ?>" style="border-bottom:1px solid #C3C7C3" colspan="9"><a name="Klassen"></a><?php echo $strClasses; ?></th>
			</tr>
			<tr>
			  <td style="border-bottom:1px solid #C3C7C3">&nbsp;<?php echo $strID; ?></td>
				<td style="border-bottom:1px solid #C3C7C3">&nbsp;<?php echo $strClass; ?></td>
	<?		foreach($supportedLanguages as $language){
					if($language != 'german'){	?>
						<td style="border-bottom:1px solid #C3C7C3">&nbsp;<?php echo $strClass.' '.$language; ?></td>
	<?			}
				}		?>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><?php echo $strExpression; ?></td>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><?php echo $strText; ?></td>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><?php echo $strSignOrder; ?></td>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><?php echo $strDelete; ?></td>
	<!--			<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3">ändern</td>  -->
			</tr>
			<?
			for($i = 0; $i < count($this->classes); $i++){
				echo '
			<tr>
				<input type="hidden" name="ID['.$this->classes[$i]['Class_ID'].']" value="'.$this->classes[$i]['Class_ID'].'">
				<td style="border-bottom:1px solid #C3C7C3">'.$this->classes[$i]['Class_ID'].'</td>	
				<td style="border-bottom:1px solid #C3C7C3"><input size="12" type="text" name="name['.$this->classes[$i]['Class_ID'].']" value="'.$this->classes[$i]['Name'].'"</td>';
				foreach($supportedLanguages as $language){
					if($language != 'german'){	
						echo '<td style="border-bottom:1px solid #C3C7C3"><input size="12" type="text" name="name_'.$language.'['.$this->classes[$i]['Class_ID'].']" value="'.$this->classes[$i]['Name_'.$language].'"</td>';
					}
				}
				echo '<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><textarea name="expression['.$this->classes[$i]['Class_ID'].']" cols="28" rows="3">'.$this->classes[$i]['Expression'].'</textarea></td>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><textarea name="text['.$this->classes[$i]['text'].']" cols="18" rows="3">'.$this->classes[$i]['text'].'</textarea></td>
				<td align="center" style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><input size="3" type="text" name="order['.$this->classes[$i]['Class_ID'].']" value="'.$this->classes[$i]['drawingorder'].'"></td>
				<td style="border-left:1px solid #C3C7C3; border-bottom:1px solid #C3C7C3"><a href="javascript:Bestaetigung(\'index.php?go=Layereditor_Klasse_Löschen&class_id='.$this->classes[$i]['Class_ID'].'&selected_layer_id='.$this->formvars['selected_layer_id'].'#Klassen\', \''.$this->strDeleteWarningMessage.'\');">'.$this->strDelete.'</a></td>
			</tr>						
				';
			}
			?>
			<tr>
				<td style="border-bottom:1px solid #C3C7C3" colspan="8"><a href="index.php?go=Layereditor_Klasse_Hinzufügen&selected_layer_id=<? echo $this->formvars['selected_layer_id'] ?>#Klassen"><?php echo $strAddClass; ?></a></td>
			</tr>
			<tr>
				<td colspan="8"><a href="index.php?go=Style_Label_Editor&selected_layer_id=<? echo $this->formvars['selected_layer_id'] ?>">Styles und Labels bearbeiten</a></td>
			</tr>
		</table>
		<?}?>
		</td>
		<td valign="top">
			<a style="float: right; margin-top: -20px; margin-right: 10px;" href="javascript:window.scrollTo(0, document.body.scrollHeight);"	title="nach unten">
				<i class="fa fa-arrow-down hover-border" aria-hidden="true"></i>
			</a>
		</td>
  </tr>
  <tr>
  	<td>&nbsp;</td>
  </tr>
  <tr> 
    <td align="center">
    	<input type="hidden" name="go_plus" id="go_plus" value="">
    	<input type="button" name="dummy2" value="<?php echo $this->strButtonBack; ?>" onclick="location.href='index.php?go=Layer_Anzeigen'">&nbsp;<?php
     if ($this->formvars['selected_layer_id']>0) { ?>
      <input type="hidden" name="selected_layer_id" value="<?php echo $this->formvars['selected_layer_id']; ?>">
      <input type="button" name="dummy" value="<?php echo $strButtonSave; ?>" onclick="submitWithValue('GUI','go_plus','Ändern')">
      <?php
     } ?>&nbsp;<input type="button" name="dummy" value="<?php echo $strButtonSaveAsNewLayer; ?>" onclick="submitWithValue('GUI','go_plus','Als neuen Layer eintragen')">     
		</td>
		<td valign="top">
			<a style="float: right; margin-top: -20px; margin-right: 10px;" href="javascript:window.scrollTo(0, 0);"	title="nach oben">
				<i class="fa fa-arrow-up hover-border" aria-hidden="true"></i>
			</a>
		</td>
  </tr>
  <tr>
  	<td>&nbsp;</td>
  </tr>
</table>

<input type="hidden" name="go" value="Layereditor">
<input type="hidden" name="selstellen" value="<? 
	echo $this->formvars['selstellen']["ID"][0];
  for($i=1; $i < count($this->formvars['selstellen']["Bezeichnung"]); $i++){
  	echo ', '.$this->formvars['selstellen']["ID"][$i];
  }
?>">
