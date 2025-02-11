document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rfqking-form');

    if (form) {
        form.addEventListener('submit', function (e) {
            // Validar campos obligatorios
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });

            // Validar cantidad (número positivo)
            const quantityField = form.querySelector('input[name="quantity"]');
            if (quantityField && (isNaN(quantityField.value) || parseInt(quantityField.value) <= 0)) {
                isValid = false;
                quantityField.style.borderColor = 'red';
                alert('La cantidad debe ser un número positivo.');
            }

            // Validar archivos adjuntos
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5 MB

                for (const file of fileInput.files) {
                    if (!allowedTypes.includes(file.type) || file.size > maxSize) {
                        alert(`Archivo no válido: ${file.name}. Solo se permiten PDF, JPG y PNG, máximo 5 MB.`);
                        e.preventDefault();
                        return;
                    }
                }
            }

            // Evitar envío si hay errores
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios.');
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const deadlineInput = document.getElementById('deadline');

    if (deadlineInput) {
        // Obtener la fecha actual
        const today = new Date();
        const maxDate = new Date();
        maxDate.setDate(today.getDate() + 30); // Añadir 30 días

        // Formatear las fechas para el input de tipo "date"
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        // Establecer los atributos min y max
        deadlineInput.setAttribute('min', formatDate(today));
        deadlineInput.setAttribute('max', formatDate(maxDate));
    }
});