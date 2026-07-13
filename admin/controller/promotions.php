<?php
namespace WooKasani\Admin\Controller;

use WP_Query;

class PromotionControllers
{
  public static function get_promotions(array $args = [], array $meta_args = [])
  {

    $args_promotions = array_merge(
      [
        'post_type' => 'promotions',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => $meta_args,
      ],
      $args
    );

    return new WP_Query($args_promotions);
  }

  public static function get_last_promotion(array $args = [])
  {
    $promo_args = array_merge(
      ['post_per_page' => 1, 'order' => 'DESC'],
      $args
    );
    return self::get_promotions($promo_args);
  }

  public static function deactive_promotions_active($post_id)
  {
    $promotions = self::get_promotions(
      [
        'post__in_not' => [$post_id]
      ],
      [
        [
          'key' => '_promotion_active',
          'value' => 'yes'
        ]
      ]
    );

    if ($promotions->have_posts()) {
      while ($promotions->have_posts()) {
        $promotions->the_post();

        update_post_meta(get_the_ID(), '_promotion_active', 'no');
      }
    }
    wp_reset_postdata();
  }

  public static function get_promotions_active(array $args = [], array $meta_args = [])
  {

    $meta_args_promotions = array_merge([
      [
        'key' => '_promotion_active',
        'value' => 'yes'
      ]
    ], $meta_args);

    $args_promotions = array_merge(['post_per_page' => 1], $args);

    return self::get_promotions($args_promotions, $meta_args_promotions);
  }

  public static function get_promotion_meta() {
    global $wpdb;

    $prefix = $wpdb->prefix;
    $sql = "
      SELECT
        wppm_outer.post_id,
          wppm_outer.meta_key,
          wppm_outer.meta_value
      FROM {$prefix}postmeta wppm_outer
      WHERE wppm_outer.post_id IN (
          SELECT
              wpp.ID
          FROM {$prefix}posts wpp
          INNER JOIN {$prefix}postmeta wppm_inner
              ON wpp.ID = wppm_inner.post_id
          WHERE 
              wpp.post_type = 'promotions' AND
              wpp.post_status = 'publish' AND
              wppm_inner.meta_key = '_promotion_active' AND
              wppm_inner.meta_value = 'yes'
      )
      AND (
          wppm_outer.meta_key = '_promotion_active' 
          OR wppm_outer.meta_key = '_promotion_end_time'
          OR wppm_outer.meta_key = '_products_selected'
      );
    ";
    $data = $wpdb->get_results($sql);

    return $data;
  }
}