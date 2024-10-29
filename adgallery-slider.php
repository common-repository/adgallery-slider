<?php
/*
Plugin Name: AdGallery Slider
Plugin URI: http://pws.ru/wordpress/adgallery-slider
Description: This is plugin for inserting Gallery Slider to your blog's posts and pages
Author: Alexander Novikov
Version: 1.1
Author URI: http://pws.ru/wordpress/
*/

define(ADGALLERY_TABLE, "adgalleryslider_table");
define(ADGALLERY_SLIDES, "adgalleryslider_slides");
define(ADGALLERY_FOLDER, "slides");

//set hook to admin menu
function adgalleryslider_menu() {
	add_options_page('Ad Gallery Slider', 'Ad Gallery Slider', 8, __FILE__, 'adgalleryslider_options');
}
add_action('admin_menu', 'adgalleryslider_menu');

//install plugin
function adgalleryslider_install() {
	global $wpdb;
	
	$query="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.ADGALLERY_TABLE."` (
		`id` INT(11) NOT NULL AUTO_INCREMENT, 
		`title` VARCHAR(255) NOT NULL,
		`enable` CHAR(1) NOT NULL DEFAULT 'Y',
		`settings` TEXT NOT NULL,
		PRIMARY KEY(`id`))";
 	$wpdb->query($query);
 	$query="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.ADGALLERY_SLIDES."` (
 		`id` INT(11) NOT NULL AUTO_INCREMENT,
 		`showid` INT(11) NOT NULL,
 		`title` VARCHAR(255) NOT NULL DEFAULT '',
 		`img` VARCHAR(64) NOT NULL DEFAULT '',
 		`content` TEXT NOT NULL DEFAULT '',
 		`enable` CHAR(1) NOT NULL DEFAULT 'Y',
 		`position` INT(11) NOT NULL DEFAULT '0',
 		`settings` TEXT NOT NULL DEFAULT '',
 		PRIMARY KEY(`id`))";
	$wpdb->query($query);
}
register_activation_hook(__FILE__, 'adgalleryslider_install');

function adgalleryslider_options() {
	global $wpdb;
	if($_SERVER["REQUEST_METHOD"]=="POST") {
		if(isset($_POST["action"]) && $_POST["action"]!="") {
			$action=trim($_POST["action"]);
		} else {
			$action="showlist";
		}
		switch($action) {
			case "addshow":
				adgalleryslider_showlist(adgalleryslider_addshow());
			break;
			case "showlist":
			default:
				adgalleryslider_showlist();
			break;
			case "edit":
				if(isset($_POST["id"])) {
					$id=intval($_POST["id"]);
				} else {
					$id=0;
				}
				if(isset($_POST["slideid"])) {
					$slideid=intval($_POST["slideid"]);
				} else {
					$slideid=0;
				}
				adgalleryslider_editslide($id, $slideid);
			break;
		}
	} else {
		if(isset($_GET["action"]) && $_GET["action"]!="") {
			$action=trim($_GET["action"]);
		} else {
			$action="showlist";
		}
		if(isset($_GET["id"])) {
			$id=intval($_GET["id"]);
		} else {
			$id=0;
		}
		if(isset($_GET["slideid"])) {
			$slideid=intval($_GET["slideid"]);
		} else {
			$slideid=0;
		}
		switch($action) {
			case "deleteshow":
				adgalleryslider_showlist(adgalleryslider_deleteshow($id)*2);
			break;
			case "slides":
				adgalleryslider_slidelist($id);
			break;
			case "edit":
				adgalleryslider_editslide($id, $slideid);
			break;
			case "deleteslide":
				adgalleryslider_slidelist($id, adgalleryslider_deleteslide($slideid));	
			break;
			case "showlist":
			default:
				if(isset($_GET["mes"])) {
					$mes=intval($_GET["mes"]);
				} else {
					$mes=0;
				}
				adgalleryslider_showlist($mes);
			break;	
		}
	}
}
//show list of items
function adgalleryslider_showlist($mes=0) {
	global $wpdb;
        $query="SELECT COUNT(*) AS `cnt` FROM `".$wpdb->prefix.ADGALLERY_TABLE."`";
        $total=$wpdb->get_var($query);
        $per_page=30;
        
        if(isset($_GET['apage'])) {
		$page=intval($_GET['apage']);
	} else {
		$page=1;
	}
	$start=$offset=($page-1)*$per_page;

	$page_links=paginate_links(array(
		'base'=>add_query_arg( 'apage', '%#%' ),
		'format'=>'',
		'total'=>ceil($total/$per_page),
		'current'=>$page
	));
        $query="SELECT * FROM `".$wpdb->prefix.ADGALLERY_TABLE."` ORDER BY `id` LIMIT ".$start.", ".$per_page;
	$result=$wpdb->get_results($query);

if($mes>0) {
?>
<div id="message" class="updated fade"><p><strong><?php 
switch($mes) {
case 1: _e("Slideshow has been added"); break;
case 2: _e("Slideshow has been deleted success"); break;
}
?></strong></a></p></div>
<?php
}
?>
<div class="wrap">
<h2><?php _e("Slide show list"); ?></h2>
<div class="tablenav">
<?php
if ( $page_links )
	echo "<div class='tablenav-pages'>$page_links</div>";
?>
<br class="clear" />
</div>
<table class="widefat">
<thead>
  <tr>
    <th scope="col"><?php _e('ID'); ?></th>
    <th scope="col"><?php _e('Code'); ?></th>
    <th scope="col" width="60%"><?php _e('Title'); ?></th>
    <th scope="col"><?php _e('Active'); ?></th>
    <th scope="col" class="action-links"><?php _e('Actions'); ?></th>
  </tr>
</thead>
<tbody id="the-comment-list" class="list:comment">
<?php
foreach($result AS $show) {
?>
<tr>
<td><?php echo($show->id); ?></td>
<td>{slideshow<?php echo($show->id); ?>}</td>
<td><a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=slides&id=<?php echo($show->id); ?>" title="<?php _e("Edit slides for show"); ?>"><?php if($show->title!="") {echo($show->title);} else {_e("noname"); } ?></a></td>
<td><?php switch($show->enable) { case "Y": _e("Yes"); break; case "N": _e("No"); break;} ?></td>
<td class="action-links" style="white-space:nowrap;">
<a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=deleteshow&id=<?php echo($show->id); ?>" onclick="return confirm('<?php _e("Are you shure to delete this item?"); ?>');" ><?php _e('Delete'); ?></a>
</td>
</tr>
<?php
}
?>
</tbody>
</table>
<div class="tablenav">
<?php
if ($page_links)
	echo("<div class='tablenav-pages'>$page_links</div>");
?>
<br class="clear" />
<h3><?php _e("Add slide show"); ?></h3>
<form method="post">
<label for="showtitle"><?php _e("Title"); ?>:<input type="text" name="showtitle" id="showtitle" value="" size="60" /></label>
<input type="hidden" name="action" value="addshow" />
<input type="submit" value="<?php _e("Add"); ?>" class="button">
</form>
<br class="clear" />
</div>
</div>
<?php
}

function adgalleryslider_slidelist($showid=0, $mes=0) {
	global $wpdb;
        if($showid==0) {
?>
<div id="message" class="error fade"><p><strong><?php _e("Wrong slide show ID"); ?></strong></p></div>
<div class="wrap"><a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>"><?php _e("Return to Slide show list"); ?></a><br class="clear" /></div>
<?        
		return;
	}
	$query="SELECT `title` FROM `".$wpdb->prefix.ADGALLERY_TABLE."` WHERE `id`='".$showid."'";
        $showtitle=$wpdb->get_var($query);
        if($showtitle=="") {
        	$showtitle=_("noname");
        }
        $query="SELECT * FROM `".$wpdb->prefix.ADGALLERY_SLIDES."` WHERE `showid`='".$showid."' ORDER BY `position`";
	$result=$wpdb->get_results($query);
	if($mes>0) {
	?>
<div id="message" class="updated fade"><p><strong><?php 
switch($mes) {
case 1: _e("Slide has been deleted"); break;
}
?></strong></a></p></div>
<?php
}
?>
<div class="wrap">
<h2><?php _e("Slides for "); ?>&quot;<?php echo($showtitle); ?>&quot;</h2>
<br class="clear" />
<div>
<a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>"><?php _e("Back to Slide show list"); ?></a>
</div>
<div class="tablenav">
<div class="alignleft">
<form method="get" action="<?php echo($_SERIVER["PHP_SELF"]); ?>">
<input type="submit" value="<?php _e('Add'); ?>" name="addit" class="button-secondary" />
<input type="hidden" name="page" value="<?php echo($_GET["page"]); ?>" />
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="slideid" value="0" />
<input type="hidden" name="id" value="<?php echo($showid); ?>" />
</form>
</div>
<br class="clear" />
</div>

<table class="widefat">
<thead>
  <tr>
    <th scope="col"><?php _e('ID'); ?></th>
    <th scope="col" width="60%"><?php _e('Title'); ?></th>
    <th scope="col"><?php _e('Active'); ?></th>
    <th scope="col"><?php _e('Position'); ?></th>
    <th scope="col" class="action-links"><?php _e('Actions'); ?></th>
  </tr>
</thead>
<tbody id="the-comment-list" class="list:comment">
<?php
foreach($result AS $slide) {
?>
<tr>
	<td><?php echo($slide->id); ?></td>
	<td><a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=edit&id=<?php echo($showid); ?>&slideid=<?php echo($slide->id); ?>"><?php echo($slide->title); ?></a></td>
	<td><?php switch($slide->enable) { case "Y": _e("Yes"); break; case "N": _e("No"); break;} ?></td>
	<td><?php echo($slide->position); ?></td>
	<td class="action-links" style="white-space:nowrap;">
		<a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=edit&id=<?php echo($showid); ?>&slideid=<?php echo($slide->id); ?>" ><?php _e('Edit'); ?></a>
		<a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=deleteslide&id=<?php echo($showid); ?>&slideid=<?php echo($slide->id); ?>" onclick="return confirm('<?php _e("Are you shure to delete this item?"); ?>');" ><?php _e('Delete'); ?></a>
	</td>
</tr>
<?
}
?>
</tbody>
</table>
<br class="clear" />
</div>
<?
}
//add record with slide show
function adgalleryslider_addshow() {
	global $wpdb;
	if(isset($_POST["showtitle"])) {
		$title=trim(addslashes(stripslashes($_POST["showtitle"])));
	} else {
		$title="";
	}
	$query="INSERT INTO `".$wpdb->prefix.ADGALLERY_TABLE."`(`title`, `enable`, `settings`) VALUES('".$title."', 'Y', '')";
	if($wpdb->query($query)) {
        	return 1;
	}
	return 0;
}
//delete record with slideshow
function adgalleryslider_deleteshow($id=0) {
	global $wpdb;
	if($id==0) {
		return 0;
	}
	$query="DELETE FROM `".$wpdb->prefix.ADGALLERY_SLIDES."` WHERE `showid`='".$id."'";
	$wpdb->query($query);
	$query="DELETE FROM `".$wpdb->prefix.ADGALLERY_TABLE."` WHERE `id`='".$id."'";
	if($wpdb->query($query)) {
		return 1;
	}
	return 0;
}
//delete slide
function adgalleryslider_deleteslide($id=0) {
	global $wpdb;
	if($id==0) {
		return 0;
	}
	$query="DELETE FROM `".$wpdb->prefix.ADGALLERY_SLIDES."` WHERE `id`='".$id."'";
	if($wpdb->query($query)) {
		return 1;
	}
	return 0;
}

//show page with form
function adgalleryslider_editslide($id, $slideid=0) {
	global $wpdb;
	if($slideid==0) {
		$Action_header=__("Add Slide");
		$Fields=array("showid"=>$id, "title"=>"", "content"=>"", "enable"=>"", "img"=>"", "enable"=>"Y", "position"=>"0", "settings"=>"");
	} else {
		$Action_header=__("Edit Slide");
		$Fields=$wpdb->get_row("SELECT * FROM `".$wpdb->prefix.ADGALLERY_SLIDES."` WHERE `id`='".$slideid."'", ARRAY_A);
	}
	$imgfile=dirname(__FILE__)."/".ADGALLERY_FOLDER."/".$Fields["img"];
	if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["adgallery"]) && $_POST["adgallery"]=="savesettings") {
		foreach($Fields AS $fkey=>$fval) {
			if(isset($_POST[$fkey])) {
				$Fields[$fkey]=trim($_POST[$fkey]);
				$mes=true;
			}
		}
		$Fields["content"]=StripSlashes(trim($Fields["content"]));
		if(isset($Fields["id"])) {
			unset($Fields["id"]);
		}
		if(isset($_FILES) && isset($_FILES["img"]) && $_FILES["img"]["error"]==0) {
			$imginfo=getImageSize($_FILES["img"]["tmp_name"]);
			if($imginfo && is_array($imginfo)) {
				if($Fields["img"]=="") {
					$filename=basename($_FILES["img"]["name"]);
					$filename=$id."_".md5(time())."_".$filename;
					$Fields["img"]=$filename;
				}
				$imgfile=dirname(__FILE__)."/".ADGALLERY_FOLDER."/".$Fields["img"];
				if(!move_uploaded_file($_FILES["img"]["tmp_name"], $imgfile)) {
					$err.=__("Can't save image file")."<br />";
					$mes=false;
				}
			} else {
				$err.=__("Wrong image format")."<br />";
				$mes=false;
			}
		}
		
		$slideid=intval($_POST["slideid"]);
		if($slideid==0) {
  			$wpdb->insert($wpdb->prefix.ADGALLERY_SLIDES, $Fields);
  			$slideid=$wpdb->insert_id;
  			if($slideid==0) {
  				echo($wpdb->last_query);
  			}
  		} else {
  			if(!$wpdb->update($wpdb->prefix.ADGALLERY_SLIDES, $Fields, array("id"=>$slideid))) {
  				echo($wpdb->last_query);
  			}
  		}
	}
	
        if(file_exists($imgfile) && filesize($imgfile)>0) {
        	$image= WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).ADGALLERY_FOLDER."/".$Fields["img"];
        } else {
        	$image="";
        }
if($mes) {
?>
<div id="message" class="updated fade"><p><strong><?php _e("Slide updated successfully")?></strong></a></p></div>
<?php
}
if($err!="") {
?>
<div id="message" class="error fade"><p><strong><?php echo($err); ?></strong></a></p></div>
<?php
}
?>
<div class="wrap">
<h2><?php echo($Action_header); ?></h2>
<div>
<a href="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=slides&id=<?php echo($id); ?>"><?php _e("Back to Slides list"); ?></a>
</div>
<br class="clear" />
<form method="post" enctype="multipart/form-data" action="<?php echo($_SERVER["PHP_SELF"]); ?>?page=<?php echo($_GET["page"]); ?>&action=edit&id=<?php echo($id); ?>&slideid=<?php echo($slideid); ?>">
<input type="hidden" name="id" value="<?php echo($id); ?>" />
<input type="hidden" name="showid" value="<?php echo($id); ?>" />
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="slideid" value="<?php echo($slideid); ?>" />
<input type="submit" value="Save" class="button" />
<table class="widefat">
<tr><td><label for="title"><?php _e('Title'); ?>:</label></td><td>
<input type="text" name="title" id="title" value="<?php echo($Fields["title"]); ?>" size="40" /> <br /><small><?php _e("Use only for admin area"); ?></small>
<tr><td><label for="enable"><?php _e('Enable'); ?>:</label></td><td>
<select name="enable" id="enable">
<option value="Y"<?php if($Fields["enable"]=="Y"){ echo(" selected"); } ?>><?php _e("Yes"); ?></option>
<option value="N"<?php if($Fields["enable"]=="N"){ echo(" selected"); } ?>><?php _e("No"); ?></option>
</select>
</td></tr>
<tr><td><label for="content"><?php _e('Content'); ?>:</label></td><td>
<textarea name="content" id="content" cols="50" rows="5"><?php echo(StripSlashes($Fields["content"])); ?></textarea>
</td></tr>
<tr><td><label for="img"><?php _e("Image"); ?>:</label></td><td>
<input type="file" name="img" id="img">
<?php if($image!="") {
?>
[<a href="<?php echo($image); ?>" target="_blank"><?php _e("View image"); ?></a>]
<?php
}
?>
</td></tr>
<tr><td><label for="position"><?php _e("Position"); ?>:</label></td><td>
<input type="text" name="position" id="position" size="4" value="<?php echo($Fields["position"]); ?>" />
</td></tr>
</table>
<p>
<input type="submit" value="Save" class="button" />
<input type="hidden" name="adgallery" value="savesettings" />
</p>
</form>
<br class="clear" />
</div>
</div>
<?php
}

function adgalleryslider_buildhtml($ind) {
	global $wpdb;
	
	$pluginpath=WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
        $content="";
        
        if(!is_single() && !is_page()) {
        	return $content;
        }
        $query="SELECT * FROM `".$wpdb->prefix.ADGALLERY_TABLE."` WHERE `id`='".$ind."' AND `enable`='Y'";
        $show=$wpdb->get_row($query);
        if(!$show || count($show)==0) {
        	return $content;
        }
        
        $query="SELECT * FROM `".$wpdb->prefix.ADGALLERY_SLIDES."` WHERE `showid`='".$ind."' AND `enable`='Y' ORDER BY `position`";
        $slides=$wpdb->get_results($query);
        if(!$slides || count($slides)==0) {
        	return $content;
        }
	$content.='
	    <div>        
            <div class="slider_qwe-container"><div class="slider_qwe_bg-top"><div class="slider_qwe_bg-bottom">
            <div class="ad-gallery" id="ad-gallery-'.$ind.'">
              <div class="ad-image-wrapper">
              </div>
              <div class="ad-nav">
                <div class="ad-thumbs">
                  <ul class="ad-thumb-list">';
		  foreach($slides AS $slide) {
		  	$content.='                    
                   	<li>
                      	<a href="'.$pluginpath.ADGALLERY_FOLDER.'/'.$slide->img.'">
                        <img src="'.$pluginpath.'img/none.jpg">
                      	</a>
                      	<div class="description">'.$slide->content.'</div>
                    	</li>';
                   }
	$content.='                  
                  </ul>
                </div>
              </div>
            </div>        
            <div class="clear"></div>
            <div class="cusom-prev" id="cusom-prev-'.$ind.'"></div>
            <div class="cusom-next" id="cusom-next-'.$ind.'"></div>
            <div class="cusom-description-container" id="cusom-description-container-'.$ind.'"><div class="cusom-description" id="cusom-description-'.$ind.'"></div></div>
            <div class="clear"></div>
        </div></div></div>
    </div>
';
$content.="
<script type=\"text/javascript\">
<!--
var sender;
var galleries = \$('#ad-gallery-".$ind."').adGallery({
  width: 510, 
  height: 280, 
  thumb_opacity: 1, 
  start_at_index: 0, 
  animate_first_image: false, 
  animation_speed: 400, 
  display_next_and_prev: true, 
  display_back_and_forward: false, 
  scroll_jump: 0, 
  effect: 'slide-hori', 
  enable_keyboard_move: true, 
  cycle: true, 
  callbacks: {
    init: function() {
      this.preloadAll();
      count = \$('.slider_qwe-container .ad-thumbs .ad-thumb-list li').length;
      \$('.slider_qwe-container .ad-thumbs .ad-thumb-list').css('left',250-count*17);
    },
    afterImageVisible: function() {
      \$('.cusom-description', '.slider_qwe-container').fadeIn('fast');
      \$('.cusom-description-container').css('height', 'auto');
    },
    beforeImageVisible: function(new_image, old_image) {
    	\$('img', \$('li', '.slider_qwe-container .ad-thumb-list')).each(
    	    function()
    	    {
    	        \$(this).attr('src','".$pluginpath."img/none.jpg');
    	    }
    	);
    	\$('img', \$('a.ad-active', '.slider_qwe-container .ad-thumb-list')).attr('src','".$pluginpath."img/active.jpg');

    	h = \$('.cusom-description-container', sender).height();
    	
    	if (h > 65)
      	 h += 12;
   	 
      $('.cusom-description-container', sender).css('height', h + 'px');
      
    	\$('.cusom-description', sender).fadeOut('fast', function(){
      	\$(this).html('');
      	if ($('.description', \$('a.ad-active', sender).parent()).length > 0)
        	\$(this).html('<div class=\"clear\"></div>' + \$('.description', \$('a.ad-active', sender).parent()).html() + '<div class=\"clear\"></div>');        	
      }); 
    }
  }
});

\$('#cusom-next-".$ind."').click(
		function()
		{
		sender = \$(this).parent();
		\$('.ad-next-image', \$(this).parent()).click()
		}
);
\$('#cusom-prev-".$ind."').click(
		function()
		{
      		sender = \$(this).parent();
      		\$('.ad-prev-image', \$(this).parent()).click()
		}
);

//-->
</script>    

";
	return $content;
}
//parse content
function adgalleryslider_parser($content) {
	preg_match_all("/\\{slideshow([0-9]*)\\}/i", $content, $regs);
        if(is_array($regs) && is_array($regs[1]) && count($regs[1])>0) {
                foreach($regs[1] AS $indx=>$input) {
			$content=str_replace($regs[0][$indx], adgalleryslider_buildhtml($regs[1][$indx]), $content);
		}
	}
	return $content;
}
add_filter('the_content', 'adgalleryslider_parser');

//include scripts and styles
function adgalleryslider_scripts() {
	$pluginpath=WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
	if(!is_single() && !is_page()) {
		return;
	}
?>
<link rel="stylesheet" type="text/css" href="<?php echo($pluginpath); ?>js/jquery.ad-gallery.css"> 
<script src="<?php echo($pluginpath); ?>js/jquery-1.4.2.min.js"></script>
<script src="<?php echo($pluginpath); ?>js/jquery.ad-gallery.pack.js"></script>
<style>
<!--
.slider_qwe-container div.clear {clear: both; height: 1px; margin: 0px; padding: 0px;}
.slider_qwe-container {position: relative; width: 625px; height:auto; background: url(<?php echo($pluginpath); ?>img/slide_bg_center.jpg) repeat-y; font-family: Tahoma,Geneva,sans-serif; font-size: 12px; color: #505050; line-height: 16px; --float: left;}
.slider_qwe-container .slider_qwe_bg-top {position: relative; background: url(<?php echo($pluginpath); ?>img/slide_bg_top.jpg) no-repeat top; --float: left;}
.slider_qwe-container .slider_qwe_bg-bottom {position: relative; background: url(<?php echo($pluginpath); ?>img/slide_bg_bottom.jpg) no-repeat bottom; --float: left;}
.slider_qwe-container h1 {font-size: 14px; padding: 0px; margin: 0px; margin-bottom: 5px;}
.slider_qwe-container .ad-image p {display: none !important;}
.slider_qwe-container .ad-gallery {position: relative; left: 59px;}
.slider_qwe-container .ad-gallery .ad-thumbs li a img {border: 0px;}
.slider_qwe-container .cusom-next {width: 50px; height: 50px; position: absolute; right: 8px; top:145px; z-index: 1000; cursor: pointer;}
.slider_qwe-container .cusom-prev {width: 50px; height: 50px; position: absolute; left: 8px; top:145px; z-index: 1000; cursor: pointer;}
.slider_qwe-container .ad-next, .slider_qwe-container .ad-prev {display: none !important;}
.slider_qwe-container .ad-thumbs {position: absolute; right: 7px; top: -19px; z-index: 600; width: 250px !important; text-align: right; overflow:visible !important;}
.slider_qwe-container .ad-thumbs .ad-thumb-list {position: absolute; left: 0px; top: 0px;list-style-type:none;}
.ad-thumb-list li{list-style-type:none;float:left;}
.slider_qwe-container .ad-thumb-list .description {display: none !important;}
.slider_qwe-container .cusom-description-container { position:  relative; min-height: 65px !important; padding: 0px; margin: 0px;}
.slider_qwe-container .cusom-description { position:  relative; width: 494px; height: auto !important; top: -20px; left: 68px; z-index: 1; margin-bottom: 12px !important;}
-->
</style>
<?php	
}
add_action('wp_print_scripts', 'adgalleryslider_scripts');

?>