<?php get_header(); ?>

<style>
    .paperThumb {
        width:200px;
        height:auto;
    }

    .publicationBox {
        margin-left: 225px;
    }

    .summary-shortdesc {
        font-weight: bold;
    }

    .publication {
        margin-top:50px;
    }
</style>

<h2>Publications</h2>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="publication">
		<?php
		if(has_post_thumbnail())
		{
			echo get_the_post_thumbnail(get_the_ID(), 'full', array( 'class' => 'pull-left paperThumb'));
		}
		?>

		<div class="publicationBox">
			<h4 style="margin-top: 0;"><?php the_title(); ?></h4>

			<?php
			$authors = get_post_meta(get_the_ID(), '_authors', true);
			if($authors != '')
				echo '<i>'.$authors.'</i>';
			?>

			<?php the_content(); ?>

			<p>
				<?php
				$proceeding = get_post_meta(get_the_ID(), '_proceeding', true);
				if($proceeding != '')
					echo $proceeding.'<br>';

				$links = Array();

				$page_url = get_post_meta(get_the_ID(), '_page_url', true);
				if($page_url != '')
					$links[] = '<a href="'.$page_url.'">project page</a> ';

				$publication_url = get_post_meta(get_the_ID(), '_publication_url', true);
				if($publication_url != '')
					$links[] = '<a href="'.$publication_url.'">paper</a> ';

				$video_url = get_post_meta(get_the_ID(), '_video_url', true);
				if($video_url != '')
					$links[] = '<a href="'.$video_url.'">video</a> ';

				$supp_material_url = get_post_meta(get_the_ID(), '_supp_material_url', true);
				if($supp_material_url != '')
					$links[] = '<a href="'.$supp_material_url.'">supplemental material</a> ';

				$bibtex_url = get_post_meta(get_the_ID(), '_bibtex_url', true);
				if($bibtex_url != '')
					$links[] = '<a href="'.$bibtex_url.'">bibtex</a> ';

				echo implode(" &middot; ", $links);
				?>
			</p>
		</div>
	</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
