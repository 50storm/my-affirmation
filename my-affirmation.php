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
 * Version: 0.9.0
 * Description: アファメーションを登録するとランダムにダッシュボードに表示されます
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
use MyAffirmationModel\Affimation;

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

register_activation_hook(__FILE__, array('Affimation', 'activate_create_table'));

/**
 * show affirmation
 *
 * @return void
 */
function show_affirmation_admin_notice()
{
    $my_affirmation = Affimation::select_one_affirmation_randomly();
    if (!empty($my_affirmation)) {
        echo '<div class="my-affirmation-notice-area">';
        echo '<p id="affirmation" class="my-affirmation-notice">';
        echo esc_html($my_affirmation[0]['affirmation']);
        echo '</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'show_affirmation_admin_notice');

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
    // Debug::debug_vars("test");
    $affirmation_saved = false;
    $affirmation_updated = false;
    $affirmation_deleted = false;
    $url_show = '';
    $affirmation = '';
    $message = "";
    $id_for_show = 0;
    $css_class['add']['dispaly'] = '';
    $css_class['update']['dispaly'] = '';
    $css_class['delete']['dispaly'] = '';
    $show_add_link = false;
    $mode = $_GET['mode'] ?? 'add';
    $action = $_GET['action'] ?? 'insert';
    if (!Validator::is_allowed_mode($mode)) {
      return false;
    }
    if (!Validator::is_allowed_action($action)) {
      return false;
    }

    
    switch ($mode) {
      case 'show':
        if (!isset($_GET['id'])) {
          return false;
        }
        if ( !Validator::is_number($_GET['id']) ) {
          return false;
        }
        $record_affirmation = Affimation::select_one_affirmation_by_id($_GET['id']);
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
          $updated_id = Affimation::update($update_data);
          $affirmation_updated = true;
          $affirmation = $_POST['affirmation'];
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
          Affimation::delete($sanitized_id);
          $affirmation_deleted = true;
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
          $insert_id = Affimation::insert_affirmation($sanitized_affirmation);
          $affirmation_saved = true;
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
    $affirmations = Affimation::select_all();

    if ($affirmation_saved) {
        $message = "作成しました！";
    } elseif ($affirmation_updated) {
        $message = "修正しました！";
    } elseif ($affirmation_deleted) {
        $message = "削除しました！";
    }
?>
 <div class="affirmation-input-are">
   <div class="header">
    <h1>アファメーションカード</h1>
   </div>
   <?php if(!empty($message)): ?>
   <div id="message" class="message-area">
    <span class="message-text"><?php echo $message; ?></span>
   </div><!-- message -->
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
        <input type="hidden" id="id" name="id" value="<?php echo $id_for_show ; ?>" />
      </div>
      <div>
        <input type="hidden" id="mode" name="mode" value="" />
      </div>
      <!-- menu/button -->
      <div class="submit">
        <input class="button-primary button-common submit <?php echo $css_class['add']['display']; ?>" 
              id="insertButton" 
              name="insert" 
              type="submit" 
              value="<?php echo esc_html__('作成', 'insert'); ?>"
        />  
        <input class="button-primary button-common submit <?php echo $css_class['update']['display']; ?>" 
              id="updateButton" 
              name="update" 
              type="submit" 
              value="<?php echo esc_html__('編集', 'update'); ?>"/>
        <input class="button-primary button-common submit <?php echo $css_class['delete']['display']; ?>" 
              id="deletButton" 
              name="delete" 
              type="submit" 
              value="<?php echo esc_html__('削除', 'delete'); ?>"/>
        </div>
        <div>
          <?php if ($show_add_link): ?>
            <span>
              <a class="button-menu " href="<?php echo esc_html__('?page=my_affirmation&mode=add'); ?>">新しくアファーションを作る</a>
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
                  <?php echo esc_html__($val['affirmation']); ?>
                </td>
                <td class="affirmation-border affirmation-table-menu">
                  <a class="button-menu " href="<?php echo esc_html__($url_show); ?>">編集/削除</a>
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