<?php
/****
 * GoodKoding - Social Media Share
 * Version 1.0
 * Shares product information to Facebook when a product is added or edited
 * Developed by GoodKoding
 * www.goodkoding.com
 ****/
class ControllerModuleGkSocialMediaShare extends Controller {
    private $error = array();  
    protected static $fb_app_id = 'APP ID';
    protected static $fb_app_secret = 'APP Secret';
    
    
    public function install() {
        $this->load->model('module/gk_social_media_share');
        $this->model_module_gk_social_media_share->install();
        
        $social_data = array();
        $social_data['facebook'] = array('app_id' => self::$fb_app_id, 'app_secret' => self::$fb_app_secret);
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('gk_social_media_share', array('gk_social_media_share'=>base64_encode(serialize($social_data))));
        
        $this->load->model('extension/event');
        $this->model_extension_event->addEvent('gk_social_media_share', 'post.admin.product.add', 'module/gk_social_media_share/gk_social_media_share_add');
        $this->model_extension_event->addEvent('gk_social_media_share', 'post.admin.product.edit', 'module/gk_social_media_share/gk_social_media_share_edit');
    }
    
    public function uninstall() {
        $this->load->model('module/gk_social_media_share');
        $this->model_module_gk_social_media_share->uninstall();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('gk_social_media_share', array('gk_social_media_share'=>base64_encode(serialize(array()))));
        
        $this->load->model('extension/event');
        $this->model_extension_event->deleteEvent('gk_social_media_share');
    }
    
	public function index() {  
        $this->load->language('module/gk_social_media_share');
		$this->document->setTitle($this->language->get('page_title'));
        $data['success'] = false;
        $data['error_warning'] = false;
        
		$this->load->model('setting/setting');
        $social_data = array();
        $gk_social_media_share = $this->model_setting_setting->getSetting('gk_social_media_share');
        $social_data = unserialize(base64_decode($gk_social_media_share['gk_social_media_share']));
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $fb_data = $this->request->post['facebook'];
            $social_data['facebook']['app_id'] = $fb_data['app_id'];
            $social_data['facebook']['app_secret'] = $fb_data['app_secret'];
            $social_data['facebook']['share_fb'] = isset($fb_data['share_fb'])?$fb_data['share_fb']:0;
            $social_data['facebook']['share_add'] = isset($fb_data['share_add'])?$fb_data['share_add']:0;
            $social_data['facebook']['share_edit'] = isset($fb_data['share_edit'])?$fb_data['share_edit']:0;
            
            if(!isset($social_data['facebook']['app_id']) || empty($social_data['facebook']['app_id'])) {
                $social_data['facebook']['app_id'] = self::$fb_app_id;
                $social_data['facebook']['app_secret'] = self::$fb_app_secret;
            }
            $this->model_setting_setting->editSetting('gk_social_media_share',array('gk_social_media_share' => base64_encode(serialize($social_data))));
        }
        
        if(!$social_data || !isset($social_data['facebook']['app_id']) || empty($social_data['facebook']['app_id'])) {
            $social_data['facebook']['app_id'] = self::$fb_app_id;
            $social_data['facebook']['app_secret'] = self::$fb_app_secret;
        }
        
        
        /*** Facebook API call ***/
        if(!empty($social_data['facebook']['app_id']) && !empty($social_data['facebook']['app_secret'])){
			require_once(DIR_SYSTEM.'library/gk_social_media_share_facebook.php');
			$facebook = new Facebook(array(
                'appId'  => $social_data['facebook']['app_id'],
                'secret' => $social_data['facebook']['app_secret'],
                'allowSignedRequest' => false
			));
            
            if(!$social_data || empty($social_data['facebook']['user'])) {
                $social_data['facebook']['user'] = $facebook->getUser();
            }
            
			if ($social_data['facebook']['user'] && $social_data['facebook']['user'] > 0) {
				try {
                    if(!$social_data || empty($social_data['facebook']['page_id'])) {
                        if(isset($_GET['page_id'])) {
                            $social_data['facebook']['page_id'] = $_GET['page_id'];
                            $social_data['facebook']['page_name'] = $_GET['page_name'];
                            if(!isset($_GET['page_access_token'])) {
                                $social_data['facebook']['page_access_token'] = $facebook->api("/".$social_data['facebook']['page_id']."?fields=access_token");
                            } else {                            
                                $social_data['facebook']['page_access_token'] = $_GET['page_access_token'];
                            }
                            
                            //Save the information to DB
                            $this->model_setting_setting->editSetting('gk_social_media_share',array('gk_social_media_share' => base64_encode(serialize($social_data))));
                            $data['success'] = $this->language->get('text_success');
                        }
                        else {
                            //We got the user. Save it to DB
                            $this->model_setting_setting->editSetting('gk_social_media_share',array('gk_social_media_share' => base64_encode(serialize($social_data))));
                            
                            //Fetch the pages the user is admin of
                            $result = $facebook->api("/".$social_data['facebook']['user']."/accounts");
                            if(!empty($result["data"])) {
                                $data['pages'] = $result['data'];
                            }
                        }
                    }
				} catch (FacebookApiException $e) {
					$data['error_warning'] = $e;
				}
			} else {
				$data['error_warning'] = $this->language->get('fb_authenticate_error');
				$_SESSION['token'] = $_GET['token'];
				$_SESSION['route'] = $_GET['route'];
                $_SESSION['admin_http_server'] = HTTP_SERVER;
				$data['fb_login_url'] = $facebook->getLoginUrl(
						array(
							"scope" => "manage_pages, publish_pages",
							"redirect_uri" => HTTP_CATALOG."gk_social_media_share_redirect.php"
						)
					); 
			 }
		 }
        
        $data['social_data'] = $social_data;
        
        $data['token'] = $this->session->data['token'];
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_module'] = $this->language->get('text_module');
		$data['fb_title'] = $this->language->get('fb_title');
		$data['fb_login_text'] = $this->language->get('fb_login_text');
		$data['fb_logout_text'] = $this->language->get('fb_logout_text');
        
		$data['app_id'] = $this->language->get('app_id');
		$data['app_secret'] = $this->language->get('app_secret');
		$data['share_fb'] = $this->language->get('share_fb');
		$data['share_add'] = $this->language->get('share_add');
		$data['share_edit'] = $this->language->get('share_edit');
        $data['fb_select_page'] = $this->language->get('fb_select_page');
        $data['fb_select_page_text'] = $this->language->get('fb_select_page_text');
        $data['fb_selected_page'] = $this->language->get('fb_select_page');
        $data['fb_selected_page_text'] = $this->language->get('fb_select_page_text');
        
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_remove'] = $this->language->get('button_remove');
        
  		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/gk_social_media_share', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$data['fb_logout_url'] = $this->url->link('module/gk_social_media_share/logout', 'token=' . $this->session->data['token'], 'SSL');
        
		$data['action'] = $this->url->link('module/gk_social_media_share', 'token=' . $this->session->data['token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
        
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/gk_social_media_share.tpl', $data));
    }
    
    public function logout() {
        $this->load->model('setting/setting');
		$gk_social_media_share = $this->model_setting_setting->getSetting('gk_social_media_share');
        $social_data = unserialize(base64_decode($gk_social_media_share['gk_social_media_share']));
		$social_data['facebook']['app_id'] = '';
		$social_data['facebook']['app_secret'] = '';
		$social_data['facebook']['user'] = '';
		$social_data['facebook']['page_access_token'] = '';
		$social_data['facebook']['page_id'] = '';
		$social_data['facebook']['page_name'] = '';
		$this->model_setting_setting->editSetting('gk_social_media_share',array('gk_social_media_share' => base64_encode(serialize($social_data))));
		$this->redirect($this->url->link('module/gk_social_media_share', 'token=' . $this->session->data['token'], 'SSL'));
    }
    
    private function validate() {
		if (!$this->user->hasPermission('modify', 'module/gk_social_media_share')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
			
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
    
    public function gk_social_media_share_add($product_id = null) {
        if($product_id == null) {
            return;
        }
        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        $product_images = $this->model_catalog_product->getProductImages($product_id);
        
        $params = array(
            'title' => $product['name'],
            'description' => strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')),
            'image' => 'placeholder.png',
            'link' => 'index.php?route=product/product&product_id='.$product['product_id'],
            'product_id' => $product['product_id'],
            'post_type' => 'add'
        );    
        
        if(is_array($product_images) && isset($product_images[0])) {
            $params['image'] = $product_images[0]['image'];
        }
        
        $this->gk_social_media_share($params);
    }
    
    public function gk_social_media_share_edit($product_id = null) {
        if($product_id == null) {
            return;
        }
        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        $product_images = $this->model_catalog_product->getProductImages($product_id);
        
        $params = array(
            'title' => $product['name'],
            'description' => strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')),
            'image' => 'placeholder.png',
            'link' => 'index.php?route=product/product&product_id='.$product['product_id'],
            'product_id' => $product['product_id'],
            'post_type' => 'edit'
        );    
        
        if(is_array($product_images) && isset($product_images[0])) {
            $params['image'] = $product_images[0]['image'];
        }
        
        $this->gk_social_media_share($params);
    }
    
    /** Share product info to social media **/
    protected function gk_social_media_share($params = array()){
        
		$this->load->model('setting/setting'); 
		$gk_social_media_share = $this->model_setting_setting->getSetting('gk_social_media_share');
		$social_data = unserialize(base64_decode($gk_social_media_share['gk_social_media_share']));
        $post_type = $params['post_type'];
        
        if(!isset($social_data['facebook']) || !isset($social_data['facebook']['share_fb'])) {
            return;
        }
        if($post_type == 'add' && (!isset($social_data['facebook']['share_add']) || $social_data['facebook']['share_add'] == 0)) {
            return;
        }
        if($post_type == 'edit' && (!isset($social_data['facebook']['share_edit']) || $social_data['facebook']['share_edit'] == 0)) {
            return;
        }
		$share_fb_enable = $social_data['facebook']['share_fb'];
		
		if(isset($share_fb_enable) && $share_fb_enable == 1 ){ 
			$img = $params['image']; 
			$status = html_entity_decode($params['description']); 
			$status=strip_tags($status);
			$link=$params['link'];
			require_once(DIR_SYSTEM.'library/gk_social_media_share_facebook.php');    
						
			if(!empty($social_data['facebook']['page_id']) && !empty($social_data['facebook']['page_access_token'])) { 
				
				$args = array(
					'access_token'  => $social_data['facebook']['page_access_token'],
					'message'       => $status,
					'picture'       => HTTP_CATALOG."image/".$img,
					'link'          => HTTP_CATALOG.$link
				);
				try {
					$facebook = new Facebook(array(
								'appId'  => $social_data['facebook']['app_id'],
								'secret' => $social_data['facebook']['app_secret'],
								'allowSignedRequest' => false
							));
					$page_id = $social_data['facebook']['page_id'];
					$post_id = $facebook->api("/$page_id/feed","post",$args);
					
					if($post_id && isset($post_id['id'])) {
						$this->db->query('INSERT INTO `'.DB_PREFIX.'gk_social_media_share` SET `product_id` = '.(int)$params['product_id'].', `post_id` = "'.$post_id['id'].'", `post_date` = NOW(), `post_type` = "'.$params['post_type'].'"');
					}
				} catch(Exception $e) {
					trigger_error($e->getMessage());
				}
            }
        }
	}
}
?>