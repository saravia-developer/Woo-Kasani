<?php
namespace WooKasani\Admin\Metaboxes;

require_once WOO_KASANI_CONTROLLER_DIR . 'promotions.php';

use WooKasani\Admin\Controller\PromotionControllers;
use DateTime;

class Promotions_Duration
{

  public function render_promotion_duration_metabox($post)
  {
    wp_nonce_field('promotion_duration_nonce', 'promotion_duration_nonce');

    $promotion_start_time = get_post_meta($post->ID, '_promotion_start_time', true);
    $promotion_end_time = get_post_meta($post->ID, '_promotion_end_time', true);

    // Convertir timestamps a formato datetime-local (YYYY-MM-DDTHH:mm)
    $input_start_date = $promotion_start_time ? wp_date('Y-m-d\TH:i', intval($promotion_start_time)) : '';
    $input_end_date = $promotion_end_time ? wp_date('Y-m-d\TH:i', intval($promotion_end_time)) : '';

    require_once WOO_KASANI_TEMPLATE_METABOX_DIR . 'promotion_duration.php';

    // Mostrar timestamps actuales (para depuración)
    require_once WOO_KASANI_TEMPLATE_METABOX_DIR . 'display_timestamp.php';
  }

  public function save_promotion_duration_metabox($post_id, $post)
  {
    // Verificar nonce
    if (
      !isset($_POST['promotion_duration_nonce']) ||
      !wp_verify_nonce($_POST['promotion_duration_nonce'], 'promotion_duration_nonce')
    ) {
      return;
    }

    // Verificar autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    $timezone = wp_timezone();

    $fields = [
      'start_promo_date' => '_promotion_start_time',
      'end_promo_date' => '_promotion_end_time',
    ];

    foreach ($fields as $input_name => $meta_key) {

      if (empty($_POST[$input_name])) {
        delete_post_meta($post_id, $meta_key);
        continue;
      }

      $value = sanitize_text_field($_POST[$input_name]);

      $date = DateTime::createFromFormat(
        'Y-m-d\TH:i',
        $value,
        $timezone
      );

      if ($date instanceof DateTime) {
        update_post_meta(
          $post_id,
          $meta_key,
          $date->getTimestamp()
        );
      } else {
        delete_post_meta($post_id, $meta_key);
      }
    }
  }

  /**
   * Mostrar mensaje de advertencia y contador en la página de tienda
   */

  public function display_error_flash_sale()
  {
    if ($message = get_transient('mi_flash_sale_error')) {
      ?>
      <div class="notice notice-error is-dismissible">
        <p>
          <strong>⚠️ </strong> <?php echo esc_html($message) ?>
        </p>
      </div>
      <?php
      delete_transient('mi_flash_sale_error');
    }
  }

  public function add_flash_warning_and_timer()
  {
    // Buscar la oferta flash activa más próxima a terminar (la que tiene la fecha fin más cercana)
    $offers = PromotionControllers::get_promotions_active(['fields' => 'ids']);

    if (empty($offers->have_posts()))
      return;

    $offer = $offers->posts[0];

    $start_date = get_post_meta($offer, '_promotion_start_time', true);
    $end_date = get_post_meta($offer, '_promotion_end_time', true);
    $image_url = get_the_post_thumbnail_url($offer, 'thumbnail');

    $timestamp_start = $start_date;
    $timestamp_end = $end_date;
    ?>
    <div class="flash-warning-container">
      <div class="message-flash-warning">
        <!-- <span>
          ¡Aprovecha nuestra ofertas flash!
        </span> -->
        <img 
          src="<?= $image_url; ?>"
          alt="flash-sale-image"
        />
      </div>

      <div id="flash-countdown" data-start="<?php echo esc_attr($timestamp_start); ?>"
        data-end="<?php echo esc_attr($timestamp_end); ?>">
        <span class="days">00</span><span style="font-size:16px; color:#EFEFEF;">d</span>
        <span class="hours">00</span><span style="font-size:16px; color:#111111;">h</span>
        <span class="minutes">00</span><span style="font-size:16px; color:#111111;">m</span>
        <span class="seconds">00</span><span style="font-size:16px; color:#111111;">s</span>
      </div>
    </div>

    <?php
    // Encolar el script del contador SOLO si hay oferta activa
    add_action('wp_footer', [$this, 'promotion_script_contador']);
  }

  public function promotion_script_contador()
  {
    // Para evitar encolarlo múltiples veces, usamos un flag
    if (did_action('mi_contador_encolado'))
      return;
    do_action('mi_contador_encolado');

    ?>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('flash-countdown');
        if (!container) return;

        const startTimestamp = parseInt(container.dataset.start) * 1000; // a milisegundos
        const endTimestamp = parseInt(container.dataset.end) * 1000; // a milisegundos

        const intervalPromoCount = setInterval(updateCountdown, 1000);

        function updateCountdown() {
          const now = new Date().getTime();
          let distance = endTimestamp - now;

          if (now < startTimestamp) {
            container.innerHTML = '';
            return;
          } else if (startTimestamp <= now && now < endTimestamp) {
            container.innerHTML = `
                <span class="days">00</span><span style="font-size:16px; color:#666;">d</span>
                <span class="hours">00</span><span style="font-size:16px; color:#666;">h</span>
                <span class="minutes">00</span><span style="font-size:16px; color:#666;">m</span>
                <span class="seconds">00</span><span style="font-size:16px; color:#666;">s</span>
              `;

            const changeEvent = new CustomEvent('flash_sale_countdown_change');
            container.dispatchEvent(changeEvent);

          } else if (endTimestamp < now) {
            container.innerHTML = '🔥 ¡OFERTA TERMINADA!';
            clearInterval(intervalPromoCount);
            return;
          }

          const days = Math.floor(distance / (1000 * 60 * 60 * 24));
          const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          const seconds = Math.floor((distance % (1000 * 60)) / 1000);

          container.querySelector(`.days`).textContent = String(days).padStart(2, '0');
          container.querySelector(`.hours`).textContent = String(hours).padStart(2, '0');
          container.querySelector(`.minutes`).textContent = String(minutes).padStart(2, '0');
          container.querySelector(`.seconds`).textContent = String(seconds).padStart(2, '0');
        }
      });
    </script>
    <?php
  }
}