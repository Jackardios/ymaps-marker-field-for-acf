<?php

// exit if accessed directly
if (!defined('ABSPATH')) exit;


// check if class already exists
if (!class_exists('jackardios_acf_ymaps_marker_field')) :


    class jackardios_acf_ymaps_marker_field extends acf_field
    {

        /*
        *  __construct
        *
        *  This function will setup the field type data
        *
        *  @type	function
        *  @date	5/03/2014
        *  @since	5.0.0
        *
        *  @param	n/a
        *  @return	n/a
        */

        function __construct($settings)
        {

            /*
            *  name (string) Single word, no spaces. Underscores allowed
            */

            $this->name = 'ymaps_marker_field';


            /*
            *  label (string) Multiple words, can include spaces, visible when selecting a field type
            */

            $this->label = __('Yandex Maps Marker', 'acf-ymaps-marker-field');


            /*
            *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
            */

            $this->category = 'jquery';


            /*
            *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
            */

            $this->defaults = array(
                'zoom' => '13',
                'height' => '400',
                'center_lat' => '55.751244',
                'center_lng' => '37.618423',
            );


            /*
            *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
            *  var message = acf._e('ymaps_marker_field', 'error');
            */

            $this->l10n = array();


            /*
            *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
            */

            $this->settings = $settings;


            // do not delete!
            parent::__construct();
        }


        /*
        *  render_field_settings()
        *
        *  Create extra settings for your field. These are visible when editing a field
        *
        *  @type	action
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @param	$field (array) the $field being edited
        *  @return	n/a
        */

        function render_field_settings($field)
        {

            /*
            *  acf_render_field_setting
            *
            *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
            *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
            *
            *  More than one setting can be added by copy/paste the above code.
            *  Please note that you must also have a matching $defaults value for the field name (font_size)
            */

            acf_render_field_setting($field, array(
                'label' => __('Зум', 'acf-ymaps-marker-field'),
                'instructions' => __('Установите изначальный зум карты', 'acf-ymaps-marker-field'),
                'type' => 'number',
                'name' => 'zoom',
                'min' => '6',
                'max' => '18'
            ));

            acf_render_field_setting($field, array(
                'label' => __('Центральная точка (lat)', 'acf-ymaps-marker-field'),
                'instructions' => __('Установите изначальную центральную точку карты', 'acf-ymaps-marker-field'),
                'type' => 'text',
                'name' => 'center_lat',
                'prepend' => 'lat',
                'placeholder' => $this->defaults['center_lat']
            ));

            acf_render_field_setting($field, array(
                'label' => __('Центральная точка (lng)', 'acf-ymaps-marker-field'),
                'instructions' => __('Установите изначальную центральную точку карты', 'acf-ymaps-marker-field'),
                'type' => 'text',
                'name' => 'center_lng',
                'prepend' => 'lng',
                'placeholder' => $this->defaults['center_lng'],
                'wrapper' => array(
                    'data-append' => 'center_lat'
                )
            ));

            acf_render_field_setting($field, array(
                'label' => __('Высота карты', 'acf-ymaps-marker-field'),
                'instructions' => __('Установите высоту карты', 'acf-ymaps-marker-field'),
                'type' => 'text',
                'name' => 'height',
                'append' => 'px',
                'placeholder' => $this->defaults['height']
            ));
        }

        /*
        *  input_admin_enqueue_scripts()
        *
        *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
        *  Use this action to add CSS + JavaScript to assist your render_field() action.
        *
        *  @type	action (admin_enqueue_scripts)
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @param	n/a
        *  @return	n/a
        */

        function input_admin_enqueue_scripts()
        {
            // vars
            $url = $this->settings['url'];
            $version = $this->settings['version'];

            $apiArgs = array(
                'apikey' => acf_get_setting('ymaps_api_key'),
                'lang' => acf_get_locale()
            );
            $apiArgs = apply_filters('acf-field-ymaps-marker/ymaps-api-args', $apiArgs);

            // register & include JS
            wp_register_script('afc-field-ymaps-marker/ymaps-api', add_query_arg($apiArgs, 'https://api-maps.yandex.ru/2.1'), array('jquery'), null);
            wp_enqueue_script('afc-field-ymaps-marker/ymaps-api');

            wp_register_script('afc-field-ymaps-marker/input-js', "{$url}assets/js/input.js", array('acf-input', 'afc-field-ymaps-marker/ymaps-api'), $version);
            wp_enqueue_script('afc-field-ymaps-marker/input-js');

            // register & include CSS
            wp_register_style('afc-field-ymaps-marker/input-css', "{$url}assets/css/input.css", array('acf-input'), $version);
            wp_enqueue_style('afc-field-ymaps-marker/input-css');
        }

        /*
        *  render_field()
        *
        *  Create the HTML interface for your field
        *
        *  @param	$field (array) the $field being rendered
        *
        *  @type	action
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @param	$field (array) the $field being edited
        *  @return	n/a
        */

        function render_field($field)
        {
            // Attributes
            $attrs = array(
                'id' => $field['id'],
                'class' => "acf-ymaps-marker-field {$field['class']}",
                'data-lat' => $field['center_lat'],
                'data-lng' => $field['center_lng'],
                'data-zoom' => $field['zoom'],
            );

            if ($field['value']) {
                $attrs['class'] .= ' -value';
            } else {
                $field['value'] = '';
            }

?>
            <div <?php acf_esc_attr_e($attrs); ?>>
                <?php acf_hidden_input(array('name' => $field['name'], 'value' => $field['value'])); ?>
                <div class="ymaps-container" style="<?php echo esc_attr('height: ' . $field['height'] . 'px;'); ?>"></div>
            </div>
<?php
        }

        /**
         * load_value
         *
         * Filters the value loaded from the database.
         *
         * @date	16/10/19
         * @since	5.8.1
         *
         * @param	mixed $value The value loaded from the database.
         * @param	mixed $post_id The post ID where the value is saved.
         * @param	array $field The field settings array.
         * @return	(array|false)
         */
        function load_value($value, $post_id, $field)
        {
            // Ensure value is an array.
            if ($value) {
                return wp_parse_args($value, array(
                    'lat'        => 0,
                    'lng'        => 0
                ));
            }

            // Return default.
            return false;
        }


        /*
        *  update_value()
        *
        *  This filter is appied to the $value before it is updated in the db
        *
        *  @type	filter
        *  @since	3.6
        *  @date	23/01/13
        *
        *  @param	$value - the value which will be saved in the database
        *  @param	$post_id - the $post_id of which the value will be saved
        *  @param	$field - the field array holding all the field options
        *
        *  @return	$value - the modified value
        */
        function update_value($value, $post_id, $field)
        {
            // decode JSON string.
            if (is_string($value)) {
                $value = json_decode(wp_unslash($value), true);
            }

            // Ensure value is an array.
            if ($value) {
                return (array) $value;
            }

            // Return default.
            return false;
        }
    }

    // initialize
    new jackardios_acf_ymaps_marker_field($this->settings);


// class_exists check
endif;

?>