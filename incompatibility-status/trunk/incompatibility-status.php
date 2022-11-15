<?php
/**
 * @package	incompatibility-status
 * @author	Ingo Steinke
 * @version 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Incompatibility Status
 * Text Domain: incompatibility-status
 * Domain Path: /languages
 * Plugin URI: https://github.com/openmindculture/wp-incompatibility-status/
 * Description: Incompatibility Status adds a status message to the admin dashboard to display possible incompatibility issues using the block editor and full-site editing.
 * Short Description: Show Gutenberg Incompatibility Status in WP-Admin
 * Version: 1.0.0
 *  Author: openmindculture
 * Author URI: https://wordpress.org/support/users/openmindculture/
 * Requires at least: 6.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 */

if ( is_admin() ) {

	function openmindculture_wpstatus__dashboard_widgets() {
		wp_add_dashboard_widget('custom_help_widget', 'Incompatibility Warnings', 'openmindculture_wpstatus__content');
	}

	function openmindculture_wpstatus__content() {
		$details = [];
		$warnings = [];
		$has_classic_editor = false;
		$has_custom_gutenberg = false;
		$has_active_block_theme = false;
		global $wp_version;

		$openmindculture_wpstatus__KNOWN_PLUGINS__CLASSIC = [
			'classic-editor/classic-editor.php',
			'disable-gutenberg/disable-gutenberg.php',
			'enable-classic-editor/enable-classic-editor.php'
		];
		$openmindculture_wpstatus__KNOWN_PLUGINS__CUSTOM_BLOCK = [
			'gutenberg/gutenberg.php'
		];
		$openmindculture_wpstatus__BLOCK_THEME_TAGS = [
			'block-patterns',
			'block-styles',
			'full-site-editing'
		];

		if ($wp_version)
		{
			array_push($details, "WordPress version: $wp_version");
		}

		if (phpversion())
		{
			array_push($details, 'PHP version: ' . phpversion());
		}

		$current_theme = wp_get_theme();
		array_push($details,"Current Theme: " . $current_theme['Name'] . ' ' . $current_theme['Version']);

		$current_theme_tags = $current_theme['Tags'];
		if (in_array('fse', $current_theme_tags)) {
			echo "current theme fse";
		}
		foreach ($openmindculture_wpstatus__BLOCK_THEME_TAGS as $i => $block_theme_tag) {
			if (in_array($block_theme_tag, $current_theme_tags)) {
				array_push($details,"Current Theme supports $block_theme_tag.");
				$has_active_block_theme = true;
			}
		}

		foreach ($openmindculture_wpstatus__KNOWN_PLUGINS__CLASSIC as $i => $relative_plugin_path) {
			if (is_plugin_active($relative_plugin_path)) {
				$plugin_entrypoint = WP_PLUGIN_DIR . '/' . $relative_plugin_path;
				$plugin_data = get_plugin_data($plugin_entrypoint);
				array_push($details, "Active classic editor plugin: " . $plugin_data['Name'] . ' ' . $plugin_data['Version']);
				$has_classic_editor = true;
			}
		}

		foreach ($openmindculture_wpstatus__KNOWN_PLUGINS__CUSTOM_BLOCK as $i => $relative_plugin_path) {
			if (is_plugin_active($relative_plugin_path)) {
				$plugin_entrypoint = WP_PLUGIN_DIR . '/' . $relative_plugin_path;
				$plugin_data = get_plugin_data($plugin_entrypoint);
				array_push($details, "Active Gutenberg plugin: " . $plugin_data['Name'] . ' ' . $plugin_data['Version']);
				$has_custom_gutenberg = true;
			}
		}

		if ($has_classic_editor && $has_active_block_theme) {
			array_push($warnings, "<span class='openmindculture_wpstatus__summary openmindculture_wpstatus__summary--warning'>Conflict: Classic Editor vs. active block theme.</span>");
		}

		if ($has_classic_editor && $has_custom_gutenberg) {
			array_push($warnings, "<span class='openmindculture_wpstatus__summary openmindculture_wpstatus__summary--warning'>Conflict: Classic Editor vs. Gutenberg plugin.</span>");
		}


		openmindculture_wpstatus__print_styles();

		if (count($warnings) > 1)
		{
			echo '<p class="openmindculture_wpstatus__summary openmindculture_wpstatus__summary--warning">';
			echo $warnings; // TODO escape
			echo 'issues might need your attention.</p>';
		}
		else if (count($warnings) > 0)
		{
			echo '<p class="openmindculture_wpstatus__summary openmindculture_wpstatus__summary--warning">One issue might need your attention.</p>';
		}
		else
		{
			echo '<p class="openmindculture_wpstatus__summary openmindculture_wpstatus__summary--ok">No critical issues detected.</p>';
		}

		/**
		 * @var int $i
		 * @var string $warning
		 */
		foreach ($warnings as $i => $warning) {
			echo $warning; // TODO escape
		}

		echo '<ul>';
		/**
		 * @var int $i
		 * @var string $detail
		 */
		foreach ($details as $i => $detail) {
			echo "<li>$detail</li>";
		}
		echo '</ul>';

		echo '<p>This message is generated by the <b>Incompatibility Status</b> plugin currently active on this site. You can disable the plugin on the <a href="plugins.php">plugins page</a>.</p>';
	}

	function openmindculture_wpstatus__print_styles() {
		$styles = file_get_contents(__DIR__ . '/styles.css');
		echo '<style>';
		echo $styles; // TODO escape
		echo '</style>';
	}

	add_action('wp_dashboard_setup', 'openmindculture_wpstatus__dashboard_widgets');
}
