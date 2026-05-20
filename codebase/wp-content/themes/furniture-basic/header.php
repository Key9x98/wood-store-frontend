<?php
/**
 * Header — Furniture Basic.
 */
defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content">Bỏ qua đến nội dung</a>

<div class="topbar">
  <div class="container topbar__inner">
    <div class="topbar__info">
      <span><?php echo fb_icon( 'clock', 15 ); ?> <?php echo esc_html( fb_field( 'working_hours' ) ); ?></span>
      <span><?php echo fb_icon( 'mail', 15 ); ?> <a href="mailto:<?php echo esc_attr( fb_field( 'email' ) ); ?>"><?php echo esc_html( fb_field( 'email' ) ); ?></a></span>
    </div>
    <div class="topbar__cta">
      <span class="topbar__ship"><?php echo fb_icon( 'truck', 15 ); ?> Miễn phí giao lắp nội thành</span>
      <?php if ( fb_field( 'facebook' ) ) : ?>
        <a class="topbar__social" href="<?php echo esc_url( fb_field( 'facebook' ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php echo fb_icon( 'facebook', 14 ); ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container site-header__inner">
    <?php if ( has_custom_logo() ) : ?>
      <div class="brand"><?php the_custom_logo(); ?></div>
    <?php else : ?>
      <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <span class="brand__mark"><?php echo fb_icon( 'sofa', 26 ); ?></span>
        <span class="brand__text">
          <span class="brand__name"><?php echo esc_html( fb_field( 'shop_name' ) ); ?></span>
          <span class="brand__tag"><?php echo esc_html( fb_field( 'tagline' ) ); ?></span>
        </span>
      </a>
    <?php endif; ?>

    <nav class="primary-nav" id="primary-nav" aria-label="Menu chính">
      <?php
      wp_nav_menu( array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'nav-menu',
        'fallback_cb'    => 'fb_fallback_menu',
        'depth'          => 2,
      ) );
      ?>
    </nav>

    <div class="header-actions">
      <button class="icon-btn search-toggle" aria-label="Tìm kiếm" aria-expanded="false">
        <?php echo fb_icon( 'search', 21 ); ?>
      </button>
      <a class="header-phone" href="tel:<?php echo esc_attr( fb_tel() ); ?>">
        <span class="header-phone__icon"><?php echo fb_icon( 'phone', 19 ); ?></span>
        <span class="header-phone__text">
          <small>Hotline đặt hàng</small>
          <strong><?php echo esc_html( fb_field( 'phone' ) ); ?></strong>
        </span>
      </a>
      <button class="menu-toggle" aria-label="Mở menu" aria-expanded="false" aria-controls="primary-nav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <div class="header-search-bar">
    <div class="container"><?php get_search_form(); ?></div>
  </div>
</header>
<div class="nav-overlay"></div>

<div id="content">
