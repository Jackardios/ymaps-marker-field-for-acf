<?php

/*
Plugin Name: Add Yandex Maps Marker Field for ACF
Plugin URI: https://github.com/Jackardios/ymaps-marker-field-for-acf
Description: Adds a new 'Yandex Maps Marker' field to Advanced Custom Fields plugin.
Version: 1.0.0
Author: Salakhutdinov Salavat
Author URI: https://github.com/Jackardios
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if (!defined('ABSPATH')) exit;


// check if class already exists
if (!class_exists('jackardios_acf_plugin_ymaps_marker_field')) :

    class jackardios_acf_plugin_ymaps_marker_field
    {
        // vars
        var $settings;

        /*
        *  __construct
        *
        *  This function will setup the class functionality
        *
        *  @type	function
        *  @date	17/02/2016
        *  @since	1.0.0
        *
        *  @param	void
        *  @return	void
        */

        function __construct()
        {

            // settings
            // - these will be passed into the field class.
            $this->settings = array(
                'version' => '1.0.0',
                'url' => plugin_dir_url(__FILE__),
                'path' => plugin_dir_path(__FILE__)
            );


            // include field
            add_action('acf/include_field_types',     array($this, 'include_field')); // v5
        }


        /*
        *  include_field
        *
        *  This function will include the field type class
        *
        *  @type	function
        *  @date	17/02/2016
        *  @since	1.0.0
        *
        *  @param	$version (int) major ACF version. Defaults to false
        *  @return	void
        */

        function include_field($version = false)
        {
            // support empty $version
            if (!$version) $version = 5;

            // load textdomain
            load_plugin_textdomain('acf-ymaps-marker-field', false, plugin_basename(dirname(__FILE__)) . '/lang');

            // include
            if ($version >= 5) {
                include_once('fields/class-jackardios-acf-ymaps-marker-field-v' . $version . '.php');
            } else {
                add_action('admin_notices', array($this, 'show_unsupported_version_notice'));
            }
        }
    }


    // initialize
    new jackardios_acf_plugin_ymaps_marker_field();


// class_exists check
endif;
