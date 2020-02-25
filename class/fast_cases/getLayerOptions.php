<?

function replace_params($str, $params, $user_id = NULL, $stelle_id = NULL, $hist_timestamp = NULL, $language = NULL) {
	if (is_array($params)) {
		foreach($params AS $key => $value){
			$str = str_replace('$'.$key, $value, $str);
		}
	}
	if (!is_null($user_id))				 $str = str_replace('$user_id', $user_id, $str);
	if (!is_null($stelle_id))			 $str = str_replace('$stelle_id', $stelle_id, $str);
	if (!is_null($hist_timestamp)) $str = str_replace('$hist_timestamp', $hist_timestamp, $str);
	if (!is_null($language))			 $str = str_replace('$language', $language, $str);
	return $str;
}

function get_select_parts($select){
	$column = explode(',', $select);		# an den Kommas splitten
  for($i = 0; $i < count($column); $i++){
  	$klammerauf = substr_count($column[$i], '(');
  	$klammerzu = substr_count($column[$i], ')');
		$hochkommas = substr_count($column[$i], "'");
		# Wenn ein Select-Teil eine ungerade Anzahl von Hochkommas oder mehr Klammern auf als zu hat,
		# wurde hier entweder ein Komma im einem String verwendet (z.B. x||','||y) oder eine Funktion (z.B. round(x, 2)) bzw. eine Unterabfrage mit Kommas verwendet
  	if($hochkommas % 2 != 0 OR $klammerauf > $klammerzu){
  		$column[$i] = $column[$i].','.$column[$i+1];
  		array_splice($column, $i+1, 1);
			$i--;							# und nochmal prüfen, falls mehrere Kommas drin sind
  	}
  }
  return $column;
}

function get_first_word_after($str, $word, $delim1 = ' ', $delim2 = ' ', $last = false){
	if($last)$word_pos = strripos($str, $word);
	else $word_pos = stripos($str, $word);
	if($word_pos !== false){
		$str_from_word_pos = substr($str, $word_pos+strlen($word));
		$parts = explode($delim2, trim($str_from_word_pos, $delim1));
		return $parts[0];
	}
}


class GUI {

  var $layout;
  var $style;
  var $mime_type;
  var $menue;
  var $pdf;
  var $addressliste;
  var $debug;
  var $dbConn;
  var $flst;
  var $formvars;
  var $legende;
  var $map;
  var $mapDB;
  var $img;
  var $FormObject;
  var $StellenForm;
  var $Fehlermeldung;
  var $messages;
  var $Hinweis;
  var $Stelle;
  var $ALB;
  var $activeLayer;
  var $nImageWidth;
  var $nImageHeight;
  var $user;
  var $qlayerset;
  var $scaleUnitSwitchScale;
  var $map_scaledenom;
  var $map_factor;
  var $formatter;
  var $success;

	function GUI($main, $style, $mime_type) {
		# Debugdatei setzen
		global $debug;
		$this->debug = $debug;

		# Logdatei für Mysql setzen
		global $log_mysql;
		$this->log_mysql = $log_mysql;

		# Logdatei für PostgreSQL setzten
		global $log_postgres;
		$this->log_postgres = $log_postgres;

		global $log_loginfail;
		$this->log_loginfail = $log_loginfail;

		# layout Templatedatei zur Anzeige der Daten
		if ($main != "") {
			$this->main = $main;
		}

		# Stylesheetdatei
		if (isset($style)) {
			$this->style = $style;
		}

		# mime_type html, pdf
		if (isset ($mime_type)) $this->mime_type=$mime_type;
		$this->scaleUnitSwitchScale = 239210;
		$this->trigger_functions = array();
	}

	function loadMultiLingualText($language) {
    #echo 'In der Rolle eingestellte Sprache: '.$GUI->user->rolle->language;
    $this->Stelle->language=$language;
    $this->Stelle->getName();
    include(LAYOUTPATH.'languages/'.$this->user->rolle->language.'.php');
  }

	function getLayerOptions(){
		$mapDB = new db_mapObj($this->Stelle->id,$this->user->id);
		if($this->formvars['layer_id'] > 0)$layer = $this->user->rolle->getLayer($this->formvars['layer_id']);
		else $layer = $this->user->rolle->getRollenLayer(-$this->formvars['layer_id']);
		if($layer[0]['connectiontype']==6){
			$layerdb = $mapDB->getlayerdatabase($this->formvars['layer_id'], $this->Stelle->pgdbhost);
			$attributes = $mapDB->getDataAttributes($layerdb, $this->formvars['layer_id'], false);
			$privileges = $this->Stelle->get_attributes_privileges($this->formvars['layer_id']);
		}
		$disabled_classes = $mapDB->read_disabled_classes();
		$layer[0]['Class'] = $mapDB->read_Classes($this->formvars['layer_id'], $disabled_classes);
		echo '
		<div class="layerOptions" id="options_content_'.$this->formvars['layer_id'].'">
			<div style="position: absolute;top: 0px;right: 0px"><a href="javascript:closeLayerOptions('.$this->formvars['layer_id'].');" title="Schlie&szlig;en"><img style="border:none" src="'.GRAPHICSPATH.'exit2.png"></img></a></div>
			<table cellspacing="0" cellpadding="0" style="padding-bottom: 8px">
				<tr>
					<td class="layerOptionsHeader">
						<span class="fett">'.$this->layerOptions.'</span>
					</td>
				</tr>
				<tr>
					<td>
						<ul>';
						if($this->formvars['layer_id'] < 0){
							echo '<li><a href="index.php?go=delete_rollenlayer&id='.(-$this->formvars['layer_id']).'">'.$this->strRemove.'</a></li>';
							echo '<li><span>'.$this->strName.':</span> <input type="text" name="layer_options_name" value="'.$layer[0]['Name'].'"></li>';
						}
						else{
							if($this->Stelle->isMenueAllowed('Layer_Anzeigen')){
								echo '<li><span><a href="javascript:toggle(document.getElementById(\'layer_properties\'));">'.$this->properties.'</a></span></li>';
								echo '<div id="layer_properties" style="display: none">
												<ul>
													<li><a href="index.php?go=Layereditor&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->layerDefinition.'</a></li>
													<li><a href="index.php?go=Attributeditor&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->attributeditor.'</a></li>
													<li><a href="index.php?go=Layerattribut-Rechteverwaltung&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->strPrivileges.'</a></li>
													<li><a href="index.php?go=Style_Label_Editor&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->strStyles.'</a></li>
												</ul>
											</div>';
							}
						}
						if($layer[0]['connectiontype']==6 OR($layer[0]['Datentyp']==MS_LAYER_RASTER AND $layer[0]['connectiontype']!=7)){
							echo '<li><a href="javascript:zoomToMaxLayerExtent('.$this->formvars['layer_id'].')">'.$this->FullLayerExtent.'</a></li>';
						}
						if(in_array($layer[0]['connectiontype'], [MS_POSTGIS, MS_WFS]) AND $layer[0]['queryable']){
							echo '<li><a href="index.php?go=Layer-Suche&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->strSearch.'</a></li>';
						}
						if($layer[0]['privileg'] > 0){
							echo '<li><a href="index.php?go=neuer_Layer_Datensatz&selected_layer_id='.$this->formvars['layer_id'].'">'.$this->newDataset.'</a></li>';
						}
						if($layer[0]['Class'][0]['Name'] != ''){
							if($layer[0]['showclasses'] != ''){
								echo '<li><a href="javascript:getlegend(\''.$layer[0]['Gruppe'].'\', '.$this->formvars['layer_id'].', document.GUI.nurFremdeLayer.value);closeLayerOptions('.$this->formvars['layer_id'].')">';
								if($layer[0]['showclasses'])echo $this->HideClasses;
								else echo $this->DisplayClasses;
								echo '</a></li>';
							}
							for($c = 0; $c < count($layer[0]['Class']); $c++){
								$class_ids[] = $layer[0]['Class'][$c]['Class_ID'];
							}
							if($layer[0]['Class'][0]['Status'] == '1' || $layer[0]['Class'][1]['Status'] == '1')echo '<li><a href="javascript:deactivateAllClasses(\''.implode(',', $class_ids).'\')">'.$this->deactivateAllClasses.'</a></li>';
							if($layer[0]['Class'][0]['Status'] == '0' || $layer[0]['Class'][1]['Status'] == '0')echo '<li><a href="javascript:activateAllClasses(\''.implode(',', $class_ids).'\')">'.$this->activateAllClasses.'</a></li>';
						}
						if($layer[0]['connectiontype']==6 AND ($this->formvars['layer_id'] < 0 OR $layer[0]['original_labelitem'] != '')){		# für Rollenlayer oder normale Layer mit labelitem
							echo '<li><span>'.$this->label.':</span>
											<select name="layer_options_labelitem">
												<option value=""> - '.$this->noLabel.' - </option>';
												if($this->formvars['layer_id'] > 0){
													echo '<option value="'.$layer[0]['original_labelitem'].'" '.($layer[0]['labelitem'] == $layer[0]['original_labelitem'] ? 'selected' : '').'>'.$layer[0]['original_labelitem'].'</option>';
												}
												for($i = 0; $i < count($attributes)-2; $i++){
													if(($this->formvars['layer_id'] < 0 OR ($privileges[$attributes[$i]['name']] != '' AND $attributes[$i]['name'] != $layer[0]['original_labelitem'])) AND $attributes['the_geom'] != $attributes[$i]['name'])echo '<option value="'.$attributes[$i]['name'].'" '.($layer[0]['labelitem'] == $attributes[$i]['name'] ? 'selected' : '').'>'.$attributes[$i]['name'].'</option>';
												}
							echo 	 '</select>
										</li>';
						}
						echo '<li><span>'.$this->transparency.':</span> <input name="layer_options_transparency" onchange="transparency_slider.value=parseInt(layer_options_transparency.value);" style="width: 30px" value="'.$layer[0]['transparency'].'"><input type="range" id="transparency_slider" name="transparency_slider" style="height: 6px; width: 120px" value="'.$layer[0]['transparency'].'" onchange="layer_options_transparency.value=parseInt(transparency_slider.value);layer_options_transparency.onchange()" oninput="layer_options_transparency.value=parseInt(transparency_slider.value);layer_options_transparency.onchange()"></li>';
						if(ROLLENFILTER AND $this->user->rolle->showrollenfilter){
							echo '	
									<li>
									<a href="javascript:void(0);" onclick="$(\'#rollenfilter, #rollenfilterquestionicon\').toggle()">Filter</a>
									<a href="javascript:void(0);" onclick="message(\'\
										Sie können im Textfeld einen SQL-Ausdruck eintragen, der sich als Filter auf die Kartendarstellung und Sachdatenanzeige des Layers auswirkt.<br>\
										In diesem Thema stehen dafür folgende Attribute zur Verfügung:<br>\
										<ul>';
										for($i = 0; $i < count($attributes)-2; $i++){
											if(($this->formvars['layer_id'] < 0 OR $privileges[$attributes[$i]['name']] != '') AND $attributes['the_geom'] != $attributes[$i]['name'])echo '<li>'.$attributes[$i]['name'].'</li>';
										}									
										echo	'</ul>\
										Mehrere Filter werden mit AND oder OR verknüpft.<br>\
										Ist ein Filter gesetzt wird in der Legende neben dem Themanamen ein Filtersymbol angezeigt.<br>\
										Der Filter wird gelöscht indem das Textfeld geleert wird.<p>\
										Beispiele:<br>\
										<ul>\
											<li>id > 10 AND status = 1</li>\
											<li>type = \\\'Brunnen\\\' OR type = \\\'Quelle\\\'</li>\
											<li>status IN (1, 2)</li>\
										</ul>\
										\')">
										<i
											id="rollenfilterquestionicon"
											title="Hilfe zum Filter anzeigen"
											class="fa fa-question-circle button layerOptionsIcon"
											style="
												float: right;
												'.($layer[0]['rollenfilter'] == ''? 'display: none' : '').'
											"
										></i>
									</a><br>
									<textarea
										id="rollenfilter"
										style="
											width: 98%;
											'.($layer[0]['rollenfilter'] == ''? 'display: none' : '').'
										"
										name="layer_options_rollenfilter"
									>' . $layer[0]['rollenfilter'] . '</textarea>
								</li>';
						}
echo '			</ul>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table cellspacing="0" cellpadding="0">
							<tr>';
							if($this->formvars['layer_id'] > 0){
				echo '	<td>
									<input type="button" onmouseup="resetLayerOptions('.$this->formvars['layer_id'].')" value="'.$this->strReset.'">
								</td>';}
				echo '	<td>
									<input type="button" onmouseup="saveLayerOptions('.$this->formvars['layer_id'].')" value="'.$this->strSave.'">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		█
		legend_top = document.getElementById(\'legenddiv\').getBoundingClientRect().top;
		legend_bottom = document.getElementById(\'legenddiv\').getBoundingClientRect().bottom;
		posy = document.getElementById(\'options_'.$this->formvars['layer_id'].'\').getBoundingClientRect().top;
		options_height = document.getElementById(\'options_content_'.$this->formvars['layer_id'].'\').getBoundingClientRect().height;
		if(posy > legend_bottom - options_height)posy = legend_bottom - options_height;
		document.getElementById(\'options_content_'.$this->formvars['layer_id'].'\').style.top = document.getElementById(\'map\').offsetTop + posy - legend_top;
		';
	}
}

class database {

  var $ist_Fortfuehrung;
  var $debug;
  var $loglevel;
  var $logfile;
  var $commentsign;
  var $blocktransaction;

  function database() {
    global $debug;
    $this->debug=$debug;
    $this->loglevel=LOG_LEVEL;
 		$this->defaultloglevel=LOG_LEVEL;
 		global $log_mysql;
    $this->logfile=$log_mysql;
 		$this->defaultlogfile=$log_mysql;
    $this->ist_Fortfuehrung=1;
    $this->type="MySQL";
    $this->commentsign='#';
    # Wenn dieser Parameter auf 1 gesetzt ist werden alle Anweisungen
    # BEGIN TRANSACTION, ROLLBACK und COMMIT unterdrückt, so daß alle anderen SQL
    # Anweisungen nicht in Transactionsblöcken ablaufen.
    # Kann zur Steigerung der Geschwindigkeit von großen Datenbeständen verwendet werden
    # Vorsicht: Wenn Fehler beim Einlesen passieren, ist der Datenbestand inkonsistent
    # und der Einlesevorgang muss wiederholt werden bis er fehlerfrei durchgelaufen ist.
    # Dazu Fehlerausschriften bearchten.
    $this->blocktransaction=0;
  }

  function open() {
    $this->debug->write("<br>MySQL Verbindung öffnen mit Host: ".$this->host." User: ".$this->user,4);
    $this->dbConn=mysql_connect($this->host,$this->user,$this->passwd);
    $this->debug->write("Datenbank mit ID: ".$this->dbConn." und Name: ".$this->dbName." auswählen.",4);
    return mysql_select_db($this->dbName,$this->dbConn);
  }

  function execSQL($sql, $debuglevel, $loglevel) {
  	switch ($this->loglevel) {
  		case 0 : {
  			$logsql=0;
  		} break;
  		case 1 : {
  			$logsql=1;
  		} break;
  		case 2 : {
  			$logsql=$loglevel;
  		} break;
  	}
    # SQL-Statement wird nur ausgeführt, wenn DBWRITE gesetzt oder
    # wenn keine INSERT, UPDATE und DELETE Anweisungen in $sql stehen.
    # (lesend immer, aber schreibend nur mit DBWRITE=1)
    if (DBWRITE OR (!stristr($sql,'INSERT') AND !stristr($sql,'UPDATE') AND !stristr($sql,'DELETE'))) {
			#echo '<br>sql in execSQL: ' . $sql;
      $query=mysql_query($sql,$this->dbConn);
      #echo $sql;
      if ($query==0) {
        $ret[0]=1;
        $ret[1]="<b>Fehler bei SQL Anweisung:</b><br>".$sql."<br>".mysql_error($this->dbConn);
        $this->debug->write($ret[1], $debuglevel);
        if ($logsql) {
          $this->logfile->write("#".$ret[1]);
        }
      }
      else {
        $ret[0] = 0;
				$ret['success'] = true;
        $ret[1] = $ret['query'] = $query;
        if ($logsql) {
          $this->logfile->write($sql.';');
        }
        $this->debug->write(date('H:i:s')."<br>".$sql,$debuglevel);
      }
      $ret[2] = $sql;
    }
    else {
    	if ($logsql) {
    		$this->logfile->write($sql.';');
    	}
    	$this->debug->write("<br>".$sql,$debuglevel);
    }
    return $ret;
  }

  function close() {
    $this->debug->write("<br>MySQL Verbindung mit ID: ".$this->dbConn." schließen.",4);
    if (LOG_LEVEL>0){
    	$this->logfile->close();
    }
    return mysql_close($this->dbConn);
  }
}

class user {

  var $id;
  var $Name;
  var $Vorname;
  var $login_name;
  var $funktion;
  var $dbConn;
  var $Stellen;
  var $nZoomFactor;
  var $nImageWidth;
  var $nImageHeight;
  var $database;
  var $remote_addr;

	function user($login_name, $id, $database, $passwort = '') {
		global $debug;
		$this->debug = $debug;
		$this->database = $database;
		if ($login_name) {
			$this->login_name = $login_name;
			$this->readUserDaten(0, $login_name, $passwort);
			$this->remote_addr = getenv('REMOTE_ADDR');
		}
		else {
			$this->id = $id;
			$this->readUserDaten($id, 0);
		}
	}

	function readUserDaten($id, $login_name, $passwort = '') {
		$where = array();
		if ($id > 0) array_push($where, "ID = " . $id);
		if ($login_name != '') array_push($where, "login_name LIKE '" . $login_name . "'");
		if ($passwort != '') array_push($where, "passwort = md5('" . $passwort . "')");
		$sql = "
			SELECT
				*
			FROM
				user
			WHERE
				" . implode(" AND ", $where) . "
		";
		#echo '<br>Sql: ' . $sql;

		$this->debug->write("<p>file:users.php class:user->readUserDaten - Abfragen des Namens des Benutzers:<br>" . $sql, 3);
		$query = mysql_query($sql,$this->database->dbConn);
		if ($query == 0) { $this->debug->write("<br>Abbruch Zeile: " . __LINE__, 4); return 0; }
		$rs = mysql_fetch_array($query);
		$this->id = $rs['ID'];
		$this->login_name = $rs['login_name'];
		$this->Namenszusatz = $rs['Namenszusatz'];
		$this->Name = $rs['Name'];
		$this->Vorname = $rs['Vorname'];
		$this->stelle_id = $rs['stelle_id'];
		$this->phon = $rs['phon'];
		$this->email = $rs['email'];
		if (CHECK_CLIENT_IP) {
			$this->ips = $rs['ips'];
		}
		$this->funktion = $rs['Funktion'];
		$this->password_setting_time = $rs['password_setting_time'];
		$this->agreement_accepted = $rs['agreement_accepted'];
	}

	function setRolle($stelle_id) {
		# Abfragen und zuweisen der Einstellungen für die Rolle
		$rolle = new rolle($this->id, $stelle_id, $this->database);
		if ($rolle->readSettings()) {
			$this->rolle=$rolle;
			return 1;
		}
		return 0;
	}
}

class stelle {

  var $id;
  var $Bezeichnung;
  var $debug;
  var $nImageWidth;
  var $nImageHeight;
  var $oGeorefExt;
  var $pixsize;
  var $selectedButton;
  var $database;

	function stelle($id, $database) {
		global $debug;
		global $log_mysql;
		$this->debug = $debug;
		$this->log = $log_mysql;
		$this->id = $id;
		$this->database = $database;
		$this->Bezeichnung = $this->getName();
		$this->readDefaultValues();
	}
	
	function isMenueAllowed($menuename){
		$sql = "SELECT distinct a.* from u_menues as a, u_menue2stelle as b ";
		$sql.= "WHERE links LIKE 'index.php?go=".$menuename."%' AND b.menue_id = a.id AND b.stelle_id = ".$this->id;
		#echo $sql;
		$this->debug->write("<p>file:stelle.php class:stelle->isMenueAllowed - Guckt ob der Menuepunkt der Stelle zugeordnet ist:<br>".$sql,4);
		$query=mysql_query($sql,$this->database->dbConn);
		if ($query==0) {
			$this->debug->write("<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__,4);
			$errmsg='Fehler bei der Ueberpruefung des Menuepunkts für die Stelle';
		}
		else{
			$rs=mysql_fetch_array($query);
		}
		if($rs[0] != '') {
			return 1;
		}
		else {
			return 0;
		}
	}	

	function get_attributes_privileges($layer_id) {
		$sql = "
			SELECT
				`attributename`,
				`privileg`,
				`tooltip`
			FROM
				`layer_attributes2stelle`
			WHERE
				`stelle_id` = " . $this->id . " AND
				`layer_id` = " . $layer_id;
		#echo '<br>Sql: ' . $sql;
		$this->debug->write("<p>file:stelle.php class:stelle->get_attributes_privileges - Abfragen der Layerrechte zur Stelle:<br>" . $sql, 4);
		$query = mysql_query($sql, $this->database->dbConn);
		if ($query == 0) { $this->debug->write("<br>Abbruch in " . $PHP_SELF . " Zeile: " . __LINE__, 4); return 0; }
		while ($rs = mysql_fetch_array($query)) {
			$privileges[$rs['attributename']] = $rs['privileg'];
			$privileges['tooltip_' . $rs['attributename']] = $rs['tooltip'];
			$privileges['attributenames'][] = $rs['attributename'];
		}
		return $privileges;
	}	
	
  function getName() {
    $sql ='SELECT ';
    if ($this->language != 'german' AND $this->language != ''){
      $sql.='`Bezeichnung_'.$this->language.'` AS ';
    }
    $sql.='Bezeichnung FROM stelle WHERE ID='.$this->id;
    #echo $sql;
    $this->debug->write("<p>file:stelle.php class:stelle->getName - Abfragen des Namens der Stelle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs=mysql_fetch_array($query);
    $this->Bezeichnung=$rs['Bezeichnung'];
    return $rs['Bezeichnung'];
  }

	function readDefaultValues() {
		$sql = "
			SELECT
				*
			FROM
				stelle
			WHERE
				ID = " . $this->id . "
		";
		$this->debug->write('<p>file:stelle.php class:stelle->readDefaultValues - Abfragen der Default Parameter der Karte zur Stelle:<br>' . $sql, 4);
		$query = mysql_query($sql,$this->database->dbConn);
		if ($query == 0) { $this->debug->write('<br>Abbruch Zeile: ' . __LINE__, 4); return 0; }
		$rs = mysql_fetch_array($query);    
		$this->MaxGeorefExt = ms_newRectObj();
		$this->MaxGeorefExt->setextent($rs['minxmax'], $rs['minymax'], $rs['maxxmax'], $rs['maxymax']);
		$this->epsg_code = $rs['epsg_code'];
		$this->pgdbhost = ($rs['pgdbhost'] == 'PGSQL_PORT_5432_TCP_ADDR' ? getenv('PGSQL_PORT_5432_TCP_ADDR') : $rs['pgdbhost']);
		$this->pgdbname = $rs['pgdbname'];
		$this->pgdbuser = $rs['pgdbuser'];
		$this->pgdbpasswd = $rs['pgdbpasswd'];
		$this->protected = $rs['protected'];
		//---------- OWS Metadaten ----------//
		$this->ows_title = $rs['ows_title'];
		$this->ows_abstract = $rs['ows_abstract'];
		$this->wms_accessconstraints = $rs['wms_accessconstraints'];
		$this->ows_contactperson = $rs['ows_contactperson'];
		$this->ows_contactorganization = $rs['ows_contactorganization'];
		$this->ows_contactelectronicmailaddress = $rs['ows_contactemailaddress'];
		$this->ows_contactposition = $rs['ows_contactposition'];
		$this->ows_fees = $rs['ows_fees'];
		$this->ows_srs = $rs['ows_srs'];
		$this->check_client_ip = $rs['check_client_ip'];
		$this->checkPasswordAge = $rs['check_password_age'];
		$this->allowedPasswordAge = $rs['allowed_password_age'];
		$this->useLayerAliases = $rs['use_layer_aliases'];
		$this->selectable_layer_params = $rs['selectable_layer_params'];
		$this->hist_timestamp = $rs['hist_timestamp'];
		$this->default_user_id = $rs['default_user_id'];
	}
}

class rolle {

  var $user_id;
  var $stelle_id;
  var $debug;
  var $database;
  var $loglevel;
  static $hist_timestamp;
  static $layer_params;

	function rolle($user_id,$stelle_id,$database) {
		global $debug;
		$this->debug=$debug;
		$this->user_id=$user_id;
		$this->stelle_id=$stelle_id;
		$this->database=$database;
		#$this->layerset=$this->getLayer('');
		#$this->groupset=$this->getGroups('');
		$this->loglevel = 0;
	}

  function readSettings() {
		global $language;
    # Abfragen und Zuweisen der Einstellungen der Rolle
    $sql = "
			SELECT
				*
			FROM
				rolle
			WHERE
				user_id = " . $this->user_id . " AND
				stelle_id = " . $this->stelle_id . "
		";
    #echo $sql;
    $this->debug->write("<p>file:rolle.php class:rolle function:readSettings - Abfragen der Einstellungen der Rolle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) {
      $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4);
      return 0;
    }
		if(mysql_num_rows($query) > 0){
			$rs = mysql_fetch_assoc($query);
			$this->oGeorefExt=ms_newRectObj();
			$this->oGeorefExt->setextent($rs['minx'],$rs['miny'],$rs['maxx'],$rs['maxy']);
			$this->nImageWidth=$rs['nImageWidth'];
			$this->nImageHeight=$rs['nImageHeight'];			
			$this->mapsize=$this->nImageWidth.'x'.$this->nImageHeight;
			$this->auto_map_resize=$rs['auto_map_resize'];
			@$this->pixwidth=($rs['maxx']-$rs['minx'])/$rs['nImageWidth'];
			@$this->pixheight=($rs['maxy']-$rs['miny'])/$rs['nImageHeight'];
			$this->pixsize=($this->pixwidth+$this->pixheight)/2;
			$this->nZoomFactor=$rs['nZoomFactor'];
			$this->epsg_code=$rs['epsg_code'];
			$this->epsg_code2=$rs['epsg_code2'];
			$this->coordtype=$rs['coordtype'];
			$this->last_time_id=$rs['last_time_id'];
			$this->gui=$rs['gui'];
			$this->language=$rs['language'];
			$language = $this->language;
			$this->hideMenue=$rs['hidemenue'];
			$this->hideLegend=$rs['hidelegend'];
			$this->fontsize_gle=$rs['fontsize_gle'];
			$this->highlighting=$rs['highlighting'];
			$this->scrollposition=$rs['scrollposition'];
			$this->result_color=$rs['result_color'];
			$this->always_draw=$rs['always_draw'];
			$this->runningcoords=$rs['runningcoords'];
			$this->showmapfunctions=$rs['showmapfunctions'];
			$this->showlayeroptions=$rs['showlayeroptions'];
			$this->showrollenfilter=$rs['showrollenfilter'];
			$this->menue_buttons=$rs['menue_buttons'];
			$this->singlequery=$rs['singlequery'];
			$this->querymode=$rs['querymode'];
			$this->geom_edit_first=$rs['geom_edit_first'];		
			$this->overlayx=$rs['overlayx'];
			$this->overlayy=$rs['overlayy'];
			$this->instant_reload=$rs['instant_reload'];
			$this->menu_auto_close=$rs['menu_auto_close'];
			rolle::$layer_params = (array)json_decode('{' . $rs['layer_params'] . '}');
			$this->visually_impaired = $rs['visually_impaired'];
			$this->legendtype = $rs['legendtype'];
			$this->print_legend_separate = $rs['print_legend_separate'];
			if($rs['hist_timestamp'] != ''){
				$this->hist_timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $rs['hist_timestamp'])->format('d.m.Y H:i:s');			# der wird zur Anzeige des Timestamps benutzt
				rolle::$hist_timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $rs['hist_timestamp'])->format('Y-m-d\TH:i:s\Z');	# der hat die Form, wie der timestamp in der PG-DB steht und wird für die Abfragen benutzt
			}
			else
				rolle::$hist_timestamp = $this->hist_timestamp = '';
			$this->selectedButton=$rs['selectedButton'];
			$buttons = explode(',', $rs['buttons']);
			$this->back = in_array('back', $buttons);
			$this->forward = in_array('forward', $buttons);
			$this->zoomin = in_array('zoomin', $buttons);
			$this->zoomout = in_array('zoomout', $buttons);
			$this->zoomall = in_array('zoomall', $buttons);
			$this->recentre = in_array('recentre', $buttons);
			$this->jumpto = in_array('jumpto', $buttons);
			$this->coord_query = in_array('coord_query', $buttons);			
			$this->query = in_array('query', $buttons);
			$this->queryradius = in_array('queryradius', $buttons);
			$this->polyquery = in_array('polyquery', $buttons);
			$this->touchquery = in_array('touchquery', $buttons);
			$this->measure = in_array('measure', $buttons);
			$this->freepolygon = in_array('freepolygon', $buttons);
			$this->freetext = in_array('freetext', $buttons);
			$this->freearrow = in_array('freearrow', $buttons);
			$this->gps = in_array('gps', $buttons);
			return 1;
		}else return 0;
  }

  function getLayer($LayerName) {
		global $language;

    # Abfragen der Layer in der Rolle
		if($language != 'german') {
			$name_column = "
			CASE
				WHEN l.`Name_" . $language . "` != \"\" THEN l.`Name_" . $language . "`
				ELSE l.`Name`
			END AS Name";
		}
		else
			$name_column = "l.Name";

		if ($LayerName != '') {
			$layer_name_filter = " AND (l.Name LIKE '" . $LayerName . "'";
			if(is_numeric($LayerName))
				$layer_name_filter .= " OR l.Layer_ID = " . $LayerName;
			$layer_name_filter .= ")";
		}

		$sql = "
			SELECT " .
				$name_column . ",
				l.Layer_ID,
				alias, Datentyp, Gruppe, pfad, maintable, maintable_is_view, Data, tileindex, `schema`, document_path, document_url, CASE WHEN connectiontype = 6 THEN concat('host=', c.host, ' port=', c.port, ' dbname=', c.dbname, ' user=', c.user, ' password=', c.password) ELSE l.connection END as connection, printconnection,
				classitem, connectiontype, epsg_code, tolerance, toleranceunits, wms_name, wms_auth_username, wms_auth_password, wms_server_version, ows_srs,
				wfs_geom, selectiontype, querymap, processing, kurzbeschreibung, datenherr, metalink, status, trigger_function, ul.`queryable`, ul.`drawingorder`,
				ul.`minscale`, ul.`maxscale`,
				ul.`offsite`,
				coalesce(r2ul.transparency, ul.transparency, 100) as transparency,
				coalesce(r2ul.labelitem, l.labelitem) as labelitem,
				l.labelitem as original_labelitem,
				ul.`postlabelcache`,
				`Filter`,
				CASE r2ul.gle_view
					WHEN '0' THEN 'generic_layer_editor.php'
					ELSE ul.`template`
				END as template,
				`header`,
				`footer`,
				ul.`symbolscale`,
				ul.`logconsume`,
				ul.`requires`,
				ul.`privileg`,
				ul.`export_privileg`,
				`start_aktiv`,
				r2ul.showclasses,
				r2ul.rollenfilter,
				r2ul.geom_from_layer
			FROM
				used_layer AS ul,
				u_rolle2used_layer as r2ul,
				layer AS l
				LEFT JOIN connections as c ON l.connection_id = c.id
			WHERE
				l.Layer_ID=ul.Layer_ID AND
				r2ul.Stelle_ID=ul.Stelle_ID AND
				r2ul.Layer_ID=ul.Layer_ID AND
				ul.Stelle_ID= " . $this->stelle_id . " AND
				r2ul.User_ID= " . $this->user_id .
				$layer_name_filter . "
			ORDER BY
				ul.drawingorder desc
		";
#		echo $sql.'<br>';
    $this->debug->write("<p>file:rolle.php class:rolle->getLayer - Abfragen der Layer zur Rolle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__,4); return 0; }
		$i = 0;
		while ($rs=mysql_fetch_assoc($query)) {
			foreach (array('Name', 'alias', 'connection', 'classification') AS $key) {
				$rs[$key] = replace_params(
					$rs[$key],
					rolle::$layer_params,
					$this->User_ID,
					$this->Stelle_ID,
					rolle::$hist_timestamp,
					$this->rolle->language
				);
			}		
			$layer[$i]=$rs;
			$layer['layer_ids'][$rs['Layer_ID']] =& $layer[$i];
			$layer['layer_ids'][$layer[$i]['requires']]['required'] = $rs['Layer_ID'];
			$i++;
    }
    return $layer;
  }
	
	function getRollenLayer($LayerName, $typ = NULL) {
    $sql ="SELECT l.*, -l.id as Layer_ID, l.query as pfad, CASE WHEN Typ = 'import' THEN 1 ELSE 0 END as queryable FROM rollenlayer AS l";
    $sql.=' WHERE l.stelle_id = '.$this->stelle_id.' AND l.user_id = '.$this->user_id;
    if ($LayerName!='') {
      $sql.=' AND (l.Name LIKE "'.$LayerName.'" ';
      if(is_numeric($LayerName)){
        $sql.='OR l.id = "'.$LayerName.'")';
      }
      else{
        $sql.=')';
      }
    }
		if($typ != NULL){
			$sql .= " AND Typ = '".$typ."'";
		}
    #echo $sql.'<br>';
    $this->debug->write("<p>file:users.php class:rolle->getRollenLayer - Abfragen der Rollenlayer zur Rolle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__,4); return 0; }
		$layer = array();
    while ($rs=mysql_fetch_array($query)) {
      $layer[]=$rs;
    }
    return $layer;
  }	
	
}

class db_mapObj {

  var $debug;
  var $referenceMap;
  var $Layer;
  var $anzLayer;
  var $nurAufgeklappteLayer;
  var $Stelle_ID;
  var $User_ID;
  var $database;

	function db_mapObj($Stelle_ID, $User_ID, $database = NULL) {
		global $debug;
		global $GUI;
		$this->debug = $debug;
		$this->GUI = $GUI;
		$this->Stelle_ID = $Stelle_ID;
		$this->User_ID = $User_ID;
		$this->rolle = new rolle($User_ID, $Stelle_ID, $database);
		$this->database = $database;
	}

	function getlayerdatabase($layer_id, $host){
		if($layer_id < 0){	# Rollenlayer
			$sql ='SELECT `connection`, "'.CUSTOM_SHAPE_SCHEMA.'" as `schema` FROM rollenlayer WHERE -id = '.$layer_id.' AND connectiontype = 6';
		}
		else{
			$sql ="SELECT concat('host=', c.host, ' port=', c.port, ' dbname=', c.dbname, ' user=', c.user, ' password=', c.password) as `connection`, `schema` FROM layer as l, connections as c WHERE l.Layer_ID = ".$layer_id." AND l.connection_id = c.id AND l.connectiontype = 6";
		}
		$this->debug->write("<p>file:kvwmap class:db_mapObj->getlayerdatabase - Lesen des connection-Strings des Layers:<br>".$sql,4);
		$query=mysql_query($sql);
		if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
		$rs = mysql_fetch_array($query);
		$connectionstring = $rs[0];
#		$this->debug->write("<p>file:kvwmap class:db_mapObj->getlayerdatabase - Gefundener Connection String des Layers:<br>" . $connectionstring, 4);
		if($connectionstring != ''){
			$layerdb = new pgdatabase();
			if($rs[1] == ''){
				$rs[1] = 'public';
			}
			$layerdb->schema = $rs[1];
			$connection = explode(' ', trim($connectionstring));
			for($j = 0; $j < count($connection); $j++){
				if($connection[$j] != ''){
					$value = explode('=', $connection[$j]);
					if(strtolower($value[0]) == 'user'){
						$layerdb->user = $value[1];
					}
					if(strtolower($value[0]) == 'dbname'){
						$layerdb->dbName = $value[1];
					}
					if(strtolower($value[0]) == 'password'){
						$layerdb->passwd = $value[1];
					}
					if(strtolower($value[0]) == 'host'){
						$layerdb->host = $value[1];
					}
					if(strtolower($value[0]) == 'port'){
						$layerdb->port = $value[1];
					}
				}
			}
			if (!isset($layerdb->host)) {
				$layerdb->host = $host;
			}
			if (!$layerdb->open()) {
				echo 'Die Verbindung zur PostGIS-Datenbank konnte mit folgenden Daten nicht hergestellt werden:';
				echo '<br>Host: '.$layerdb->host;
				echo '<br>User: '.$layerdb->user;
				echo '<br>Datenbankname: '.$layerdb->dbName;
				exit;
			}
		}
		return $layerdb;
	}

  function getDataAttributes($database, $layer_id, $ifEmptyUseQuery = false){
    $data = $this->getData($layer_id);
    if($data != ''){
      $select = $this->getSelectFromData($data);
      if($database->schema != ''){
      	$select = str_replace($database->schema.'.', '', $select);
      }
      $ret = $database->getFieldsfromSelect($select);
			if ($ret[0]) {
				$this->GUI->add_message('error', $ret[1]);
			}
      return $ret[1];
    }
    elseif($ifEmptyUseQuery){
			$path = replace_params(
				$this->getPath($layer_id),
				$all_layer_params,
				$this->User_ID,
				$this->Stelle_ID,
				rolle::$hist_timestamp,
				$this->user->rolle->language
			);
			return $this->getPathAttributes($database, $path);
		}
		else{
      echo 'Das Data-Feld des Layers mit der Layer-ID '.$layer_id.' ist leer.';
      return NULL;
    }
  }

  function getData($layer_id){
  	if($layer_id < 0){	# Rollenlayer
  		$sql = "
				SELECT
					Data
				FROM
					rollenlayer
				WHERE
					-id = " . $layer_id . "
			";
  	}
  	else{
    	$sql = "
				SELECT
					Data
				FROM
					layer
				WHERE
					Layer_ID = " . $layer_id . "
			";
  	}
  	#echo $sql;
    $this->debug->write("<p>file:kvwmap class:db_mapObj->getData - Lesen des Data-Statements des Layers:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs = mysql_fetch_assoc($query);
    $data = replace_params(
			$rs['Data'],
			rolle::$layer_params,
			$this->User_ID,
			$this->Stelle_ID,
			rolle::$hist_timestamp,
			$this->rolle->language
		);
    return $data;
  }

  function getSelectFromData($data){
    if(strpos($data, '(') === false){
      $from = stristr($data, ' from ');
      $usingposition = strpos($from, 'using');
      if($usingposition > 0){
        $from = substr($from, 0, $usingposition);
      }
      $select = 'select * '.$from.' where 1=1';
    }
    else{
      $select = stristr($data,'(');
      $select = trim($select, '(');
      $select = substr($select, 0, strrpos($select, ')'));
      if(strpos($select, 'select') != false){
        $select = stristr($select, 'select');
      }
    }
    return $select;
  }

  function read_disabled_classes(){
  	#Anne
    $sql_classes = 'SELECT class_id, status FROM u_rolle2used_class WHERE user_id='.$this->User_ID.' AND stelle_id='.$this->Stelle_ID.';';
    $query_classes=mysql_query($sql_classes);
    while($row = mysql_fetch_assoc($query_classes)){
  		$classarray['class_id'][] = $row['class_id'];
			$classarray['status'][$row['class_id']] = $row['status'];
		}
		return $classarray;
  }

	function read_Classes($Layer_ID, $disabled_classes = NULL, $all_languages = false, $classification = '') {
		global $language;

		$sql = "
			SELECT " .
				((!$all_languages AND $language != 'german') ? "
					CASE
						WHEN `Name_" . $language . "`IS NOT NULL THEN `Name_" . $language . "`
						ELSE `Name`
					END" : "
					`Name`"
				) . " AS Name,
				`Name_low-german`,
				`Name_english`,
				`Name_polish`,
				`Name_vietnamese`,
				`Class_ID`,
				`Layer_ID`,
				`Expression`,
				`classification`,
				`legendgraphic`,
				`legendimagewidth`,
				`legendimageheight`,
				`drawingorder`,
				`legendorder`,
				`text`
			FROM
				`classes`
			WHERE
				`Layer_ID` = " . $Layer_ID .
				(
					(!empty($classification)) ? " AND
						(
							classification IS NULL OR classification IN ('', '" . $classification . "')
						)
					" : ""
				) . "
			ORDER BY
				classification,
				drawingorder,
				Class_ID
		";
		#echo $sql.'<br>';
		$this->debug->write("<p>file:kvwmap class:db_mapObj->read_Class - Lesen der Classen eines Layers:<br>" . $sql, 4);
		$query = mysql_query($sql);
		if ($query == 0) { echo "<br>Abbruch in " . $PHP_SELF . " Zeile: " . __LINE__; return 0; }
		$index = 0;
		while ($rs = mysql_fetch_assoc($query)) {
			$rs['Style'] = $this->read_Styles($rs['Class_ID']);
			$rs['Label'] = $this->read_Label($rs['Class_ID']);
			$rs['index'] = $index;
			#Anne
			if($disabled_classes){
				if($disabled_classes['status'][$rs['Class_ID']] == 2) {
					$rs['Status'] = 1;
					for($i = 0; $i < count($rs['Style']); $i++) {
						if ($rs['Style'][$i]['color'] != '' AND $rs['Style'][$i]['color'] != '-1 -1 -1') {
							$rs['Style'][$i]['outlinecolor'] = $rs['Style'][$i]['color'];
							$rs['Style'][$i]['color'] = '-1 -1 -1';
							if($rs['Style'][$i]['width'] == '') $rs['Style'][$i]['width'] = 3;
							if($rs['Style'][$i]['minwidth'] == '') $rs['Style'][$i]['minwidth'] = 2;
							if($rs['Style'][$i]['maxwidth'] == '') $rs['Style'][$i]['maxwidth'] = 4;
							$rs['Style'][$i]['symbolname'] = '';
						}
					}
				}
				elseif ($disabled_classes['status'][$rs['Class_ID']] == '0') {
					$rs['Status'] = 0;
				}
				else $rs['Status'] = 1;
			}
			else $rs['Status'] = 1;

			$Classes[] = $rs;
			$index++;
		}
		return $Classes;
	}

  function read_Styles($Class_ID) {
    $sql ='SELECT * FROM styles AS s,u_styles2classes AS s2c';
    $sql.=' WHERE s.Style_ID=s2c.style_id AND s2c.class_id='.$Class_ID;
    $sql.=' ORDER BY drawingorder';
    $this->debug->write("<p>file:kvwmap class:db_mapObj->read_Styles - Lesen der Styledaten:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__; return 0; }
    while($rs=mysql_fetch_assoc($query)) {
      $Styles[]=$rs;
    }
    return $Styles;
  }

  function read_Label($Class_ID) {
    $sql ='SELECT * FROM labels AS l,u_labels2classes AS l2c';
    $sql.=' WHERE l.Label_ID=l2c.label_id AND l2c.class_id='.$Class_ID;
    $this->debug->write("<p>file:kvwmap class:db_mapObj->read_Label - Lesen der Labels zur Classe eines Layers:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__; return 0; }
    while ($rs=mysql_fetch_assoc($query)) {
      $Labels[]=$rs;
    }
    return $Labels;
  }
}

class pgdatabase {

  var $ist_Fortfuehrung;
  var $debug;
  var $loglevel;
  var $defaultloglevel;
  var $logfile;
  var $defaultlogfile;
  var $commentsign;
  var $blocktransaction;

	function pgdatabase() {
	  global $debug;
    $this->debug=$debug;
    $this->loglevel=LOG_LEVEL;
 		$this->defaultloglevel=LOG_LEVEL;
 		global $log_postgres;
    $this->logfile=$log_postgres;
 		$this->defaultlogfile=$log_postgres;
    $this->ist_Fortfuehrung=1;
    $this->type='postgresql';
    $this->commentsign='--';
    # Wenn dieser Parameter auf 1 gesetzt ist werden alle Anweisungen
    # START TRANSACTION, ROLLBACK und COMMIT unterdrï¿½ckt, so daï¿½ alle anderen SQL
    # Anweisungen nicht in Transactionsblï¿½cken ablaufen.
    # Kann zur Steigerung der Geschwindigkeit von groï¿½en Datenbestï¿½nden verwendet werden
    # Vorsicht: Wenn Fehler beim Einlesen passieren, ist der Datenbestand inkonsistent
    # und der Einlesevorgang muss wiederholt werden bis er fehlerfrei durchgelaufen ist.
    # Dazu Fehlerausschriften bearchten.
    $this->blocktransaction=0;
		$this->spatial_ref_code = EPSGCODE_ALKIS . ", " . EARTH_RADIUS;
  }

  function open() {
  	if($this->port == '') $this->port = 5432;
    #$this->debug->write("<br>Datenbankverbindung öffnen: Datenbank: ".$this->dbName." User: ".$this->user,4);
		$this->connect_string = '' .
      'dbname='. $this->dbName .
      ' port=' . $this->port .
      ' user=' . $this->user .
      ' password=' . $this->passwd;
		if($this->host != 'localhost' AND $this->host != '127.0.0.1')
      $this->connect_string .= ' host=' . $this->host; // das beschleunigt den Connect extrem
    $this->dbConn = pg_connect($this->connect_string);
    $this->debug->write("Datenbank mit Connection_ID: ".$this->dbConn." geöffnet.",4);
    # $this->version = pg_version($this->dbConn); geht erst mit PHP 5
    $this->version = POSTGRESVERSION;
    return $this->dbConn;
  }
	
	function getEnumElements($name, $schema){
		$sql = "SELECT array_to_string(array_agg(''''||e.enumlabel||''''), ',') as enum_string ";
		$sql.= "FROM pg_enum e ";
		$sql.= "JOIN pg_type t ON e.enumtypid = t.oid ";
		$sql.= "JOIN pg_namespace ns ON (t.typnamespace = ns.oid) ";
		$sql.= "WHERE t.typname = '".$name."' ";
		$sql.= "AND ns.nspname = '".$schema."'";
		$ret1 = $this->execSQL($sql, 4, 0);
		if($ret1[0]==0){
			$result = pg_fetch_assoc($ret1[1]);
		}
		return $result['enum_string'];
	}
	
	function writeCustomType($typname, $schema){		
		$datatype_id = $this->getDatatypeId($typname, $schema, $this->dbName, $this->host, $this->port);
		$this->writeDatatypeAttributes($datatype_id, $typname, $schema);
		return $datatype_id;
	}

	function getDatatypeId($typname, $schema, $dbname, $host, $port){
		$sql = "SELECT id FROM datatypes WHERE ";
		$sql.= "name = '".$typname."' AND `schema` = '".$schema."' AND dbname = '".$dbname."' AND host = '".$host."' AND port = ".$port;
		$query=mysql_query($sql);
		if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__."<br>wegen: ".$sql."<p>".INFO1; return 0; }
		$rs=mysql_fetch_assoc($query);
		if($rs == NULL){
			$sql = "INSERT INTO datatypes (name, `schema`, dbname, host, port) VALUES ('".$typname."', '".$schema."', '".$dbname."', '".$host."', ".$port.")";
			$query=mysql_query($sql);
			$datatype_id = mysql_insert_id();
		}
		else{	
			$datatype_id = $rs['id'];
		}
		return $datatype_id;
	}
	
	function writeDatatypeAttributes($datatype_id, $typname, $schema){
		$attr_info = $this->get_attribute_information($schema, $typname);
		for($i = 0; $i < count($attr_info); $i++){
			$fields[$i]['real_name'] = $attr_info[$i]['name'];
			$fields[$i]['name'] = $attr_info[$i]['name'];
			$fieldtype = $attr_info[$i]['type_name'];
			$fields[$i]['nullable'] = $attr_info[$i]['nullable']; 
			$fields[$i]['length'] = $attr_info[$i]['length'];
			$fields[$i]['decimal_length'] = $attr_info[$i]['decimal_length'];
			$fields[$i]['default'] = $attr_info[$i]['default'];					
			if($attr_info[$i]['is_array'] == 't')$prefix = '_'; else $prefix = '';
			if($attr_info[$i]['type_type'] == 'c'){		# custom datatype
				$sub_datatype_id = $this->writeCustomType($attr_info[$i]['type'], $attr_info[$i]['type_schema']);
				$fieldtype = $prefix.$sub_datatype_id; 
			}
			$constraintstring = '';
			if($attr_info[$i]['type_type'] == 'e'){		# enum
				$fieldtype = $prefix.'text';
				$constraintstring = $this->getEnumElements($attr_info[$i]['type'], $attr_info[$i]['type_schema']);
			}
			$fields[$i]['constraints'] = $constraintstring;
			$fields[$i]['type'] = $fieldtype;
			if($fields[$i]['nullable'] == '')$fields[$i]['nullable'] = 'NULL';
			if($fields[$i]['length'] == '')$fields[$i]['length'] = 'NULL';
			if($fields[$i]['decimal_length'] == '')$fields[$i]['decimal_length'] = 'NULL';
			$sql = "INSERT INTO datatype_attributes SET
								datatype_id = ".$datatype_id.", 
								name = '".$fields[$i]['name']."', 
								real_name = '".$fields[$i]['real_name']."', 
								type = '".$fields[$i]['type']."', 
								constraints = '".addslashes($fields[$i]['constraints'])."', 
								nullable = ".$fields[$i]['nullable'].", 
								length = ".$fields[$i]['length'].", 
								decimal_length = ".$fields[$i]['decimal_length'].", 
								`default` = '".addslashes($fields[$i]['default'])."', 
								`order` = ".$i." 
							ON DUPLICATE KEY UPDATE
								real_name = '".$fields[$i]['real_name']."', 
								type = '".$fields[$i]['type']."', 
								constraints = '".addslashes($fields[$i]['constraints'])."', 
								nullable = ".$fields[$i]['nullable'].", 
								length = ".$fields[$i]['length'].", 
								decimal_length = ".$fields[$i]['decimal_length'].", 
								`default` = '".addslashes($fields[$i]['default'])."', 
								`order` = ".$i;
			$query=mysql_query($sql);
			if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__."<br>wegen: ".$sql."<p>".INFO1; return 0; }
		}
	}	

	function pg_field_schema($table_oid){
		if($table_oid != ''){
			$sql = "select nspname as schema from pg_class c, pg_namespace ns
						where c.relnamespace = ns.oid 
						and c.oid = ".$table_oid;
			$ret = $this->execSQL($sql, 4, 0);
			if($ret[0]==0)$ret = pg_fetch_assoc($ret[1]);
			return $ret['schema'];
		}
	}
	
	function getTableAliasNames($plans){
		$table_aliases = array();
		foreach($plans as $plan){
			if($plan['Parent Relationship'] != 'SubPlan'){
				if($plan['Relation Name'] != ''){
					$table_aliases[$plan['Relation Name']] = $plan['Alias'];
				}
				if($plan['Plans'] != NULL)$table_aliases = $table_aliases + $this->getTableAliasNames($plan['Plans']);
			}
		}
		return $table_aliases;
	}	

	function getFieldsfromSelect($select, $assoc = false) {
		$err_msgs = array();
		$error_reporting = error_reporting();
		error_reporting(E_NOTICE);
		ini_set("pgsql.log_notice", '1');
		ini_set("pgsql.ignore_notice", '0');
		ini_set("display_errors", '0');
		$error_list = array();
		$myErrorHandler = function ($error_level, $error_message, $error_file, $error_line, $error_context) use (&$error_list) {
			if(strpos($error_message, "\n      :resno") !== false){
				$error_list[] = $error_message;
			}
			return false;
		};
		set_error_handler($myErrorHandler);
		$sql = 'SET client_min_messages=\'log\';SET debug_print_parse=true;'.$select." LIMIT 0;";		# den Queryplan als Notice mitabfragen um an Infos zur Query zu kommen
		$ret = $this->execSQL($sql, 4, 0);
		error_reporting($error_reporting);		
		if ($ret['success']) {
			$query_plan = $error_list[0];
			$table_alias_names = $this->get_table_alias_names($query_plan);
			$field_plan_info = explode("\n      :resno", $query_plan);
			
			for ($i = 0; $i < pg_num_fields($ret[1]); $i++) {				
				# Attributname
				$fields[$i]['name'] = $fieldname = pg_field_name($ret[1], $i);
				
				# Spaltennummer in der Tabelle
				$col_num = get_first_word_after($field_plan_info[$i+1], ':resorigcol');				
				
				# Tabellen-oid des Attributs
				$table_oid = pg_field_table($ret[1], $i, true);			

				# wenn das Attribut eine Tabellenspalte ist -> weitere Attributeigenschaften holen
				if ($table_oid > 0){					
					# Tabellenname des Attributs
					$fields[$i]['table_name'] = $tablename = pg_field_table($ret[1], $i);
					if ($tablename != NULL) {
						$all_table_names[] = $tablename;
					}
										
					# Tabellenaliasname des Attributs
					$fields[$i]['table_alias_name'] = $table_alias_names[$table_oid];

					# Schemaname der Tabelle des Attributs
					$schemaname = $this->pg_field_schema($table_oid);		# der Schemaname kann hiermit aus der Query ermittelt werden; evtl. in layer_attributes speichern?	
					
					$constraintstring = '';
					$attr_info = $this->get_attribute_information($schemaname, $tablename, $col_num);
					if($attr_info[0]['relkind'] == 'v'){		# wenn View, dann Attributinformationen aus View-Definition holen
						if($view_defintion_attributes[$tablename] == NULL) {
							$ret2 = $this->getFieldsfromSelect(substr($attr_info[0]['view_definition'], 0, -1), true);
							if ($ret2['success']) {
								$view_defintion_attributes[$tablename] = $ret2[1];
							}
							else {
								# Füge Fehlermeldung hinzu und setze leeres Array
								$err_msgs[] = $ret2[1];
								$view_defintion_attributes[$tablename] = array();
							}
						}
						if ($view_defintion_attributes[$tablename][$fieldname]['nullable'] != NULL)$attr_info[0]['nullable'] = $view_defintion_attributes[$tablename][$fieldname]['nullable'];
						if ($view_defintion_attributes[$tablename][$fieldname]['default'] != NULL)$attr_info[0]['default'] = $view_defintion_attributes[$tablename][$fieldname]['default'];
					}
					# realer Name der Spalte in der Tabelle
					$fields[$i]['real_name'] = $attr_info[0]['name'];
					$fieldtype = $attr_info[0]['type_name'];
					$fields[$i]['nullable'] = $attr_info[0]['nullable']; 
					$fields[$i]['length'] = $attr_info[0]['length'];
					$fields[$i]['decimal_length'] = $attr_info[0]['decimal_length'];
					$fields[$i]['default'] = $attr_info[0]['default'];
					if($attr_info[0]['is_array'] == 't')$prefix = '_'; else $prefix = '';
					if($attr_info[0]['type_type'] == 'c'){		# custom datatype
						$datatype_id = $this->writeCustomType($attr_info[0]['type'], $attr_info[0]['type_schema']);
						$fieldtype = $prefix.$datatype_id; 
					}
					if($attr_info[0]['type_type'] == 'e'){		# enum
						$fieldtype = $prefix.'text';
						$constraintstring = $this->getEnumElements($attr_info[0]['type'], $attr_info[0]['type_schema']);
					}
					if($attr_info[0]['indisunique'] == 't')$constraintstring = 'UNIQUE';
					if($attr_info[0]['indisprimary'] == 't')$constraintstring = 'PRIMARY KEY';
					$constraints = $this->pg_table_constraints($tablename);		# todo
					if($fieldtype != 'geometry'){
						# testen ob es für ein Attribut ein constraint gibt, das wie enum wirkt
						for($j = 0; $j < count($constraints); $j++){
							if(strpos($constraints[$j], '('.$fieldname.')')){
								$options = explode("'", $constraints[$j]);
								for($k = 0; $k < count($options); $k++){
									if($k%2 == 1){
										if($k > 1){
											$constraintstring.= ",";
										}
										$constraintstring.= "'".$options[$k]."'";
									}
								}
							}
						}
					}
					$fields[$i]['constraints'] = $constraintstring;
				}
				else { # Attribut ist keine Tabellenspalte -> nicht speicherbar
					$fieldtype = 'not_saveable';
				}
				$fields[$i]['type'] = $fieldtype;

				# Geometrietyp
				if ($fieldtype == 'geometry') {
					$fields[$i]['geomtype'] = $this->get_geom_type($schemaname, $fields[$i]['real_name'], $tablename);
					$fields['the_geom'] = $fieldname;
					$fields['the_geom_id'] = $i;
				}
				if ($assoc) {
					$fields_assoc[$fieldname] = $fields[$i];
				}
			}
			$ret[1] = ($assoc ? $fields_assoc : $fields);
		}
		else {
			# Füge Fehlermeldung hinzu
			$err_msgs[] = $ret[1];
		}

		if (count($err_msgs) > 0) {
			# Wenn Fehler auftraten liefer nur die Fehler zurück
			$ret[0] = 1;
			$ret[1] = implode('<br>', $err_msgs);
		}
		return $ret;
	}

	function get_attribute_information($schema, $table, $col_num = NULL) {
		if($col_num != NULL)$and_column = " a.attnum = ".$col_num." ";
		else $and_column = " a.attnum > 0 ";
		$attributes = array();
		$sql = "
			SELECT
				ns.nspname as schema,
				c.relname AS table_name,
				c.relkind,
				a.attname AS name,
				NOT a.attnotnull AS nullable,
				a.attnum AS ordinal_position,
				ad.adsrc as default,
				t.typname AS type_name,
				tns.nspname as type_schema,
				CASE WHEN t.typarray = 0 THEN eat.typname ELSE t.typname END AS type,
				t.oid AS attribute_type_oid,
				coalesce(eat.typtype, t.typtype) as type_type,
				case when t.typarray = 0 THEN true ELSE false END AS is_array,
				CASE WHEN t.typname = 'varchar' AND a.atttypmod > 0 THEN a.atttypmod - 4 ELSE NULL END as character_maximum_length,
				CASE a.atttypid
				 WHEN 21 /*int2*/ THEN 16
				 WHEN 23 /*int4*/ THEN 32
				 WHEN 20 /*int8*/ THEN 64
				 WHEN 1700 /*numeric*/ THEN
				      CASE WHEN atttypmod = -1
					   THEN null
					   ELSE ((atttypmod - 4) >> 16) & 65535
					   END
				 WHEN 700 /*float4*/ THEN 24 /*FLT_MANT_DIG*/
				 WHEN 701 /*float8*/ THEN 53 /*DBL_MANT_DIG*/
				 ELSE null
				END   AS numeric_precision,
				CASE 
				    WHEN atttypid IN (21, 23, 20) THEN 0
				    WHEN atttypid IN (1700) THEN
					CASE 
					    WHEN atttypmod = -1 THEN null
					    ELSE (atttypmod - 4) & 65535
					END
				       ELSE null
				  END AS decimal_length,
				i.indisunique,
				i.indisprimary,
				v.definition as view_definition
			FROM
				pg_catalog.pg_class c JOIN
				pg_catalog.pg_attribute a ON (c.oid = a.attrelid) JOIN
				pg_catalog.pg_namespace ns ON (c.relnamespace = ns.oid) JOIN
				pg_catalog.pg_type t ON (a.atttypid = t.oid) LEFT JOIN
				pg_catalog.pg_namespace tns ON (t.typnamespace = tns.oid) LEFT JOIN
				pg_catalog.pg_type eat ON (t.typelem = eat.oid) LEFT JOIN 
				pg_index i ON i.indrelid = c.oid AND a.attnum = ANY(i.indkey)	LEFT JOIN 
				pg_catalog.pg_attrdef ad ON a.attrelid = ad.adrelid AND ad.adnum = a.attnum LEFT JOIN 
				pg_catalog.pg_views v ON v.viewname = c.relname AND v.schemaname = ns.nspname
			WHERE
				ns.nspname IN ('" .  implode("','", array_map(function($schema) { return trim($schema); }, explode(',', $schema)))  .  "') AND
				c.relname = '".$table."' AND
				".$and_column."
			ORDER BY a.attnum, indisunique desc, indisprimary desc
		";
		#echo '<br><br>' . $sql;
		$ret = $this->execSQL($sql, 4, 0);
		if($ret[0]==0){
			while($attr_info = pg_fetch_assoc($ret[1])){
				if($attr_info['nullable'] == 'f' AND substr($attr_info['default'], 0, 7) != 'nextval'){$attr_info['nullable'] = '0';}else{$attr_info['nullable'] = '1';}
        if($attr_info['numeric_precision'] != '')$attr_info['length'] = $attr_info['numeric_precision'];
        else $attr_info['length'] = $attr_info['character_maximum_length'];
	      if($attr_info['decimal_length'] == ''){$attr_info['decimal_length'] = 'NULL';}	      
	      if($attr_info['default'] != '' AND substr($attr_info['default'], 0, 7) != 'nextval')$attr_info['default'] = 'SELECT '.$attr_info['default'];
	  		else $attr_info['default'] = '';
				$attributes[] = $attr_info;
			}
		}
		return $attributes;
	}
	
	function get_table_alias_names($query_plan){
		$table_info = explode(":eref \n         {ALIAS \n         ", $query_plan);
		for($i = 1; $i < count($table_info); $i++){
			$table_alias = get_first_word_after($table_info[$i], ':aliasname');
			$table_oid = get_first_word_after($table_info[$i], ':relid');
			$table_alias_names[$table_oid] = $table_alias;
		}
		return $table_alias_names;
	}	

  function eliminate_star($query, $offset){
  	if(substr_count(strtolower($query), ' from ') > 1){
  		$whereposition = strpos($query, ' WHERE ');
  		$withoutwhere = substr($query, 0, $whereposition);
  		$fromposition = strpos($withoutwhere, ' FROM ');
  	}
  	else{
  		$whereposition = strpos(strtolower($query), ' where ');
  		if($whereposition){
  			$withoutwhere = substr($query, 0, $whereposition);
  		}
  		else{
  			$withoutwhere = $query;
  		}
  		$fromposition = strpos(strtolower($withoutwhere), ' from ');
  	}
    $select = substr($query, $offset, $fromposition-$offset);
    $from = substr($query, $fromposition);
    $column = explode(',', $select);
    $column = get_select_parts($select);
    for($i = 0; $i < count($column); $i++){
      if(strpos(trim($column[$i]), '*') === 0 OR strpos($column[$i], '.*') !== false){
        $sql .= "SELECT ".$column[$i]." ".$from." LIMIT 0";
        $ret = $this->execSQL($sql, 4, 0);
        if($ret[0]==0){
        	$tablename = str_replace('*', '', trim($column[$i]));
          $columns = $tablename.pg_field_name($ret[1], 0);
          for($j = 1; $j < pg_num_fields($ret[1]); $j++){
            $columns .= ', '.$tablename.pg_field_name($ret[1], $j);
          }
          $query = str_replace(trim($column[$i]), $columns, $query);
        }
      }
    }
    return $query;
  }

	function execSQL($sql, $debuglevel, $loglevel, $suppress_error_msg = false) {
		$ret = array(); // Array with results to return

		switch ($this->loglevel) {
			case 0 : {
				$logsql = 0;
			} break;
			case 1 : {
				$logsql = 1;
			} break;
			case 2 : {
				$logsql = $loglevel;
			} break;
		}
		# SQL-Statement wird nur ausgeführt, wenn DBWRITE gesetzt oder
		# wenn keine INSERT, UPDATE und DELETE Anweisungen in $sql stehen.
		# (lesend immer, aber schreibend nur mit DBWRITE=1)
		if (DBWRITE OR (!stristr($sql,'INSERT') AND !stristr($sql,'UPDATE') AND !stristr($sql,'DELETE'))) {
			#echo "<br>SQL in execSQL: " . $sql;
			if (stristr($sql, 'SELECT')) {
				$sql = "SET datestyle TO 'German';" . $sql;
			};
			if ($this->schema != ''){
				$sql = "SET search_path = " . $this->schema . ", public;" . $sql;
			}
			$query = @pg_query($this->dbConn, $sql);
			//$query=0;
			if ($query == 0) {
				$ret[0] = 1;
				$ret['success'] = false;
				$errormessage = pg_last_error($this->dbConn);
				#header('error: true');		// damit ajax-Requests das auch mitkriegen
				$ret[1] = "Fehler bei SQL Anweisung:<br><br>\n\n" . $sql . "\n\n<br><br>" . $errormessage;
				$ret['msg'] = $ret[1];
				$ret['type'] = 'error';
				if (!$suppress_error_msg) {
					echo "<br><b>" . $ret[1] . "</b>";
				}
				$this->debug->write("<br><b>" . $ret[1] . "</b>", $debuglevel);
				if ($logsql) {
					$this->logfile->write($this->commentsign . " " . $ret[1]);
				}
			}
			else {
				# Abfrage wurde erfolgreich ausgeführt
				$ret[0] = 0;
				$ret['success'] = true;
				$ret[1] = $query;
				$ret['query'] = $ret[1]; 
				$this->debug->write("<br>" . $sql, $debuglevel);
				# 2006-07-04 pk $logfile ersetzt durch $this->logfile
				if ($logsql) {
					$this->logfile->write($sql . ';');
				}
			}
			$ret[2] = $sql;
		}
		else {
			# Es werden keine SQL-Kommandos ausgeführt
			# Die Funktion liefert ret[0]=0, und zeigt damit an, daß kein Datenbankfehler aufgetreten ist,
			$ret[0] = 0;
			$ret['success'] = true;
			# jedoch hat $ret[1] keine query_ID sondern auch den Wert 0
			$ret[1] = 0;
			# Wenn $this->loglevel != 0 wird die sql-Anweisung in die logdatei geschrieben
			# zusätzlich immer in die debugdatei
			# 2006-07-04 pk $logfile ersetzt durch $this->logfile
			if ($logsql) {
				$this->logfile->write($sql . ';');
			}
			$this->debug->write("<br>" . $sql, $debuglevel);
		}
		return $ret;
	}

  function check_real_attribute_name($fieldstring, $fieldname){
	    # testen ob Attributname durch 'as' umbenannt wurde
	    if(strpos(strtolower($fieldstring), ' as '.$fieldname)){
	      $fieldstring = trim($fieldstring);
	      $explosion = explode(' ', $fieldstring);
	      $klammerstartpos = strrpos($fieldstring, '(');
	      if($klammerstartpos !== false){										# eine Funktion wurde auf das Attribut angewendet
	        $klammerendpos = strpos($fieldstring, ')');
	        if($klammerendpos){
						$klammer_inhalt = substr($explosion[0], $klammerstartpos+1, $klammerendpos-$klammerstartpos-1);
						if(strpos($klammer_inhalt, "'") === false)$name_pair['real_name'] = $klammer_inhalt;
	        	$name_pair['name'] = $explosion[count($explosion)-1];
	        	$name_pair['no_real_attribute'] = true;
	        }
	      }
	      elseif(strpos(strtolower($fieldstring), '||') OR strpos(strtolower($fieldstring), '+')){		# irgendwas zusammengesetztes mit || oder +
	      	$explosion2 = explode('||', $fieldstring);
	      	for($i = 0; $i < count($explosion2); $i++){
	      		if(strpos($explosion2[$i], "'") === false){
	      			$realname = explode('.', $explosion2[$i]);
	      			$name_pair['real_name'] = $realname[count($realname)-1];
	          	$name_pair['name'] = $explosion[count($explosion)-1];
	          	$name_pair['no_real_attribute'] = true;
	          	break;
	      		}
	      	}
	      }
	      else{ # 'irgendein String' as ...
	        $fieldname = explode('.', $explosion[0]);
					if(strtolower($explosion[0]) == 'case' OR strpos($fieldname[count($fieldname)-1], "'") !== false){
	          $name_pair['no_real_attribute'] = true;
	        }
	        else{		# tabellenname.attributname
	          $name_pair['real_name'] = $fieldname[count($fieldname)-1];
	          $name_pair['name'] = $explosion[count($explosion)-1];
	        }
	      }
	      return $name_pair;
	    }
	    else{
	      return NULL;
	    }
  }

  function get_table_alias($tablename, $fromposition, $withoutwhere){
    $tablealias = $tablename;
    $from = substr($withoutwhere, $fromposition);
    $tablestring = substr($from, 5);
    $tables = explode(',', trim($tablestring));
    $i = 0;
    $found = false;
    while($found == false AND $i < count($tables)){
      $tableexplosion = explode(' ', trim($tables[$i]));
      if(count($tableexplosion) > 1){
	      for($j = 0; $j < count($tableexplosion); $j++){
					if($found)return $tablealias;
	      	if($tablename == $tableexplosion[$j]){
	      		if(strtolower($tableexplosion[$j+1]) == 'as'){			# Umbenennung mit AS
	      			$found = true;
	        		$tablealias = $tableexplosion[$j+2];
	      		}
	      		elseif(strtolower($tableexplosion[$j+1]) != 'on' AND strtolower($tableexplosion[$j+1]) != 'left'){	# Umbenennung ohne AS, wie z.B. beim LEFT JOIN
	      			$found = true;
	        		$tablealias = $tableexplosion[$j+1];
	      		}
	      	}
	      }
      }
      $i++;
    }
    return $tablealias;
  }

  function pg_table_constraints($table){
  	if($table != ''){
	    $sql = "SELECT consrc FROM pg_constraint, pg_class WHERE contype = 'check'";
	    $sql.= " AND pg_class.oid = pg_constraint.conrelid AND pg_class.relname = '".$table."'";
	    $ret = $this->execSQL($sql, 4, 0);
	    if($ret[0]==0){
	      while($row = pg_fetch_assoc($ret[1])){
	        $constraints[] = $row['consrc'];
	      }
	    }
	    return $constraints;
  	}
  }

	function get_geom_type($schema, $geomcolumn, $tablename){
		if ($schema == '') {
			$schema = 'public';
		}
		$schema = str_replace(',', "','", $schema);
		if ($geomcolumn != '' AND $tablename != '') {
			#-- search_path ist zwar gesetzt, aber nur auf custom_shapes, daher ist das Schema der Tabelle erforderlich
			$sql = "
				SELECT coalesce(
					(select geometrytype(" . $geomcolumn . ") FROM " . $schema . "." . $tablename . " limit 1)
					,  
					(select type from geometry_columns WHERE 
					 f_table_schema IN ('" . $schema . "') and 
					 f_table_name = '" . $tablename . "' AND 
					 f_geometry_column = '" . $geomcolumn . "')
				) as type
			";
			$ret1 = $this->execSQL($sql, 4, 0);
			if($ret1[0] == 0) {
				$result = pg_fetch_assoc($ret1[1]);
				$geom_type = $result['type'];
			}
			else {
				$geom_type = 'GEOMETRY';
			}
		}
		else{
			$geom_type = NULL;
		}
		return $geom_type;
	}
}
?>
