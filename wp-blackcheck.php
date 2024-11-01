<?php
/**
 * @package WP-BlackCheck
 * @author Viktoria Rei Bauer
 * @version 2.7.2
 */
/*
Plugin Name: WP-BlackCheck
Plugin URI: http://www.stargazer.at/projects#
Description: This plugin is a simple blacklisting checker that works with our hosts
Author: Viktoria Rei Bauer
Version: 2.7.2
Author URI: http://my.stargazer.at/

    Copyright 2011 Viktoria Rei Bauer  (email : blackcheck@stargazer.at)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

// Securing against direct calls
if (!defined('ABSPATH')) die("Called directly. Taking the emergency exit.");

define('WPBC_VERSION', '2.7.2');
define('WPBC_SERVER', 'www.stargazer.at');

// define('WPBC_LOGFILE', '');
define('WPBC_LOGFILE', 'wpbclog.txt');

include ('functions.inc.php');
include ('precheck.inc.php');

// Checking a comment as we got it (hook calls us)
function wpbc_blackcheck($comment) {
	// IPv6 - IPv4 compatibility mode hack
	$_SERVER['REMOTE_ADDR'] = preg_replace("/^::ffff:/", "", $_SERVER['REMOTE_ADDR']);
	$userip = $_SERVER['REMOTE_ADDR'];


	// trackbacks/pingbacks are a different topic
	if ($comment['comment_type'] == 'trackback' || $comment['comment_type'] == 'pingback') {
		// trackback verification happens here
		if (get_option('wpbc_trackback_check')) {
			// Proxy servers do not send trackbacks
			$headers = wpbc_get_http_headers();
			if (array_key_exists('Via', $headers) || array_key_exists('Max-Forwards', $headers) || array_key_exists('X-Forwarded-For', $headers) || array_key_exists('Client-Ip', $headers)) {
				wpbc_counter('tbvia');
				wp_spammer( __( 'Invalid request: Proxy servers do not send trackbacks or pingbacks.', 'wp-blackcheck') );
			}

			// Proper URL?
			if(!preg_match("/^http/", $comment['comment_author_url'])) {
				wpbc_counter('tburl');
				wp_spammer( __('Invalid url: ', 'wp-blackcheck') . $comment['comment_author_url']);
			}

			// Validate IP Address
			$sender_IP = preg_replace('/[^0-9.]/', '', $_SERVER['REMOTE_ADDR'] );
			$trackback_IP = preg_replace('/[^0-9.]/', '', gethostbyname( wpbc_get_domainname($comment['comment_author_url']) ));

			if ($sender_IP != $trackback_IP) {
				wpbc_counter('tburl');
				wp_spammer( __('Sender IP does not match trackback IP.') );
			}

			// Make use of WP's Snoopy Class
			include_once( ABSPATH . WPINC . '/class-snoopy.php' );
				$wpbc_snoopy = new Snoopy;
				$wpbc_snoopy->fetchlinks($comment['comment_author_url']);
				$remoteLinks = $wpbc_snoopy->results;
				if ( is_array($remoteLinks) ) {

					$wpbcBackLink = false;

					// We found some links at the other end
					foreach ($remoteLinks as $loopLink) {
						$loopLink = preg_replace('/(\/|\/trackback|\/trackback\/)$/', '', $loopLink);
						$BlogLink = get_bloginfo('siteurl');
						if ( strrpos( $loopLink, $BlogLink ) !== false ) $wpbcBackLink = true;
					}
					if ($wpbcBackLink == false) {
						wpbc_counter('tburl');
						wp_spammer( __('Backlink not found.') );
					}

				} else {
					// Problem? Logging?
					if(WPBC_LOGFILE != ''){
						$log = fopen(WPBC_LOGFILE, 'a');
						fwrite($log, date('c') . " - Snoopy: Problem: Request from ".$_SERVER['REMOTE_ADDR']);
					}

				}
				unset($wpbc_snoopy);


		}

		if (get_option('wpbc_trackback_list')) {
			$response = wpbc_do_check($userip);
			if ($response[1] != "NOT LISTED") {
				wpbc_counter('list');
				wp_spammer( __('Your host is blacklisted and cannot send any trackbacks.', 'wp-blackcheck') );
			}
		}

		return $comment;

	}

    // people we did whitelist don't need to go through it all if we honor that
    if ( get_option('comment_whitelist') == 1 ) {
	 if ($comment['comment_type'] != 'trackback' || $comment['comment_type'] != 'pingback') {
	 	if ( wpbc_get_comments_approved($comment['comment_author_email'], $comment['comment_author']) == 1 ) return $comment;
	 }
    }

	if (!is_user_logged_in()) {
		// Additional checks happen here as needed/wanted - sorted by effectivity
		if (get_option('wpbc_timecheck')) 		wpbc_pc_speedlimit($comment);
		if (get_option('wpbc_linklimit')) 		wpbc_pc_linklimit($comment);
		if (get_option('wpbc_nobbcode')) 		wpbc_pc_nobbcode($comment);
		// last resort if other anti spam plugins got the bugger into the queue already
		if (get_option('wpbc_ip_already_spam')) 	wpbc_pc_already_spam($userip);
		// message hash checking
		if (get_option('wpbc_hash'))			wpbc_pc_commenthash($comment);
		// do the blacklist-check now
		$response = wpbc_do_check($userip);

		if ($response[1] != "NOT LISTED") {
			wpbc_counter('list');
			$diemsg  = '<h1>'. sprintf( __('The blacklist says: %s', 'wp-blackcheck'), $response[1]) ."</h1>\n<br />";
			$diemsg .= sprintf( __('See <a href="%s">here</a> for details.', 'wp-blackcheck'), 'http://www.stargazer.at/blacklist/?ip='.urlencode($userip) );
			wp_spammer($diemsg);
		} else {
			if ( get_option( 'wpbc_emailnotify' ) == 'on' && $comment->comment_type == 'spam') {
				wp_notify_moderator($comment->comment_ID);
			}
			return $comment;
		}
	} else {
		return $comment;
	}
}

// get the number of approved comments
function wpbc_get_comments_approved( $comment_author_email, $comment_author ) {
	global $wpdb;
	if ( !empty($comment_author_email) )
		return $wpdb->get_var("SELECT comment_approved FROM $wpdb->comments WHERE comment_author = '$comment_author' AND comment_author_email = '$comment_author_email' and comment_approved = '1' LIMIT 1");
	return 0;
}

// Report-Spam button for the Spam-Queue
function wpbc_report_spam_button($comment_status) {
	if ( $comment_status=='approved' )
                return;

	if ( function_exists('plugins_url') )
		$link = 'tools.php?page=wp-blackcheck/wp-blackcheck.php';
	echo "</div><div class='alignleft'><a class='button-secondary checkforspam' href='$link'>" . __('Report and Clean Spam', 'wp-blackcheck') . "</a>";

}

// Statistics for the admin dashboard
function wpbc_table_end() {
	if ( get_option('wpbc_statistics') == 'on' ) {
		$count = get_option('blackcheck_spam_count');
		echo sprintf('<tr><td class="first b b-tags"></td><td class="t tags"></td><td class="b b-spam" style="font-size:18px"><a href="index.php?page=wp-blackcheck/wp-blackcheck.php">%s</a></td><td class="last t">%s</td></tr>', number_format_i18n($count),   __('Blocked', 'wp-blackcheck') );
	}
}
function wpbc_discussion_table_end($count) {
	if ( get_option('wpbc_statistics') == 'on' ) {
		$count = get_option('blackcheck_spam_count');
		echo sprintf('<tr><td class="b b-spam" style="font-size:18px">%s</td><td class="last t"><a href="index.php?page=wp-blackcheck/wp-blackcheck.php">%s</a></td></tr>', number_format_i18n($count),	__('Blocked', 'wp-blackcheck'));
	}
}


// Admin warning if our settings are outdated
function wpbc_blackcheck_warning() {
	if ( get_option('wpbc_version') != WPBC_VERSION && get_option('wpbc_updatenotice') == 'on') {
	    if( !isset($_POST['submitted'])) {
		    echo "<div id='wpbc-warning' class='updated fade'><p><strong>".sprintf( __('Your <a href="%s">Settings</a> for WP-BlackCheck are outdated! You should update them as soon as possible!', 'wp-blackcheck'), 'options-general.php?page=wp-blackcheck/wp-blackcheck.php') .'</strong></p></div>';
		}
	}
}


// Trigger for the reporting
function wpbc_blackcheck_report($param) {
    echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br /></div><h2>WP-BlackCheck</h2>';
    echo '<ul>';
    wpbc_check_spam_queue(get_option('wpbc_reportstack', '-1'));
    echo '</ul><p>' . __('Process finished', 'wp-blackcheck') . '.</p>';
    echo '</div>';
}

// Add our pages
function wpbc_blackcheck_add_page() {
	add_submenu_page('tools.php', 		'WP-BlackCheck', 'Report Spam',  'manage_options', __FILE__, 'wpbc_blackcheck_report');
	add_submenu_page('index.php', 		'WP-BlackCheck', 'Spam Stats',   'manage_options', __FILE__, 'wpbc_statspage');
	add_submenu_page('options-general.php', 'WP-BlackCheck', 'WP-BlackCheck','manage_options', __FILE__, 'wpbc_adminpage');
}

// extend the comment form - we want to know more
function wpbc_extend_commentform() {
	// timecode
	if ( get_option('wpbc_timecheck')) {
		echo '<p style="display: none;"><input type="hidden" id="comment_timestamp" name="comment_timestamp" value="' . base64_encode($_SERVER['REQUEST_TIME']) . '" /></p>';
	}
}

// Call for the admin page - page actually in adminpanel.php
function wpbc_adminpage() {
	global $wp_db_version;

	if (function_exists('current_user_can')) {
		if (current_user_can('manage_options')) {
			include('adminpanel.php');
		}
	}
}

function wpbc_statspage() {
	global $wp_db_version;

        if (function_exists('current_user_can')) {
                if (current_user_can('manage_options')) {
                        include('stats.php');
                }
        }
}

add_filter( 'plugin_action_links', 'wpbc_plugin_action_links', 10, 2 );

// Action hooks here

if (wpbc_min_wp('3.0')) {
	add_action('right_now_discussion_table_end', 'wpbc_discussion_table_end' );
} else {
	add_action('right_now_table_end', 'wpbc_table_end' );
}


function wpbc_activation() {
	if ( !wp_next_scheduled('wpbc_event') ) {
		wp_schedule_event(time(), 'daily', 'wpbc_event');
	}
}
register_activation_hook(__FILE__, 'wpbc_activation');

add_action('wp',			'wpbc_activation');
add_action('wpbc_event',		'wpbc_purge');
add_action('admin_notices',		'wpbc_blackcheck_warning');
add_action('admin_notices', 		'wpbc_version');
add_action('admin_notices', 		'wpbc_requirements');
add_action('activate_wp-blackcheck/wp-blackcheck.php', 'wpbc_install');
add_action('admin_menu', 		'wpbc_blackcheck_add_page');
add_action('comment_form', 		'wpbc_extend_commentform');
add_action('init', 			'wpbc_textdomain');
add_action('manage_comments_nav',	'wpbc_report_spam_button');
add_action('preprocess_comment', 	'wpbc_blackcheck', 1);
?>
