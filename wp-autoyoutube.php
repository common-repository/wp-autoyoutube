<?php
/*
Plugin Name: WP-AutoYoutube
Plugin URI: http://wordpress.menorcadev.com/plugin/wp-autoyoutube/
Description: Import your uploads and favorites videos from Youtube in the easiest way.
Author: Pau Capó
Version: 0.3.1
Author URI: http://capo.cat/
Text Domain: wp-autoyoutube
*/
/*  Copyright 2012  Pau Capó Pons  (email : pau@capo.cat)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$sidebar['plugin'] = 'wp-autoyoutube';
$sidebar['author_name'] = 'Pau Capó';
$sidebar['author_url'] = 'capo.cat';
$sidebar['author_twitter'] = 'pau_capo';

if (!defined('WP_CONTENT_URL')) die('no direct access');

register_activation_hook(__FILE__, 'autoyoutube_activation');
function autoyoutube_activation() {
   $autoyoutube = get_option('autoyoutube', array());
   if (!isset($autoyoutube['template'])) $autoyoutube['template'] = "{title} ({time})\n{video}\n{description}";
   update_option('autoyoutube', $autoyoutube);

   if ($autoyoutube['auto_wp'] == 'on') wp_schedule_event(time(), 'hourly', 'autoyoutube_event');
}
register_deactivation_hook(__FILE__, 'autoyoutube_deactivation');
function autoyoutube_deactivation() {
   if ($autoyoutube['auto_wp'] == 'on') wp_clear_scheduled_hook('autoyoutube_event');
}




function autoyoutube_init() {
   $autoyoutube = get_option('autoyoutube', array());
   $plugin_dir = basename(dirname(__FILE__));
   load_plugin_textdomain( 'wp-autoyoutube', false, $plugin_dir.'/lang');
   if ($autoyoutube['auto'] == 'on' && $autoyoutube['auto_wp'] != 'on' && isset($_GET['autoyoutube'])) {
      autoyoutube_cron();
      exit;
   }
}
add_action('init', 'autoyoutube_init');


function autoyoutube_page() {
   autoyoutube_post_form();
}

// Admin menú
function autoyoutube_adminmenu() {
   add_submenu_page('edit.php', 'AutoYoutube', 'AutoYoutube', 'edit_posts', 'autoyoutube', 'autoyoutube_page');

   // add to options admin menu
   if (current_user_can('manage_options'))
      add_options_page('AutoYoutube', 'AutoYoutube', 1, __FILE__, 'autoyoutube_adminpage');
}
add_action('admin_menu', 'autoyoutube_adminmenu');

function autoyoutube_adminpage() {
   if (!current_user_can('manage_options')) return;
   /*
    *  Settings page
    */
?>
<div class="wrap">
   <div id="icon-options-general" class="icon32"><br /></div>
   <h2>AutoYoutube</h2>
   <div class="metabox-holder has-right-sidebar">
      <div class="inner-sidebar">
         <?php include 'support.php'; ?>
      </div>
      <div class="has-sidebar sm-padded">
         <div id="post-body-content" class="has-sidebar-content">
            <div class="meta-box-sortabless">
               <?php include 'settings.php'; ?>
            </div>
         </div>
      </div>
      <br style="clear:both" />
   </div>
</div>
<?php
}

// Settings link
function autoyoutube_settings_link($links, $file) {
   if ($file == plugin_basename(__FILE__)) {
      $settings_link = '<a href="options-general.php?page='.plugin_basename(__FILE__).'" title="'.__('Settings').'">'.__('Settings').'</a>';
      array_unshift($links, $settings_link);
   }
   return $links;
}
add_filter('plugin_action_links', 'autoyoutube_settings_link', 10, 2);

// Shortcode [autoyoutube id="XXXX"]
function autoyoutube_shortcode($atts) {
   if (isset($atts['id']))
      return '
         <p style="text-align: center" id="vid_'.$atts['id'].'">
            <img src="http://i.ytimg.com/vi/'.$atts['id'].'/0.jpg" onclick="autoyoutube_play(\''.$atts['id'].'\')" style="cursor:pointer" />
         </p>';
}
add_shortcode('autoyoutube', 'autoyoutube_shortcode');
function autoyoutube_script() {
   ?>
   <script type="text/javascript">
      function autoyoutube_play(id) {
         html = '<iframe width="480" height="385" src="http://www.youtube.com/embed/'+id+'?fs=1&amp;feature=oembed&amp;autoplay=1" frameborder="0" allowfullscreen=""></iframe>';
         document.getElementById('vid_'+id).innerHTML = html;
      }
   </script>
<?php
}
add_action('wp_head', 'autoyoutube_script');

// Tab menu
function autoyoutube_media_upload_tab($tabs) {
	$newtab = array('autoyoutube' => 'AutoYoutube');
	return array_merge($tabs, $newtab);
}
add_filter('media_upload_tabs', 'autoyoutube_media_upload_tab');


// Tab iframe
function autoyoutube_media_menu_handle() {
   return wp_iframe('media_autoyoutube_process');
}
add_action('media_upload_autoyoutube', 'autoyoutube_media_menu_handle');

function media_autoyoutube_process() {
   media_upload_header();
   autoyoutube_media_form();
}

function autoyoutube_post($v) {
   $autoyoutube = get_option('autoyoutube', array());

   $content = $autoyoutube['template'];
   if ($autoyoutube['wordpress'] == 'on') {
      $content = str_replace('{video}', 'http://www.youtube.com/watch?v='.$v['id'], $content);
   } else {
      $content = str_replace('{video}', '[autoyoutube id="'.$v['id'].'"]', $content);
   }
   $content = str_replace('{description}', $v['description'], $content);
   $content = str_replace('{title}', $v['title'], $content);
   $content = str_replace('{time}', $v['time'], $content);
   $content = str_replace('{category}', $v['category'], $content);
   $content = str_replace('{tags}', $v['tags'], $content);
   $content = str_replace('{author}', $v['author'], $content);
   $content = str_replace('{id}', $v['id'], $content);
   $content = str_replace('{image}', $v['image'], $content);

   $my_post = array(
      'post_title' => $v['title'],
      'post_content' => $content,
      'post_status' => $autoyoutube['status'],
      'post_category' => array($autoyoutube['category']),
      'tags_input' => ($autoyoutube['tags'] == 1 ? $v['tags'] : '')
   );
   $postID = wp_insert_post($my_post);

   update_post_meta($postID, 'autoyoutube', $v['id']);

   return $postID;
}

function autoyoutube_post_form() {
   $videos = autoyoutube_get_videos();

   if (isset($_GET['vid']) && (isset($videos['uploads'][$_GET['vid']]) || isset($videos['favorites'][$_GET['vid']]))) {
      $v = (isset($videos['uploads'][$_GET['vid']]) ? $videos['uploads'][$_GET['vid']] : $videos['favorites'][$_GET['vid']]);

      $postID = autoyoutube_post($v);

      $drafturl = admin_url('post.php?post='.$postID.'&action=edit');
   }

   ?>
   <div class="wrap">
      <div id="icon-edit" class="icon32 icon32-posts-post"><br /></div>
      <h2>AutoYoutube Videos</h2>
      <?php if (isset($drafturl)) :
         echo '<div class="updated settings-error"><p>';
         if (isset($_GET['publish']))
            echo __('Post published.', 'wp-autoyoutube');
         else
            echo __('Post saved as draft.', 'wp-autoyoutube');
         echo ' <a href="'.$drafturl.'">'.__('See the post.', 'wp-autoyoutube').'</a></p></div>';
      endif; ?>
      <script type="text/javascript">
         var autoyoutube_vid = '';
         function autoyoutube_show(type) {
            if (autoyoutube_vid != '') {
               autoyoutube_close(autoyoutube_vid);
               autoyoutube_vid = '';
            }
            if (type == 'favorites') {
               jQuery('.autoyoutube_uploads').css('display', 'none');
               jQuery('.autoyoutube_favorites').css('display', 'table-row');
               jQuery('#autoyoutube_uploads_bt').removeClass('current');
               jQuery('#autoyoutube_favorites_bt').addClass('current');
            } else {
               jQuery('.autoyoutube_uploads').css('display', 'table-row');
               jQuery('.autoyoutube_favorites').css('display', 'none');
               jQuery('#autoyoutube_uploads_bt').addClass('current');
               jQuery('#autoyoutube_favorites_bt').removeClass('current');
            }
            return false;
         }
         function autoyoutube_play(id) {
            if (autoyoutube_vid != '') {
               autoyoutube_close(autoyoutube_vid);
               if (id == autoyoutube_vid) {
                  autoyoutube_vid = '';
                  return false;
               }
            }
            autoyoutube_vid = id;
            document.getElementById('row_'+id).style.display = 'table-row';
            document.getElementById('vid_'+id).innerHTML = '<iframe width="480" height="385" src="http://www.youtube.com/embed/'+id+'?fs=1&amp;feature=oembed&amp;autoplay=1" frameborder="0" allowfullscreen=""></iframe>';
            return false;
         }
         function autoyoutube_close(id) {
            document.getElementById('row_'+id).style.display = 'none';
            document.getElementById('vid_'+id).innerHTML = '';
         }
      </script>
      <ul class="subsubsub">
         <li>
            <a href="#" onclick="return autoyoutube_show('uploads')" class="current" id="autoyoutube_uploads_bt">
               <?php _e('Uploads', 'wp-autoyoutube'); ?> <span class="count">(<?php echo count($videos['uploads']); ?>)</span>
            </a>
            |
         </li>
         <li>
            <a href="#" onclick="return autoyoutube_show('favorites')" id="autoyoutube_favorites_bt">
               <?php _e('Favorites', 'wp-autoyoutube'); ?> <span class="count">(<?php echo count($videos['favorites']); ?>)</span>
            </a>
         </li>
      </ul>
      <table class="wp-list-table widefat fixed posts" cellspacing="0">
         <thead>
            <tr>
               <th scope="col" id="image" class="manage-column" style="width: 60px;">&nbsp;</th>
               <th scope="col" id="title" class="manage-column"><?php _e('Title', 'wp-autoyoutube'); ?></th>
               <th scope="col" id="tags" class="manage-column"><?php _e('Description', 'wp-autoyoutube'); ?></th>
               <th scope="col" id="time" class="manage-column" style="width: 80px;"><?php _e('Time', 'wp-autoyoutube'); ?></th>
               <!--<th scope="col" id="actions" class="manage-column"><?php _e('Actions', 'wp-autoyoutube'); ?></th>-->
            </tr>
         </thead>

         <tbody id="the-list">
         <?php $n = 0; foreach ($videos as $cat => $vids) : ?>
         <?php foreach ($vids as $v) : ?>
            <tr class="autoyoutube_<?php echo $cat; ?> <?php echo ($n % 2 == 0 ? 'alternate' : ''); ?>" style="<?php echo ($cat != 'uploads' ? 'display:none;' : ''); ?>">
               <td>
                  <a href="#" onclick="return autoyoutube_play('<?php echo $v['id']; ?>')">
                     <img src="<?php echo $v['image']; ?>" alt="<?php echo $v['description']; ?>" style="width: 50px; vertical-align: middle;" />
                  </a>
               </td>
               <td class="post-title">
                     <?php
                     $status = array(
                           'draft' => __('Draft', 'wp-autoyoutube'),
                           'pending' => __('Pending Review', 'wp-autoyoutube'),
                           'future' => __('Scheduled', 'wp-autoyoutube'),
                        );
                     $posts = autoyoutube_post_exists($v['id']);
                     $actions = '';
                     $title = $v['title'];
                     if ($posts != false) {
                        foreach($posts as $p) {
                           if ($p->post_status != 'trash') {
                              if ($p->post_status != 'trash') {
                                 $actions .= ' <a href="'.admin_url('post.php?post='.$p->ID.'&action=edit').'" title="'.$p->post_title.'">'.__('Edit', 'wp-autoyoutube').'</a> |';
                                 $title = ' <a class="row-title" href="'.admin_url('post.php?post='.$p->ID.'&action=edit').'" title="'.$p->post_title.'">'.$v['title'].'</a>';
                              }

                              if ($p->post_status == 'publish')
                                 $actions .= ' <a href="'.get_permalink($p->ID).'" target="_blank" title="'.$p->post_title.'">'.__('View', 'wp-autoyoutube').'</a>';
                              else {
                                 $actions .= ' <a href="'.get_home_url('/').'?p='.$p->ID.'&amp;preview=true" target="wp-preview" title="'.$p->post_title.'">'.__('Preview', 'wp-autoyoutube').'</a>';
                                 $title .= ' - '.$status[$p->post_status];
                              }

                           } else {
                              $actions .= '<a href="'.admin_url('edit.php?post_status=trash&amp;post_type=post').'">'.__('Trash', 'wp-autoyoutube').'</a>';
                           }
                        }
                     } else {
                        $actions = '<a href="'.admin_url('edit.php?page=autoyoutube&vid='.$v['id']).'">'.__('Import', 'wp-autoyoutube').'</a>';
                     } ?>
                  <strong><?php echo $title; ?></strong>
                  <div class="row-actions">
                     <?php echo $actions; ?>
                  </div>
               </td>
               <td><?php echo $v['description']; ?></td>
               <td><?php echo $v['time']; ?></td>
            </tr>
            <tr id="row_<?php echo $v['id']; ?>" style="text-align: center;display:none;">
               <td colspan="4" id="vid_<?php echo $v['id']; ?>"></td>
            </tr>
         <?php $n++; endforeach; ?>
         <?php endforeach; ?>
         </tbody>
      </table>
   </div>
   <?php
}

function autoyoutube_media_form() {
   $videos = autoyoutube_get_videos();
   $autoyoutube = get_option('autoyoutube', array());

   if (count($videos) > 0) {
   ?>
      <script type="text/javascript">
         var autoyoutube_vid = '';
         function autoyoutube_insert(id) {
            <?php if ($autoyoutube['wordpress'] == 'on') : ?>
            id = 'http://www.youtube.com/watch?v='+id;
            <?php else : ?>
            id = '[autoyoutube id="'+id+'"]';
            <?php endif; ?>
            parent.jQuery("#TB_closeWindowButton").click();
            parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + id);
            return false;
         }
         function autoyoutube_show(type) {
            if (autoyoutube_vid != '') {
               autoyoutube_close(autoyoutube_vid);
               autoyoutube_vid = '';
            }
            if (type == 'favorites') {
               jQuery('.autoyoutube_uploads').css('display', 'none');
               jQuery('.autoyoutube_favorites').css('display', 'block');
               jQuery('#autoyoutube_uploads_bt').removeClass('current');
               jQuery('#autoyoutube_favorites_bt').addClass('current');
            } else {
               jQuery('.autoyoutube_uploads').css('display', 'block');
               jQuery('.autoyoutube_favorites').css('display', 'none');
               jQuery('#autoyoutube_uploads_bt').addClass('current');
               jQuery('#autoyoutube_favorites_bt').removeClass('current');
            }
            return false;
         }
         function autoyoutube_play(id) {
            if (autoyoutube_vid != '') {
               autoyoutube_close(autoyoutube_vid);
               if (id == autoyoutube_vid) {
                  autoyoutube_vid = '';
                  return false;
               }
            }
            autoyoutube_vid = id;
            document.getElementById('vid_'+id).style.display = 'block';
            document.getElementById('vid_'+id).innerHTML = '<iframe width="480" height="385" src="http://www.youtube.com/embed/'+id+'?fs=1&amp;feature=oembed&amp;autoplay=1" frameborder="0" allowfullscreen=""></iframe>';
            return false;
         }
         function autoyoutube_close(id) {
            document.getElementById('vid_'+id).style.display = 'none';
            document.getElementById('vid_'+id).innerHTML = '';
         }
      </script>
      <div style="margin:1em;">
         <ul class="subsubsub">
            <li>
               <a href="#" onclick="return autoyoutube_show('uploads')" class="current" id="autoyoutube_uploads_bt">
                  <?php _e('Uploads', 'wp-autoyoutube'); ?> <span class="count">(<?php echo count($videos['uploads']); ?>)</span>
               </a>
               |
            </li>
            <li>
               <a href="#" onclick="return autoyoutube_show('favorites')" id="autoyoutube_favorites_bt">
                  <?php _e('Favorites', 'wp-autoyoutube'); ?> <span class="count">(<?php echo count($videos['favorites']); ?>)</span>
               </a>
            </li>
         </ul>
         <br style="clear:both">
         <ul>
         <?php foreach ($videos as $cat => $vids) : ?>
         <?php foreach ($vids as $v) : ?>
            <li class="autoyoutube_<?php echo $cat; ?>" style="border: 1px solid #DFDFDF;min-height: 35px;<?php echo ($cat != 'uploads' ? 'display:none;' : ''); ?>">
               <img src="<?php echo $v['image']; ?>" alt="<?php echo $v['description']; ?>" style="height: 35px; vertical-align: middle;" />
               <span style="cursor: pointer;" onclick="return autoyoutube_play('<?php echo $v['id']; ?>')"><?php echo $v['title']; ?></span>
               <a href="#" onclick="return autoyoutube_insert('<?php echo $v['id']; ?>')" class="button-secondary" style="float:right;margin: 5px;"><?php _e('Insert', 'wp-autoyoutube'); ?></a>
               <div class="autoyoutube_player" id="vid_<?php echo $v['id']; ?>" style="display:none;text-align:center"></div>
            </li>
         <?php endforeach; ?>
         <?php endforeach; ?>
         </ul>
      </div>
   <?php
   }
}

function autoyoutube_get_videos() {
   $videos['uploads'] = autoyoutube_fetch_videos('uploads');
   $videos['favorites'] = autoyoutube_fetch_videos('favorites');
   return $videos;
}
function autoyoutube_fetch_videos($type = 'uploads') {
   $autoyoutube = get_option('autoyoutube', array());

   if (!isset($autoyoutube['username']) || $autoyoutube['username'] == '') return false;

   $feedURL = 'https://gdata.youtube.com/feeds/api/users/'.$autoyoutube['username'].'/'.$type; //uploads, favorites

   // read feed into SimpleXML object
   $sxml = simplexml_load_file($feedURL);
   $videos = array();

   // iterate over entries in feed
   foreach ($sxml->entry as $entry) {

      // get nodes in media: namespace for media information
      $media = $entry->children('http://search.yahoo.com/mrss/');

      $video['title'] = addslashes(trim($media->group->title));
      $video['description'] = addslashes(trim(str_replace("\n",' ',$media->group->description)));
      $video['category'] = addslashes(trim($media->group->category));
      $video['tags'] = addslashes(trim($media->group->keywords));
      $video['author'] = addslashes(trim($entry->author->name));

      // get video player URL
      $attrs = $media->group->player->attributes();

      $video['id'] = substr($attrs['url'],0,strpos($attrs['url'], '&'));
      $video['id'] = substr($video['id'], strpos($attrs['url'], '=')+1);

      $yt = $media->children('http://gdata.youtube.com/schemas/2007');
      $attrs = $yt->duration->attributes();
      $video['time'] = floor($attrs['seconds']/60).':'.str_pad($attrs['seconds']%60, 2, '0');

      $video['image'] = 'http://i.ytimg.com/vi/'.$video['id'].'/0.jpg';

      $videos[$video['id']] = $video;

   }

   return array_reverse($videos);
}


add_action('autoyoutube_event', 'autoyoutube_cron');
function autoyoutube_cron() {
   $autoyoutube = get_option('autoyoutube', array());
   $videos = autoyoutube_get_videos();
   foreach ($videos as $cat => $vids) {
      if ($cat == 'favorites' && $autoyoutube['auto_fav'] != 'on') continue;
      foreach ($vids as $v) {
         if (autoyoutube_post_exists($v['id']) == false) {
            autoyoutube_post($v);
         }
      }
   }
   $autoyoutube['update'] = current_time('timestamp');
   update_option('autoyoutube', $autoyoutube);
}

function autoyoutube_post_exists($vid) {

/* solució 0 */
   $query = new WP_Query(
      array(
         'post_status' => array('publish', 'pending', 'draft', 'future', 'trash'),
         'numberposts' => -1,
         'meta_key' => 'autoyoutube',
         'meta_value' => $vid
      )
   );
   $posts = $query->posts;
   wp_reset_postdata();
   if (!empty($posts))
      return $posts;
   return false;

/* 
   solució 1
   $posts = get_posts(array('numberposts' => -1, 'post_status' => null, 'meta_key' => 'autoyoutube', 'meta_value' => $vid));
   if (!empty($posts))
      return $posts;
   return false;

   solució 2
   global $wpdb;
   $vids = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_status = 'publish' AND post_content LIKE '%{$vid}%'");
   return count($vids) != 0;
*/
}


function md2_getChangelog($n = 1) { //get last changelog
   $data = file_get_contents(dirname(__FILE__) . '/readme.txt');

   $str = '== Changelog ==';
   $start = strpos($data, $str);

   $tail = substr($data, $start + strlen($str));
   unset($data);

   $end = strpos($tail, '==');
   if (!$end) $end = strlen($tail);

   $out = substr($tail, 0, $end);
   unset($tail);
   $lines = explode("\n", $out);
   unset($out);

   if (count($lines) > 0) {

      echo '<div class="postbox">';
      $c = 0;
      foreach ($lines as $k => $v) {
         if ($v) {
            if ($v[0] == '=') {
               if ($c < $n) {
                  echo '<h3 class="hndle">Changelog ' . trim(str_replace('=', '', $v)) . '</h3><div class="inside"><ol>';
                  $c++;
               }
               else
                  break;
            } elseif ($v[0] == '*') {
               echo '<li>' . trim(substr($v, 1)) . '</li>';
            }
         }
      }
      unset($lines);
      if ($c > 0) echo '</ol></div>';
      echo '</div>';
   }
}
function md_getChangelog($n = 1) {
   $slug = dirname(__FILE__);
   $slug = substr($slug, strrpos($slug, '/')+1);

   include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
   $api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => true)));
   $last_version = $api->version;

   $changelog = trim($api->sections['changelog']);
   unset($slug, $api);
   $lines = explode("\n\n", $changelog);
   $changelog = array();
   $n = 0;
   foreach ($lines as $line) {
      if (substr($line, 0, 4) == '<h4>')
         $changelog[$n]['version'] = strip_tags($line);
      else
         $changelog[$n++]['log'] = $line;
   }
   $changelog['last_version'] = $last_version;
   return $changelog;
}

