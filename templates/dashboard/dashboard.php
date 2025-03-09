<?php
/**
 * Sección completa del avatar para templates/dashboard/dashboard.php
 */

// Obtener las iniciales del usuario para el avatar
$user_initials = '';
if (!empty($user_info['first_name']) && !empty($user_info['last_name'])) {
    // Si tiene nombre y apellido, muestra ambas iniciales
    $user_initials = strtoupper(substr($user_info['first_name'], 0, 1) . substr($user_info['last_name'], 0, 1));
} elseif (!empty($user_info['first_name'])) {
    // Si solo tiene nombre, muestra la inicial del nombre
    $user_initials = strtoupper(substr($user_info['first_name'], 0, 1));
} elseif (!empty($user_info['last_name'])) {
    // Si solo tiene apellido, muestra la inicial del apellido
    $user_initials = strtoupper(substr($user_info['last_name'], 0, 1));
} else {
    // Si no tiene ni nombre ni apellido, muestra solo la primera letra del email
    $user_initials = strtoupper(substr($user->user_email, 0, 1));
}

// HTML del avatar con las iniciales
?>
<div class="mam-user-avatar">
    <div class="mam-avatar-circle">
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

// Obtener el menú de navegación
$menu_items = wc_get_account_menu_items();
$current_endpoint = WC()->query->get_current_endpoint();
if (empty($current_endpoint)) {
    $current_endpoint = 'dashboard';
}

// Obtener estadísticas de pedidos
$total_orders = count(wc_get_orders(array(
    'customer' => $user_id,
    'limit' => -1,
    'return' => 'ids',
)));

$processing_orders = count(wc_get_orders(array(
    'customer' => $user_id,
    'status' => array('processing'),
    'limit' => -1,
    'return' => 'ids',
)));

$completed_orders = count(wc_get_orders(array(
    'customer' => $user_id,
    'status' => array('completed'),
    'limit' => -1,
    'return' => 'ids',
)));

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
        
        <ul class="mam-nav-menu">
            <?php foreach ($menu_items as $endpoint => $label) : ?>
                <li class="<?php echo $endpoint === $current_endpoint ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                        <?php echo isset($menu_icons[$endpoint]) ? $menu_icons[$endpoint] : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>'; ?>
                        <?php echo esc_html($label); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="mam-user-avatar">
            <div class="mam-avatar-circle">
                <?php echo esc_html($user_initials); ?>
            </div>
            <div class="mam-user-info">
                <p class="mam-user-name"><?php echo esc_html($user_info['first_name'] . ' ' . $user_info['last_name']); ?></p>
                <p class="mam-user-email"><?php echo esc_html($user->user_email); ?></p>
            </div>
        </div>
    </aside>
    
    <!-- Contenido principal -->
    <main class="mam-main-content">
        <!-- Encabezado del dashboard -->
        <div class="mam-dashboard-header">
            <h2 class="mam-dashboard-greeting"><?php printf(esc_html__('Hola, %s', 'my-account-enhanced'), esc_html($user->user_email)); ?></h2>
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
                        <div class="mam-info-value"><?php echo esc_html($user_info['first_name'] . ' ' . $user_info['last_name']); ?></div>
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
                        if ($field['section'] === 'account') {
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
                        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="mam-button mam-button-primary">
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
                            $status_class = '';
                            
                            if ($status == 'completed') {
                                $status_class = 'mam-status-completed';
                            } elseif ($status == 'processing') {
                                $status_class = 'mam-status-processing';
                            } else {
                                $status_class = 'mam-status-pending';
                            }
                            
                            $items_count = count($order->get_items());
                        ?>
                        <div class="mam-order-item">
                            <div>
                                <div class="mam-order-title"><?php echo esc_html__('Pedido #', 'my-account-enhanced') . $order->get_order_number(); ?></div>
                                <div class="mam-order-meta">
                                    <?php echo esc_html(wc_format_datetime($order->get_date_created())) . ' • ' . sprintf(_n('%s producto', '%s productos', $items_count, 'my-account-enhanced'), $items_count) . ' • ' . wp_kses_post($order->get_formatted_order_total()); ?>
                                </div>
                            </div>
                            <div>
                                <span class="mam-order-status <?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html(wc_get_order_status_name($status)); ?>
                                </span>
                                <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="mam-button mam-button-outline">
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
                    // Mostrar campos personalizados adicionales de empresa
                    foreach ($custom_fields as $field_id => $field) {
                        if ($field['section'] === 'billing') {
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
            <!-- Lista de deseos (o espacio alternativo si no hay empresa) -->
            <div class="mam-card">
                <div class="mam-card-header">
                    <h3 class="mam-card-title"><?php echo esc_html__('Lista de deseos', 'my-account-enhanced'); ?></h3>
                </div>
                <div class="mam-card-content">
                    <div class="mam-empty-message">
                        <?php echo esc_html__('No tienes productos en tu lista de deseos', 'my-account-enhanced'); ?>
                    </div>
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
                        
                        <?php if (class_exists('WC_Subscriptions') && wcs_user_has_subscription($user_id)) : ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('subscriptions')); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 v.01"></path><path d="M19.071 4.929c-7.05-7.05-18.585 4.485-11.535 11.535 7.05 7.05 18.585-4.485 11.535-11.535z"></path></svg>
                            <?php echo esc_html__('Mis suscripciones', 'my-account-enhanced'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (function_exists('wc_get_endpoint_url') && get_option('woocommerce_enable_myaccount_registration') === 'yes') : ?>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('customer-logout', '', wc_get_page_permalink('myaccount'))); ?>" class="mam-link-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <?php echo esc_html__('Cerrar sesión', 'my-account-enhanced'); ?>
                        </a>
                        <?php endif; ?>
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
    const sidebarToggle = document.querySelector('.mam-sidebar-toggle');
    const sidebar = document.querySelector('.mam-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
});
</script>
