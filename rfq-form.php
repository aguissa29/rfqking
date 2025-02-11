<?php
/**
 * Template para el formulario de RFQ.
 */
?>
<div class="rfqking-form-wrapper">
    <h2><?php echo esc_html__('Solicitar Cotización', 'rfqking'); ?></h2>
    <form method="post" enctype="multipart/form-data" id="rfqking-form">
        <!-- Nombre del producto -->
        <div class="rfqking-field">
            <label for="product_name"><?php echo esc_html__('Nombre del producto', 'rfqking'); ?>:</label>
            <input type="text" name="product_name" id="product_name" required>
        </div>

        <!-- Cantidad -->
        <div class="rfqking-field">
    <label for="quantity"><?php echo esc_html__('Cantidad', 'rfqking'); ?> <span class="required">*</span></label>
    <div style="display: flex; gap: 10px;">
        <input type="number" name="quantity" id="quantity" min="1" required style="flex: 1;">
        <select name="quantity_unit" id="quantity_unit" style="flex: 1;" required>
            <option value=""><?php echo esc_html__('Selecciona un tipo', 'rfqking'); ?></option>
            <?php
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_units';
            $units = $wpdb->get_results("SELECT * FROM $table_name ORDER BY unit_name ASC");

            if (!empty($units)) {
                foreach ($units as $unit) {
                    echo '<option value="' . esc_attr($unit->unit_name) . '">' . esc_html($unit->unit_name) . '</option>';
                }
            }
            ?>
        </select>
    </div>
</form>

        <!-- Categoría -->
        <div class="rfqking-field">
            <label for="category"><?php echo esc_html__('Categoría', 'rfqking'); ?></label>
            <select name="category" id="category">
                <option value=""><?php echo esc_html__('Selecciona una categoría', 'rfqking'); ?></option>
            <?php
        // Obtener todas las categorías de productos de WooCommerce
             $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false, // Mostrar categorías incluso si no tienen productos
            ));

            if (!is_wp_error($categories) && !empty($categories)) {
                foreach ($categories as $category) {
                    // Omitir la categoría "Uncategorized"
                    if ($category->slug === 'uncategorized') {
                        continue;
                    }
                    echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                }
            }
            ?>
            </select>
        </div>

        <!-- Detalles -->
        <div class="rfqking-field">
            <label for="description"><?php echo esc_html__('Descripción del producto', 'rfqking'); ?></label>
            <textarea name="description" id="description" rows="4" placeholder="<?php echo esc_attr__('Escribe detalles adicionales sobre el producto...', 'rfqking'); ?>"></textarea>
        </div>

        <!-- Rango de precio -->
        <div class="rfqking-field">
            <label for="price_range"><?php echo esc_html__('Rango de precio esperado', 'rfqking'); ?>:</label>
            <input type="text" name="price_range" id="price_range" placeholder="<?php echo esc_attr__('Ejemplo: $100 - $500', 'rfqking'); ?>">
        </div>

        <!-- Fecha límite -->
        <div class="rfqking-field">
            <label for="deadline"><?php echo esc_html__('Fecha límite', 'rfqking'); ?><span class="required">*</span></label>
            <input type="date" name="deadline" id="deadline" required>
        </div>

        <!-- Archivos adjuntos -->
        <div class="rfqking-field">
            <label for="attachments"><?php echo esc_html__('Archivos adjuntos (opcional)', 'rfqking'); ?>:</label>
            <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf,.jpg,.png">
            <small><?php echo esc_html__('Tipos permitidos: PDF, JPG, PNG. Máximo 5 MB por archivo.', 'rfqking'); ?></small>
        </div>

        <!-- Botón de envío -->
        <div class="rfqking-field">
            <button type="submit" name="submit_rfq"><?php echo esc_html__('Enviar solicitud', 'rfqking'); ?></button>
        </div>
    </form>
</div>