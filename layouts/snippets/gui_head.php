<?php
	$url_parts = explode('/', JQUERY_PATH);
	$dir_parts = explode('-' , $url_parts[count($url_parts) - 2]);
?>
<meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<script type="text/javascript" src="funktionen/calendar.js"></script>
<script type="text/javascript" src="funktionen/keyfunctions.js"></script>
<script src="<?php echo JQUERY_PATH; ?>jquery-<?php echo $dir_parts[1]; ?>.min.js"></script><?
if (true) { ?>
	<script src="<?php echo PROJ4JS_PATH; ?>proj4.js"></script><?
} ?>
<link rel="stylesheet" href="<?php echo FONTAWESOME_PATH; ?>css/font-awesome.min.css" type="text/css">
<? include(WWWROOT . APPLVERSION . 'funktionen/gui_functions.php'); ?>
<link rel="shortcut icon" href="<? echo CUSTOM_PATH; ?>wappen/favicon.ico">
<link rel="stylesheet" href="<?php echo 'layouts/' . $this->style . '?gui=' . $this->user->rolle->gui; ?>"><?
if (defined('CUSTOM_STYLE') AND CUSTOM_STYLE != '') { ?>
	<link rel="stylesheet" href="<?php echo CUSTOM_STYLE; ?>"><?
}
if ($this->Stelle->style != '') { ?>
	<link rel="stylesheet" href="<? echo $this->Stelle->style; ?>"><?
}
?>