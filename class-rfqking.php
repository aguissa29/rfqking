<?php
if (!class_exists('RFQKing')) {
    class RFQKing {
        public function __construct() {
            // Crear la tabla de unidades al inicializar la clase
            $this->create_units_table();
        }

        public function create_units_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_units'; // Nombre de la tabla
            $charset_collate = $wpdb->get_charset_collate();

            // SQL para crear la tabla
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                unit_name varchar(255) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        public function run() {
            // Registrar scripts y estilos
            add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

            // Registrar shortcodes
            add_shortcode('rfq_form', array($this, 'render_rfq_form'));
            add_shortcode('rfq_form_frontend', array($this, 'render_rfq_form_frontend'));

            // Agregar menú en el backend
            add_action('admin_menu', array($this, 'add_admin_menu'));

            // Agregar pestaña en "Mi cuenta"
            add_filter('woocommerce_account_menu_items', array($this, 'add_rfq_account_tab'));
            add_action('woocommerce_account_rfq_endpoint', array($this, 'render_rfq_account_page'));
        }

        public function enqueue_assets() {
            wp_enqueue_style('rfqking-style', RFQKING_URL . 'assets/css/style.css');
            wp_enqueue_script('rfqking-script', RFQKING_URL . 'assets/js/script.js', array('jquery'), null, true);
        }

        public function render_rfq_form() {
            ob_start();
            include RFQKING_PATH . 'templates/rfq-form.php';
            return ob_get_clean();
        }

        public function render_rfq_form_frontend() {
            if (!is_user_logged_in()) {
                return '<div class="rfqking-login-notice">' .
                       __('Debes <a href="' . wp_login_url(get_permalink()) . '">iniciar sesión</a> o <a href="' . wp_registration_url() . '">registrarte</a> para enviar una solicitud de cotización.', 'rfqking') .
                       '</div>';
            }

            ob_start();
            include RFQKING_PATH . 'templates/rfq-form.php';
            return ob_get_clean();
        }

        public function add_admin_menu() {
            add_menu_page(
                __('RFQKing', 'rfqking'),
                __('RFQKing', 'rfqking'),
                'manage_options',
                'rfqking-dashboard',
                array($this, 'render_admin_dashboard'),
                'dashicons-email',
                6
            );

            // Página de detalles (oculta en el menú)
            add_submenu_page(
                null, // Ocultar en el menú
                __('Detalles de RFQ', 'rfqking'),
                __('Detalles de RFQ', 'rfqking'),
                'manage_options',
                'rfqking-details',
                array($this, 'render_rfq_details')
            );

            // Página para gestionar unidades
            add_submenu_page(
                'rfqking-dashboard',
                __('Unidades', 'rfqking'),
                __('Unidades', 'rfqking'),
                'manage_options',
                'rfqking-units',
                array($this, 'render_admin_units')
            );
        }

        public function render_admin_dashboard() {
            include RFQKING_PATH . 'templates/rfq-dashboard.php';
        }

        public function render_rfq_details() {
            include RFQKING_PATH . 'templates/rfq-details.php';
        }

        public function add_rfq_account_tab($items) {
            $items['rfq'] = __('Solicitudes de Cotización', 'rfqking');
            return $items;
        }

        public function render_rfq_account_page() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_requests';
            $user_id = get_current_user_id();

            // Obtener parámetros de filtro
            $filter_status = isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '';
            $filter_order = isset($_GET['filter_order']) ? sanitize_text_field($_GET['filter_order']) : 'desc';

            // Construir la consulta SQL
            $query = "SELECT * FROM $table_name WHERE customer_id = %d";
            $conditions = [];
            $params = [$user_id];

            if (!empty($filter_status)) {
                $query .= " AND status = %s";
                $params[] = $filter_status;
            }

            $query .= " ORDER BY created_at " . ($filter_order === 'asc' ? 'ASC' : 'DESC');

            $rfqs = $wpdb->get_results($wpdb->prepare($query, $params));

            // Verificar si se debe mostrar el formulario
            $show_form = isset($_GET['action']) && $_GET['action'] === 'create';

            if ($show_form) {
                // Mostrar el formulario de RFQ
                echo '<h2>' . __('Crear nueva solicitud de cotización', 'rfqking') . '</h2>';
                include RFQKING_PATH . 'templates/rfq-form.php';
                echo '<a href="' . wc_get_account_endpoint_url('rfq') . '" class="button">' . __('Volver', 'rfqking') . '</a>';
            } else {
                // Mostrar el botón "Crear cotización abierta"
                echo '<div style="text-align: right; margin-bottom: 20px;">';
                echo '<a href="' . wc_get_account_endpoint_url('rfq') . '?action=create" class="button button-primary">';
                echo __('Crear cotización abierta', 'rfqking');
                echo '</a>';
                echo '</div>';

                // Mostrar el formulario de filtros
                echo '<div style="margin-bottom: 20px;">';
                echo '<form method="get" style="display: flex; gap: 10px; align-items: center;">';
                echo '<input type="hidden" name="page" value="rfq">';
                echo '<label for="filter_status">' . __('Filtrar por estado:', 'rfqking') . '</label>';
                echo '<select name="filter_status" id="filter_status">';
                echo '<option value="">' . __('Todos', 'rfqking') . '</option>';
                echo '<option value="pending" ' . selected($filter_status, 'pending', false) . '>' . __('Pendiente', 'rfqking') . '</option>';
                echo '<option value="in_review" ' . selected($filter_status, 'in_review', false) . '>' . __('En revisión', 'rfqking') . '</option>';
                echo '<option value="completed" ' . selected($filter_status, 'completed', false) . '>' . __('Completado', 'rfqking') . '</option>';
                echo '</select>';

                echo '<label for="filter_order">' . __('Ordenar por:', 'rfqking') . '</label>';
                echo '<select name="filter_order" id="filter_order">';
                echo '<option value="desc" ' . selected($filter_order, 'desc', false) . '>' . __('Más recientes primero', 'rfqking') . '</option>';
                echo '<option value="asc" ' . selected($filter_order, 'asc', false) . '>' . __('Más antiguas primero', 'rfqking') . '</option>';
                echo '</select>';

                echo '<button type="submit" class="button">' . __('Filtrar', 'rfqking') . '</button>';
                echo '</form>';
                echo '</div>';

                // Mostrar la tabla de RFQs del cliente
                echo '<h2>' . __('Tus solicitudes de cotización', 'rfqking') . '</h2>';
                include RFQKING_PATH . 'templates/rfq-customer-table.php';
            }
        }

        public function render_admin_units() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'rfqking_units';

            // Procesar la creación de una nueva unidad
            if (isset($_POST['add_unit'])) {
                $name = sanitize_text_field($_POST['unit_name']);
                $slug = sanitize_title($name);

                $wpdb->insert(
                    $table_name,
                    [
                        'unit_name' => $name,
                    ]
                );
            }

            // Procesar la eliminación de una unidad
            if (isset($_GET['delete_unit'])) {
                $unit_id = intval($_GET['delete_unit']);
                $wpdb->delete($table_name, ['id' => $unit_id]);
            }

            // Obtener todas las unidades
            $units = $wpdb->get_results("SELECT * FROM $table_name ORDER BY unit_name ASC");

            ?>
            <div class="wrap">
                <h1><?php echo esc_html__('Gestionar unidades de cantidad', 'rfqking'); ?></h1>
                <form method="post">
                    <?php wp_nonce_field('rfqking_add_unit', 'rfqking_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="unit_name"><?php echo esc_html__('Nombre de la unidad', 'rfqking'); ?></label></th>
                            <td>
                                <input type="text" name="unit_name" id="unit_name" required>
                                <button type="submit" name="add_unit" class="button button-primary"><?php echo esc_html__('Agregar unidad', 'rfqking'); ?></button>
                            </td>
                        </tr>
                    </table>
                </form>

                <h2><?php echo esc_html__('Unidades existentes', 'rfqking'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('ID', 'rfqking'); ?></th>
                            <th><?php echo esc_html__('Nombre', 'rfqking'); ?></th>
                            <th><?php echo esc_html__('Acciones', 'rfqking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($units)) : ?>
                            <?php foreach ($units as $unit) : ?>
                                <tr>
                                    <td><?php echo esc_html($unit->id); ?></td>
                                    <td><?php echo esc_html($unit->unit_name); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg('delete_unit', $unit->id)); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('¿Estás seguro de que deseas eliminar esta unidad?', 'rfqking')); ?>');"><?php echo esc_html__('Eliminar', 'rfqking'); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3"><?php echo esc_html__('No hay unidades registradas.', 'rfqking'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }
}