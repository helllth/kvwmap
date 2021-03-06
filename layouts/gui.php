<html>
	<head>
		<title><? echo TITLE; ?></title>
		<? include(SNIPPETS . 'gui_head.php'); ?>
	</head>
	<body onload="onload_functions();">
	<div id="waitingdiv" style="position: absolute;height: 100%; width: 100%; display:none; z-index: 1000000; text-align: center">
			<div style="position: absolute;  top: 50%; left: 50%; transform: translate(-50%,-50%);">
				<i class="fa fa-spinner fa-7x wobble-fix spinner"></i>
			</div>
		</div>
		<a name="oben"></a>
		<table id="gui-table" border="0" cellspacing="0" cellpadding="0" onclick="remove_calendar();">
			<tr>
				<td align="center" valign="top">
					<form name="GUI" enctype="multipart/form-data" method="post" action="index.php" id="GUI">
						<div id="message_box"></div>		<!-- muss innerhalb des form stehen -->
						<table cellpadding=0 cellspacing=0>
							<tr> 
								<td colspan="2" id="header"><?php
									$this->debug->write("<br>Include <b>".HEADER."</b> in gui.php",4);
									include(HEADER); ?>
								</td>
							</tr>
							<tr>
								<td id="menuebar" valign="top" align="center"><?php
									include(SNIPPETS . "menue.php"); ?>
								</td>
								<td align="center" width="100%" height="100%" valign="top" style="background: url('<?php echo BG_IMAGE; ?>'); border-right: 1px solid; border-color: #FFFFFF #CCCCCC #CCCCCC;">
									<div id="container_paint" style="height:100%; position: relative; overflow: hidden;">		<!-- overflow wird für rausfliegende Legende benötigt und height:100% für den Box-Shadow unter der MapFunctionsBar und Legende -->
										<script type="text/javascript">
											window.name = 'root';
											currentform = document.GUI;
											<? $this->currentform = 'document.GUI'; ?>
											function set_hist_timestamp() {
												$('#hist_timestamp_form').show();
											}
										</script>
										<div id="hist_timestamp_form" style="display:none;">
											<i class="fa fa-close" style="cursor: pointer; float: right; margin: 0 5px 0 5px;" onclick="$('#hist_timestamp_form').hide();"></i>
											<? echo $this->histTimestamp; ?>:&nbsp;<a href="javascript:;" onclick="new CalendarJS().init('hist_timestamp2', 'timestamp');"><img title="TT.MM.JJJJ hh:mm:ss" src="<? echo GRAPHICSPATH; ?>calendarsheet.png" border="0"></a><div id="calendar_hist_timestamp2" class="calendar" style="top:35px;left:150px"></div>
											<input onchange="if(this.value.length == 10)this.value = this.value + ' 06:00:00'" id="hist_timestamp2" name="hist_timestamp2" type="text" value="<? echo $this->user->rolle->hist_timestamp_de; ?>" size="16">
											<input type="button" onclick="location.href='index.php?go=setHistTimestamp&timestamp='+document.GUI.hist_timestamp2.value" value="ok">
										</div>
										<?php
										$this->debug->write("<br>Include <b>".$this->main."</b> in gui.php",4);
										if (file_exists($this->main)) {
											include($this->main); # Pluginviews
										}
										else {
											include(LAYOUTPATH . "snippets/" . $this->main);		# normale snippets
										} ?>
									</div>
								</td>
							</tr>
							<tr> 
								<td colspan="2" id="footer"><?php
									$this->debug->write("<br>Include <b>".FOOTER."</b> in gui.php",4);
									include(FOOTER); ?>
								</td>
							</tr>
						</table>
						<input type="hidden" name="overlayx" value="<? echo $this->user->rolle->overlayx; ?>">
						<input type="hidden" name="overlayy" value="<? echo $this->user->rolle->overlayy; ?>">
						<input type="hidden" name="browserwidth">
						<input type="hidden" name="browserheight">
						<input type="hidden" name="stopnavigation" value="0">
						<input type="hidden" name="gle_changed" value="">
						<input type="hidden" name="mime_type" value="">
					</form>
				</td>
			</tr><?php
			if ($this->user->funktion == 'admin' AND DEBUG_LEVEL > 0) { ?>
				<tr>
					<td>
						<div id="log">
							<?php echo readfile(LOGPATH.$_SESSION['login_name'].basename(DEBUGFILE)); ?>
						</div>
					</td>
				</tr><?php
			} ?>
			</table>
	</body>
</html>