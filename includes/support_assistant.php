<?php
	
//import CSV file and handle settings
function tsml_support_assistant() {
	global $wpdb, $tsml_data_sources, $tsml_programs, $tsml_program, $tsml_nonce, $tsml_days, $tsml_feedback_addresses, 
	$tsml_notification_addresses, $tsml_distance_units, $tsml_sharing, $tsml_sharing_keys, $tsml_contact_display,
	$tsml_google_maps_key, $tsml_mapbox_key, $wp_version, $tsml_google_overrides, $tsml_support_assistant;

	$error = false;
	$addresses = get_option('tsml_addresses', array());

	//change support assistant setting
	if (!empty($_POST['tsml_support_assistant']) && isset($_POST['tsml_nonce']) && wp_verify_nonce($_POST['tsml_nonce'], $tsml_nonce)) {
		if ($_POST['tsml_support_assistant'] == 'public') {
			$tsml_support_assistant['status'] = 'public';
			$tsml_support_assistant['expires'] = time() + (7 * 24 * 60 * 60);
			$tsml_support_assistant['key'] = md5(uniqid(time(), true));
		} else {
			$tsml_support_assistant['status'] = 'admin_only';
			$tsml_support_assistant['expires'] = time();
			$tsml_support_assistant['key'] = md5(uniqid(time(), true));
		}

		update_option('tsml_support_assistant', $tsml_support_assistant);
		tsml_alert(__('Support Assistant setting updated.', '12-step-meeting-list'));
	}


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
<p><?php echo serialize($tsml_support_assistant); ?></p>
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
					<div class="postbox" id="wheres_my_info">
						<div class="inside">
							<form method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
								<h3><strong><?php _e('Public Support Assistant', '12-step-meeting-list')?></strong></h3>
								<p><?php _e('This setting controls whether the Support Assistant page is available via a public accessible URL, or only through the admin pages of WordPress. By default, it is set to Admin Only.', '12-step-meeting-list')?></p>
								<p><?php _e('The only time this should be set to public is when you\'re working with a support person for the 12 Step Meeting List plugin, and they ask you to change this setting. When your support ticket is closed, you should change this setting back to Admin Only. Also, if Public is selected for more than 7 days, it will automatically switch back to Admin Only.', '12-step-meeting-list')?></p>
								<?php wp_nonce_field($tsml_nonce, 'tsml_nonce', false)?>
								<select name="tsml_support_assistant" onchange="this.form.submit()">
								<?php
								foreach (array(
										'admin_only' => __('Admin Only', '12-step-meeting-list'),
										'public' => __('Public', '12-step-meeting-list'),
									) as $key => $value) {?>
									<!--<option value="<?php echo $key?>"<?php selected($tsml_sharing, $key)?>><?php echo $value?></option>-->
									<option value="<?php echo $key?>"<?php selected(empty($tsml_support_assistant['status']) ? 'admin_only' : $tsml_support_assistant['status'], $key)?>><?php echo $value?></option>
								<?php }?>
								</select>
								<?php if ($tsml_support_assistant['status'] == 'public') { ?>
									<br />
									<ul class="ul-disc">
										<li><?php printf('<a href="%s">%s</a>', admin_url('admin-ajax.php') . '?action=csv&key=' . $tsml_support_assistant['key'], __('Download CSV file', '12-step-meeting-list')); ?></li>
										<li><?php printf('<a href="%s">%s</a>', admin_url('admin-ajax.php') . '?action=meetings&key=' . $tsml_support_assistant['key'], __('See Meetings Feed', '12-step-meeting-list')); ?></li>
									</ul>
								<?php } ?>
							</form>

						</div>
					</div>

				</div>
			</div>
		</div>
	</div>	
<!-- 
- Address Cache
- Begin Copy
<?php echo serialize($addresses); ?>

- End Copy
 -->
	<?php
}
