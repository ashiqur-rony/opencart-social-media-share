<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-gk-social-media-share" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
      <div class="alert alert-success"><i class="fa fa-check-square"></i><?php echo $success; ?></div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-share-alt"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-gk-social-media-share" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $app_id;?></label>
            <div class="col-sm-10">
              <input type="text" name="facebook[app_id]" placeholder="<?php echo $entry_name; ?>" value="<?php echo $social_data['facebook']['app_id'];?>" id="input-name" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $app_secret;?></label>
            <div class="col-sm-10">
              <input type="text" name="facebook[app_secret]" placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control" value="<?php echo $social_data['facebook']['app_secret'];?>" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $share_fb;?></label>
            <div class="col-sm-10">
              <input type="checkbox" value="1" name="facebook[share_fb]"<?php if(isset($social_data['facebook']['share_fb']) && $social_data['facebook']['share_fb'] == '1') echo ' checked="checked"';?> />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $share_add;?></label>
            <div class="col-sm-10">
              <input type="checkbox" value="1" name="facebook[share_add]"<?php if(isset($social_data['facebook']['share_add']) && $social_data['facebook']['share_add'] == '1') echo ' checked="checked"';?> />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $share_edit;?></label>
            <div class="col-sm-10">
              <input type="checkbox" value="1" name="facebook[share_edit]"<?php if(isset($social_data['facebook']['share_edit']) && $social_data['facebook']['share_edit'] == '1') echo ' checked="checked"';?> />
            </div>
          </div>
          <?php
            if((strtolower($social_data['facebook']['app_secret']) != 'app secret') && (strtolower($social_data['facebook']['app_id']) != 'app id') && isset($fb_login_url)){
          ?>
          <div class="form-group">
            <label class="col-sm-12 control-label"><?php echo '<a href="'. $fb_login_url .'">'.$fb_login_text.'</a>'; ?></label>
          </div>
          <?php
            } elseif(isset($pages) && is_array($pages)) {
          ?>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $fb_select_page ?></label>
            <div class="col-sm-10">
              <?php
                foreach($pages as $page) {
                    echo '<p>&raquo;<a href="index.php?route='.$_GET['route'].'&token='.$_GET['token'].'&page_id='.$page["id"].'&page_access_token='.$page["access_token"].'&page_name='.$page["name"].'">'.$page["name"].'</a></p>';
                }
              ?>
              <div class="text-info"><?php echo $fb_select_page_text; ?></div>
            </div>
          </div>
          <?php
            } elseif(!empty($social_data['facebook']['app_id']) && !empty($social_data['facebook']['app_secret']) && isset($social_data['facebook']['page_name'])) {
          ?>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $fb_selected_page ?></label>
            <div class="col-sm-10">
              <strong><?php echo $social_data['facebook']['page_name']; ?></strong>
              <input type="hidden" name="facebook[page_id]" value="<?php echo $social_data['facebook']['page_id']; ?>" />
              <input type="hidden" name="facebook[page_access_token]" value="<?php echo $social_data['facebook']['page_access_token']; ?>" />
              <input type="hidden" name="facebook[page_name]" value="<?php echo $social_data['facebook']['page_name']; ?>" />
              <input type="hidden" name="facebook[user]" value="<?php echo $social_data['facebook']['user']; ?>" />
              <div class="text-info"><?php echo $fb_selected_page_text; ?></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-12 control-label"><a href="<?php echo $fb_logout_url;?>"><?php echo $fb_logout_text; ?></a></label>
          </div>
          <?php
            }
          ?>
        </form>
      </div>
    </div>
  </div>
</div>