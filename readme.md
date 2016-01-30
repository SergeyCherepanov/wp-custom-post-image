## WordPress Custom Post and Page Images

List of images:

    <?php list_post_images($post_id = false, $width = false, $height = false, $method = 'crop', $background = 'FFFFFF'); ?>
    
Get array of images:

    <?php array get_post_images($post_id = false); ?>

Usage example:

    <?php if (have_posts()) :
      <?php while (have_posts()) : the_post(); ?>
        <h2><?php the_title();?></h2>
        <?php list_post_images(false, 400, 300);?>
        <div class=”entry”><?php the_content();?></div>
      <?php endwhile; ?>
    <?php endif;?>
