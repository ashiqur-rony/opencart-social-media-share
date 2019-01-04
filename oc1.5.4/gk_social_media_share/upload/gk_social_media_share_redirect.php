<?php
/****
 * GoodKoding - Social Media Share
 * Version 1.0
 * Shares product information to Facebook when a product is added or edited
 * Developed by GoodKoding
 * www.goodkoding.com
 ****/
    if(!isset($_GET) || !isset($_GET['code']) || strlen($_GET['code']) < 1) {
        redirect("index.php");
    }
    session_start();
	require_once('config.php');
    require_once(DIR_SYSTEM . 'startup.php');
	// Registry
	$registry = new Registry();

	// Loader
	$loader = new Loader($registry);
	$registry->set('load', $loader);

	// Config
	$config = new Config();
	$registry->set('config', $config);

	// Database 
	$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
	$registry->set('db', $db);
	
	$config->set('config_store_id', 0);
	
	$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `key` LIKE 'gk_social_media_share'");

	foreach ($query->rows as $setting) {
		if (!$setting['serialized']) {
			$config->set($setting['key'], $setting['value']);
		} else {
			$config->set($setting['key'], unserialize($setting['value']));
		}
	}
	
	$social_data = unserialize(base64_decode($config->get('gk_social_media_share')));
	
	require_once(DIR_SYSTEM.'library/gk_social_media_share_facebook.php');
	
	$facebook = new Facebook(array(
        'appId'  => $social_data['facebook']['app_id'],
        'secret' => $social_data['facebook']['app_secret'],
        'allowSignedRequest' => false
    )); 
    
    $user = $facebook->getUser();
    
	$social_data['facebook']['user'] = $user;
    
    $token = $_SESSION['token'];
	$route = $_SESSION['route'];
    $url = $_SESSION['admin_http_server']."index.php?route=$route&token=$token";
	if(isset($_GET) && !empty($_GET)) {
        foreach($_GET as $key => $value) {
            if($key !== 'route' || $key !== 'token') {
                $url .= "&$key=$value";
            }
        }
    }
    $url .= "&user=$user";
    
    $query = $db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '".base64_encode(serialize($social_data))."' WHERE `store_id` = '0' AND `group` = 'gk_social_media_share' AND `key` = 'gk_social_media_share'");
    
	header("Location: $url");
	exit;
?>