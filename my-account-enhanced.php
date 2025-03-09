<?php
/**
 * Plugin Name: Mi Cuenta Mejorado
 * Plugin URI: https://tudominio.com/plugins/mi-cuenta-mejorado
 * Description: Extiende y personaliza la sección "Mi Cuenta" de WooCommerce con una interfaz moderna, mejores prácticas UX, y campos personalizados específicos para empresas.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://tudominio.com
 * Text Domain: my-account-enhanced
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * WC requires at least: 5.0
 * WC tested up to: 7.0
 *
 * @package Mi_Cuenta_Mejorado
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}

// Definir constantes del plugin
define('MAM_VERSION', '1.0.0');
define('MAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MAM_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Declarar compatibilidad con características de WooCommerce
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declarar compatibilidad con HPOS
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
});

/**
 * Verificar si WooCommerce está activo
 */
function mam_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'mam_woocommerce_missing_notice');
        return false;
    }
    return true;
}

/**
 * Mostrar aviso si WooCommerce no está activo
 */
function mam_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Mi Cuenta Mejorado requiere que WooCommerce esté instalado y activado.', 'my-account-enhanced'); ?></p>
    </div>
    <?php
}

/**
 * Verificar si un archivo existe y cargarlo
 */
function mam_require_if_exists($file) {
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}

/**
 * Inicializar el plugin
 */
function mam_init() {
    // Verificar dependencias
    if (!mam_check_woocommerce_active()) {
        return;
    }

    // Cargar archivos de clases principales
    mam_require_if_exists(MAM_PLUGIN_DIR . 'includes/class-fields.php');
    mam_require_if_exists(MAM_PLUGIN_DIR . 'includes/class-registration.php');
    mam_require_if_exists(MAM_PLUGIN_DIR . 'includes/class-dashboard.php');
    mam_require_if_exists(MAM_PLUGIN_DIR . 'includes/class-company.php');
    
    // Cargar archivos de administración
    if (is_admin()) {
        mam_require_if_exists(MAM_PLUGIN_DIR . 'admin/class-admin.php');
        mam_require_if_exists(MAM_PLUGIN_DIR . 'admin/class-field-manager.php');
        mam_require_if_exists(MAM_PLUGIN_DIR . 'admin/class-settings.php');
    }
    
    // Inicializar clases del plugin si existen
    if (class_exists('MAM_Fields')) {
        $fields = new MAM_Fields();
        
        if (class_exists('MAM_Registration')) {
            $registration = new MAM_Registration($fields);
        }
        
        if (class_exists('MAM_Dashboard')) {
            $dashboard = new MAM_Dashboard();
        }
               
        if (class_exists('MAM_Company')) {
            $company = new MAM_Company();
        }
        
        // Inicializar admin si estamos en el panel de administración
        if (is_admin() && class_exists('MAM_Field_Manager') && class_exists('MAM_Admin')) {
            $field_manager = new MAM_Field_Manager();
            $admin = new MAM_Admin($field_manager);
        }
    }
    
    // Registrar scripts y estilos - este hook se ejecuta después de que WooCommerce está cargado
    add_action('wp_enqueue_scripts', 'mam_enqueue_frontend_assets');
    
    if (is_admin()) {
        add_action('admin_enqueue_scripts', 'mam_enqueue_admin_assets');
    }
    
    // Añadir template override para la página Mi Cuenta
    add_filter('woocommerce_locate_template', 'mam_override_myaccount_template', 10, 2);
}
add_action('plugins_loaded', 'mam_init');
// Reemplaza esta función en my-account-enhanced.php
function mam_override_dashboard_template($located, $template_name, $args, $template_path, $default_path) {
    if ($template_name === 'myaccount/dashboard.php') {
        $custom_template = MAM_PLUGIN_DIR . 'templates/dashboard/dashboard.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $located;
}
add_filter('wc_get_template', 'mam_override_dashboard_template', 10, 5);
/**
 * Override del template de Mi Cuenta
 */
function mam_override_myaccount_template($template, $template_name) {
    if ($template_name === 'myaccount/form-login.php') {
        $custom_template = MAM_PLUGIN_DIR . 'templates/my-account-template.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}

/**
 * Cargar scripts y estilos del frontend
 */
function mam_enqueue_frontend_assets() {
    if (!function_exists('is_account_page') || !is_account_page()) {
        return;
    }
    
    // Primero deregistrar cualquier estilo que pueda interferir
    wp_deregister_style('woocommerce-general');
    wp_deregister_style('woocommerce-layout');
    
    // Luego registrar y encolar nuestros estilos en el orden correcto
    wp_enqueue_style(
        'mam-base-styles',
        MAM_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        MAM_VERSION . '.' . time() // Añadir timestamp para forzar recarga
    );
    
    wp_enqueue_style(
        'mam-dashboard-styles',
        MAM_PLUGIN_URL . 'assets/css/dashboard.css',
        array('mam-base-styles'),
        MAM_VERSION . '.' . time()
    );
    
    wp_enqueue_style(
        'mam-account-forms',
        MAM_PLUGIN_URL . 'assets/css/account-forms.css',
        array('mam-base-styles'),
        MAM_VERSION . '.' . time()
    );
        
        // Añadir estilos críticos directamente
        add_action('wp_head', function() {
            echo '<style>
                .woocommerce-MyAccount-navigation { display: none !important; }
                .woocommerce-MyAccount-content { width: 100% !important; float: none !important; padding: 0 !important; margin: 0 !important; }
                .woocommerce-account .woocommerce { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            </style>';
        }, 999);
        
        // Cargar script frontend 
        wp_enqueue_script(
            'mam-frontend-scripts',
            MAM_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            MAM_VERSION,
            true
        );
        
        // Localizar script - ESTO DEBE ESTAR DENTRO DE LA CONDICIÓN is_account_page()
    wp_localize_script('mam-frontend-scripts', 'MAM_Data', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'security' => wp_create_nonce('mam-frontend-nonce'),
    'i18n' => array(
                'menu' => __('Menú', 'my-account-enhanced'),
'loading' => __('Cargando...', 'my-account-enhanced'),
        'error' => __('Ha ocurrido un error, por favor intenta de nuevo.', 'my-account-enhanced'),
                'view' => __('Ver', 'my-account-enhanced'),
                'orderNumber' => __('Pedido', 'my-account-enhanced'),
                'date' => __('Fecha', 'my-account-enhanced'),
                'status' => __('Estado', 'my-account-enhanced'),
                'total' => __('Total', 'my-account-enhanced'),
                'actions' => __('Acciones', 'my-account-enhanced')
            ),
            'ordersUrl' => function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url('orders') : ''
        ));
    }
}
/**
 * Cargar script de validación
 */
function enqueue_validation_scripts() {
    if (is_account_page()) {
        wp_enqueue_script(
            'mam-validation', 
            MAM_PLUGIN_URL . 'assets/js/validation.js', 
            ['jquery'], 
            MAM_VERSION, 
            true
        );
        
        // Pasar mensajes traducibles y datos de configuración al script
        wp_localize_script('mam-validation', 'mam_validation_data', [
            'messages' => [
                'field_required' => __('Este campo es obligatorio.', 'my-account-enhanced'),
                'invalid_email' => __('Por favor, ingresa una dirección de correo electrónico válida.', 'my-account-enhanced'),
                'invalid_cuit' => __('El CUIT ingresado no es válido.', 'my-account-enhanced'),
                'invalid_cuit_length' => __('El CUIT debe tener 11 dígitos.', 'my-account-enhanced'),
                'password_short' => __('La contraseña debe tener al menos 6 caracteres.', 'my-account-enhanced'),
                'privacy_required' => __('Debes aceptar la política de privacidad para continuar.', 'my-account-enhanced')
            ]
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_validation_scripts');
/**
 * Cargar scripts y estilos de administración
 */
function mam_enqueue_admin_assets($hook) {
    // Solo cargar en la página de configuración del plugin
    if (strpos($hook, 'my-account-enhanced') === false) {
        return;
    }
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
    
    wp_enqueue_style(
        'mam-admin-styles',
        MAM_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        MAM_VERSION
    );
    
    wp_enqueue_script(
        'mam-admin-scripts',
        MAM_PLUGIN_URL . 'assets/js/admin.js',
        array('jquery', 'jquery-ui-sortable', 'wp-color-picker'),
        MAM_VERSION,
        true
    );
    
    // Pasar variables a JavaScript
    wp_localize_script('mam-admin-scripts', 'mamAdminData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('mam-admin-nonce'),
        'i18n' => array(
            'confirmDelete' => __('¿Estás seguro de que quieres eliminar este campo?', 'my-account-enhanced'),
            'fieldOptions' => __('Opciones del campo', 'my-account-enhanced'),
            'fieldOptionsDesc' => __('Una opción por línea. Formato: valor:etiqueta. Ejemplo: opcion1:Opción 1', 'my-account-enhanced')
        )
    ));
}

/**
 * Activación del plugin
 */
function mam_activate() {
    // Inicializar opciones por defecto si no existen
    if (!get_option('mam_field_settings')) {
        // Crear configuración predeterminada para campos
        $default_fields = array(
            'billing_company' => array(
                'enabled' => true,
                'required' => true,
                'position' => 10
            ),
            'billing_cuit' => array(
                'enabled' => true,
                'required' => true,
                'position' => 20
            ),
            // Más campos predeterminados...
        );
        
        update_option('mam_field_settings', $default_fields);
    }
    
    // Marcar para limpiar los rewrite rules
    update_option('mam_flush_rewrite_rules', true);
}
register_activation_hook(__FILE__, 'mam_activate');

/**
 * Desactivación del plugin
 */
function mam_deactivate() {
    
    // Limpiar los rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'mam_deactivate');

/**
 * Flush rewrite rules en la siguiente carga si está marcado para ello
 */
function mam_maybe_flush_rewrite_rules() {
    if (get_option('mam_flush_rewrite_rules')) {
        flush_rewrite_rules();
        delete_option('mam_flush_rewrite_rules');
    }
}
add_action('init', 'mam_maybe_flush_rewrite_rules', 20);

/**
 * Carga de traducciones
 */
function mam_load_textdomain() {
    load_plugin_textdomain(
        'my-account-enhanced',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('init', 'mam_load_textdomain');
