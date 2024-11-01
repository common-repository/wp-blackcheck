<?php
/**
 * @package WP-BlackCheck-Functions
 * @author Viktoria Rei Bauer
 * @version 2.7.2
 */
/*
 Function library used with WP-BlackCheck

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

// Hostname for our blog
function wpbc_get_host() {
	return urlencode(parse_url(get_option('home'), PHP_URL_HOST));
}

// Check an IP
function wpbc_do_check($userip) {
	$querystring = 'user_ip='.$userip.'&mode=query&bloghost='.wpbc_get_host();
	$response = wpbc_do_request($querystring, WPBC_SERVER, '/webservice/query.php');
	return $response;
}

// Check Hash
function wpbc_check_hash($hash) {
	$querystring = 'comment='.$hash.'&mode=hash&bloghost='.wpbc_get_host();
	$response = wpbc_do_request($querystring, WPBC_SERVER, '/webservice/query.php');
	return $response;
}

// Report an IP
function wpbc_do_report($userip) {
	$response = wpbc_do_check($userip);
	if ($response[1] == "NOT LISTED") {
		$querystring = 'user_ip='.$userip.'&mode=report&bloghost='.wpbc_get_host();
		$response = wpbc_do_request($querystring, WPBC_SERVER, '/webservice/query.php');
		update_option('wpbc_counter_report', get_option('wpbc_counter_report') + 1 );
		return $response;
	}
}

// Actual reporting happens here
//- we loop through the comments
function wpbc_check_spam_queue($limit='-1') {
	global $wpdb;
	if (!is_numeric($limit)) $limit = '-1';
	if ($limit == -1) {
		$comments = $wpdb->get_results("SELECT comment_author_IP FROM $wpdb->comments WHERE comment_approved = 'spam' GROUP BY comment_author_IP");
	} else {
		$comments = $wpdb->get_results("SELECT comment_author_IP FROM $wpdb->comments WHERE comment_approved = 'spam' GROUP BY comment_author_IP LIMIT $limit");
	}

	if ($comments) {
		foreach($comments as $comment) {
			$userip = $comment->comment_author_IP;
			// prevent reporting listed hosts
			$response = wpbc_do_check($userip);
			// found someone new?
			if ($response[1] == "NOT LISTED") {
				$response = wpbc_do_report($userip);
				echo '<li>' . __('Reported new:', 'wp-blackcheck') . ' ' .$userip.'</li>';
			} else {
				echo '<li>' . __('Already known:', 'wp-blackcheck') . ' ' .$userip.'</li>';
			}
			// Purge IP from the spam quarantine
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam' AND comment_author_IP = '$userip'");
		}
		$comments = $wpdb->get_results("SELECT comment_author_IP FROM $wpdb->comments WHERE comment_approved = 'spam'");
		if ($comments)  echo '<p>' . __('There are still some spam comments in your queue. Click <a href="index.php?page=wp-blackcheck/wp-blackcheck.php">here</a> to process the next batch.', 'wp-blackcheck') . '</p>';

	} else {
		echo '<p>' . __('Nothing to report. Your spam queue is empty.', 'wp-blackcheck') . '</p>';
	}
}

// Version check
function wpbc_version() {
	if (get_option('wpbc_updatenotice') == 'on') {
		$querystring = 'mode=wp-plugver&bloghost='.wpbc_get_host();
		$response = wpbc_do_request($querystring, WPBC_SERVER, '/webservice/query.php');
		$serverversion = explode('.', (string)$response[1]);
		$plugversion = explode('.', WPBC_VERSION);

		if ( $serverversion[0] > $plugversion[0]) {
			echo "<div id='wpbc-info' class='updated fade'><p><strong>" . __('There is a new version of WP-BlackCheck available. It offers a ton of new features.', 'wp-blackcheck') . '</strong></p></div>';
		}

		if ($serverversion[1] > $plugversion[1]) {
			echo "<div id='wpbc-info' class='updated fade'><p><strong>" . __('There is a new version of WP-BlackCheck available which offers some enhancements!', 'wp-blackcheck') . '</strong></p></div>';
		}

		if ( $serverversion[2] > $plugversion[2] ) {
			if ($serverversion[1] == $plugversion[1]) echo "<div id='wpbc-info' class='updated fade'><p><strong>" . __('There is a new version of WP-BlackCheck available which fixes some bugs!', 'wp-blackcheck') . '</strong></p></div>';
		}
	}
}

function wpbc_min_wp($version) {
	return version_compare(	$GLOBALS['wp_version'],	 $version. 'alpha', '>=');
}

function wpbc_min_php($version) {
	return version_compare( phpversion(), $version, '>=' );
}

function wpbc_requirements() {
	if (!wpbc_min_wp('2.9') || !wpbc_min_php('5.0') ) {
		echo "<div id='wpbc-warning' class='updated fade'><p><strong>". __('Your WordPress installation does not meet the minimum requirements for running WP-BlackCheck!', 'wp-blackcheck'). '<br />';
		echo __('WP-BlackCheck needs at least PHP 5 and WordPress 2.9!', 'wp-blackcheck') .'</strong></p></div>';
	}
}

// Doing the check - request
function wpbc_do_request($request, $host, $path, $port = 80) {
	global $wp_version;

	if ( function_exists( 'wp_remote_post' ) ) {
		$http_args = array(
			'body'			=> $request,
			'headers'		=> array(
			'Content-Type'		=> 'application/x-www-form-urlencoded; ' . 'charset=' . get_option( 'blog_charset' ),
			'Host'			=> $host,
			'User-Agent'		=> "WordPress/$wp_version | CheckBlack/" . WPBC_VERSION,
		     ),
		     'httpversion'	=> '1.0',
		     'timeout'		=> 50
		);
		$myurl = 'http://' . $host . $path;

		$response = wp_remote_post( $myurl, $http_args );

		if ( is_wp_error( $response ) )
			return '';

		return array( $response['headers'], $response['body'] );

	} else {

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded; charset=" . get_option('blog_charset') . "\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: WordPress/$wp_version | CheckBlack/" . WPBC_VERSION . "\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;

		$response = '';
		if( false != ( $fs = @fsockopen($host, $port, $errno, $errstr, 50) ) ) {
			fwrite($fs, $http_request);

			while ( !feof($fs) )
				$response .= fgets($fs, 1160); // One TCP-IP packet
				fclose($fs);
			$response = explode("\r\n\r\n", $response, 2);
		}
	}
	return $response;
}

// all to uppercase - we need CAPS!
function wpbc_ucase_all($string) {
	$temp = preg_split('/(\W)/', str_replace("_", "-", $string), -1, PREG_SPLIT_DELIM_CAPTURE);
	foreach ($temp as $key=>$word) {
		$temp[$key] = ucfirst(strtolower($word));
	}
	return join ('', $temp);
}

// Get a usable HTTP Header (all in caps)
function wpbc_get_http_headers() {
	$headers = array();
	foreach ($_SERVER as $h => $v)
		if (preg_match('/HTTP_(.+)/', $h, $hp))
			$headers[str_replace("_", "-", wpbc_ucase_all($hp[1]))] = $v;
		return $headers;
}

// Link at the dashboard to get to the settings
function wpbc_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/wp-blackcheck.php' ) ) {
		$links[] = '<a href="options-general.php?page=wp-blackcheck/wp-blackcheck.php">'.__('Settings').'</a>';
	}

	return $links;
}


// Purge comment spam older than 2 weeks
function wpbc_purge() {
    if ( get_option('wpbc_autopurge') ) {
    	global $wpdb;
	    $now_gmt = current_time('mysql', 1);
	    $comment_ids = $wpdb->get_col("SELECT comment_id FROM $wpdb->comments WHERE DATE_SUB('$now_gmt', INTERVAL 14 DAY) > comment_date_gmt AND comment_approved = 'spam'");
	    if ( empty( $comment_ids ) )
		    return;

    	$comma_comment_ids = implode( ', ', array_map('intval', $comment_ids) );

    	do_action( 'delete_comment', $comment_ids );
	    $wpdb->query("DELETE FROM $wpdb->comments WHERE comment_id IN ( $comma_comment_ids )");
	    $wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id IN ( $comma_comment_ids )");
	    clean_comment_cache( $comment_ids );
	    $wpdb->query("OPTIMIZE TABLE $wpdb->comments");
    }
}

// Installer - Option handling
function wpbc_install() {
	if ( !get_option('wpbc_stacksize') ) {
		update_option('wpbc_statistics',		'on');
		update_option('wpbc_reportstack', 		'100');
		update_option('wpbc_ip_already_spam', 		'on');
		update_option('wpbc_nobbcode', 			'');
		update_option('wpbc_nobbcode_autoreport',	'');
		update_option('wpbc_timecheck', 		'on');
		update_option('wpbc_timecheck_autoreport',	'on');
		update_option('wpbc_linklimit',			'');
		update_option('wpbc_linklimit_number',		'2');
		update_option('wpbc_trackback_list', 		'');
		update_option('wpbc_trackback_check', 		'on');
		update_option('wpbc_autopurge',           	'');
		update_option('wpbc_emailnotice',           	'');
		update_option('wpbc_updatenotice',           	'on');
		update_option('wpbc_redirect',			'on');
		update_option('wpbc_redirect_to',		'http://www.fbi.gov/wanted/wanted_terrorists');
		update_option('wpbc_hash',			'');

		// Zero stats
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
}

function wpbc_reset() {
	update_option('wpbc_statistics',		'on');
	update_option('wpbc_reportstack', 		'100');
	update_option('wpbc_ip_already_spam', 		'on');
	update_option('wpbc_nobbcode', 			'');
	update_option('wpbc_nobbcode_autoreport',	'');
	update_option('wpbc_timecheck', 		'on');
	update_option('wpbc_timecheck_autoreport',	'');
	update_option('wpbc_linklimit',			'');
	update_option('wpbc_linklimit_number',		'2');
	update_option('wpbc_trackback_list', 		'');
	update_option('wpbc_trackback_check', 		'on');
	update_option('wpbc_autopurge',			'');
	update_option('wpbc_emailnotice',           	'');
	update_option('wpbc_updatenotice',           	'on');
	update_option('wpbc_redirect',			'on');
	update_option('wpbc_redirect_to',               'http://www.fbi.gov/wanted/wanted_terrorists');
	update_option('wpbc_hash',			'');
}

// Locales loading
function wpbc_textdomain() {
	if (function_exists('load_plugin_textdomain')) {
		if ( !defined('WP_PLUGIN_DIR') ) {
			load_plugin_textdomain('wp-blackcheck', str_replace( ABSPATH, '', dirname(__FILE__) ) . '/languages');
		} else {
			load_plugin_textdomain('wp-blackcheck', false, dirname( plugin_basename(__FILE__) ) . '/languages');
		}
	}
}

// Extract domain name from URL
function wpbc_get_domainname($url) {
	preg_match('@^(?:http://)?([^/]+)@i',$url, $matches);
	return $matches[1];
}


// Update counters in one place
function wpbc_counter($counter) {
	update_option( 'blackcheck_spam_count', get_option('blackcheck_spam_count') + 1 );

	switch($counter) {
		case 'tbvia':
			update_option( 'wpbc_counter_tbvia', get_option('wpbc_counter_tbvia') + 1 );
			break;
		case 'tburl':
			update_option( 'wpbc_counter_tburl', get_option('wpbc_counter_tburl') + 1 );
			break;
		case 'list':
			update_option( 'wpbc_counter_blacklist', get_option('wpbc_counter_blacklist') + 1 );
			break;
		case 'squeue':
			update_option( 'wpbc_counter_spamqueue', get_option('wpbc_counter_spamqueue') + 1 );
			break;
		case 'bbCode':
			update_option( 'wpbc_counter_bbcode', get_option('wpbc_counter_bbcode') + 1 );
			break;
		case 'speed':
			update_option( 'wpbc_counter_speed', get_option('wpbc_counter_speed') + 1 );
			break;
		case 'link':
			update_option( 'wpbc_counter_link', get_option('wpbc_counter_link') + 1 );
			break;
		case 'hash':
			update_option( 'wpbc_counter_hash', get_option('wpbc_counter_hash') + 1 );
			break;
	}
}


// Spam goes to?
function wp_spammer($message) {
	if ( get_option('wpbc_redirect') ){
		wp_redirect(get_option('wpbc_redirect_to'));
		// header('Location: ' . get_option('wpbc_redirect_to') );
		exit;
	} else {
		wp_die($message);
	}
}

?>
