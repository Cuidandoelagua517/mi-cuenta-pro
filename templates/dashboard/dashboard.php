<?php
/**
 * Template para el dashboard moderno personalizado
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

// Asegurarse de que tenemos el objeto de usuario correcto
if (!$user || !is_object($user) || !isset($user->user_email)) {
    $user = wp_get_current_user();
}

// Inicialización de las iniciales
$user_initials = '';

// Intentar obtener el nombre y apellido (con múltiples fuentes)
$first_name = get_user_meta($user_id, 'billing_first_name', true);
$last_name = get_user_meta($user_id, 'billing_last_name', true);

// Si no hay datos de facturación, intentar con datos básicos de usuario
if (empty($first_name)) {
    $first_name = $user->first_name;
}
if (empty($last_name)) {
    $last_name = $user->last_name;
}

// Crear iniciales si hay datos de nombre/apellido
if (!empty($first_name)) {
    $user_initials .= strtoupper(substr(trim($first_name), 0, 1));
}
if (!empty($last_name)) {
    $user_initials .= strtoupper(substr(trim($last_name), 0, 1));
}

// Si no hay iniciales basadas en nombre, SIEMPRE usar email
if (empty($user_initials) && !empty($user->user_email)) {
    // Obtener SOLO la primera letra del email
    $user_initials = strtoupper(substr($user->user_email, 0, 1));
}

// Obtener el menú de navegación
$menu_items = wc_get_account_menu_items();
$current_endpoint = WC()->query->get_current_endpoint();
if (empty($current_endpoint)) {
    $current_endpoint = 'dashboard';
}

// Optimizar consultas de pedidos - obtener estadísticas de una sola vez
$orders = wc_get_orders(array(
    'customer' => $user_id,
    'limit' => -1,
    'return' => 'ids'
));

$total_orders = count($orders);

// Contar pedidos por estado de manera eficiente
$processing_orders = 0;
$completed_orders = 0;

if ($total_orders > 0) {
    $status_counts = wc_get_orders(array(
        'customer' => $user_id,
        'status' => array('processing', 'completed'),
        'return' => 'ids',
        'limit' => -1,
        'group_by' => 'status'
    ));
    
    $processing_orders = isset($status_counts['processing']) ? count($status_counts['processing']) : 0;
    $completed_orders = isset($status_counts['completed']) ? count($status_counts['completed']) : 0;
}

// Definir iconos para menú
$menu_icons = array(
    'dashboard' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
    'orders' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
    'downloads' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>',
    'edit-address' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
    'edit-account' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
    'company' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
    'customer-logout' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>'
);

// Verificar si hay plugin de lista de deseos activo
$has_wishlist_plugin = false;
$wishlist_items = array();
$wishlist_url = '';

// Verificar YITH WooCommerce Wishlist
if (function_exists('YITH_WCWL')) {
    $has_wishlist_plugin = true;
    $wishlist_items = YITH_WCWL()->get_products();
    $wishlist_url = YITH_WCWL()->get_wishlist_url();
} 
// Verificar TI WooCommerce Wishlist
elseif (function_exists('tinv_get_wishlist_products')) {
    $has_wishlist_plugin = true;
    $wishlist_items = tinv_get_wishlist_products();
    $wishlist_url = tinvwl_url_wishlist_default();
}
?>

<div class="mam-dashboard-container">
    <!-- Sidebar de navegación -->
    <aside class="mam-sidebar">
        <!-- Botón de TIENDA ahora está dentro del sidebar como primer elemento -->
        <div class="mam-sidebar-logo">
            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="tienda-btn">
                TIENDA
            </a>
        </div>
        
<!-- Reemplaza la sección actual del menú de navegación con esto: -->
<ul class="mam-nav-menu">
    <?php foreach ($menu_items as $endpoint => $label) : 
        // Determinar si este elemento está activo
        $is_active = ($endpoint === $current_endpoint) ? 'active' : '';
        
        // Asignar el icono correcto según el endpoint
        $icon = isset($menu_icons[$endpoint]) ? $menu_icons[$endpoint] : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>';
        
        // Importante: Generar la URL correcta para cada endpoint de WooCommerce
        $endpoint_url = $endpoint === 'dashboard' 
            ? wc_get_page_permalink('myaccount') 
            : wc_get_account_endpoint_url($endpoint);
    ?>
    <li class="<?php echo $is_active; ?>">
        <a href="<?php echo esc_url($endpoint_url); ?>" class="direct-link">
            <?php echo $icon; ?>
            <span class="mam-nav-text"><?php echo esc_html($label); ?></span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>
        
        <div class="mam-user-avatar">
            <div class="mam-avatar-circle" title="<?php echo esc_attr(sprintf(__('Iniciado como %s', 'my-account-enhanced'), $user->user_email)); ?>">
                <?php echo esc_html($user_initials); ?>
            </div>
            <div class="mam-user-info">
                <p class="mam-user-name">
                    <?php 
                    if (!empty($user_info['first_name']) || !empty($user_info['last_name'])) {
                        echo esc_html(trim($user_info['first_name'] . ' ' . $user_info['last_name']));
                    } else {
                        echo esc_html($user->user_email);
                    }
                    ?>
                </p>
                <p class="mam-user-email"><?php echo esc_html($user->user_email); ?></p>
            </div>
        </div>
    </aside>
    
    <!-- Contenido principal -->
    <main class="mam-main-content">
        <!-- Encabezado del dashboard -->
        <div class="mam-dashboard-header">
            <h2 class="mam-dashboard-greeting">
                <?php 
                $greeting_name = !empty($user_info['first_name']) ? $user_info['first_name'] : $user->user_email;
                printf(esc_html__('Hola, %s', 'my-account-enhanced'), esc_html($greeting_name)); 
                ?>
            </h2>
        </div>
        
        <!-- Tarjetas de información -->
        <div class="mam-dashboard-cards">
            <!-- Información personal -->
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Información Personal', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Nombre:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value">
                            <?php 
                            $display_name = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
                            echo !empty($display_name) ? esc_html($display_name) : '<em>' . esc_html__('No especificado', 'my-account-enhanced') . '</em>'; 
                            ?>
                        </div>
                    </div>
                    
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Email:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value"><?php echo esc_html($user->user_email); ?></div>
                    </div>
                    
                    <?php if (!empty($user_info['phone'])) : ?>
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Teléfono:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value"><?php echo esc_html($user_info['phone']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($user_info['birthday'])) : ?>
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Fecha de cumpleaños:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($user_info['birthday']))); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Mostrar campos personalizados adicionales
                    $custom_fields = get_option('mam_custom_fields', array());
                    foreach ($custom_fields as $field_id => $field) {
                        if ($field['section'] === 'account' && isset($field['enabled']) && $field['enabled']) {
                            $value = get_user_meta($user_id, $field_id, true);
                            if (!empty($value)) {
                                echo '<div class="mam-info-row">';
                                echo '<div class="mam-info-label">' . esc_html($field['label']) . ':</div>';
                                echo '<div class="mam-info-value">' . esc_html($value) . '</div>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                    
                    <div class="mam-mt-20">
                        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="mam-button mam-button-primary" aria-label="<?php echo esc_attr__('Editar mi información personal', 'my-account-enhanced'); ?>">
                            <?php echo esc_html__('Editar información', 'my-account-enhanced'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pedidos recientes -->
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Pedidos Recientes', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <?php if (!empty($recent_orders)) : ?>
                        <?php foreach ($recent_orders as $order) : 
                            $status = $order->get_status();
                            $status_label = wc_get_order_status_name($status);
                            $status_class = '';
                            
                            // Determinar clase CSS según estado
                            switch ($status) {
                                case 'completed':
                                    $status_class = 'mam-status-completed';
                                    break;
                                case 'processing':
                                case 'on-hold':
                                    $status_class = 'mam-status-processing';
                                    break;
                                default:
                                    $status_class = 'mam-status-pending';
                            }
                            
                            $items_count = count($order->get_items());
                            $items_text = sprintf(_n('%s producto', '%s productos', $items_count, 'my-account-enhanced'), $items_count);
                        ?>
                        <div class="mam-order-item">
                            <div>
                                <div class="mam-order-title"><?php echo esc_html__('Pedido #', 'my-account-enhanced') . $order->get_order_number(); ?></div>
                                <div class="mam-order-meta">
                                    <?php echo esc_html(wc_format_datetime($order->get_date_created())) . ' • ' . $items_text . ' • ' . wp_kses_post($order->get_formatted_order_total()); ?>
                                </div>
                            </div>
                            <div>
                                <span class="mam-order-status <?php echo esc_attr($status_class); ?>" role="status">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                                <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="mam-button mam-button-outline" aria-label="<?php echo esc_attr(sprintf(__('Ver detalles del pedido #%s', 'my-account-enhanced'), $order->get_order_number())); ?>">
                                    <?php echo esc_html__('Ver', 'my-account-enhanced'); ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="mam-mt-20">
                            <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="mam-button mam-button-outline">
                                <?php echo esc_html__('Ver todos los pedidos', 'my-account-enhanced'); ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="mam-empty-message">
                            <?php echo esc_html__('No tienes pedidos recientes.', 'my-account-enhanced'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Resumen de actividad -->
            <div class="mam-card mam-card-full">
                <div class="mam-card-content">
                    <div class="mam-activity-summary">
                        <div class="mam-activity-item">
                            <p class="mam-activity-number"><?php echo esc_html($total_orders); ?></p>
                            <p class="mam-activity-label"><?php echo esc_html__('Pedidos totales', 'my-account-enhanced'); ?></p>
                        </div>
                        <div class="mam-activity-item">
                            <p class="mam-activity-number"><?php echo esc_html($processing_orders); ?></p>
                            <p class="mam-activity-label"><?php echo esc_html__('En camino', 'my-account-enhanced'); ?></p>
                        </div>
                        <div class="mam-activity-item">
                            <p class="mam-activity-number"><?php echo esc_html($completed_orders); ?></p>
                            <p class="mam-activity-label"><?php echo esc_html__('Completados', 'my-account-enhanced'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información de empresa (condicional) -->
            <?php if (!empty($company_data['name'])) : ?>
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Información de Empresa', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Empresa:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value"><?php echo esc_html($company_data['name']); ?></div>
                    </div>
                    
                    <?php if (!empty($company_data['cuit'])) : ?>
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('CUIT:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value"><?php echo esc_html($company_data['cuit']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php
                    // Mostrar dirección de facturación como información empresarial
                    $billing_address = wc_get_account_formatted_address('billing');
                    if (!empty($billing_address)) :
                    ?>
                    <div class="mam-info-row">
                        <div class="mam-info-label"><?php echo esc_html__('Dirección:', 'my-account-enhanced'); ?></div>
                        <div class="mam-info-value mam-address"><?php echo wp_kses_post($billing_address); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Mostrar campos personalizados adicionales de empresa
                    foreach ($custom_fields as $field_id => $field) {
                        if ($field['section'] === 'billing' && isset($field['enabled']) && $field['enabled']) {
                            $value = get_user_meta($user_id, $field_id, true);
                            if (!empty($value) && $field_id !== 'billing_company' && $field_id !== 'billing_cuit') {
                                echo '<div class="mam-info-row">';
                                echo '<div class="mam-info-label">' . esc_html($field['label']) . ':</div>';
                                echo '<div class="mam-info-value">' . esc_html($value) . '</div>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                    
                    <div class="mam-mt-20">
                        <a href="<?php echo esc_url(wc_get_endpoint_url('company')); ?>" class="mam-button mam-button-primary">
                            <?php echo esc_html__('Ver información de empresa', 'my-account-enhanced'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <!-- Lista de deseos (si hay plugin compatible instalado) -->
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Lista de deseos', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <?php if ($has_wishlist_plugin && !empty($wishlist_items)) : ?>
                        <div class="mam-wishlist-items">
                            <?php 
                            $count = 0;
                            foreach ($wishlist_items as $item) : 
                                // Lógica específica según el plugin
                                $product_id = 0;
                                
                                if (function_exists('YITH_WCWL')) {
                                    $product_id = isset($item['prod_id']) ? $item['prod_id'] : 0;
                                } elseif (function_exists('tinv_get_wishlist_products')) {
                                    $product_id = isset($item->get_product_id) ? $item->get_product_id() : 0;
                                }
                                
                                if (!$product_id) continue;
                                
                                $product = wc_get_product($product_id);
                                if (!$product) continue;
                                
                                $count++;
                                if ($count > 3) break; // Mostrar solo los primeros 3 elementos
                            ?>
                            <div class="mam-wishlist-item">
                                <div class="mam-wishlist-item-name"><?php echo esc_html($product->get_name()); ?></div>
                                <div class="mam-wishlist-item-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="mam-mt-20">
                                <a href="<?php echo esc_url($wishlist_url); ?>" class="mam-button mam-button-outline">
                                    <?php echo esc_html__('Ver lista completa', 'my-account-enhanced'); ?>
                                </a>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="mam-empty-message">
                            <?php echo esc_html__('No tienes productos en tu lista de deseos', 'my-account-enhanced'); ?>
                            <?php if (!$has_wishlist_plugin) : ?>
                                <p class="mam-info-small"><?php echo esc_html__('La funcionalidad de lista de deseos no está habilitada', 'my-account-enhanced'); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Enlaces rápidos -->
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Enlaces rápidos', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <div class="mam-quick-links">
                        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?php echo esc_html__('Mis direcciones', 'my-account-enhanced'); ?>
                        </a>
                        
                        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            <?php echo esc_html__('Ir a la tienda', 'my-account-enhanced'); ?>
                        </a>
                        
                        <?php 
                        // Verificar si WooCommerce Subscriptions está activo y el usuario tiene suscripciones
                        if (class_exists('WC_Subscriptions') && function_exists('wcs_user_has_subscription') && wcs_user_has_subscription($user_id)) : 
                        ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('subscriptions')); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 v.01"></path><path d="M19.071 4.929c-7.05-7.05-18.585 4.485-11.535 11.535 7.05 7.05 18.585-4.485 11.535-11.535z"></path></svg>
                            <?php echo esc_html__('Mis suscripciones', 'my-account-enhanced'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php 
                        // Verificar si el usuario tiene descargas disponibles
                        if (wc_get_customer_available_downloads($user_id)) : 
                        ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('downloads')); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            <?php echo esc_html__('Descargas', 'my-account-enhanced'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php 
                        // Enlace de cerrar sesión
                        ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('customer-logout', '', wc_get_page_permalink('myaccount'))); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <?php echo esc_html__('Cerrar sesión', 'my-account-enhanced'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php 
        // Hook para que otros plugins/temas puedan añadir contenido
        do_action('mam_after_dashboard_content'); 
        ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que todos los enlaces de navegación funcionen como enlaces directos
    const navLinks = document.querySelectorAll('.mam-nav-menu a.direct-link');
    navLinks.forEach(link => {
        // Eliminar cualquier evento de clic existente
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);
        
        // Asegurar que el enlace funcione normalmente sin interceptación
        newLink.addEventListener('click', function(e) {
    // Gestión de navegación en móvil
    const setupMobileNav = () => {
        // Solo añadir botón de navegación en móvil si no existe
        if (window.innerWidth <= 768 && !document.querySelector('.mam-sidebar-toggle')) {
            const navToggle = document.createElement('button');
            navToggle.className = 'mam-sidebar-toggle';
            navToggle.setAttribute('aria-label', '<?php echo esc_js(__('Abrir menú de navegación', 'my-account-enhanced')); ?>');
            navToggle.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
            
            const sidebar = document.querySelector('.mam-sidebar');
            if (sidebar) {
                sidebar.insertBefore(navToggle, sidebar.firstChild);
                
                navToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    
                    // Actualizar el texto del aria-label según el estado
                    const expanded = sidebar.classList.contains('open');
                    this.setAttribute('aria-label', expanded ? 
                        '<?php echo esc_js(__('Cerrar menú de navegación', 'my-account-enhanced')); ?>' : 
                        '<?php echo esc_js(__('Abrir menú de navegación', 'my-account-enhanced')); ?>'
                    );
                    this.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                });
            }
        }
        
        // Configurar comportamiento de enlaces en móvil
      const menuLinks = document.querySelectorAll('.mam-nav-menu a');
        if (window.innerWidth <= 768) {
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    // Sólo cerrar el menú, pero permitir que el navegador siga el enlace
                    const sidebar = document.querySelector('.mam-sidebar');
                    if (sidebar && sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                    }
                    // NO usar preventDefault() aquí
                });
            });
        }
    };
    
    // Optimizar el rendimiento para móviles - cargar imágenes bajo demanda
    const optimizeForMobile = () => {
        if (window.innerWidth <= 768) {
            // Aplazar carga de imágenes fuera de la vista
            const lazyLoadImages = document.querySelectorAll('.mam-lazy-load');
            lazyLoadImages.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
            });
        }
    };
    
    // Inicializar
    setupMobileNav();
    optimizeForMobile();
    
    // Volver a ejecutar al cambiar el tamaño de la ventana
    window.addEventListener('resize', function() {
        setupMobileNav();
        optimizeForMobile();
    });
    
    // Añadir clase para indicar que JavaScript está disponible
    document.body.classList.add('mam-js-enabled');
});
</script>
