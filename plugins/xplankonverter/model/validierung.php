<?php
###################################################################
# kvwmap - Kartenserver für Kreisverwaltungen                     #
###################################################################
# Lizenz                                                          #
#                                                                 #
# Copyright (C) 2004  Peter Korduan                               #
#                                                                 #
# This program is free software; you can redistribute it and/or   #
# modify it under the terms of the GNU General Public License as  #
# published by the Free Software Foundation; either version 2 of  #
# the License, or (at your option) any later version.             #
#                                                                 #
# This program is distributed in the hope that it will be useful, #
# but WITHOUT ANY WARRANTY; without even the implied warranty of  #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the    #
# GNU General Public License for more details.                    #
#                                                                 #
# You should have received a copy of the GNU General Public       #
# License along with this program; if not, write to the Free      #
# Software Foundation, Inc., 59 Temple Place, Suite 330, Boston,  #
# MA 02111-1307, USA.                                             #
#                                                                 #
# Kontakt:                                                        #
# peter.korduan@gdi-service.de                                    #
# stefan.rahn@gdi-service.de                                      #
###################################################################
#############################
# Klasse Validierung #
#############################

class Validierung extends PgObject {


	static $schema = 'xplankonverter';
	static $tableName = 'validierungen';
	static $write_debug = false;

	function __construct($gui) {
		#echo '<br>Create new Object Validierung';
		$this->PgObject($gui, Validierung::$schema, Validierung::$tableName);
		$this->konvertierung_id = 0;
	}

	public static	function find_by_id($gui, $by, $id) {
		$validierung = new Validierung($gui);
		$validierung->find_by($by, $id);
		return $validierung;
	}

	public static function delete_by_id($gui, $by, $id) {
		$validierung = new Validierung($gui);
		$validierung->delete_by($by, $id);
	}

	function regel_existiert($regeln) {
		$regeln_existieren = (count($regeln) > 0);
		$validierungsergebnis = new Validierungsergebnis($this->gui);
		$validierungsergebnis->create(
			array(
				'konvertierung_id' => $this->konvertierung_id,
				'validierung_id' => $this->get('id'),
				'status' => ($regeln_existieren ? 'Erfolg' : 'Warnung'),
				'msg' => 'Es sind' . ($regeln_existieren ? ' ' . count($regeln) : ' keine') . ' Regeln zur Konvertierung vorhanden.'
			)
		);
		return $regeln_existieren;
	}

	function sql_ausfuehrbar($regel, $konvertierung_id) {
		$this->debug->show('<br>Validiere ob sql_ausfuehrbar: ', Validierung::$write_debug);
		$ausfuehrbar = true;

		$sql = $regel->get_convert_sql($konvertierung_id);

		# Objekte anlegen
		$result = @pg_query(
			$this->database->dbConn,
			'EXPLAIN ' . $sql
		);

		if (!$result) {
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $this->konvertierung_id,
					'validierung_id' => $this->get('id'),
					'status' => 'Fehler',
					'msg' => 'Regel: ' . $regel->get('name') . ', ' . str_replace("'","''",'SQL: ' . $sql . ' nicht ausführbar.<br>' . @pg_last_error($this->database->dbConn)),
					'regel_id' => $regel->get('id')
				)
			);
			$ausfuehrbar = false;
		}
		$this->debug->show('<br>' . ($ausfuehrbar ? ' sql ist ausführbar.' : ' sql ist nicht ausführbar'), Validierung::$write_debug);
		return $ausfuehrbar;
	}

	function sql_vorhanden($sql, $regel) {
		$this->debug->show('<hr><br>Regel: ' . $regel->get('name') . '<br>Validiere ob sql_vorhanden sql:<br>' . $sql . ' validieren.', Validierung::$write_debug);
		$vorhanden = true;
		if (empty($sql)) {
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $this->konvertierung_id,
					'validierung_id' => $this->get('id'),
					'status' => 'Fehler',
					'msg' => 'Das SQL-Statement ist leer.',
					'regel_id' => $regel->get('id')
				)
			);
			$vorhanden = false;
		}
		$this->debug->show('<br>' . ($vorhanden ? 'sql vorhanden' : 'sql nicht vorhanden'), Validierung::$write_debug);
		return $vorhanden;
	}

	function alle_sql_ausfuehrbar($alle_ausfuehrbar) {
		if ($alle_ausfuehrbar) {
			$this->debug->show('<br>Alle sql ausführbar.', Validierung::$write_debug);
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $this->konvertierung_id,
					'validierung_id' => $this->get('id'),
					'status' => 'Erfolg',
					'msg' => 'Alle Regeln konnten erfolgreich konvertiert werden.'
				)
			);
		}
	}

	/*
	* Funktion findet alle Shape-Objekte, die the_geom = NULL haben.
	* ToDo: Eigentlich müsste hier noch die Shape-Tabelle, bzw. der Shape-Layer extrahiert werden,
	* damit in der Meldung direkt auf die fehlerhaften Objekte verwiesen werden kann.
	* Wenn die Geometrie dann auch änderbar sein soll, müssten die Layerrechte für den shape Layer
	* noch gesetzt werden. Aber dann immer automatisch beim Anlegen des Layers.
	*/
	function geometrie_vorhanden($sql, $regel_id, $sourcetype = 'shape') {
		$this->debug->show('<br>validate ob geometrie_vorhanden mit Ausgangs-sql: ' . $sql, Validierung::$write_debug);
		$geometrie_vorhanden = true;
		$sql = stristr($sql, 'select');
		$this->debug->show('<br>sql von select an: ' . $sql, Validierung::$write_debug);

		# Default Shape
		# ogc_fid is pkey serial (an alternative oid would have to be added to each table after loading it)
		# (position as the default geometry of all objects inheriting from xp_objekt in gmlas
		$gid_or_oid = ($sourcetype == 'gmlas') ? 'ogc_fid' : 'gid';
		$geometry_col = ($sourcetype == 'gmlas') ? 'position' : 'the_geom';
		$search_path  = ($sourcetype == 'gmlas') ? "xplan_gmlas_{$this->gui->user->id}" : "xplan_shapes_{$this->konvertierung_id}";

		# Frage gid mit ab
		$sql = str_ireplace(
			'select',
			"select " . $gid_or_oid . ",",
			$sql
		);
		$this->debug->show('<br>sql mit ' . $gid_or_oid . ': ' . $sql, Validierung::$write_debug);

		# Hänge Where Klauses is null an
		if (strpos(strtolower($sql), 'where') === false) {
			$sql .= ' where ' . $geometry_col .' IS NULL';
		}
		else {
			$sql = str_ireplace(
				'where',
				"WHERE " . $geometry_col . " IS NULL AND ",
				$sql
			);
		}
		$this->debug->show('<br>sql mit where Klausel: ' . $sql, Validierung::$write_debug);

		$sql = "SET search_path=" . $search_path . ", public; " . $sql;

		$this->debug->show('<br>Validiere ob geometrie_vorhanden mit sql:<br>' . $sql, Validierung::$write_debug);

		$result = pg_query($this->database->dbConn, $sql);

		while ($row = pg_fetch_assoc($result)) {
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $this->konvertierung_id,
					'validierung_id' => $this->get('id'),
					'status' => 'Warnung',
					'regel_id' => $regel_id,
					'shape_gid' => $row['gid'],
					'msg' => 'Objekt mit gid=' . $row['gid'] . ' hat keine Geometrie.'
				)
			);
			$geometrie_vorhanden = false;
		}

		$this->debug->show('<br>' . ($geometrie_vorhanden ? ' geometrie vorhanden' : ' geometrie nicht vorhanden'), Validierung::$write_debug);
		return $geometrie_vorhanden;
	}

	/*
	* Prüft ob die im SQL-Teil der Abfrage gelieferten Geometrien valide sind
	* @param object $regel Objekt der Regel
	* @param object $konvertierung Objekt der Konvertierung
	* @return boolean $all_valid true wenn alle valide sind, false wenn nicht.
	*/
	function geometrie_isvalid($regel, $konvertierung) {
		$this->debug->show('<br>Validate geom_isvalid:', Validierung::$write_debug);
		$all_geom_isvalid = true;
		$sourcetype = $regel->is_source_shape_or_gmlas($regel);
		# shape or gmlas?
		$geometry_col = ($sourcetype == 'gmlas') ? 'position' : 'the_geom';

		$sql = $regel->get_convert_sql($konvertierung->get('id'));

		# Extrahiere alles ab select
		$sql = stristr($sql, 'select');
		$this->debug->show('<br>sql von select an: ' . $sql, Validierung::$write_debug);

		if($sourcetype != 'gmlas') {
			# Selektiere gid zur eindeutigen Identifizierung des Datensatzes und st_isvalidreason
			$sql = str_ireplace(
				'select',
				"select
					gid,
					st_isvalidreason(" . $geometry_col . ") validreason,
				",
				$sql
			);
			$this->debug->show('<br>sql mit gid und is_validreason: ' . $sql, Validierung::$write_debug);

		} else {
			# Selektiere gid zur eindeutigen Identifizierung des Datensatzes und st_isvalidreason
			$sql = str_ireplace(
				'select',
				"select
					st_isvalidreason(" . $geometry_col .") validreason,
				",
				$sql
			);
			$this->debug->show('<br>sql mit is_validreason: ' . $sql, Validierung::$write_debug);
		}

	# where klausel für plan hinzufügen
	$sql = str_ireplace(
		'where',
		"where
			NOT st_isvalid(" . $geometry_col . ") AND (
		",
		$sql
		);

		$this->debug->show('<br>sql mit where st_isvalid: ' . $sql, Validierung::$write_debug);

#		$sql = "SET search_path=xplan_shapes_" . $konvertierung->get('id') . ", public; " . substr($sql, 0, stripos($sql, 'returning'));
		$sql = substr($sql, 0, stripos($sql, 'returning')) . ')';

		$this->debug->show('Sql für Prüfung geom_isvalid:<br>' . $sql, Validierung::$write_debug);

		$result = @pg_query(
			$this->database->dbConn,
			$sql
		);

		if (!$result) {
			$this->debug->show('<br>sql ist nicht ausführbar: ' . $sql, Validierung::$write_debug);
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $konvertierung->get('id'),
					'validierung_id' => $this->get('id'),
					'status' => 'Fehler',
					'msg' => 'Regel: ' . $regel->get('name') . ', ' . str_replace("'","''",'SQL: ' . $sql . ' nicht ausführbar.<br>' . @pg_last_error($this->database->dbConn)),
					'user_id' => $this->gui->user->id,
					'regel_id' => $regel->get('id')
				)
			);
		}
		else {
			while ($row = pg_fetch_assoc($result)) {
				$this->debug->show('<br>geometrie mit gid: ' . $row['gid'] . ' ist nicht valid', Validierung::$write_debug);
				$validierungsergebnis = new Validierungsergebnis($this->gui);
				$validierungsergebnis->create(
					array(
						'konvertierung_id' => $konvertierung->get('id'),
						'validierung_id' => $this->get('id'),
						'status' => 'Fehler',
						'regel_id' => $regel->get('id'),
						'shape_gid' => $row['gid'],
						'msg' => 'Regel: ' . $regel->get('name') . '. Objekt mit gid=' . $row['gid'] . ' ist nicht valide. Grund: ' . $row['validreason']
					)
				);
				$all_geom_isvalid = false;
			}
		}

		if ($all_geom_isvalid) {
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $konvertierung->get('id'),
					'validierung_id' => $this->get('id'),
					'status' => 'Erfolg',
					'msg' => 'Alle Geometrien der Regel sind valide.',
					'regel_id' => $regel->get('id')
				)
			);
		}

		$this->debug->show('<br>' . ($all_geom_isvalid ? 'Alle Geometrien sind valid.' : 'Es sind nicht alle Geometrien valid.'), Validierung::$write_debug);
		return $all_geom_isvalid;
	}

	/*
	* Prüft ob die im SQL-Teil der Abfrage gelieferten Geometrien im räumlichen Geltungsbereich
	* des Planes mit plan_gml_id enthalten sind.
	* Wenn nicht wird pro Ausreißer ein Validierungsergebnis mit status = Warnung angelegt
	* wenn die Entfernung <= 100km ist ansonsten mit status = Fehler.
	* Wenn alle innerhalb sind, wird ein Validierungsergebnis mit status = Erfolg angelegt.
	* @param object $regel Objekt der Regel
	* @param object $konvertierung Objekt der Konvertierung
	* @return boolean $all_within_plan true wenn alle drin liegen, false wenn nicht.
	*/
	function geom_within_plan($regel, $konvertierung) {
		$this->debug->show('<br>Validate ob geom_within_plan sql.', Validierung::$write_debug);
		$all_within_plan = true;

		$sql = $regel->get_convert_sql($konvertierung->get('id'));
		$sourcetype = $regel->is_source_shape_or_gmlas($regel);
		# Default Shape (the_geom), gmlas (position)
		$geometry_col = ($sourcetype == 'gmlas') ? 'position' : 'the_geom';

		$plantype = $konvertierung->get_plan()->tableName;

		# Extrahiere alles ab select
		$sql = stristr($sql, 'select');
		$this->debug->show('<br>sql von select an: ' . $sql, Validierung::$write_debug);
		

		# Selektiere gid zur eindeutigen Identifizierung des Datensatzes und within und distance
		#if for gid (shape) or no gid (gmlas)
		# changed to 25832 as otherwise thered be mixed geometry errors
		# TODO Get the Epsg code from $konvertierung
		if($sourcetype != 'gmlas') {
			$sql = str_ireplace(
				'select',
				"select
					gid,
					NOT st_within(" . $geometry_col . ", " . $plantype . ".raeumlichergeltungsbereich) AS ausserhalb,
					st_distance(ST_Transform(" . $geometry_col . ", " . $konvertierung->get('input_epsg') ."), ST_Transform(" . $plantype . ".raeumlichergeltungsbereich, " . $konvertierung->get('input_epsg') ."))/1000 AS distance,
				",
				$sql
			);
			$this->debug->show('<br>sql mit gid, within und distance: ' . $sql, Validierung::$write_debug);
		} else {
			$sql = str_ireplace(
				'select',
				"select
					NOT st_within(" . $geometry_col . ", " . $plantype . ".raeumlichergeltungsbereich) AS ausserhalb,
					st_distance(ST_Transform(" . $geometry_col . ", " . $konvertierung->get('input_epsg') ."), ST_Transform(" . $plantype . ".raeumlichergeltungsbereich, " . $konvertierung->get('input_epsg') ."))/1000 AS distance,
				",
				$sql
			);
			$this->debug->show('<br>sql mit gid, within und distance: ' . $sql, Validierung::$write_debug);
		}

		# tabelle " . $plantype . " hinzufügen
		$sql = str_ireplace(
			'from',
			"from
				xplan_gml." . $plantype . " " . $plantype . ",
			",
			$sql
		);
		$this->debug->show('<br>sql mit " . $plantype . " Tabelle: ' . $sql, Validierung::$write_debug);

		# where klausel für plan hinzufügen
		$sql = str_ireplace(
			'where',
			"where
				" . $plantype . ".konvertierung_id = " . $konvertierung->get('id') . " AND 
				NOT st_within(" . $geometry_col . ", raeumlichergeltungsbereich) AND (
			",
			$sql
		);
		$this->debug->show('<br>sql mit where Klausel für ' . $plantype . ': ' . $sql, Validierung::$write_debug);

		#$sql = "SET search_path=xplan_shapes_" . $konvertierung->get('id') . ", public; " . 
		$sql = substr($sql, 0, stripos($sql, 'returning')) . ')';
		$this->debug->show('<br>sql ohne returning: ' . $sql, Validierung::$write_debug);

		$this->debug->show('Sql für Prüfung geom_within_plan:<br>' . $sql, false);

		$result = @pg_query(
			$this->database->dbConn,
			$sql
		);

		if (!$result) {
			$this->debug->show('<br>sql ist nicht ausführbar: ' . $sql, Validierung::$write_debug);
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $konvertierung->get('id'),
					'validierung_id' => $this->get('id'),
					'status' => 'Fehler',
					'msg' => 'Regel: ' . $regel->get('name') . ', ' . str_replace("'","''",'SQL: ' . $sql . ' nicht ausführbar.<br>' . @pg_last_error($this->database->dbConn)),
					'user_id' => $this->gui->user->id,
					'regel_id' => $regel->get('id')
				)
			);
		}
		else {
			while ($row = pg_fetch_assoc($result)) {
				if ($row['ausserhalb'] == 't') {
					$this->debug->show('geometrie mit gid: ' . $row['gid'] . ' ist außerhalb vom Planbereich', Validierung::$write_debug);
					$validierungsergebnis = new Validierungsergebnis($this->gui);
					if($sourcetype != 'gmlas') {
						# default, e.g. Shape
						$validierungsergebnis->create(
							array(
								'konvertierung_id' => $konvertierung->get('id'),
								'validierung_id' => $this->get('id'),
								'status' => ($row['distance'] > 100 ? 'Fehler' : 'Warnung'),
								'regel_id' => $regel->get('id'),
								'shape_gid' => $row['gid'],
								'msg' => 'Objekt mit gid=' . $row['gid'] . ' ist außerhalb des räumlichen Geltungsbereiches des Planes.' . ($row['distance'] > 100 ? ' Das Objekt ist mehr als 100 km entfernt.' : '')
							)
						);
					} else {
						#GMLAS
						$validierungsergebnis->create(
							array(
								'konvertierung_id' => $konvertierung->get('id'),
								'validierung_id' => $this->get('id'),
								'status' => ($row['distance'] > 100 ? 'Fehler' : 'Warnung'),
								'regel_id' => $regel->get('id'),
								'msg' => 'Objekt ist außerhalb des räumlichen Geltungsbereiches des Planes.' . ($row['distance'] > 100 ? ' Das Objekt ist mehr als 100 km entfernt.' : '')
							)
						);
					}
					$all_within_plan = false;
				}
			}
		}

		if ($all_within_plan) {
			$validierungsergebnis = new Validierungsergebnis($this->gui);
			$validierungsergebnis->create(
				array(
					'konvertierung_id' => $konvertierung->get('id'),
					'validierung_id' => $this->get('id'),
					'status' => 'Erfolg',
					'msg' => 'Alle Objekte der Regel liegen im Planbereich.',
					'regel_id' => $regel->get('id')
				)
			);
		}
		$this->debug->show('<br>' . ($all_within_plan ? 'Alle Geometrien innerhalb des Planbereiches' : 'Nicht alle Geometrien innerhalb des Planbereiches.'), Validierung::$write_debug);
		return $all_within_plan;
	}

	/*
	* ToDo
	*/
	function geom_within_bereich() {
		$this->debug->show('<br>Validiere ob Geometrien in Bereich. (ToDo: Noch zu implementieren)', Validierung::$write_debug);
	}

	function detaillierte_requires_bedeutung($bereich) {
		$success = true;
		if ($bereich->get('detailliertebeschreibung') == '') {
			$msg = 'Im Bereich ' . $bereich->get('nummer') . ' wurde keine detaillierte Bedeutung angegeben, deswegen muss auch keine Bedeutung angegeben werden.';
		}
		else {
			if ($bereich->get('bedeutung') == '') {
				$success = false;
				$msg = 'Da im Bereich ' . $bereich->get('nummer') . ' eine detaillierte Bedeutung angegeben wurde, muss auch eine Bedeutung angegeben werden.';
			}
			else {
				$msg = 'Zur angegebenen detaillierten Bedeutung im Bereich ' . $bereich->get('nummer') . ' wurde auch eine Bedeutung angegeben.';
			}
		}
		$validierungsergebnis = new Validierungsergebnis($this->gui);
		$validierungsergebnis->create(
			array(
				'konvertierung_id' => $this->konvertierung_id,
				'validierung_id' => $this->get('id'),
				'status' => ($success ? 'Erfolg' : 'Fehler'),
				'msg' => $msg
			)
		);
		return $success;
	}

  function doValidate($konvertierung) {
    // validate here
    if (true)
      return array('success' => 'OK');
    else
      return array('success' => 'ERROR', 'error' => 'Validierung fehlgeschlagen');
  }

  function validateKonvertierung($konvertierung,$success,$failure) {
    $result = $this->doValidate($konvertierung);
    if ($result['success'] == 'OK') {
      // status setzen
      $konvertierung->set('status', Konvertierung::$STATUS['KONVERTIERUNG_OK']);
      $konvertierung->update();
      // success callback ausführen
     $success();
    } else {
      // status setzen
      $konvertierung->set('status', Konvertierung::$STATUS['KONVERTIERUNG_ERR']);
      $konvertierung->update();
      // error callback ausfuehren
     $failure($result['error']);
    }
    return;
  }
}

?>
