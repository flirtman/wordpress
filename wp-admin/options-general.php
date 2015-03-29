<?php
/**
 * General settings administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

/** WordPress Translation Install API */
require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

if ( ! current_user_can( 'manage_options' ) )
	wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );

$title = __('General Settings');
$parent_file = 'options-general.php';
/* translators: date and time format for exact current time, mainly about timezones, see http://php.net/date */
$timezone_format = _x('Y-m-d H:i:s', 'timezone date format');

/**
 * Display JavaScript on the page.
 *
 * @since 3.5.0
 */
function options_general_add_js() {
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		var $siteName = $( '#wp-admin-bar-site-name' ).children( 'a' ).first(),
			homeURL = ( <?php echo wp_json_encode( get_home_url() ); ?> || '' ).replace( /^(https?:\/\/)?(www\.)?/, '' );

		$( '#blogname' ).on( 'input', function() {
			var title = $.trim( $( this ).val() ) || homeURL;

			// Truncate to 40 characters.
			if ( 40 < title.length ) {
				title = title.substring( 0, 40 ) + '\u2026';
			}

			$siteName.text( title );
		});

		$("input[name='date_format']").click(function(){
			if ( "date_format_custom_radio" != $(this).attr("id") )
				$("input[name='date_format_custom']").val( $(this).val() ).siblings('.example').text( $(this).siblings('span').text() );
		});
		$("input[name='date_format_custom']").focus(function(){
			$( '#date_format_custom_radio' ).prop( 'checked', true );
		});

		$("input[name='time_format']").click(function(){
			if ( "time_format_custom_radio" != $(this).attr("id") )
				$("input[name='time_format_custom']").val( $(this).val() ).siblings('.example').text( $(this).siblings('span').text() );
		});
		$("input[name='time_format_custom']").focus(function(){
			$( '#time_format_custom_radio' ).prop( 'checked', true );
		});
		$("input[name='date_format_custom'], input[name='time_format_custom']").change( function() {
			var format = $(this);
			format.siblings('.spinner').css('display', 'inline-block'); // show(); can't be used here
			$.post(ajaxurl, {
					action: 'date_format_custom' == format.attr('name') ? 'date_format' : 'time_format',
					date : format.val()
				}, function(d) { format.siblings('.spinner').hide(); format.siblings('.example').text(d); } );
		});

		var languageSelect = $( '#WPLANG' );
		$( 'form' ).submit( function() {
			// Don't show a spinner for English and installed languages,
			// as there is nothing to download.
			if ( ! languageSelect.find( 'option:selected' ).data( 'installed' ) ) {
				$( '#submit', this ).after( '<span class="spinner language-install-spinner" />' );
			}
		});
	});
</script>
<?php
}
add_action('admin_head', 'options_general_add_js');

$options_help = '<p>' . __('The fields on this screen determine some of the basics of your site setup.') . '</p>' .
	'<p>' . __('Most themes display the site title at the top of every page, in the title bar of the browser, and as the identifying name for syndicated feeds. The tagline is also displayed by many themes.') . '</p>';

if ( ! is_multisite() ) {
	$options_help .= '<p>' . __('The WordPress URL and the Site URL can be the same (example.com) or different; for example, having the WordPress core files (example.com/wordpress) in a subdirectory instead of the root directory.') . '</p>' .
		'<p>' . __('If you want site visitors to be able to register themselves, as opposed to by the site administrator, check the membership box. A default user role can be set for all new users, whether self-registered or registered by the site admin.') . '</p>';
}

$options_help .= '<p>' . __( 'You can set the language, and the translation files will be automatically downloaded and installed (available if your filesystem is writable).' ) . '</p>' .
	'<p>' . __( 'UTC means Coordinated Universal Time.' ) . '</p>' .
	'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>';

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => $options_help,
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.wordpress.org/Settings_General_Screen" target="_blank">Documentation on General Settings</a>') . '</p>' .
	'<p>' . __('<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

include( ABSPATH . 'wp-admin/admin-header.php' );
?>

<div class="wrap">
<h2><?php echo esc_html( $title ); ?></h2>

<form method="post" action="options.php" novalidate="novalidate">
<?php settings_fields('general'); ?>

<table class="form-table">
<tr>
<th scope="row"><label for="blogname"><?php _e('Site Title') ?></label></th>
<td><input name="blogname" type="text" id="blogname" value="<?php form_option('blogname'); ?>" class="regular-text" /></td>
</tr>
<tr>
<th scope="row"><label for="blogdescription"><?php _e('Tagline') ?></label></th>
<td><input name="blogdescription" type="text" id="blogdescription" value="<?php form_option('blogdescription'); ?>" class="regular-text" />
<p class="description"><?php _e('In a few words, explain what this site is about.') ?></p></td>
</tr>
<?php if ( !is_multisite() ) { ?>
<tr>
<th scope="row"><label for="siteurl"><?php _e('WordPress Address (URL)') ?></label></th>
<td><input name="siteurl" type="url" id="siteurl" value="<?php form_option( 'siteurl' ); ?>"<?php disabled( defined( 'WP_SITEURL' ) ); ?> class="regular-text code<?php if ( defined( 'WP_SITEURL' ) ) echo ' disabled' ?>" /></td>
</tr>
<tr>
<th scope="row"><label for="home"><?php _e('Site Address (URL)') ?></label></th>
<td><input name="home" type="url" id="home" value="<?php form_option( 'home' ); ?>"<?php disabled( defined( 'WP_HOME' ) ); ?> class="regular-text code<?php if ( defined( 'WP_HOME' ) ) echo ' disabled' ?>" />
<p class="description"><?php _e('Enter the address here if you want your site homepage <a href="http://codex.wordpress.org/Giving_WordPress_Its_Own_Directory">to be different from the directory</a> you installed WordPress.'); ?></p></td>
</tr>
<tr>
<th scope="row"><label for="admin_email"><?php _e('E-mail Address') ?> </label></th>
<td><input name="admin_email" type="email" id="admin_email" value="<?php form_option( 'admin_email' ); ?>" class="regular-text ltr" />
<p class="description"><?php _e('This address is used for admin purposes, like new user notification.') ?></p></td>
</tr>
<tr>
<th scope="row"><?php _e('Membership') ?></th>
<td> <fieldset><legend class="screen-reader-text"><span><?php _e('Membership') ?></span></legend><label for="users_can_register">
<input name="users_can_register" type="checkbox" id="users_can_register" value="1" <?php checked('1', get_option('users_can_register')); ?> />
<?php _e('Anyone can register') ?></label>
</fieldset></td>
</tr>
<tr>
<th scope="row"><label for="default_role"><?php _e('New User Default Role') ?></label></th>
<td>
<select name="default_role" id="default_role"><?php wp_dropdown_roles( get_option('default_role') ); ?></select>
</td>
</tr>
<?php } else { ?>
<tr>
<th scope="row"><label for="new_admin_email"><?php _e('E-mail Address') ?> </label></th>
<td><input name="new_admin_email" type="email" id="new_admin_email" value="<?php form_option( 'admin_email' ); ?>" class="regular-text ltr" />
<p class="description"><?php _e('This address is used for admin purposes. If you change this we will send you an e-mail at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong>') ?></p>
<?php
$new_admin_email = get_option( 'new_admin_email' );
if ( $new_admin_email && $new_admin_email != get_option('admin_email') ) : ?>
<div class="updated inline">
<p><?php printf( __('There is a pending change of the admin e-mail to <code>%1$s</code>. <a href="%2$s">Cancel</a>'), esc_html( $new_admin_email ), esc_url( admin_url( 'options.php?dismiss=new_admin_email' ) ) ); ?></p>
</div>
<?php endif; ?>
</td>
</tr>
<?php } ?>
<tr>
<?php
$current_offset = get_option('gmt_offset');
$tzstring = get_option('timezone_string');

$check_zone_info = true;

// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}

?>
<th scope="row"><label for="timezone_string"><?php _e('Timezone') ?></label></th>
<td>

<select id="timezone_string" name="timezone_string">
<?php echo wp_timezone_choice($tzstring); ?>
</select>

	<span id="utc-time"><?php printf(__('<abbr title="Coordinated Universal Time">UTC</abbr> time is <code>%s</code>'), date_i18n($timezone_format, false, 'gmt')); ?></span>
<?php if ( get_option('timezone_string') || !empty($current_offset) ) : ?>
	<span id="local-time"><?php printf(__('Local time is <code>%1$s</code>'), date_i18n($timezone_format)); ?></span>
<?php endif; ?>
<p class="description"><?php _e('Choose a city in the same timezone as you.'); ?></p>
<?php if ($check_zone_info && $tzstring) : ?>
<br />
<span>
	<?php
	// Set TZ so localtime works.
	date_default_timezone_set($tzstring);
	$now = localtime(time(), true);
	if ( $now['tm_isdst'] )
		_e('This timezone is currently in daylight saving time.');
	else
		_e('This timezone is currently in standard time.');
	?>
	<br />
	<?php
	$allowed_zones = timezone_identifiers_list();

	if ( in_array( $tzstring, $allowed_zones) ) {
		$found = false;
		$date_time_zone_selected = new DateTimeZone($tzstring);
		$tz_offset = timezone_offset_get($date_time_zone_selected, date_create());
		$right_now = time();
		foreach ( timezone_transitions_get($date_time_zone_selected) as $tr) {
			if ( $tr['ts'] > $right_now ) {
			    $found = true;
				break;
			}
		}

		if ( $found ) {
			echo ' ';
			$message = $tr['isdst'] ?
				__('Daylight saving time begins on: <code>%s</code>.') :
				__('Standard time begins on: <code>%s</code>.');
			// Add the difference between the current offset and the new offset to ts to get the correct transition time from date_i18n().
			printf( $message, date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $tr['ts'] + ($tz_offset - $tr['offset']) ) );
		} else {
			_e('This timezone does not observe daylight saving time.');
		}
	}
	// Set back to UTC.
	date_default_timezone_set('UTC');
	?>
	</span>
<?php endif; ?>
</td>

</tr>
<tr>
<th scope="row"><?php _e('Date Format') ?></th>
<td>
	<fieldset><legend class="screen-reader-text"><span><?php _e('Date Format') ?></span></legend>
<?php
	/**
	* Filter the default date formats.
	*
	* @since 2.7.0
	* @since 4.0.0 Added ISO date standard YYYY-MM-DD format.
	*
	* @param array $default_date_formats Array of default date formats.
	*/
	$date_formats = array_unique( apply_filters( 'date_formats', array( __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );

	$custom = true;

	foreach ( $date_formats as $format ) {
		echo "\t<label title='" . esc_attr($format) . "'><input type='radio' name='date_format' value='" . esc_attr($format) . "'";
		if ( get_option('date_format') === $format ) { // checked() uses "==" rather than "==="
			echo " checked='checked'";
			$custom = false;
		}
		echo ' /> <span>' . date_i18n( $format ) . "</span></label><br />\n";
	}

	echo '	<label><input type="radio" name="date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m"';
	checked( $custom );
	echo '/> ' . __('Custom:') . ' </label><input type="text" name="date_format_custom" value="' . esc_attr( get_option('date_format') ) . '" class="small-text" /> <span class="example"> ' . date_i18n( get_option('date_format') ) . "</span> <span class='spinner'></span>\n";
?>
	</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php _e('Time Format') ?></th>
<td>
	<fieldset><legend class="screen-reader-text"><span><?php _e('Time Format') ?></span></legend>
<?php
	/**
	* Filter the default time formats.
	*
	* @since 2.7.0
	*
	* @param array $default_time_formats Array of default time formats.
	*/
	$time_formats = array_unique( apply_filters( 'time_formats', array( __( 'g:i a' ), 'g:i A', 'H:i' ) ) );

	$custom = true;

	foreach ( $time_formats as $format ) {
		echo "\t<label title='" . esc_attr($format) . "'><input type='radio' name='time_format' value='" . esc_attr($format) . "'";
		if ( get_option('time_format') === $format ) { // checked() uses "==" rather than "==="
			echo " checked='checked'";
			$custom = false;
		}
		echo ' /> <span>' . date_i18n( $format ) . "</span></label><br />\n";
	}

	echo '	<label><input type="radio" name="time_format" id="time_format_custom_radio" value="\c\u\s\t\o\m"';
	checked( $custom );
	echo '/> ' . __('Custom:') . ' </label><input type="text" name="time_format_custom" value="' . esc_attr( get_option('time_format') ) . '" class="small-text" /> <span class="example"> ' . date_i18n( get_option('time_format') ) . "</span> <span class='spinner'></span>\n";

	echo "\t<p>" . __('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>.') . "</p>\n";
?>
	</fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="start_of_week"><?php _e('Week Starts On') ?></label></th>
<td><select name="start_of_week" id="start_of_week">
<?php
global $wp_locale;

for ($day_index = 0; $day_index <= 6; $day_index++) :
	$selected = (get_option('start_of_week') == $day_index) ? 'selected="selected"' : '';
	echo "\n\t<option value='" . esc_attr($day_index) . "' $selected>" . $wp_locale->get_weekday($day_index) . '</option>';
endfor;
?>
</select></td>
</tr>
<?php do_settings_fields('general', 'default'); ?>

<?php
$languages = get_available_languages();
$translations = wp_get_available_translations();
if ( ! is_multisite() && defined( 'WPLANG' ) && '' !== WPLANG && 'en_US' !== WPLANG && ! in_array( WPLANG, $languages ) ) {
	$languages[] = WPLANG;
}
if ( ! empty( $languages ) || ! empty( $translations ) ) {
	?>
	<tr>
		<th width="33%" scope="row"><label for="WPLANG"><?php _e( 'Site Language' ); ?></label></th>
		<td>
			<?php
			$locale = get_locale();
			if ( ! in_array( $locale, $languages ) ) {
				$locale = '';
			}

			wp_dropdown_languages( array(
				'name'         => 'WPLANG',
				'id'           => 'WPLANG',
				'selected'     => $locale,
				'languages'    => $languages,
				'translations' => $translations,
				'show_available_translations' => ( ! is_multisite() || is_super_admin() ) && wp_can_install_language_pack(),
			) );

			// Add note about deprecated WPLANG constant.
			if ( defined( 'WPLANG' ) && ( '' !== WPLANG ) && $locale !== WPLANG ) {
				if ( is_super_admin() ) {
					?>
					<p class="description">
						<strong><?php _e( 'Note:' ); ?></strong> <?php printf( __( 'The %s constant in your %s file is no longer needed.' ), '<code>WPLANG</code>', '<code>wp-config.php</code>' ); ?>
					</p>
					<?php
				}
				_deprecated_argument( 'define()', '4.0', sprintf( __( 'The %s constant in your %s file is no longer needed.' ), 'WPLANG', 'wp-config.php' ) );
			}
			?>
		</td>
	</tr>
	<?php
}
?>
</table>

<?php do_settings_sections('general'); ?>

<?php submit_button(); ?>
</form>

</div>

<?php include( ABSPATH . 'wp-admin/admin-footer.php' ); ?>
