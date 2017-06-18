<?php
/**
 * @url https://yandex.ru/support/news/info-for-mass-media.html
 * @url https://partner.news.yandex.ru/public/documents/tech.pdf
 */

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

function text_clear( $text) {
	$text = html_entity_decode( $text );
	$text = preg_replace( '/\\s*\\[[^()]*\\]\\s*/', '', strip_tags( $text ) );
	$text = htmlspecialchars( $text );

	return $text;;
}

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
	<channel>
		<title><?php wp_title_rss(); ?></title>
		<link><?php bloginfo_rss('url'); ?></link>
		<description><?php bloginfo_rss("description"); ?></description>
		<?php while( have_posts()) : the_post(); ?>
			<item turbo="true">
				<title><?php the_title_rss(); ?></title>
				<link><?php the_permalink_rss(); ?></link>
				<pubDate><?php echo get_the_date('r'); ?></pubDate>
				<description><?php text_clear(the_excerpt_rss()); ?></description>
				<yandex:full-text><?php echo text_clear(get_the_content_feed('rss2')); ?></yandex:full-text>
				<enclosure url="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ); ?>" type="<?php echo get_post_mime_type( get_post_thumbnail_id() ); ?>" />
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
