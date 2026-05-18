<?php
/**
 * Header — Furniture Basic.
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
    <?php echo esc_html( fb_field( 'shop_name' ) ); ?>
  </a>
  <span class="hotline" style="color:<?php echo esc_attr( fb_field( 'hotline_color' ) ); ?>">
    ☎ Hotline: <?php echo esc_html( fb_field( 'phone' ) ); ?>
  </span>
</header>
