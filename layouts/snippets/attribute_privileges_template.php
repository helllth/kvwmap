		<td width="280px">
			<table>
  			<tr>
			  	<td colspan="4" width="100%">
			  		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			  			<tr>
			  				<? if($this->stelle->id != '' AND $this->layer[0]['Name'] != ''){ ?>
						  	<td height="50px" valign="top" align="center"><span class="fetter px16"><? echo $this->stelle->Bezeichnung; ?></span></td>
						  	<? }elseif($this->layer[0]['Name'] != ''){ ?>
						  	<td height="50px" valign="top" align="center"><span class="fetter px16"><? echo $strDefaultPrivileges; ?></span></td>
						  	<? } ?>
			  			</tr>
			  		</table>
			  	</td>
			  </tr>
			  <tr>
			  	<td colspan="4">
			    	<table align="center" border="0" cellspacing="2" cellpadding="2">
			    		<tr>
						  	<td align="center"><span class="fett"><? echo $strLayerAccessPrivileges; ?></span></td>
						  </tr>
						  <tr>
						  	<td>
						  		<select name="privileg<? echo $this->stelle->id; ?>">
						  			<option <? if($this->layer[0]['privileg'] == '0'){echo 'selected';} ?> value="0"><? echo $strReadAndEdit; ?></option>
						  			<option <? if($this->layer[0]['privileg'] == '1'){echo 'selected';} ?> value="1"><? echo $strCreateNewRecords; ?></option>
						  			<option <? if($this->layer[0]['privileg'] == '2'){echo 'selected';} ?> value="2"><? echo $strCreateAndDelete; ?></option>
						  		</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
			  	<td colspan="4">
			    	<table align="center" border="0" cellspacing="2" cellpadding="2">
			    		<tr>
						  	<td align="center"><span class="fett"><? echo $strLayerExportPrivileges; ?></span></td>
						  </tr>
						  <tr>
						  	<td>
						  		<select name="export_privileg<? echo $this->stelle->id; ?>">
						  			<option <? if($this->layer[0]['export_privileg'] == '0'){echo 'selected';} ?> value="0"><? echo $strNoExport; ?></option>						  			
										<option <? if($this->layer[0]['export_privileg'] == '2'){echo 'selected';} ?> value="2"><? echo $strOnlyData; ?></option>
										<option <? if($this->layer[0]['export_privileg'] == '1'){echo 'selected';} ?> value="1"><? echo $strDataAndGeom; ?></option>
						  		</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr> 
					<td colspan="4">
						<table align="center" border="0" cellspacing="0" cellpadding="0"><?
						if ($this->layer[0]['Name'] != '' AND count($this->attributes) != 0) {
							echo '
									<tr>
										<td align="center">
											<span class="fett">Attribut</span>
										</td>
										<td>&nbsp;</td>
										<td align="center">
											<span class="fett">Privileg</span>
										</td>
										<td>&nbsp;</td>
										<td align="center">
											<span class="fett">Tooltip</span>
										</td>
									</tr>
							';
							if($this->stelle->id != '' AND $this->attributes_privileges == NULL){				# zu diesem Layer und Stelle gibt es keinen Eintrag -> alle Attribute sind lesbar
								$noentry = true;
							}
							else{
								$noentry = false;
							}
							$attributenames = implode('|', $this->attributes['name']);
							for ($i = 0; $i < count($this->attributes['type']); $i++){
				    		if ($this->stelle->id == ''){
				    			$this->attributes_privileges[$this->attributes['name'][$i]] = $this->attributes['privileg'][$i]; 	# die default-Rechte kommen aus layer_attributes
				    			$this->attributes_privileges['tooltip_'.$this->attributes['name'][$i]] = $this->attributes['query_tooltip'][$i]; 	# die default-Rechte kommen aus layer_attributes
				    		}
								echo '
								<tr>
								  <td align="center">
								  	<input style="width:100px" type="text" name="attribute_'.$this->attributes['name'][$i].'" value="'.$this->attributes['name'][$i].'" readonly>
								  </td>
								  <td>&nbsp;</td>
								  <td align="center" style="height:21px">';
									$privilege_options = array(
										array(
											value => '',
											output => $strNoAccess,
										),
										array(
											value => '0',
											output => $strRead,
										),
										array(
											value => '1',
											output => $strEdit,
										)
									);

								  echo '<select style="width:100px" name="privileg_'.$this->attributes['name'][$i].'_'.$this->stelle->id.'">';
									foreach($privilege_options AS $option) {
										$selected = ($this->attributes_privileges[$this->attributes['name'][$i]] == $option['value'] ? ' selected' : '');
										echo '<option value="' . $option['value'] . '"' . $selected . '>' . $option['output'] . '</option>';
									}
									echo '</select>
								  </td>
								  <td>&nbsp;</td>
								  <td align="center"><input type="checkbox" name="tooltip_'.$this->attributes['name'][$i].'_'.$this->stelle->id.'" ';
								  if($this->attributes_privileges['tooltip_'.$this->attributes['name'][$i]] == 1){
								  	echo 'checked';
								  }
									echo ' ></td>
				        </tr>
				        ';
				    	}
							echo '
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>';
				    			if($this->formvars['stelle'] != 'a'){
				    				echo '
								  <td align="center">
								  	<input style="width:100px" type="text" name="" value="alle" readonly>
								  </td>';} echo '
								  <td>&nbsp;</td>
								  <td align="center">
								  	<select  style="width:100px" name="" onchange="set_all(\''.$attributenames.'\', \''.$this->stelle->id.'\', this.value);"">
											<option value=""> - '.$this->strChoose.' - </option>
								  		<option value="">'.$strNoAccess.'</option>
								  		<option value="0">'.$strRead.'</option>
								  		<option value="1">'.$strEdit.'</option>
								  	</select>
								  </td>
								  <td>&nbsp;</td>
								  <td>&nbsp;</td>
				        </tr>
								';
							if (count($this->attributes) > 0) {
								$stelle_and_layer_selected = $this->stelle->id != '' AND $this->layer[0]['Name'] != '';
								if ($stelle_and_layer_selected ) {
									$default_stellen_ids = $this->stelle->id;
									$default_privileges_link_text = $strUseDefaultPrivileges;
									$save_stellen_ids = implode('|', $this->stellen['ID']);
								}
								else {
									$default_stellen_ids = implode('|', $this->stellen['ID']);
									$default_privileges_link_text = $strAssignDefaultPrivileges;
									$save_stellen_ids = '';
								}

								if ($stelle_and_layer_selected OR count($this->stellen['ID']) > 0) { ?>
									<tr>
										<td colspan="5" height="40px" align="center" valign="middle">
											<a href="javascript:get_from_default(
												'<? echo $attributenames; ?>',
													'<? echo $default_stellen_ids; ?>'
												);"><? echo $default_privileges_link_text; ?></a>
										</td>
									</tr>
									<tr>
										<td align="center" colspan="5">
											<input
												type="button"
												onclick="save('<? echo $save_stellen_ids; ?>');"
												name="speichern"
												value="<? echo $this->strSave; ?>"
											>
										</td>
									</tr><?
								} ?>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<tr>
									<td height="40px" align="center" colspan="5">
										<span class="fett">
											<span style="font-size:15px"><? echo $this->stelle->Bezeichnung; ?></span>
										</span>
									</td>
								</tr><?
							}
						} ?>
						</table>
					</td>
				</tr>
			</table>
		</td>
