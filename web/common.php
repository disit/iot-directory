<?php

function manageLegacy() {
	if ((!$_SESSION['isPublic'] && isset($_SESSION['newLayout']) && $_SESSION['newLayout'] === true) || ($_COOKIE['layout'] == "new_layout")) {
		return;
	} else {
		include('../legacy/management/'.basename($_SERVER["SCRIPT_FILENAME"]));
		exit;
	}
}

function enableIsCertified() {
	if(isset($GLOBALS['blockchainEnabled']) && $GLOBALS['blockchainEnabled']) 
		return '';
	return "style='display:none' ";
}
