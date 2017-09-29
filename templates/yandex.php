<?php
/**
 * @url https://yandex.ru/support/news/info-for-mass-media.html
 * @url https://partner.news.yandex.ru/public/documents/tech.pdf
 */

header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );


$options = get_option( 'news_feeds' );


echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
	<channel>
		<title><?php wp_title_rss(); ?></title>
		<link><?php bloginfo_rss('url'); ?></link>
		<description><?php bloginfo_rss("description"); ?></description>
		<?php if ( ! empty( $options['yandex_id'] ) ) : ?>
		<yandex:analytics id="<?php echo $options['yandex_id']; ?>" type="Yandex"></yandex:analytics>
		<?php endif; ?>
		<yandex:analytics type="LiveInternet"></yandex:analytics>
		<?php while( have_posts() ) : the_post();

			if ( get_post_type() === 'post' ) :

				/**
				 * Исключение записи
				 */
				$exclude_feed = get_field( 'feeds_exclude_post' );
				if ( in_array( basename( __FILE__, '.php' ), $exclude_feed ) ) continue;


				/**
				 * Получаем основной контент записи
				 */
				$content = apply_filters( 'the_content_feed', wpautop( do_shortcode( get_post_field( 'post_content', get_the_ID() ) ) ), 'rss2' );
				?><item>
					<title><?php the_title_rss(); ?></title>
					<link><?php the_permalink_rss(); ?></link>
					<pubDate><?php echo get_the_date( 'r' ); ?></pubDate>
					<description><?php NewsFeed::text_clear( the_excerpt_rss() ); ?></description>
					<?php
					if ( has_post_thumbnail() ) :
						NewsFeed::get_enclosure( get_post_thumbnail_id() );
					endif;
					?>
					<yandex:full-text><?php

						$patterns = array(
							'/<p><img(.*?)\/><\/p>/s',
							'/<p><figure(.*?)>(.*?)<\/figure><\/p>/s',
							'/<figure(.*?)>(.*?)<\/figure>/s',
							'/<p>https:\/\/youtu.*?<\/p>/i',
							'/<p>https:\/\/www.youtu.*?<\/p>/i'
						);
						$replacements = '';
						$content = preg_replace( $patterns, $replacements, $content );

						$content = NewsFeed::text_clear( $content );

						echo $content;
					?></yandex:full-text>
				</item><?php

			endif;

		endwhile;
	?></channel>
</rss>
