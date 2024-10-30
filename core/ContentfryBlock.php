<?php

class ContentfryBlock {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'ctf_scripts' ) );
        add_action( 'init', array( $this, 'ctf_languages' ) );
        add_action( 'init', array( $this, 'ctf_register_block' ) );
    }

    public function ctf_scripts() {
        wp_enqueue_script( 'contentfry_embed_gutenberg', plugins_url( '../contentfry-embed-gutenberg.js', __FILE__ ), array(), '0.0.1', true );
    }

    public function ctf_languages() {
        load_plugin_textdomain( 'contentfry', false, basename( __DIR__ ) . '/languages' );
    }

    /**
     * Registers all block assets so that they can be enqueued through Gutenberg in
     * the corresponding context.
     *
     * Passes translations to JavaScript.
     */
    public function ctf_register_block() {

        if ( ! function_exists( 'register_block_type' ) ) {
            // Gutenberg is not active.
            return;
        }

        wp_register_script(
            'gutenberg-contentfry',
            plugins_url( '../build/index.js', __FILE__ ),
            array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../build/index.js' )
        );

        $api_url        = site_url('wp-json/cf-api/v1/settings');
        wp_localize_script('gutenberg-contentfry', 'CF', array( 'apiUrl' => $api_url  ));

        wp_register_style(
            'gutenberg-contentfry-editor',
            plugins_url( '../assets/css/editor.css', __FILE__ ),
            array( 'wp-edit-blocks' ),
            filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/editor.css' )
        );

        wp_register_style(
            'gutenberg-contentfry-style',
            plugins_url( '../assets/css/style.css', __FILE__ ),
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . '../assets/css/style.css' )
        );


        register_block_type( 'contentfry/gutenberg-block', array(
            'style'         => 'gutenberg-contentfry-style',
            'editor_style'  => 'gutenberg-contentfry-editor',
            'editor_script' => 'gutenberg-contentfry',
        ) );

        if ( function_exists( 'wp_set_script_translations' ) ) {
            /**
             * May be extended to wp_set_script_translations( 'my-handle', 'my-domain',
             * plugin_dir_path( MY_PLUGIN ) . 'languages' ) ). For details see
             * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
             */
            wp_set_script_translations( 'gutenberg-contentfry-esnext', 'contentfry' );
        }

    }
}
