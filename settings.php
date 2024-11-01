<?php
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

if (!defined('WP_CONTENT_URL')) die('no direct access');

/*
 *  Save settings
 */
if (isset($_POST['submit'])) {

   update_option('autoyoutube', $_POST['autoyoutube']);

   if ($_POST['autoyoutube']['auto_wp'] == 'on') wp_schedule_event(time(), 'hourly', 'autoyoutube_event');
   else wp_clear_scheduled_hook('autoyoutube_event');

   // Info
   echo '<div class="updated settings-error"><p><strong>'.__('Settings saved.', 'wp-autoyoutube').'</strong></p></div>';

}

$autoyoutube = get_option('autoyoutube', array());

if (!isset($autoyoutube['template'])) $autoyoutube['template'] = "{title} ({time})\n{video}\n{description}";

?>

<form action="<?php echo $plugin_url; ?>" method="post">

   <div class="postbox">
      <h3 class="hndle">AutoYoutube</h3>
      <div class="inside">

         <table class="form-table" style="clear:none">

            <tr valign="top">
               <th scope="row"><label for="autoyoutube_username"><?php _e('Youtube Username', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[username]" type="text" id="autoyoutube_username" value="<?php echo $autoyoutube['username']; ?>" />
               </td>
            </tr>

            <tr valign="top">
               <th scope="row"><label for="autoyoutube_status"><?php _e('Default status', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <select name="autoyoutube[status]" id="autoyoutube_status">
                     <option value="publish"<?php echo ($autoyoutube['status'] == 'publish' ? ' selected="selected"': ''); ?>><?php _e('Published'); ?></option>
                     <option value="draft"<?php echo ($autoyoutube['status'] == 'draft' ? ' selected="selected"': ''); ?>><?php _e('Draft'); ?></option>
                     <option value="pending"<?php echo ($autoyoutube['status'] == 'pending' ? ' selected="selected"': ''); ?>><?php _e('Pending Review'); ?></option>
                  </select>
               </td>
            </tr>

            <tr valign="top">
               <th scope="row"><label for="autoyoutube_category"><?php _e('Default category', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <?php
                  wp_dropdown_categories(
                     array(
                        'hide_empty' => 0,
                        'id' => 'autoyoutube_category',
                        'name' => 'autoyoutube[category]',
                        'selected' => $autoyoutube['category'],
                        'hierarchical' => false,
                        'show_option_none' => false
                     )
                  );
                  ?>
               </td>
            </tr>

            <!--<tr valign="top">
               <th scope="row"><label for="autoyoutube_tags"><?php _e('Import tags from Youtube', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[tags]" type="checkbox" id="autoyoutube_tags"<?php echo ($autoyoutube['tags'] == 'on' ? ' checked="checked"' : ''); ?> />
               </td>
            </tr>-->

            <tr valign="top">
               <th scope="row">
                  <label for="autoyoutube_template"><?php _e('Post template', 'wp-autoyoutube'); ?></label><br />
                  <em>{title}, {description}, {video}, {time}, {category}, <!--{tags}, -->{author}, {id}, {image}</em>
               </th>
               <td>
                  <textarea name="autoyoutube[template]" id="autoyoutube_template" style="width: 100%;min-height:100px;"><?php echo $autoyoutube['template']; ?></textarea>
               </td>
            </tr>

            <tr valign="top">
               <th scope="row"><label for="autoyoutube_wordpress"><?php _e('Wordpress embed method', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[wordpress]" type="checkbox" id="autoyoutube_wordpress"<?php echo ($autoyoutube['wordpress'] == 'on' ? ' checked="checked"' : ''); ?> />
               </td>
            </tr>

            <tr valign="top">
               <th scope="row"><label for="autoyoutube_auto"><?php _e('Videos posted by CronJob', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[auto]" type="checkbox" id="autoyoutube_auto"<?php echo ($autoyoutube['auto'] == 'on' ? ' checked="checked"' : ''); ?> />
                  <span class="autoyoutube_auto"<?php echo ($autoyoutube['auto'] == 'on' ? '' : ' style="display: none"'); ?>>
                     <br />
                     <?php _e('Last update'); ?>:
                     <?php echo ($autoyoutube['update'] > 0 ? date_i18n(get_option('date_format') , $autoyoutube['update']).' '.date_i18n(get_option('time_format') , $autoyoutube['update']) : ''); ?>
                  </span>
                  <input type="hidden" id="autoyoutube_update" name="autoyoutube[update]" value="<?php echo $autoyoutube['update']; ?>" />
               </td>
            </tr>

            <tr valign="top" class="autoyoutube_auto"<?php echo ($autoyoutube['auto'] == 'on' ? '' : ' style="display: none"'); ?>>
               <th scope="row"><label for="autoyoutube_auto_wp"><?php _e('Import Favorites videos', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[auto_fav]" type="checkbox" id="autoyoutube_auto_fav"<?php echo ($autoyoutube['auto_fav'] == 'on' ? ' checked="checked"' : ''); ?> />
               </td>
            </tr>

            <tr valign="top" class="autoyoutube_auto"<?php echo ($autoyoutube['auto'] == 'on' ? '' : ' style="display: none"'); ?>>
               <th scope="row"><label for="autoyoutube_auto_wp"><?php _e('Use Wordpress CronJob', 'wp-autoyoutube'); ?></label></th>
               <td>
                  <input name="autoyoutube[auto_wp]" type="checkbox" id="autoyoutube_auto_wp"<?php echo ($autoyoutube['auto_wp'] == 'on' ? ' checked="checked"' : ''); ?> /><br />
                  <span class="autoyoutube_auto_linux"<?php echo ($autoyoutube['auto_wp'] == 'on' ? ' style="display: none"' : ''); ?>>
                     <?php _e('Linux CronJob:', 'wp-autoyoutube'); ?><br />
                     <code style="font-size:80%;">
                     0 * * * * /usr/bin/curl --silent <?php bloginfo('home'); ?>/?autoyoutube
                     </code>
                  </span>
               </td>
            </tr>

         </table>
<script type="text/javascript">
jQuery('#autoyoutube_auto').change(function () {
      if (jQuery(this).prop('checked'))
         jQuery('.autoyoutube_auto').show();
      else {
         jQuery('.autoyoutube_auto').hide();
         jQuery('#autoyoutube_auto_fav').removeAttr('checked');
         jQuery('#autoyoutube_auto_wp').removeAttr('checked');
         jQuery('.autoyoutube_auto_linux').show();
      }
   });
jQuery('#autoyoutube_auto_wp').change(function () {
      if (jQuery(this).prop('checked'))
         jQuery('.autoyoutube_auto_linux').hide();
      else
         jQuery('.autoyoutube_auto_linux').show();
   });
</script>
      </div>
   </div>

   <p class="submit">
      <input type="submit" name="submit" value="<?php _e('Save changes', 'wp-autoyoutube'); ?>" class="button-primary" />
   </p>
</form>
