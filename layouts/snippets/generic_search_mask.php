<?
include(LAYOUTPATH.'languages/generic_search_'.$this->user->rolle->language.'.php');
include_once(SNIPPETS.'/generic_form_parts.php');
$num_colspan = ($this->user->rolle->visually_impaired) ? 3 : 5;
?>
<table width="100%" align="center" border="0" cellspacing="0" cellpadding="3" id="searchmasks">
<?    
			if($searchmask_number > 0){						// es ist nicht die erste Suchmaske, sondern eine weitere hinzugefügte
				$prefix = $searchmask_number.'_'; ?>
				<tr>
					<td align="center" width="100%" colspan="<?php echo $num_colspan; ?>">
						<select name="boolean_operator_<? echo $searchmask_number; ?>">
							<option value="OR" <? if($this->formvars['searchmask_operator'][$searchmask_number] == 'OR')echo 'selected'; ?>><? echo $strOr; ?></option>
							<option value="AND" <? if($this->formvars['searchmask_operator'][$searchmask_number] == 'AND')echo 'selected'; ?>><? echo $strAnd; ?></option>
						</select>
						&nbsp;
						<img src="<?php echo GRAPHICSPATH;?>icon_i.png" onMouseOver="stm(Text1, Style[0], document.getElementById('Tip1'))" onmouseout="htm()">
						<div id="Tip1" style="visibility:hidden;position:absolute;z-index:1000;"></div>
						<!--a href="javascript:close_record('record_<? echo $layer['shape'][$k][$layer['maintable'].'_oid']; ?>');" title="Schlie&szlig;en"><img style="border:none" src="<? echo GRAPHICSPATH."symbol_delete.gif"; ?>"></img></a-->
					</td>
				</tr>
				<?
			}
			else{
?>
				<tr>
					<td width="150px"><span class="fett"><? echo $strAttribute; ?></span></td>
					<td>&nbsp;&nbsp;</td><?php if (!$this->user->rolle->visually_impaired) { ?>
					<td width="100px" align="center"><span class="fett"><? echo $strOperator; ?></span></td>
					<td>&nbsp;&nbsp;</td><?php } ?>
					<td width="150px" align="left"><span class="fett">&nbsp;&nbsp;<? echo $strValue; ?></span></td>
				</tr>

<?		}
			if($this->{'attributes'.$searchmask_number} != NULL){
				$this->attributes = $this->{'attributes'.$searchmask_number};   # dieses Attributarray nehmen, weil eine gespeicherte Suche geladen wurde
			}
			$last_attribute_index = NULL;
			$z_index = 500;
			for($i = 0; $i < count($this->attributes['name']); $i++) {
				if ($this->attributes['mandatory'][$i] == '' or $this->attributes['mandatory'][$i] > -1) {
					$operator = $this->formvars[$prefix . 'operator_' . $this->attributes['name'][$i]];
					
	        if($this->attributes['form_element_type'][$i] != 'dynamicLink' AND !($this->attributes['form_element_type'][$i] == 'SubFormFK' AND $this->attributes['type'][$i] == 'not_saveable')){					
						if($this->attributes['group'][$i] != $this->attributes['group'][$last_attribute_index]){		# wenn die vorige Gruppe anders ist: ...
							$explosion = explode(';', $this->attributes['group'][$i]);
							if($explosion[1] != '')$collapsed = true;else $collapsed = false;
							$groupname = $explosion[0];
							if($last_attribute_index !== NULL){		# ... Tabelle schliessen, wenn es nicht die erste Gruppe ist
								echo '</table></td></tr>';
							}
							$last_attribute_index = $i;					
							# ... Tabelle beginnen
							echo '<tr>
											<td colspan="' . $num_colspan . '" width="100%">
												<table cellpadding="3" cellspacing="0" width="100%" id="colgroup'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'"  style="'; if(!$collapsed)echo 'display:none;'; echo ' border:1px solid grey">
													<tr>
														<td width="100%" bgcolor="'.BG_GLEATTRIBUTE.'" colspan="2">&nbsp;<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'group'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'\').style.display=\'\';document.getElementById(\'colgroup'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'\').style.display=\'none\';"><img border="0" src="'.GRAPHICSPATH.'/plus.gif"></a>&nbsp;<span class="fett">'.$groupname.'</span></td>
													</tr>
												</table>
												<table cellpadding="3" cellspacing="0" width="100%" id="group'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'" style="'; if($collapsed)echo 'display:none;'; echo 'border:1px solid grey">
													<tr>
														<td style="border-bottom:1px dotted grey" bgcolor="'.BG_GLEATTRIBUTE.'" colspan="' . $num_colspan . '">&nbsp;<a href="javascript:void(0);" onclick="javascript:document.getElementById(\'group'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'\').style.display=\'none\';document.getElementById(\'colgroup'.$layer['Layer_ID'].'_'.$i.'_'.$searchmask_number.'\').style.display=\'\';"><img border="0" src="'.GRAPHICSPATH.'/minus.gif"></a>&nbsp;<span class="fett">'.$groupname.'</span></td>
													</tr>';
					}
				
	          ?><tr>
	            <td width="40%"><?
	              if($this->attributes['alias'][$i] != ''){
	                echo $this->attributes['alias'][$i];
	              }
	              else{
	                echo $this->attributes['name'][$i];
	              }
								$date_types = array('date' => 'TT.MM.JJJJ', 'timestamp' => 'TT.MM.JJJJ hh:mm:ss', 'time' => 'hh:mm:ss');
								if(array_key_exists($this->attributes['type'][$i], $date_types)){
	              ?>
	                <a href="javascript:;" onclick="new CalendarJS().init('<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>', '<? echo $this->attributes['type'][$i]; ?>');"><img title="<? echo $date_types[$this->attributes['type'][$i]]; ?>" src="<? echo GRAPHICSPATH; ?>calendarsheet.png" border="0"></a><div id="calendar_<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>" class="calendar"></div>
	              <?
	              }
	          ?></td>
	            <td>&nbsp;&nbsp;</td>
							<?php
								if (
									$operator == 'LIKE' OR					# ähnlich vorauswählen
									(
										in_array(
											$this->attributes['form_element_type'][$i],
											array('Text','Textfeld')
										) AND
										in_array(
											$this->attributes['type'][$i],
											array('varchar', 'text')
										) AND
										$operator == ''
									)
								) $operator = 'LIKE';
							?>
							<?php
							if (!$this->user->rolle->visually_impaired) { ?>
	            <td width="100px">
	              <select  style="width:75px" <? if(count($this->attributes['enum_value'][$i]) == 0) { ?>onchange="operatorchange(<? echo $this->formvars['selected_layer_id']; ?>, '<? echo $this->attributes['name'][$i]; ?>', <? echo $searchmask_number; ?>);" id="<? echo $prefix; ?>operator_<? echo $this->attributes['name'][$i]; ?>" <? } ?> name="<? echo $prefix; ?>operator_<? echo $this->attributes['name'][$i]; ?>">
	                <option title="<? echo $strEqualHint; ?>" value="=" <? if($operator == '='){ echo 'selected';} ?> >=</option>
								<? if($this->attributes['type'][$i] != 'geometry'){ ?>
	                <option title="<? echo $strNotEqualHint; ?>" value="!=" <? if($operator == '!='){ echo 'selected';} ?> >!=</option>
								<? }
									if(!in_array($this->attributes['type'][$i], array('bool'))){		# bei boolean und Array-Datentypen nur = und !=
										if($this->attributes['type'][$i] != 'geometry'){ ?>
									<? if(!in_array($this->attributes['type'][$i], array('text'))){ ?>
	                <option title="<? echo $strLowerHint; ?>" value="<" <? if($operator == '<'){ echo 'selected';} ?> ><</option>
	                <option title="<? echo $strGreaterHint; ?>" value=">" <? if($operator == '>'){ echo 'selected';} ?> >></option>
									<option title="<? echo $strLowerEqualHint; ?>" value="<" <? if($operator == '<='){ echo 'selected';} ?> ><=</option>
	                <option title="<? echo $strGreaterEqualHint; ?>" value=">" <? if($operator == '>='){ echo 'selected';} ?> >>=</option>
									<? }
									if($this->attributes['form_element_type'][$i] == 'Autovervollständigungsfeld' OR !in_array($this->attributes['type'][$i], array('int2', 'int4', 'int8', 'numeric', 'float4', 'float8', 'date', 'timestampt', 'timestamptz'))){ ?>
	                <option title="<? echo $strLikeHint; ?>" value="LIKE" <? if($operator == 'LIKE'){ echo 'selected';} ?> ><? echo $strLike; ?></option>
	                <option title="<? echo $strLikeHint; ?>" value="NOT LIKE" <? if($operator == 'NOT LIKE'){ echo 'selected';} ?> ><? echo $strNotLike; ?></option>
									<? }
									} ?>
	                <option title="<? echo $strIsEmptyHint; ?>" value="IS NULL" <? if($operator == 'IS NULL'){ echo 'selected';} ?> ><? echo $strIsEmpty; ?></option>
	                <option title="<? echo $strIsNotEmptyHint; ?>" value="IS NOT NULL" <? if($operator == 'IS NOT NULL'){ echo 'selected';} ?> ><? echo $strIsNotEmpty; ?></option>
									<? if($this->attributes['type'][$i] != 'geometry'){ ?>
	                <option title="<? echo $strInHint; ?>" value="IN" <? if (count($this->attributes['enum_value'][$i]) > 0){ echo 'disabled="true"'; } ?> <? if($operator == 'IN'){ echo 'selected';} ?> ><? echo $strIsIn; ?></option>
									<? if(!in_array($this->attributes['type'][$i], array('text'))){ ?>
	                <option title="<? echo $strBetweenHint; ?>" value="between" <? if (count($this->attributes['enum_value'][$i]) > 0){ echo 'disabled="true"'; } ?> <? if($operator == 'between'){ echo 'selected';} ?> ><? echo $strBetween; ?></option>
									<? }
										}
									} ?>
	              </select>
	            </td>
	            <td>&nbsp;&nbsp;</td><?php }
							else {
								if ($operator == '') $operator = '=';
								echo "<input type=\"hidden\" name=\"{$prefix}operator_{$this->attributes['name'][$i]}\" value=\"{$operator}\">";
							} ?>
	            <td align="left" width="40%" style="position: relative; min-width: 300px"><?
	            	switch ($this->attributes['form_element_type'][$i]) {
	            		case 'Auswahlfeld' : case 'Radiobutton' : {
	                  ?><select 
	                  <?
	                  	if($this->layerset[0]['connectiontype'] == MS_WFS OR $this->attributes['req_by'][$i] != '' OR substr($this->attributes['type'][$i], 0, 1) == '_'){		# bei WFS-Layern, abhängigen Auswahlfeldern oder Array-Typen keine multible Auswahl
												echo 'onchange="update_require_attribute(\''.$this->attributes['req_by'][$i].'\','.$this->formvars['selected_layer_id'].', new Array(\''.implode($this->attributes['name'], "','").'\'), '.$searchmask_number.');" ';
												$array = '';
											}
											else{
												$array = '[]';
												echo ' multiple="true" size="1" style="height: 20px;z-index:'.($z_index-=1).';position: absolute;top: 3px; width: 293px" onmousedown="if(this.style.height==\'20px\'){this.style.height=\'300px\';preventDefault(event);}" onmouseleave="if(event.relatedTarget){this.style.height=\'20px\';scrollToSelected(this);}"';
											}
										?> 
	                  	id="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i].$array; ?>"><?echo "\n"; ?>
	                      <option value="">-- <? echo $this->strChoose; ?> --</option><? echo "\n";
	                      if(is_array($this->attributes['enum_value'][$i][0])){
	                      	$this->attributes['enum_value'][$i] = $this->attributes['enum_value'][$i][0];
	                      	$this->attributes['enum_output'][$i] = $this->attributes['enum_output'][$i][0];
	                      }
	                    for($o = 0; $o < count($this->attributes['enum_value'][$i]); $o++){
	                      ?>
	                      <option <? 
													if(!is_array($this->formvars[$prefix.'value_'.$this->attributes['name'][$i]]))$this->formvars[$prefix.'value_'.$this->attributes['name'][$i]] = array($this->formvars[$prefix.'value_'.$this->attributes['name'][$i]]);
													if(in_array($this->attributes['enum_value'][$i][$o], $this->formvars[$prefix.'value_'.$this->attributes['name'][$i]]) AND $this->attributes['enum_value'][$i][$o] != ''){ echo 'selected';} ?> value="<? echo $this->attributes['enum_value'][$i][$o]; ?>"><? echo $this->attributes['enum_output'][$i][$o]; ?></option><? echo "\n";
	                    } ?>
	                    </select>
	                    <input style="width:145px" id="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" type="hidden" value="<? echo $this->formvars[$prefix.'value2_'.$this->attributes['name'][$i]]; ?>">
	                    <?
	                }break;
								
									case 'Autovervollständigungsfeld' : {
										echo '<div id="'.$prefix.'_avf_'.$this->attributes['name'][$i].'" style="';
										if(in_array($operator, array('LIKE', 'NOT LIKE')))echo 'display:none';
										echo '">';
											echo Autovervollstaendigungsfeld($this->formvars['selected_layer_id'], $this->attributes['name'][$i], $i, $this->attributes['alias'][$i], $prefix.'value_'.$this->attributes['name'][$i], $this->formvars[$prefix.'value_'.$this->attributes['name'][$i]], $this->attributes['enum_output'][$i][0], 1, $prefix, NULL, NULL, NULL, NULL, false, 15, false, 40, NULL, NULL);
										echo '</div>';
										echo '<div id="'.$prefix.'_text_'.$this->attributes['name'][$i].'" style="';
										if(!in_array($operator, array('LIKE', 'NOT LIKE')))echo 'display:none';
										echo '">';
											echo '<input style="width:293px" id="'.$prefix.'text_value_'.$this->attributes['name'][$i].'" name="'.$prefix.'value_'.$this->attributes['name'][$i].'" type="text" value="'.$this->formvars[$prefix.'value_'.$this->attributes['name'][$i]].'"';
											if(!in_array($operator, array('LIKE', 'NOT LIKE')))echo ' disabled="true"';
											echo '>';
										echo '</div>';
									
									}break;
                
	                case 'Checkbox' : {
	                  ?><select  id="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>"><?echo "\n"; ?>
	                      <option value="">-- <? echo $this->strChoose; ?> --</option><? echo "\n"; ?>
	                      <option <? if($this->formvars[$prefix.'value_'.$this->attributes['name'][$i]] == 't'){ echo 'selected';} ?> value="t">ja</option><? echo "\n"; ?>
	                      <option <? if($this->formvars[$prefix.'value_'.$this->attributes['name'][$i]] == 'f'){ echo 'selected';} ?> value="f">nein</option><? echo "\n"; ?>
	                    </select>
	                    <input style="width:145px" id="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" type="hidden" value="<? echo $this->formvars[$prefix.'value2_'.$this->attributes['name'][$i]]; ?>">
	                    <?
	                }break;
                
			default : { 
	                  ?>
	                  <input style="width:<? if($this->formvars[$prefix.'value2_'.$this->attributes['name'][$i]] != ''){echo '120';}else{echo '293';} ?>px" id="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value_<? echo $this->attributes['name'][$i]; ?>" type="text" value="<? echo $this->formvars[$prefix.'value_'.$this->attributes['name'][$i]]; ?>" onkeyup="checknumbers(this, '<? echo $this->attributes['type'][$i]; ?>', '<? echo $this->attributes['length'][$i]; ?>', '<? echo $this->attributes['decimal_length'][$i]; ?>');">
	                  <input style="width:145px" id="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" name="<? echo $prefix; ?>value2_<? echo $this->attributes['name'][$i]; ?>" type="<? if($this->formvars[$prefix.'value2_'.$this->attributes['name'][$i]] != ''){echo 'text';}else{echo 'hidden';} ?>" value="<? echo $this->formvars[$prefix.'value2_'.$this->attributes['name'][$i]]; ?>">
	                  <?
	               }
	      				}
	           ?></td>
	          </tr><?					
	        }
				}
      }
			if($last_attribute_index !== NULL){		# ... Tabelle schliessen, wenn es Gruppen gibt
				echo '</table></td></tr>';
			}
?>
</table>