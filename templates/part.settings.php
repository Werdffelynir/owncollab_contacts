<?php

/**
 * @var array $_
 */

$url = \OC::$server->getURLGenerator()->getAbsoluteURL('index.php/apps/owncollab_contacts')

?>
<div id="app-settings">
	<div id="app-settings-header">
		<button class="settings-button" data-apps-slide-toggle="#app-settings-content">Settings</button>
	</div>
	<div id="app-settings-content" style="display: none;">
		<div class="download_button">
			<a href="<?=$url?>/getvcard" target="_blank" class="icon-download">Export contacts</a>
		</div>
		<h2>
            <label for="webdavurl">Remote</label>
        </h2>
		<input id="webdavurl" readonly="readonly" value="<?=$url?>/vcard" type="text">
		<br>
	</div>
</div>