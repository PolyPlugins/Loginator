<?php

/**
 * Plugin Name: Loginator
 * Plugin URI: https://www.polyplugins.com/product/loginator/
 * Description: Adds a simple global function for logging to files for developers. 
 * Version: 2.0.0
 * Author: Poly Plugins
 * Author URI: https://www.polyplugins.com
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) exit;

require('classes/Backend/Settings.php');

register_activation_hook(__FILE__, array('Loginator', 'activation'));

class Loginator {
  /**
	 * Full path and filename of plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin    Full path and filename of plugin.
	 */
  private $plugin;

  /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The ID of this plugin.
	 */
  private $plugin_slug;

  /**
	 * The slug but with _ instead of -
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug_id    The slug but with _ instead of -
	 */
  private $plugin_slug_id;

  /**
	 * The unique name for the plugins options.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options_name    The name used to uniquely identify this plugins options.
	 */
  private $options_name;

  /**
	 * The location of the options page.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options_page    The slug for where the page should be located. 'options-general.php' for Settings tab.
	 */
  private $options_page;

  /**
	 * The plugin's options array
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    The plugin's options array
	 */
  private $options;
  
  /**
	 * The plugin's instance
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      object    $instance    The plugin's instance
	 */
  private static $instance = null;

  public function __construct()
  {
    $this->plugin           = __FILE__;
    $this->plugin_slug      = dirname( plugin_basename( $this->plugin ) );
    $this->plugin_slug_id   = str_replace( '-', '_', $this->plugin_slug );
    $this->options_name     = $this->plugin_slug_id . '_options';
    $this->options_page     = 'options-general.php';
    $this->options          = get_option( $this->options_name );
  }
  
  /**
   * Check if an instance already exists before creating a new one.
   *
   * @return object
   */
  public static function getInstance()
  {
    if (!isset(static::$instance)) {
      static::$instance = new static;
    }

    return static::$instance;
  }
  
  /**
   * Plugin should only be initialized on admin as everything else is static
   *
   * @return void
   */
  public function init() {
    if (!is_admin()) return;

    $fields = $this->fields();

    $this->settings = new PolyPlugins\Loginator\Backend\Settings($this->plugin, $this->plugin_slug, $this->plugin_slug_id, $this->options_name, $this->options, $fields);
    
    add_action( 'admin_init', array( $this, 'admin_init' ));
		add_action( 'admin_menu', array( $this, 'admin_menu' ));
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ));
  }
  
  /**
   * Generate fields for settings
   *
   * @return void
   */
  private function fields() {
    $fields = array(
      'general' => array(
        array(
          'name'    => __('Enabled', $this->plugin_slug),
          'type'    => 'switch',
          'default' => false,
        ),
        array(
          'name'     => __('Emails', $this->plugin_slug),
          'type'     => 'email',
          'default'  => get_bloginfo('admin_email'),
          'required' => true,
          'help'     => __('Enter emails separated by commas to receive critical error alerts. If empty emails will be sent to ' . get_bloginfo('admin_email'), $this->plugin_slug),
        ),
        array(
          'name'    => __('Pipedream URL', $this->plugin_slug),
          'type'    => 'url',
          'help'    => __('Enter a pipedream url to send your log data as a payload to. https://your-id-here.m.pipedream.net', $this->plugin_slug),
        ),
      ),
    );
    
    return $fields;
  }
  
  /**
   * Initialize Admin
   *
   * @return void
   */
  public function admin_init() {
    $this->settings->init();
  }
  
  /**
   * Enqueue scripts
   *
   * @return void
   */
  public function enqueue() {
    $this->settings->enqueue();
  }
  
  /**
   * Add menu to admin sidebar
   *
   * @return void
   */
  public function admin_menu() {
		$this->settings->admin_menu($this->options_page);
	}

  /**
   * Emergency logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function emergency($log, $args = array()) {
    $defaults = array(
      'flag' => 'em',
    );

    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }

  /**
   * Alert logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function alert($log, $args = array()) {
    $defaults = array(
      'flag' => 'a',
    );

    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }

  /**
   * Critical logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function critical($log, $args = array()) {
    $defaults = array(
      'flag' => 'c',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }
  
  /**
   * Error logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function error($log, $args = array()) {
    $defaults = array(
      'flag' => 'e',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }
  
  /**
   * Warning logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function warning($log, $args = array()) {
    $defaults = array(
      'flag' => 'w',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }
  
  /**
   * Notice logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function notice($log, $args = array()) {
    $defaults = array(
      'flag' => 'n',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }

  /**
   * Info logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function info($log, $args = array()) {
    $defaults = array(
      'flag' => 'i',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }
  
  /**
   * Debug logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function debug($log, $args = array()) {
    $defaults = array(
      'flag' => 'd',
      'pipedream' => true,
    );

    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }

  /**
   * Success logging
   *
   * @param  mixed $log  The data being logged
   * @param  array $args See log method for available args
   * @return void
   */
  public static function success($log, $args = array()) {
    $defaults = array(
      'flag' => 's',
    );
    
    $args = wp_parse_args( $args, $defaults );

    self::getInstance()->log($log, $args);
  }

  /**
   * Log data
   * @param  mixed $log  The data being logged
   * @param  array $args See $default_args for a list of arguments
   */
  private function log($log, $args) {
    $default_args = array(
			'flag'      => 'd',
			'id'        => '',
			'file'      => '',
			'pipedream' => false,
		);

		$args      = array_map( 'trim', wp_parse_args( $args, $default_args ) );

    $flag      = ($args['flag']) ? $args['flag'] : '';
    $id        = ($args['id']) ? $args['id'] : '';
    $file      = ($args['file']) ? $args['file'] : '';
    $enabled   = $this->get_option('general', 'enabled');
    $email     = $this->get_option('general', 'email');
    $pipedream = ($args['pipedream']) ? $this->get_option('general', 'pipedream-url') : '';

    // Log if enabled
    if ($enabled) {
      // Sanitize
      $file = sanitize_file_name($file);
      $flag = sanitize_text_field($flag);
      $id   = ($id) ? '-' . sanitize_text_field($id) : '';

      // Error Email
      if ($flag === 'c' || $flag === 'em') {
        $to = ($email) ? $email : get_bloginfo('admin_email');
        $subject = get_bloginfo('name') . ' ' . __('has encountered a critical error!', 'loginator');
        $body = (is_object($log) || is_array($log)) ? print_r($log, true) : $log;
        
        wp_mail($to, $subject, $body);
      }

      // Pipe Dream
      if (filter_var($pipedream, FILTER_VALIDATE_URL) !== false) {
        $headers = array(
          'Content-Type'  => 'application/json',
        );
    
        $args = array(
          'headers' => $headers,
          'body'    => (!empty($log)) ? json_encode($log) : ''
        );
    
        wp_remote_post($pipedream, $args);
      }

      // Flag Handling
      switch ($flag) {
        case "em":
          $flag = "EMERGENCY";
          break;
        case "a":
          $flag = "ALERT";
          break;
        case "c":
          $flag = "CRITICAL";
          break;
        case "e":
          $flag = "ERROR";
          break;
        case "w":
          $flag = "Warning";
          break;
        case "n":
          $flag = "Notice";
          break;
        case "i":
          $flag = "Info";
          break;
        case "d":
          $flag = "Debug";
          break;
        case "s":
          $flag = "Success";
          break;
        default:
          $flag = "Debug";
          break;
      }

      // Use flag if file empty
      if (empty($file)) {
        $file = strtolower($flag);
      }

      // Save logs
      $dir = ABSPATH . '/wp-logs';
      if (is_object($log) || is_array($log)) {
        file_put_contents($dir . '/' . $file . $id . '.log', $flag . ' ' . date('m-d-y h:i:s') . ': ' . print_r($log, true) . PHP_EOL, FILE_APPEND);
      } else {
        file_put_contents($dir . '/' . $file . $id . '.log', $flag . ' ' . date('m-d-y h:i:s') . ': ' . $log . PHP_EOL, FILE_APPEND);
      }
    }
  }

  /**
   * Get option from options array
   *
   * @param  mixed $section      Section of setting
   * @param  mixed $option       Get option of the previously specified section
   * @return mixed $option_value Returns the value of the option
   */
  public function get_option($section, $option) {
    if (!empty($this->options[$section][$option]['value'])) {
      $option_value = $this->options[$section][$option]['value'];
    } else {
      $option_value = '';
    }
    
    return $option_value;
  }
  
  /**
   * Activation
   *
   * @return void
   */
  public static function activation()
  {
    $plugin         = __FILE__;
    $plugin_slug    = dirname( plugin_basename( $plugin ) );
    $plugin_slug_id = str_replace( '-', '_', $plugin_slug );
    $options_name   = $plugin_slug_id . '_options';
    $dir_logs       = ABSPATH . '/wp-logs';
    $index          = $dir_logs . '/index.php';
    $htaccess       = $dir_logs . '/.htaccess';

    if (!get_option($options_name)) {
      add_option($options_name);
    }

    // Check if logs directory exists
    if (!file_exists($dir_logs)) {
      // Make the directory, allow writing so we can add a file
      mkdir($dir_logs, 0755, true);
      // Shhh we don't need script kiddies looking at our logs
      $contents = '<?php' . PHP_EOL . '// Silence is golden';
      file_put_contents($index, $contents);
      // Apache directory blocking
      $contents = 'Order Allow,Deny' . PHP_EOL . 'Deny from All';
      file_put_contents($htaccess, $contents);
    }
  }
}

$loginator = Loginator::getInstance();
$loginator->init();

?>