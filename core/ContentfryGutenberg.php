<?php

class ContentfryGutenberg {
    protected $slug = 'contentfry-api-settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'ctf_api_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'ctf_api_settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );

        add_action( 'rest_api_init', function () {
            register_rest_route( "cf-api/v1", '/settings', array(
                    array(
                        'methods'  => \WP_REST_Server::READABLE,
                        'callback' => array( $this, 'get_tokens' )
                    ),
                )
            );
        } );
    }

    public function get_tokens() {
        $settings = get_option( 'ctf_api_settings' );

        return isset( $settings['tokens'] ) ? array_values( $settings['tokens'] ) : [];
    }

    public function load_custom_wp_admin_style( $hook ) {
        if ( $hook != 'settings_page_' . $this->slug ) {
            return;
        }
        wp_enqueue_style( $this->slug, plugins_url( '../assets/admin/style.css', __FILE__ ) );

        wp_enqueue_script( $this->slug, plugins_url( '../assets/admin/scripts.js', __FILE__ ), array(), '1.0' );

        $settings         = get_option( 'ctf_api_settings' );
        $tokens           = isset( $settings['tokens'] ) ? array_values( $settings['tokens'] ) : [];
        $tokens_count     = count( $tokens );
        $admin_url        = admin_url( "options-general.php?page={$this->slug}&origin=wordpress" );
        $click_to_connect = '<a class="button button-primary" href="https://app.contentfry.com/token-request?redirect_uri=' . $admin_url . '">Get a New Token</a>';

        wp_localize_script( $this->slug, 'CF', array(
            'adminUrl'       => $admin_url,
            'clickToConnect' => $click_to_connect,
            'strings'        => array(
                'saved' => __( 'Settings Saved', 'contentfry' ),
                'error' => __( 'Error', 'contentfry' )
            ),
            'api'            => array(
                'url'   => esc_url_raw( rest_url( 'cf-api/v1/settings' ) ),
                'nonce' => wp_create_nonce( 'wp_rest' )
            ),
            'tokens'         => $tokens,
            'tokensCount'    => $tokens_count
        ) );
    }

    public function ctf_api_add_admin_menu() {
        add_options_page(
            'Contentfry Settings API',
            'Contentfry',
            'manage_options',
            $this->slug,
            array( $this, 'ctf_api_options_page' )
        );
    }

    public function ctf_api_settings_init() {
        register_setting( 'ctfPlugin', 'ctf_api_settings' );

        add_settings_section(
            'ctf_api_ctfPlugin_section',
            __( '', 'contentfry' ),
            array( $this, 'ctf_api_settings_section_callback' ),
            'ctfPlugin'
        );

        add_settings_field(
            'token',
            __( 'New Token', 'contentfry' ),
            array( $this, 'text_field_render' ),
            'ctfPlugin',
            'ctf_api_ctfPlugin_section'
        );

        add_settings_field(
            'tokens',
            __( 'Your Access Tokens', 'contentfry' ),
            array( $this, 'text_field_array_render' ),
            'ctfPlugin',
            'ctf_api_ctfPlugin_section'
        );

//        add_settings_field(
//            'environment',
//            __( 'Environment', 'contentfry' ),
//            array( $this, 'select_field_render' ),
//            'ctfPlugin',
//            'ctf_api_ctfPlugin_section'
//        );
    }

    public function text_field_render() {
//        $options = get_option( 'ctf_api_settings' );
//        $name    = 'ctf_api_settings[token]';
        // $value   = isset( $options['token'] ) ? strip_tags( $options['token'] ) : '';
        $value = isset( $_GET['access_token'] ) ? strip_tags( $_GET['access_token'] ) : '';

        $redirect_uri     = admin_url( "options-general.php?page={$this->slug}&origin=wordpress" );
        $click_to_connect = '<a class="button button-primary" href="https://app.contentfry.com/token-request?redirect_uri=' . $redirect_uri . '">Get a Token</a>';
//        $save_btn         = '<button type="button" class="button button-primary" id="cf-create-token">Save</button>';
        ?>
        <div>
            <div class="cf-flex">
                <input type="hidden" class="widefat" name="" value="<?php echo $value; ?>" id="cf-access-token"/>
                <?php echo $value === '' ? $click_to_connect : ''; ?>
            </div>
            <div id="cf-create-tokens"></div>
        </div>
        <?php
    }

    public function text_field_array_render() {
        $options = get_option( 'ctf_api_settings' );
//        $name    = 'ctf_api_settings[tokens][]';
        $values = isset( $options['tokens'] ) ? array_values( $options['tokens'] ) : [];
        ?>
        <div id="cf-tokens">
            <?php foreach ( $values as $key => $value ): ?>
                <div class="cf-token">
                    <h3><?php echo $value['team_name']; ?>
                        <small><?php echo $value['name']; ?></small>
                    </h3>
                    <input type="text" class="hidden" name="ctf_api_settings[tokens][<?php echo $key; ?>][team_name]"
                           value="<?php echo $value['team_name']; ?>">
                    <input type="text" class="hidden" name="ctf_api_settings[tokens][<?php echo $key; ?>][name]"
                           value="<?php echo $value['name']; ?>">
                    <input type="text" class="widefat" name="ctf_api_settings[tokens][<?php echo $key; ?>][token]"
                           value="<?php echo $value['token']; ?>" readonly>
                    <button class="button" data-action="delete">Delete</button>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function select_field_render() {
        $options = get_option( 'ctf_api_settings' );
        $name    = 'ctf_api_settings[environment]';
        $value   = isset( $options['environment'] ) ? $options['environment'] : 'production';
        ?>
        <select name="<?php echo $name; ?>">
            <option value="production" <?php selected( $value, 1 ); ?>>Production</option>
            <option value="development" <?php selected( $value, 2 ); ?>>Development</option>
        </select>

        <?php
    }

    public function ctf_api_settings_section_callback() {
        $redirect_uri = admin_url( "options-general.php?page={$this->slug}&origin=wordpress" );

        echo __( 'The Access Token connects your contentfry account with WordPress. Click the «Get a Token» button below to generate a new Access Token.', 'contentfry' );
    }

    public function ctf_api_options_page() {
        ?>
        <div class="wrap">
            <form action='options.php' method='post' class="cf-admin-settings" id="cf-settings-form">
                <h2><?php echo get_admin_page_title(); ?></h2>
                <?php
                settings_fields( 'ctfPlugin' );
                do_settings_sections( 'ctfPlugin' );
                submit_button( 'Save Changes', 'hidden', 'submit', false, array(
                    'id' => 'cf-settings-form-submit'
                ) );
                ?>
            </form>
        </div>
        <?php
    }
}
