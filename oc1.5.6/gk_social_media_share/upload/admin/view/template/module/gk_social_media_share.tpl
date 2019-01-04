<?php echo $header; ?>
<div id="content">
<div class="breadcrumb">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
  <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
  <?php } ?>
</div>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>

<div class="box">
  <div class="heading">
    <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
  </div>
    <div class="content">
        <div id="tabs" class="htabs">
            <a href="#tab-fb"><?php echo $fb_title; ?></a>
        </div>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

        <div id="tab-fb">
        <table class="form">
            <tr>
                <td><label><?php echo $app_id;?></label></td> 
                    <td>
                       <input type="text" name="facebook[app_id]" value="<?php echo $social_data['facebook']['app_id'];?>" />
                    </td>
            </tr> 
            <tr>
                <td><?php echo $app_secret;?></td>
                <td>
                    <input type="text" name="facebook[app_secret]" value="<?php echo $social_data['facebook']['app_secret'];?>" />
                </td>
            </tr>
            <tr>
                <td><?php echo $share_fb;?></td>
                <td><input type="checkbox" value="1" name="facebook[share_fb]"<?php if(isset($social_data['facebook']['share_fb']) && $social_data['facebook']['share_fb'] == '1') echo ' checked="checked"';?> /></td>
            </tr>
            <tr>
                <td><?php echo $share_add;?></td>
                <td><input type="checkbox" value="1" name="facebook[share_add]"<?php if(isset($social_data['facebook']['share_add']) && $social_data['facebook']['share_add'] == '1') echo ' checked="checked"';?> /></td>
            </tr>
            <tr>
                <td><?php echo $share_edit;?></td>
                <td><input type="checkbox" value="1" name="facebook[share_edit]"<?php if(isset($social_data['facebook']['share_edit']) && $social_data['facebook']['share_edit'] == '1') echo ' checked="checked"';?> /></td>
            </tr> 
            
            <?php
                if((strtolower($social_data['facebook']['app_secret']) != 'app secret') && (strtolower($social_data['facebook']['app_id']) != 'app id') && isset($fb_login_url)){
            ?>
             <tr>
                <td colspan="2">
                    <label><?php echo '<a href="'. $fb_login_url .'">'.$fb_login_text.'</a>'; ?></label>
                </td>
            </tr>
            <?php
                }
                elseif(isset($pages) && is_array($pages)) {
            ?>
            <tr>
                <td>
                    <label><?php echo $fb_select_page?></label><br />
                    <em><?php echo $fb_select_page_text; ?></em>
                </td>
                <td>
                    <?php
                        foreach($pages as $page) {
                            echo '<p>&raquo;<a href="index.php?route='.$_GET['route'].'&token='.$_GET['token'].'&page_id='.$page["id"].'&page_access_token='.$page["access_token"].'&page_name='.$page["name"].'">'.$page["name"].'</a></p>';
                        }
                    ?>
                </td>
            </tr>
            <?php
                }
                elseif(!empty($social_data['facebook']['app_id']) && !empty($social_data['facebook']['app_secret']) && isset($social_data['facebook']['page_name'])) {
            ?>
            <tr>
                <td>
                    <label><?php echo $fb_selected_page?></label><br />
                    <em><?php echo $fb_selected_page_text; ?></em>
                </td>
                <td>
                    <strong><?php echo $social_data['facebook']['page_name']; ?></strong>
                    <input type="hidden" name="facebook[page_id]" value="<?php echo $social_data['facebook']['page_id']; ?>" />
                    <input type="hidden" name="facebook[page_access_token]" value="<?php echo $social_data['facebook']['page_access_token']; ?>" />
                    <input type="hidden" name="facebook[page_name]" value="<?php echo $social_data['facebook']['page_name']; ?>" />
                    <input type="hidden" name="facebook[user]" value="<?php echo $social_data['facebook']['user']; ?>" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <label><a href="<?php echo $fb_logout_url;?>"><?php echo $fb_logout_text; ?></a></label>
                </td> 
            </tr>
            <?php
                }
            ?>
          </table>
        </div> 
        </form> <!-- form action (end) -->
    </div> <!-- content (end) -->
</div> <!-- box (end) -->

<script type="text/javascript"><!--
$('#tabs a').tabs(); 
//--></script> 