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

echo '<div class="wrap">';
echo '<div id="icon-options-general" class="icon32"><br /></div><h2>' . __('WP-BlackCheck - Statistics', 'wp-blackcheck') . '</h2>';
echo '<p>' . __('Welcome to the statistics page for WP-BlackCheck. This page shows information about the effectiveness of the spam prevention methods of this plugin.', 'wp-blackcheck') . '<br />';
echo sprintf ( __('For more information visit <a href="%s" target="_blank">this page</a>.', 'wp-blackcheck'), 'http://my.stargazer.at/tag/wp-blackcheck/?pk_campaign=BlackCheck%20Plugin' ) . ' ';
echo sprintf ( __('If you found a bug, please report it at <a href="%s" target="_blank">this page</a>.', 'wp-blackcheck'), 'http://bugs.stargazer.at/' ) . '</p>';
?>

<div border="1"><canvas width="400" height="220" id="canvas"></canvas></div>

<?php

if(isset($_POST['submitted'])) {
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
} else {
	echo '<form name="wpbc-stats" action="" method="post">';
	echo '<div class="submit"><input type="hidden" name="submitted" /><input type="submit" name="Submit" value="';
	 _e($rev_action, 'wp-blackcheck');
 	_e('Reset WP-BlackCheck stats', 'wp-blackcheck');
	echo ' &raquo;" /></div>';
	echo '</form>';
}

?>

<script type="application/javascript">
window.onload = function() {
	var canvas = document.getElementById("canvas");
	var ctx = canvas.getContext("2d");

	ctx.font = "bold 12px sans-serif";

	ctx.fillText("Trackback having invalid headers:", 0, 15);
	ctx.fillText("<?php echo get_option('wpbc_counter_tbvia'); ?>", 210, 15);
	ctx.fillText("Trackback having an invalid URL:",  0, 35);
	ctx.fillText("<?php echo get_option('wpbc_counter_tburl'); ?>",  210, 35);
	ctx.fillText("IP already in Spam Queue:",  0, 55);
	ctx.fillText("<?php echo get_option('wpbc_counter_spamqueue'); ?>",  210, 55);
	ctx.fillText("bbCode used:",  0, 75);
	ctx.fillText("<?php echo get_option('wpbc_counter_bbcode'); ?>",  210, 75);
	ctx.fillText("Link limit:",  0, 95);
	ctx.fillText("<?php echo get_option('wpbc_counter_link'); ?>",  210, 95);
	ctx.fillText("Speed limit:",  0, 115);
	ctx.fillText("<?php echo get_option('wpbc_counter_speed'); ?>",  210, 115);

	ctx.fillText("Hashlist:",  0, 135);
	ctx.fillText("<?php echo get_option('wpbc_counter_hash'); ?>",  210, 135);

	ctx.fillText("Blacklist:",  0, 155);
	ctx.fillText("<?php echo get_option('wpbc_counter_blacklist'); ?>",  210, 155);

	ctx.fillText("Total:",  0, 175);
	ctx.fillText("<?php echo get_option('blackcheck_spam_count'); ?>",  210, 175);

	ctx.fillText("Reported IPs:",  0, 205);
	ctx.fillText("<?php echo get_option('wpbc_counter_report'); ?>",  210, 205);


	ctx.fillStyle = "rgb(200,0,0)";
	ctx.fillRect (240, 6, 	<?php echo wpbc_percentage_bar('wpbc_counter_tbvia'); ?>, 10);
	ctx.fillRect (240, 26,  <?php echo wpbc_percentage_bar('wpbc_counter_tburl'); ?>, 10);
	ctx.fillRect (240, 46,  <?php echo wpbc_percentage_bar('wpbc_counter_spamqueue'); ?>, 10);
	ctx.fillRect (240, 66,  <?php echo wpbc_percentage_bar('wpbc_counter_bbcode'); ?>, 10);
	ctx.fillRect (240, 86,  <?php echo wpbc_percentage_bar('wpbc_counter_link'); ?>, 10);
	ctx.fillRect (240, 106, <?php echo wpbc_percentage_bar('wpbc_counter_speed'); ?>, 10);
	ctx.fillRect (240, 126, <?php echo wpbc_percentage_bar('wpbc_counter_hash'); ?>, 10);
	ctx.fillRect (240, 146, <?php echo wpbc_percentage_bar('wpbc_counter_blacklist'); ?>, 10);

	ctx.fillStyle = "rgb(0,0,0)";
	ctx.fillRect (0, 160, 400, 2);

	ctx.fillRect (0, 180, 400, 2);
	ctx.fillRect (0, 183, 400, 2);

	ctx.fillStyle = "rgb(200,0,0)";

        <?php 

	if (!get_option('wpbc_trackback_check')) {
	  echo 'ctx.fillRect (0, 10, 230, 2);';
	  echo 'ctx.fillRect (0, 30, 230, 2);';
	}
        if (!get_option('wpbc_ip_already_spam')) echo 'ctx.fillRect (0, 50, 230, 2);';
        if (!get_option('wpbc_nobbcode'))  echo 'ctx.fillRect (0, 70, 230, 2);';
        if (!get_option('wpbc_linklimit')) echo 'ctx.fillRect (0, 90, 230, 2);';
        if (!get_option('wpbc_timecheck')) echo 'ctx.fillRect (0, 110, 230, 2);';
        if (!get_option('wpbc_hash')) echo 'ctx.fillRect (0, 130, 230, 2);';
	
	?>

}
</script>
</div>

<?php
function wpbc_percentage_bar($option) {
	if ( get_option('blackcheck_spam_count') > 0 ) {
		return get_option($option) /  get_option('blackcheck_spam_count') * 200;
	} else {
		return 0;
	}
}


?>
