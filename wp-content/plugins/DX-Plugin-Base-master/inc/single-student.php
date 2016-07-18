<?php
 /*
 * Template Name: Student Single Template
 */
 
get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
    <h1>Single Page</h1>
    
        <?php
        $student_post = array( 'post_type' => 'student', );
        $loop = new WP_Query( $student_post );
        ?>

        <?php 

        while ( have_posts() ) : the_post(); get_template_part( 'student', 'single' ); 

        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="container">
 
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
				<p>Summary: <?php the_content(); ?></p>
            </div>
        </article>
 
        <?php endwhile; ?>

    </main>
    <?php get_sidebar( 'content-bottom' ); ?>
</div>


<?php wp_reset_query(); ?>

<?php get_sidebar(); ?>

<?php get_footer(); ?>


<div class="container">
    <article>
        <?php

            $args=array(
                    'post_type' =>  'event',
                    'posts_per_page'    => 10,
                    'post_status' => 'publish'
                    );
            $loop = new WP_Query($args);

            while ( $loop->have_posts() ) : $loop->the_post(); ?>

                <div class="main-content">
                    <h1><?php the_title(); ?></h1>
                    <br />
                    <p>
                        <?php the_content(); ?>
                    </p>
                </div>
        ?>
    </article>
</div>
