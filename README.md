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

en la línea 238 del archivo woocommerce-lucky-wheel.js tienes que ingresar el argumento 'is_new_user' que viene en la 'response' :

```
  spins_wheel(response.stop_position, response.result_notification, response.result, response?.is_new_user);
```

en la línea 285 del archivo woocommerce-lucky-wheel.js tienes que ingresar el parametro 'is_new_user' con el valor por defecto de 'null' :

```
  function spins_wheel(stop_position, result_notification, result, is_new_user = null)
```

en la línea 370 del archivo woocommerce-lucky-wheel.js tienes que ingresar lo siguiente:

```
if(is_new_user) {
    let container = $('.wlwl_lucky_wheel_content');
    container.addClass('success');
    $('.wheel-content-wrapper .wheel_content_right .wlwl_user_lucky > .wlwl-frontend-result').html(is_new_user?.message).fadeIn(300);
}
```