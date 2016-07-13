<?php 
/**
 * The template for displaying all archive posts and attachments.
 *
**/
?>

<?php get_header(); ?>

<?php

$args = array( 'post_type' => 'student', 'posts_per_page' => 10 );

$loop = new WP_Query( $args );
while ( $loop->have_posts() ) : $loop->the_post();
    echo "h1>";
    the_title();
    echo "</h1>";
    echo "<br />";
    echo '<div class="entry-content">';
    the_content();
    echo '</div>';
endwhile;

?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>