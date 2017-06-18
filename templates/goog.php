<?php
/**
 * @url https://support.google.com/news/publisher/answer/1407682
 */

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );

function text_clear( $text) {
	$text = html_entity_decode( $text );
	$text = preg_replace( '/\\s*\\[[^()]*\\]\\s*/', '', strip_tags( $text ) );
	$text = htmlspecialchars( $text );

	return $text;;
}

echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?'.'>';
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<channel>
		<title><?php wp_title_rss(); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<image>
			<url>http://www.ссылка_на_логотип.png</url>
			<title>Всплывающая подсказка для логотипа</title>
			<link>http://www.ссылка_для_перехода_по_нажатию_на_логотип.ru</link>
		</image>
		<lastBuildDate>Дата</lastBuildDate>
		<?php while( have_posts()) : the_post(); ?>
			<item>
				<title><?php the_title_rss(); ?></title>
				<link><?php the_permalink_rss(); ?></link>
				<description><?php text_clear( the_excerpt_rss() ); ?></description>
				<dc:creator><?php the_author(); ?></dc:creator>
				<pubDate><?php echo get_the_date('r'); ?></pubDate>
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
