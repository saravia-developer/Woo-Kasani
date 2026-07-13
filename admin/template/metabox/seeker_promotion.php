<div id="product-selector">
  <!-- Buscador -->
  <div class="buscador-wrapper">
    <input type="text" id="products_seeker" placeholder="Buscar productos..." autocomplete="off">
    <div id="search-results" style="display:none;"></div>
  </div>

  <div id="products_list" style="display: none;"></div>

  <!-- Input de selección (solo lectura) -->
  <div class="seleccion-wrapper">
    <label for="selected-products">Productos seleccionados:</label>
    <div id="selected-products" class="tags-container">
      <?php echo $html_products_selected; ?>
    </div>
    <input type="hidden" id="selected-products-input" name="products_selected_for_seeker"
      value="<?php echo esc_attr($products_selected_json_encoded); ?>">
  </div>
</div>