<?php
	
//import CSV file and handle settings
function tsml_support_assistant() {
	global $wpdb, $tsml_data_sources, $tsml_programs, $tsml_program, $tsml_nonce, $tsml_days, $tsml_feedback_addresses, 
	$tsml_notification_addresses, $tsml_distance_units, $tsml_sharing, $tsml_sharing_keys, $tsml_contact_display,
	$tsml_google_maps_key, $tsml_mapbox_key, $wp_version, $tsml_google_overrides;

	$error = false;
	$addresses = get_option('tsml_addresses', array());

	/*debugging
	delete_option('tsml_data_sources');
	tsml_delete('everything');
	tsml_delete_orphans();
	*/
	?>
<style>
div#content {max-width:1200px; margin:auto; font-size:0.8em;}
div#post-body-content {float:left; width:66%;}
div#postbox-container-1 {width:33%; float:right; min-width:250px;}
ul.ul-disc li, ul.ul-square li {margin:-0.5em 0; padding-top:0; }
ul.ul-disc, ul.ul-square {margin-top: -0.5em; padding-top:0}
h3 {margin-bottom:10px;}
p {margin-bottom:-0px; padding:0}
</style>

	<div class="wrap">
		<h2><?php _e('Support Assistant', '12-step-meeting-list')?></h2>
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content">
					<div class="postbox">
						<div class="inside">
							<p>Instructions go here!</p>
							<details>
								<summary><strong><?php printf(__('See Cached Addresses (%d)', '12-step-meeting-list'), count($addresses)); ?></strong></summary>
								<section>
    								<?php
										ksort($addresses);
										foreach ($addresses as $key => $value) {
											printf('<p>');
											printf('<strong>%s</strong><ul class="ul-square">' . PHP_EOL, $key);
											printf('<li>Address: %s</li>' . PHP_EOL, $value['formatted_address']);
											printf('<li>Latitude: %s</li>' . PHP_EOL, $value['latitude']);
											printf('<li>Longitude: %s</li>' . PHP_EOL, $value['longitude']);
											printf('<li>City: %s</li>' . PHP_EOL, $value['city']);
											printf('</ul></p>' . PHP_EOL . PHP_EOL);
										}
									?>

								</section>
							</details>
						</div>
					</div>
					<div class="postbox">
						<div class="inside">
							<details>
								<summary><strong><?php printf(__('See Static Addresses (%d)', '12-step-meeting-list'), count($tsml_google_overrides))?></strong></summary>
								<section>
    								<?php ksort($tsml_google_overrides);
										foreach ($tsml_google_overrides as $key => $value) {
											printf('<p>');
											printf('<strong>%s</strong><ul class="ul-square">' . PHP_EOL, $key);
											printf('<li>Address: %s</li>' . PHP_EOL, $value['formatted_address']);
											printf('<li>Latitude: %s</li>' . PHP_EOL, $value['latitude']);
											printf('<li>Longitude: %s</li>' . PHP_EOL, $value['longitude']);
											printf('<li>City: %s</li>' . PHP_EOL, $value['city']);
											printf('</ul></p>' . PHP_EOL . PHP_EOL);
										}
									?>
								</section>
							</details>
						</div>
					</div>
					<div class="postbox">
						<div class="inside">
							<details>
								<summary><strong><?php _e('Add Cached Address', '12-step-meeting-list')?></strong></summary>
								<section>
									<p>Good stuff goes here!</p>
								</section>
							</details>
						</div>
					</div>
					<div class="postbox">
						<div class="inside">
							<details>
								<summary><strong><?php _e('PHP Info', '12-step-meeting-list')?></strong></summary>
								<section>
									<?php
										// Get phpinfo() HTML
										ob_start();
										phpinfo(INFO_GENERAL);
										$phpInfoHtml = ob_get_clean();
										$startPos = strpos($phpInfoHtml, '<body>') + 6;
										$endPos = strpos($phpInfoHtml, '</body>');
										echo substr($phpInfoHtml, $startPos, $endPos - $startPos);
									?>
								</section>
							</details>
						</div>
					</div>
				</div>
				<div id="postbox-container-1" class="postbox-container">

<?php //Todo: Make this a function because it's also used on the Imports & Settings Page ?>
					<?php if (version_compare(PHP_VERSION, '5.4') < 0) {?>
					<div class="notice notice-warning inline">
						<p><?php printf(__('You are running PHP <strong>%s</strong>, while <a href="%s" target="_blank">WordPress recommends</a> PHP %s or above. This can cause unexpected errors. Please contact your host and upgrade!', '12-step-meeting-list'), PHP_VERSION, 'https://wordpress.org/about/requirements/', '5.6')?></p>
					</div>
					<?php }
					
					if (!is_ssl()) {?>
					<div class="notice notice-warning inline">
						<p><?php _e('If you enable SSL (https), your users will be able to search near their location.', '12-step-meeting-list')?></p>
					</div>
					<?php }?>

					<div class="postbox" id="wheres_my_info">
						<div class="inside">
							<?php
							$meetings = tsml_count_meetings();
							$locations = tsml_count_locations();
							$regions = tsml_count_regions();
							$groups = tsml_count_groups();
							?>

							<h3><?php _e('Version Numbers', '12-step-meeting-list')?></h3>
							<?php $my_theme = wp_get_theme(); ?>
							<ul class="ul-disc">
								<li><?php printf(__('WordPress: %s', '12-step-meeting-list'), $wp_version); ?></li>
								<li><?php printf(__('PHP: %s', '12-step-meeting-list'), PHP_VERSION); ?></li>
								<li><?php printf(__('TSML: %s', '12-step-meeting-list'), TSML_VERSION); ?></li>
								<li><?php printf(__('Theme: %s', '12-step-meeting-list'), $my_theme->get('Name')); ?></li>
								<li><?php printf(__('Version: %s', '12-step-meeting-list'), $my_theme->get('Version')); ?></li>
							</ul>

							<h3><?php _e('Meetings', '12-step-meeting-list')?></h3>
							<ul class="ul-disc">
								<li><?php printf(__('Published: %d', '12-step-meeting-list'), $meetings); ?></li>
								<li><?php printf(__('Not Published: %d', '12-step-meeting-list'), count(tsml_get_all_meetings()) - $meetings); ?></li>
								<li><?php printf(__('Locations: %d', '12-step-meeting-list'), $locations); ?></li>
								<li><?php printf(__('Regions: %d', '12-step-meeting-list'), $regions); ?></li>
								<li><?php printf(__('Groups: %d', '12-step-meeting-list'), $groups); ?></li>
								<li><?php printf(__('Sharing: %s', '12-step-meeting-list'), $tsml_sharing); ?></li>
							</ul>
							<h3><?php _e('Active Plugins', '12-step-meeting-list')?></h3>
							<ul class="ul-disc">
<?php
$active_plugins = get_option('active_plugins');
foreach ($active_plugins as $key => $value) {
	printf('<li>%s</li>' . PHP_EOL, $value);
}
?>
							</ul>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>	
	<?php
}
