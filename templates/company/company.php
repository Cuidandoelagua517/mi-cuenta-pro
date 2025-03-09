<?php
/**
 * Template para la sección de empresa
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}
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
            <?php 
            $countries = WC()->countries->get_countries();
            echo esc_html($countries[$company_data['country']]); 
            ?>
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
        $custom_fields = get_option('mam_custom_fields', array());
        
        foreach ($company_data as $key => $value) {
            if (strpos($key, 'mam_custom_') === 0 && !empty($value)) {
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
    $company_orders = wc_get_orders(array(
        'meta_key' => '_billing_company',
        'meta_value' => $company_data['name'],
        'limit' => -1
    ));
    
    if (!empty($company_orders)) {
        // Formatear datos para la plantilla de WooCommerce
        $customer_orders = array(
            'orders' => $company_orders,
            'count' => count($company_orders)
        );
        
        wc_get_template(
            'myaccount/orders.php',
            array(
                'current_page' => 1,
                'customer_orders' => $customer_orders,
                'has_orders' => true
            )
        );
    } else {
        echo '<p>' . esc_html__('No hay pedidos para esta empresa.', 'my-account-enhanced') . '</p>';
    }
    ?>
    
    <h3><?php echo esc_html__('Documentos Fiscales', 'my-account-enhanced'); ?></h3>
    
    <?php
    // Si hay documentos fiscales, mostrarlos
    $fiscal_documents = apply_filters('mam_company_fiscal_documents', array(), $company_data['name']);
    
    if (!empty($fiscal_documents)) {
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
                <?php foreach ($fiscal_documents as $document) : ?>
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
    } else {
        echo '<p>' . esc_html__('No hay documentos fiscales disponibles.', 'my-account-enhanced') . '</p>';
    }
    ?>
    
    <?php 
    // Hook para que otros plugins/temas puedan añadir contenido
    do_action('mam_after_company_content'); 
    ?>
</div>
