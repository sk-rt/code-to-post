<?php
/**
 * Plugin Name:     Code To Post
 * Plugin URI:      ''
 * Description:     Import static Html to post content
 * Author:          Ryuta Sakai
 * Author URI:      https://github.com/sk-rt
 * Text Domain:     code-to-post
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         CodeToPost
 */
add_action('init', 'CodeToPost::init');

class CodeToPost
{
    const VERSION = '0.1.0';
    const PLUGIN_ID = 'code-to-post';
    const TEXT_DOMAIN = self::PLUGIN_ID;
    const MAIN_MENU_SLUG = self::PLUGIN_ID;
    const CREDENTIAL_SAVE_ACTION = self::PLUGIN_ID . '-nonce-save-action';
    const CREDENTIAL_SAVE = self::PLUGIN_ID . '-nonce-save-key';
    const CREDENTIAL_RUN_ACTION = self::PLUGIN_ID . '-nonce-run-action';
    const CREDENTIAL_RUN = self::PLUGIN_ID . '-nonce-run-key';
    const DB_PREFIX = self::PLUGIN_ID . '_';
    const DB_SLUG_PATH = 'src-path';
    const TRANSIENT_KEY_COMPLETE = self::DB_PREFIX . '_complete';
    public static function init()
    {
        return new self();
    }

    public function __construct()
    {
        if (is_admin() && is_user_logged_in()) {
            // メニュー追加
            add_action('admin_menu', [$this, 'set_plugin_menu']);
            add_action('admin_init', [$this, 'save_config']);
            add_action('admin_init', [$this, 'run_update']);
            add_action('admin_notices', [$this, 'show_save_config_message']);

        }
    }

    public function set_plugin_menu()
    {
        add_menu_page(
            'Code To Post',
            'Code To Post',
            'manage_options',
            self::MAIN_MENU_SLUG, /* URL */
            [$this, 'show_config_form'], /* callback関数 */
            'dashicons-text', /*  see: https://developer.wordpress.org/resource/dashicons/#awards */
            99/* 表示位置 */
        );
    }

    public function show_config_form()
    {
        // ① wp_optionsのデータをひっぱってくる
        $src_path = get_option(self::DB_PREFIX . self::DB_SLUG_PATH);

        ?>

		<div class="wrap">
		  <h1>Code to Post</h1>
		  <?php do_action(self::PLUGIN_ID . '_veiw_top');?>
			<hr>
			<div class="ctp__block">
				<h2><?php _e("Config", self::TEXT_DOMAIN)?></h2>
				<form action="" method='post' id="config">
				<?php wp_nonce_field(self::CREDENTIAL_SAVE_ACTION, self::CREDENTIAL_SAVE)?>
				<p>
					<label for="<?=self::DB_SLUG_PATH?>"><strong><?php _e("Path:", self::TEXT_DOMAIN)?></strong></label>
					<?=ABSPATH?>
					<input type="text" name="<?=self::DB_SLUG_PATH?>" value="<?=$src_path?>" size="80" />
				</p>
				<p><input type='submit' value='<?php _e("Save Config", self::TEXT_DOMAIN)?>' class='button button-primary button-large'></p>
				</form>
			</div>
			<hr>
			<div class="ctp__block">
				<h2><?php _e("Run Update", self::TEXT_DOMAIN)?></h2>
				<form action="" method='post' id="run">
				<?php wp_nonce_field(self::CREDENTIAL_RUN_ACTION, self::CREDENTIAL_RUN)?>
				<p><input type='submit' value='<?php _e("Update to Post", self::TEXT_DOMAIN)?>' class='button button-primary button-large'></p>
				</form>
			</div>
			<hr>
			<?php do_action(self::PLUGIN_ID . '_veiw_bottom');?>

		</div>
  <?php
}
    public function save_config()
    {
        if (isset($_POST[self::CREDENTIAL_SAVE]) && $_POST[self::CREDENTIAL_SAVE]) {
            if (check_admin_referer(self::CREDENTIAL_SAVE_ACTION, self::CREDENTIAL_SAVE)) {

                $path = isset($_POST[self::DB_SLUG_PATH]) ? $_POST[self::DB_SLUG_PATH] : "";
                update_option(self::DB_PREFIX . self::DB_SLUG_PATH, $path);
                $completed_text = __("設定の保存が完了しました。", self::TEXT_DOMAIN);
                set_transient(self::TRANSIENT_KEY_COMPLETE, $completed_text, 5);
            }
        }
    }
    public function run_update()
    {
        if (isset($_POST[self::CREDENTIAL_RUN]) && $_POST[self::CREDENTIAL_RUN]) {
            if (check_admin_referer(self::CREDENTIAL_RUN_ACTION, self::CREDENTIAL_RUN)) {

                $src_path = ABSPATH . get_option(self::DB_PREFIX . self::DB_SLUG_PATH);
                foreach (glob($src_path . "*") as $filename) {
                    $post_type = urlencode(self::util_mb_basename($filename));
                    $post_type_obj = get_post_type_object($post_type);
                    if (!is_dir($filename) || !$post_type_obj) {
                        continue;
                    }
                    foreach (glob($filename . '/*.html') as $targetfile) {
                        $title = esc_html(self::util_mb_basename($targetfile, '.html'));
                        $slug = sanitize_title($title);
                        $content = file_get_contents($targetfile);
                        $post_id;
                        $is_update = false;
                        $arg = array(
                            'name' => $slug,
                            'post_type' => $post_type,
                            'posts_per_page' => 1,
                        );
                        // var_dump($arg);
                        $target_posts = get_posts($arg);
                        if ($target_posts && $target_posts[0]) {
                            $post_id = wp_update_post(array(
                                'ID' => $target_posts[0]->ID,
                                'post_content' => $content,
                            ));
                            $is_update = true;
                        } else {
                            $post_id = wp_insert_post(array(
                                'post_title' => $title,
                                'post_type' => $post_type,
                                'post_name' => $slug,
                                'post_content' => $content,
                                'post_status' => 'publish',
                            ));
                        };
                        if ($post_id) {
                            add_action(self::PLUGIN_ID . '_veiw_top', function () use ($is_update, $post_id, $post_type_obj) {
                                self::get_update_message($is_update, $post_id, $post_type_obj);
                            }, 10, 0);
                        }
                    }

                }

            }
        }
    }
    public function show_save_config_message()
    {
        if ($complete_message = get_transient(self::TRANSIENT_KEY_COMPLETE)) {
            ?>
			<div class="notice notice-success is-dismissible">
				<p><?=$complete_message?></p>
			</div>
		<?php
};
    }
    public function get_update_message($is_update, $post_id, $post_type_obj)
    {
        $label = $is_update ? __("Updated", self::TEXT_DOMAIN) : __("New Posted", self::TEXT_DOMAIN);
        $target_post = get_post($post_id);
        ?>
		<div class="updated">
			<h3 style="margin-bottom:0.2em"><?=$label . ': "' . esc_html($target_post->post_title)?>"</h3>
			<p>
				<span style="background:#999;color:#fff;border-radius:4px;padding:2px 8px;"><?php echo esc_html($post_type_obj->labels->name); ?></span>
			</p>
			<p>
				<strong>ID: </strong><span><?=$target_post->ID?></span>,
				<strong>Slug: </strong><span><?=esc_html($target_post->post_name)?></span><br>
				<a href="<?=get_edit_post_link($target_post->ID)?>"><?php _e("Edit")?></a> | <a href="<?=get_permalink($target_post->ID)?>"><?php _e("View")?></a>
			</p>
		</div>
		<?php
}
    public function util_mb_basename($str, $suffix = null)
    {
        $tmp = preg_split('/[\/\\\\]/', $str);
        $res = end($tmp);
        if (strlen($suffix)) {
            $suffix = preg_quote($suffix);
            $res = preg_replace("/({$suffix})$/u", "", $res);
        }
        return $res;
    }

}

?>
