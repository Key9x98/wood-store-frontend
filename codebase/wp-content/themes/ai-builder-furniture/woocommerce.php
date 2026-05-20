<?php
defined('ABSPATH') || exit;
get_header();
?>
<section class="aib-container aib-shop">
  <div class="aib-shop__layout">
    <?php aib_furniture_filter_sidebar(); ?>
    <div class="aib-shop__main">
      <?php woocommerce_content(); ?>
    </div>
  </div>
</section>
<?php get_footer();
