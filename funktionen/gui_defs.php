<script language="javascript" type="text/javascript">

	var scrolldown = <? echo $this->scrolldown ?: 0; ?>;
	var auto_map_resize = <? echo $this->user->rolle->auto_map_resize; ?>;
	var querymode = <? echo $this->user->rolle->querymode; ?>;
	var deactivatelayer = '<? echo $this->deactivatelayer; ?>';
	var deactivatequery = '<? echo $this->deactivatequery; ?>';
	var activatequery = '<? echo $this->activatequery; ?>';
	var activatelayer = '<? echo $this->activatelayer; ?>';
 
<?
 	if($this->user->rolle->legendtype == 1){ # alphabetisch sortierte Legende
		echo "var layernames = new Array();\n";
		$layercount = count($this->sorted_layerset);
		for($j = 0; $j < $layercount; $j++){
			echo 'layernames['.$j.'] = \''.str_replace('"', '', str_replace("'", '', $this->sorted_layerset[$j]['alias']))."';\n";
		}
	}
?>

</script>