<?php
/**
 * Clase para manejar el dashboard personalizado
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar el dashboard personalizado
 */
class MAM_Dashboard {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Eliminar acción nativa de WooCommerce - MAYOR PRIORIDAD
        remove_action('woocommerce_account_dashboard', 'woocommerce_account_dashboard');
        
        // Eliminar completamente la navegación nativa de WooCommerce
        remove_action('woocommerce_account_navigation', 'woocommerce_account_navigation');
        
        // Agregar nuestro dashboard personalizado
        add_action('woocommerce_account_dashboard', array($this, 'render_custom_dashboard'), 5);
        
        // Personalizar los elementos del menú
        add_filter('woocommerce_account_menu_items', array($this, 'customize_account_menu_items'), 10, 1);
        
        // Añadir clase CSS para identificar elementos activos
        add_filter('woocommerce_account_menu_item_classes', array($this, 'add_active_class_to_menu_item'), 10, 2);
        
        // Asegurar que los scripts se carguen en todas las páginas de mi cuenta
        add_action('wp_enqueue_scripts', array($this, 'ensure_scripts_loaded'), 20);
        
        // Añadir clases específicas al body
        add_filter('body_class', array($this, 'add_body_class'));
        
        // Añadir estilos inline para evitar duplicación de menús
        add_action('wp_head', array($this, 'add_custom_inline_styles'), 100);
    }
    
    /**
     * Añadir estilos inline críticos
     */
    public function add_custom_inline_styles() {
        if (is_account_page()) {
            echo '<style>
                .woocommerce-MyAccount-navigation { display: none !important; }
                .woocommerce-MyAccount-content { width: 100% !important; float: none !important; padding: 0 !important; margin: 0 !important; }
                .woocommerce-account .woocommerce { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
                
                /* Estilos críticos para el dashboard moderno */
                .mam-dashboard { display: block !important; visibility: visible !important; }
                .mam-dashboard-container { display: flex !important; visibility: visible !important; }
                .mam-sidebar { width: 250px; position: sticky; top: 0; height: 100vh; background: white; }
                .mam-main-content { flex: 1; padding: 30px; }
            </style>';
        }
    }

    /**
     * Asegurar que scripts se carguen en mi cuenta
     */
    public function ensure_scripts_loaded() {
        if (is_account_page()) {
            // Forzar carga de scripts en todas las páginas de Mi Cuenta
            wp_enqueue_script('mam-frontend-scripts');
            wp_enqueue_style('mam-frontend-styles');
            wp_enqueue_style('mam-dashboard-styles');
        }
    }
    
    /**
     * Añadir clase active a elementos de menú
     */
    public function add_active_class_to_menu_item($classes, $endpoint) {
        global $wp;
        
        $current = isset($wp->query_vars[$endpoint]);
        
        if ($endpoint === 'dashboard' && (empty($wp->query_vars) || isset($wp->query_vars['page']))) {
            $current = true;
        }
        
        if ($current) {
            $classes[] = 'is-active';
        }
        
        return $classes;
    }
    
    /**
     * Renderizar dashboard personalizado
     */
    public function render_custom_dashboard() {
        // Obtener datos para la plantilla
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $user_info = $this->get_user_info($user_id);
        $recent_orders = $this->get_recent_orders($user_id);
        $company_data = $this->get_company_data($user_id);
        
        // Comprobar si existe plantilla personalizada en el tema
        $template = locate_template(array(
            'my-account-enhanced/dashboard.php',
            'woocommerce/my-account-enhanced/dashboard.php'
        ));
        
        // Si no existe, usar plantilla predeterminada del plugin
        if (!$template) {
            $template = MAM_PLUGIN_DIR . 'templates/dashboard/dashboard.php';
        }
        
        // Si existe, incluir la plantilla (con todas las variables necesarias)
        if (file_exists($template)) {
            // Asegurarnos de que todas las variables estén disponibles para la plantilla
            include $template;
        } else {
            // Si no existe plantilla, mostrar dashboard predeterminado
            $this->render_default_dashboard();
        }
    }
    
    /**
     * Renderizar dashboard predeterminado
     */
    public function render_default_dashboard() {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $user_info = $this->get_user_info($user_id);
        $recent_orders = $this->get_recent_orders($user_id);
        $company_data = $this->get_company_data($user_id);
        
        ?>
        <div class="mam-dashboard">
            <div class="mam-dashboard-header">
                <h2><?php echo esc_html(sprintf(__('Hola, %s', 'my-account-enhanced'), $user->display_name)); ?></h2>
                <p><?php echo esc_html__('Bienvenido a tu cuenta.', 'my-account-enhanced'); ?></p>
            </div>
            
            <div class="mam-dashboard-content">
                <!-- Información del usuario -->
                <div class="mam-dashboard-card mam-user-card">
                    <h3><?php echo esc_html__('Información Personal', 'my-account-enhanced'); ?></h3>
                    
                    <div class="mam-user-details">
                        <p>
                            <strong><?php echo esc_html__('Nombre:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html($user_info['first_name'] . ' ' . $user_info['last_name']); ?>
                        </p>
                        
                        <p>
                            <strong><?php echo esc_html__('Email:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html($user->user_email); ?>
                        </p>
                        
                        <?php if (!empty($user_info['phone'])) : ?>
                        <p>
                            <strong><?php echo esc_html__('Teléfono:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html($user_info['phone']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($user_info['birthday'])) : ?>
                        <p>
                            <strong><?php echo esc_html__('Fecha de cumpleaños:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user_info['birthday']))); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="button">
                        <?php echo esc_html__('Editar información', 'my-account-enhanced'); ?>
                    </a>
                </div>
                
                <!-- Si tiene datos de empresa, mostrar tarjeta de empresa -->
                <?php if (!empty($company_data['name'])) : ?>
                <div class="mam-dashboard-card mam-company-card">
                    <h3><?php echo esc_html__('Información de Empresa', 'my-account-enhanced'); ?></h3>
                    
                    <div class="mam-company-details">
                        <p>
                            <strong><?php echo esc_html__('Empresa:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html($company_data['name']); ?>
                        </p>
                        
                        <?php if (!empty($company_data['cuit'])) : ?>
                        <p>
                            <strong><?php echo esc_html__('CUIT:', 'my-account-enhanced'); ?></strong> 
                            <?php echo esc_html($company_data['cuit']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?php echo esc_url(wc_get_endpoint_url('company')); ?>" class="button">
                        <?php echo esc_html__('Ver información de empresa', 'my-account-enhanced'); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Pedidos recientes -->
                <div class="mam-dashboard-card mam-orders-card">
                    <h3><?php echo esc_html__('Pedidos Recientes', 'my-account-enhanced'); ?></h3>
                    
                    <?php if (!empty($recent_orders)) : ?>
                    <table class="mam-orders-table">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('Pedido', 'my-account-enhanced'); ?></th>
                                <th><?php echo esc_html__('Fecha', 'my-account-enhanced'); ?></th>
                                <th><?php echo esc_html__('Estado', 'my-account-enhanced'); ?></th>
                                <th><?php echo esc_html__('Total', 'my-account-enhanced'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order) : ?>
                            <tr>
                                <td>#<?php echo esc_html($order->get_order_number()); ?></td>
                                <td><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></td>
                                <td><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></td>
                                <td><?php echo wp_kses_post($order->get_formatted_order_total()); ?></td>
                                <td>
                                    <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="button">
                                        <?php echo esc_html__('Ver', 'my-account-enhanced'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else : ?>
                    <p><?php echo esc_html__('No hay pedidos recientes.', 'my-account-enhanced'); ?></p>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="button">
                        <?php echo esc_html__('Ver todos los pedidos', 'my-account-enhanced'); ?>
                    </a>
                </div>
                
                <!-- Direcciones -->
                <div class="mam-dashboard-card mam-addresses-card">
                    <h3><?php echo esc_html__('Mis Direcciones', 'my-account-enhanced'); ?></h3>
                    
                    <div class="mam-addresses-container">
                        <div class="mam-address-box">
                            <h4><?php echo esc_html__('Dirección de Facturación', 'my-account-enhanced'); ?></h4>
                            <?php
                            $billing_address = wc_get_account_formatted_address('billing');
                            if (!empty($billing_address)) {
                                echo '<address>' . wp_kses_post($billing_address) . '</address>';
                            } else {
                                echo '<p>' . esc_html__('No has configurado esta dirección aún.', 'my-account-enhanced') . '</p>';
                            }
                            ?>
                        </div>
                        
                        <div class="mam-address-box">
                            <h4><?php echo esc_html__('Dirección de Envío', 'my-account-enhanced'); ?></h4>
                            <?php
                            $shipping_address = wc_get_account_formatted_address('shipping');
                            if (!empty($shipping_address)) {
                                echo '<address>' . wp_kses_post($shipping_address) . '</address>';
                            } else {
                                echo '<p>' . esc_html__('No has configurado esta dirección aún.', 'my-account-enhanced') . '</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" class="button">
                        <?php echo esc_html__('Gestionar direcciones', 'my-account-enhanced'); ?>
                    </a>
                </div>
            </div>
