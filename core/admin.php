<?php
namespace WugsTracker\Core;

use WugsTracker\Plugin;
use WugsTracker\Utils;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;
    /**
	 * Instance of Plugin class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	public $plugin = null;


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;
			self::$instance->do_hooks();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {}

	/**
	 * Handle WP actions and filters.
	 *
	 * @since 	1.0.0
	 */
	private function do_hooks() {
        add_action( 'admin_menu', [self::$instance, 'add_pages'] );
        add_action('admin_enqueue_scripts', [self::$instance, 'css_and_js']);
        add_action('script_loader_tag', [self::$instance, 'replace_to_type_module']);
        add_filter( 'after_setup_theme', [self::$instance, 'remove_admin_bar']);
	}
    
    public function remove_admin_bar() {
        global $pagenow;
        if($pagenow === 'admin.php' && strpos(isset($_GET['page']) ? $_GET['page'] : '', 'wugstracker-admin-page') !== false ) {
            show_admin_bar(false);
        }
        if($pagenow === 'admin.php' && strpos(isset($_GET['page']) ? $_GET['page'] : '', 'wugstracker-admin-configuration') !== false ) {
            show_admin_bar(false);
        }
    }

	public function add_pages() {
        add_menu_page(
            __('WugsTracker', 'wugstracker'),
            __('WugsTracker', 'wugstracker'),
            'manage_options',
            'wugstracker-admin-tracker',
            [self::$instance, 'render'],            
            //Icons made by https://www.flaticon.com/authors/flat-icons
            'data:image/svg+xml;base64,' . base64_encode('
            <svg height="35" viewBox="0 0 60 60" width="35" xmlns="http://www.w3.org/2000/svg">
                <g id="wugstracker-icon-gruop" fill="rgb(219,38,26)" fill-rule="evenodd">
                    <g id="wugstracker-icon" fill="rgb(219,38,26)" fill-rule="nonzero">
                        <path id="Shape" fill="rgb(219,38,26)" d="m5 60h14c2.7600532-.0033061 4.9966939-2.2399468 5-5v-24.789c.0006057-.9870955-.2917353-1.9521688-.84-2.773l-2.734-4.1c-.5558861-.8353512-1.4925954-1.3374815-2.496-1.338h-2.93v-2c1.1045695 0 2-.8954305 2-2v-8c0-1.1045695-.8954305-2-2-2h-6c-1.1045695 0-2 .8954305-2 2v8c0 1.1045695.8954305 2 2 2v2h-2.93c-1.00444212-.0012658-1.94286979.50023-2.5 1.336l-2.73 4.102c-.54826467.8208312-.84060567 1.7859045-.84 2.773v24.789c.00330612 2.7600532 2.23994685 4.9966939 5 5zm7.833-14.247c-1.3141906-1.9794715-1.0510879-4.610908.6290021-6.2909979 1.6800899-1.68009 4.3115264-1.9431927 6.2909979-.6290021zm8.334-5.506c1.3141906 1.9794715 1.0510879 4.610908-.6290021 6.2909979-1.6800899 1.68009-4.3115264 1.9431927-6.2909979.6290021zm-4.167-4.247c-3.1797362-.0039869-5.9626985 2.1356799-6.7759552 5.2096598-.81325664 3.0739798.5475265 6.309922 3.3132341 7.8788706 2.7657077 1.5689486 6.241444 1.0766985 8.4627211-1.1985304v4.11h-13c-.55228475 0-1-.4477153-1-1v-16c0-.5522847.44771525-1 1-1h13v4.11c-1.3136228-1.3492728-3.1168798-2.1102472-5-2.11zm-8-26h6v2h-1c-.5522847 0-1 .4477153-1 1s.4477153 1 1 1h1v4h-6zm2 10h2v2h-2zm-9 10.211c-.00177984-.5919401.17224058-1.1710801.5-1.664l2.734-4.1c.18584903-.2800136.49992473-.4479464.836-.447h11.86c.3342785-.00005.6464754.1669303.832.445l2.734 4.1c.3277594.4929199.5017798 1.0720599.5 1.664v1.791h-12.996c-1.65685425 0-3 1.3431458-3 3v16c0 1.6568542 1.34314575 3 3 3h13v1c0 1.6568542-1.3431458 3-3 3h-14c-1.65685425 0-3-1.3431458-3-3z"/><path id="Shape" d="m53.52 4.31c-.5014129-.00036944-1.0012581.05598895-1.49.168-.7848733-2.67148108-3.2456869-4.49893106-6.03-4.478-3.657 0-6.865 2.868-6.988 6.185-.9603705-.3634158-2.0413195-.19187885-2.842.451-.5888461-.58876482-1.2937972-1.04851553-2.07-1.35-.5127964-.20544991-1.09505.04370363-1.3005.55650001-.2054499.51279638.0437036 1.09505005.5565 1.30049999.6774801.24768628 1.2700764.6838066 1.708 1.257-.0855348.41125807-.0824667.83602043.009 1.246-2.903.897-7.285 1.354-13.073 1.354-.5522847 0-1 .4477153-1 1s.4477153 1 1 1c6.325 0 11.079-.55 14.191-1.622.6665528.5141948 1.5196465.7221432 2.3480502.5723559s1.5546623-.6433052 1.9989498-1.3583559c.710959-.1130628 1.4394164-.0211384 2.1.265.3317211.1334387.7094327.07977.9908546-.1407895.2814218-.2205596.4237992-.5745018.3735-.92850001-.0502993-.35399825-.2856335-.65427179-.6173546-.78771049-.7699252-.31733814-1.5974791-.47092942-2.43-.451-.0351661-.2512098-.1023802-.49687739-.2-.731 0-.008-.009-.015-.013-.024.1706728-.45188873.2580837-.93095485.258-1.414 0-2.293 2.383-4.38 5-4.38 2.2167906-.00364608 4.0730566 1.67855441 4.287 3.885.0362993.31323413.2182149.59099777.4908402.74945507s.6040195.1790434.8941598.05554493c.5838833-.25059186 1.2126135-.3798762 1.848-.38 1.9106089-.05197839 3.6329207 1.14482408 4.2505206 2.95360778.6175999 1.80878372-.0131025 3.80901082-1.5565206 4.93639222-.2541682.1886412-.404021.4864767-.404021.803s.1498528.6143588.404021.803c1.5378616 1.1290222 2.1645791 3.1258156 1.5476944 4.9311307-.6168847 1.8053152-2.3345004 3.0010389-4.2416944 2.9528693-.6353865-.0001238-1.2641167-.1294081-1.848-.38-.2893878-.1233875-.619969-.1034019-.8923845.05395-.2724154.1573519-.4549034.4337239-.4926155.74605-.211532 2.208426-2.068469 3.8934004-4.287 3.89-2.617 0-5-2.087-5-4.38 0-4.454-6.215-6.62-19-6.62-.5522847 0-1 .4477153-1 1s.4477153 1 1 1c10.8 0 17 1.684 17 4.62 0 3.4 3.271 6.38 7 6.38 2.7843131.0209311 5.2451267-1.8065189 6.03-4.478.4887419.1120111.9885871.1683694 1.49.168 2.5518343.0482945 4.8854402-1.4333462 5.9272792-3.7633167 1.041839-2.3299704.5901482-5.0570478-1.1472792-6.9266833 1.7374274-1.8696355 2.1891182-4.5967129 1.1472792-6.92668332-1.041839-2.32997046-3.3754449-3.81161113-5.9272792-3.76331668zm-15.126 5.609c-.4383584.1883148-.9486968.04073596-1.21889-.35247674-.2701931-.3932127-.2249815-.92253361.1079834-1.26422789.3329648-.34169429.8609388-.40058628 1.2610107-.14065739.400072.25992888.5608057.76627794.3838959 1.20936202-.097171.24697566-.2896212.44447134-.534.548z"/><path id="Shape" d="m43.054 19.536c.2558134 1.4264298 1.4968131 2.4646696 2.946 2.4646696s2.6901866-1.0382398 2.946-2.4646696c.6193697-.3712983 1.3321779-.5573086 2.054-.536.5522847 0 1-.4477153 1-1s-.4477153-1-1-1c-.833242-.0084804-1.6591152.1566943-2.425.485-.531804-.919078-1.5131526-1.4850336-2.575-1.4850336s-2.043196.5659556-2.575 1.4850336c-.7658848-.3283057-1.591758-.4934804-2.425-.485-.5522847 0-1 .4477153-1 1s.4477153 1 1 1c.7217776-.0209162 1.4344774.1650658 2.054.536zm2.946-1.536c.5522847 0 1 .4477153 1 1s-.4477153 1-1 1-1-.4477153-1-1 .4477153-1 1-1z"/>
                    </g>
                </g>
            </svg>
            '),
            30
        );
        add_submenu_page(
            'wugstracker-admin-tracker',
            __('Configuration', 'wugstracker'),
            __('Configuration', 'wugstracker'),
            'manage_options',
            'wugstracker-admin-configuration',
            [self::$instance, 'render'],
            10
        );
	}

    public function replace_to_type_module($script) {
        global $pagenow;

        if( $pagenow === 'admin.php' ) {
            
            $currentPage = isset($_GET['page']) ? $_GET['page'] : '';

            if( in_array($currentPage, ['wugstracker-admin-tracker', 'wugstracker-admin-configuration']) ) {
                if (strpos($script, 'wugstracker-admin-app-') !== false) {
                    return str_replace( 'src=', 'type="module" src=', $script );
                }       

                if ($script === 'id="wugstracker-admin-app"') {
                    return str_replace( 'src=', 'type="text/babel" src=', $script );
                }
            }
        }

        return $script;
    }
 
    public function css_and_js($hook) {
        global $pagenow;
            
        $currentPage = isset($_GET['page']) ? $_GET['page'] : '';
        
        if( $pagenow === 'admin.php' ) {

            if( in_array($currentPage, ['wugstracker-admin-tracker', 'wugstracker-admin-configuration']) ) {
                wp_deregister_style('wp-admin');
                wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css', array() );
                wp_enqueue_style( 'bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css', array() );
                wp_enqueue_style( 'wugstracker-admin', WUGSTRACKER_URL . '/admin/css/admin.css', array(), time() , 'all' );

                wp_enqueue_script( 'luxon', 'https://cdn.jsdelivr.net/npm/luxon@1.26.0/build/global/luxon.min.js', array() );
                wp_enqueue_script( 'lodash', 'https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js', array() );
                wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js', array() );
                wp_enqueue_script( 'htm', 'https://unpkg.com/htm@latest/dist/htm.js', array() );
                wp_enqueue_script( 'preact', 'https://unpkg.com/preact@latest/dist/preact.umd.js', array() );
                wp_enqueue_script( 'hooks', 'https://unpkg.com/preact@latest/hooks/dist/hooks.umd.js', array() );
                wp_enqueue_script( 'compat', 'https://unpkg.com/preact@latest/compat/dist/compat.umd.js', array() );
                wp_enqueue_script( 'prop-types', 'https://unpkg.com/prop-types@latest/prop-types.min.js', array() );
                wp_enqueue_script( 'babel', 'https://unpkg.com/babel-standalone@6.26.0/babel.min.js', array() );

                wp_enqueue_script( 'wugstracker-admin-api', WUGSTRACKER_URL . '/admin/js/api.js', array(), time() , 'all' );
                $data = array(
                    'current' => str_replace('wugstracker-admin-', '', $currentPage),
                    'home_url' => home_url(),
                    'api_url' => home_url() . '/wugs/',
                    'wp_admin_menu' => $GLOBALS['menu'],
                    'options' => self::get_options()
                );
                wp_localize_script( 'wugstracker-admin-api', 'wugs_data', $data );
                wp_enqueue_script( 'wugstracker-admin-api' );

                wp_enqueue_script( 'wugstracker-admin-api', WUGSTRACKER_URL . '/admin/js/api.js', array(), time() , 'all' );
            }        
        
            if( $currentPage === 'wugstracker-admin-tracker' || $currentPage === 'wugstracker-admin-configuration' ) {
                wp_enqueue_script( 'wugstracker-admin-app', WUGSTRACKER_URL . '/admin/app/app.js', array(), time() , 'all' );
            }
        }       
    }

    private static function get_options() {
        $options = [
            'wugstracker_JS_active' => get_option('wugstracker_JS_active', false) === '1' ? true : false,
            'wugstracker_JS_test_active' => get_option('wugstracker_JS_test_active', false) === '1' ? true : false,
            'wugstracker_WP_debug' => get_option('wugstracker_WP_debug', false) === '1' ? true : false,
            'wugstracker_WP_log' => get_option('wugstracker_WP_log', false) === '1' ? true : false,
            'wugstracker_WP_display' => get_option('wugstracker_WP_display', false) === '1' ? true : false,
            'wugstracker_PHP_active' => get_option('wugstracker_PHP_active', false) === '1' ? true : false,
        ];

        return $options;
    }

    public static function get_option($name) {
        $options = self::get_options();

        if(isset($options[$name])) {
            return $options[$name];
        }

        return null;
    }

    /**
     * Render for Preact dom rendering
     *
     * @return void
     */
    public function render() {
        
        echo '<div id="root">
            <div class="d-flex justify-content-center" style="margin: 150px 0;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>
        </div>';
        return;
    }

}
