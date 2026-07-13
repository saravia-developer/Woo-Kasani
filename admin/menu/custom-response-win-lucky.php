<?php
namespace WooKasani\Admin\Menu;

class CustomWinLucky
{

  public function __construct()
  {

  }

  public function add_menu()
  {

    add_menu_page(
      'Ganadores de ruleta',
      'Mensaje extra a ganadores de ruleta',
      'manage_options',
      'message-to-roulette-winners',
      [$this, 'template_message_to_roulette_winners'],
      'dashicons-media-code'
    );

  }

  public function template_message_to_roulette_winners()
  {
    // Verificar permisos del usuario
    if (!current_user_can('manage_options')) {
      return;
    }

    // Guardar los datos si el formulario fue enviado
    if (
      isset($_POST['message_to_roulette_winners_nonce']) &&
      wp_verify_nonce(
        $_POST['message_to_roulette_winners_nonce'],
        'message_to_roulette_winners_action'
      )
    ) {
      // wp_kses_post permite HTML seguro (negritas, enlaces, imágenes) pero bloquea scripts maliciosos
      $contenido_seguro = wp_kses_post($_POST['message_to_roulette_winners_editor']);
      update_option('message_to_roulette_winners', $contenido_seguro);

      $color_seguro = sanitize_hex_color($_POST['background_color_win_lycky_wheel']);
      update_option('mi_color_guardado', $color_seguro);
      ?>
      <div class="updated">
        <p>
          <strong>Contenido guardado correctamente.</strong>
        </p>
      </div>
      <?php
    }

    $editor_id = 'message_to_roulette_winners_editor';
    $message = get_option('message_to_roulette_winners', '');
    $color_actual = get_option('mi_color_guardado', '#0073aa');

    // Configuración del editor
    $configuracion = array(
      'media_buttons' => true,  // Permite insertar imágenes/videos
      'textarea_rows' => 15,    // Altura inicial del editor en filas
      'tinymce' => true,  // Carga el editor visual TinyMCE
      'quicktags' => true,  // Habilita las pestañas Visual y Texto (HTML)
    );
    ?>
    <div class="">
      <h1>Ajustes al mensaje de los ganadores de la ruleta</h1>
      <p>Escribe aquí la información que deseas mostrar en la sección pública de tu sitio web.</p>
      <form method="post" action="">
        <?php wp_nonce_field('message_to_roulette_winners_action', 'message_to_roulette_winners_nonce'); ?>

        <h2>
          Mensaje a agregar
        </h2>

        <div style="margin-bottom: 20px;">
          <?php
          wp_editor($message, $editor_id, $configuracion);
          ?>
        </div>

        <div style="
            margin-bottom: 30px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
          ">
          <h2>Color de Acento</h2>
          <p>Selecciona un color de la paleta para personalizar elementos en la web:</p>
          <input type="text" name="background_color_win_lycky_wheel" value="<?php echo esc_attr($color_actual); ?>"
            class="mi-selector-color" data-default-color="#0073aa" />
        </div>

        <?php submit_button('Guardar Cambios'); ?>
      </form>
    </div>
    <?php
  }

  public function cargar_scripts_color_picker($hook)
  {
    // Asegurar que solo se cargue en nuestra página de ajustes personalizada
    if ($hook !== 'toplevel_page_message-to-roulette-winners') {
      return;
    }

    // Carga los estilos y scripts del selector de color nativo
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Script en línea para inicializar el selector en nuestro input text
    wp_add_inline_script('wp-color-picker', "
        jQuery(document).ready(function($) {
            $('.mi-selector-color').wpColorPicker();
        });
    ");
  }

}