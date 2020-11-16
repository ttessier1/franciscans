<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" >
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<!-- Content Wrap Start -->
<div id="content-wrap">
<!-- Flag Menu -->
<?php
	// Do flag sidebar
	$has_language_menu = is_active_sidebar( 'language-bar' );
	if ( $has_language_menu ){
?>
		<div class="language-bar">
			<?php dynamic_sidebar( 'language-bar' ); ?>
		</div>
<?php
	}
?>
<!-- End of Flag Menu -->


<!-- Search Bar -->
<?php
	$has_search = is_active_sidebar( 'search-bar' );
	if ( $has_search ){
?>
		<div class="search-bar">
			<?php dynamic_sidebar( 'search-bar' ); ?>
		</div>
<?php
	}
?>
<!-- End of Search Bar -->


<?php
	wp_body_open();
?>

		<header id="site-header" class="header-footer-group" role="banner">

			<div class="header-inner section-inner">

				<div class="header-titles-wrapper">

					<div class="header-titles">

						<?php
							// Site title or logo.
							franciscans_site_logo();

							// Site description.
							franciscans_site_description();
						?>

					</div><!-- .header-titles -->

				</div><!-- .header-titles-wrapper -->


			
			</div><!-- .header-inner -->

			<?php
			// Output the search modal (if it is activated in the customizer).
			if ( true === $enable_header_search ) {
				get_template_part( 'template-parts/modal-search' );
			}
			?>

		</header><!-- #site-header -->
		<div class="nav-top">
			<?php
				$has_logo = is_active_sidebar( 'site-logo' );
				$has_main_nav = is_active_sidebar( 'menu-bar' );
				if ( $has_logo && $has_main_nav) 
				{?>
					<div class="site-logo">
							<?php dynamic_sidebar( 'site-logo' ); ?>
					</div>
					<div class="menu-bar">
							<?php dynamic_sidebar( 'menu-bar' ); ?>
					</div>
<?php
				}
				
				else if ( $has_logo ) 
				{?>
					<div class="site-logo">
							<?php dynamic_sidebar( 'site-logo' ); ?>
					</div>
<?php
				}
				else if ( $has_main_nav)
				{
					?>
					<div class="menu-bar">
							<?php dynamic_sidebar( 'menu-bar' ); ?>
					</div>
				<?php
				}
			?>

		</div>
		<?php
			$has_header_left = is_active_sidebar( 'header-left' );
			$has_header_content = is_active_sidebar( 'header-content' );
			$has_header_right = is_active_sidebar( 'header-right' );
			?>
		<div class="header-bottom<?php 
			if ( $has_header_content ){
				if ( $has_header_left && $has_header_right)
				{
					?>-sml<?php
				}else if ($has_header_left || $has_header_right ) 
				{
					?>-mid<?php

				}else{
					?>-lrg<?php
				}
			}
		
		?>">
			
				<!-- Header-Left -->
				<?php
					
					if ( $has_header_left ){
				?>
						<div class="header-left">
							<?php dynamic_sidebar( 'header-left' ); ?>
						</div>
				<?php
					}
				?>
				<!-- End of Header-Left -->
				<!-- Header-Content -->
				<?php
					
					if ( $has_header_content ){
						if ( $has_header_left && $has_header_right)
						{
							?><div class="header-content-sml"><?php
						}else if ($has_header_left || $has_header_right ) 
						{
							?><div class="header-content-mid"><?php

						}else{
							?><div class="header-content-lrg"><?php
						}
				?>
						
							<?php dynamic_sidebar( 'header-content' ); ?>
						</div>
				<?php
					}
				?>
				<!-- End of Header-Content -->
				<!-- Header-Right -->
				<?php
					
					if ( $has_header_right ){
				?>
						<div class="header-right">
							<?php dynamic_sidebar( 'header-right' ); ?>
						</div>
				<?php
					}
				?>
				<!-- End of Header-Right -->
		</div>
		<?php
		// Output the menu modal.
		get_template_part( 'template-parts/modal-menu' );
