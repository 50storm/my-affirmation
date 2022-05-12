<?php
/**
 * Affirmation
 *
 * @package     Affirmation
 * @author      Hiroshi Igarashi
 * @license     GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: My Affirmation
 * Version: 1.0.0
 * Description: アファメーションを登録するとランダムにダッシュボードに表示されます。
 * アファメーション設定画面で登録したアファメーションの一覧がみれます。
 * Author: Hiroshi Igarashi
 * Author URI: https://github.com/50Storm
 * Plugin URI: https://github.com/50Storm/myaffirmation
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'const.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'model.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'utility.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
// use MyAffirmationUtility\Debug;
use MyAffirmationUtility\Validator;
use MyAffirmationModel\Affirmation;

function my_affirmation_enqueue_styles()
{
    wp_enqueue_style(
        MY_AFFIRMATION_STYLE_NAME_ONLY,
        MY_AFFIRMATION_CSS_FILENAME_URI,
        array(),
        filemtime(MY_AFFIRMATION_CSS_FILENAME_FULL_PATH)
    );

    wp_enqueue_script(
        MY_AFFIRMATION_PLUGIN_NAME,
        plugins_url(MY_AFFIRMATION_SCRIPT_FILENAME_FROM_INC, __FILE__)
    );
}
add_action('admin_enqueue_scripts', 'my_affirmation_enqueue_styles');

/**
 * my_affirmation_load_plugin_textdomain function
 *
 * @return void
 */
function my_affirmation_load_plugin_textdomain()
{
    load_plugin_textdomain('my-affirmation');
}
add_action('plugins_loaded', 'my_affirmation_load_plugin_textdomain');

/**
* activate_create_table function
*
* @return (int|false)
*/
function my_affirmation_activate_create_table()
{        
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . Affirmation::AFFIRMATION_TABLE_NAME;
  $sql = "CREATE TABLE $table_name (
                id int(9) NOT NULL AUTO_INCREMENT,
                affirmation varchar(255) NOT NULL DEFAULT '',
                UNIQUE KEY id (id)
               ) $charset_collate;";
  //sqlを実行
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}
register_activation_hook(__FILE__, 'my_affirmation_activate_create_table');

/**
 * show_one_affirmation function
 *
 * @param [type] $affirmaton
 * @param string $id
 * @param string $class
 * @return void
 */
function show_one_affirmation($affirmaton, $class="my-affirmation-notice") {
  echo '<div class="my-affirmation-notice-area">';
  echo '<p id="affirmation" class="' . esc_attr($class) .'">';
  echo esc_html($affirmaton);
  echo '</p>';
  echo '</div>';
}

/**
 * show affirmation
 *
 * @return void
 */
function show_affirmation_admin_notice()
{
  $my_affirmation = Affirmation::select_one_affirmation_randomly();
  if (!empty($my_affirmation)) {
    show_one_affirmation($my_affirmation[0]['affirmation']);
  }
}
if (isset($_GET['page']) && Validator::is_my_affirmation_plugin_page(sanitize_title_for_query($_GET['page']))) {
  // 表示しない
} else {
  add_action('admin_notices', 'show_affirmation_admin_notice');
}

/**
 * We need some CSS to position the paragraph
 */
function affirmation_css()
{
    echo "
 <style type='text/css'>
 .display-none {
   display:none!important;
 }
 .display-block {
  display-block:none!important;
 }

 </style>
 ";
}
add_action('admin_head', 'affirmation_css');

/**
 * Add the settings page to the menu
 */
function affirmation_menu()
{
    add_options_page(__('アファメーションプラグイン設定', 'Affirmation'), __('アファメーション設定', 'Affirmation'), 'read', 'my_affirmation', 'my_affirmation_options');
}

add_action('admin_menu', 'affirmation_menu');


/**
 * The plugin options page
 */
function my_affirmation_options()
{
    $url_show = '';
    $affirmation = '';
    $message = "";
    $id_for_show = 0;
    $css_class['add']['dispaly'] = '';
    $css_class['update']['dispaly'] = '';
    $css_class['delete']['dispaly'] = '';
    $affirmation_saved = false;
    $affirmation_updated = false;
    $affirmation_deleted = false;
    $show_add_link = false;
    $mode = 'add';
    $action = 'insert';
    if (isset($_GET['mode'])) {      
      if (!Validator::is_allowed_mode(sanitize_title_for_query($_GET['mode']))) {
        return false;
      } else {
        $mode = sanitize_title_for_query($_GET['mode']);
      }
    }
    if (isset($_GET['action'])) {      
      if (!Validator::is_allowed_action(($_GET['action']))) {
        return false;
      } else {
        $action = sanitize_title_for_query($_GET['action']);
      }
    }
    switch ($mode) {
      case 'show':
        if (!isset($_GET['id'])) {
            return false;
        }
        if (!Validator::is_number($_GET['id'])) {
            return false;
        }
        $record_affirmation = Affirmation::select_one_affirmation_by_id($_GET['id']);
        $affirmation = $record_affirmation['affirmation'];
        // 編集・削除用のID
        $id_for_show = $record_affirmation['id'];
        if ($action === 'update' && check_admin_referer('my_affirmation_options', 'my_affirmation_options_nonce')) {
            // update
            if (!isset($_POST['id']) || !$_POST['affirmation']) {
                // show
                $css_class['add']['display'] = 'display-none';
                $css_class['update']['display'] = 'display-block';
                $css_class['delete']['display'] = 'display-block';
                $show_add_link = true;
                break;
            }
            $sanitized_id = sanitize_text_field($_POST['id']);
            $sanitized_affirmation = sanitize_textarea_field($_POST['affirmation']);

            if (!Validator::is_number($sanitized_id) || !Validator::notEmptyString($sanitized_affirmation)) {
                // show
                $css_class['add']['display'] = 'display-none';
                $css_class['update']['display'] = 'display-block';
                $css_class['delete']['display'] = 'display-block';
                $show_add_link = true;
                break;
            }
            // execute updating
            $update_data['id'] = $sanitized_id;
            $update_data['affirmation'] = $sanitized_affirmation;
            $updated_id = Affirmation::update($update_data);
            $affirmation_updated = true;
            $message = "修正しました！";
            $affirmation = $sanitized_affirmation;
            $css_class['add']['display'] = 'display-none';
            $css_class['update']['display'] = 'display-block';
            $css_class['delete']['display'] = 'display-block';
            $show_add_link = true;
        } elseif ($action === 'delete' && check_admin_referer('my_affirmation_options', 'my_affirmation_options_nonce')) {
            // delete
            if (!isset($_POST['id'])) {
                // show
                $css_class['add']['display'] = 'display-none';
                $css_class['update']['display'] = 'display-block';
                $css_class['delete']['display'] = 'display-block';
                $show_add_link = true;
                break;
            }
            $sanitized_id = sanitize_text_field($_POST['id']);
            if (!Validator::is_number($sanitized_id)) {
                // show
                $css_class['add']['display'] = 'display-none';
                $css_class['update']['display'] = 'display-block';
                $css_class['delete']['display'] = 'display-block';
                $show_add_link = true;
                break;
            }
            // execute deleting data
            Affirmation::delete($sanitized_id);
            $affirmation_deleted = true;
            $message = "削除しました！";
            $css_class['add']['display'] = 'display-block';
            $css_class['update']['display'] = 'display-none';
            $css_class['delete']['display'] = 'display-none';
            $show_add_link = false;
        } else {
            // show
            $css_class['add']['display'] = 'display-none';
            $css_class['update']['display'] = 'display-block';
            $css_class['delete']['display'] = 'display-block';
            $show_add_link = true;
        }
        break;
      case 'add':
        if (!isset($_POST['affirmation'])) {
            $css_class['add']['display'] = 'display-block';
            $css_class['update']['display'] = 'display-none';
            $css_class['delete']['display'] = 'display-none';
            $show_add_link = false;
            break;
        }

        $sanitized_affirmation = sanitize_textarea_field($_POST['affirmation']);
        if (!Validator::notEmptyString($sanitized_affirmation)) {
            $css_class['add']['display'] = 'display-block';
            $css_class['update']['display'] = 'display-none';
            $css_class['delete']['display'] = 'display-none';
            $show_add_link = false;
            $message = "アファメーションを入力してください";
            break;
        }

        if (isset($_POST['affirmation']) && check_admin_referer('my_affirmation_options', 'my_affirmation_options_nonce')) {
            $insert_id = Affirmation::insert_affirmation($sanitized_affirmation);
            $affirmation_saved = true;
            $affirmation = $sanitized_affirmation;
            $message = "作成しました！";
        }
        $css_class['add']['display'] = 'display-block';
        $css_class['update']['display'] = 'display-none';
        $css_class['delete']['display'] = 'display-none';
        $show_add_link = false;
        break;
      default:
        $css_class['add']['display'] = 'display-block';
        $css_class['update']['display'] = 'display-none';
        $css_class['delete']['display'] = 'display-none';
        $show_add_link = false;
        break;
    }
    // 毎回登録データ全て取得
    $affirmations = Affirmation::select_all(); ?>
 <div class="affirmation-input-are">
   <div class="header">
    <h1>アファメーションカード</h1>
   </div>
   <?php if (!empty($message)): ?>
   <div id="message" class="message-area">
    <span class="message-text"><?php echo esc_html($message); ?></span>
   </div><!-- message -->
   <?php endif; ?>
   <?php if($affirmation_saved || $affirmation_updated): ?>
   <?php 
    show_one_affirmation($sanitized_affirmation, "my-affirmation-notice-setting-area");
   ?> 
   <?php endif; ?>
   <div class="form">
    <form id="affirmationform" method="post" action="">
    <div>
      <?php wp_nonce_field('my_affirmation_options', 'my_affirmation_options_nonce'); ?>
      <div class="input-field" >
        <!-- 入力エリア -->
        <textarea placeholder="アファメーションを書こう"
                  class="textarea-affirmation" 
                  name="affirmation" 
                  ><?php echo trim(esc_textarea($affirmation)); ?></textarea>
        <input type="hidden" id="id" name="id" value="<?php echo esc_attr($id_for_show) ; ?>" />
      </div>
      <div>
        <input type="hidden" id="mode" name="mode" value="" />
      </div>
      <!-- menu/button -->
      <div class="submit">
        <input class="button-primary button-common submit <?php echo esc_attr($css_class['add']['display']); ?>" 
              id="insertButton" 
              name="insert" 
              type="submit" 
              value="<?php echo esc_html__('作成', 'insert'); ?>"
        />  
        <input class="button-primary button-common submit <?php echo esc_attr($css_class['update']['display']); ?>" 
              id="updateButton" 
              name="update" 
              type="submit" 
              value="<?php echo esc_attr('編集', 'update'); ?>"/>
        <input class="button-primary button-common submit <?php echo esc_attr($css_class['delete']['display']); ?>" 
              id="deletButton" 
              name="delete" 
              type="submit" 
              value="<?php echo esc_attr('削除', 'delete'); ?>"/>
        </div>
        <div>
          <?php if ($show_add_link): ?>
            <span>
              <a class="button-menu " href="<?php echo esc_url('?page=my_affirmation&mode=add'); ?>">新しくアファーションを作る</a>
            </span>
          <?php endif; ?>
        </div>
        <div class="affirmation-table-area">
          <?php if (!empty($affirmations)): ?>
          <table class="affirmation-table">
            <tbody>
              <tr class="affirmation-border affirmation-table-color-style-header">
                <th class="affirmation-border" colspan="2">
                  アファメーションカード一覧
                </th>
              </tr>
              <?php
                foreach ($affirmations as $val):
                  $url_show = "?page=my_affirmation&mode=show&id=". $val['id']; ?>
              <tr class="affirmation-border affirmation-table-color-style affirmation-table-tr" >
                <td class="affirmation-border affirmation-table-td">
                  <?php echo esc_html($val['affirmation']); ?>
                </td>
                <td class="affirmation-border affirmation-table-menu">
                  <a class="button-menu " href="<?php echo esc_url($url_show); ?>">編集/削除</a>
                </td>
              </tr>
              <?php
                endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div> 
      </div>
    </div>
    </form>
   </div><!-- form -->
 </div>
 <?php
}
?>