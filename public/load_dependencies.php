<?php
namespace WooKasani\Frontend;

require_once WOO_KASANI_CONTROLLER_DIR . 'promotions.php';

use WooKasani\Admin\Controller\PromotionControllers;
class Load_Dependencies
{
  /**
   * Load all scripts of public directory
   * @return void
   */
  public function load_scripts()
  {
    $base_path = WOO_KASANI_PUBLIC_DIR . 'js/';
    $scripts_alt = glob($base_path . '*.js');

    if (empty($scripts_alt)) {
      return;
    }

    for ($i = 0; $i < count($scripts_alt); $i++) {
      list(
        $handle,
        $uri,
        $deps,
        $version,
        $args,
        $restricted_scripts
      ) = $this->format_data_for_enqueue($scripts_alt[$i], 'js');

      if (!$this->should_load_on_current_page($restricted_scripts)) {
        continue;
      }

      wp_enqueue_script(
        $handle,
        $uri,
        $deps,
        $version,
        array_merge(['in_footer' => true], $args)
      );
    }
  }

  /**
   * Load all styles of public directory
   * @return void
   */
  public function load_styles()
  {
    $base_path = WOO_KASANI_PUBLIC_DIR . 'css/';
    $styles_alt = glob($base_path . '*.css');

    if (empty($styles_alt)) {
      return;
    }

    for ($i = 0; $i < count($styles_alt); $i++) {
      list(
        $handle,
        $uri,
        $deps,
        $version,
        $args,
        $restricted_scripts
      ) = $this->format_data_for_enqueue($styles_alt[$i], 'css');

      if (!$this->should_load_on_current_page($restricted_scripts)) {
        continue;
      }

      wp_enqueue_style(
        $handle,
        $uri,
        $deps,
        $version,
        $args
      );
    }
  }

  /**
   * Load libraries in header
   * @return void
   */
  public function load_libraries()
  {
    ?>

    <?php
  }

  private function get_options_scripts()
  {
    return [
      'deactive-promotions.js' => [
        'dependencies' => ['jquery'],
        'arguments' => [],
        'restricted_scripts' => [],
        'localizes' => [
          [
            'key' => 'togglePromotion',
            'value' => [
              'ajaxurl' => admin_url('admin-ajax.php'),
              'nonce' => wp_create_nonce('deactivate_flash_promotion_nonce'),
              'promotion_end_time' => PromotionControllers::get_promotion_meta() ?? [],
            ]
          ]
        ]
      ],
    ];
  }

  private function get_options_styles()
  {
    return [
      'woo_kasani_public.css' => [
        'dependencies' => [],
        'arguments' => 'all',
        'restricted_scripts' => [],
      ],
    ];
  }

  public function load_wp_localize_script()
  {
    $script_opts = $this->get_options_scripts();

    foreach ($script_opts as $name => $opts) {
      $localizes = $opts['localizes'] ?? [];

      if (empty($localizes)) {
        continue;
      }

      foreach ($localizes as $localize) {
        $key = $localize['key'];
        $value = $localize['value'];
        $handle = 'woo_kasani_' . str_replace('.js', '', $name);

        wp_localize_script(
          $handle,
          $key,
          $value
        );
      }
    }
  }

  private function format_data_for_enqueue($data, $directory)
  {
    $base_uri = WOO_KASANI_PUBLIC_URL . $directory . '/';

    $options_scripts = "";

    switch ($directory) {
      case 'js':
        $options_scripts = $this->get_options_scripts();
        break;

      case 'css':
        $options_scripts = $this->get_options_styles();
        break;

      default:
        $options_scripts = [];
    }

    $basename = basename($data);
    $handle = 'woo_kasani_' . str_replace(".$directory", '', $basename);
    $uri = $base_uri . $basename;
    $deps = $options_scripts[$basename]['dependencies'] ?? [];
    $version = file_exists($data) ? filemtime($data) : '1.0.0';
    $args = $options_scripts[$basename]['arguments'] ?? [];
    $restricted_scripts = $options_scripts[$basename]['restricted_scripts'] ?? [];

    if ($directory === 'css') {
      return [
        $handle,
        $uri,
        $deps,
        $version,
        $args,
        $restricted_scripts,
      ];
    }

    return [
      $handle,
      $uri,
      $deps,
      $version,
      $args,
      $restricted_scripts,
    ];
  }


  private function should_load_on_current_page($pages): bool
  {
    if (empty($pages)) {
      return true;
    }

    if (!is_array($pages)) {
      $pages = [$pages];
    }

    return is_page($pages);
  }
}