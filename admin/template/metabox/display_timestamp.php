<?php

    if ($promotion_start_time || $promotion_end_time) {
      ?>
        <div style="background:#f5f5f5; padding:10px; border-radius:4px; font-size:12px; color:#555;">
          <strong>Timestamps guardados:</strong><br>
          <?php
          if ($promotion_start_time) {
          ?>
            Inicio: <?php echo esc_html($promotion_start_time) ?> (<?php echo wp_date('Y-m-d H:i:s', intval($promotion_start_time)) ?>)
            <br/>
          <?php
          }
          if ($promotion_end_time) {
          ?>
            Fin: <?php echo esc_html($promotion_end_time) ?> (<?php echo wp_date('Y-m-d H:i:s', intval($promotion_end_time)) ?>)
            <br/>
          <?php
          }
          ?>
        </div>
      <?php
    }