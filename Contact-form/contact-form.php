<?php
/*
Plugin Name: Contact Form
Description: Plugin to add a msg contact form to dash my site
Version: 1.0
*/

if (!defined('ABSPATH')) {
  exit;
}

function enqueue_bootstrap()
{
  wp_enqueue_style('bootstrap', 'bootstrap.min.css', array(), '4.5.2');
  wp_enqueue_script('bootstrap', 'bootstrap.min.js', array('jquery'), '4.5.2', true);
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap');










function contact_form_create_table()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'contact_form';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(50) NOT NULL,
        last_name varchar(50) NOT NULL,
        email varchar(50) NOT NULL,
        subject varchar(250) NOT NULL,
        message varchar(350) NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
register_activation_hook(__FILE__, 'contact_form_create_table');





function contact_form_delete_table()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'contact_form';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'contact_form_delete_table');

function my_contact_form_shortcode()
{
  $output = '';

  if (isset($_POST['submit_contact_form']) && wp_verify_nonce($_POST['_wpnonce'], 'submit_contact_form')) {
    contact_form_submit();
  }

  $output .= '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';

  $output .= '<label for="first_name">First Name:</label>';
  $output .= '<input type="text" name="first_name" id="first_name" required>';
  $output .= '<label for="last_name">Last Name:</label>';
  $output .= '<input type="text" name="last_name" id="last_name" required>';
  $output .= '<label for="email">Email:</label>';
  $output .= '<input type="email" name="email" id="email" required>';
  $output .= '<label for="subject">Subject:</label>';
  $output .= '<input type="text" name="subject" id="subject" required>';
  $output .= '<label for="message">Message:</label>';
  $output .= '<textarea name="message" id="message" rows="5" required></textarea>';
  $output .= '<input type="hidden" name="action" value="submit_contact_form">';
  $output .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce('submit_contact_form') . '">';
  $output .= '<input type="submit" name="submit_contact_form" value="Submit">';
  $output .= '</form>';

  return $output;
}




function register_contact_form_shortcode()
{
  add_shortcode('contact_form', 'my_contact_form_shortcode');
}
add_action('init', 'register_contact_form_shortcode');




function cf_add_menu_page()
{
  add_menu_page('contact-form', 'Messages', 'manage_options', 'cf_responses_page', 'cf_render_responses_page', 'dashicons-email-alt', 1);
  add_submenu_page('cf_responses_page', 'Contact Form ALl messages', 'personnalizer', 'manage_options', 'cf_Message_page', 'cf_render_Message_page');
}
add_action('admin_menu', 'cf_add_menu_page');




function contact_form_submit()
{

  $first_name = sanitize_text_field($_POST['first_name']);
  $last_name = sanitize_text_field($_POST['last_name']);
  $email = sanitize_email($_POST['email']);
  $subject = sanitize_text_field($_POST['subject']);
  $message = wp_kses_post($_POST['message']);

  global $wpdb;
  $table_name = $wpdb->prefix . 'contact_form';
  $wpdb->insert(
    $table_name,
    array(
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $email,
      'subject' => $subject,
      'message' => $message,
    ),
    array('%s', '%s', '%s', '%s', '%s')
  );



  wp_redirect(home_url('/test/'));
  exit;
}

add_action('admin_post_submit_contact_form', 'contact_form_submit');
add_action('admin_post_nopriv_submit_contact_form', 'contact_form_submit');



function cf_render_responses_page()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'contact_form';
  $results = $wpdb->get_results("SELECT * FROM $table_name");

  echo '<div class="wrap bg-dark">';
  echo '<h1>' . esc_html__('Contact Form Responses', 'contact-form') . '</h1>';
  echo '<p>' . esc_html__('View and manage responses submitted through the contact form.') . '</p>';
  echo '<table class="wp-list-table widefat fixed striped">';
  echo '<thead>';
  echo '<tr>';
  echo '<th style="width: 2rem;">' . esc_html__('ID', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('First name', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Last name', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Email', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Subject', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Message', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Date', 'contact-form') . '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach ($results as $row) {
    echo '<tr>';
    echo '<td>' . $row->id . '</td>';
    echo '<td>' . $row->first_name . '</td>';
    echo '<td>' . $row->last_name . '</td>';
    echo '<td>' . $row->email . '</td>';
    echo '<td>' . $row->subject . '</td>';
    echo '<td>' . $row->message . '</td>';
    echo '<td>' . $row->date . '</td>';
    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';

  echo '</div>';
}

?>
