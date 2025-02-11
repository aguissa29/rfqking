<?php
/**
 * Template para el dashboard de RFQs.
 */
global $wpdb;
$table_name = $wpdb->prefix . 'rfqking_requests';

// Procesar cambios de estado
if (isset($_POST['update_status'])) {
    if (!isset($_POST['rfqking_nonce']) || !wp_verify_nonce($_POST['rfqking_nonce'], 'rfqking_update_status')) {
        wp_die(__('Error de seguridad: intento no autorizado.', 'rfqking'));
    }

    $rfq_id = intval($_POST['rfq_id']);
    $new_status = sanitize_text_field($_POST['status']);

    // Obtener datos del RFQ
    $rfq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rfq_id));
    if ($rfq) {
        // Actualizar estado
        $wpdb->update(
            $table_name,
            ['status' => $new_status],
            ['id' => $rfq_id]
        );

        // Enviar notificación al cliente
        $customer_email = get_userdata($rfq->customer_id)->user_email;
        $subject = __('Actualización de estado de tu solicitud de cotización', 'rfqking');
        $message = sprintf(
            __('Hola, el estado de tu solicitud de cotización para "%s" ha sido actualizado a: %s.', 'rfqking'),
            $rfq->product_name,
            $new_status
        );
        wp_mail($customer_email, $subject, $message);

        echo '<div class="updated"><p>' . __('Estado actualizado correctamente y notificación enviada.', 'rfqking') . '</p></div>';
    } else {
        echo '<div class="error"><p>' . __('Error: No se encontró la solicitud de cotización.', 'rfqking') . '</p></div>';
    }
}

// Procesar cambios de prioridad
if (isset($_POST['update_priority'])) {
    if (!isset($_POST['rfqking_nonce']) || !wp_verify_nonce($_POST['rfqking_nonce'], 'rfqking_update_priority')) {
        wp_die(__('Error de seguridad: intento no autorizado.', 'rfqking'));
    }

    $rfq_id = intval($_POST['rfq_id']);
    $new_priority = sanitize_text_field($_POST['priority']);

    $wpdb->update(
        $table_name,
        ['priority' => $new_priority],
        ['id' => $rfq_id]
    );

    echo '<div class="updated"><p>' . __('Prioridad actualizada correctamente.', 'rfqking') . '</p></div>';
}

// Aplicar filtros
$filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
$filter_priority = isset($_GET['filter_priority']) ? sanitize_text_field($_GET['filter_priority']) : '';

$query = "SELECT * FROM $table_name";
$conditions = [];

if (!empty($filter_status)) {
    $conditions[] = $wpdb->prepare("status = %s", $filter_status);
}

if (!empty($filter_priority)) {
    $conditions[] = $wpdb->prepare("priority = %s", $filter_priority);
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY created_at DESC';
$rfqs = $wpdb->get_results($query);
?>
<div class="wrap">
    <h1><?php echo esc_html__('Solicitudes de Cotización', 'rfqking'); ?></h1>

    <!-- Botón de exportación -->
    <a href="<?php echo admin_url('admin-post.php?action=export_rfq_csv'); ?>" class="button button-primary" style="margin-bottom: 20px; display: inline-block;">
        <?php echo esc_html__('Exportar a CSV', 'rfqking'); ?>
    </a>

    <!-- Formulario de filtros -->
    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="rfqking-dashboard">
        <label for="filter_status"><?php echo esc_html__('Filtrar por estado:', 'rfqking'); ?></label>
        <select name="filter_status" id="filter_status">
            <option value=""><?php echo esc_html__('Todos', 'rfqking'); ?></option>
            <option value="pending" <?php selected($_GET['filter_status'] ?? '', 'pending'); ?>><?php echo esc_html__('Pendiente', 'rfqking'); ?></option>
            <option value="in_review" <?php selected($_GET['filter_status'] ?? '', 'in_review'); ?>><?php echo esc_html__('En revisión', 'rfqking'); ?></option>
            <option value="completed" <?php selected($_GET['filter_status'] ?? '', 'completed'); ?>><?php echo esc_html__('Completado', 'rfqking'); ?></option>
        </select>

        <label for="filter_priority"><?php echo esc_html__('Filtrar por prioridad:', 'rfqking'); ?></label>
        <select name="filter_priority" id="filter_priority">
            <option value=""><?php echo esc_html__('Todas', 'rfqking'); ?></option>
            <option value="low" <?php selected($_GET['filter_priority'] ?? '', 'low'); ?>><?php echo esc_html__('Baja', 'rfqking'); ?></option>
            <option value="medium" <?php selected($_GET['filter_priority'] ?? '', 'medium'); ?>><?php echo esc_html__('Media', 'rfqking'); ?></option>
            <option value="high" <?php selected($_GET['filter_priority'] ?? '', 'high'); ?>><?php echo esc_html__('Alta', 'rfqking'); ?></option>
        </select>

        <button type="submit" class="button"><?php echo esc_html__('Filtrar', 'rfqking'); ?></button>
    </form>

    <!-- Tabla de RFQs -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__('Producto', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Cantidad', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Categoría', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Estado', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Prioridad', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Fecha límite', 'rfqking'); ?></th>
                <th><?php echo esc_html__('Acciones', 'rfqking'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rfqs)) : ?>
                <?php foreach ($rfqs as $rfq) : ?>
                    <tr>
                        <td><?php echo esc_html($rfq->product_name); ?></td>
                        <td><?php echo esc_html($rfq->quantity); ?></td>
                        <td><?php echo esc_html($rfq->category); ?></td>
                        <td>
                            <!-- Formulario para cambiar el estado -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="rfq_id" value="<?php echo esc_attr($rfq->id); ?>">
                                <?php wp_nonce_field('rfqking_update_status', 'rfqking_nonce'); ?>
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php selected($rfq->status, 'pending'); ?>><?php echo esc_html__('Pendiente', 'rfqking'); ?></option>
                                    <option value="in_review" <?php selected($rfq->status, 'in_review'); ?>><?php echo esc_html__('En revisión', 'rfqking'); ?></option>
                                    <option value="completed" <?php selected($rfq->status, 'completed'); ?>><?php echo esc_html__('Completado', 'rfqking'); ?></option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <!-- Formulario para cambiar la prioridad -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="rfq_id" value="<?php echo esc_attr($rfq->id); ?>">
                                <?php wp_nonce_field('rfqking_update_priority', 'rfqking_nonce'); ?>
                                <select name="priority" onchange="this.form.submit()">
                                    <option value="low" <?php selected($rfq->priority, 'low'); ?>><?php echo esc_html__('Baja', 'rfqking'); ?></option>
                                    <option value="medium" <?php selected($rfq->priority, 'medium'); ?>><?php echo esc_html__('Media', 'rfqking'); ?></option>
                                    <option value="high" <?php selected($rfq->priority, 'high'); ?>><?php echo esc_html__('Alta', 'rfqking'); ?></option>
                                </select>
                                <input type="hidden" name="update_priority" value="1">
                            </form>
                        </td>
                        <td><?php echo esc_html($rfq->deadline); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=rfqking-details&rfq_id=' . $rfq->id); ?>" class="button">
                                <?php echo esc_html__('Ver detalles', 'rfqking'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7"><?php echo esc_html__('No hay solicitudes de cotización.', 'rfqking'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>