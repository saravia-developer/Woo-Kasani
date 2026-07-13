# WOO KASANI

## ¿Cómo usarlo?

Antes de activarlo, se tiene que asegurar que el plugin Woocommerce este activado e instalado, y que el plugin "Woocommerce Lucky Wheel" en el archivo frontend.php que se encuentra en la siguiente ruta "woocommerce-lucky-wheel\frontend\frontend.php", tenga un filtro personalizado en la línea 1473, reemplazando el valor:

```
	$stop = self::get_result( $wheel );
```
Por:

```
  $stop = apply_filters(
      'custom_wlwl_stop_position',
      self::get_result( $wheel ),
      $wheel,
      $email
  );
```

También se tiene que añadir un nuevo filtro de Wordpress para que puedan agregasé los diseños que uno quiera desde el plugin Woo Kasani, nos vamos al archivo "C:\Users\ASUS\Herd\wordpress-2\wp-content\plugins\woocommerce-lucky-wheel\frontend\frontend.php" y estando en la línea 1576 añadimos lo siguiente.

```
  $result_notification .= apply_filters( 
    'wlwl_add_message_of_success_design', 
    $name,
    $email,
    $wheel_label,
    $email_coupons_code
  );     
```

en la línea 289 del archivo woocommerce-lucky-wheel.js tienes que ingresar lo siguiente:

```
  let container = $('.wlwl_lucky_wheel_content');
  container.addClass('success');
```