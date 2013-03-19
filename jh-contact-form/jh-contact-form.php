<?php
/*
Plugin Name: JH Contact Form
Plugin URI:  http://joshhawcroft.com/wordpress/plugins/
Description: Contact form allows visitors to your site to send you email without publishing your email address.
Author: Josh Hawcroft
Version: 1.0
Author URI: http://joshhawcroft.com/wordpress/
License: GPLv2 or later
*/
/*  Copyright (c) 2013 Josh Hawcroft <wordpress@joshhawcroft.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class JHWPPlugContactForm
{
	private static $options = array();
	private static $validation_errors = array();
	private static $submission_error = false;
	private static $mail_from = '';

	public static function init()
	{
		add_shortcode('contact-form', 
			array('JHWPPlugContactForm', 'form_display'));
		add_action('admin_init', 
			array('JHWPPlugContactForm', 'register_settings'));
		add_action('admin_menu', 
			array('JHWPPlugContactForm', 'admin_add_menu'));
		add_action('admin_enqueue_scripts', 
			array('JHWPPlugContactForm', 'admin_scripts'));
		add_action('wp_enqueue_scripts', 
			array('JHWPPlugContactForm', 'form_scripts'));
			
		add_action('plugin_action_links_'.plugin_basename(__FILE__), 
			array('JHWPPlugContactForm', 'plugin_settings_link'));
		
		//add_action('admin_head',
		//	array('JHWPPlugContactForm', 'admin_add_help'));
	}
	
	
	public static function plugin_settings_link($links)
	{
		$settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'">Settings</a>'; 
  		array_unshift($links, $settings_link); 
  		return $links; 
	}
	
	
	public static function register_settings()
	{
		register_setting('jh-contact-form', 'jh-contact-form');
		
		add_settings_section('jh-contact-form-recipient', 'Recipient', 
			array('JHWPPlugContactForm', 'admin_display_section'), __FILE__);
		add_settings_field('email-user', 'User', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-recipient', array('field'=>'email-user'));
		//add_settings_field('email-custom', 'Email Address', 
		//	array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
		//	'jh-contact-form-recipient', array('field'=>'email-custom'));
			
		add_settings_section('jh-contact-form-fields', 'Optional Fields', 
			array('JHWPPlugContactForm', 'admin_display_section'), __FILE__);
		add_settings_field('form-show-phone', 'Phone', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-fields', array('field'=>'form-show-phone'));
			
		add_settings_section('jh-contact-form-reqd', 'Required Fields', 
			array('JHWPPlugContactForm', 'admin_display_section'), __FILE__);
		add_settings_field('form-req-name', 'Name', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-reqd', array('field'=>'form-req-name'));
		add_settings_field('form-req-phone', 'Phone', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-reqd', array('field'=>'form-req-phone'));
		add_settings_field('form-req-email', 'Email', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-reqd', array('field'=>'form-req-email'));
		add_settings_field('form-req-subject', 'Subject', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-reqd', array('field'=>'form-req-subject'));
		
		add_settings_section('jh-contact-form-action', 'Action', 
			array('JHWPPlugContactForm', 'admin_display_section'), __FILE__);
		add_settings_field('form-action', 'After Submitting', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-action', array('field'=>'form-action'));
		add_settings_field('form-thankyou', 'Thankyou Message', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-action', array('field'=>'form-thankyou'));
		add_settings_field('form-redirect', 'Redirect URL', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-action', array('field'=>'form-redirect'));
		
		add_settings_section('jh-contact-form-app', 'Appearance', 
			array('JHWPPlugContactForm', 'admin_display_section'), __FILE__);
		add_settings_field('form-styles', 'Use Built-in Stylesheet', 
			array('JHWPPlugContactForm', 'admin_display_field'), __FILE__,
			'jh-contact-form-app', array('field'=>'form-styles'));
	}
	
	
	public static function defaults_init()
	{
		$defaults = array(
			'email-user' => 'admin',
			'email-custom' => '',
			'form-show-phone' => 0,
			'form-req-name' => 0,
			'form-req-email' => 0,
			'form-req-phone' => 0,
			'form-req-subject' => 0,
			'form-action' => 'thankyou',
			'form-thankyou' => 'Thankyou for your email.',
			'form-redirect' => '',
			'form-styles' => 1,
		);
			
		if( !get_option('jh-contact-form') )
			add_option('jh-contact-form', $defaults, '', 'yes');
			
		JHWPPlugContactForm::$options = get_option('jh-contact-form');
		JHWPPlugContactForm::$options = array_merge(array_keys($defaults), 
			JHWPPlugContactForm::$options);
		update_option('jh-contact-form', JHWPPlugContactForm::$options);
	}
	
	public static function admin_text_help()
	{ ?>
<h2>Configuration</h2>
<p>Don't eat the jelly beans.</p>
<?php }
	
	public static function admin_text_about()
	{ ?>
<h2>About this Plugin</h2>
<p>Copyright &copy; 2013 Joshua Hawcroft</p>
<?php }
	
	public static function admin_add_help()
	{
		get_current_screen()->add_help_tab(array(
			'id'=>'jh-contact-form-help',
			'title'=>'Configuration',
			'content'=>'',
			'callback'=>array('JHWPPlugContactForm', 'admin_text_help')
			));
		get_current_screen()->add_help_tab(array(
			'id'=>'jh-contact-form-about',
			'title'=>'About this Plugin',
			'content'=>'',
			'callback'=>array('JHWPPlugContactForm', 'admin_text_about')
			));
		/*get_current_screen()->set_help_sidebar(
			'<p>Content</p>'
			);*/
	}
	
	public static function admin_scripts()
	{
		JHWPPlugContactForm::defaults_init();
		wp_enqueue_style('', plugins_url('css/admin-style.css', __FILE__ ));
	}
	
	public static function form_scripts()
	{
		JHWPPlugContactForm::defaults_init();
		if (JHWPPlugContactForm::$options['form-styles']==1)
			wp_enqueue_style('', plugins_url('css/form-style.css', __FILE__ ));
	}
	
	public static function admin_add_menu()
	{
		add_options_page(
			'Contact Form Settings', 
			'Contact Form', 
			'manage_options', 
			__FILE__,
			array('JHWPPlugContactForm', 'admin_display')
			);
	}
	
	public static function admin_display_section($inArgs)
	{
	}
	
	public static function admin_display_field($inArgs)
	{
		switch ($inArgs['field']):
		case 'email-user':
		?><select name="jh-contact-form[email-user]">
			<?php
			$blogusers = get_users();
			foreach ($blogusers as $user)
			{
				print '<option value="'.$user->ID.'"'.((JHWPPlugContactForm::$options['email-user']==$user->ID)?' selected="true"':'').'>'.$user->user_nicename.'</option>';
				//print '<option value="'.$user->user_email.'"'.((JHWPPlugContactForm::$options['email-user']==$user->user_email)?' selected="true"':'').'>'.$user->user_nicename.'</option>';
			}
			?>
		</select><?php
			break;
		case 'email-custom':
			print '<input type="text" name="jh-contact-form[email-custom]" size="30" value="'.JHWPPlugContactForm::$options['email-custom'].'">';
			break;
			
		case 'form-show-phone':
			print '<input type="checkbox" name="jh-contact-form[form-show-phone]" '.((JHWPPlugContactForm::$options['form-show-phone']==1)?' checked="true"':'').' value="1">';
			break;
			
		case 'form-req-name':
			print '<input type="checkbox" name="jh-contact-form[form-req-name]" '.((JHWPPlugContactForm::$options['form-req-name']==1)?' checked="true"':'').' value="1">';
			break;
		case 'form-req-phone':
			print '<input type="checkbox" name="jh-contact-form[form-req-phone]" '.((JHWPPlugContactForm::$options['form-req-phone']==1)?' checked="true"':'').' value="1">';
			break;
		case 'form-req-email':
			print '<input type="checkbox" name="jh-contact-form[form-req-email]" '.((JHWPPlugContactForm::$options['form-req-email']==1)?' checked="true"':'').' value="1">';
			break;
		case 'form-req-subject':
			print '<input type="checkbox" name="jh-contact-form[form-req-subject]" '.((JHWPPlugContactForm::$options['form-req-subject']==1)?' checked="true"':'').' value="1">';
			break;
		
		case 'form-action':
			print '<select name="jh-contact-form[form-action]">';
			print '<option value="thankyou"'.((JHWPPlugContactForm::$options['form-action']=='thankyou')?' selected="true"':'').'>Show Thankyou Message</option>';
			print '<option value="redirect"'.((JHWPPlugContactForm::$options['form-action']=='redirect')?' selected="true"':'').'>Go to Redirect URL</option>';
			print '</select>';
			break;
		case 'form-thankyou':
			print '<textarea name="jh-contact-form[form-thankyou]" rows="3" cols="50" wrap="virtual">'.JHWPPlugContactForm::$options['form-thankyou'].'</textarea>';
			break;
		case 'form-redirect':
			print '<input type="text" name="jh-contact-form[form-redirect]" size="50" value="'.JHWPPlugContactForm::$options['form-redirect'].'">';
			break;
		
		case 'form-styles':
			print '<input type="checkbox" name="jh-contact-form[form-styles]" '.((JHWPPlugContactForm::$options['form-styles']==1)?' checked="true"':'').' value="1">';
			break;
			
		endswitch;
	}
	
	public static function admin_display()
	{
?>
<div class="wrap">
<?php screen_icon('jh-contact-form'); ?>
<h2>Contact Form Settings</h2>

<form method="post" action="options.php">
	<?php settings_fields('jh-contact-form'); ?>
	<?php do_settings_sections(__FILE__); ?>
    <?php do_settings_fields('jh-contact-form-options', 'jh-contact-form-recipient'); ?>
    <?php do_settings_fields('jh-contact-form-options', 'jh-contact-form-fields'); ?>
    <?php do_settings_fields('jh-contact-form-options', 'jh-contact-form-reqd'); ?>
    <?php submit_button(); ?>
</form>
</div>
<?php
	}
	
	
	public static function form_field_sanitize($inName)
	{
		if (!isset($_POST['contact-form-'.$inName])) return;
		$_POST['contact-form-'.$inName] = strip_tags($_POST['contact-form-'.$inName]);
	}
	
	
	public static function form_field_filled($inName, $inShowable = false, $inRequireable = false)
	{
		JHWPPlugContactForm::form_field_sanitize($inName);
		
		if ($inShowable)
		{
			if (JHWPPlugContactForm::$options['form-show-'.$inName]!=1)
				return true;
		}
		
		if ($inRequireable)
		{
			if (JHWPPlugContactForm::$options['form-req-'.$inName]!=1)
				return true;
		}
			
		if ( (!isset($_POST['contact-form-'.$inName])) || (trim($_POST['contact-form-'.$inName]) == '') ) return false;
		
		return true;
	}
	
	
	public static function clear_form_fields()
	{
		unset($_POST['contact-form-name']);
		unset($_POST['contact-form-phone']);
		unset($_POST['contact-form-email']);
		unset($_POST['contact-form-subject']);
		unset($_POST['contact-form-message']);
	}

	
	public static function get_mail_from_name($inContent)
	{
		return JHWPPlugContactForm::$mail_from;
	}
	
	
	public static function form_check_submit()
	{
		if (!JHWPPlugContactForm::form_field_filled('name', false, true))
			JHWPPlugContactForm::$validation_errors[] = 'Your Name';
		if (!JHWPPlugContactForm::form_field_filled('email', false, true))
			JHWPPlugContactForm::$validation_errors[] = 'Email';
		if (!JHWPPlugContactForm::form_field_filled('phone', true, true))
			JHWPPlugContactForm::$validation_errors[] = 'Phone';
		if (!JHWPPlugContactForm::form_field_filled('subject', false, true))
			JHWPPlugContactForm::$validation_errors[] = 'Subject';
		if (!JHWPPlugContactForm::form_field_filled('message', false, false))
			JHWPPlugContactForm::$validation_errors[] = 'Message';
		
		if (count(JHWPPlugContactForm::$validation_errors) != 0)
			return;
		
		$message = '
<html>
<head>
	<title>Contact Form'.get_bloginfo('name').'</title>
</head>
<body>
	<table>
		<tr>
			<td width="160">Sender Name</td><td>'.$_POST['contact-form-name'].'</td>
		</tr>
		<tr>
			<td>Email</td><td>'.$_POST['contact-form-email'].'</td>
		</tr>';
		if (JHWPPlugContactForm::$options['form-show-phone']==1)
			$message .= '
		<tr>
			<td>Phone</td><td>'.$_POST['contact-form-phone'].'</td>
		</tr>';
		$message .= '
		<tr>
			<td>Subject</td><td>'.$_POST['contact-form-subject'].'</td>
		</tr>
		<tr>
			<td valign="top">Message</td><td>'.nl2br($_POST['contact-form-message']).'</td>
		</tr>
		<tr>
			<td>Site</td><td>'.get_bloginfo('url').'</td>
		</tr>
		<tr>
			<td><br /></td><td><br /></td>
		</tr>
	</table>
</body>
</html>
';

		if (trim($_POST['contact-form-email']) == '')
			JHWPPlugContactForm::$mail_from = get_bloginfo('name').' <'.get_bloginfo('admin_email').'>';
		else if (trim($_POST['contact-form-name']) == '')
			JHWPPlugContactForm::$mail_from = $_POST['contact-form-email'].' <'.$_POST['contact-form-email'].'>';
		else
			JHWPPlugContactForm::$mail_from = $_POST['contact-form-name'].' <'.$_POST['contact-form-email'].'>';

		$headers = 'MIME-Version: 1.0'."\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
		$headers .= 'From: '.JHWPPlugContactForm::$mail_from."\r\n";
	
		//add_filter('wp_mail_from_name', array('JHWPPlugContactForm',
		//	'get_mail_from_name'), 10, 1);
		
		$to_user = get_user_by('id', JHWPPlugContactForm::$options['email-user']);
		$to = $to_user->first_name.' '.$to_user->last_name.' <'.$to_user->user_email.'>';

		if (!wp_mail(
				$to,
				$_POST['contact-form-subject'],
				$message,
				$headers
			))
			JHWPPlugContactForm::$submission_error = true;
		
		else
			JHWPPlugContactForm::clear_form_fields();
	}
	
	public static function form_display()
	{
		if (isset($_POST['contact-form-submit']))
			JHWPPlugContactForm::form_check_submit();
	
		$page_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == "on" ? 'https://' : 'http://' ).$_SERVER['SERVER_NAME'].strip_tags( $_SERVER['REQUEST_URI'] );
?>
<form method="post" action="<?php print $page_url; ?>" class="jh-contact-form">

	<?php if (count(JHWPPlugContactForm::$validation_errors) != 0): ?><p class="jh-contact-form-errors">
		Sorry, the form cannot be submitted because one or more required fields have not been completed.<?php //print implode(', ',JHWPPlugContactForm::$validation_errors); ?>
	</p><?php endif; ?>
	
	<?php if (JHWPPlugContactForm::$submission_error): ?><p class="jh-contact-form-errors">
		Sorry, the form cannot be submitted because of a mail gateway error.
	</p><?php endif; ?>
	
	<?php if ( (count(JHWPPlugContactForm::$validation_errors)==0) &&
		(!JHWPPlugContactForm::$submission_error) && 
		isset($_POST['contact-form-submit']) ): ?><p class="jh-contact-form-thankyou">
		<?php print JHWPPlugContactForm::$options['form-thankyou']; ?>
		<?php
		if (JHWPPlugContactForm::$options['form-action']=='redirect')
			print '<script type="text/javascript">window.location.href=\''.JHWPPlugContactForm::$options['form-redirect'].'\';</script>';
		?>
	</p><?php endif; ?>

	<label for="contact-form-name">Your Name: <?php print ((JHWPPlugContactForm::$options['form-req-name']==1)?'*':''); ?></label>
	<input type="text" name="contact-form-name" class="jh-contact-form-name" size="30"<?php print ((isset($_POST['contact-form-name']))?' value="'.$_POST['contact-form-name'].'"':''); ?>><br>
	
	<label for="contact-form-email">Email: <?php print ((JHWPPlugContactForm::$options['form-req-email']==1)?'*':''); ?></label>
	<input type="text" name="contact-form-email" class="jh-contact-form-email" size="40"<?php print ((isset($_POST['contact-form-email']))?' value="'.$_POST['contact-form-email'].'"':''); ?>><br>
	
	<?php if (JHWPPlugContactForm::$options['form-show-phone']==1): ?><label for="contact-form-phone">Phone: <?php print ((JHWPPlugContactForm::$options['form-req-phone']==1)?'*':''); ?></label>
	<input type="text" name="contact-form-phone" class="jh-contact-form-phone" size="20"<?php print ((isset($_POST['contact-form-phone']))?' value="'.$_POST['contact-form-phone'].'"':''); ?>><br><?php endif; ?>
	
	<label for="contact-form-subject">Subject: <?php print ((JHWPPlugContactForm::$options['form-req-subject']==1)?'*':''); ?></label>
	<input type="text" name="contact-form-subject" class="jh-contact-form-subject" size="50"<?php print ((isset($_POST['contact-form-subject']))?' value="'.$_POST['contact-form-subject'].'"':''); ?>><br>
	
	<label for="contact-form-message">Message: *</label>
	<textarea rows="10" cols="50" name="contact-form-message" class="jh-contact-form-message" wrap="virtual"><?php print ((isset($_POST['contact-form-message']))?$_POST['contact-form-message']:''); ?></textarea><br>
	
	<input type="submit" name="contact-form-submit" class="jh-contact-form-submit" value="Submit" class="button-secondary">
</form>
<br clear="both">
<?php
	}
}


add_action( 'init', array('JHWPPlugContactForm', 'init') );

?>