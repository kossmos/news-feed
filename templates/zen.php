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

				$images = get_attached_media( 'image' );
				$author = get_field( 'author' );
				?>
				<item>
					<title><?php the_title_rss(); ?></title>
					<link><?php the_permalink_rss(); ?></link>
			        <guid><?php the_guid(); ?></guid>
					<amplink><?php the_permalink_rss(); ?>amp/</amplink>
					<pubDate><?php echo get_the_date( 'r' ); ?></pubDate>
					<media:rating scheme="urn:simple">nonadult</media:rating>
					<category>Мода</category>
					<?php
					if ( $author ) :
						$obAuthor = get_field_object( 'author' );
						?>
						<author><?php echo $obAuthor['choices'][ $author ]; ?></author>
					<?php
					endif;

					if ( $images ) :
						foreach ( $images as $image ) :
							?><enclosure url="<?php echo $image->guid; ?>" type="<?php echo $image->post_mime_type; ?>"/>
							<?php
						endforeach;
					endif;

					?><description><![CDATA[<?php echo the_excerpt_rss(); ?>]]></description>
					<content:encoded><![CDATA[<?php echo NewsFeed::text_clear( get_the_content_feed( 'rss2' ) ); ?>]]></content:encoded>
				</item>

				<?php
			endif;

		endwhile;
		?>
	</channel>
</rss>
