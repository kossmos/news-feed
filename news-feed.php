<?php
/*
Plugin Name: News XML Feeds
Plugin URI: https://github.com/kossmos/news-feed/
Description: Wordpress плагин для генерации xml-фидов для новостных сервисов
Version: 2.6
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
		FEEDS = array( 'rambler', 'yandex', 'google', 'zen' ),
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
		foreach ( self::FEEDS as $value ) :

			if ( $query->is_main_query() && $query->is_feed && $query->is_feed( $value ) ) :

				$query->set( 'posts_per_rss', $this->options['count_posts_' . $value] );
				$query->set( 'nopaging', true );

				if ( $value === 'yandex' && $this->options['exclude_category'] !== '0' ) :
					$query->set( 'cat', '-' . $this->options['exclude_category'] );
				endif;

			endif;

		endforeach;
	}

	public function admin_plugin_info()	{
		?>
		<div class="wrap">
			<h2>News feeds</h2>
			<h3><?php echo get_admin_page_title(); ?></h3>

			<?php foreach ( self::FEEDS as $value ) : ?>
				<?php echo ucfirst( $value ) ?> feed url: <a target="_blank" href="<?php echo get_feed_link( $value ); ?>" title="Ссылка на фид"><?php echo get_feed_link( $value ); ?></a><br>
				<?php echo ucfirst( $value ) ?> static feed url: <a target="_blank" href="<?php echo get_bloginfo( 'url' ) . self::DEST_PATH . $value . '.xml'; ?>" title="Ссылка на фид"><?php echo get_bloginfo( 'url' ) . self::DEST_PATH . $value . '.xml'; ?></a><br><br>
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
		date_default_timezone_set( 'Europe/Moscow' );

		$this->register_acf_zen_category();

		foreach ( self::FEEDS as $value ) :
			add_feed( $value, function() use ( $value ) {
				// add_filter( 'pre_option_rss_use_excerpt', '__return_zero' );

				ob_start();
					load_template( plugin_dir_path( __FILE__ ) . 'templates/' . $value . '.php' );
				$output = ob_get_clean();

				$this->save_feed( $output, $value ); // Сохраняем фид в папку

				echo $output;
			} );
		endforeach;
	}

	/**
	 * Сохраняем фид в папку
	 */
	public function save_feed( $output, $name ) {
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

		/**
		 * Yandex Zen
		 */
		add_settings_section(
			'yandex_zen_settings',
			'Яндекс.Дзен',
			'',
			'news-feeds'
		);
		add_settings_field(
			'count_posts_zen',
			'Количество постов в фиде',
			array( $this, 'count_posts_zen_callback_function' ),
			'news-feeds',
			'yandex_zen_settings'
		);
		add_settings_field(
			'category_zen',
			'Тематика сообщений',
			array( $this, 'category_zen_callback_function' ),
			'news-feeds',
			'yandex_zen_settings'
		);

		/**
		 * Rambler News
		 */
		add_settings_section(
			'rambler_settings',
			'Рамблер новости',
			'',
			'news-feeds'
		);
		add_settings_field(
			'count_posts_rambler',
			'Количество постов в фиде',
			array( $this, 'count_posts_rambler_callback_function' ),
			'news-feeds',
			'rambler_settings'
		);

		/**
		 * Google News
		 */
		add_settings_section(
			'google_settings',
			'Google новости',
			'',
			'news-feeds'
		);
		add_settings_field(
			'count_posts_google',
			'Количество постов в фиде',
			array( $this, 'count_posts_google_callback_function' ),
			'news-feeds',
			'google_settings'
		);

		/**
		 * Yandex News
		 */
		add_settings_section(
			'yandex_settings',
			'Яндекс.Новости',
			'',
			'news-feeds'
		);
		add_settings_field(
			'count_posts_yandex',
			'Количество постов в фиде',
			array( $this, 'count_posts_yandex_callback_function' ),
			'news-feeds',
			'yandex_settings'
		);
		add_settings_field(
			'exclude_category',
			'Исключить категорию',
			array( $this, 'exclude_category_callback_function' ),
			'news-feeds',
			'yandex_settings'
		);

		/**
		 * Общие настройки
		 */
		register_setting(
			'news_feeds_option_group',
			'news_feeds',
			array( $this, 'news_feeds_sanitize_callback' )
		);

		flush_rewrite_rules();
	}

	function news_feeds_sanitize_callback( $options ) {
		foreach ( $options as $name => & $val ) {
			if ( $name == 'logo' )
				$val = esc_url_raw( $val );

			if ( $name == 'yandex_id' || $name == 'exclude_category')
				$val = strip_tags( $val );
		}

		return $options;
	}

	function logo_callback_function() {
		$val = isset($this->options['logo']) ? esc_attr( $this->options['logo'] ) : ''; ?>

		<input class="regular-text" type="text" name="news_feeds[logo]" placeholder="https://" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}

	function exclude_category_callback_function() {
		$val = isset( $this->options['exclude_category'] ) ? esc_attr( $this->options['exclude_category'] ) : '';
		$categories = get_categories();
		array_push( $categories, (object) array(
			'term_id' => '0',
			'name' => 'Нет категории'
		) );
		?>

		<select name="news_feeds[exclude_category]">
			<?php foreach( $categories as $key => $category ) : ?>
				<option value="<?php echo $category->term_id; ?>" <?php selected( $category->term_id, $val ); ?>><?php echo esc_attr( $category->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	function yandex_id_callback_function() {
		$val = isset($this->options['yandex_id']) ? esc_attr( $this->options['yandex_id'] ) : ''; ?>

		<input class="" type="text" name="news_feeds[yandex_id]" placeholder="" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}


	/**
	 * Yandex Zen
	 */
	function count_posts_zen_callback_function() {
		$val = isset($this->options['count_posts_zen']) ? esc_attr( $this->options['count_posts_zen'] ) : 50; ?>

		<input type="number" name="news_feeds[count_posts_zen]" placeholder="" min="1" max="9999" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}

	function category_zen_callback_function() {
		$val = isset( $this->options['category_zen'] ) ? $this->options['category_zen'] : '';
		?>

		<select size="8" multiple name="news_feeds[category_zen][]">
			<?php foreach( $this->get_zen_category() as $key => $category ) :
				$selected = in_array( $key, $val ) ? ' selected ' : '';
				?>

				<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo esc_attr( $category ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description">Общая тематика сообщений выводится если в посте не указана индивидуальная</p>
		<?php
	}


	/**
	 * Rambler News
	 */
	function count_posts_rambler_callback_function() {
		$val = isset( $this->options['count_posts_rambler'] ) ? esc_attr( $this->options['count_posts_rambler'] ) : 10; ?>

		<input type="number" name="news_feeds[count_posts_rambler]" placeholder="" min="1" max="9999" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}


	/**
	 * Google News
	 */
	function count_posts_google_callback_function() {
		$val = isset($this->options['count_posts_google']) ? esc_attr( $this->options['count_posts_google'] ) : 10; ?>

		<input type="number" name="news_feeds[count_posts_google]" placeholder="" min="1" max="9999" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}


	/**
	 * Yandex News
	 */
	function count_posts_yandex_callback_function() {
		$val = isset($this->options['count_posts_yandex']) ? esc_attr( $this->options['count_posts_yandex'] ) : 10; ?>

		<input type="number" name="news_feeds[count_posts_yandex]" placeholder="" min="1" max="9999" value="<?php echo $val; ?>">
		<p class="description">Обязательно поле</p>
		<?php
	}


	public static function text_clear( $text ) {
		$text = html_entity_decode( $text );
		$text = preg_replace( '/\\s*\\[[^()]*\\]\\s*/', '', strip_tags( $text ) );
		$text = trim( esc_textarea( $text ) );

		return $text;;
	}

	/**
	 * Тэг <enclosure>
	 * Это обязательный элемент для иллюстраций, аудио- и видеофайлов
	 * @param  int $id изображение
	 * @return string вывод тега
	 */
	public static function get_enclosure( $id ) {
		printf(
			'<enclosure url="%s" type="%s"/>%s',
			wp_get_attachment_image_url( $id, 'full' ),
			get_post_mime_type( $id ),
			PHP_EOL
		);
	}

	/**
	 * Подключаю поле плагина ACF для выбора тематики сообщения
	 */
	function register_acf_zen_category() {
		if ( function_exists( 'register_field_group' ) ) :

			register_field_group( array(
				'id'     => 'acf_tematika-zapisey-dlya-yandeks-dzen',
				'title'  => 'Настройки новостных фидов',
				'fields' => array(
					array(
						'key'           => 'field_59ca25e68c08b',
						'label'         => 'Тематики записи для Яндекс.Дзен',
						'name'          => 'category_zen',
						'type'          => 'select',
						'instructions' 	=> 'Выберите тематики для данной записи. Если тематики не выбраны, то для записи используется глобальная настройка.',
						'required'      => 0,
						'choices'       => $this->get_zen_category(),
						'default_value' => '',
						'allow_null'    => 1,
						'multiple'      => 1,
					),
					array (
						'key'          => 'field_59ce21d576b0a',
						'label'        => 'Исключить запись из фидов',
						'name'         => 'feeds_exclude_post',
						'type'         => 'select',
						'instructions' => 'Выберите фиды из которых нужно исключить данную запись',
						'choices'      => $this->get_list_feeds(),
						'default_value' => '',
						'allow_null'    => 1,
						'multiple'      => 1,
					),
				),
				'location' => array(
					array(
						array (
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
							'order_no' => 0,
							'group_no' => 0,
						),
					),
				),
				'options' => array(
					'position'       => 'side',
					'layout'         => 'default',
					'hide_on_screen' => array(),
				),
				'menu_order' => 0
			));

		endif;
	}

	function get_zen_category() {
		return array(
			'incidents'    => 'Происшествия',
			'policy'       => 'Политика',
			'war'          => 'Война',
			'society'      => 'Общество',
			'economy'      => 'Экономика',
			'sport'        => 'Спорт',
			'technologies' => 'Технологии',
			'science'      => 'Наука',
			'games'        => 'Игры',
			'music'        => 'Музыка',
			'literature'   => 'Литература',
			'cinema'       => 'Кино',
			'culture'      => 'Культура',
			'fashion'      => 'Мода',
			'celebrities'  => 'Знаменитости',
			'psychology'   => 'Психология',
			'health'       => 'Здоровье',
			'auto'         => 'Авто',
			'house'        => 'Дом',
			'hobby'        => 'Хобби',
			'food'         => 'Еда',
			'design'       => 'Дизайн',
			'photo'        => 'Фотографии',
			'humor'        => 'Юмор',
			'nature'       => 'Природа',
			'travels'      => 'Путешествия'
		);
	}

	function get_list_feeds() {
		$array = array();

		foreach ( self::FEEDS as $value ) :
			$array[ $value ] = ucfirst( $value );
		endforeach;

		return $array;
	}
}


$news_feed = new NewsFeed();
