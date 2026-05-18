<?php
defined('ABSPATH') || exit;
get_header();
?>
<section class="aib-container aib-page">
  <?php while (have_posts()): the_post(); ?>
    <article <?php post_class(); ?>>
      <header class="aib-page__header">
        <h1><?php the_title(); ?></h1>
      </header>
      <div class="aib-page__content">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; ?>
</section>
<?php get_footer();
