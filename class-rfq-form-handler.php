<?php
if (!class_exists('RFQ_Form_Handler')) {
    class RFQ_Form_Handler {
        public function __construct() {
            add_action('template_redirect', array($this, 'process_rfq_submission'));
        }

        public function process_rfq_submission() {
            if (isset($_POST['submit_rfq'])) {
                // Validar nonce (seguridad)
                if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'rfqking_submit')) {
                    wp_die(__('Error de seguridad: intento no autorizado.', 'rfqking'));
                }

                // Recoger datos del formulario
                $product_name = sanitize_text_field($_POST['product_name']);
                $quantity = intval($_POST['quantity']);
                $quantity_unit = sanitize_text_field($_POST['quantity_unit']); 
                $category = sanitize_text_field($_POST['category']);
                $description = sanitize_textarea_field($_POST['description']); 
                $price_range = sanitize_text_field($_POST['price_range']);
                $deadline = sanitize_text_field($_POST['deadline']);
                $customer_id = get_current_user_id();

                // Validar campos obligatorios
                if (empty($product_name) || empty($quantity) || empty($quantity_unit) || empty($category) || empty($deadline)) {
                    add_action('admin_notices', function () {
                        echo '<div class="error"><p>' . __('Todos los campos obligatorios deben ser completados.', 'rfqking') . '</p></div>';
                    });
                    return;
                }

                // Validar archivos adjuntos
                $attachments = isset($_FILES['attachments']) ? $_FILES['attachments'] : [];
                $attachment_ids = [];
                if (!empty($attachments['name'][0])) {
                    foreach ($attachments['name'] as $key => $name) {
                        if ($attachments['error'][$key] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $attachments['name'][$key],
                                'type' => $attachments['type'][$key],
                                'tmp_name' => $attachments['tmp_name'][$key],
                                'error' => $attachments['error'][$key],
                                'size' => $attachments['size'][$key],
                            ];

                            // Validar tipo y tamaño
                            $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
                            $max_size = 5 * 1024 * 1024; // 5 MB
                            if (!in_array($file['type'], $allowed_types) || $file['size'] > $max_size) {
                                add_action('admin_notices', function () use ($name) {
                                    echo '<div class="error"><p>' . sprintf(__('Archivo no válido: %s.', 'rfqking'), esc_html($name)) . '</p></div>';
                                });
                                return;
                            }

                            // Subir archivo
                            $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));
                            if (!$upload['error']) {
                                $attachment_ids[] = $upload['url'];
                            } else {
                                add_action('admin_notices', function () use ($upload) {
                                    echo '<div class="error"><p>' . esc_html($upload['error']) . '</p></div>';
                                });
                                return;
                            }
                            if ($upload['error']) {
                                add_action('admin_notices', function () use ($upload) {
                                    echo '<div class="error"><p>' . esc_html($upload['error']) . '</p></div>';
                                });
                                return; // Detener el proceso si hay un error
                            }
                        }
                    }
                }

                // Guardar datos en la base de datos
                global $wpdb;
                $table_name = $wpdb->prefix . 'rfqking_requests';
                $data = [
                    'customer_id' => get_current_user_id(),
                    'product_name' => $product_name,
                    'quantity' => $quantity,
                    'quantity_unit' => $quantity_unit,
                    'category' => $category,
                    'description' => $description,
                    'price_range' => $price_range,
                    'deadline' => $deadline,
                    'status' => 'pending',
                    'attachments' => json_encode($attachment_ids),
                    'created_at' => current_time('mysql'),
                ];
                $wpdb->insert($table_name, $data);

                // Enviar notificación al administrador
                $notifications = new RFQ_Notifications();
                $subject = __('Nueva solicitud de cotización recibida', 'rfqking');
                $message = sprintf(
                    __('Se ha recibido una nueva solicitud de cotización para el producto: %s.', 'rfqking'),
                    $product_name
                );
                $notifications->send_notification(get_option('admin_email'), $subject, $message);

                // Mostrar mensaje de éxito
                add_action('admin_notices', function () {
                    echo '<div class="updated"><p>' . __('Tu solicitud de cotización ha sido enviada exitosamente.', 'rfqking') . '</p></div>';
                });
            }
        }
    }
}