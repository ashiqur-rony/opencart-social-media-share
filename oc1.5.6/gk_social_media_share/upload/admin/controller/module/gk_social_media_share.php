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
    }
    
    public function uninstall() {
        $this->load->model('module/gk_social_media_share');
        $this->model_module_gk_social_media_share->uninstall();
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('gk_social_media_share', array('gk_social_media_share'=>base64_encode(serialize(array()))));
    }
    
	public function index() {  
        $this->load->language('module/gk_social_media_share');
		$this->document->setTitle($this->language->get('page_title'));
        $this->data['success'] = false;
        $this->data['error_warning'] = false;
        
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
                            $this->data['success'] = $this->language->get('text_success');
                        }
                        else {
                            //We got the user. Save it to DB
                            $this->model_setting_setting->editSetting('gk_social_media_share',array('gk_social_media_share' => base64_encode(serialize($social_data))));
                            
                            //Fetch the pages the user is admin of
                            $result = $facebook->api("/".$social_data['facebook']['user']."/accounts");
                            if(!empty($result["data"])) {
                                $this->data['pages'] = $result['data'];
                            }
                        }
                    }
				} catch (FacebookApiException $e) {
					$this->data['error_warning'] = $e;
				}
			} else {
				$this->data['error_warning'] = $this->language->get('fb_authenticate_error');
				$_SESSION['token'] = $_GET['token'];
				$_SESSION['route'] = $_GET['route'];
                $_SESSION['admin_http_server'] = HTTP_SERVER;
				$this->data['fb_login_url'] = $facebook->getLoginUrl(
						array(
							"scope" => "manage_pages, publish_pages",
							"redirect_uri" => HTTP_CATALOG."gk_social_media_share_redirect.php"
						)
					); 
			 }
		 }
        
        $this->data['social_data'] = $social_data;
        
        $this->data['token'] = $this->session->data['token'];
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_module'] = $this->language->get('text_module');
		$this->data['fb_title'] = $this->language->get('fb_title');
		$this->data['fb_login_text'] = $this->language->get('fb_login_text');
		$this->data['fb_logout_text'] = $this->language->get('fb_logout_text');
        
		$this->data['app_id'] = $this->language->get('app_id');
		$this->data['app_secret'] = $this->language->get('app_secret');
		$this->data['share_fb'] = $this->language->get('share_fb');
		$this->data['share_add'] = $this->language->get('share_add');
		$this->data['share_edit'] = $this->language->get('share_edit');
        $this->data['fb_select_page'] = $this->language->get('fb_select_page');
        $this->data['fb_select_page_text'] = $this->language->get('fb_select_page_text');
        $this->data['fb_selected_page'] = $this->language->get('fb_select_page');
        $this->data['fb_selected_page_text'] = $this->language->get('fb_select_page_text');
        
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['button_remove'] = $this->language->get('button_remove');
        
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/gk_social_media_share', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['fb_logout_url'] = $this->url->link('module/gk_social_media_share/logout', 'token=' . $this->session->data['token'], 'SSL');
        
		$this->data['action'] = $this->url->link('module/gk_social_media_share', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');	 
        
        $this->template = 'module/gk_social_media_share.tpl';
		$this->children = array(
			'common/header',
			'common/footer',
		);
				
		$this->response->setOutput($this->render());
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
}
?>