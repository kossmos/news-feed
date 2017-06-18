<?php
/*
Plugin Name: News XML Feeds
Plugin URI: https://github.com/kossmos/news-feed/
Description: Wordpress плагин для генерации xml-фидов для новостных сервисов
Version: 1.0
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
		FEEDS = array( 'rambler', 'yandex', 'goog' ),
		POST_PER_RSS = 9999,
		DEST_PATH = '/feeds/';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_setting_page' ) );
		add_action( 'pre_get_posts', array( $this, 'before_query' ) );
		add_action( 'init', array( $this, 'init' ) );
	}

	function add_setting_page() {
		add_submenu_page( 'tools.php', 'Генератор фидов новостных сервисов', 'News feeds', 'manage_options', 'news-feeds', array( &$this,'admin_plugin_info' ) );
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
				<h2>News feeds</h2><br>

				<?php
					global $wp_rewrite;

					echo "<pre>";
					var_dump(ot_get_option('logo'));
					// var_dump(plugin_dir_path( __FILE__ ) . 'templates/' . $value . '.php');
					// var_dump($wp_rewrite->feeds);
					echo "</pre>";
				?>

				<?php foreach (self::FEEDS as $value) : ?>
					<?php
					echo "<pre>";
						var_dump(  );
					echo "</pre>";

					?>
					<?php echo ucfirst($value) ?> feed url: <a target="_blank" href="<?php echo get_feed_link($value); ?>" title="Ссылка на фид"><?php echo get_feed_link($value); ?></a><br>
					<?php echo ucfirst($value) ?> static feed url: <a target="_blank" href="<?php echo get_bloginfo('url') . self::DEST_PATH . $value . '.xml'; ?>" title="Ссылка на фид"><?php echo get_bloginfo('url') . self::DEST_PATH . $value . '.xml'; ?></a><br><br>
				<?php endforeach; ?>
			</div>
		<?php
	}

	public function init() {
		foreach ( self::FEEDS as $value ) :
			// file_put_contents( $_SERVER["DOCUMENT_ROOT"] . self::DEST_PATH . $name . '.log', var_export( file_exists( plugin_dir_path( __FILE__ ) . 'templates/' . $value . '.php' ) ) );

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
}


$news_feed = new NewsFeed();
