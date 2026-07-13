<div class="row-promotion-duration">
  <label for="start_promo_date"><strong>Fecha de Inicio:</strong></label>
  <input type="datetime-local" id="start_promo_date" name="start_promo_date"
    value="<?php echo esc_attr($input_start_date); ?>"
    style="width:100%; padding:8px; margin:5px 0 15px 0; box-sizing:border-box;">
  <p style="font-size:12px; color:#666; margin-top:-10px;">La promoción comenzará en esta fecha y hora.</p>
</div>

<div class="row-promotion-duration">
  <label for="end_promo_date"><strong>Fecha de Fin:</strong></label>
  <input type="datetime-local" id="end_promo_date" name="end_promo_date"
    value="<?php echo esc_attr($input_end_date); ?>"
    style="width:100%; padding:8px; margin:5px 0 15px 0; box-sizing:border-box;">
  <p style="font-size:12px; color:#666; margin-top:-10px;">La promoción finalizará en esta fecha y hora.</p>
</div>