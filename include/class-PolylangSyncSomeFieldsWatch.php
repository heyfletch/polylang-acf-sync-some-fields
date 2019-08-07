<?php
if ( !class_exists('PolylangSyncSomeFieldsWatch') ) {

    class PolylangSyncSomeFieldsWatch {
        /**
         *    Holding the singleton instance
         */
        private static $_instance = null;
        /**
         * @return PolylangPostClonerWatchMeta
         */
        public static function instance() {

            if ( is_null( self::$_instance ) ) {

                self::$_instance = new self();

            }

            return self::$_instance;
        }
        /**
         *    Prevent from creating more instances
         */
        private function __clone() { }
        
        private function __construct() {

            add_filter( 'pll_copy_post_metas', array( &$this, 'filter_keys' ), 20, 3);
            add_action( 'acf/render_field_settings', array( &$this, 'action_acf_create_field_options' ), 10, 1);
            add_action( 'acf/render_field', array( &$this, 'action_acf_create_field' ), 10, 1);
            add_action( 'init', array( &$this, 'register_strings' ) );

        }

        public function action_acf_create_field( $field ) {

            if ( get_post_type() != 'post' ) {

                if ( isset($field['lang_sync']) ? $field['lang_sync'] : false ) {

                     echo '<small style="color:MediumSeaGreen;">Syncs Across Languages</small>';

                }

            
            }

        }
        /**
         * Register Field Strings so they can be translated
         *
         * @param $field
         * @param $post_id
         */
        public function register_strings() {

            if ( !PLL_ADMIN ) {

                return;

            }

            $return = acf_get_field_groups();

            foreach( $return as $group ) {
                
                pll_register_string( 'group_' . $group['ID'] . '_title', $group['title'] );

                $fields = acf_get_fields( $group['ID'] );
                
                if( $fields ) {

                    foreach( $fields as $field ) {

                        pll_register_string( $field['key'] . '_label', $field['label'] );

                        if ( isset( $field['choices'] ) ) {

                           foreach( $field['choices'] as $key => $choiceValue ) {

                                pll_register_string( $field['key'] . '_label_choice_' . $key, $choiceValue );

                            }

                        }
                    }
                }
            }
        }
        /**
         * Handle language sync option
         *
         * @action 'pll_copy_post_metas'
         */
        public function filter_keys( $keys ) {

            foreach( $keys as $index => $key ) {

                $field = get_field_object( $key );
                
                if ( $field ) { 
                    
                    $sync = isset( $field['lang_sync'] ) ? $field['lang_sync'] : false;

                    if ( !$sync ) {

                        unset( $keys[ $index ] );

                    }

                }

            }

            return $keys;
        }
        /**
         * Add option to ACF fields about sync
         *
         * @param $field
         */
        public function action_acf_create_field_options( $field ) {
            
            acf_render_field_setting( $field, array(
                'label'         => __('Synchronize?'),
                'instructions'  => '',
                'type'          => 'true_false',
                'ui'            => true,
                'name'          => 'lang_sync',
                'value'         => isset( $field['lang_sync'] ) ? $field['lang_sync'] : false,
                'required'      => true,
            ), true);
        
        }
    }

}
