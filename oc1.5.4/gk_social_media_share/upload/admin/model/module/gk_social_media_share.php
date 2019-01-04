<?php
/****
 * GoodKoding - Social Media Share
 * Version 1.0
 * Shares product information to Facebook when a product is added or edited
 * Developed by GoodKoding
 * www.goodkoding.com
 ****/
class ModelModuleGkSocialMediaShare extends Model {
    public function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "gk_social_media_share`(
                    `gk_social_media_share_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                    `product_id` int(10) unsigned NOT NULL,
                    `post_id` varchar(80) COLLATE utf8_bin NOT NULL,
                    `post_date` datetime NOT NULL,
                    `post_type` enum('add','edit') COLLATE utf8_bin DEFAULT NULL,
                    PRIMARY KEY (`gk_social_media_share_id`)
                )";
        $this->db->query($query);
    }
    
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "gk_social_media_share`");
    }
}
?>