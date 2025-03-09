<?php
/**
 * Clase para manejar la administración del plugin (versión simplificada)
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar la administración
 */
class MAM_Admin {
    
    /**
     * Instancia de MAM_Field_Manager
     *
     * @var MAM_Field_Manager
     */
    private $field_manager;
    
    /**
     * Constructor
     *
     * @param MAM_Field_Manager $field_manager Instancia del gestor de campos
     */
    public function __construct($field_manager) {
        $this->field_manager = $field_manager;
        
        // Añadir menú de administración
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Añadir menú de administración
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Mi Cuenta Mejorado', 'my-account-enhanced'),
            __('Mi Cuenta Mejorado', 'my-account-enhanced'),
            'manage_woocommerce',
            'my-account-enhanced',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Renderizar página de administración
     */
    public function render_admin_page() {
        // Versión simplificada para depuración
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Mi Cuenta Mejorado - Configuración', 'my-account-enhanced'); ?></h1>
            
            <div class="notice notice-info">
                <p><?php echo esc_html__('Versión en desarrollo. Estamos trabajando para completar todas las funcionalidades.', 'my-account-enhanced'); ?></p>
            </div>
            
            <h2 class="nav-tab-wrapper">
                <a href="#fields" class="nav-tab nav-tab-active"><?php echo esc_html__('Campos', 'my-account-enhanced'); ?></a>
                <a href="#sections" class="nav-tab"><?php echo esc_html__('Secciones', 'my-account-enhanced'); ?></a>
                <a href="#brands" class="nav-tab"><?php echo esc_html__('Panel de Marcas', 'my-account-enhanced'); ?></a>
                <a href="#appearance" class="nav-tab"><?php echo esc_html__('Apariencia', 'my-account-enhanced'); ?></a>
            </h2>
            
            <div id="fields" class="tab-content">
                <p class="description"><?php echo esc_html__('La configuración completa estará disponible pronto.', 'my-account-enhanced'); ?></p>
            </div>
        </div>
        <?php
    }
}
