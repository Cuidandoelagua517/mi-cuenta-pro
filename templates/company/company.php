<?php
/**
 * Template para la sección de empresa (versión modernizada)
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}
?>
<div class="mam-company-info">
    <!-- Vamos a mantener el contenido original pero añadiendo las clases del nuevo diseño -->
    <div class="mam-dashboard-card mam-full-width-card">
        <div class="mam-card-header">
            <h3 class="mam-card-title"><?php echo esc_html__('Información de la Empresa', 'my-account-enhanced'); ?></h3>
        </div>
        <div class="mam-card-content">
            <!-- Contenido adaptado al nuevo estilo -->
            <div class="mam-info-row">
                <div class="mam-info-label"><?php echo esc_html__('Nombre:', 'my-account-enhanced'); ?></div>
                <div class="mam-info-value"><?php echo esc_html($company_data['name']); ?></div>
            </div>
            
            <?php if (!empty($company_data['cuit'])) : ?>
            <div class="mam-info-row">
                <div class="mam-info-label"><?php echo esc_html__('CUIT:', 'my-account-enhanced'); ?></div>
                <div class="mam-info-value"><?php echo esc_html($company_data['cuit']); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- Más campos de empresa... -->
            
            <div class="mam-info-row" style="margin-top: 1rem;">
                <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'billing')); ?>" class="mam-button mam-button-primary">
                    <?php echo esc_html__('Editar información de empresa', 'my-account-enhanced'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Pedidos de la empresa -->
    <div class="mam-dashboard-card mam-full-width-card">
        <div class="mam-card-header">
            <h3 class="mam-card-title"><?php echo esc_html__('Historial de Pedidos de la Empresa', 'my-account-enhanced'); ?></h3>
        </div>
        <div class="mam-card-content">
            <!-- Implementar lista de pedidos en el nuevo estilo -->
            <?php
            // Obtener pedidos de la empresa
            $company_orders = wc_get_orders(array(
                'meta_key' => '_billing_company',
                'meta_value' => $company_data['name'],
                'limit' => -1
            ));
            
            if (!empty($company_orders)) : 
                foreach ($company_orders as $order) :
                    // Obtener datos del pedido
                    $status = $order->get_status();
                    $status_class = '';
                    
                    if ($status == 'completed') {
                        $status_class = 'mam-status-completed';
                    } elseif ($status == 'processing') {
                        $status_class = 'mam-status-processing';
                    } else {
                        $status_class = 'mam-status-pending';
                    }
                    
                    $date = $order->get_date_created()->date_i18n(get_option('date_format'));
                    $items_count = count($order->get_items());
                ?>
                <div class="mam-order-item">
                    <div>
                        <div class="mam-order-title"><?php echo esc_html__('Pedido #', 'my-account-enhanced') . $order->get_order_number(); ?></div>
                        <div class="mam-order-meta">
                            <?php echo esc_html($date) . ' • ' . sprintf(_n('%s producto', '%s productos', $items_count, 'my-account-enhanced'), $items_count) . ' • ' . wp_kses_post($order->get_formatted_order_total()); ?>
                        </div>
                    </div>
                    <div>
                        <span class="mam-order-status <?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html(wc_get_order_status_name($status)); ?>
                        </span>
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="mam-button mam-button-outline" style="margin-left: 0.5rem;">
                            <?php echo esc_html__('Ver', 'my-account-enhanced'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p><?php echo esc_html__('No hay pedidos para esta empresa.', 'my-account-enhanced'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Documentos fiscales -->
    <div class="mam-dashboard-card mam-full-width-card">
        <div class="mam-card-header">
            <h3 class="mam-card-title"><?php echo esc_html__('Documentos Fiscales', 'my-account-enhanced'); ?></h3>
        </div>
        <div class="mam-card-content">
            <!-- Contenido adaptado al nuevo estilo -->
            <?php
            $fiscal_documents = apply_filters('mam_company_fiscal_documents', array(), $company_data['name']);
            
            if (!empty($fiscal_documents)) : 
                foreach ($fiscal_documents as $document) : 
                    $doc_type = '';
                    switch ($document['type']) {
                        case 'invoice':
                            $doc_type = esc_html__('Factura', 'my-account-enhanced');
                            break;
                        case 'receipt':
                            $doc_type = esc_html__('Recibo', 'my-account-enhanced');
                            break;
                        default:
                            $doc_type = esc_html($document['type']);
                    }
                ?>
                <div class="mam-order-item">
                    <div>
                        <div class="mam-order-title"><?php echo $doc_type . ' ' . esc_html($document['number']); ?></div>
                        <div class="mam-order-meta">
                            <?php echo esc_html($document['date']) . ' • ' . wp_kses_post($document['total']); ?>
                        </div>
                    </div>
                    <div>
                        <a href="<?php echo esc_url($document['download_url']); ?>" class="mam-button mam-button-primary">
                            <?php echo esc_html__('Descargar', 'my-account-enhanced'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p><?php echo esc_html__('No hay documentos fiscales disponibles.', 'my-account-enhanced'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php 
    // Hook para que otros plugins/temas puedan añadir contenido
    do_action('mam_after_company_content'); 
    ?>
</div>
