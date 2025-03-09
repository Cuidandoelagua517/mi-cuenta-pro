
<?php
// En includes/class-dashboard.php
class MAM_Dashboard {
    
// Al inicio del constructor de MAM_Dashboard
public function __construct() {
    // Aumentar la prioridad a un número más bajo para que se ejecute antes
    remove_action('woocommerce_account_dashboard', 'woocommerce_account_dashboard');
    add_action('woocommerce_account_dashboard', array($this, 'render_custom_dashboard'), 1);
    
    // Mantener los hooks existentes
    add_filter('woocommerce_account_menu_items', array($this, 'customize_account_menu_items'), 10, 1);
    
    // Añadir estas líneas nuevas para asegurarnos de ocultar la navegación nativa
    add_action('wp_head', array($this, 'add_custom_inline_styles'));
    add_filter('body_class', array($this, 'add_body_class'));
     // Registrar endpoints
    add_action('init', array($this, 'register_endpoints'), 10);
}
/**
 * Registrar endpoints de WooCommerce
 */
public function register_endpoints() {
    // Asegurarse de que los endpoints estándar de WooCommerce estén registrados
    add_rewrite_endpoint('orders', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('view-order', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('downloads', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('edit-account', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('edit-address', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('customer-logout', EP_ROOT | EP_PAGES);
    
    // Endpoints personalizados
    add_rewrite_endpoint('company', EP_ROOT | EP_PAGES);
    
    // Importante: Hacer flush de las reglas de rewrite si es necesario
    if (get_option('mam_flush_rewrite_rules', false)) {
        flush_rewrite_rules();
        delete_option('mam_flush_rewrite_rules');
    }
}
/**
 * Añadir estilos inline críticos para asegurar que nuestra interfaz tome prioridad
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
 
    public function render_custom_dashboard() {
        // Eliminar la acción predeterminada de WooCommerce
        remove_action('woocommerce_account_dashboard', 'woocommerce_account_dashboard');
        
        // Comprobar si existe plantilla personalizada en el tema
        $template = locate_template(array(
            'my-account-enhanced/dashboard.php',
            'woocommerce/my-account-enhanced/dashboard.php'
        ));
        
        // Si no existe, usar plantilla predeterminada del plugin
        if (!$template) {
            $template = MAM_PLUGIN_DIR . 'templates/dashboard/dashboard.php';
        }
        
        // Si existe, incluir la plantilla
        if (file_exists($template)) {
            // Obtener datos para la plantilla
            $user_id = get_current_user_id();
            $user = get_userdata($user_id);
            $user_info = $this->get_user_info($user_id);
            $recent_orders = $this->get_recent_orders($user_id);
            $company_data = $this->get_company_data($user_id);
            
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
        </div>
        <?php
    }
    
    /**
     * Obtener información del usuario
     *
     * @param int $user_id ID del usuario
     * @return array Información del usuario
     */
    public function get_user_info($user_id) {
        return array(
            'first_name' => get_user_meta($user_id, 'billing_first_name', true),
            'last_name' => get_user_meta($user_id, 'billing_last_name', true),
            'phone' => get_user_meta($user_id, 'billing_phone', true),
            'birthday' => get_user_meta($user_id, 'customer_birthday', true)
        );
    }
    
    /**
     * Obtener pedidos recientes del usuario
     *
     * @param int $user_id ID del usuario
     * @param int $limit Número de pedidos a obtener
     * @return array Pedidos recientes
     */
    public function get_recent_orders($user_id, $limit = 5) {
        return wc_get_orders(array(
            'customer' => $user_id,
            'limit' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
    }
    
    /**
     * Obtener datos de empresa del usuario
     *
     * @param int $user_id ID del usuario
     * @return array Datos de empresa
     */
    public function get_company_data($user_id) {
        return array(
            'name' => get_user_meta($user_id, 'billing_company', true),
            'cuit' => get_user_meta($user_id, 'billing_cuit', true)
        );
    }
    
    /**
     * Personalizar orden y elementos del menú de cuenta
     *
     * @param array $items Items del menú
     * @return array Items modificados
     */
    public function customize_account_menu_items($items) {
        // Obtener configuración de secciones
        $section_settings = get_option('mam_section_settings', array());
        
        // Si no hay configuración, devolver los items sin modificar
        if (empty($section_settings)) {
            return $items;
        }
        
        $new_items = array();
        
        // Filtrar y ordenar secciones según configuración
        foreach ($section_settings as $section_id => $section) {
            // Si la sección está habilitada y existe en los items originales
            if (isset($section['enabled']) && $section['enabled'] && isset($items[$section_id])) {
                $new_items[$section_id] = $items[$section_id];
            }
        }
        
        // Si hay secciones habilitadas, ordenarlas por posición
        if (!empty($new_items)) {
            // Crear array temporal con posiciones
            $temp_items = array();
            foreach ($new_items as $section_id => $label) {
                $temp_items[$section_id] = array(
                    'label' => $label,
                    'position' => isset($section_settings[$section_id]['position']) ? $section_settings[$section_id]['position'] : 999
                );
            }
            
            // Ordenar por posición
            uasort($temp_items, function($a, $b) {
                return $a['position'] - $b['position'];
            });
            
            // Reconstruir array final
            $new_items = array();
            foreach ($temp_items as $section_id => $data) {
                $new_items[$section_id] = $data['label'];
            }
            
            // Añadir el endpoint de empresa si no está incluido pero el usuario tiene datos de empresa
            $user_id = get_current_user_id();
            $company_name = get_user_meta($user_id, 'billing_company', true);
            
            if (!empty($company_name) && !isset($new_items['company'])) {
                // Buscar la posición de logout
                if (isset($new_items['customer-logout'])) {
                    // Guardar el logout
                    $logout = $new_items['customer-logout'];
                    unset($new_items['customer-logout']);
                    
                    // Añadir empresa
                    $new_items['company'] = __('Mi Empresa', 'my-account-enhanced');
                    
                    // Volver a añadir el logout
                    $new_items['customer-logout'] = $logout;
                } else {
                    // Si no hay logout, simplemente añadir al final
                    $new_items['company'] = __('Mi Empresa', 'my-account-enhanced');
                }
            }
            
            return $new_items;
        }
        
        // Si no hay secciones habilitadas, devolver los items originales
        return $items;
    }
    
    /**
     * Añadir clase CSS al cuerpo para personalizar
     *
     * @param array $classes Clases del cuerpo
     * @return array Clases modificadas
     */
   public function add_body_class($classes) {
    if (is_account_page()) {
        $classes[] = 'mam-my-account';
        // AÑADE esta línea a la función existente
        $classes[] = 'mam-modernized-account'; // Nueva clase para el diseño modernizado
    }
    
    return $classes;
}
}
