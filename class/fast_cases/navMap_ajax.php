<?

function in_subnet($ip,$net) {
  	$ipparts=explode('.',$ip);
  	$netparts=explode('.',$net);
  
  	# Direkter Vergleich
  	if ($ip==$net) {
  		return 1;
  	}
  
    # Test auf C-Netz
  	if (trim($netparts[3],'0')=='' OR $netparts[3]=='*') {
  		# C-Netzvergleich
  	  if ($ipparts[0].'.'.$ipparts[1].'.'.$ipparts[2]==$netparts[0].'.'.$netparts[1].'.'.$netparts[2]) {
  	  	return 1;
  	  }
  	}
  
    # Test auf B-Netz
  	if ((trim($netparts[3],'0')=='' OR $netparts[3]=='*') AND (trim($netparts[2],'0')=='' OR $netparts[2]=='*')) {
  		# B-Netzvergleich
  	  if ($ipparts[0].'.'.$ipparts[1]==$netparts[0].'.'.$netparts[1]) {
  	  	return 1;
  	  }
  	}
  
    # Test auf A-Netz
  	if ((trim($netparts[3],'0')=='' OR $netparts[3]=='*') AND (trim($netparts[2],'0')=='' OR $netparts[2]=='*') AND (trim($netparts[1],'0')=='' OR $netparts[1]=='*')) {
  		# A-Netzvergleich
  	  if ($ipparts[0]==$netparts[0]) {
  	  	return 1;
  	  }
  	}
  	return 0;
}

function checkPasswordAge($passwordSettingTime,$allowedPassordAgeMonth) {
  $passwordSettingUnixTime=strtotime($passwordSettingTime); # Unix Zeit in Sekunden an dem das Passwort gesetzt wurde
  $allowedPasswordAgeDays=round($allowedPassordAgeMonth*30.5); # Zeitintervall, wie alt das Password sein darf in Tagen
  $passwordAgeDays=round((time()-$passwordSettingUnixTime)/60/60/24); # Zeitinterval zwischen setzen des Passwortes und aktueller Zeit in Tagen
  $allowedPasswordAgeRemainDays=$allowedPasswordAgeDays-$passwordAgeDays; # Zeitinterval wie lange das Passwort noch gilt in Tagen
	return $allowedPasswordAgeRemainDays; // Passwort ist abgelaufen wenn Wert < 1  
}

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

function umlaute_umwandeln($name){
  $name = str_replace('ä', 'ae', $name);
  $name = str_replace('ü', 'ue', $name);
  $name = str_replace('ö', 'oe', $name);
  $name = str_replace('Ä', 'Ae', $name);  
  $name = str_replace('Ü', 'Ue', $name);
  $name = str_replace('Ö', 'Oe', $name);
  $name = str_replace('a?', 'ae', $name);
  $name = str_replace('u?', 'ue', $name);
  $name = str_replace('o?', 'oe', $name);
  $name = str_replace('A?', 'ae', $name);
  $name = str_replace('U?', 'ue', $name);
  $name = str_replace('O?', 'oe', $name);
  $name = str_replace('ß', 'ss', $name);
  $name = str_replace('.', '', $name);
  $name = str_replace(':', '', $name);
  $name = str_replace('/', '-', $name);
  $name = str_replace(' ', '', $name);
  $name = str_replace('-', '_', $name);
  $name = str_replace('?', '_', $name);
	$name = str_replace('+', '_', $name);
	$name = str_replace(',', '_', $name);
	$name = str_replace('*', '_', $name);
  return $name;
}

function strip_pg_escape_string($string){
	$string = str_replace("''", "'", $string);
	return $string;
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
    $this->debug=$debug;
    # Logdatei für Mysql setzen
    global $log_mysql;
    $this->log_mysql=$log_mysql;
    # Logdatei für PostgreSQL setzten
    global $log_postgres;
    $this->log_postgres=$log_postgres;
    # layout Templatedatei zur Anzeige der Daten
    if ($main!="") $this->main=$main;
    # Stylesheetdatei
    if (isset($style)) $this->style=$style;
    # mime_type html, pdf
    if (isset ($mime_type)) $this->mime_type=$mime_type;
		$this->scaleUnitSwitchScale = 239210;
		$this->trigger_functions = array();
  }
	
	function reduce_mapwidth($width_reduction, $height_reduction = NULL){
		# Diese Funktion reduziert die aktuelle Kartenbildbreite um $width_reduction Pixel (und optional die Kartenbildhöhe um $height_reduction Pixel), damit das Kartenbild in Fachschalen nicht zu groß erscheint.
		# Diese reduzierte Breite wird aber nicht in der Datenbank gespeichert, sondern gilt nur solange man in der Fachschale bleibt.
		# Außerdem wird bei Bedarf der aktuelle Maßstab berechnet und zurückgeliefert (er wird berechnet, weil ein loadmap() ja noch nicht aufgerufen wurde).
		# Mit diesem Maßstab kann dann einmal beim ersten Aufruf der Fachschale von der Hauptkarte aus nach dem loadmap() der Extent wieder so angepasst werden, dass der ursprüngliche Maßstab erhalten bleibt.
		# Dieser verkleinerte Extent wird wiederum in der Datenbank gespeichert. In der Datenbank steht dann also weiterhin die ursprüngliche Kartenbildgröße und der (dazu eigentlich nicht passende) in der Breite verkleinerte Extent.
		# Damit der Extent aber nur dann angepasst wird, wenn es notwendig ist (nämlich wenn man von der Hauptkarte kommt), wird der Maßstab nur berechnet, wenn Kartenbildgröße und Extent zusammenpassen.
		# Am "Nichtzusammenpassen" von Kartenbildgröße und Extent wird also erkannt, dass der Extent schon einmal verkleinert wurde.
		$this->formvars['width_reduction'] = $width_reduction;
		$this->formvars['height_reduction'] = $height_reduction;
		$width = $this->user->rolle->nImageWidth;
		$height = $this->user->rolle->nImageHeight;
		$extentwidth = $this->user->rolle->oGeorefExt->maxx - $this->user->rolle->oGeorefExt->minx;
		$extentheight = $this->user->rolle->oGeorefExt->maxy - $this->user->rolle->oGeorefExt->miny;
		$ratio_image = round($width/$height, 2);
		$ratio_extent = round($extentwidth/$extentheight, 2);
		if($ratio_image == $ratio_extent){
			$center_y = ($this->user->rolle->oGeorefExt->maxy + $this->user->rolle->oGeorefExt->miny) / 2;
			if($this->user->rolle->epsg_code == 4326){$unit = MS_DD;} else {$unit = MS_METERS;}
			$md = ($width-1)/(96 * InchesPerUnit($unit, $center_y));
			$gd = $this->user->rolle->oGeorefExt->maxx - $this->user->rolle->oGeorefExt->minx;
			$scale = $gd/$md;
		}
		$width = $width - $width_reduction;
		$height = $height - $height_reduction;
		if($this->user->rolle->hideMenue == 1){$width = $width - 195;}
		if($this->user->rolle->hideLegend == 1){$width = $width - 254;}
		$this->user->rolle->nImageWidth = $width;
		$this->user->rolle->nImageHeight = $height;
		return $scale;
	}
	
	function loadMultiLingualText($language) {
    #echo 'In der Rolle eingestellte Sprache: '.$GUI->user->rolle->language;
    $this->Stelle->language=$language;
    $this->Stelle->getName();
    include(LAYOUTPATH.'languages/'.$this->user->rolle->language.'.php');
  }

  function loadMap($loadMapSource) {
    $this->debug->write("<p>Funktion: loadMap('".$loadMapSource."','".$connStr."')",4);
    switch ($loadMapSource) {
      # lade Karte aus Post-Parametern
      case 'Post' : {
        if (MAPSERVERVERSION < 600) {
				  $map = ms_newMapObj(SHAPEPATH.'MapFiles/tk_niedersachsen.map');
				}
				else {
				  $map = new mapObj(SHAPEPATH.'MapFiles/tk_niedersachsen.map');
				}
				echo '<br>MapServer Version: '.ms_GetVersionInt();
				echo '<br>Details: '.ms_GetVersion();

        # Allgemeine Parameter
        #var_dump($this->formvars);
        $map->width = $this->formvars['post_width'];
        $map->set('height', $this->formvars['post_height']);
        $map->set('resolution',72);
        $map->set('units',MS_METERS);
        #$map->set('transparent', MS_OFF);
        #$map->set('interlace', MS_ON);
        $map->set('status', MS_ON);
        $map->set('name', MAPFILENAME);
        $map->imagecolor->setRGB(255,255,255);
        if($this->formvars['post_minx'] != ''){
          $map->setextent($this->formvars['post_minx'], $this->formvars['post_miny'], $this->formvars['post_maxx'], $this->formvars['post_maxy']);
        }
        else{
          $map->setextent($this->user->rolle->oGeorefExt->minx,$this->user->rolle->oGeorefExt->miny,$this->user->rolle->oGeorefExt->maxx,$this->user->rolle->oGeorefExt->maxy);
        }
        $map->setProjection('+init='.strtolower($this->formvars['post_epsg']),MS_TRUE);

        $map->setSymbolSet(SYMBOLSET);
        $map->setFontSet(FONTSET);
        $map->set('shapepath', SHAPEPATH);

        # Webobject
        $map->web->set('imagepath', IMAGEPATH);
        $map->web->set('imageurl', IMAGEURL);

        # OWS Metadaten
        $map->setMetaData('ows_title', 'WMS Ausdruck');
        $map->setMetaData('wms_extent',$this->formvars['post_minx'].''.$this->formvars['post_miny'].' '.$this->formvars['post_maxx'].' '.$this->formvars['post_maxy']);

        # Legendobject
        $map->legend->set('status', MS_ON);
        #$map->legend->set('transparent', MS_OFF);
        $map->legend->set('keysizex', '16');
        $map->legend->set('keysizey', '16');
        $map->legend->set('template', LAYOUTPATH.'legend_layer.htm');
        $map->legend->imagecolor -> setRGB(255,255,255);
        $map->legend->outlinecolor -> setRGB(-1,-1,-1);
        $map->legend->label->set('type', MS_TRUETYPE);
        $map->legend->label->set('font', 'arial');
        $map->legend->label->set('size', 12);
        $map->legend->label->color->setRGB(5,30,220);

        # layer
        if (is_array($this->formvars['layer'])) {
          $layerset=array_values($this->formvars['layer']);
        }
        else {
          $layerset=array();
        }
        for ($i=0; $i<count($layerset); $i++) {
				  if (MAPSERVERVERSION < 600) {
            $layer = ms_newLayerObj($map);
          }
					else {
					  $layer = new layerObj($map);
					}
					$layer->setMetaData('wms_name', $layerset[$i][name]);
          $layer->setMetaData('wms_server_version','1.1.1');
          $layer->setMetaData('wms_format','image/png');
          $layer->setMetaData('wms_extent',$this->formvars['post_minx'].' '.$this->formvars['post_miny'].' '.$this->formvars['post_maxx'].' '.$this->formvars['post_maxy']);
          $layer->setMetaData('ows_title', $layerset[$i][name]);
          if($layerset[$i][epsg_code] != ''){
            $layer->setMetaData('ows_srs', $layerset[$i][epsg_code]);
          }
          else{
            $layer->setMetaData('ows_srs', $this->formvars['post_epsg']);
          }
          $layer->setMetaData('wms_exceptions_format', 'application/vnd.ogc.se_inimage');
          $layer->setMetaData('real_layer_status', 1);
          $layer->setMetaData('off_requires',0);
          $layer->setMetaData('wms_connectiontimeout',60);
          $layer->setMetaData('wms_queryable',0);
          $layer->setMetaData('wms_group_title','WMS');
          $layer->set('type', 3);
          $layer->set('name', $layerset[$i][name]);
          $layer->set('status', 1);
          if($this->map_factor == ''){
            $this->map_factor=1;
          }
          if($layerset[$i]['maxscale'] > 0) {
            if(MAPSERVERVERSION > 500){
              $layer->set('maxscaledenom', $layerset[$i]['maxscale']/$this->map_factor*1.414);
            }
            else{
              $layer->set('maxscale', $layerset[$i]['maxscale']/$this->map_factor*1.414);
            }
          }
          if($layerset[$i]['minscale'] > 0) {
            if(MAPSERVERVERSION > 500){
              $layer->set('minscaledenom', $layerset[$i]['minscale']/$this->map_factor*1.414);
            }
            else{
              $layer->set('minscale', $layerset[$i]['minscale']/$this->map_factor*1.414);
            }
          }
          if($layerset[$i][epsg_code] != ''){
            $layer->setProjection('+init='.strtolower($layerset[$i][epsg_code])); # recommended
          }
          else{
            $layer->setProjection('+init='.strtolower($this->formvars['post_epsg']));
          }
          #$layer->set('connection',"http://www.kartenserver.niedersachsen.de/wmsconnector/com.esri.wms.Esrimap/Biotope?LAYERS=7&REQUEST=GetMap&TRANSPARENT=true&FORMAT=image/png&SERVICE=WMS&VERSION=1.1.1&STYLES=&EXCEPTIONS=application/vnd.ogc.se_xml&SRS=EPSG:31467");
          #echo '<br>Name: '.$layerset[$i][name];
          #echo '<br>Connection: '.$layerset[$i][connection];
          $layer->set('connection', $layerset[$i][connection]);
          if (MAPSERVERVERSION < 540) {
			      $layer->set('connectiontype', 7);
			    }
			    else {
			      $layer->setConnectionType(7);
			    }
          if($layerset[$i]['transparency'] != ''){
            if(MAPSERVERVERSION > 500){
              $layer->set('opacity',$layerset[$i]['transparency']);
            }
            else{
              $layer->set('transparency',$layerset[$i]['transparency']);
            }
          }
        } # end of Schleife layer
        $this->map=$map;
      } break;

      # lade Karte von einer Map-Datei
      case 'File' : {
        $debug->write("MapDatei $connStr laden",4);
				if (MAPSERVERVERSION < 600) {
          $this->map = ms_newMapObj(DEFAULTMAPFILE);
        }
				else {
				  $this->map = new mapObj(DEFAULTMAPFILE);
				}
			} break;

      # lade Karte von Datenbank
      case 'DataBase' : {
				if (MAPSERVERVERSION < 600) {
							$map = ms_newMapObj(DEFAULTMAPFILE);
						}
				else {
					$map = new mapObj(DEFAULTMAPFILE, SHAPEPATH);
				}
        $mapDB = new db_mapObj($this->Stelle->id,$this->user->id);

        # Allgemeine Parameter
        $map->set('width',$this->user->rolle->nImageWidth);
        $map->set('height',$this->user->rolle->nImageHeight);
        $map->set('resolution',96);
        #$map->set('transparent', MS_OFF);
        #$map->set('interlace', MS_ON);
        $map->set('status', MS_ON);
        $map->set('name', MAPFILENAME);
        $map->set('debug', MS_ON);
        $map->imagecolor->setRGB(255,255,255);
        $map->maxsize = 4096;
        $map->setProjection('+init=epsg:'.$this->user->rolle->epsg_code,MS_TRUE);

				# setzen der Kartenausdehnung über die letzten Benutzereinstellungen
				if($this->user->rolle->oGeorefExt->minx==='') {
				  echo "Richten Sie mit phpMyAdmin in der kvwmap Datenbank eine Referenzkarte, eine Stelle, einen Benutzer und eine Rolle ein ";
				  echo "<br>(Tabellen referenzkarten, stelle, user, rolle) ";
				  echo "<br>oder wenden Sie sich an ihren Systemverwalter.";
				  exit;
				}
				else {
				  $map->setextent($this->user->rolle->oGeorefExt->minx,$this->user->rolle->oGeorefExt->miny,$this->user->rolle->oGeorefExt->maxx,$this->user->rolle->oGeorefExt->maxy);
        }

        # OWS Metadaten

        if($this->Stelle->ows_title != ''){
          $map->setMetaData("ows_title",$this->Stelle->ows_title);}
        else{
          $map->setMetaData("ows_title",OWS_TITLE);
        }
        if($this->Stelle->ows_abstract != ''){
          $map->setMetaData("ows_abstract",$this->Stelle->ows_abstract);}
        else{
          $map->setMetaData("ows_title",OWS_ABSTRACT);
        }
        if($this->Stelle->wms_accessconstraints != ''){
          $map->setMetaData("wms_accessconstraints",$this->Stelle->wms_accessconstraints);}
        else{
          $map->setMetaData("wms_accessconstraints",OWS_ACCESSCONSTRAINTS);
        }
        if($this->Stelle->ows_contactperson != ''){
          $map->setMetaData("ows_contactperson",$this->Stelle->ows_contactperson);}
        else{
          $map->setMetaData("ows_contactperson",OWS_CONTACTPERSON);
        }
        if($this->Stelle->ows_contactorganization != ''){
          $map->setMetaData("ows_contactorganization",$this->Stelle->ows_contactorganization);}
        else{
          $map->setMetaData("ows_contactorganization",OWS_CONTACTORGANIZATION);
        }
        if($this->Stelle->ows_contactelectronicmailaddress != ''){
          $map->setMetaData("ows_contactelectronicmailaddress",$this->Stelle->ows_contactelectronicmailaddress);}
        else{
          $map->setMetaData("ows_contactelectronicmailaddress",OWS_CONTACTELECTRONICMAILADDRESS);
        }
        if($this->Stelle->ows_contactposition != ''){
          $map->setMetaData("ows_contactposition",$this->Stelle->ows_contactposition);}
        else{
          $map->setMetaData("ows_contactposition",OWS_CONTACTPOSITION);
        }
        if($this->Stelle->ows_fees != ''){
          $map->setMetaData("ows_fees",$this->Stelle->ows_fees);}
        else{
          $map->setMetaData("ows_fees",OWS_FEES);
        }
        if($this->Stelle->ows_srs != ''){
          $map->setMetaData("ows_srs",$this->Stelle->ows_srs);}
        else{
          $map->setMetaData("ows_srs",OWS_SRS);
        }
        $ows_onlineresource = OWS_SERVICE_ONLINERESOURCE.'&Stelle_ID='.$this->Stelle->id;
        $map->setMetaData("ows_onlineresource",$ows_onlineresource);
        $bb=$this->Stelle->MaxGeorefExt;
        $map->setMetaData("wms_extent",$bb->minx.' '.$bb->miny.' '.$bb->maxx.' '.$bb->maxy);
				// enable service types
        $map->setMetaData("ows_enable_request", '*');
        ///------------------------------////

        $map->setSymbolSet(SYMBOLSET);
        $map->setFontSet(FONTSET);
        $map->set('shapepath', SHAPEPATH);

        # Umrechnen des Stellenextents kann hier raus, weil es schon in start.php gemacht wird

        # Webobject
        $map->web->set('imagepath', IMAGEPATH);
        $map->web->set('imageurl', IMAGEURL);
        $map->web->set('log', LOGPATH.'mapserver.log');
        $map->setMetaData('wms_feature_info_mime_type',  'text/html');
        //$map->web->set('ERRORFILE', LOGPATH.'mapserver_error.log');

        # Referenzkarte
				$this->ref=$mapDB->read_ReferenceMap();
				if(MAPSERVERVERSION < 600){
					$reference_map = ms_newMapObj(DEFAULTMAPFILE);
				}
				else {
					$reference_map = new mapObj(DEFAULTMAPFILE);
				}
				$reference_map->web->set('imagepath', IMAGEPATH);
				$reference_map->setProjection('+init=epsg:'.$this->ref['epsg_code'],MS_FALSE);
				#$reference_map->extent->setextent in drawreferencemap() ausgelagert, da der Extent sich geändert haben kann nach dem loadmap
				$reference_map->reference->extent->setextent(round($this->ref['xmin']),round($this->ref['ymin']),round($this->ref['xmax']),round($this->ref['ymax']));
        $reference_map->reference->set('image',REFERENCEMAPPATH.$this->ref['Dateiname']);
        $reference_map->reference->set('width',$this->ref['width']);
        $reference_map->reference->set('height',$this->ref['height']);
        $reference_map->reference->set('status','MS_ON');
				if (MAPSERVERVERSION < 600) {
					$extent=ms_newRectObj();
				}
				else {
				  $extent = new rectObj();
				}
        $reference_map->reference->color->setRGB(-1,-1,-1);
        $reference_map->reference->outlinecolor->setRGB(255,0,0);

        # Scalebarobject
        $map->scalebar->set('status', MS_ON);
        $map->scalebar->set('units', MS_METERS);
        $map->scalebar->set('intervals', 4);
        $map->scalebar->color->setRGB(0,0,0);
        $r = substr(BG_MENUETOP, 1, 2);
        $g = substr(BG_MENUETOP, 3, 2);
        $b = substr(BG_MENUETOP, 5, 2);
        $map->scalebar->imagecolor->setRGB(hexdec($r), hexdec($g), hexdec($b));
        $map->scalebar->outlinecolor->setRGB(0,0,0);
				$map->scalebar->label->type = 'truetype';
				$map->scalebar->label->font = 'SourceSansPro';
				$map->scalebar->label->size = 10.5;

        # Groups
        if($this->formvars['nurAufgeklappteLayer'] == ''){
	        $this->groupset=$mapDB->read_Groups();
        }

        # Layer
				$mapDB->nurAktiveLayer = $this->formvars['nurAktiveLayer'];
        $mapDB->nurAufgeklappteLayer=$this->formvars['nurAufgeklappteLayer'];
        $mapDB->nurFremdeLayer=$this->formvars['nurFremdeLayer'];
        if($this->class_load_level == ''){
          $this->class_load_level = 1;
        }
        $layer = $mapDB->read_Layer($this->class_load_level, $this->Stelle->useLayerAliases, $this->list_subgroups($this->formvars['group']));     # class_load_level: 2 = für alle Layer die Klassen laden, 1 = nur für aktive Layer laden, 0 = keine Klassen laden
        $rollenlayer = $mapDB->read_RollenLayer();
        $layerset = array_merge($layer, $rollenlayer);
        $layerset['anzLayer'] = count($layerset) - 1; # wegen $layerset['layer_ids']
        unset($this->layers_of_group);		# falls loadmap zweimal aufgerufen wird
				unset($this->groups_with_layers);	# falls loadmap zweimal aufgerufen wird
        for($i=0; $i < $layerset['anzLayer']; $i++){
					$this->groups_with_layers[$layerset[$i]['Gruppe']][] = $i;			# die $i's pro Gruppe im layerset-Array
					if($layerset[$i]['requires'] == ''){
						$this->layers_of_group[$layerset[$i]['Gruppe']][] = $layerset[$i]['Layer_ID'];				# die Layer-IDs in einer Gruppe
					}
					$this->layer_id_string .= $layerset[$i]['Layer_ID'].'|';							# alle Layer-IDs hintereinander in einem String

					if($layerset[$i]['requires'] != ''){
						$layerset[$i]['aktivStatus'] = $layerset['layer_ids'][$layerset[$i]['requires']]['aktivStatus'];
						$layerset[$i]['showclasses'] = $layerset['layer_ids'][$layerset[$i]['requires']]['showclasses'];
					}

					if($this->class_load_level == 2 OR ($this->class_load_level == 1 AND $layerset[$i]['aktivStatus'] != 0)){      # nur wenn der Layer aktiv ist, sollen seine Parameter gesetzt werden
						$layer = ms_newLayerObj($map);
						$layer->setMetaData('wfs_request_method', 'GET');
						$layer->setMetaData('wms_name', $layerset[$i]['wms_name']);
						$layer->setMetaData('wfs_typename', $layerset[$i]['wms_name']);
						$layer->setMetaData('ows_title', $layerset[$i]['Name']); # required
						$layer->setMetaData('wms_group_title',$layerset[$i]['Gruppenname']);
						$layer->setMetaData('wms_queryable',$layerset[$i]['queryable']);
						$layer->setMetaData('wms_format',$layerset[$i]['wms_format']);
						$layer->setMetaData('ows_server_version',$layerset[$i]['wms_server_version']);
						$layer->setMetaData('ows_version',$layerset[$i]['wms_server_version']);
						if($layerset[$i]['ows_srs'] == '')$layerset[$i]['ows_srs'] = 'EPSG:'.$layerset[$i]['epsg_code'];
						$layer->setMetaData('ows_srs',$layerset[$i]['ows_srs']);
						$layer->setMetaData('wms_connectiontimeout',$layerset[$i]['wms_connectiontimeout']);
						$layer->setMetaData('ows_auth_username', $layerset[$i]['wms_auth_username']);
						$layer->setMetaData('ows_auth_password', $layerset[$i]['wms_auth_password']);
						$layer->setMetaData('ows_auth_type', 'basic');
						$layer->setMetaData('wms_exceptions_format', 'application/vnd.ogc.se_xml');

						$layer->set('dump', 0);
						$layer->set('type',$layerset[$i]['Datentyp']);
						$layer->set('group',$layerset[$i]['Gruppenname']);

						$layer->set('name', $layerset[$i]['alias']);

						if($layerset[$i]['status'] != ''){
							$layerset[$i]['aktivStatus'] = 0;
						}

						//---- wenn die Layer einer eingeklappten Gruppe nicht in der Karte //
						//---- dargestellt werden sollen, muß hier bei aktivStatus != 1 //
						//---- der layer_status auf 0 gesetzt werden//
						if($layerset[$i]['aktivStatus'] == 0){
						$layer->set('status', 0);
						}
						else{
						$layer->set('status', 1);
						}
						$layer->set('debug',MS_ON);

						# fremde Layer werden auf Verbindung getestet
						if($layerset[$i]['aktivStatus'] != 0 AND $layerset[$i]['connectiontype'] == 6 AND strpos($layerset[$i]['connection'], 'host') !== false AND strpos($layerset[$i]['connection'], 'host=localhost') === false AND strpos($layerset[$i]['connection'], 'host=pgsql') === false){
						$connection = explode(' ', trim($layerset[$i]['connection']));
								for($j = 0; $j < count($connection); $j++){
								if($connection[$j] != ''){
									$value = explode('=', $connection[$j]);
									if(strtolower($value[0]) == 'host'){
									$host = $value[1];
									}
									if(strtolower($value[0]) == 'port'){
									$port = $value[1];
									}
								}
								}
								if($port == '')$port = '5432';
						$fp = @fsockopen($host, $port, $errno, $errstr, 5);
						if(!$fp){			# keine Verbindung --> Layer ausschalten
							$layer->set('status', 0);
							$layer->setMetaData('queryStatus', 0);
									$this->Fehlermeldung = $errstr.' für Layer: '.$layerset[$i]['Name'].'<br>';
						}
						}

						if($layerset[$i]['aktivStatus'] != 0){
							$collapsed = false;
							$group = $this->groupset[$layerset[$i]['Gruppe']];				# die Gruppe des Layers
							if($group['status'] == 0){
								$this->group_has_active_layers[$layerset[$i]['Gruppe']] = 1;  	# die zugeklappte Gruppe hat aktive Layer
								$collapsed = true;
							}
							while($group['obergruppe'] != ''){
								$group = $this->groupset[$group['obergruppe']];
								if($collapsed OR $group['status'] == 0){
									$this->group_has_active_layers[$group['id']] = 1;  	# auch alle Obergruppen durchlaufen
									$collapsed = true;
								}
							}
						}

						if(!$this->noMinMaxScaling AND $layerset[$i]['minscale']>=0) {
							if($this->map_factor != ''){
								if(MAPSERVERVERSION > 500){
									$layer->set('minscaledenom', $layerset[$i]['minscale']/$this->map_factor*1.414);
								}
								else{
									$layer->set('minscale', $layerset[$i]['minscale']/$this->map_factor*1.414);
								}
							}
							else{
								if(MAPSERVERVERSION > 500){
									$layer->set('minscaledenom', $layerset[$i]['minscale']);
								}
								else{
									$layer->set('minscale', $layerset[$i]['minscale']);
								}
							}
						}
						if(!$this->noMinMaxScaling AND $layerset[$i]['maxscale']>0) {
							if($this->map_factor != ''){
								if(MAPSERVERVERSION > 500){
									$layer->set('maxscaledenom', $layerset[$i]['maxscale']/$this->map_factor*1.414);
								}
								else{
									$layer->set('maxscale', $layerset[$i]['maxscale']/$this->map_factor*1.414);
								}
							}
							else{
								if(MAPSERVERVERSION > 500){
									$layer->set('maxscaledenom', $layerset[$i]['maxscale']);
								}
								else{
									$layer->set('maxscale', $layerset[$i]['maxscale']);
								}
							}
						}
            $layer->setProjection('+init=epsg:'.$layerset[$i]['epsg_code']); # recommended
            if ($layerset[$i]['connection']!='') {
              if($this->map_factor != '' AND $layerset[$i]['connectiontype'] == 7){		# WMS-Layer
              	if($layerset[$i]['printconnection']!=''){
              		$layerset[$i]['connection'] = $layerset[$i]['printconnection']; 		# wenn es eine Druck-Connection gibt, wird diese verwendet
              	}
              	else{
                	//$layerset[$i]['connection'] .= '&mapfactor='.$this->map_factor;			# bei WMS-Layern wird der map_factor durchgeschleift (für die eigenen WMS) erstmal rausgenommen, weil einige WMS-Server der zusätzliche Parameter mapfactor stört
              	}
              }
              if($layerset[$i]['connectiontype'] == 6)$layerset[$i]['connection'] .= " options='-c client_encoding=".MYSQL_CHARSET."'";		# z.B. für Klassen mit Umlauten
              $layer->set('connection', $layerset[$i]['connection']);
            }
            if ($layerset[$i]['connectiontype']>0) {
              if (MAPSERVERVERSION >= 540) {
                $layer->setConnectionType($layerset[$i]['connectiontype']);
              }
              else {
                $layer->set('connectiontype',$layerset[$i]['connectiontype']);
              }
            }

						if($layerset[$i]['connectiontype'] == 6)$layerset[$i]['processing'] = 'CLOSE_CONNECTION=DEFER;'.$layerset[$i]['processing'];		# DB-Connection erst am Ende schliessen und nicht für jeden Layer neu aufmachen
            if ($layerset[$i]['processing'] != "") {
              $processings = explode(";",$layerset[$i]['processing']);
              foreach ($processings as $processing) {
                $layer->setProcessing($processing);
              }
            }
						if ($layerset[$i]['postlabelcache'] != 0) {
							$layer->set('postlabelcache',$layerset[$i]['postlabelcache']);
						}

						if($layerset[$i]['Datentyp'] == MS_LAYER_POINT AND $layerset[$i]['cluster_maxdistance'] != ''){
							$layer->cluster->maxdistance = $layerset[$i]['cluster_maxdistance'];
							$layer->cluster->region = 'ellipse';
						}

            if ($layerset[$i]['Datentyp']=='3') {
              if($layerset[$i]['transparency'] != ''){
                if(MAPSERVERVERSION > 500){
                  $layer->set('opacity',$layerset[$i]['transparency']);
                }
                else{
                  $layer->set('transparency',$layerset[$i]['transparency']);
                }
              }
              if ($layerset[$i]['tileindex']!='') {
                $layer->set('tileindex',SHAPEPATH.$layerset[$i]['tileindex']);
              }
              else {
                $layer->set('data', $layerset[$i]['Data']);
              }
              $layer->set('tileitem',$layerset[$i]['tileitem']);
              if ($layerset[$i]['offsite']!='') {
                $RGB=explode(' ',$layerset[$i]['offsite']);
                $layer->offsite->setRGB($RGB[0],$RGB[1],$RGB[2]);
              }
            }
            else {
              # Vektorlayer
              if($layerset[$i]['Data'] != ''){
								$layerset[$i]['Data'] = replace_params(
									$layerset[$i]['Data'],
									rolle::$layer_params,
									$this->user->id,
									$this->Stelle->id,
									rolle::$hist_timestamp,
									$this->user->rolle->language
								);
                $layer->set('data', $layerset[$i]['Data']);
              }

              # Setzen der Templatedateien für die Sachdatenanzeige inclt. Footer und Header.
              # Template (Body der Anzeige)
              if ($layerset[$i]['template']!='') {
                $layer->set('template',$layerset[$i]['template']);
              }
              # Header (Kopfdatei)
              if ($layerset[$i]['header']!='') {
                $layer->set('header',$layerset[$i]['header']);
              }
              # Footer (Fusszeile)
              if ($layerset[$i]['footer']!='') {
                $layer->set('footer',$layerset[$i]['footer']);
              }
              # Setzen der Spalte nach der der Layer klassifiziert werden soll
              if ($layerset[$i]['classitem']!='') {
                $layer->set('classitem', replace_params($layerset[$i]['classitem'], rolle::$layer_params));
              }
              else {
                #$layer->set('classitem','id');
              }
              # Setzen des Filters
              if($layerset[$i]['Filter'] != ''){
              	$layerset[$i]['Filter'] = str_replace('$userid', $this->user->id, $layerset[$i]['Filter']);
               if (substr($layerset[$i]['Filter'],0,1)=='(') {
                 $expr=$layerset[$i]['Filter'];
               }
               else {
                 $expr=buildExpressionString($layerset[$i]['Filter']);
               }
               $layer->setFilter($expr);
              }
              # Layerweite Labelangaben
              if (MAPSERVERVERSION < 500 AND $layerset[$i]['labelangleitem']!='') {
                $layer->set('labelangleitem',$layerset[$i]['labelangleitem']);
              }
              if ($layerset[$i]['labelitem']!='') {
                $layer->set('labelitem',$layerset[$i]['labelitem']);
              }
              if ($layerset[$i]['labelmaxscale']!='') {
                if(MAPSERVERVERSION > 500){
                  $layer->set('labelmaxscaledenom',$layerset[$i]['labelmaxscale']);
                }
                else{
                  $layer->set('labelmaxscale',$layerset[$i]['labelmaxscale']);
                }
              }
              if ($layerset[$i]['labelminscale']!='') {
                if(MAPSERVERVERSION > 500){
                  $layer->set('labelminscaledenom',$layerset[$i]['labelminscale']);
                }
                else{
                  $layer->set('labelminscale',$layerset[$i]['labelminscale']);
                }
              }
              if ($layerset[$i]['labelrequires']!='') {
                $layer->set('labelrequires',$layerset[$i]['labelrequires']);
              }
              if ($layerset[$i]['tolerance']!='3') {
                $layer->set('tolerance',$layerset[$i]['tolerance']);
              }
              if ($layerset[$i]['toleranceunits']!='pixels') {
                $layer->set('toleranceunits',$layerset[$i]['toleranceunits']);
              }
              if ($layerset[$i]['transparency']!=''){
                if(MAPSERVERVERSION > 500){
                  if ($layerset[$i]['transparency']==-1) {
                      $layer->set('opacity',MS_GD_ALPHA);
                  }
                  else {
                      $layer->set('opacity',$layerset[$i]['transparency']);
                  }
                }
                else {
                  if ($layerset[$i]['transparency']==-1) {
                      $layer->set('transparency',MS_GD_ALPHA);
                  }
                  else {
                      $layer->set('transparency',$layerset[$i]['transparency']);
                  }
                }
              }
              if ($layerset[$i]['symbolscale']!='') {
                if($this->map_factor != ''){
                  if(MAPSERVERVERSION > 500){
                    $layer->set('symbolscaledenom',$layerset[$i]['symbolscale']/$this->map_factor*1.414);
                  }
                  else{
                    $layer->set('symbolscale',$layerset[$i]['symbolscale']/$this->map_factor*1.414);
                  }
                }
                else{
                  if(MAPSERVERVERSION > 500){
                    $layer->set('symbolscaledenom',$layerset[$i]['symbolscale']);
                  }
                  else{
                    $layer->set('symbolscale',$layerset[$i]['symbolscale']);
                  }
                }
              }
            } # ende of Vektorlayer
            # Klassen
            $classset=$layerset[$i]['Class'];
            $this->loadclasses($layer, $layerset[$i], $classset, $map);
          } # Ende Layer ist aktiv
        } # end of Schleife layer

				$this->layerset = $layerset;
        $this->map=$map;
				$this->reference_map = $reference_map;
				if (MAPSERVERVERSION >= 600 ) {
					$this->map_scaledenom = $map->scaledenom;
				}
				else {
					$this->map_scaledenom = $map->scale;
				}
        $this->mapDB=$mapDB;
      } break; # end of lade Karte von Datenbank
    } # end of switch loadMapSource
    return 1;
  }

	function list_subgroups($groupid){
		if($groupid != ''){
			$group = $this->groupset[$groupid];
			if($group['untergruppen'] != ''){
				foreach($group['untergruppen'] as $untergruppe){
					$subgroups .= ', '.$this->list_subgroups($untergruppe);
				}
				return $groupid.$subgroups;
			}
			else return $groupid;
		}
	}

  function loadclasses($layer, $layerset, $classset, $map){
    $anzClass=count($classset);
    for ($j=0;$j<$anzClass;$j++) {
      $klasse = ms_newClassObj($layer);
      if ($classset[$j]['Name']!='') {
        $klasse -> set('name',$classset[$j]['Name']);
      }
      if($classset[$j]['Status']=='1'){
      	$klasse->set('status', MS_ON);
      }
      else{
      	$klasse->set('status', MS_OFF);
      }
      $klasse -> set('template', $layerset['template']);
      $klasse -> setexpression($classset[$j]['Expression']);
      if ($classset[$j]['text']!='') {
        $klasse -> settext($classset[$j]['text']);
      }
      if ($classset[$j]['legendgraphic'] != '') {
				$imagename = WWWROOT.APPLVERSION.GRAPHICSPATH . 'custom/' . $classset[$j]['legendgraphic'];
				$klasse->set('keyimage', $imagename);
			}
      for ($k=0;$k<count($classset[$j]['Style']);$k++) {
        $dbStyle=$classset[$j]['Style'][$k];
				if (MAPSERVERVERSION < 600) {
          $style = ms_newStyleObj($klasse);
        }
				else {
				  $style = new styleObj($klasse);
				}
				if($dbStyle['geomtransform'] != ''){
					$style->updateFromString("STYLE GEOMTRANSFORM '".$dbStyle['geomtransform']."' END"); 
				}
				if($dbStyle['minscale'] != ''){
					$style->set('minscaledenom', $dbStyle['minscale']);
				}
				if($dbStyle['maxscale'] != ''){
					$style->set('maxscaledenom', $dbStyle['maxscale']);
				}
				if ($dbStyle['symbolname']!='') {
          $style->set('symbolname',$dbStyle['symbolname']);
        }
        if ($dbStyle['symbol']>0) {
          $style->set('symbol',$dbStyle['symbol']);
        }
        if (MAPSERVERVERSION >= 620) {
					if($dbStyle['geomtransform'] != '') {
						$style->setGeomTransform($dbStyle['geomtransform']);
					}
          if ($dbStyle['pattern']!='') {
            $style->setPattern(explode(' ',$dbStyle['pattern']));
            $style->linecap = 'butt';
          }
					if($dbStyle['gap'] != '') {
	          $style->set('gap', $dbStyle['gap']);
	        }
					if($dbStyle['initialgap'] != '') {
            $style->set('initialgap', $dbStyle['initialgap']);
          }
					if($dbStyle['linecap'] != '') {
	          $style->set('linecap', constant(MS_CJC_.strtoupper($dbStyle['linecap'])));
	        }
					if($dbStyle['linejoin'] != '') {
	          $style->set('linejoin', constant(MS_CJC_.strtoupper($dbStyle['linejoin'])));
	        }
					if($dbStyle['linejoinmaxsize'] != '') {
	          $style->set('linejoinmaxsize', $dbStyle['linejoinmaxsize']);
	        }
					if($dbStyle['polaroffset'] != '') {
	          $style->updateFromString("STYLE POLAROFFSET ".$dbStyle['polaroffset']." END"); 
	        }
        }

        if($this->map_factor != ''){
          if (MAPSERVERVERSION >= 620) {
            $pattern = $style->getpatternarray();
            if($pattern){
					    foreach($pattern as &$pat){
					      $pat = $pat * $this->map_factor;
					    }
					    $style->setPattern($pattern);
				    }
          }
          else {
            if($style->symbol > 0){
              $symbol = $map->getSymbolObjectById($style->symbol);
              $pattern = $symbol->getpatternarray();
              if(is_array($pattern) AND $symbol->inmapfile != 1){
                foreach($pattern as &$pat){
                  $pat = $pat * $this->map_factor;
                }
                $symbol->setpattern($pattern);
                $symbol->set('inmapfile', 1);
              }
            }
          }
        }
				if($dbStyle['size'] != ''){
					if ($layerset['Datentyp'] == 8) {
						# Skalierung der Stylegröße when Type Chart
						$style->setbinding(MS_STYLE_BINDING_SIZE, $dbStyle['size']);
					}
					else {
						if($this->map_factor != '') {
							$style->set('size', $dbStyle['size']*$this->map_factor/1.414);
						}
						else{
							if(is_numeric($dbStyle['size']))$style->set('size', $dbStyle['size']);
							else $style->updateFromString("STYLE SIZE [".$dbStyle['size']."] END");
						}
					}
				}

        if ($dbStyle['minsize']!='') {
          if($this->map_factor != ''){
            $style -> set('minsize',$dbStyle['minsize']*$this->map_factor/1.414);
          }
          else{
            $style -> set('minsize',$dbStyle['minsize']);
          }
        }

        if ($dbStyle['maxsize']!='') {
          if($this->map_factor != ''){
            $style -> set('maxsize',$dbStyle['maxsize']*$this->map_factor/1.414);
          }
          else{
            $style -> set('maxsize',$dbStyle['maxsize']);
          }
        }

				if($dbStyle['angle'] != '') {
					$style->updateFromString("STYLE ANGLE ".$dbStyle['angle']." END"); 		# wegen AUTO
				}
        if ($dbStyle['angleitem']!=''){
          if(MAPSERVERVERSION < 500){
            $style->set('angleitem',$dbStyle['angleitem']);
          }
          else{
            $style->setbinding(MS_STYLE_BINDING_ANGLE, $dbStyle['angleitem']);
          }
        }
        if ($dbStyle['width']!='') {
          if ($dbStyle['antialias']!='') {
            $style -> set('antialias',$dbStyle['antialias']);
          }
          if($this->map_factor != ''){
            $style -> set('width',$dbStyle['width']*$this->map_factor/1.414);
          }
          else{
            $style->set('width',$dbStyle['width']);
          }
        }

        if ($dbStyle['minwidth']!='') {
          if($this->map_factor != ''){
            $style->set('minwidth',$dbStyle['minwidth']*$this->map_factor/1.414);
          }
          else{
            $style->set('minwidth',$dbStyle['minwidth']);
          }
        }

        if ($dbStyle['maxwidth']!='') {
          if($this->map_factor != ''){
            $style->set('maxwidth',$dbStyle['maxwidth']*$this->map_factor/1.414);
          }
          else{
            $style->set('maxwidth',$dbStyle['maxwidth']);
          }
        }

        if (MAPSERVERVERSION < 500 AND $dbStyle['sizeitem']!='') {
          $style->set('sizeitem', $dbStyle['sizeitem']);
        }
        if ($dbStyle['color']!='') {
          $RGB=explode(" ",$dbStyle['color']);
          if ($RGB[0]=='') { $RGB[0]=0; $RGB[1]=0; $RGB[2]=0; }
          if(is_numeric($RGB[0]))$style->color->setRGB($RGB[0],$RGB[1],$RGB[2]);
					else $style->updateFromString("STYLE COLOR [".$dbStyle['color']."] END");
        }
				if($dbStyle['opacity'] != '') {		# muss nach color gesetzt werden
					$style->set('opacity', $dbStyle['opacity']);
				}
        if ($dbStyle['outlinecolor']!='') {
          $RGB=explode(" ",$dbStyle['outlinecolor']);
        	if ($RGB[0]=='') { $RGB[0]=0; $RGB[1]=0; $RGB[2]=0; }
          $style->outlinecolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
        }
        if ($dbStyle['backgroundcolor']!='') {
          $RGB=explode(" ",$dbStyle['backgroundcolor']);
        	if ($RGB[0]=='') { $RGB[0]=0; $RGB[1]=0; $RGB[2]=0; }
          $style->backgroundcolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
        }
				if($dbStyle['colorrange'] != '') {
					$style->updateFromString("STYLE COLORRANGE ".$dbStyle['colorrange']." END");
				}
				if($dbStyle['datarange'] != '') {
					$style->updateFromString("STYLE DATARANGE ".$dbStyle['datarange']." END");
				}
				if($dbStyle['rangeitem'] != '') {
					$style->updateFromString("STYLE RANGEITEM ".$dbStyle['rangeitem']." END");
				}
        if ($dbStyle['offsetx']!='') {
          $style->set('offsetx', $dbStyle['offsetx']);
        }
        if ($dbStyle['offsety']!='') {
          $style->set('offsety', $dbStyle['offsety']);
        }
      } # Ende Schleife für mehrere Styles

      # setzen eines oder mehrerer Labels
      # Änderung am 12.07.2005 Korduan
      for ($k=0;$k<count($classset[$j]['Label']);$k++) {
        $dbLabel=$classset[$j]['Label'][$k];
        if (MAPSERVERVERSION < 600) {
          $klasse->label->set('type',$dbLabel['type']);
          $klasse->label->set('font',$dbLabel['font']);
          $RGB=explode(" ",$dbLabel['color']);
          if ($RGB[0]=='') { $RGB[0]=0; }
          if ($RGB[1]=='') { $RGB[1]=0; }
          if ($RGB[2]=='') { $RGB[2]=0; }
          $klasse->label->color->setRGB($RGB[0],$RGB[1],$RGB[2]);
          $RGB=explode(" ",$dbLabel['outlinecolor']);
          $klasse->label->outlinecolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
          if ($dbLabel['shadowcolor']!='') {
            $RGB=explode(" ",$dbLabel['shadowcolor']);
            $klasse->label->shadowcolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
            $klasse->label->set('shadowsizex',$dbLabel['shadowsizex']);
            $klasse->label->set('shadowsizey',$dbLabel['shadowsizey']);
          }
          if ($dbLabel['backgroundcolor']!='') {
            $RGB=explode(" ",$dbLabel['backgroundcolor']);
            $klasse->label->backgroundcolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
          }
          if ($dbLabel['backgroundshadowcolor']!='') {
            $RGB=explode(" ",$dbLabel['backgroundshadowcolor']);
            $klasse->label->backgroundshadowcolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
            $klasse->label->set('backgroundshadowsizex',$dbLabel['backgroundshadowsizex']);
            $klasse->label->set('backgroundshadowsizey',$dbLabel['backgroundshadowsizey']);
          }
          $klasse->label->set('angle',$dbLabel['angle']);
          if(MAPSERVERVERSION > 500 AND $layerset['labelangleitem']!=''){
            $klasse->label->setbinding(MS_LABEL_BINDING_ANGLE, $layerset['labelangleitem']);
          }
        	if($dbLabel['autoangle']==1) {
            if(MAPSERVERVERSION >= 600){
	          	$klasse->label->set('anglemode', MS_AUTO);
	          }
	          else{
	          	$klasse->label->set('autoangle',$dbLabel['autoangle']);
            }
          }
          if ($dbLabel['buffer']!='') {
            $klasse->label->set('buffer',$dbLabel['buffer']);
          }
					$klasse->label->set('maxlength',$dbLabel['maxlength']);
          $klasse->label->set('wrap',$dbLabel['wrap']);
          $klasse->label->set('force',$dbLabel['the_force']);
          $klasse->label->set('partials',$dbLabel['partials']);
          $klasse->label->set('size',$dbLabel['size']);
          $klasse->label->set('minsize',$dbLabel['minsize']);
          $klasse->label->set('maxsize',$dbLabel['maxsize']);
          # Skalierung der Labelschriftgröße, wenn map_factor gesetzt
          if($this->map_factor != ''){
            $klasse->label->set('minsize',$dbLabel['minsize']*$this->map_factor/1.414);
            $klasse->label->set('maxsize',$dbLabel['size']*$this->map_factor/1.414);
            $klasse->label->set('size',$dbLabel['size']*$this->map_factor/1.414);
          }
          if ($dbLabel['position']!='') {
            switch ($dbLabel['position']){
              case '0' :{
                $klasse->label->set('position', MS_UL);
              }break;
              case '1' :{
                $klasse->label->set('position', MS_LR);
              }break;
              case '2' :{
                $klasse->label->set('position', MS_UR);
              }break;
              case '3' :{
                $klasse->label->set('position', MS_LL);
              }break;
              case '4' :{
                $klasse->label->set('position', MS_CR);
              }break;
              case '5' :{
                $klasse->label->set('position', MS_CL);
              }break;
              case '6' :{
                $klasse->label->set('position', MS_UC);
              }break;
              case '7' :{
                $klasse->label->set('position', MS_LC);
              }break;
              case '8' :{
                $klasse->label->set('position', MS_CC);
              }break;
              case '9' :{
                $klasse->label->set('position', MS_AUTO);
              }break;
            }
          }
          if ($dbLabel['offsetx']!='') {
            $klasse->label->set('offsetx',$dbLabel['offsetx']);
          }
          if ($dbLabel['offsety']!='') {
            $klasse->label->set('offsety',$dbLabel['offsety']);
          }
        } # ende mapserver < 600
        else {
          $label = new labelObj();
          $label->type = $dbLabel['type'];
          $label->font = $dbLabel['font'];
          $RGB=explode(" ",$dbLabel['color']);
          if ($RGB[0]=='') { $RGB[0]=0; $RGB[1]=0; $RGB[2]=0; }
          $label->color->setRGB($RGB[0],$RGB[1],$RGB[2]);
          if($dbLabel['outlinecolor'] != ''){
						$RGB=explode(" ",$dbLabel['outlinecolor']);
						$label->outlinecolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
					}
          if ($dbLabel['shadowcolor']!='') {
            $RGB=explode(" ",$dbLabel['shadowcolor']);
            $label->shadowcolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
            $label->shadowsizex = $dbLabel['shadowsizex'];
            $label->shadowsizey = $dbLabel['shadowsizey'];
          }

          if($dbLabel['backgroundshadowcolor']!='') {
            $RGB=explode(" ",$dbLabel['backgroundshadowcolor']);
            $style = new styleObj($label);
						$style->setGeomTransform('labelpoly');
            $style->color->setRGB($RGB[0],$RGB[1],$RGB[2]);
            $style->set('offsetx', $dbLabel['backgroundshadowsizex']);
						$style->set('offsety', $dbLabel['backgroundshadowsizey']);
						if ($dbLabel['buffer']!='') {
							$style->outlinecolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
							$style->set('width', $dbLabel['buffer']);
						}
          }
					if ($dbLabel['backgroundcolor']!='') {
            $RGB=explode(" ",$dbLabel['backgroundcolor']);
						$style = new styleObj($label);
						$style->setGeomTransform('labelpoly');
            $style->color->setRGB($RGB[0],$RGB[1],$RGB[2]);
						if ($dbLabel['buffer']!='') {
							$style->outlinecolor->setRGB($RGB[0],$RGB[1],$RGB[2]);
							$style->set('width', $dbLabel['buffer']);
						}
          }

          $label->angle = $dbLabel['angle'];
          if($layerset['labelangleitem']!=''){
            $label->setBinding(MS_LABEL_BINDING_ANGLE, $layerset['labelangleitem']);
          }
        	if($dbLabel['autoangle']==1) {
            if(MAPSERVERVERSION >= 600){
            	$label->set('anglemode', MS_AUTO);
            }
            else{
            	$label->autoangle = $dbLabel['autoangle'];
            }
          }
          if ($dbLabel['buffer']!='') {
            $label->buffer = $dbLabel['buffer'];
          }
					$label->set('maxlength',$dbLabel['maxlength']);
          $label->wrap = $dbLabel['wrap'];
          $label->force = $dbLabel['the_force'];
          $label->partials = $dbLabel['partials'];
          $label->size = $dbLabel['size'];
          $label->minsize = $dbLabel['minsize'];
          $label->maxsize = $dbLabel['maxsize'];
          # Skalierung der Labelschriftgröße, wenn map_factor gesetzt
          if($this->map_factor != ''){
            $label->minsize = $dbLabel['minsize']*$this->map_factor/1.414;
            $label->maxsize = $dbLabel['size']*$this->map_factor/1.414;
            $label->size = $dbLabel['size']*$this->map_factor/1.414;
          }
          if ($dbLabel['position']!='') {
            switch ($dbLabel['position']){
              case '0' :{
                $label->set('position', MS_UL);
              }break;
              case '1' :{
                $label->set('position', MS_LR);
              }break;
              case '2' :{
                $label->set('position', MS_UR);
              }break;
              case '3' :{
                $label->set('position', MS_LL);
              }break;
              case '4' :{
                $label->set('position', MS_CR);
              }break;
              case '5' :{
                $label->set('position', MS_CL);
              }break;
              case '6' :{
                $label->set('position', MS_UC);
              }break;
              case '7' :{
                $label->set('position', MS_LC);
              }break;
              case '8' :{
                $label->set('position', MS_CC);
              }break;
              case '9' :{
                $label->set('position', MS_AUTO);
              }break;
            }
          }
          if ($dbLabel['offsetx']!='') {
            $label->offsetx = $dbLabel['offsetx'];
          }
          if ($dbLabel['offsety']!='') {
            $label->offsety = $dbLabel['offsety'];
          }
          $klasse->addLabel($label);
        } # ende mapserver >=600
      } # ende Schleife für mehrere Label
    } # end of Schleife Class
  }

  function navMap($cmd) {
    switch ($cmd) {
      case "previous" : {
#        $this->user->rolle->setSelectedButton('previous');
        $this->setPrevMapExtent($this->user->rolle->last_time_id);
      } break;
      case "next" : {
#        $this->user->rolle->setSelectedButton('next');
        $this->setNextMapExtent($this->user->rolle->last_time_id);
      } break;
      case "zoomin" : {
        $this->user->rolle->setSelectedButton('zoomin');
        $this->zoomMap($this->user->rolle->nZoomFactor);
      } break;
			case "zoomin_wheel" : {
        $this->zoomMap($this->user->rolle->nZoomFactor);
      } break;
      case "zoomout" : {
        $this->user->rolle->setSelectedButton('zoomout');
        $this->zoomMap($this->user->rolle->nZoomFactor*-1);
      } break;
      case "recentre" : {
        $this->user->rolle->setSelectedButton('recentre');
        $this->zoomMap(1);
      } break;
      // case "jump_coords" : {
        // $this->user->rolle->setSelectedButton('recentre');
        // $this->zoomMap(1);
      // } break;
      case "pquery" : {
        $this->user->rolle->setSelectedButton('pquery');
        $this->queryMap();
      } break;
      case "touchquery" : {
        $this->user->rolle->setSelectedButton('touchquery');
        $this->queryMap();
      } break;
      case "ppquery" : {
        $this->user->rolle->setSelectedButton('ppquery');
        $this->queryMap();
      } break;
      case "polygonquery" : {
        $this->user->rolle->setSelectedButton('polygonquery');
        $this->queryMap();
      } break;
      case "Full_Extent" : {
        $this->user->rolle->setSelectedButton('zoomin');   # um anschliessend wieder neu zoomen zu koennen!
        $this->setFullExtent();
      } break;
      default : {
      }
    }
  	if (MAPSERVERVERSION > 600) {
			$this->map_scaledenom = $this->map->scaledenom;
		}
		else {
			$this->map_scaledenom = $this->map->scale;
		}
  }

  function zoomMap($nZoomFactor){
		# Funktion zum Zoomen über die Navigationswerkzeuge; Koordinaten sind Bildkoordinaten
    $corners=explode(';',$this->formvars['INPUT_COORD']);
    # Auslesen der ersten übergebenen Koordinate
    $lo=explode(',',$corners[0]);
    $minx=$lo[0];
    $maxy=$lo[1];
    # Abfrage, ob eine oder zwei Koordinaten übergeben wurden
    if (count($corners)==1) {
      # es wurde nur ein Punkt übergeben zum zoomen
      #echo '<br>Zoom zum Punkt.';
      $zoom='point';
    }
    else {
      # es wurde ein Rechteck gesetzt zum zoomen
      #echo '<br>Zoom to Rechteck.';
      $ru=explode(',',$corners[1]);
      $miny=$ru[1];
      $maxx=$ru[0];
      if ($minx==$maxx AND $miny==$maxy) {
        # Das Rechteck hat die Kantenlänge 0 deshalb zoom auf Punkt
        $zoom='point';
      }
      else {
        # zoom auf Rechteck wegen Kantenlänge > 0
        $zoom='rectangle';
      }
    }
    if ($zoom=='point') {
      # Zoomen auf einen Punkt
      $this->debug->write('<br>Es wird auf einen Punkt gezoomt',4);
      # Erzeugen eines Punktobjektes
      $oPixelPos=ms_newPointObj();

			$oPixelPos->setXY($minx,$maxy);
			$this->map->zoompoint($nZoomFactor,$oPixelPos,$this->map->width,$this->map->height,$this->map->extent,$this->Stelle->MaxGeorefExt);
    }
    else {
      # Zoomen auf ein Rechteck
      $this->debug->write('<br>Es wird auf ein Rechteck gezoomt',4);
			$oPixelExt=ms_newRectObj();
      if($minx != 'undefined' AND $miny != 'undefined' AND $maxx != 'undefined' AND $maxy != 'undefined'){
       	$oPixelExt->setextent($minx,$miny,$maxx,$maxy);
        $this->map->zoomrectangle($oPixelExt,$this->map->width,$this->map->height,$this->map->extent);
        # Nochmal Zoomen auf die Mitte mit Faktor 1, damit der Ausschnitt in den erlaubten Bereich
        # verschoben wird, falls er ausserhalb liegt, zoompoint berücksichtigt das, zoomrectangle nicht.
        $oPixelPos=ms_newPointObj();
        $oPixelPos->setXY($this->map->width/2,$this->map->height/2);
        $this->map->zoompoint(1,$oPixelPos,$this->map->width,$this->map->height,$this->map->extent,$this->Stelle->MaxGeorefExt);
      }
    }
  }

  function saveMap($saveMapDestination) {
		if ($saveMapDestination=='') {
      $saveMapDestination=SAVEMAPFILE;
    }
    if ($saveMapDestination != '') {
      $this->map->save($saveMapDestination);
    }
    $this->user->rolle->saveSettings($this->map->extent);
    # 2006-02-16 pk
    $this->user->rolle->readSettings();
  }

  function drawMap() {
		if($this->formvars['go'] != 'navMap_ajax')set_error_handler("MapserverErrorHandler");		// ist in allg_funktionen.php definiert
    if($this->main == 'map.php' AND MINSCALE != '' AND $this->map_factor == '' AND $this->map_scaledenom < MINSCALE){
      $this->scaleMap(MINSCALE);
			$this->saveMap('');
    }
    $this->image_map = $this->map->draw() OR die($this->layer_error_handling());
    $filename = $this->user->id.'_'.rand(0, 1000000).'.'.$this->map->outputformat->extension;
    $this->image_map->saveImage(IMAGEPATH.$filename);
    $this->img['hauptkarte'] = IMAGEURL.$filename;
    $this->debug->write("Name der Hauptkarte: ".$this->img['hauptkarte'],4);

		if($this->formvars['go'] != 'navMap_ajax'){
			$this->legende = $this->create_dynamic_legend();
			$this->debug->write("Legende erzeugt",4);
		}
		else{
			# Zusammensetzen eines Layerhiddenstrings, in dem die aktuelle Sichtbarkeit aller aufgeklappten Layer gespeichert ist um damit bei Bedarf die Legende neu zu laden
			for($i = 0; $i < $this->layerset['anzLayer']; $i++) {
				$layer=&$this->layerset[$i];
				if($layer['requires'] == ''){
					if($this->check_layer_visibility($layer))$layerhiddenflag = '0';
					else $layerhiddenflag = '1';
					$this->layerhiddenstring .= $layer['Layer_ID'].' '.$layerhiddenflag.' ';
				}
			}
		}

    # Erstellen des Maßstabes
		$this->map_scaledenom = $this->map->scaledenom;
    $this->switchScaleUnitIfNecessary();
    $img_scalebar = $this->map->drawScaleBar();
    $filename = $this->map_saveWebImage($img_scalebar,'png');
    $newname = $this->user->id.basename($filename);
    rename(IMAGEPATH.basename($filename), IMAGEPATH.$newname);
    $this->img['scalebar'] = IMAGEURL.$newname;
    $this->debug->write("Name des Scalebars: ".$this->img['scalebar'],4);
		$this->calculatePixelSize();
		$this->drawReferenceMap();
  }

  function scaleMap($nScale) {
    $oPixelPos=ms_newPointObj();
    $oPixelPos->setXY($this->map->width/2,$this->map->height/2);
    $this->map->zoomscale($nScale,$oPixelPos,$this->map->width,$this->map->height,$this->map->extent,$this->Stelle->MaxGeorefExt);
  	if (MAPSERVERVERSION >= 600 ) {
			$this->map_scaledenom = $this->map->scaledenom;
		}
		else {
			$this->map_scaledenom = $this->map->scale;
		}
  }

	function check_layer_visibility(&$layer){
		if($layer['status'] != '' OR ($this->map_scaledenom < $layer['minscale'] OR ($layer['maxscale'] > 0 AND $this->map_scaledenom > $layer['maxscale']))) {
			return false;
		}
		return true;
	}

  function switchScaleUnitIfNecessary() {
		if ($this->map_scaledenom > $this->scaleUnitSwitchScale) $this->map->scalebar->set('units', MS_KILOMETERS);
  }

	function map_saveWebImage($image,$format) {
		if(MAPSERVERVERSION >= 600 ) {
			return $image->saveWebImage();
		}
		else {
			return $image->saveWebImage($format, 1, 1, 0);
		}
	}

	function calculatePixelSize() {
    $this->pixwidth = ($this->map->extent->maxx - $this->map->extent->minx)/$this->map->width;
    $this->pixheight = ($this->map->extent->maxy - $this->map->extent->miny)/$this->map->height;
    if ($this->pixwidth>$this->pixheight) {
      $this->pixsize=$this->pixwidth;
    }
    else {
      $this->pixsize=$this->pixheight;
    }
	}

  function drawReferenceMap(){
    # Erstellen der Referenzkarte
    if($this->reference_map->reference->image != NULL){
			$this->reference_map->setextent($this->map->extent->minx,$this->map->extent->miny,$this->map->extent->maxx,$this->map->extent->maxy);
			if($this->ref['epsg_code'] != $this->user->rolle->epsg_code){
				if(MAPSERVERVERSION < '600'){
					$projFROM = ms_newprojectionobj("init=epsg:".$this->user->rolle->epsg_code);
					$projTO = ms_newprojectionobj("init=epsg:".$this->ref['epsg_code']);
				}
				else{
					$projFROM = $this->map->projection;
					$projTO = $this->reference_map->projection;
				}
				$this->reference_map->extent->project($projFROM, $projTO);
			}
      $img_refmap = $this->reference_map->drawReferenceMap();
      $filename = $this->map_saveWebImage($img_refmap,'png');
      $newname = $this->user->id.basename($filename);
      rename(IMAGEPATH.basename($filename), IMAGEPATH.$newname);
      $this->img['referenzkarte'] = IMAGEURL.$newname;
      $this->debug->write("Name der Referenzkarte: ".$this->img['referenzkarte'],4);
      $this->Lagebezeichung=$this->getLagebezeichnung($this->user->rolle->epsg_code);
    }
	}

  function getLagebezeichnung($epsgcode) {
    switch (LAGEBEZEICHNUNGSART) {
      case 'Flurbezeichnung' : {
        $Lagebezeichnung = $this->getFlurbezeichnung($epsgcode);
			} break;
			default : {
			  $Lagebezeichnung = '';
			}
	  }
    return $Lagebezeichnung;
  }

  function getFlurbezeichnung($epsgcode) {
    $Flurbezeichnung = '';
 	  $flur = new Flur('','','',$this->pgdatabase);
		$bildmitte['rw']=($this->map->extent->maxx+$this->map->extent->minx)/2;
		$bildmitte['hw']=($this->map->extent->maxy+$this->map->extent->miny)/2;
		$ret=$flur->getBezeichnungFromPosition($bildmitte, $epsgcode);
		if ($ret[0]) {
		}
		else {
			if ($ret[1]['flur'] != '') {
				$Flurbezeichnung = $ret[1];
			}
		}
		return $Flurbezeichnung;
  }

  function output() {
		global $sizes;
	  foreach($this->formvars as $key => $value){
			#if(is_string($value))$this->formvars[$key] = stripslashes($value);
			if(is_string($value))$this->formvars[$key] = strip_pg_escape_string($value);
	  }
    # bisher gibt es folgenden verschiedenen Dokumente die angezeigt werden können
		if ($this->formvars['mime_type'] != '') $this->mime_type = $this->formvars['mime_type'];
    switch ($this->mime_type) {
      case 'printversion' : {
        include (LAYOUTPATH.'snippets/printversion.php');
      } break;
      case 'html' : {
        $this->debug->write("<br>Include <b>".LAYOUTPATH.$this->user->rolle->gui."</b> in kvwmap.php function output()",4);
        if (basename($this->user->rolle->gui)=='') {
          $this->user->rolle->gui='gui.php';
        }
        include (LAYOUTPATH . $this->user->rolle->gui);
				if($this->alert != ''){
					echo '<script type="text/javascript">alert("'.$this->alert.'");</script>';			# manchmal machen alert-Ausgaben über die allgemeinde Funktioen showAlert Probleme, deswegen am besten erst hier am Ende ausgeben
				}
				if (!empty($this->messages)) {
					$this->output_messages();
				}
      } break;
			case 'overlay_html' : {
				$this->overlaymain = $this->main;
				include (LAYOUTPATH.'snippets/overlay.php');
				if($this->alert != ''){
					echo '<script type="text/javascript">alert("'.$this->alert.'");</script>';			# manchmal machen alert-Ausgaben über die allgemeinde Funktioen showAlert Probleme, deswegen am besten erst hier am Ende ausgeben
				}
				if (!empty($this->messages)) {
					$this->output_messages();
				}
			} break;
      case 'map_ajax' : {
				$this->debug->write("Include <b>".LAYOUTPATH."snippets/map_ajax.php</b> in kvwmap.php function output()",4);
        include (LAYOUTPATH.'snippets/map_ajax.php');
      } break;
      case 'pdf' : {
        $this->formvars['file']=1;
        if ($this->formvars['file']) {
          $htmlstr.='<html><head><title>PDF-Ausgabe</title>';
          $htmlstr.='<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
          $htmlstr.='<META HTTP-EQUIV=REFRESH CONTENT="0; URL='.TEMPPATH_REL.$this->outputfile.'">';
          $htmlstr.='</head><body>';
          $htmlstr.='<BR>Folgende Datei wird automatisch aufgerufen: <a href="'.TEMPPATH_REL.$this->outputfile.'">'.$this->outputfile.'</a>';
          $htmlstr.='</body></html>';
          echo $htmlstr;
        }
        else {
          $this->pdf->ezStream();
        }
      } break;
			default : {
				if ($this->formvars['format'] != '') {
					include('formatter.php');
					$this->formatter = new formatter($this->qlayersetParamStrip(), $this->formvars['format'], $this->formvars['content_type'], $this->formvars['callback']);
		    	echo utf8_encode($this->formatter->output());
				}
			}
    }
  } # end of function output
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

  function execSQL($sql,$debuglevel, $loglevel) {
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
      $query=mysql_query($sql,$this->dbConn);
      #echo $sql;
      if ($query==0) {
        $ret[0]=1;
        $ret[1]="<b>Fehler bei SQL Anweisung:</b><br>".$sql."<br>".mysql_error($this->dbConn);
        $this->debug->write($ret[1],$debuglevel);
        if ($logsql) {
          $this->logfile->write("#".$ret[1]);
        }
      }
      else {
        $ret[0]=0;
        $ret[1]=$query;
        if ($logsql) {
          $this->logfile->write($sql.';');
        }
        $this->debug->write(date('H:i:s')."<br>".$sql,$debuglevel);
      }
      $ret[2]=$sql;
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

	function user($login_name,$id,$database) {
		global $debug;
		$this->debug=$debug;
		$this->database=$database;
		if($login_name){
			$this->login_name=$login_name;
			$this->readUserDaten(0,$login_name);
			$this->remote_addr=getenv('REMOTE_ADDR');
		}
		else{
			$this->id = $id;
			$this->readUserDaten($id,0);
		}
	}

	function readUserDaten($id, $login_name) {
		$where = array();
		if ($id > 0) array_push($where, "ID = " . $id);
		if ($login_name != '') array_push($where, "login_name LIKE '" . $login_name . "'");
		$sql = "
			SELECT
				*
			FROM
				user
			WHERE " .
				implode(" AND ", $where) . "
		";
		#echo '<br>Sql: ' . $sql;

		$this->debug->write("<p>file:users.php class:user->readUserDaten - Abfragen des Namens des Benutzers:<br>" . $sql, 4);
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
  }

  function getLastStelle() {
    $sql = 'SELECT stelle_id FROM user WHERE ID='.$this->id;
    $this->debug->write("<p>file:users.php class:user->getLastStelle - Abfragen der zuletzt genutzten Stelle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs=mysql_fetch_array($query);
    return $rs['stelle_id'];
  }

	function StellenZugriff($stelle_id) {
		$this->Stellen=$this->getStellen($stelle_id);
		if (count($this->Stellen['ID'])>0) {
			return 1;
		}
		return 0;
	}

	function getStellen($stelle_ID) {
		$sql = "
			SELECT
				s.ID,
				s.Bezeichnung
			FROM
				stelle AS s,
				rolle AS r
			WHERE
				s.ID = r.stelle_id AND
				r.user_id = " . $this->id .
				($stelle_ID > 0 ? " AND s.ID = " . $stelle_ID : "") . "
				AND (
					('" . date('Y-m-d h:i:s') . "' >= s.start AND '" . date('Y-m-d h:i:s') . "' <= s.stop)
					OR
					(s.start = '0000-00-00 00:00:00' AND s.stop = '0000-00-00 00:00:00')
				)
			ORDER BY
				Bezeichnung;
		";

		#echo '<br>sql: ' . $sql;
		$this->debug->write("<p>file:users.php class:user->getStellen - Abfragen der Stellen die der User einnehmen darf:<br>".$sql,4);
		$query=mysql_query($sql,$this->database->dbConn);
		if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
		while($rs=mysql_fetch_array($query)) {
			$stellen['ID'][]=$rs['ID'];
			$stellen['Bezeichnung'][]=$rs['Bezeichnung'];
		}
		return $stellen;
	}

	function clientIpIsValide($remote_addr) {
    # Prüfen ob die übergebene IP Adresse zu den für den Nutzer eingetragenen Adressen passt
    $ips=explode(';',$this->ips);
    foreach ($ips AS $ip) {
      if (trim($ip)!='') {
        $ip=trim($ip);
				if(!is_numeric(array_pop(explode('.', $ip))))$ip = gethostbyname($ip);			# für dyndns-Hosts
        if (in_subnet($remote_addr, $ip)) {
          $this->debug->write('<br>IP:'.$remote_addr.' paßt zu '.$ip,4);
          #echo '<br>IP:'.$remote_addr.' paßt zu '.$ip;
          return 1;
        }
      }
    }
    return 0;
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
		$this->debug=$debug;
		$this->id=$id;
		$this->database=$database;
		$this->Bezeichnung=$this->getName();
		$this->readDefaultValues();
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
    $this->debug->write("<p>file:stelle.php class:stelle->readDefaultValues - Abfragen der Default Parameter der Karte zur Stelle:<br>".$sql,4);
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs=mysql_fetch_array($query);    
    $this->MaxGeorefExt=ms_newRectObj();
    $this->MaxGeorefExt->setextent($rs['minxmax'],$rs['minymax'],$rs['maxxmax'],$rs['maxymax']);
    $this->epsg_code=$rs["epsg_code"];
    $this->pgdbhost = ($rs["pgdbhost"] == 'PGSQL_PORT_5432_TCP_ADDR') ? getenv('PGSQL_PORT_5432_TCP_ADDR') : $rs["pgdbhost"];
    $this->pgdbname=$rs["pgdbname"];
    $this->pgdbuser=$rs["pgdbuser"];
    $this->pgdbpasswd=$rs["pgdbpasswd"];
    $this->protected=$rs["protected"];
    //---------- OWS Metadaten ----------//
    $this->ows_title=$rs["ows_title"];
    $this->ows_abstract=$rs["ows_abstract"];
    $this->wms_accessconstraints=$rs["wms_accessconstraints"];
    $this->ows_contactperson=$rs["ows_contactperson"];
    $this->ows_contactorganization=$rs["ows_contactorganization"];
    $this->ows_contactelectronicmailaddress=$rs["ows_contactemailaddress"];
    $this->ows_contactposition=$rs["ows_contactposition"];
    $this->ows_fees=$rs["ows_fees"];
    $this->ows_srs=$rs["ows_srs"];
    $this->check_client_ip=$rs["check_client_ip"];
    $this->checkPasswordAge=$rs["check_password_age"];
    $this->allowedPasswordAge=$rs["allowed_password_age"];
    $this->useLayerAliases=$rs["use_layer_aliases"];
		$this->selectable_layer_params = $rs['selectable_layer_params'];
		$this->hist_timestamp=$rs["hist_timestamp"];
		$this->default_user_id = $rs['default_user_id'];
  }

  function checkClientIpIsOn() {
    $sql ='SELECT check_client_ip FROM stelle WHERE ID = '.$this->id;
    $this->debug->write("<p>file:stelle.php class:stelle->checkClientIpIsOn- Abfragen ob IP's der Nutzer in der Stelle getestet werden sollen<br>".$sql,4);
    #echo '<br>'.$sql;
    $query=mysql_query($sql,$this->database->dbConn);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs=mysql_fetch_array($query);
    if ($rs['check_client_ip']=='1') {
      return 1;
    }
    return 0;
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
			return 1;
		}else return 0;
  }

  function setSelectedButton($selectedButton) {
    $this->selectedButton=$selectedButton;
    # Eintragen des aktiven Button
    $sql ='UPDATE rolle SET selectedButton="'.$selectedButton.'"';
    $sql.=' WHERE user_id='.$this->user_id.' AND stelle_id='.$this->stelle_id;
    $this->debug->write("<p>file:rolle.php class:rolle->setSelectedButton - Speichern des zuletzt gewählten Buttons aus dem Kartenfensters:",4);
    $this->database->execSQL($sql,4, $this->loglevel);
    return 1;
  }

  function saveSettings($extent) {
    $sql ='UPDATE rolle SET minx='.$extent->minx.',miny='.$extent->miny;
    $sql.=',maxx='.$extent->maxx.',maxy='.$extent->maxy;
    $sql.=' WHERE user_id='.$this->user_id.' AND stelle_id='.$this->stelle_id;
    # echo $sql;
    $this->debug->write("<p>file:rolle.php class:rolle function:saveSettings - Speichern der Einstellungen zur Rolle:",4);
    $this->database->execSQL($sql,4, $this->loglevel);
    return 1;
  }

  function setConsumeActivity($time,$activity,$prevtime) {
    if (LOG_CONSUME_ACTIVITY==1) {
      # function setzt eine Verbraucheraktivität (den Zugriff auf Layer oder Daten)
      # Starten der Transaktion
      $sql ='START TRANSACTION';
      $ret=$this->database->execSQL($sql,4, 1);
      if ($ret[0]) {
        # Fehler bei Datenbankanfrage
        $ret[1]='<br>Die Transaktion zur Eintragung der Verbraucheraktivität konnte gestartet werden.<br>'.$ret[1];
      }
      else {
        # Eintragen der Consume Activity
        $sql ='INSERT INTO u_consume SET';
        $sql.=' user_id='.$this->user_id;
        $sql.=', stelle_id='.$this->stelle_id;
        $sql.=', time_id="'.$time.'"';
        $sql.=',activity="'.$activity.'"';
        if ($prevtime=="0000-00-00 00:00:00" OR $prevtime=='') {
          $prevtime=$time;
        }
        $sql.=',prev="'.$prevtime.'"';        
        $sql.=', nimagewidth='.$this->nImageWidth.',nimageheight='.$this->nImageHeight;
				$sql.=", epsg_code='".$this->epsg_code."'";
        $sql.=', minx='.$this->oGeorefExt->minx.', miny='.$this->oGeorefExt->miny;
        $sql.=', maxx='.$this->oGeorefExt->maxx.', maxy='.$this->oGeorefExt->maxy;
        #echo $sql;
        $ret=$this->database->execSQL($sql,4, 1);
        
        if ($ret[0]) {
          # Fehler bei Datenbankanfrage
          $errmsg.='<br>Die Verbraucheraktivität konnte nicht eingetragen werden.<br>'.$ret[1];
        }
        if($activity != 'print' AND $activity != 'print_preview'){    # bei der Druckvorschau und dem PDF-Export zwar loggen aber nicht in die History aufnehmen
          $this->newtime = $time;
          $ret = $this->set_last_time_id($time);
          if ($ret[0]) {
            # Fehler bei Datenbankanfrage
            $errmsg.='<br>Die Verbraucheraktivität konnte nicht eingetragen werden.<br>'.$ret[1];
          }
        }
        
        # Abfragen der aktiven Layer
        $ret=$this->getAktivLayer(array(1,2),array(),1);
        if ($ret[0]) {
          # Fehler bei Datenbankanfrage
          $errmsg.='<br>Fehler bei der Abfrage der aktiven Layer.<br>'.$ret[1];
        }
        else {
          $layer=$ret[1];
          # Eintragung des Zugriffs auf die angeschalteten Layer
          for ($i=0;$i<count($layer);$i++) {
            # !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            # Hier eventuell später mal einbauen, dass geprüft wird, ob die Layer wirklich verfügbar sind.
            # Wichtig wird das besonders für externe Datenquellen wie fremde WMS oder WFS Layer
            # Die dürfen nicht mit abgerechnet werden, wenn sie beim Client nicht erscheinen.
            # bzw. nicht geliefert werden
            $sql ='INSERT INTO u_consume2layer SET';
            $sql.=' user_id='.$this->user_id;
            $sql.=', stelle_id='.$this->stelle_id;
            $sql.=', time_id="'.$time.'"';
            $sql.=', layer_id='.$layer[$i];
            $ret=$this->database->execSQL($sql,4, 1);
            if ($ret[0]) {
              # Fehler bei Datenbankanfrage
              $errmsg.='<br>Die Verbraucheraktivität für den Zugiff auf den Layer: '.$layer[$i].' konnte nicht eingetragen werden.<br>'.$ret[1];
            }
          } # ende eintragen aktiver Layer
        } # ende erfolgreiches Abfragen der aktiven Layer
      } # ende kartenausschnitt loggen
      if ($errmsg!='') {
        # Es sind Fehler innerhalb der Transaktion aufgetreten, Abbrechen der Transaktion
        $sql ='ROLLBACK TRANSACTION';
        $ret=$this->database->execSQL($sql,4, 1);
        if ($ret[0]) {
          # Fehler bei Datenbankanfrage
          $errmsg.='<br>Die Transaktion zum Eintragen der Verbraucheraktivität konnte nicht abgebrochen werden.<br>'.$ret[1];
        }
        $ret[0]=1;
        $ret[1]=$errmsg;
      }
      else {
        # Es sind keine Fehler innerhalb der Transaktion aufgetreten, Erfolgreich abschließen der Transaktion
        $sql ='COMMIT';
        $ret=$this->database->execSQL($sql,4, 1);
        if ($ret[0]) {
          # Fehler bei Datenbankanfrage
          $errmsg.='<br>Die Transaktion zum Eintragen der Verbraucheraktivität konnte nicht erfolgreich abgeschlossen werden.<br>'.$ret[1];
        }
        $ret[0]=0;
        $ret[1]='<br>Verbraucheraktivität erfolgreich eingetragen.';
      }
    }
    else {
      $ret[0]=0;
      $ret[1]='<br>Funktion zur Speicherung der Verbraucheraktivitäten ist ausgeschaltet (LOG_CONSUME_ACTIVITY).';
    }
    return $ret;
  }

  function set_last_time_id($time){
    # Eintragen der last_time_id
    $sql = 'UPDATE rolle SET last_time_id="'.$time.'"';
    $sql.= ' WHERE user_id = '.$this->user_id.' AND stelle_id = '.$this->stelle_id;
    #echo $sql;
    $ret=$this->database->execSQL($sql,4, 1);
    return $ret;
  }

  function getAktivLayer($aktivStatus,$queryStatus,$logconsume) {
    # Abfragen der zu loggenden Layer der Rolle
    $sql ='SELECT r2ul.layer_id FROM u_rolle2used_layer AS r2ul';
    if ($logconsume) {
      $sql.=',used_layer AS ul,layer AS l,stelle AS s';
    }
    $sql.=' WHERE r2ul.user_id='.$this->user_id.' AND r2ul.stelle_id='.$this->stelle_id;
    if ($logconsume) {
      $sql.=' AND r2ul.layer_id=ul.Layer_ID AND r2ul.stelle_id=ul.Stelle_ID';
      $sql.=' AND ul.Layer_ID=l.Layer_ID AND ul.Stelle_ID=s.ID';
      $sql.=' AND (s.logconsume="1"';
      $sql.=' OR l.logconsume="1"';
      $sql.=' OR ul.logconsume="1"';
      $sql.=' OR r2ul.logconsume="1")';
    }
    $anzaktivStatus=count($aktivStatus);
    if ($anzaktivStatus>0) {
      $sql.=' AND r2ul.aktivStatus IN ("'.$aktivStatus[0].'"';
      for ($i=1;$i<$anzaktivStatus;$i++) {
        $sql.=',"'.$aktivStatus[$i].'"';
      }
      $sql.=')';
    }
    $anzqueryStatus=count($queryStatus);
    if ($anzqueryStatus>0) {
      $sql.=' AND r2ul.queryStatus IN ("'.$queryStatus[0].'"';
      for ($i=1;$i<$anzqueryStatus;$i++) {
        $sql.=',"'.$queryStatus[$i].'"';
      }
      $sql.=')';
    }
    #echo $sql;
    $this->debug->write("<p>file:rolle.php class:rolle->getAktivLayer - Abfragen der aktiven Layer zur Rolle:<br>".$sql,4);
    $queryret=$this->database->execSQL($sql,4, 0);
    if ($queryret[0]) {
      # Fehler bei Datenbankanfrage
      $ret[0]=1;
      $ret[1]='<br>Die aktiven Layer konnten nicht abgefragt werden.<br>'.$ret[1];
    }
    else {
      while ($rs=mysql_fetch_array($queryret[1])) {
        $layer[]=$rs['layer_id'];
      }
      $ret[0]=0;
      $ret[1]=$layer;
    }
    return $ret;
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

  function setClientEncoding() {
    $sql ="SET CLIENT_ENCODING TO '".POSTGRES_CHARSET."';";
		$ret=$this->execSQL($sql, 4, 0);
    if ($ret[0]) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    return $ret[1];    	
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
			#echo "<br>".$sql;
			if (stristr($sql, 'SELECT')) {
				$sql = "SET datestyle TO 'German';" . $sql;
			};
			if ($this->schema != ''){
				$sql = "SET search_path = " . $this->schema . ", public;" . $sql;
			}
			if ($suppress_error_msg) {
				$query = @pg_query($this->dbConn, $sql);
			}
			else {
				$query = @pg_query($this->dbConn, $sql);
			}

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

	function read_epsg_codes($order = true){
    global $supportedSRIDs;
    $sql ="SELECT spatial_ref_sys.srid, coalesce(alias, substr(srtext, 9, 35)) as srtext, minx, miny, maxx, maxy FROM spatial_ref_sys ";
    $sql.="LEFT JOIN spatial_ref_sys_alias ON spatial_ref_sys_alias.srid = spatial_ref_sys.srid";
    # Wenn zu unterstützende SRIDs angegeben sind, ist die Abfrage diesbezüglich eingeschränkt
    $anzSupportedSRIDs = count($supportedSRIDs);
    if ($anzSupportedSRIDs > 0) {
      $sql.=" WHERE spatial_ref_sys.srid IN (".implode(',', $supportedSRIDs).")";
    }
    if($order)$sql.=" ORDER BY srtext";
    #echo $sql;		
    $ret = $this->execSQL($sql, 4, 0);		
    if($ret[0]==0){
			$i = 0;
      while($row = pg_fetch_assoc($ret[1])){
				$epsg_codes[$row['srid']] = $row;
				$i++;
      }
    }
    return $epsg_codes;
  }

	function getBezeichnungFromPosition($position, $epsgcode) {
    $this->debug->write("<p>kataster.php Flur->getBezeichnungFromPosition:",4);
		$sql ="SELECT gm.bezeichnung as gemeindename, fl.gemeindezugehoerigkeit_gemeinde gemeinde, gk.bezeichnung as gemkgname, fl.land::text||fl.gemarkungsnummer::text as gemkgschl, fl.flurnummer as flur, CASE WHEN fl.nenner IS NULL THEN fl.zaehler::text ELSE fl.zaehler::text||'/'||fl.nenner::text end as flurst, s.bezeichnung as strasse, l.hausnummer ";
    $sql.="FROM alkis.ax_gemarkung as gk, alkis.ax_gemeinde as gm, alkis.ax_flurstueck as fl ";
		$sql.="LEFT JOIN alkis.ax_lagebezeichnungmithausnummer l ON l.gml_id = ANY(fl.weistauf) ";
		$sql.="LEFT JOIN alkis.ax_lagebezeichnungkatalogeintrag s ON l.kreis=s.kreis AND l.gemeinde=s.gemeinde AND s.lage = lpad(l.lage,5,'0') ";
    $sql.="WHERE gk.gemarkungsnummer = fl.gemarkungsnummer AND gm.kreis = fl.gemeindezugehoerigkeit_kreis AND gm.gemeinde = fl.gemeindezugehoerigkeit_gemeinde ";
    $sql.=" AND ST_WITHIN(st_transform(st_geomfromtext('POINT(".$position['rw']." ".$position['hw'].")',".$epsgcode."), ".EPSGCODE_ALKIS."),fl.wkb_geometry) ";
		$sql.= $this->build_temporal_filter(array('gk', 'gm', 'fl'));
    #echo $sql;
    $ret=$this->execSQL($sql,4, 0);
    if ($ret[0]!=0) {
      $ret[1]='Fehler bei der Abfrage der Datenbank.'.$ret[1];
    }
    else {
      if (pg_num_rows($ret[1])>0) {
        $ret[1]=pg_fetch_assoc($ret[1]);
      }
    }
    return $ret;
  }

	function build_temporal_filter($tablenames){
		$timestamp = rolle::$hist_timestamp;
		if($timestamp == ''){
			foreach($tablenames as $tablename){
				$filter .= ' AND '.$tablename.'.endet IS NULL ';
			}
		}
		else{
			foreach($tablenames as $tablename){
				$filter .= ' AND '.$tablename.'.beginnt <= \''.$timestamp.'\' and (\''.$timestamp.'\' < '.$tablename.'.endet or '.$tablename.'.endet IS NULL) ';
			}
		}
		return $filter;
	}

  function close() {
    $this->debug->write("<br>PostgreSQL Verbindung mit ID: ".$this->dbConn." schließen.",4);
    return pg_close($this->dbConn);
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
    $this->debug=$debug;
    $this->Stelle_ID=$Stelle_ID;
    $this->User_ID=$User_ID;
		$this->rolle = new rolle($User_ID, $Stelle_ID, $database);
		$this->database=$database;
  }

	function read_ReferenceMap() {
    $sql ='SELECT r.* FROM referenzkarten AS r, stelle AS s WHERE r.ID=s.Referenzkarte_ID';
    $sql.=' AND s.ID='.$this->Stelle_ID;
    $this->debug->write("<p>file:kvwmap class:db_mapObj->read_ReferenceMap - Lesen der Referenzkartendaten:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { $this->debug->write("<br>Abbruch Zeile: ".__LINE__,4); return 0; }
    $rs=mysql_fetch_array($query);
    $this->referenceMap=$rs;
    return $rs;
  }

  function read_Layer($withClasses, $useLayerAliases = false, $groups = NULL){
		global $language;

		if($language != 'german') {
			$name_column = "
			CASE
				WHEN l.`Name_" . $language . "` != \"\" THEN l.`Name_" . $language . "`
				ELSE l.`Name`
			END AS Name";
		}
		else
			$name_column = "l.Name";

		$sql = "
			SELECT DISTINCT
				coalesce(rl.transparency, ul.transparency, 100) as transparency, rl.`aktivStatus`, rl.`queryStatus`, rl.`gle_view`, rl.`showclasses`, rl.`logconsume`, rl.`rollenfilter`,
				ul.`queryable`, COALESCE(rl.drawingorder, ul.drawingorder) as drawingorder, ul.`minscale`, ul.`maxscale`, ul.`offsite`, ul.`postlabelcache`, ul.`Filter`, ul.`template`, ul.`header`, ul.`footer`, ul.`symbolscale`, ul.`logconsume`, ul.`requires`, ul.`privileg`, ul.`export_privileg`,
				l.Layer_ID," .
				$name_column . ",
				l.alias,
				l.Datentyp, l.Gruppe, l.pfad, l.Data, l.tileindex, l.tileitem, l.labelangleitem, coalesce(rl.labelitem, l.labelitem) as labelitem,
				l.labelmaxscale, l.labelminscale, l.labelrequires, l.connection, l.printconnection, l.connectiontype, l.classitem, l.classification, l.filteritem,
				l.cluster_maxdistance, l.tolerance, l.toleranceunits, l.processing, l.epsg_code, l.ows_srs, l.wms_name, l.wms_server_version,
				l.wms_format, l.wms_auth_username, l.wms_auth_password, l.wms_connectiontimeout, l.selectiontype, l.logconsume,l.metalink, l.status,
				g.*
			FROM
				u_rolle2used_layer AS rl,
				used_layer AS ul,
				layer AS l,
				u_groups AS g,
				u_groups2rolle as gr
			WHERE
				rl.stelle_id = ul.Stelle_ID AND
				rl.layer_id = ul.Layer_ID AND
				l.Layer_ID = ul.Layer_ID AND
				(ul.minscale != -1 OR ul.minscale IS NULL) AND l.Gruppe = g.id AND rl.stelle_ID = " . $this->Stelle_ID . " AND rl.user_id = " . $this->User_ID . " AND
				gr.id = g.id AND
				gr.stelle_id = " . $this->Stelle_ID . " AND
				gr.user_id = " . $this->User_ID;

		if($groups != NULL){
			$sql.=' AND g.id IN ('.$groups.')';
		}
    if($this->nurAufgeklappteLayer){
      $sql.=' AND (rl.aktivStatus != "0" OR gr.status != "0" OR ul.requires != "")';
    }
    if($this->nurAktiveLayer){
      $sql.=' AND (rl.aktivStatus != "0")';
    }
		if($this->OhneRequires){
      $sql.=' AND (ul.requires IS NULL)';
    }
    if($this->nurFremdeLayer){			# entweder fremde (mit host=...) Postgis-Layer oder aktive nicht-Postgis-Layer
    	$sql.=' AND (l.connection like "%host=%" AND l.connection NOT like "%host=localhost%" OR l.connectiontype != 6 AND rl.aktivStatus != "0")';
    }
    $sql.=' ORDER BY drawingorder';
    #echo $sql;
    $this->debug->write("<p>file:kvwmap class:db_mapObj->read_Layer - Lesen der Layer der Rolle:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__."<br>wegen: ".$sql."<p>".INFO1; return 0; }
    $layer = array();
    $this->disabled_classes = $this->read_disabled_classes();
		$i = 0;
    while ($rs=mysql_fetch_assoc($query)){
			if($rs['rollenfilter'] != ''){		// Rollenfilter zum Filter hinzufügen
				if($rs['Filter'] == ''){
					$rs['Filter'] = '('.$rs['rollenfilter'].')';
				}
				else {
					$rs['Filter'] = str_replace(' AND ', ' AND ('.$rs['rollenfilter'].') AND ', $rs['Filter']);
				}
			}
			if($rs['alias'] == '' OR !$useLayerAliases){
				$rs['alias'] = $rs['Name'];
			}
			$rs['id'] = $i;
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
			if ($withClasses == 2 OR $rs['requires'] != '' OR ($withClasses == 1 AND $rs['aktivStatus'] != '0')) {
				# bei withclasses == 2 werden für alle Layer die Klassen geladen,
				# bei withclasses == 1 werden Klassen nur dann geladen, wenn der Layer aktiv ist
				$rs['Class']=$this->read_Classes($rs['Layer_ID'], $this->disabled_classes, false, $rs['classification']);
			}
			if($rs['maxscale'] > 0)$rs['maxscale'] = $rs['maxscale']+0.3;
			if($rs['minscale'] > 0)$rs['minscale'] = $rs['minscale']-0.3;
			$layer[$i]=$rs;			
			$layer['layer_ids'][$rs['Layer_ID']] =& $layer[$i];		# damit man mit einer Layer-ID als Schlüssel auf dieses Array zugreifen kann
			$i++;
    }
    return $layer;
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
				NULLIF(classification, '') IS NULL,
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

  function read_RollenLayer($id = NULL, $typ = NULL){
		$sql = "SELECT DISTINCT l.*, l.Name as alias, g.Gruppenname, -l.id AS Layer_ID, 1 as showclasses, CASE WHEN Typ = 'import' THEN 1 ELSE 0 END as queryable, concat('(', rollenfilter, ')') as Filter from rollenlayer AS l, u_groups AS g";		
    $sql.= ' WHERE l.Gruppe = g.id AND l.stelle_id='.$this->Stelle_ID.' AND l.user_id='.$this->User_ID;
    if($id != NULL){
    	$sql .= ' AND l.id = '.$id;
    }
  	if($typ != NULL){
    	$sql .= ' AND l.Typ = \''.$typ.'\'';
    }
    $this->debug->write("<p>file:kvwmap class:db_mapObj->read_RollenLayer - Lesen der RollenLayer:<br>".$sql,4);
    $query=mysql_query($sql);
    if ($query==0) { echo "<br>Abbruch in ".$PHP_SELF." Zeile: ".__LINE__."<br>wegen: ".$sql."<p>".INFO1; return 0; }
    $Layer = array();
    while ($rs=mysql_fetch_array($query)) {
      $rs['Class']=$this->read_Classes(-$rs['id'], $this->disabled_classes);
      $Layer[]=$rs;
    }
    return $Layer;
  }
}

class Flur {

  var $FlurID;
  var $database;

	function Flur($GemID,$GemkgID,$FlurID,$database) {
    # constructor
    global $debug;
    $this->debug=$debug;
    $this->GemID=$GemID;
    $this->GemkgID=$GemkgID;
    $this->FlurID=$FlurID;
    $this->database=$database;
    $this->LayerName=LAYERNAME_FLUR;
  }

	function getBezeichnungFromPosition($position, $epsgcode){
		return $this->database->getBezeichnungFromPosition($position, $epsgcode);
  }
}
?>
