<?php
 /*
 * Template Name: Student Single Template
 */
 
get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    <?php
    $student_post = array( 'post_type' => 'student', );
    $loop = new WP_Query( $student_post );
    ?>
    <?php while ( $loop->have_posts() ) : $loop->the_post();?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
 
                <strong>Name: </strong><?php the_title(); ?><br />
                <strong>ID: </strong>
                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_id', true ) ); ?>
                <br />
                <strong>Section: </strong>
                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_section', true ) ); ?>
                <br />
                <strong>Year: </strong>
                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_year', true ) ); ?>
                <br />
                <strong>Address: </strong>
                <?php echo esc_html( get_post_meta( get_the_ID(), 'student_address', true ) ); ?>
                <br />
				<p class="entry-content">Summary: <?php the_content(); ?></p>
            </header>
        </article>
 
    <?php endwhile; ?>

    </main>
    <?php get_sidebar( 'content-bottom' ); ?>
</div>


<?php wp_reset_query(); ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
