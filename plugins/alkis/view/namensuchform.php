<?php
  include(LAYOUTPATH.'languages/namensuche_'.$this->user->rolle->language.'.php');
	include('funktionen/input_check_functions.php');
 ?>

<script type="text/javascript">
<!--

	function checkall(name){
		var flurstkennz = "";
		var flurstarray = document.getElementsByName(name);
		if(flurstarray[0].checked){
			check = false;
		}
		else{
			check = true;
		}
		for(i = 0; i < flurstarray.length; i++){
			flurstarray[i].checked = check;
		}
	}

	function changeorder(orderby){
		document.GUI.order.value = orderby;
		document.GUI.go.value = 'Namen_Auswaehlen_Suchen';
		document.GUI.submit();
	}

	function nextquery(offset){
		if(offset.value == ''){
			offset.value = 0;
		}
		offset.value = parseInt(offset.value) + parseInt(document.GUI.anzahl.value);
		document.GUI.go.value = 'Namen_Auswaehlen_Suchen';
		document.GUI.submit();
	}

	function prevquery(offset){
		if(parseInt(offset.value) < parseInt(document.GUI.anzahl.value)){
			offset.value = 0;
		}
		else{
			offset.value = parseInt(offset.value) - parseInt(document.GUI.anzahl.value);
		}
		document.GUI.go.value = 'Namen_Auswaehlen_Suchen';
		document.GUI.submit();
	}

	function save(){
		gdatum = document.getElementsByName('name4')[0].value;
		if(gdatum != '' && !checkDate(gdatum)){
  		alert('Das Geburtsdatum hat nicht das Format TT.MM.JJJJ.');
  		return;
  	}
		document.GUI.offset.value = 0;
		document.GUI.go.value = 'Namen_Auswaehlen_Suchen';
		document.GUI.submit();
	}

	function send_selected_flurst(go, i, formnummer, wz, target){
		currentform.go_backup.value=currentform.go.value;
		var semi = false;
		var flurstkennz = "";
		var flurstarray = document.getElementsByName("check_flurstueck_"+i);
		for(i = 0; i < flurstarray.length; i++){
			if(flurstarray[i].checked == true){
				if(semi == true){
					flurstkennz += ';';
				}
				flurstkennz += flurstarray[i].value;
				semi = true;
			}
		}
		if(semi == true){
			currentform.target = '';
			if(target == '_blank'){
				currentform.target = '_blank';
			}
			currentform.go.value=go;
			currentform.FlurstKennz.value=flurstkennz;
			currentform.formnummer.value=formnummer;
			currentform.wz.value=wz;
			currentform.submit();
		}
		else{
			alert('Es wurden keine Flurstücke ausgewählt.');
		}
	}

	function send_selected_grundbuecher(go){
		var semi = false;
		var grundbuecher = "";
		var gbarray = document.getElementsByName("check_grundbuch");
		for(i = 0; i < gbarray.length; i++){
	  	if(gbarray[i].checked == true){
	  		if(semi == true){
	    		grundbuecher += ', ';
	    	}
	    	grundbuecher += gbarray[i].value;
	    	semi = true;
	    }
	  }
	  if(semi == true){
		  currentform.selBlatt.value = grundbuecher;
			currentform.go.value = go;
		 	currentform.submit();
		}
		else{
			alert('Es wurden keine Grundbuchblätter ausgewählt.');
		}
	}

	function grundbuchsuche(bezirk, blatt){
		document.GUI.selBlatt.value = bezirk+'-'+blatt;
		document.GUI.go.value = 'Grundbuchblatt_Auswaehlen_Suchen';
		document.GUI.submit();
	}

	function flurstsuche(bezirk, blatt){
		document.GUI.selBlatt.value = bezirk+'-'+blatt;
		document.GUI.go.value = 'Suche_Flurstuecke_zu_Grundbuechern';
		document.GUI.submit();
	}

//-->
</script>

<br><h2><?php echo $strTitle; ?></h2>
<?php if ($this->Fehlermeldung!='') {
include(LAYOUTPATH."snippets/Fehlermeldung.php");
}
?><p>

<table border="0" cellpadding="0" cellspacing="2" width="100%" style="padding: 10px">
<? if($this->formvars['gml_id'] == ''){ ?>
  <tr>
    <td colspan="3" class="menu"><span class="fett">&nbsp;Person</span></td>
  </tr>
  <tr>
    <td colspan="3" height="10">&nbsp;</td>
  </tr>
  <tr>
    <td height="28" align="right" width="220px"><span class="fett"><?php echo $strName1; ?>:&nbsp;</span></td>
    <td width="210px">
			<div style="width:150px;">
				<input name="name1" type="text" value="<? echo htmlentities($this->formvars['name1'], NULL, 'UTF-8'); ?>" size="25" tabindex="1">
				<div valign="top" style="height:0px; position:relative;">
					<div id="suggests1" style="display:none; position:absolute; left:0px; top:0px; width: 150px; vertical-align:top; overflow:hidden; border:solid grey 1px;"></div>
				</div>
			</div>
		</td>
		<td rowspan="2"><input type="checkbox" name="exakt" value="1" <? if($this->formvars['exakt']) echo 'checked'; ?>> exakte Suche</td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName2; ?>:&nbsp;</span></td>
    <td>
			<div style="width:150px;">
				<input name="name2" type="text" value="<?php echo $this->formvars['name2']; ?>" size="25" tabindex="1">
				<div valign="top" style="height:0px; position:relative;">
					<div id="suggests2" style="display:none; position:absolute; left:0px; top:0px; width: 150px; vertical-align:top; overflow:hidden; border:solid grey 1px;"></div>
				</div>
			</div>
		</td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett">Namensbestandteil:&nbsp;</span>
      </td>
    <td><input name="name9" type="text" value="<?php echo $this->formvars['name9']; ?>" size="25"  tabindex="3"></td>
  </tr>
	<tr>
    <td height="28" align="right"><span class="fett">akademischer Grad:&nbsp;</span>
      </td>
    <td><input name="name10" type="text" value="<?php echo $this->formvars['name10']; ?>" size="25"  tabindex="3"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName3; ?>:&nbsp;</span>
      </td>
    <td><input name="name3" type="text" value="<?php echo $this->formvars['name3']; ?>" size="25"  tabindex="3"></td>
  </tr>
  <tr>
    <td height="28" height="28" align="right"><span class="fett"><?php echo $strName4; ?>:&nbsp;</span>
      </td>
    <td>
			<input name="name4" type="text" value="<?php echo $this->formvars['name4']; ?>" size="25"  tabindex="4">
			&nbsp;<a href="javascript:;"><img title="TT.MM.JJJJ" src="<? echo GRAPHICSPATH; ?>calendarsheet.png" border="0">
		</td>
  </tr>
	<tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName5; ?>:&nbsp;</span>
    </td>
    <td><input name="name5" type="text" value="<?php echo $this->formvars['name5']; ?>" size="25" tabindex="1"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName6; ?>:&nbsp;</span>
      </td>
    <td><input name="name6" type="text" value="<?php echo $this->formvars['name6']; ?>" size="25" tabindex="2"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName7; ?>:&nbsp;</span>
      </td>
    <td><input name="name7" type="text" value="<?php echo $this->formvars['name7']; ?>" size="25"  tabindex="3"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strName8; ?>:&nbsp;</span>
      </td>
    <td><input name="name8" type="text" value="<?php echo $this->formvars['name8']; ?>" size="25"  tabindex="4"></td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="2" width="100%" style="padding: 10px">
  <tr>
    <td colspan="3" class="menu"><span class="fett">&nbsp;Gebiet</span></td>
  </tr>
  <tr>
    <td colspan="3" height="10">&nbsp;</td>
  </tr>
  <tr>
    <td height="28" align="right" width="220px"><span class="fett"><?php echo $strGbbez; ?>:&nbsp;</span></td>
    <td><input name="bezirk" type="text" value="<?php echo $this->formvars['bezirk']; ?>" size="6"  tabindex="5"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strGbbl; ?>:&nbsp;</span></td>
    <td><input name="blatt" type="text" value="<?php echo $this->formvars['blatt']; ?>" size="6"  tabindex="6"></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strGemkg; ?>:&nbsp;</span></td>
    <td><?php echo $this->GemkgFormObj->html; ?></td>
  </tr>
  <tr>
    <td height="28" align="right"><span class="fett"><?php echo $strFlur; ?>:&nbsp;</span></td>
    <td><?php echo $this->FlurFormObj->html; ?></td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="2" width="100%" style="padding: 10px">
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <!--
  <tr>
    <td><span class="fett"><?php echo $strHintCaseSens; ?></span>&nbsp;
    <input name="caseSensitive" type="checkbox" value="1"<?php if ($this->formvars['caseSensitive']) { ?> checked<?php } ?>><tr><td colspan="2"></td>
  <tr><td colspan="2"></tr>//-->
  <tr bgcolor="#FFFFCC">
    <td colspan="3"><em><?php echo $strHintWildcard; ?>.</em></td>
  </tr>
  <tr>
    <td colspan="3" height="10">&nbsp;</td>
  </tr>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="2" width="100%">
  <tr>
    <td height="28" align="right" width="220px"><span class="fett"><?php echo $strShowHits; ?>:&nbsp;</span></td>
    <td><input name="anzahl" onkeyup="checknumbers(this, 'int2', '', '');" type="text" value="<?php echo $this->formvars['anzahl']; ?>" size="2" tabindex="5"></td>
  </tr>
  <tr>
    <td height="28" align="right" width="220px"><span class="fett"><?php echo $strShowWithFst; ?>:&nbsp;</span></td>
    <td>
      <input name="withflurst" type="checkbox" <? if($this->formvars['withflurst'] == 'on'){echo 'checked';} ?>>&nbsp;
			<span data-tooltip="Zeigt auch die zum Grundbuchblatt gehörenden Flurstücke an."></span>
    </td>
  </tr>
  <tr>
    <td width="290px">&nbsp;</td>
    <td>      
      <input type="submit" onclick="save();" style="width: 0px;height: 0px;border: none">
      <input type="button" name="go_plus" onclick="save();" value="<?php echo $strSearch; ?>" tabindex="0"><br>
   </td>
  </tr>
<? }
  $anzNamen=count($this->namen);
  if ($anzNamen>0) {
   ?>
	<tr>
    <td colspan="3" align="center">
			<span class="fett"><br>
			<?php echo $strTotalHits; ?>: <?php echo $this->anzNamenGesamt; ?>
				<br>
				<br>
			</span>
<? if($this->formvars['gml_id'] != ''){ ?>
			<span class="fett"><?php echo $strShowHits; ?>:&nbsp;</span>
			<input name="anzahl" onkeyup="checknumbers(this, 'int2', '', '');" type="text" value="<?php echo $this->formvars['anzahl']; ?>" size="2" tabindex="5">
			<input type="button" onclick="document.GUI.go.value = 'Namen_Auswaehlen_Suchen';document.GUI.submit();" value="aktualisieren">
			<br>
			<br>
<? } ?>			
			<table border="1" cellpadding="3" cellspacing="0">
				<tr bgcolor="<?php echo BG_DEFAULT ?>">
					<td class="menu">&nbsp;</td>
					<td class="menu" align="center"><span class="fett"><a href="javascript:changeorder('bezirk');"><?php echo $strGbbezShort; ?></a></span></td>
					<td class="menu" align="center"><span class="fett"><a href="javascript:changeorder('blatt');"><?php echo $strGbblShort; ?></a></span></td>
					<td class="menu" align="left"><span class="fett"><a href="javascript:changeorder('nachnameoderfirma, vorname');"><?php echo $strName1Short; ?></a></span></td>
					<td class="menu" align="left"><span class="fett"><a href="javascript:changeorder('geburtsname');"><?php echo $strName2Short; ?></a></span></td>
					<td class="menu" align="left" bgcolor="<?php echo BG_DEFAULT ?>"><span class="fett"><a href="javascript:changeorder('strasse,hausnummer');"><?php echo $strName3Short; ?></a></span></td>
					<td class="menu" align="left"><span class="fett"><a href="javascript:changeorder('postleitzahlpostzustellung, ort_post');"><?php echo $strName4Short; ?></a></span></td>
					<td class="menu" align="center" colspan="2"><span class="fett"><?php echo $strFst; ?></span></td>
				</tr>
  <?php
  for ($i=0;$i<count($this->namen);$i++) {

  	$this->namen[$i]['name1'] = str_replace(',,,', '', $this->namen[$i]['name1']);
		$this->namen[$i]['name1'] = str_replace(',,', ',', $this->namen[$i]['name1']);
		if(substr($this->namen[$i]['name1'], strlen($this->namen[$i]['name1'])-1) == ',') {
			$this->namen[$i]['name1'] = substr($this->namen[$i]['name1'], 0, strlen($this->namen[$i]['name1'])-1);
		}

  ?>
      <tr>
      	<td style="border: 1px solid #cccccc;"><input type="checkbox" name="check_grundbuch" value="<? echo $this->namen[$i]['bezirk'].'-'.$this->namen[$i]['blatt']; ?>"></td>
        <td style="border: 1px solid #cccccc;" align="center"><a href="javascript:grundbuchsuche(<?php echo '\''.$this->namen[$i]['bezirk'].'\',\''.$this->namen[$i]['blatt'].'\''; ?>);"><?php echo $this->namen[$i]['bezirk']; ?></a></td>
        <td style="border: 1px solid #cccccc;" align="center"><a href="javascript:grundbuchsuche(<?php echo '\''.$this->namen[$i]['bezirk'].'\',\''.$this->namen[$i]['blatt'].'\''; ?>);"><?php echo $this->namen[$i]['blatt']; ?></a></td>
        <td style="border: 1px solid #cccccc;" align="left"><?php echo str_replace(' ','&nbsp;',$this->namen[$i]['name1']); if ($this->namen[$i]['name1']=='') { ?>&nbsp;<?php } ?></td>
        <td style="border: 1px solid #cccccc;" align="left"><?php echo str_replace(' ','&nbsp;',$this->namen[$i]['name2']); if ($this->namen[$i]['name2']=='') { ?>&nbsp;<?php } ?></td>
        <td style="border: 1px solid #cccccc;" align="left"><?php echo str_replace(' ','&nbsp;',$this->namen[$i]['name3']); if ($this->namen[$i]['name3']=='') { ?>&nbsp;<?php } ?></td>
        <td style="border: 1px solid #cccccc;" align="left"><?php echo str_replace(' ','&nbsp;',$this->namen[$i]['name4']); if ($this->namen[$i]['name4']=='') { ?>&nbsp;<?php } ?></td>
        <td style="border: 1px solid #cccccc;" align="center"><a href="javascript:flurstsuche('<?php echo $this->namen[$i]['bezirk'].'\',\''.$this->namen[$i]['blatt']; ?>');"><?php echo $strShowFst; ?></a></td>
		<td style="border: 1px solid #cccccc;" align="center"><a href="index.php?go=Zeige_Flurstuecke_zu_Grundbuechern&selBlatt=<?php echo $this->namen[$i]['bezirk'].'-'.$this->namen[$i]['blatt'];?>"><?php echo $strToMap; ?></a></td>
      </tr>
    <? if($this->formvars['withflurst'] == 'on'){ ?>
    	<tr>
    		<td style="border: 1px solid #cccccc; border-top: 0px solid #ffffff;" colspan="10">

    			<table width="100%" border="0" cellpadding="0" cellspacing="0">
    				<tr>
    					<td></td>
    					<td><span class="px13 fett"><?php echo $strParcelNo; ?></span></span></td>
    					<td><span class="px13 fett"><?php echo $strGemkgName; ?></span></span></td>
    					<td><span class="px13 fett"><?php echo $strAreaALB; ?></span></span></td>
    					<td><span class="px13 fett"><?php echo $strDoPrintoutsALB; ?></span></span></td>
    					<td><span class="px13 fett"><?php echo $strMapSection; ?></span></span></td>
    				</tr>
	    <?	for($j = 0; $j < count($this->namen[$i]['flurstuecke']); $j++){ ?>
			      <tr>
			      	<td>
			      	<? if(count($this->namen[$i]['flurstuecke']) > 1){ ?>
			      		<input type="checkbox" name="check_flurstueck_<? echo $i; ?>" value="<? echo $this->namen[$i]['flurstuecke'][$j]; ?>">
			      	<? }
			      		 else{ ?>
			      		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			      	<? } ?>
			      	</td>
			      	<td><span class="px13"><? echo formatFlurstkennzALK($this->namen[$i]['flurstuecke'][$j]); ?></span></td>
			      	<td><span class="px13"><? echo $this->namen[$i]['alb_data'][$j]['gemkgname']; ?></span></td>
			      	<td><span class="px13"><? echo $this->namen[$i]['alb_data'][$j]['flaeche']; ?> m²</span></td>
			      	<td>
			      		<? $this->getFunktionen(); ?>
								<select style="width: 130px">
									<option>-- Auswahl --</option>
									<? if($this->Stelle->funktionen['MV0510']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALKIS_Auszug&formnummer=MV0510&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurstücksnachweis</option><? } ?>
									<? if($this->Stelle->funktionen['MV0550']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALKIS_Auszug&formnummer=MV0550&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurstücks- und Eigentumsnachweis</option><? } ?>
									<? if($this->Stelle->funktionen['MV0520']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALKIS_Auszug&formnummer=MV0520&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurstücksnachweis mit Bodenschätzung</option><? } ?>
									<? if($this->Stelle->funktionen['MV0560']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALKIS_Auszug&formnummer=MV0560&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurstücks- und Eigentumsnachweis mit Bodenschätzung</option><? } ?>

									<? if($this->Stelle->funktionen['ALB-Auszug 30']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALB_Anzeige&formnummer=30&wz=1&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurst&uuml;cksdaten</option><? } ?>
									<? if($this->Stelle->funktionen['ALB-Auszug 35']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALB_Anzeige&formnummer=35&wz=1&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Flurst&uuml;cksdaten&nbsp;mit&nbsp;Eigent&uuml;mer</option><? } ?>
									<? if($this->Stelle->funktionen['ALB-Auszug 40']['erlaubt']){ ?><option onclick="window.open('index.php?go=ALB_Anzeige&formnummer=40&wz=1&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>','_blank')">Eigent&uuml;merdaten&nbsp;zum&nbsp;Flurst&uuml;ck</option><? } ?>
								</select>
				      </td>
							<td><a href="index.php?go=ZoomToFlst&FlurstKennz=<?php echo $this->namen[$i]['flurstuecke'][$j]; ?>"><span style="font-size:12px;"><?php echo $strMapSection; ?></span></a></td>
			      </tr>
	    <?	}
	    		if(count($this->namen[$i]['flurstuecke']) > 1 AND $this->Stelle->funktionen['ohneWasserzeichen']['erlaubt']){ ?>
	    		<tr>
	    			<td colspan="6">&nbsp;&nbsp;<? echo '<a href="javascript:checkall(\'check_flurstueck_'.$i.'\');"><img src="'.GRAPHICSPATH.'pfeil_unten-rechts.gif" width="10" height="20" border="0"></a>'; ?>&nbsp;<?php echo $strSelFst; ?>:
							<select style="width: 130px">
								<option>-- Auswahl --</option>
								<? if($this->Stelle->funktionen['MV0510']['erlaubt']){ ?><option onclick="send_selected_flurst('ALKIS_Auszug', '<? echo $i; ?>', 'MV0510', 1, '_blank');">Flurstücksnachweis</option><? } ?>
								<? if($this->Stelle->funktionen['MV0550']['erlaubt']){ ?><option onclick="send_selected_flurst('ALKIS_Auszug', '<? echo $i; ?>', 'MV0550', 1, '_blank');">Flurstücks- und Eigentumsnachweis</option><? } ?>
								<? if($this->Stelle->funktionen['MV0520']['erlaubt']){ ?><option onclick="send_selected_flurst('ALKIS_Auszug', '<? echo $i; ?>', 'MV0520', 1, '_blank');">Flurstücksnachweis mit Bodenschätzung</option><? } ?>
								<? if($this->Stelle->funktionen['MV0560']['erlaubt']){ ?><option onclick="send_selected_flurst('ALKIS_Auszug', '<? echo $i; ?>' 'MV0560', 1, '_blank');">Flurstücks- und Eigentumsnachweis mit Bodenschätzung</option><? } ?>

								<? if($this->Stelle->funktionen['ALB-Auszug 30']['erlaubt']){ ?><option onclick="send_selected_flurst('ALB_Anzeige', '<? echo $i; ?>', '30', 1, '_blank');">Flurst&uuml;cksdaten</option><? } ?>
								<? if($this->Stelle->funktionen['ALB-Auszug 35']['erlaubt']){ ?><option onclick="send_selected_flurst('ALB_Anzeige', '<? echo $i; ?>', '35', 1, '_blank');">Flurst&uuml;cksdaten&nbsp;mit&nbsp;Eigent&uuml;mer</option><? } ?>
								<? if($this->Stelle->funktionen['ALB-Auszug 40']['erlaubt']){ ?><option onclick="send_selected_flurst('ALB_Anzeige', '<? echo $i; ?>', '40', 1, '_blank');">Eigent&uuml;merdaten&nbsp;zum&nbsp;Flurst&uuml;ck</option><? } ?>
							</select>
	    			</td>
	    		</tr>
	    		<? } ?>
				  <tr><td><span style="font-size:8px;">&nbsp;</span></td></tr>
					</table>

				</td>
			</tr>
	<?	}
	  }
	  ?>
    </table></td>
  </tr>
  <tr height="20px" valign="bottom">
		<td colspan="2">&nbsp;&nbsp;&nbsp;<? echo '<a href="javascript:checkall(\'check_grundbuch\');">'.$strCheckAll.'</a>'; ?>
		</td>
	</tr>
  <tr>
		<td colspan="2">&nbsp;&nbsp;&nbsp;<? echo '<a href="javascript:checkall(\'check_grundbuch\');"><img src="'.GRAPHICSPATH.'pfeil_unten-rechts.gif" width="10" height="20" border="0"></a>'; ?>&nbsp;<?php echo $strSelGbbl; ?>: <a href="javascript:send_selected_grundbuecher('Grundbuchblatt_Auswaehlen_Suchen');">anzeigen</a>&nbsp;|&nbsp;<a href="javascript:send_selected_grundbuecher('Suche_Flurstuecke_zu_Grundbuechern');"><?php echo $strShowFst; ?></a>&nbsp;|&nbsp;<a href="javascript:send_selected_grundbuecher('Zeige_Flurstuecke_zu_Grundbuechern');"><?php echo $strShowFstInMap; ?></a>
		</td>
	</tr>
  <tr>
  	<td colspan="9" align="center">

  	<?	# Blätterfunktion
	   if($this->formvars['offset'] == ''){
		   $this->formvars['offset'] = 0;
		 }
		 $von = $this->formvars['offset'] + 1;
	   $bis = $this->formvars['offset'] + $this->formvars['anzahl'];
	   if($bis > $this->anzNamenGesamt){
	   	$bis = $this->anzNamenGesamt;
	   }
	   echo'
	   <table width="400" border="0" cellpadding="2" cellspacing="0">
	   	<tr><td colspan="3">&nbsp;</td></tr>
	   	<tr align="center">
	   		<td width="100">
	   		  <table border="0" align="right">
	   		    <tr>
	   		      <td>&nbsp;';
	   		        if($this->formvars['offset'] > 0){
	   		        	echo '<a href="javascript:prevquery(document.GUI.offset);">'.$strBack.'&nbsp;<img src="'.GRAPHICSPATH.'pfeil_links.gif" width="10" height="10" border="0"></a>';
	   		        }
	              echo '
			      </td>
			    </tr>
			  </table>
			</td>
			<td width="150">
			  <span style="color:#32326E;">
			    '.$von.' - '.$bis.' von '.$this->anzNamenGesamt.'
			  </span>
			</td>
	        <td width="100">
	   		  <table border="0" align="left">
	   		    <tr>
	   		      <td>';
	      if($bis < $this->anzNamenGesamt){
	      	echo '<a href="javascript:nextquery(document.GUI.offset);"><img src="'.GRAPHICSPATH.'pfeil_rechts.gif" width="10" height="10" border="0">&nbsp;'.$strNext.'</a>';
	      }
	      echo '&nbsp;
			      </td>
			    </tr>
			  </table>
			</td>
	    </tr>
	   </table>
	   ';
  ?>
  	</td>
  </tr>

  <?php
  }
  ?>

</table>
<? if($this->formvars['gml_id'] != ''){ ?>
		<a href="index.php?go=get_last_query">zurück</a>
		<input name="withflurst" type="hidden" value="<?php echo $this->formvars['withflurst']; ?>">
<? } ?>
<input type="hidden" name="go" value="Namen_Auswaehlen">
<input name="gml_id" type="hidden" value="<? echo $this->formvars['gml_id']; ?>">
<input type="hidden" name="go_backup" value="">
<input name="namensuche" type="hidden" value="true">
<input name="selBlatt" type="hidden" value="">
<input name="Grundbuecher" type="hidden" value="">
<input name="lfd_nr_name" type="hidden" value="">
<input name="offset" type="hidden" value="<? echo $this->formvars['offset']; ?>">
<input type="hidden" name="order" value="<? echo $this->formvars['order'] ?>">
<input type="hidden" name="FlurstKennz" value="">
<input type="hidden" name="formnummer" value="">
<input type="hidden" name="wz" value="">

