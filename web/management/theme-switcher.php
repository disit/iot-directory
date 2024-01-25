<?php
$defaultTheme = $layoutDefault; // Set the default theme

// Check if the theme is already set in the cookie
if (isset($_COOKIE['selected_theme'])) {
	$selectedTheme = $_COOKIE['selected_theme'];
} else {
	$selectedTheme = $defaultTheme;
	setcookie('selected_theme', $selectedTheme, [
		'expires' => time() + (30 * 24 * 60 * 60), // Set the cookie for 30 days
		'secure' => true,
		'samesite' => 'None',
	]);
}

$current_page = basename($_SERVER['PHP_SELF']);

switch ($current_page) {
	default:
		// Include the appropriate CSS file based on the selected theme
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-dashboard.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-dashboardList.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-dashboardView.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-addWidgetWizard2.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-addDashboardTab.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-dashboard_configdash.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-iotApplications.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-addWidgetWizardDataInspector.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/themes/' . $selectedTheme . '/s4c-snapTour.css">';
		echo '<link rel="stylesheet" type="text/css" href="' . $themeBaseUri . '/css/s4c-css/fontawesome-free-6.2.0-web/css/all.min.css">';
		break;
}
?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
	/*var themeSwitcher = parent.document.getElementById('theme-switcher');
	themeSwitcher.value = '<?php echo $selectedTheme; ?>'; // Set the initial selected theme

	themeSwitcher.addEventListener('change', function() {
	  var selectedTheme = themeSwitcher.value;
	  document.cookie = 'selected_theme=' + selectedTheme + '; expires=' + new Date(Date.now() + (30 * 24 * 60 * 60 * 1000)).toUTCString(); // Update the cookie with the selected theme
	  location.reload(); // Refresh the page to apply the new theme
	});*/
  });
</script>
