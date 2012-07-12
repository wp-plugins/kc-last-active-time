<?php
/*
	Plugin Name: KC Last Active Time
	Plugin URI: http://krumch.com/kc-last-active-time.html
	Description: A "last active time" monitor for members
	Version: 1.0
	Author: Krum Cheshmedjiev
	Author URI: http://krumch.com
	Tested up to: 3.4.1
	Requires at least: 3.0
	Requires: WordPress® 3.0+, PHP 5.2+
	Tags: last active time, monitor, active, activity, time, member, members, members info, developers tools, tool
*/

function kc_lat_activate() {
	if(!get_option('kc_lat')) update_option('kc_lat', array('save' => 1, 'format' => 'F j, Y', 'class' => 'kc_last_active_time'));
}

register_activation_hook( __FILE__, 'kc_lat_activate' );

function kc_lat_deactivate() {
	$kc_lat_options = get_option('kc_lat');
	if(!$kc_lat_options['save']) {
		global $wpdb;
		$wpdb->query("DELETE FROM `".$wpdb->usermeta."` WHERE `meta_key` = '".$wpdb->prefix."kc_lat'");
		delete_option('kc_lat');
	}
}

register_deactivation_hook( __FILE__, 'kc_lat_deactivate' );

function kc_lat_admin() {
	$getinfo = 1;
	if(isset($_POST['Submit'])) {
		if('' == $_POST['kc_lat_format']) {
?>
				<div class="updated"><p><strong>You must provide Date format.</strong></p></div>
<?php
		} else {
			$kc_lat_options['save'] = (int)$_POST['kc_lat_save'];
			$kc_lat_options['format'] = $_POST['kc_lat_format'];
			$kc_lat_options['class'] = strtolower($_POST['kc_lat_class']);
			update_option('kc_lat', $kc_lat_options);
			$getinfo = 0;
?>
				<div class="updated"><p><strong>Options saved.</strong></p></div>
<?php
		}
	}
	if($getinfo) $kc_lat_options = get_option('kc_lat');
?>
				<h2>Handling member's last active time</h2>
	<form name="admin_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<p><table border=0>
			<tr><td><span style="color: #164A61;">Save times on disactivate (include these options):</span><input type="radio" name="kc_lat_save" value="1"<?php echo ($kc_lat_options['save'] == 1)?' checked':''; ?>>Yes&nbsp;&nbsp;<input type="radio" name="kc_lat_save" value="0"<?php echo ($kc_lat_options['save'] == 0)?' checked':''; ?>>No</td></tr>
			<tr><td><span style="color: #164A61;">Date format:</span><input name="kc_lat_format" value="<?php echo $kc_lat_options['format']; ?>"> (any format, acceptable by <a href="http://php.net/manual/en/function.date.php">date()</a> PHP function)</td></tr>
			<tr><td><span style="color: #164A61;">Output class:</span><input name="kc_lat_class" value="<?php echo $kc_lat_options['class']; ?>">(lowercase)</td></tr>
			<tr><td align=center><p class="submit"><input type="submit" name="Submit" value="Update Options" /></p></td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td><span style="color: #164A61;">Usage:</span><br/><b>Functions</b><br><br>
(HTML)string kc_lat_get($options = array('userid' => NULL));<br>userid - WP userid, or current user.<br>Returns formatted 'date' string. If "output class" above is set, it will be used to format the output as HTML code.<br><br>
boolean kc_lat_put($options = array('userid' => NULL));<br>userid - WP userid, or current user.<br><br><br>
<b>Short code</b><br>
[kc_last_active_time userid=currentuser] and [kc_lat userid=currentuser]<br>
Same parameter as function kc_lat_get().
			</td></tr>
		</table>
		</p>
			</form>
</div>
<?php
}

function kc_lat_adminmenu() {
	add_options_page("kc_lat", "KC Last Active Time", 1, "kc_lat", "kc_lat_admin");
}

add_action('admin_menu', 'kc_lat_adminmenu');

# Functions

function kc_lat_get($options) {
	$rezz = '';
	if(is_user_logged_in()) {
		if(!$options['userid'] or $options['userid'] == '') {
			global $current_user;
			$options['userid'] = $current_user->ID;
		}
		if(!$kc_lat_options) $kc_lat_options = get_option('kc_lat');
		$kclat = date($kc_lat_options['format'], get_user_option($wpdb->prefix."kc_last_active_time", $options['userid']));
		if(isset($kc_lat_options['class'])) {
			$rezz .= '<span class="'.$kc_lat_options['class']."\">$kclat</span>";
		} else {
			$rezz .= $kclat;
		}
	}
	return $rezz;
}

add_shortcode('kc_last_active_time', 'kc_lat_get');
add_shortcode('kc_lat', 'kc_lat_get');

function kc_lat_put($options = array('userid' => NULL)) {
	if(is_user_logged_in()) {
		global $wpdb;
		if(!isset($options['userid']) or !$options['userid']) {
			global $current_user;
			$options['userid'] = $current_user->ID;
		}
		return update_user_option($options['userid'], $wpdb->prefix."kc_last_active_time", time());
	}
	return true;
}

add_action( 'wp_loaded', 'kc_lat_put' );

?>
