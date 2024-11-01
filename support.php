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
global $sidebar;

$changelog = md_getChangelog();
$last = $changelog[0];
?>

<div class="postbox">
   <h3 class="hndle">
      Changelog <?php echo $last['version']; ?>
      <?php
         if ($last['version'] != $changelog['last_version'])
            echo ' <a href="#" title="'.__('Available').' '.$changelog['last_version'].'" style="text-decoration:none;color:red">!!</a>';
      ?>
   </h3>
   <div class="insite">
      <?php echo str_replace('ul>', 'ol>', $last['log']); ?>
   </div>
</div>

<div class="postbox">
      <h3 class="hndle">Support this plugin</h3>
      <div class="inside" style="font-size: 90%;text-align: center;">
            <h3 style="background:none;border:0;box-shadow:none;"><a href="http://wordpress.org/extend/plugins/<?php echo $sidebar['plugin']; ?>">Rate this plugin</a></h3>
            <h3 style="background:none;border:0;box-shadow:none;"><a href="http://wordpress.menorcadev.com/plugin/<?php echo $sidebar['plugin']; ?>">Write your feedback</a></h3>
      </div>
</div>
<div class="postbox">
   <h3 class="hndle">Credits</h3>
   <div class="inside">
      <ul>
         <li><a href="http://wordpress.menorcadev.com/plugin/<?php echo $sidebar['plugin']; ?>/" target="_blank">Official plugin page</a></li>
         <li><a href="http://wordpress.menorcadev.com" target="_blank">More interesting WordPress plugins</a></li>
         <li>Developed by <a href="http://<?php echo $sidebar['author_url']; ?>" target="_blank"><?php echo $sidebar['author_name']; ?></a> at</li>
      </ul>
      <center>
         <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmenorcadev&amp;width=250&amp;height=70&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=false&amp;appId=231319580216245" scrolling="no" frameborder="0" style="border:none;background:transparent; overflow:hidden; width:250px; height:70px;" allowTransparency="true"></iframe>

         <a href="https://twitter.com/menorcadev" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @menorcadev</a>


         <a href="https://twitter.com/<?php echo $sidebar['author_twitter']; ?>" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @<?php echo $sidebar['author_twitter']; ?></a>

         <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

         <a href="http://www.menorcadev.com" target="_blank" title="Powered by #menorcadev"><img src="http://www.menorcadev.com/logo-powered.png" alt="Powered by #menorcadev" /></a>
      </center>

   </div>
</div>
