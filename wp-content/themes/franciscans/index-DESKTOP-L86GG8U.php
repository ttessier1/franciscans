<!-- header -->
<?php
get_header();
?>
<!-- end of header -->
<main id="site-content" role="main">

	<?php

	$archive_title    = '';
	$archive_subtitle = '';

	if ( is_search() )
	{
		global $wp_query;

		$archive_title = sprintf(
			'%1$s %2$s',
			'<span class="color-accent">' . __( 'Search:', 'twentytwenty' ) . '</span>',
			'&ldquo;' . get_search_query() . '&rdquo;'
		);

		if ( $wp_query->found_posts ) {
			$archive_subtitle = sprintf(
				/* translators: %s: Number of search results. */
				_n(
					'We found %s result for your search.',
					'We found %s results for your search.',
					$wp_query->found_posts,
					'franciscans'
				),
				number_format_i18n( $wp_query->found_posts )
			);
		} else {
			$archive_subtitle = __( 'We could not find any results for your search. You can give it another try through the search form below.', 'twentytwenty' );
		}
	} elseif ( is_archive() && ! have_posts() ) {
		$archive_title = __( 'Nothing Found', 'franciscans' );
	} elseif ( ! is_home() ) {
		$archive_title    = get_the_archive_title();
		$archive_subtitle = get_the_archive_description();
	}

	if ( $archive_title || $archive_subtitle ) {
		?>

		<header class="archive-header has-text-align-center header-footer-group">

			<div class="archive-header-inner section-inner medium">

				<?php if ( $archive_title ) { ?>
					<h1 class="archive-title"><?php echo wp_kses_post( $archive_title ); ?></h1>
				<?php } ?>

				<?php if ( $archive_subtitle ) { ?>
					<div class="archive-subtitle section-inner thin max-percentage intro-text"><?php echo wp_kses_post( wpautop( $archive_subtitle ) ); ?></div>
				<?php } ?>

			</div><!-- .archive-header-inner -->

		</header><!-- .archive-header -->

		<?php
	}

	if ( have_posts() )
	{

		$i = 0;
?>
		<div class="content<?php 
			if ( $has_content ){
				if ( $has_content_left && $has_content_right)
				{
					?>-sml<?php
				}else if ($has_content_left || $has_content_right ) 
				{
					?>-mid<?php

				}else{
					?>-lrg<?php
				}
			}
		
		?>">
		<?php
			// Content Left
			?>
			<!-- Content-Left -->
			<?php
				
				if ( $has_content_left ){
			?>
					<div class="content-left">
						<?php dynamic_sidebar( 'content-left' ); ?>
					</div>
			<?php
				}
			?>
			<!-- End of Content-Left -->
			<!-- Content -->
			<?php
				
				
					if ( $has_content_left && $has_Content_right)
					{
						?><div class="content-sml"><?php
					}else if ($has_content_left || $has_content_right ) 
					{
						?><div class="content-mid"><?php
	
					}else{
						?><div class="content-lrg"><?php
					}
				if ( $has_content )
				{
					dynamic_sidebar( 'content' );
				}
		

		while ( have_posts() ) {
			$i++;
			if ( $i > 1 ) {
				echo '<hr class="post-separator styled-separator is-style-wide section-inner" aria-hidden="true" />';
			}
			the_post();


			get_template_part( 'template-parts/content', get_post_type() );


			// Content Right
		}
		?>
		<!-- End of Header-Content -->
		<!-- Content-Right -->
		<?php
			
			if ( $has_content_right ){
		?>
				<div class="content-right">
					<?php dynamic_sidebar( 'content-right' ); ?>
				</div>
		<?php
			}
		?>
		<!-- End of Content-Right -->
	<?php


	} elseif ( is_search() ) {
	?>

		<div class="no-search-results-form section-inner thin">

			<?php
			get_search_form(
				array(
					'label' => __( 'search again', 'twentytwenty' ),
				)
			);
			?>

		</div><!-- .no-search-results -->

		<?php
	}
	?>

	<?php get_template_part( 'template-parts/pagination' ); ?>

</main><!-- #site-content -->
<?php
	$has_footer_left = is_active_sidebar( 'footer-left' );
	$has_footer_content = is_active_sidebar( 'footer-content' );
	$has_footer_right = is_active_sidebar( 'footer-right' );
	?>
<div class="footer-bottom<?php 
	if ( $has_footer_content ){
		if ( $has_footer_left && $has_footer_right)
		{
			?>-sml<?php
		}else if ($has_footer_left || $has_footer_right ) 
		{
			?>-mid<?php

		}else{
			?>-lrg<?php
		}
	}

?>">
<!-- Footer-Left -->
<?php
	
	if ( $has_footer_left ){
?>
		<div class="footer-left">
			<?php dynamic_sidebar( 'footer-left' ); ?>
		</div>
<?php
	}
?>
<!-- End of Footer-Left -->
<!-- Footer-Content -->
<?php
	
	if ( $has_footer_content ){
		if ( $has_footer_left && $has_footer_right)
		{
			?><div class="footer-content-sml"><?php
		}else if ($has_footer_left || $has_footer_right ) 
		{
			?><div class="footer-content-mid"><?php

		}else{
			?><div class="footer-content-lrg"><?php
		}
?>
		
			<?php dynamic_sidebar( 'footer-content' ); ?>
		</div>
<?php
	}
?>
<!-- End of Footer-Content -->
<!-- Footer-Right -->
<?php
	
	if ( $has_footer_right ){
?>
		<div class="footer-right">
			<?php dynamic_sidebar( 'footer-right' ); ?>
		</div>
<?php
	}
?>
<!-- End of Footer-Right -->
<?php
get_footer();
?>
</div>