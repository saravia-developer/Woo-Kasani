jQuery(document).ready(function ($) {
    // Variable para evitar bucles de eventos
    var updating = false;

    // Usamos delegación de eventos para que funcione con toggles añadidos dinámicamente (paginación, etc.)
    $(document).on('change', '.toggle-promotion', function () {
        // Si ya estamos actualizando, salir para evitar bucles
        if (updating) {
            return;
        }

        var checkbox = $(this);
        var post_id = checkbox.data('post-id');
        var nonce = checkbox.data('nonce');
        var is_checked = checkbox.prop('checked') ? 1 : 0;

        // Deshabilitar el toggle mientras se procesa
        checkbox.prop('disabled', true);

        $.ajax({
            url: ajaxurl, // o wooKasaniToggle.ajax_url si lo tienes definido
            type: 'POST',
            data: {
                action: 'toggle_promotion',
                post_id: post_id,
                active: is_checked,
                nonce: nonce,
            },
            success: function (response) {
                if (response.success) {
                    // Si se activó (is_checked === 1), desmarcar todos los demás toggles
                    if (is_checked === 1) {
                        // Bloquear eventos para que no se disparen otros cambios al desmarcar
                        updating = true;

                        // Seleccionar todos los toggles que NO tengan el mismo post_id
                        $('.toggle-promotion').not('[data-post-id="' + post_id + '"]').each(function () {
                            $(this).prop('checked', false);
                        });

                        // Desbloquear
                        updating = false;
                    }
                    console.log('Actualizado correctamente');
                } else {
                    // Revertir si hubo error
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    alert('Error al actualizar la promoción');
                }
                checkbox.prop('disabled', false);
            },
            error: function () {
                checkbox.prop('checked', !checkbox.prop('checked'));
                checkbox.prop('disabled', false);
                alert('Error de conexión');
            }
        });
    });
});