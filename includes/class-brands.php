<?php
/**
 * Clase para manejar el panel de marcas
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar el panel de marcas
 */
class MAM_Brands {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Añadir panel de marcas en mi cuenta
        add_action('woocommerce_account_content', array($this, 'maybe_render_brands_panel'), 5);
        
        // AJAX para filtrar pedidos por marca
        add_action('wp_ajax_mam_filter_orders_by_brand', array($this, 'ajax_filter_orders_by_brand'));
        
        // Añadir estilos específicos para el panel de marcas
        add_action('wp_enqueue_scripts', array($this, 'enqueue_brands_styles'), 20);
    }
    
    /**
     * Verificar si el panel de marcas está activo
     *
     * @return bool Si el panel está activo o no
     */
    public function is_brands_panel_enabled() {
        $settings = get_option('mam_brands_settings', array());
        return isset($settings['enabled']) ? (bool) $settings['enabled'] : true;
    }
    
    /**
     * Renderizar panel lateral de marcas si está habilitado
     */
    public function maybe_render_brands_panel() {
        // Verificar si está habilitado
        if (!$this->is_brands_panel_enabled()) {
            return;
        }
        
        // Verificar si estamos en una página de mi cuenta
        if (!is_account_page()) {
            return;
        }
        
        // Obtener todas las marcas
        $brands = $this->get_all_brands();
        
        if (empty($brands)) {
            return;
        }
        
        // Renderizar el panel de marcas
        $this->render_brands_panel($brands);
    }
    
    /**
     * Obtener todas las marcas disponibles en WooCommerce
     *
     * @return array Listado de marcas
     */
    public function get_all_brands() {
        // Verificar si existe el atributo 'brand' o similar
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $brands_taxonomy = '';
        
        // Buscar el atributo que corresponde a marcas
        foreach ($attribute_taxonomies as $attr) {
            if (strpos(strtolower($attr->attribute_name), 'brand') !== false || 
               strpos(strtolower($attr->attribute_label), 'marca') !== false) {
                $brands_taxonomy = 'pa_' . $attr->attribute_name;
                break;
            }
        }
        
        if (empty($brands_taxonomy)) {
            return array();
        }
        
        // Obtener todos los términos (marcas)
        $terms = get_terms(array(
            'taxonomy' => $brands_taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }
        
        $brands = array();
        foreach ($terms as $term) {
            // Obtener logo si existe (asumiendo que se almacena como term meta)
            $logo_id = get_term_meta($term->term_id, 'brand_logo', true);
            $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
            
            $brands[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'logo' => $logo_url,
                'description' => $term->description,
                'count' => $term->count,
                'taxonomy' => $brands_taxonomy
            );
        }
        
        return $brands;
    }
    
    /**
     * Renderizar panel lateral de marcas
     *
     * @param array $brands Listado de marcas a mostrar
     */
    public function render_brands_panel($brands) {
        // Obtener configuración del panel
        $settings = get_option('mam_brands_settings', array());
        $columns = isset($settings['columns']) ? intval($settings['columns']) : 2;
        $logo_size = isset($settings['logo_size']) ? $settings['logo_size'] : 'medium';
        
        // Clases según configuración
        $container_class = 'mam-brands-container columns-' . $columns . ' size-' . $logo_size;
        
        // Comenzar a renderizar el panel
        ?>
        <div class="mam-brands-panel">
            <h4><?php echo esc_html__('Nuestras Marcas', 'my-account-enhanced'); ?></h4>
            <div class="<?php echo esc_attr($container_class); ?>">
                <?php foreach ($brands as $brand) : ?>
                    <div class="mam-brand-item" data-brand-id="<?php echo esc_attr($brand['id']); ?>" data-brand-taxonomy="<?php echo esc_attr($brand['taxonomy']); ?>">
                        <?php if (!empty($brand['logo'])) : ?>
                            <img src="<?php echo esc_url($brand['logo']); ?>" alt="<?php echo esc_attr($brand['name']); ?>" class="mam-brand-logo">
                        <?php else : ?>
                            <span class="mam-brand-name"><?php echo esc_html($brand['name']); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (is_wc_endpoint_url('orders')) : ?>
                <div class="mam-brand-filter">
                    <button class="mam-filter-by-brand button"><?php echo esc_html__('Filtrar pedidos por marca', 'my-account-enhanced'); ?></button>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Filtrar pedidos por marca vía AJAX
     */
public function ajax_filter_orders_by_brand() {
    // Verificar nonce y permisos
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mam-frontend-nonce')) {
        wp_send_json_error(array('message' => __('Error de seguridad. Por favor, recarga la página.', 'my-account-enhanced')));
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => __('Debes iniciar sesión para ver tus pedidos.', 'my-account-enhanced')));
    }
    
    // Obtener datos de la solicitud
    $brand_id = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
    $brand_taxonomy = isset($_POST['brand_taxonomy']) ? sanitize_text_field($_POST['brand_taxonomy']) : '';
    
    if ($brand_id <= 0 || empty($brand_taxonomy)) {
        wp_send_json_error(array('message' => __('Datos de marca inválidos.', 'my-account-enhanced')));
    }
    
    // Obtener pedidos del usuario actual
    $customer_id = get_current_user_id();
    $customer_orders = wc_get_orders(array(
        'customer_id' => $customer_id,
        'limit' => -1
    ));
        
        // Filtrar pedidos que contienen productos de la marca seleccionada
        $filtered_orders = array();
        
        foreach ($customer_orders as $order) {
            $items = $order->get_items();
            $has_brand = false;
            
            foreach ($items as $item) {
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
                
                if ($product && $product->is_type('variation')) {
                    $parent_id = $product->get_parent_id();
                    $product_terms = wp_get_post_terms($parent_id, $brand_taxonomy, array('fields' => 'ids'));
                } else {
                    $product_terms = wp_get_post_terms($product_id, $brand_taxonomy, array('fields' => 'ids'));
                }
                
                if (in_array($brand_id, $product_terms)) {
                    $has_brand = true;
                    break;
                }
            }
            
            if ($has_brand) {
                $filtered_orders[] = array(
                    'id' => $order->get_id(),
                    'number' => $order->get_order_number(),
                    'status' => wc_get_order_status_name($order->get_status()),
                    'date' => $order->get_date_created()->date_i18n(get_option('date_format')),
                    'total' => $order->get_formatted_order_total(),
                    'view_url' => $order->get_view_order_url()
                );
            }
        }
        
        // Enviar respuesta
        if (empty($filtered_orders)) {
            wp_send_json_success(array(
                'message' => __('No se encontraron pedidos para esta marca.', 'my-account-enhanced'),
                'orders' => array()
            ));
        } else {
            wp_send_json_success(array(
                'message' => sprintf(__('Se encontraron %d pedidos para esta marca.', 'my-account-enhanced'), count($filtered_orders)),
                'orders' => $filtered_orders
            ));
        }
    }
    
    /**
     * Cargar estilos específicos para el panel de marcas
     */
    public function enqueue_brands_styles() {
        // Solo cargar en páginas de Mi Cuenta
        if (!is_account_page() || !$this->is_brands_panel_enabled()) {
            return;
        }
        
        // Registrar y encolar estilos
        wp_enqueue_style(
            'mam-brands-styles',
            MAM_PLUGIN_URL . 'assets/css/brands.css',
            array(),
            MAM_VERSION
        );
    }
}
