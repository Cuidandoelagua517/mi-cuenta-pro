<?php
/**
 * Template personalizado para la página de Mi Cuenta
 * Incorporando el mensaje estándar de WooCommerce sobre política de privacidad
 *
 * @package Mi_Cuenta_Mejorado
 */

if (!defined('WPINC')) {
    die;
}

// Mostrar errores si existen
wc_print_notices();
?>

<style>
/* Nueva estructura para campos con iconos */
.input-container {
  display: flex;
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  background-color: #f9f9f9;
  border-right: 1px solid #eee;
  padding: 8px;
}

.input-field-container {
  flex: 1;
}

.input-field-container input {
  width: 100%;
  padding: 10px;
  border: none;
  outline: none;
  box-sizing: border-box;
}

/* Estilo para focus */
.input-field-container input:focus {
  background-color: #f0f7ff;
}

/* Mantener estilos existentes compatibles */
.mam-form-row-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.mam-form-col {
  display: flex;
  flex-direction: column;
}

.mam-form-row {
  margin-bottom: 20px;
}
</style>

<div class="mam-account-container">
    <!-- FORMULARIO DE REGISTRO (Vista principal) -->
    <div class="mam-form-container" id="register-form">
        <div class="mam-form-card">
            <div class="mam-form-header">
                <h2 class="mam-form-title"><?php echo esc_html__('Crear Cuenta', 'my-account-enhanced'); ?></h2>
                <p class="mam-form-subtitle"><?php echo esc_html__('Comienza tu journey con nosotros', 'my-account-enhanced'); ?></p>
            </div>

            <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?> >

                <?php do_action('woocommerce_register_form_start'); ?>

                <!-- BLOQUE 1: CAMPOS OBLIGATORIOS -->
                <div class="mam-form-row-group">
                    <!-- Correo Electrónico -->
                    <div class="mam-form-col">
                        <label for="reg_email"><?php esc_html_e('Correo electrónico', 'my-account-enhanced'); ?> <span class="required">*</span></label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" 
       name="email" id="reg_email" autocomplete="email" 
       value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" 
       placeholder="<?php esc_html_e('tu.correo@ejemplo.com', 'my-account-enhanced'); ?>"
       required />
                            </div>
                        </div>
                        <p class="mam-field-hint"><?php esc_html_e('Se enviará un enlace a tu dirección de correo electrónico.', 'my-account-enhanced'); ?></p>
                    </div>

                    <!-- Campo de Empresa -->
                    <div class="mam-form-col">
                        <label for="billing_company">
                            <?php esc_html_e('Nombre de empresa', 'my-account-enhanced'); ?>
                            <span class="required">*</span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                              <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
       name="billing_company" id="billing_company" autocomplete="organization" 
       value="<?php echo (!empty($_POST['billing_company'])) ? esc_attr(wp_unslash($_POST['billing_company'])) : ''; ?>" 
       placeholder="<?php esc_html_e('Nombre de tu empresa', 'my-account-enhanced'); ?>" 
       required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUIT (obligatorio) -->
                <div class="mam-form-row-group mam-obligatory-section">
                    <div class="mam-form-col">
                        <label for="billing_cuit">
                            <?php esc_html_e('CUIT', 'my-account-enhanced'); ?>
                            <span class="required">*</span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
       name="billing_cuit" id="billing_cuit" 
       value="<?php echo (!empty($_POST['billing_cuit'])) ? esc_attr(wp_unslash($_POST['billing_cuit'])) : ''; ?>" 
       placeholder="<?php esc_html_e('XX-XXXXXXXX-X', 'my-account-enhanced'); ?>" 
       required data-validate="cuit" />
                            </div>
                        </div>
                        <p class="mam-field-hint"><?php esc_html_e('Formato: 30-12345678-9', 'my-account-enhanced'); ?></p>
                    </div>

                    <!-- Teléfono (opcional) -->
                    <div class="mam-form-col">
                        <label for="billing_phone">
                            <?php esc_html_e('Teléfono', 'my-account-enhanced'); ?>
                            <span class="optional"><?php esc_html_e('(opcional)', 'my-account-enhanced'); ?></span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" autocomplete="tel" value="<?php echo (!empty($_POST['billing_phone'])) ? esc_attr(wp_unslash($_POST['billing_phone'])) : ''; ?>" placeholder="<?php esc_html_e('Tu número de contacto', 'my-account-enhanced'); ?>" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BLOQUE 2: CAMPOS OPCIONALES -->
                <div class="mam-form-row-group">
                    <!-- Nombre (opcional) -->
                    <div class="mam-form-col">
                        <label for="billing_first_name">
                            <?php esc_html_e('Nombre', 'my-account-enhanced'); ?>
                            <span class="optional"><?php esc_html_e('(opcional)', 'my-account-enhanced'); ?></span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" 
       name="billing_first_name" id="billing_first_name" autocomplete="given-name"
       value="<?php echo (!empty($_POST['billing_first_name'])) ? esc_attr(wp_unslash($_POST['billing_first_name'])) : ''; ?>" 
       placeholder="<?php esc_html_e('Tu nombre', 'my-account-enhanced'); ?>" />
                            </div>
                        </div>
                    </div>

                    <!-- Apellido (opcional) -->
                    <div class="mam-form-col">
                        <label for="billing_last_name">
                            <?php esc_html_e('Apellido', 'my-account-enhanced'); ?>
                            <span class="optional"><?php esc_html_e('(opcional)', 'my-account-enhanced'); ?></span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_last_name" id="billing_last_name" autocomplete="family-name" value="<?php echo (!empty($_POST['billing_last_name'])) ? esc_attr(wp_unslash($_POST['billing_last_name'])) : ''; ?>" placeholder="<?php esc_html_e('Tu apellido', 'my-account-enhanced'); ?>" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fecha de cumpleaños (opcional) -->
                <div class="mam-form-row">
                    <label for="customer_birthday">
                        <?php esc_html_e('Fecha de cumpleaños', 'my-account-enhanced'); ?>
                        <span class="optional"><?php esc_html_e('(opcional)', 'my-account-enhanced'); ?></span>
                    </label>
                    <div class="input-container">
                        <div class="icon-container">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="input-field-container">
                            <input type="date" class="woocommerce-Input woocommerce-Input--text input-text" name="customer_birthday" id="customer_birthday" value="<?php echo (!empty($_POST['customer_birthday'])) ? esc_attr(wp_unslash($_POST['customer_birthday'])) : ''; ?>" placeholder="<?php esc_html_e('dd/mm/aaaa', 'my-account-enhanced'); ?>" />
                        </div>
                    </div>
                </div>

                <!-- Campo para contraseña (requerido por WooCommerce) -->
                <?php if ('yes' === get_option('woocommerce_registration_generate_password')) : ?>
                    <input type="hidden" name="password" value="<?php echo esc_attr(wp_generate_password()); ?>" />
                <?php else : ?>
                    <div class="mam-form-row">
                        <label for="reg_password">
                            <?php esc_html_e('Contraseña', 'my-account-enhanced'); ?>
                            <span class="required">*</span>
                        </label>
                        <div class="input-container">
                            <div class="icon-container">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="input-field-container">
                                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required />
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Resto del formulario sigue igual... -->
                
                <!-- Incluir otros campos de WooCommerce sin duplicar los de MAM_Registration -->
                <?php 
                // Guardar los callbacks actuales
                $registered_callbacks = array();
                global $wp_filter;
                if (isset($wp_filter['woocommerce_register_form'])) {
                    $registered_callbacks = $wp_filter['woocommerce_register_form'];
                }

                // Remover temporalmente todas las acciones
                remove_all_actions('woocommerce_register_form');

                // Volver a añadir solo los callbacks que no pertenecen a MAM_Registration
                if (!empty($registered_callbacks)) {
                    foreach ($registered_callbacks as $priority => $callbacks) {
                        foreach ($callbacks as $callback_id => $callback_data) {
                            // Saltar el callback que añade campos personalizados (el que genera duplicados)
                            if (is_array($callback_data['function']) && 
                                is_object($callback_data['function'][0]) && 
                                get_class($callback_data['function'][0]) === 'MAM_Registration') {
                                continue;
                            }
                            
                            // Volver a añadir el resto de callbacks
                            add_action('woocommerce_register_form', $callback_data['function'], $priority, $callback_data['accepted_args']);
                        }
                    }
                }

                // Ejecutar la acción filtrada
                do_action('woocommerce_register_form');
                ?>

                <!-- Política de privacidad y resto del formulario... -->
                <div class="mam-form-row mam-privacy-policy-text">
                    <p><?php esc_html_e('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our', 'woocommerce'); ?> 
                    <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" class="woocommerce-privacy-policy-link" target="_blank"><?php esc_html_e('privacy policy', 'woocommerce'); ?></a>.</p>
                </div>

                <!-- Checkbox de política de privacidad -->
                <div class="mam-form-row mam-privacy-row">
                    <div class="mam-privacy-checkbox">
                        <input type="checkbox" id="privacy_policy" name="privacy_policy" required class="woocommerce-form__input woocommerce-form__input-checkbox">
                        <label for="privacy_policy" class="woocommerce-form__label">
                            <?php esc_html_e('Acepto los términos de privacidad', 'my-account-enhanced'); ?>
                            <span class="optional"><?php esc_html_e('(opcional)', 'my-account-enhanced'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Botón de registro -->
                <div class="mam-form-row">
                    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                    <button type="submit" class="mam-submit-button" name="register" value="<?php esc_attr_e('Registrarme', 'my-account-enhanced'); ?>">
                        <?php esc_html_e('Registrarme', 'my-account-enhanced'); ?>
                    </button>
                </div>

                <!-- Enlace para iniciar sesión -->
                <div class="mam-form-row mam-form-login-link">
                    <p>
                        <?php esc_html_e('¿Ya tienes cuenta?', 'my-account-enhanced'); ?>
                        <a href="#login-modal" class="mam-login-trigger">
                            <?php esc_html_e('Iniciar Sesión', 'my-account-enhanced'); ?>
                        </a>
                    </p>
                </div>

                <?php do_action('woocommerce_register_form_end'); ?>
            </form>
        </div>
    </div>

    <!-- MODAL DE INICIO DE SESIÓN (también actualizado con la nueva estructura) -->
    <div id="mam-login-modal" class="mam-modal">
        <div class="mam-modal-content">
            <span class="mam-modal-close">&times;</span>
            <div class="mam-form-header">
                <h2 class="mam-form-title"><?php echo esc_html__('Iniciar sesión', 'my-account-enhanced'); ?></h2>
                <p class="mam-form-subtitle"><?php echo esc_html__('Accede a tu cuenta', 'my-account-enhanced'); ?></p>
            </div>

          <form class="woocommerce-form woocommerce-form-login login" method="post">
    <?php do_action('woocommerce_login_form_start'); ?>
    
    <!-- Campo oculto necesario para WooCommerce -->
    <input type="hidden" name="woocommerce-login" value="1" />

    <!-- Usuario o correo electrónico -->
    <div class="mam-form-row">
        <label for="username"><?php esc_html_e('Nombre de usuario o correo electrónico', 'my-account-enhanced'); ?> <span class="required">*</span></label>
        <div class="input-container">
            <div class="icon-container">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="input-field-container">
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" placeholder="<?php esc_html_e('Email o nombre de usuario', 'my-account-enhanced'); ?>" required />
            </div>
        </div>
    </div>

    <!-- Contraseña -->
    <div class="mam-form-row">
        <label for="password"><?php esc_html_e('Contraseña', 'my-account-enhanced'); ?> <span class="required">*</span></label>
        <div class="input-container">
            <div class="icon-container">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div class="input-field-container">
                <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required />
            </div>
        </div>
    </div>

    <!-- Casilla "Recuérdame" con clases consistentes -->
    <div class="mam-form-row" style="margin-bottom: 10px;">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox">
            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
            <span><?php esc_html_e('Recuérdame', 'my-account-enhanced'); ?></span>
        </label>
    </div>

    <?php do_action('woocommerce_login_form'); ?>

    <!-- Botón de inicio de sesión con clases consistentes -->
    <div class="mam-form-row">
        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
        <button type="submit" class="mam-submit-button" name="login" value="<?php esc_attr_e('Iniciar sesión', 'my-account-enhanced'); ?>">
            <?php esc_html_e('Iniciar sesión', 'my-account-enhanced'); ?>
        </button>
    </div>

    <!-- Enlace para recuperar contraseña -->
    <div class="mam-form-row mam-form-login-link" style="text-align: center; margin-top: 15px;">
        <p>
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                <?php esc_html_e('¿Olvidaste tu contraseña?', 'my-account-enhanced'); ?>
            </a>
        </p>
    </div>

    <?php do_action('woocommerce_login_form_end'); ?>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias al modal de login
    var modal = document.getElementById('mam-login-modal');
    var triggers = document.querySelectorAll('.mam-login-trigger');
    var closeBtn = document.querySelector('.mam-modal-close');
    
    // Abrir modal al hacer clic en el enlace de login
    triggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'block';
            // Hacer aparecer con transición
            setTimeout(function() {
                modal.classList.add('mam-modal-open');
            }, 10);
        });
    });
    
    // Cerrar modal al hacer clic en la X
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.classList.remove('mam-modal-open');
            // Esperar a que termine la transición para ocultar
            setTimeout(function() {
                modal.style.display = 'none';
            }, 300);
        });
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('mam-modal-open');
            // Esperar a que termine la transición para ocultar
            setTimeout(function() {
                modal.style.display = 'none';
            }, 300);
        }
    });
});
</script>
