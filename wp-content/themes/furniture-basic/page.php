<?php
/**
 * Page — Furniture Basic.
 */
defined( 'ABSPATH' ) || exit;
get_header();
fb_breadcrumb();

while ( have_posts() ) :
  the_post();
  ?>
  <article <?php post_class( 'entry' ); ?>>
    <h1 class="entry__title"><?php the_title(); ?></h1>
    <?php if ( has_post_thumbnail() ) : ?>
      <div class="entry__thumb"><?php the_post_thumbnail( 'large' ); ?></div>
    <?php endif; ?>
    <div class="entry__content">
      <?php
      the_content();
      wp_link_pages( array(
        'before' => '<div class="pagination"><div class="nav-links">',
        'after'  => '</div></div>',
      ) );
      ?>
    </div>
  </article>
  <?php
endwhile;

get_footer();
