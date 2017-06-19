<?php
/**
 *
 */
header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);


$options = get_option( 'news_feeds' );


echo '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?'.'>'; ?>
<rss xmlns:rambler="http://news.rambler.ru" version="2.0">
	<channel>
		<title><?php wp_title_rss(); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<?php while( have_posts()) : the_post(); ?>
			<item>
				<guid isPermaLink="false">2334456</guid>
				<title><?php the_title_rss(); ?></title>
				<link><?php the_permalink_rss(); ?></link>
				<pubDate><?php echo get_the_date( 'r' ); ?></pubDate>
				<description><![CDATA[<?php NewsFeed::text_clear( the_excerpt_rss() ); ?>]]></description>
				<rambler:fulltext><![CDATA[<?php echo NewsFeed::text_clear( get_the_content_feed( 'rss2' ) ); ?>]]></rambler:fulltext>
				<enclosure url="<?php echo esc_url( wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) ); ?>" type="<?php echo get_post_mime_type( get_post_thumbnail_id() ); ?>" length="123" />
			</item>
		<?php endwhile; ?>
	</channel>
</rss>
