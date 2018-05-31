<h2 style="margin-top: 20px">Mobile App</h2>
<div style="text-align: left; margin: 20px">
Zur Erfassung von Haltestellendaten steht die mobile Anwendung kvwmobile zur Verfügung.
<p>
Es handelt sich um eine App für Android Handy's und Tablets, die von hier bezogen werden kann.
<p>
Zur Installation der Anwendung laden Sie bitte diese Datei mit einem Browser in Ihrem mobilen Endgerät herunter und folgen den Installationsanweisungen. Alternativ können Sie die Datei auch auf einem PC herunterladen und per USB-Kabel auf Ihr Endgerät kopieren. Starten Sie die Datei dort mit einem Doppelklick.
<p>
<p>
kvmobile.apk <a href="https://github.com/pkorduan/kvmobile/raw/develop/platforms/android/build/outputs/apk/kvmobile-1.2.1.apk">Download</a> <?
$size = get_remote_filesize('https://github.com/pkorduan/kvmobile/raw/develop/platforms/android/build/outputs/apk/kvmobile-1.2.1.apk');
$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
$power = $size > 0 ? floor(log($size, 1024)) : 0;
echo number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power]; ?>
<p>
<p>
Nach der Installation der App tragen Sie zunächst Ihre Zugangsdaten zum Server ein mit folgenden Angaben ein:<br>
Name: Geoportal LK-ROS<br>
URL: https://geoportal.lkros.de/kvwmap_dev<br>
Nutzername: Ihr Loginname bei kvwmap<br>
Password: Ihr Passwort bei kvwmap<br>
Stelle Id: 610011
<p>
Sie müssen in kvwmap Zugang zur Stelle <b>Amt 61 - ÖPNV/Rebus Mobile</b> haben. Wenn nicht wenden Sie sich an Herrn Holger Riedel
<p>
Klicken Sie auf <b>Speichern</b> und <b>Lade verfügbare Layer</b>.<br>
Wählen Sie anschließend den Layer "hst_rebus" aus und klicken auf den rechten Button zur Synchronisierung der Daten.<br>
Ist der Ladevorgang abgeschlossen können Sie nach oben Scrollen und auf das Haltestellensymbol klicken. Damit wird die Liste aller Haltestellen angezeigt, von der Sie nun durch Auswählen einzelne bearbeiten können.
</div>
