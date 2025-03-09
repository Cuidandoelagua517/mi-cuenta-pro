<?php
/**
 * Clase para manejar funcionalidades específicas de empresas
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar funcionalidades de empresa
 */
class MAM_Company {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Añadir endpoint personalizado para panel de empresa
        add_action('init', array($this, 'add_company_endpoint'));
        
        // Añadir pestaña en la navegación de mi cuenta
        add_filter('woocommerce_account_menu_items', array($this, 'add_company_menu_item'));
        
        // Añadir contenido al endpoint de empresa
        add_action('woocommerce_account_company_endpoint', array($this, 'company_endpoint_content'));
        
        // Filtrar pedidos para mostrar solo los de la empresa del usuario
        add_action('pre_get_posts', array($this, 'filter_company_orders'));
        
        // Añadir información de empresa en el checkout
        add_filter('woocommerce_checkout_fields', array($this, 'add_company_checkout_fields'));
        
        // Guardar información de empresa en el pedido
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_company_order_data'));
        
        // Mostrar información de empresa en la página de pedido
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_company_order_data'));
        
        // Añadir filtro de empresa en la lista de pedidos de administración
        add_action('restrict_manage_posts', array($this, 'add_company_filter_to_orders'));
        add_filter('request', array($this, 'process_company_filter'));
    }
    
    /**
     * Añadir endpoint personalizado para panel de empresa
     */
    public function add_company_endpoint() {
        add_rewrite_endpoint('company', EP_ROOT | EP_PAGES);
        
        // Verifica si es necesario hacer flush de reglas
        if (get_option('mam_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
            delete_option('mam_flush_rewrite_rules');
        }
    }
    
    /**
     * Añadir pestaña en la navegación de mi cuenta
     *
     * @param array $items Items del menú
     * @return array Items modificados
     */
    public function add_company_menu_item($items) {
        // Solo mostrar si el usuario tiene datos de empresa
        if ($this->user_has_company_data()) {
            // Insertar antes de cerrar sesión
            $logout_item = array('customer-logout' => $items['customer-logout']);
            unset($items['customer-logout']);
            
            $items['company'] = __('Mi Empresa', 'my-account-enhanced');
            
            // Añadir de nuevo el logout
            $items = array_merge($items, $logout_item);
        }
        
        return $items;
    }
    
    /**
     * Verificar si el usuario tiene datos de empresa
     *
     * @param int $user_id ID del usuario a verificar (opcional)
     * @return bool Si el usuario tiene datos de empresa
     */
    public function user_has_company_data($user_id = 0) {
        if ($user_id <= 0) {
            $user_id = get_current_user_id();
        }
        
        if ($user_id <= 0) {
            return false;
        }
        
        $company_name = get_user_meta($user_id, 'billing_company', true);
        $company_cuit = get_user_meta($user_id, 'billing_cuit', true);
        
        return !empty($company_name) || !empty($company_cuit);
    }
    
    /**
     * Contenido del endpoint de empresa
     */
    public function company_endpoint_content() {
        $user_id = get_current_user_id();
        
        // Verificar si el usuario tiene datos de empresa
        if (!$this->user_has_company_data($user_id)) {
            echo '<p>' . esc_html__('No hay información de empresa disponible.', 'my-account-enhanced') . '</p>';
            return;
        }
        
        // Obtener datos de la empresa
        $company_data = $this->get_company_data($user_id);
        
        // Renderizar plantilla de empresa
        $this->render_company_template($company_data);
    }
    
    /**
     * Obtener datos de la empresa del usuario
     *
     * @param int $user_id ID del usuario
     * @return array Datos de la empresa
     */
    public function get_company_data($user_id) {
        $company_data = array(
            'name' => get_user_meta($user_id, 'billing_company', true),
            'cuit' => get_user_meta($user_id, 'billing_cuit', true),
            'address_1' => get_user_meta($user_id, 'billing_address_1', true),
            'address_2' => get_user_meta($user_id, 'billing_address_2', true),
            'city' => get_user_meta($user_id, 'billing_city', true),
            'state' => get_user_meta($user_id, 'billing_state', true),
            'postcode' => get_user_meta($user_id, 'billing_postcode', true),
            'country' => get_user_meta($user_id, 'billing_country', true),
            'phone' => get_user_meta($user_id, 'billing_phone', true),
            'email' => get_user_meta($user_id, 'billing_email', true)
        );
        
        // Obtener datos adicionales (campos personalizados)
        $custom_fields = get_option('mam_custom_fields', array());
        
        foreach ($custom_fields as $field_id => $field) {
            if ($field['section'] === 'billing') {
                $company_data[$field_id] = get_user_meta($user_id, $field_id, true);
            }
        }
        
        return $company_data;
    }
    
    /**
     * Renderizar plantilla de empresa
     *
     * @param array $company_data Datos de la empresa
     */
    public function render_company_template($company_data) {
        // En el método render_company_template
$template = MAM_PLUGIN_DIR . 'templates/company/company.php';
        // Comprobar si existe plantilla personalizada en el tema
        $template = locate_template(array(
            'my-account-enhanced/company.php',
            'woocommerce/my-account-enhanced/company.php'
        ));
        
        // Si no existe, usar plantilla predeterminada del plugin
        if (!$template) {
            $template = MAM_PLUGIN_DIR . 'templates/company/company.php';
        }
        
        // Incluir plantilla
        if (file_exists($template)) {
            include $template;
        } else {
            // Plantilla de respaldo en caso de que no exista
            ?>
            <div class="mam-company-info">
                <h2><?php echo esc_html__('Información de la Empresa', 'my-account-enhanced'); ?></h2>
                
                <div class="mam-company-details">
                    <p class="mam-company-name">
                        <strong><?php echo esc_html__('Nombre:', 'my-account-enhanced'); ?></strong> 
                        <?php echo esc_html($company_data['name']); ?>
                    </p>
                    
                    <?php if (!empty($company_data['cuit'])) : ?>
                    <p class="mam-company-cuit">
                        <strong><?php echo esc_html__('CUIT:', 'my-account-enhanced'); ?></strong> 
                        <?php echo esc_html($company_data['cuit']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="mam-company-address">
                        <strong><?php echo esc_html__('Dirección:', 'my-account-enhanced'); ?></strong><br>
                        <?php echo esc_html($company_data['address_1']); ?><br>
                        <?php if (!empty($company_data['address_2'])) : ?>
                            <?php echo esc_html($company_data['address_2']); ?><br>
                        <?php endif; ?>
                        <?php echo esc_html($company_data['city']); ?>, 
                        <?php echo esc_html($company_data['state']); ?> 
                        <?php echo esc_html($company_data['postcode']); ?><br>
                        <?php echo esc_html(WC()->countries->get_countries()[$company_data['country']]); ?>
                    </div>
                    
                    <p class="mam-company-contact">
                        <strong><?php echo esc_html__('Teléfono:', 'my-account-enhanced'); ?></strong> 
                        <?php echo esc_html($company_data['phone']); ?>
                    </p>
                    
                    <p class="mam-company-email">
                        <strong><?php echo esc_html__('Email:', 'my-account-enhanced'); ?></strong> 
                        <?php echo esc_html($company_data['email']); ?>
                    </p>
                    
                    <?php 
                    // Mostrar campos personalizados
                    foreach ($company_data as $key => $value) {
                        if (strpos($key, 'mam_custom_') === 0 && !empty($value)) {
                            $custom_fields = get_option('mam_custom_fields', array());
                            if (isset($custom_fields[$key])) {
                                $label = $custom_fields[$key]['label'];
                                echo '<p class="mam-company-' . esc_attr($key) . '">';
                                echo '<strong>' . esc_html($label) . ':</strong> ';
                                echo esc_html($value);
                                echo '</p>';
                            }
                        }
                    }
                    ?>
                </div>
                
                <div class="mam-company-actions">
                    <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'billing')); ?>" class="button">
                        <?php echo esc_html__('Editar información de empresa', 'my-account-enhanced'); ?>
                    </a>
                </div>
                
                <h3><?php echo esc_html__('Historial de Pedidos de la Empresa', 'my-account-enhanced'); ?></h3>
                
                <?php
                // Obtener pedidos de la empresa
                $company_orders = $this->get_company_orders();
                
                if (!empty($company_orders)) {
                    wc_get_template(
                        'myaccount/orders.php',
                        array(
                            'current_page' => 1,
                            'customer_orders' => $company_orders,
                            'has_orders' => true
                        )
                    );
                } else {
                    echo '<p>' . esc_html__('No hay pedidos para esta empresa.', 'my-account-enhanced') . '</p>';
                }
                ?>
                
                <h3><?php echo esc_html__('Documentos Fiscales', 'my-account-enhanced'); ?></h3>
                
                <?php
                // Obtener documentos fiscales
                $fiscal_documents = $this->get_fiscal_documents();
                
                if (!empty($fiscal_documents)) {
                    $this->render_fiscal_documents_table($fiscal_documents);
                } else {
                    echo '<p>' . esc_html__('No hay documentos fiscales disponibles.', 'my-account-enhanced') . '</p>';
                }
                ?>
            </div>
            <?php
        }
    }
    
    /**
     * Obtener pedidos de la empresa
     *
     * @return WP_Query Consulta con los pedidos de la empresa
     */
   public function get_company_orders() {
    $user_id = get_current_user_id();
    $company_name = get_user_meta($user_id, 'billing_company', true);
    
    if (empty($company_name)) {
        return array();
    }
    
    // Uso adecuado de wc_get_orders con HPOS
    return wc_get_orders(array(
        'billing_company' => $company_name,
        'limit' => -1
    ));
}
    
    /**
     * Obtener documentos fiscales
     *
     * @return array Documentos fiscales
     */
    public function get_fiscal_documents() {
        // Esta función es un ejemplo y debería adaptarse según la implementación real
        // de documentos fiscales (facturas, recibos, etc.)
        
        // Aquí simplemente simulamos algunos documentos para mostrar
        $documents = array();
        
        // Obtener pedidos de la empresa
        $company_orders = $this->get_company_orders();
        
        if (!empty($company_orders)) {
            foreach ($company_orders as $order) {
                // Crear un documento para cada pedido (esto es solo un ejemplo)
                $documents[] = array(
                    'id' => $order->get_id(),
                    'type' => 'invoice',
                    'number' => 'FC-' . $order->get_order_number(),
                    'date' => $order->get_date_created()->date_i18n(get_option('date_format')),
                    'total' => $order->get_formatted_order_total(),
                    'download_url' => add_query_arg(array(
                        'download_fiscal_document' => $order->get_id(),
                        'document_type' => 'invoice',
                        'security' => wp_create_nonce('download_fiscal_document')
                    ), wc_get_endpoint_url('company'))
                );
            }
        }
        
        return $documents;
    }
    
    /**
     * Renderizar tabla de documentos fiscales
     *
     * @param array $documents Documentos fiscales
     */
    public function render_fiscal_documents_table($documents) {
        ?>
        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders">
            <thead>
                <tr>
                    <th class="woocommerce-orders-table__header"><span class="nobr"><?php echo esc_html__('Tipo', 'my-account-enhanced'); ?></span></th>
                    <th class="woocommerce-orders-table__header"><span class="nobr"><?php echo esc_html__('Número', 'my-account-enhanced'); ?></span></th>
                    <th class="woocommerce-orders-table__header"><span class="nobr"><?php echo esc_html__('Fecha', 'my-account-enhanced'); ?></span></th>
                    <th class="woocommerce-orders-table__header"><span class="nobr"><?php echo esc_html__('Total', 'my-account-enhanced'); ?></span></th>
                    <th class="woocommerce-orders-table__header"><span class="nobr"><?php echo esc_html__('Acciones', 'my-account-enhanced'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document) : ?>
                <tr>
                    <td class="woocommerce-orders-table__cell">
                        <?php 
                        switch ($document['type']) {
                            case 'invoice':
                                echo esc_html__('Factura', 'my-account-enhanced');
                                break;
                            case 'receipt':
                                echo esc_html__('Recibo', 'my-account-enhanced');
                                break;
                            default:
                                echo esc_html($document['type']);
                        }
                        ?>
                    </td>
                    <td class="woocommerce-orders-table__cell"><?php echo esc_html($document['number']); ?></td>
                    <td class="woocommerce-orders-table__cell"><?php echo esc_html($document['date']); ?></td>
                    <td class="woocommerce-orders-table__cell"><?php echo wp_kses_post($document['total']); ?></td>
                    <td class="woocommerce-orders-table__cell">
                        <a href="<?php echo esc_url($document['download_url']); ?>" class="button">
                            <?php echo esc_html__('Descargar', 'my-account-enhanced'); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Filtrar pedidos para mostrar solo los de la empresa del usuario
     *
     * @param WP_Query $query Consulta de WordPress
     */
    public function filter_company_orders($query) {
    // Solo aplicar en el frontend y en la página de pedidos
    if (is_admin() || !$query->is_main_query() || !is_wc_endpoint_url('orders')) {
        return;
    }
    
    // Verificar si el usuario tiene datos de empresa
    $user_id = get_current_user_id();
    if (!$this->user_has_company_data($user_id)) {
        return;
    }
    
    // Obtener meta_query actual
    $meta_query = $query->get('meta_query', array());
    
    // Añadir filtro por empresa
    $company_name = get_user_meta($user_id, 'billing_company', true);
    if (!empty($company_name)) {
        $meta_query[] = array(
            'key' => '_billing_company',
            'value' => $company_name,
            'compare' => '='
        );
        
        $query->set('meta_query', $meta_query);
    }
}
    
    /**
     * Añadir campos de empresa al checkout
     *
     * @param array $fields Campos del checkout
     * @return array Campos modificados
     */
    public function add_company_checkout_fields($fields) {
        // Asegurarse de que los campos de empresa estén habilitados
        $field_settings = get_option('mam_field_settings', array());
        
        // Campo de empresa
        if (isset($field_settings['billing_company']['enabled']) && $field_settings['billing_company']['enabled']) {
            $fields['billing']['billing_company']['required'] = 
                isset($field_settings['billing_company']['required']) ? 
                $field_settings['billing_company']['required'] : 
                false;
            
            $fields['billing']['billing_company']['priority'] = 
                isset($field_settings['billing_company']['position']) ? 
                $field_settings['billing_company']['position'] : 
                30;
        }
        
        // Campo de CUIT
        if (isset($field_settings['billing_cuit']['enabled']) && $field_settings['billing_cuit']['enabled']) {
            $fields['billing']['billing_cuit'] = array(
                'label' => __('CUIT', 'my-account-enhanced'),
                'placeholder' => __('Ingrese el CUIT de la empresa', 'my-account-enhanced'),
                'required' => isset($field_settings['billing_cuit']['required']) ? 
                             $field_settings['billing_cuit']['required'] : 
                             false,
                'class' => array('form-row-wide'),
                'clear' => true,
                'priority' => isset($field_settings['billing_cuit']['position']) ? 
                             $field_settings['billing_cuit']['position'] : 
                             31
            );
        }
        
        return $fields;
    }
    
    /**
     * Guardar información de empresa en el pedido
     *
     * @param int $order_id ID del pedido
     */
public function save_company_order_data($order_id) {
    $order = wc_get_order($order_id);
    
    if (isset($_POST['billing_cuit'])) {
        $order->update_meta_data('_billing_cuit', sanitize_text_field($_POST['billing_cuit']));
    }
    
    // Guardar campos personalizados de empresa
    $custom_fields = get_option('mam_custom_fields', array());
    
    foreach ($custom_fields as $field_id => $field) {
        if ($field['section'] === 'billing' && isset($_POST[$field_id])) {
            $order->update_meta_data('_' . $field_id, sanitize_text_field($_POST[$field_id]));
        }
    }
    
    $order->save();
}
    
    /**
     * Mostrar información de empresa en la página de pedido
     *
     * @param WC_Order $order Objeto de pedido
     */
    public function show_company_order_data($order) {
        $cuit = get_post_meta($order->get_id(), '_billing_cuit', true);
        
        if (!empty($cuit)) {
            echo '<p><strong>' . esc_html__('CUIT:', 'my-account-enhanced') . '</strong> ' . esc_html($cuit) . '</p>';
        }
        
        // Mostrar campos personalizados de empresa
        $custom_fields = get_option('mam_custom_fields', array());
        
        foreach ($custom_fields as $field_id => $field) {
            if ($field['section'] === 'billing') {
                $value = get_post_meta($order->get_id(), '_' . $field_id, true);
                
                if (!empty($value)) {
                    echo '<p><strong>' . esc_html($field['label']) . ':</strong> ' . esc_html($value) . '</p>';
                }
            }
        }
    }
    
    /**
     * Añadir filtro de empresa en la lista de pedidos de administración
     */
    public function add_company_filter_to_orders() {
        global $typenow;
        
        if ('shop_order' !== $typenow) {
            return;
        }
        
        // Obtener todas las empresas únicas
        global $wpdb;
        $companies = $wpdb->get_col(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
            WHERE meta_key = '_billing_company' AND meta_value != '' 
            ORDER BY meta_value ASC"
        );
        
        if (empty($companies)) {
            return;
        }
        
        $current_company = isset($_GET['company']) ? wc_clean(wp_unslash($_GET['company'])) : '';
        
        ?>
        <select name="company" id="filter-by-company">
            <option value=""><?php echo esc_html__('Todas las empresas', 'my-account-enhanced'); ?></option>
            <?php foreach ($companies as $company) : ?>
                <option value="<?php echo esc_attr($company); ?>" <?php selected($current_company, $company); ?>>
                    <?php echo esc_html($company); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    /**
     * Procesar filtro de empresa
     *
     * @param array $vars Variables de consulta
     * @return array Variables de consulta modificadas
     */
    public function process_company_filter($vars) {
        global $typenow;
        
        if ('shop_order' !== $typenow || !isset($_GET['company']) || empty($_GET['company'])) {
            return $vars;
        }
        
        $company = wc_clean(wp_unslash($_GET['company']));
        
        $vars['meta_query'][] = array(
            'key' => '_billing_company',
            'value' => $company,
            'compare' => '='
        );
        
        return $vars;
    }
}
