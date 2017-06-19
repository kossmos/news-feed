<?php
/*
Plugin Name: News XML Feeds
Plugin URI: https://github.com/kossmos/news-feed/
Description: Wordpress плагин для генерации xml-фидов для новостных сервисов
Version: 2.0
Author: Юрий «kossmos» Кравчук
Author URI: https://kossmos.space
License: GPL2
*/

/*  Copyright 2016 Юрий «kossmos» Кравчук  (email : kossmos.mobile@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class NewsFeed {
	const
		FEEDS = array( 'rambler', 'yandex', 'google' ),
		POST_PER_RSS = 9999,
		DEST_PATH = '/feeds/';

	public function __construct() {
        $this->options = get_option( 'news_feeds' );

		add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_action( 'pre_get_posts', array( $this, 'before_query' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	public function add_setting_page() {
		add_options_page(
			'Генератор фидов новостных сервисов',
			'News feeds',
			'manage_options',
			'news-feeds',
			array( &$this,'admin_plugin_info' )
		);
	}

	public function before_query( $query ) {
		foreach (self::FEEDS as $value) :
			if ( $query->is_main_query() && $query->is_feed && $query->is_feed( $value ) ) {
				$query->set( 'posts_per_rss', self::POST_PER_RSS );
			}
		endforeach;
	}

	public function admin_plugin_info()	{
		?>
			<div class="wrap">
				<h2>News feeds</h2>
				<h3><?php echo get_admin_page_title(); ?></h3>

				<?php foreach (self::FEEDS as $value) : ?>
					<?php echo ucfirst($value) ?> feed url: <a target="_blank" href="<?php echo get_feed_link($value); ?>" title="Ссылка на фид"><?php echo get_feed_link($value); ?></a><br>
					<?php echo ucfirst($value) ?> static feed url: <a target="_blank" href="<?php echo get_bloginfo('url') . self::DEST_PATH . $value . '.xml'; ?>" title="Ссылка на фид"><?php echo get_bloginfo('url') . self::DEST_PATH . $value . '.xml'; ?></a><br><br>
				<?php endforeach; ?>

				<form action="options.php" method="POST">
					<?php
						settings_fields( 'news_feeds_option_group' );
						do_settings_sections( 'news-feeds' );
						submit_button();
					?>
				</form>
			</div>
		<?php
	}

	public function init() {
		foreach ( self::FEEDS as $value ) :
			add_feed( $value, function() use ( $value ) {
				// add_filter( 'pre_option_rss_use_excerpt', '__return_zero' );

				ob_start();
					load_template( plugin_dir_path( __FILE__ ) . 'templates/' . $value . '.php' );
				$output = ob_get_clean();

				$this->save_feed($output, $value); // Сохраняем фид в папку

				echo $output;
			} );
		endforeach;
	}

	/**
	 * Сохраняем фид в папку
	 */
	public function save_feed($output, $name) {
		$file = $_SERVER["DOCUMENT_ROOT"] . self::DEST_PATH . $name . '.xml';

		file_put_contents( $file, $output );
	}

	/**
	 * Настройки
	 */
	public function page_init() {
		add_settings_section(
			'news_feeds_settings',
			'Настройки фидов',
			'',
			'news-feeds'
		);

		add_settings_field(
			'logo',
			'Ссылка на логотип сайта',
			array( $this, 'logo_callback_function' ),
			'news-feeds',
			'news_feeds_settings'
		);

		add_settings_field(
			'yandex_id',
			'ID яндекс аналитики',
			array( $this, 'yandex_id_callback_function' ),
			'news-feeds',
			'news_feeds_settings'
		);

		register_setting(
			'news_feeds_option_group',
			'news_feeds',
			array( $this, 'news_feeds_sanitize_callback' )
		);

		flush_rewrite_rules();
	}

	public function news_feeds_sanitize_callback( $options ) {
		foreach ( $options as $name => & $val ) {
			if ( $name == 'logo' )
				$val = esc_url_raw( $val );

			if ( $name == 'yandex_id' )
				$val = strip_tags( $val );
		}

		return $options;
	}

	public function logo_callback_function() {
		$val = isset($this->options['logo']) ? esc_attr( $this->options['logo'] ) : ''; ?>

		<input class="regular-text" type="text" name="news_feeds[logo]" placeholder="https://www.if24.ru/" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}

	public function yandex_id_callback_function() {
		$val = isset($this->options['yandex_id']) ? esc_attr( $this->options['yandex_id'] ) : ''; ?>

		<input class="regular-text" type="text" name="news_feeds[yandex_id]" placeholder="0123456789" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}

	public static function text_clear( $text) {
		$text = html_entity_decode( $text );
		$text = preg_replace( '/\\s*\\[[^()]*\\]\\s*/', '', strip_tags( $text ) );
		$text = esc_textarea( $text );

		return $text;;
	}
}


$news_feed = new NewsFeed();
