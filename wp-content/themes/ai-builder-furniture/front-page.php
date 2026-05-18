<?php
defined('ABSPATH') || exit;
get_header();
$hero = aib_field_url('hero_image');
$shop = aib_field_escaped('shop_name', get_bloginfo('name'));
$tagline = aib_field_escaped('tagline', __('Đồ gỗ thủ công tinh xảo từ làng nghề Việt', 'ai-builder-furniture'));
?>
<section class="aib-hero" <?php if ($hero !== ''): ?>style="background-image: linear-gradient(rgba(0,0,0,.35), rgba(0,0,0,.35)), url(<?php echo esc_url($hero); ?>);"<?php endif; ?>>
  <div class="aib-container">
    <h1><?php echo $shop; ?></h1>
    <p class="aib-hero__tagline"><?php echo $tagline; ?></p>
    <div class="aib-hero__actions">
      <?php
      $shop_url = function_exists('wc_get_page_permalink')
          ? (string) wc_get_page_permalink('shop')
          : home_url('/shop');
      ?>
      <a class="aib-btn aib-btn--primary" href="<?php echo esc_url($shop_url); ?>">
        <?php esc_html_e('Xem sản phẩm', 'ai-builder-furniture'); ?>
      </a>
      <a class="aib-btn aib-btn--ghost" href="<?php echo esc_url(home_url('/bao-gia')); ?>">
        <?php esc_html_e('Yêu cầu báo giá', 'ai-builder-furniture'); ?>
      </a>
    </div>
  </div>
</section>

<section class="aib-container aib-section">
  <h2 class="aib-section__title"><?php esc_html_e('Danh mục sản phẩm', 'ai-builder-furniture'); ?></h2>
  <div class="aib-cat-grid">
    <?php
    $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 8]);
    foreach ((array) $cats as $cat):
      if (!is_object($cat)) continue;
      $link = get_term_link($cat);
      if (is_wp_error($link)) continue;
    ?>
      <a class="aib-cat" href="<?php echo esc_url($link); ?>">
        <?php
        $thumb = (int) get_term_meta($cat->term_id, 'thumbnail_id', true);
        if ($thumb) echo wp_get_attachment_image($thumb, 'medium');
        ?>
        <span><?php echo esc_html($cat->name); ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if (class_exists('WooCommerce')): ?>
<section class="aib-container aib-section">
  <h2 class="aib-section__title"><?php esc_html_e('Sản phẩm nổi bật', 'ai-builder-furniture'); ?></h2>
  <?php echo do_shortcode('[products limit="8" columns="4" orderby="popularity"]'); ?>
</section>
<?php endif; ?>

<section class="aib-craft">
  <div class="aib-container aib-craft__row">
    <div class="aib-craft__copy">
      <h2><?php esc_html_e('Về xưởng', 'ai-builder-furniture'); ?></h2>
      <div><?php echo wp_kses_post((string) aib_field('about_short', __('Xưởng đồ gỗ truyền thống — gắn bó nhiều thế hệ với nghề mộc. Chúng tôi sử dụng các loại gỗ quý: Mít, Gụ, Lim, Hương, Sồi, Cao Su, Xoan Đào.', 'ai-builder-furniture'))); ?></div>
      <a class="aib-btn aib-btn--ghost" href="<?php echo esc_url(home_url('/gioi-thieu')); ?>"><?php esc_html_e('Tìm hiểu thêm', 'ai-builder-furniture'); ?></a>
    </div>
  </div>
</section>
<?php get_footer();
