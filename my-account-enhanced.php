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
        mam_require_if_exists(MAM_PLUGIN_DIR . 'admin/class-fields-manager.php');
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
    // Verificar que estamos en una página de cuenta
    if (!function_exists('is_account_page') || !is_account_page()) {
        return;
    }
    
    // Registrar y encolar estilos
    wp_register_style(
        'mam-frontend-styles',
        MAM_PLUGIN_URL . 'assets/css/frontend.css',
        array(),
        MAM_VERSION
    );
    
    wp_register_style(
        'mam-account-forms',
        MAM_PLUGIN_URL . 'assets/css/account-forms.css',
        array('mam-frontend-styles'),
        MAM_VERSION
    );
    
    wp_register_style(
        'mam-dashboard-styles',
        MAM_PLUGIN_URL . 'assets/css/dashboard.css',
        array('mam-frontend-styles'),
        MAM_VERSION
    );
    
    // Encolar estilos
    wp_enqueue_style('mam-frontend-styles');
    wp_enqueue_style('mam-account-forms');
    wp_enqueue_style('mam-dashboard-styles');
    
    // Registrar y encolar scripts
    wp_register_script(
        'mam-frontend-scripts',
        MAM_PLUGIN_URL . 'assets/js/frontend.js',
        array('jquery'),
        MAM_VERSION,
        true
    );
    
    wp_enqueue_script('mam-frontend-scripts');
    
    // Pasar datos al script
    wp_localize_script('mam-frontend-scripts', 'MAM_Data', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('mam-frontend-nonce'),
        'i18n' => array(
            'loading' => __('Cargando...', 'my-account-enhanced'),
            'error' => __('Ha ocurrido un error', 'my-account-enhanced')
        ),
        'isMyAccount' => is_account_page() ? 'yes' : 'no'
    ));
}
/**
 * Asegurar compatibilidad con navegación AJAX
 */
function mam_ajax_compatibility() {
    // Verificar si es una petición AJAX a una página de mi cuenta
    if (wp_doing_ajax() && isset($_SERVER['HTTP_REFERER']) && 
        strpos($_SERVER['HTTP_REFERER'], '/my-account/') !== false) {
        
        // Cargar funciones de plantilla de WooCommerce si no están cargadas
        if (!function_exists('wc_get_template')) {
            WC()->frontend_includes();
        }
    }
}
add_action('init', 'mam_ajax_compatibility', 5);
/**
 * Asegurar que las variables necesarias estén disponibles para el dashboard
 */
function mam_ensure_dashboard_variables($template_name, $template_path, $located, $args) {
    if (strpos($located, 'dashboard/dashboard.php') !== false) {
        // Si la plantilla es nuestro dashboard, asegurarnos de que las variables estén disponibles
        global $user, $user_id, $user_info, $recent_orders, $company_data;
        
        if (!isset($user) || !is_object($user)) {
            $user = wp_get_current_user();
        }
        
        if (!isset($user_id) || empty($user_id)) {
            $user_id = get_current_user_id();
        }
        
        if (!isset($user_info) || !is_array($user_info)) {
            // Inicializar información de usuario
            $user_info = array(
                'first_name' => get_user_meta($user_id, 'billing_first_name', true),
                'last_name' => get_user_meta($user_id, 'billing_last_name', true),
                'phone' => get_user_meta($user_id, 'billing_phone', true),
                'birthday' => get_user_meta($user_id, 'customer_birthday', true)
            );
        }
        
        if (!isset($recent_orders)) {
            // Obtener pedidos recientes
            $recent_orders = wc_get_orders(array(
                'customer' => $user_id,
                'limit' => 5,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
        }
        
        if (!isset($company_data)) {
            // Obtener datos de empresa
            $company_data = array(
                'name' => get_user_meta($user_id, 'billing_company', true),
                'cuit' => get_user_meta($user_id, 'billing_cuit', true)
            );
        }
    }
}
add_action('woocommerce_before_template_part', 'mam_ensure_dashboard_variables', 10, 4);
/**
 * Filtrar navegación para asegurar clases CSS correctas
 */
function mam_filter_account_navigation($items) {
    // Asegurar que los elementos de navegación tengan clases necesarias para JavaScript
    foreach ($items as $endpoint => $label) {
        add_filter('woocommerce_account_menu_item_classes', function($classes, $endpoint_check) use ($endpoint) {
            if ($endpoint_check === $endpoint) {
                $classes[] = 'mam-nav-item';
                $classes[] = 'mam-nav-item-' . sanitize_html_class($endpoint);
            }
            return $classes;
        }, 10, 2);
    }
    return $items;
}
add_filter('woocommerce_account_menu_items', 'mam_filter_account_navigation', 99);
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
function mam_handle_ajax_requests() {
    // Solo procesar si es una petición a Mi Cuenta
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' &&
        strpos($_SERVER['REQUEST_URI'], '/my-account/') !== false) {
        
        // Asegurarse de que se carguen todas las dependencias necesarias
        if (!did_action('woocommerce_init')) {
            do_action('woocommerce_init');
        }
        
        // Configurar funciones de plantilla de WooCommerce
        if (function_exists('wc_template_functions')) {
            wc_template_functions();
        }
    }
}
add_action('init', 'mam_handle_ajax_requests', 5);
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
