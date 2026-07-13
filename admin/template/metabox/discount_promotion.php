<div id="field-discount-box" class="">
  <div>
    <label for="select_type_discount">
      Tipo de descuento
    </label>
    <select id="select_type_discount" name="select_type_discount">
      <option style="display: none;">Eliga una opción</option>
      <?php
      foreach ($types_discount as $key => $type_disc):
        ?>
        <option <?php echo selected($type_discount_selected, $key) ?> value="<?php echo esc_html($key); ?>"><?php echo $type_disc; ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <label for="products_group_discount">
      Valor del Descuento
    </label>
    <input
      type="number"
      name="products_group_discount"
      id="products_group_discount"
      value="<?php echo $discount_quantity; ?>"
    />
  </div>
</div>