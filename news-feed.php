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
	private $rambler_feed_name = 'rambler';
	private $folder = '/feeds/';

	public function __construct() {
		add_action('admin_menu', array($this, 'add_setting_page'));
		add_action('pre_get_posts', array($this, 'before_query'));
		add_action('init', array($this, 'init'));
	}

	function add_setting_page() {
		add_submenu_page('tools.php', 'Генератор фидов новостных сервисов', 'News feeds', 'manage_options', 'news-feeds', array(&$this,'admin_plugin_info'));
	}

	public function before_query($query) {
		if ($query->is_main_query() && $query->is_feed && $query->is_feed($this->rambler_feed_name)) {
			$query->set('posts_per_rss', 9999);
		}
	}

	public function admin_plugin_info()	{
		?>
			<div class="wrap">
				<h2>News feeds</h2><br>

				Rambler feed url: <a target="_blank" href="<?php echo get_feed_link($this->rambler_feed_name); ?>" title="Ссылка на фид"><?php echo get_feed_link($this->rambler_feed_name); ?></a><br>
				Rambler static feed url: <a target="_blank" href="<?php echo get_bloginfo('url') . $this->folder . $this->rambler_feed_name . '.xml'; ?>" title="Ссылка на фид"><?php echo get_bloginfo('url') . $this->folder . $this->rambler_feed_name . '.xml'; ?></a>
			</div>
		<?php
	}

	public function init() {
		add_feed($this->rambler_feed_name, array($this, 'do_feed'));
	}

	public function do_feed() {
		add_filter('pre_option_rss_use_excerpt', '__return_zero');

		ob_start();
		load_template(plugin_dir_path( __FILE__ ) . 'rambler.php');
		$output = ob_get_clean();

		$this->save_feed($output);
		echo $output;
	}

	public function save_feed($output) {
		$file = $_SERVER["DOCUMENT_ROOT"] . $this->folder . $this->rambler_feed_name . '.xml';

		file_put_contents($file, $output);
	}
}


$news_feed = new NewsFeed();