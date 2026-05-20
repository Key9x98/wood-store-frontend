<?php
defined('ABSPATH') || exit;
get_header();
?>
<section class="aib-container aib-single">
  <?php while (have_posts()): the_post(); ?>
    <article <?php post_class(); ?>>
      <header class="aib-single__header">
        <h1><?php the_title(); ?></h1>
        <p class="aib-meta"><?php echo esc_html(get_the_date()); ?></p>
      </header>
      <?php if (has_post_thumbnail()): ?>
        <div class="aib-single__media"><?php the_post_thumbnail('large'); ?></div>
      <?php endif; ?>
      <div class="aib-single__content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
</section>
<?php get_footer();
