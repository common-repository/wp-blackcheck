<?php
/**
 * @package WP-Blackcheck-Admin
 * @author Viktoria Rei Bauer
 * @version 2.7.2
 */
/*
 * Function library used with WP-BlackCheck
 *
 * Copyright 2011 Viktoria Rei Bauer  (email : blackcheck@stargazer.at)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 */


// Securing against direct calls
if (!defined('ABSPATH')) die("Called directly. Taking the emergency exit.");

// Option handling - Write values
if(isset($_POST['submitted'])) {

	update_option('wpbc_version', 		WPBC_VERSION);

	// Checkbox handling
	update_option('wpbc_statistics', 	$_POST['wpbc_statistics']);
	update_option('wpbc_ip_already_spam', 	$_POST['wpbc_ip_already_spam']);
	update_option('wpbc_nobbcode', 		$_POST['wpbc_nobbcode']);
	update_option('wpbc_timecheck', 	$_POST['wpbc_timecheck']);
	update_option('wpbc_linklimit', 	$_POST['wpbc_linklimit']);
	update_option('wpbc_trackback_list', 	$_POST['wpbc_trackback_list']);
	update_option('wpbc_trackback_check', 	$_POST['wpbc_trackback_check']);
	update_option('wpbc_autopurge', 	$_POST['wpbc_autopurge']);
	update_option('wpbc_updatenotice',  	$_POST['wpbc_updatenotice']);
	update_option('wpbc_emailnotice',  	$_POST['wpbc_emailnotice']);
	update_option('wpbc_redirect',  	$_POST['wpbc_redirect']);
	update_option('wpbc_redirect_to',  	$_POST['wpbc_redirect_to']);
	update_option('wpbc_hash',  		$_POST['wpbc_hash']);


	// Special option treatment
	if ( $_POST['wpbc_nobbcode'] == 'on') {
		update_option('wpbc_nobbcode_autoreport', $_POST['wpbc_nobbcode_autoreport']);
	} else {
		update_option('wpbc_nobbcode_autoreport', '');
	}
	if ( $_POST['wpbc_timecheck'] == 'on') {
		update_option('wpbc_timecheck_autoreport', $_POST['wpbc_timecheck_autoreport']);
	} else {
		update_option('wpbc_timecheck_autoreport', '');
	}
	if ( $_POST['wpbc_linklimit'] == 'on') {
		update_option('wpbc_linklimit_number', $_POST['wpbc_linklimit_number']);
	} else {
		update_option('wpbc_linklimit_number', '-1');
	}

	// Values here
	if ($_POST['wpbc_reportstack']) update_option('wpbc_reportstack', $_POST['wpbc_reportstack']);

	// Clear statistics if requested
	if ($_POST['wpbc_clear_wpbc_stats']) {
		update_option('blackcheck_spam_count', '0');
		update_option('wpbc_counter_blacklist', '0');
		update_option('wpbc_counter_spamqueue', '0');
		update_option('wpbc_counter_bbcode', '0');
		update_option('wpbc_counter_speed', '0');
		update_option('wpbc_counter_link', '0');
		update_option('wpbc_counter_tbvia', '0');
		update_option('wpbc_counter_tburl', '0');
		update_option('wpbc_counter_hash', '0');
		update_option('wpbc_counter_report', '0');
	}
	if ($_POST['wpbc_clear_akismet_stats']) update_option('akismet_spam_count', '0');

	// FACTORY RESET
	if ($_POST['wpbc_reset'] == 'on') {
		wpbc_reset();
	}

}


// Fetch the options
$wpbc_statistics 		= get_option('wpbc_statistics');
$wpbc_reportstack 		= get_option('wpbc_reportstack');
$wpbc_ip_already_spam		= get_option('wpbc_ip_already_spam');
$wpbc_nobbcode			= get_option('wpbc_nobbcode');
$wpbc_nobbcode_autoreport	= get_option('wpbc_nobbcode_autoreport');
$wpbc_timecheck			= get_option('wpbc_timecheck');
$wpbc_timecheck_autoreport	= get_option('wpbc_timecheck_autoreport');
$wpbc_linklimit			= get_option('wpbc_linklimit');
$wpbc_linklimit_number		= get_option('wpbc_linklimit_number');
$wpbc_trackback_list		= get_option('wpbc_trackback_list');
$wpbc_trackback_check		= get_option('wpbc_trackback_check');
$wpbc_version			= get_option('wpbc_version');
$wpbc_autopurge         	= get_option('wpbc_autopurge');
$wpbc_updatenotice		= get_option('wpbc_updatenotice');
$wpbc_emailnotice		= get_option('wpbc_updatenotice');
$wpbc_redirect			= get_option('wpbc_redirect');
$wpbc_redirect_to		= get_option('wpbc_redirect_to');
$wpbc_hash			= get_option('wpbc_hash');
?>


<div class="wrap">
<?php
echo '<div id="icon-options-general" class="icon32"><br /></div><h2>' . __('WP-BlackCheck - Settings', 'wp-blackcheck') . '</h2>';
echo '<p>' . __('Welcome to the settings page for WP-BlackCheck. You are able to configure the plugin to your needs. ', 'wp-blackcheck') . '<br />';
echo sprintf ( __('For more information visit <a href="%s" target="_blank">this page</a>.', 'wp-blackcheck'), 'http://my.stargazer.at/tag/wp-blackcheck/?pk_campaign=BlackCheck%20Plugin' ) . ' ';
echo sprintf ( __('If you found a bug, please report it at <a href="%s" target="_blank">this page</a>.', 'wp-blackcheck'), 'http://bugs.stargazer.at/' ) . '</p>';

if(isset($_POST['submitted'])) echo "<div id='wpbc-info' class='updated fade'><p><strong>" . __('Settings updated.', 'wp-blackcheck') . '</strong></p></div>';

echo '<h3>' . __('Settings', 'wp-blackcheck') . '</h3>';
?>

	<form name="wpbc-settings" action="" method="post">
	<table cellspacing="2" cellpadding="5" class="editform" summary="WP-BlackCheck Settings" border="0">
		<tr height="30px">
			<td colspan="3"><strong><?php _e('Blacklist settings:', 'wp-blackcheck'); ?></strong></td>
		</tr>
		<tr>
			<td><?php _e('Number of IPs to report at once:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_reportstack" type="text" size="5" maxlength="5" value="<?php echo $wpbc_reportstack; ?>"/></td>
		</tr>
		<tr>
			<td colspan="3"><small><?php _e('Enter -1 to report all the IPs at once, disabling the limit.', 'wp-blackcheck'); ?></smalL></td>
		</tr>
		<tr>
			<td><?php _e('Purge spam comments older than 2 weeks:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_autopurge" type="checkbox" value="on" <?php if($wpbc_autopurge == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Show update notice:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_updatenotice" type="checkbox" value="on" <?php if($wpbc_updatenotice == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		<tr>
			<td><?php _e('Notify on new (spam) comments:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_emailnotice" type="checkbox" value="on" <?php if($wpbc_emailnotice == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Redirect spammers instead of giving an error:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_redirect" type="checkbox" value="on" <?php if($wpbc_redirect == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Redirect spammers to:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_redirect_to" type="text" size="50" maxlength="100" value="<?php echo $wpbc_redirect_to; ?>"/></td>
		</tr>


		<tr height="30px">
			<td colspan="3"><strong><?php _e('Misc Spam prevention functions:', 'wp-blackcheck'); ?></strong></td>
		</tr>
		<tr>
			<td><?php _e('Block spammers who have 3 comments in your queue during the last 12 hours: ', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_ip_already_spam" type="checkbox" value="on" <?php if($wpbc_ip_already_spam == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Do not accept bbCode-Links:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_nobbcode" type="checkbox" value="on" <?php if($wpbc_nobbcode == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		
		<tr>
			<td><?php _e('Automatically report IPs that try to send bbCode-Links:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_nobbcode_autoreport" type="checkbox" value="on" <?php if($wpbc_nobbcode_autoreport == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		

		<tr>
			<td><?php _e('Use speed-limit for comments (Check keystrokes per second):', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_timecheck" type="checkbox" value="on" <?php if($wpbc_timecheck == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Automatically report IPs that break speed-limits:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_timecheck_autoreport" type="checkbox" value="on" <?php if($wpbc_timecheck_autoreport == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr>
			<td><?php _e('Block comments having too many links:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_linklimit" type="checkbox" value="on" <?php if($wpbc_linklimit == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		
		<tr>
			<td><?php _e('Maximum number of links:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_linklimit_number" type="text" size="5" maxlength="2" value="<?php echo $wpbc_linklimit_number; ?>"/></td>
		</tr>
		
		<tr>
			<td><?php _e('Comment Hash module:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_hash" type="checkbox" value="on" <?php if($wpbc_hash == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		<tr height="30px">
			<td colspan="3"><strong><?php _e('Pingback / Trackback Settings:', 'wp-blackcheck'); ?></strong></td>
		</tr>
		<tr>
			<td><?php _e('Check Trackbacks against Blacklist (<i>not recommended</i>):', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_trackback_list" type="checkbox" value="on" <?php if($wpbc_trackback_list == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		<tr>
			<td><?php _e('Validate Trackbacks', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_trackback_check" type="checkbox" value="on" <?php if($wpbc_trackback_check == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>

		<tr height="30px">
			<td colspan="3"><strong><?php _e('Statistics:', 'wp-blackcheck'); ?></strong></td>
		</tr>
		<tr>
			<td><?php _e('Show statistics on the dashboard:', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_statistics" type="checkbox" value="on" <?php if($wpbc_statistics == 'on') { echo "checked=\"checked\""; } ?> /></td>
		</tr>
		<tr>
			<td><?php _e('Reset WP-BlackCheck stats', 'wp-blackcheck'); ?> (<?php echo get_option('blackcheck_spam_count'); ?>):</td>
			<td>&nbsp;</td>
			<td><input name="wpbc_clear_wpbc_stats" type="checkbox" value="on" /></td>
		</tr>

		<tr height="30px">
			<td colspan="3"><strong><?php _e('Factory Reset', 'wp-blackcheck'); ?></strong></td>
		</tr>
		<tr>
			<td><?php _e('Drop all your custom settings and revert to the defaults.', 'wp-blackcheck'); ?></td>
			<td>&nbsp;</td>
			<td><input name="wpbc_reset" type="checkbox" value="on" /></td>
		</tr>
		<tr>
			<td align="right" colspan="3">
				<div class="submit"><input type="hidden" name="submitted" /><input type="submit" name="Submit" value="<?php _e($rev_action, 'wp-blackcheck');?> <?php _e('Update Settings', 'wp-blackcheck'); ?> &raquo;" /></div>
			</td>
		</tr>
	</table>
	</form>

<?php
echo '<h3>' . __('Frequently Asked Questions (FAQ):', 'wp-blackcheck') . '</h3>';
?>

	<p>
		<strong>Q: </strong><?php _e('If the number of messages in the Spam-Queue is very high, the script times out.', 'wp-blackcheck'); ?><br />
		<strong>A: </strong><?php _e('Decrease the number of IPs being reported at once. The number you are reporting at once depends on your hosting environment.', 'wp-blackcheck'); ?>
	</p>
	<p>
		<strong>Q: </strong><?php _e('Trackbacks do not work since WP-BlackCheck checks them.', 'wp-blackcheck'); ?><br />
		<strong>A: </strong><?php _e('As some blogs live on hosted environments it might have happened that the server got listed. Disable checking trackbacks against the blacklist.', 'wp-blackcheck'); ?>
	</p>
	<p>
		<strong>Q: </strong><?php _e('Everytime someone is trying to post a comment they see: "Slow down, cowboy! Speed kills."', 'wp-blackcheck'); ?><br />
		<strong>A: </strong><?php _e('A hidden validation field in your comments does not show up. Your theme might be missing the wp_footer() call.', 'wp-blackcheck'); ?><br />
		<strong>A: </strong><?php _e('Another possibility is that you did set your comment-time too low.', 'wp-blackcheck'); ?>
	</p>

</div>
