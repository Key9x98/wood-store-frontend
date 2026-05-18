<?php defined('ABSPATH') || exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<link rel="profile" href="https://gmpg.org/xfn/11" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="aib-header">
  <div class="aib-container aib-header__row">
    <a class="aib-header__brand" href="<?php echo esc_url(home_url('/')); ?>">
      <?php
      $logo = aib_field_url('logo');
      $shop = aib_field_escaped('shop_name', get_bloginfo('name'));
      if ($logo !== '') {
          printf('<img src="%s" alt="%s" />', esc_url($logo), esc_attr($shop));
      } else {
          echo '<span class="aib-brand-text">' . $shop . '</span>';
      }
      $tagline = aib_field_escaped('tagline');
      if ($tagline !== '') {
          echo '<small class="aib-tagline">' . $tagline . '</small>';
      }
      ?>
    </a>
    <nav class="aib-header__nav">
      <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'menu_class' => 'aib-menu',
          'fallback_cb' => '__return_empty_string',
      ]); ?>
    </nav>
    <div class="aib-header__cta">
      <a class="aib-hotline" href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', (string) aib_field('phone'))); ?>">
        ☎ <?php echo aib_field_escaped('phone', '0900 000 000'); ?>
      </a>
      <?php if (get_theme_mod('aib_show_quote_cta', 'yes') === 'yes'): ?>
        <a class="aib-btn aib-btn--primary" href="<?php echo esc_url(home_url('/bao-gia')); ?>">
          <?php esc_html_e('Yêu cầu báo giá', 'ai-builder-furniture'); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="aib-main">
