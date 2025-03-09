<?php
/**
 * Template para el dashboard personalizado
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}
?>

<div class="mam-dashboard">
    <div class="mam-dashboard-header">
        <h2><?php printf(esc_html__('Hola, %s', 'my-account-enhanced'), esc_html($user->display_name)); ?></h2>
        <p><?php echo esc_html__('Bienvenido a tu cuenta. Aquí puedes gestionar tus pedidos, datos personales y más.', 'my-account-enhanced'); ?></p>
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
                
                <?php 
                // Mostrar campos personalizados adicionales
                $custom_fields = get_option('mam_custom_fields', array());
                foreach ($custom_fields as $field_id => $field) {
                    if ($field['section'] === 'account') {
                        $value = get_user_meta($user_id, $field_id, true);
                        if (!empty($value)) {
                            echo '<p>';
                            echo '<strong>' . esc_html($field['label']) . ':</strong> ';
                            echo esc_html($value);
                            echo '</p>';
                        }
                    }
                }
                ?>
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
                
                <?php 
                // Mostrar campos personalizados adicionales de empresa
                foreach ($custom_fields as $field_id => $field) {
                    if ($field['section'] === 'billing') {
                        $value = get_user_meta($user_id, $field_id, true);
                        if (!empty($value) && $field_id !== 'billing_company' && $field_id !== 'billing_cuit') {
                            echo '<p>';
                            echo '<strong>' . esc_html($field['label']) . ':</strong> ';
                            echo esc_html($value);
                            echo '</p>';
                        }
                    }
                }
                ?>
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
    
    <?php 
    // Hook para que otros plugins/temas puedan añadir contenido
    do_action('mam_after_dashboard_content'); 
    ?>
</div>
