<?php

	define('SCRIPT_TITLE', 'SS Bookmarks');
	define('SCRIPT_VERSION', 0.5);
	define('SCRIPT_AUTHOR', 'Dominic Manley');
	define('SCRIPT_HOMEPAGE', 'https://github.com/dominicwa/ss-bookmarks');

	error_reporting(E_ERROR);

	/*******************************************************************************/
	/* Script configuration                                                        */
	/*******************************************************************************/

	$sPageTitle				= SCRIPT_TITLE . ' v ' . SCRIPT_VERSION;		// the page title (typically shown in the browser title bar)
	$sScriptName			= basename(__FILE__);							// filename of this script (best not to change)
	$bEnableJavascript		= true;											// provides some UI improvements (inc. bookmarklet)
	$bShowBookmarklet		= true;											// whether to show a draggable bookmarklet link in the top right of the page
	$sNoTagLabel			= 'no-tags';									// default label for bookmarks with no tags
	$sLinkTarget			= '_blank';										// target for all links ('_self' will open in same window, '_blank' in a new window)
	$bEnableBackups			= false;										// backup you script (and bookmark data)
	$bBackupFilename		= $sScriptName . '.bck.' . date('ymd');			// filename to backup to (using date('ymd') will increment daily)
	$iViewPortWidth			= 600;											// viewport width in pixels (zooms in and eliminates white-space on iDevices)

	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!DO NOT EDIT ANYTHING BELOW THIS LINE!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/
	/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/

	/*DATA-START*/
	$iNextIndex = 1;
	$aBookmarks = array(
		0 => array('label' => 'SS-Bookmarks', 'url' => 'https://github.com/dominicwa/ss-bookmarks', 'tags' => ''),
	);/*DATA-END*/
	
	/*******************************************************************************/
	/* Add/delete bookmarks                                                        */
	/*******************************************************************************/
	
	$bReWriteScript = false; // do we rewrite the script with updated data?
	
	if ($_GET['action'] == 'add' || $_GET['action'] == 'bml') {
		$aNewBookmark = array(
			'label' => $_GET['label'],
			'url' => $_GET['url'],
			'tags' => str_replace(', ', ',', $_GET['tags']) // (strip spaces between commas)
		);
		$aBookmarks[$iNextIndex] = $aNewBookmark; // add new bookmark at next index
		$iNextIndex++; // increase the index for next time
		$bReWriteScript = true;
	}
	
	if ($_GET['action'] == 'delete') {
		$aBookmarkTags = explode(',', $aBookmarks[intval($_GET['uid'])]['tags']);
		// first just remove the tag from the bookmark's record (it may have more than one)
		foreach ($aBookmarkTags as $iIndex => $aBookmarkTag) {
			if ($aBookmarkTag == $_GET['tag']) {
				unset($aBookmarkTags[$iIndex]);
			}
		}
		$aBookmarks[intval($_GET['uid'])]['tags'] = implode(',', $aBookmarkTags);
		// if there are no more tags for this bookmark, remove the record completely
		if ($aBookmarks[intval($_GET['uid'])]['tags'] == '') {
			unset($aBookmarks[intval($_GET['uid'])]);
			$iNextIndex--; // descrease the index for next time
		}
		$bReWriteScript = true;
	}
	
	if ($bReWriteScript) {
		$sScriptContents = file_get_contents($sScriptName); // get the contents of this very file
		$sPreData = substr($sScriptContents, 0, strpos($sScriptContents, '/*DATA-START*/') + strlen('/*DATA-START*/')); // grab everything AFTER /*DATA-START*/
		$sAftData = substr($sScriptContents, strpos($sScriptContents, '/*DATA-END*/')); // grab everything UP TO /*DATA-END*/
		$sNewData  = "\n"; // build new data (as PHP) to insert in the middle
		$sNewData .= "\t" . '$iNextIndex = ' . $iNextIndex . ';' . "\n";
		$sNewData .= "\t" . '$aBookmarks = array(' . "\n";
		foreach ($aBookmarks as $iIndex => $aBookmark) {
			$sNewData .= "\t\t" . $iIndex . ' => array(\'label\' => \'' . 
				str_replace('\'', '\\\'', $aBookmark['label']) . '\', \'url\' => \'' .
				str_replace('\'', '\\\'', $aBookmark['url']) . '\', \'tags\' => \'' .
				str_replace('\'', '\\\'', $aBookmark['tags']) . '\'),' . "\n";
		}
		$sNewData .= "\t" . ');';
		if ($bEnableBackups) {
			file_put_contents($bBackupFilename, $sScriptContents); // if condfigured to do so, save a copy of current script before overwriting
		}
		file_put_contents($sScriptName, $sPreData . $sNewData . $sAftData); // overwrite current script file with new data
	}

	if ($_GET['action'] == 'bml') {
		if ($bEnableJavascript)
			echo '<html><head><script type="text/javascript">window.close();</script></head></html>';
		else
			echo '<html><body>Bookmark added. Enable Javascript to auto-close this window.</body></html>';
		exit();
	}
	
	/*******************************************************************************/
	/* Build a tags array from tags used in bookmarks data, sort alphabetically    */
	/*******************************************************************************/
	
	$aTags = array();
	foreach ($aBookmarks as $aBookmark) {
		if ($aBookmark['tags'] != '') {
			$aBookmarkTags = explode(',', $aBookmark['tags']);
			$aTags = array_merge($aTags, $aBookmarkTags);
		}
	}
	$aTags = array_unique($aTags);
	sort($aTags);
	array_unshift($aTags, $sNoTagLabel);
	
	/*******************************************************************************/
	/* Identify current tag, default to no-tags                                    */
	/*******************************************************************************/
	
	$sCurrentTag = $sNoTagLabel;
	if ($_GET['tag'] != '' && in_array($_GET['tag'], $aTags)) {
		$sCurrentTag = $_GET['tag'];
	}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=<?php echo $iViewPortWidth; ?>" />
	<title><?php echo htmlentities($sPageTitle); ?></title>
	<style type="text/css">
		*				{font-family: Arial, Helvetica, sans-serif; font-size: 11px; }
		a				{color: blue;}
		a:hover 		{color: #F60;}
		#tagForm		{padding-bottom: 10px; border-bottom: 1px solid #CCC;}
		#tagForm select	{float: left;}
		#tagForm a		{float: right;}
		ul				{margin: 0; padding: 0; margin-top: 10px; margin-bottom: 10px; border-bottom: 1px solid #CCC; padding-bottom: 5px;}
		li				{list-style: none; margin-bottom: 5px;}
		.bmLink			{float: left;}
		.bmEdit			{float: right;}
		.bmEdit a		{display: block; padding: 0 5px; background: #EEE; text-decoration: none;}
		.bmEdit a:hover	{background: #CCC;}
		.empty			{margin-bottom: 10px; border-bottom: 1px solid #CCC; padding-bottom: 10px;}
		#addForm		{margin: 0 auto; padding: 10px; width: 300px; background: #EEE;}
		#addForm label	{display: block; float: left; width: 100px; margin: 5px; text-align: right; clear: left;}
		#addForm input	{margin-top: 4px;}
		#addButton		{margin-left: 110px; clear: left; _margin-left: 118px;}
		#label			{width: 100px;}
		#url			{width: 160px;}
		#tags			{width: 100px;}
		.clear			{clear: both;}<?php if ($bEnableJavascript) { echo "\n"; ?>
		#submitButton	{display: none;}<?php } echo "\n"; ?>
	</style>
	<?php if ($bEnableJavascript) { ?>
	<script type="text/javascript">
		var fChangeTag = function (o) {
			// produces cleaner urls...
			var sUrl = '<?php echo str_replace('\'', '\\\'', urlencode($sScriptName)); ?>';
			if (o.value != '<?php echo str_replace('\'', '\\\'', $sNoTagLabel); ?>') {
				sUrl += '?tag=' + encodeURIComponent(o.value)
			}
			window.location.href = sUrl;
		}
		window.addEventListener('load', (event) => {
			var cleanURL = window.location.origin + window.location.pathname.replace('index.php', '');
			document.getElementById('bml').setAttribute('href', 'javascript:(function(){var r = prompt(\'Title\', document.title);if (r!=null){window.open(\'' + cleanURL + '?action=bml&label=\' + r + \'&url=\' + window.location.href);}}());');
			<?php if (isset($_GET['action'])) { ?>
			window.history.pushState({}, document.title, cleanURL);
			<?php } ?>
		});
	</script>
	<?php } ?>
</head>
<body>
	<form action="<?php echo htmlentities($sScriptName); ?>" method="get" id="tagForm">
		<?php if ($bEnableJavascript && $bShowBookmarklet) { ?><a href="#" id="bml">Bookmarklet</a><?php } ?>
		<select name="tag" id="tag"<?php if ($bEnableJavascript) { ?> onchange="fChangeTag(this);"<?php } ?>><?php

				echo "\n";
				for ($i = 0; $i < sizeof($aTags); $i++) {
					$sSelected = '';
					if ($sCurrentTag == $aTags[$i]) {
						$sSelected = ' selected="selected"'; // select the tag currently displaying
					}
					echo "\t\t\t" . '<option value="' . htmlentities($aTags[$i]) . '"' . $sSelected . '>' . htmlentities($aTags[$i]) . '</option>' . "\n";
				}
				echo "\t\t";

			?></select>
		<input type="submit" name="submit" id="submitButton" value="Go" />
		<div class="clear"></div>
	</form><?php
	
		echo "\n";
		$aCurrentTagBookmarks = array();
		foreach ($aBookmarks as $iIndex => $aBookmark) {
			$aBookmarkTags = explode(',', $aBookmark['tags']);
			if (in_array($sCurrentTag, $aBookmarkTags) || ($aBookmark['tags'] == '' && $sCurrentTag == $sNoTagLabel)) {
				// here we use the label and uid (to maintain uniqueness) as the key instead so it's easier to sort later
				$aCurrentTagBookmarks[($aBookmark['label'] . $aBookmark['uid'])] = array(
					'uid' => $iIndex,
					'label' => $aBookmark['label'],
					'url' => $aBookmark['url']
				);
			}
		}
		//ksort($aCurrentTagBookmarks);
		uksort($aCurrentTagBookmarks, 'strnatcasecmp');
		if (sizeof($aCurrentTagBookmarks) > 0) {
			echo "\t" . '<ul>' . "\n";
		}
		foreach ($aCurrentTagBookmarks as $aCurrentTagBookmark) {
			echo "\t\t" . '<li>' . "\n";
			echo "\t\t\t" . '<div class="bmLink">' . "\n";
			echo "\t\t\t\t" . '<a href="' . $aCurrentTagBookmark['url'] . '" target="' . $sLinkTarget . '">' . $aCurrentTagBookmark['label'] . '</a>' . "\n";
			echo "\t\t\t" . '</div>' . "\n";
			echo "\t\t\t" . '<div class="bmEdit">' . "\n";
			echo "\t\t\t\t" . '<a href="?action=delete&uid=' . $aCurrentTagBookmark['uid'] . '&tag=' . urlencode($sCurrentTag) . '">-</a>' . "\n";
			echo "\t\t\t" . '</div>' . "\n";
			echo "\t\t\t" . '<div class="clear"></div>' . "\n";
			echo "\t\t" . '</li>' . "\n";
		}
		if (sizeof($aCurrentTagBookmarks) > 0) {
			echo "\t" . '</ul>' . "\n";
		} else {
			echo "\t" . '<p class="empty">No bookmarks in "' . htmlentities($sCurrentTag) . '".</p>' . "\n";
		}
		echo "\t";
	
	?><form action="<?php echo htmlentities($sScriptName); ?>" method="get" id="addForm">
		<label for="label">Label:</label> <input type="text" name="label" id="label" value="" />
		<label for="url">URL:</label> <input type="text" name="url" id="url" value="http://" />
		<label for="tags">Tags (csv):</label> <input type="text" name="tags" id="tags" value="" />
		<div class="clear"></div>
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="tag" value="<?php echo htmlentities($sCurrentTag); ?>" />
		<input type="submit" name="submit" id="addButton" value="Add" />
	</form>
</body>
</html>