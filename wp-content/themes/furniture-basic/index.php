<?php
/**
 * Template chính — Furniture Basic.
 * Giá trị nội dung lấy qua fb_field() (CMS đẩy xuống hoặc default).
 */
get_header();
?>
<main class="site-main">
  <section class="hero">
    <h1><?php echo esc_html( fb_field( 'shop_name' ) ); ?></h1>
    <p class="intro"><?php echo esc_html( fb_field( 'intro' ) ); ?></p>
    <a class="cta" href="#products">Xem sản phẩm</a>
  </section>

  <section class="products" id="products">
    <h2>Sản phẩm nổi bật</h2>
    <?php
    $loop = new WP_Query( array( 'post_type' => 'product', 'posts_per_page' => 8 ) );
    if ( $loop->have_posts() ) :
      echo '<div class="product-grid">';
      while ( $loop->have_posts() ) :
        $loop->the_post();
        ?>
        <article class="product-card">
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="thumb"><?php the_post_thumbnail( 'medium' ); ?></div>
          <?php endif; ?>
          <h3><?php the_title(); ?></h3>
          <div class="excerpt"><?php the_excerpt(); ?></div>
        </article>
        <?php
      endwhile;
      echo '</div>';
      wp_reset_postdata();
    else :
      ?>
      <p class="empty">Chưa có sản phẩm nào. Thêm sản phẩm trong wp-admin → Sản phẩm.</p>
      <?php
    endif;
    ?>
  </section>
</main>
<?php get_footer(); ?>
