<?php
/**
 * @url https://yandex.ru/support/zen/publishers/rss.html?lang=ru
 */

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );


$options = get_option( 'news_feeds' );


echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss
	version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:media="http://search.yahoo.com/mrss/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:georss="http://www.georss.org/georss">
	<channel>
		<atom:link href="<?php bloginfo_rss( 'url' ); ?>/feeds/zen.xml" rel="self" type="application/rss+xml" />
		<title><?php wp_title_rss(); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<language>ru</language>
		<?php
		while( have_posts() ) : the_post();

			if ( get_post_type() === 'post' ) :

				$content = apply_filters( 'the_content_feed', wpautop( do_shortcode( get_post_field( 'post_content', get_the_ID() ) ) ), 'rss2' );

				$category = array();
				$category_filed = get_field( 'category_zen' ) ? get_field( 'category_zen' ) : $options['category_zen'];
				$author_field = get_field( 'author' );
				$author = $author_field ? get_field_object( 'author' )['choices'][ $author_field ] : get_the_author();

				foreach( $category_filed as $value ) :
					array_push( $category, NewsFeed::get_zen_category()[ $value ] );
				endforeach;
				?>

				<item>
					<title><?php the_title_rss(); ?></title>
					<link><?php the_permalink_rss(); ?></link>
					<guid><?php the_guid(); ?></guid>
					<amplink><?php the_permalink_rss(); ?>amp/</amplink>
					<pubDate><?php echo get_the_date( 'r' ); ?></pubDate>
					<media:rating scheme="urn:simple">nonadult</media:rating>
					<category><?php echo implode( ',', $category ); ?></category>
					<author><?php echo $author; ?></author>

					<?php
					preg_match_all( '/<img[^>]+>/i', $content, $images, PREG_PATTERN_ORDER );

					foreach ( $images[0] as $image ) :
						preg_match( '/wp-image-(\d+)(.*?)/i', $image, $matches ); // получаем id изображения из класса

						if ( count( $matches ) < 1 ) continue;

						NewsFeed::get_enclosure( $matches[1] );
					endforeach;


					if ( has_post_thumbnail() ) :
						NewsFeed::get_enclosure( get_post_thumbnail_id() );
					endif;


					?><description><![CDATA[<?php echo the_excerpt_rss(); ?>]]></description>
					<content:encoded><![CDATA[<?php
						$pattern = '/<a(.*?)>(.*?)<\/a>/s';
						$replacement = '$2';
						$content = preg_replace( $pattern, $replacement, $content );

						$pattern = '/<figure(.*?)class=\"wp-caption(.*?)>(.*?)<figcaption class=\"wp-caption-text\">(.*?)<\/figcaption>(.*?)<\/figure>/s';
						$replacement = '<p>$3</p>';
						$content = preg_replace( $pattern, $replacement, $content );

						$pattern = '/<img(.*?)src=\"(.*?)\"(.*?) \/>/i';
						$replacement = '<figure>
							<img src="$2"$3>
							<figcaption>
								' . get_the_title_rss() . '
								<span class="copyright">'. $author .'</span>
							</figcaption>
						</figure>';
						$content = preg_replace( $pattern, $replacement, $content );

						$pattern = '/<p><figure>(.*?)<\/figure><\/p>/s';
						$replacement = '<figure>$1</figure>';
						$content = preg_replace( $pattern, $replacement, $content );

						$patterns = array(
							'/<p>https:\/\/youtu.*?<\/p>/i',
							'/<p>https:\/\/www.youtu.*?<\/p>/i'
						);
						$replacements = '';
						$content = preg_replace( $patterns, $replacements, $content );

						$thumb_content = '<figure>' .
							get_the_post_thumbnail( get_the_ID(), 'full' ) .
							'<figcaption>' .
								get_the_title_rss() . '
								<span class="copyright">'. $author .'</span>
							</figcaption>
						</figure>';

						$thumb_content .= $content;

						$thumb_content = NewsFeed::text_clear( $thumb_content );

						echo $thumb_content;

					?>]]></content:encoded>
				</item>

				<?php
			endif;

		endwhile;
		?>
	</channel>
</rss>
