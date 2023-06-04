<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
/**
 * Migrate Typecho Data To Mog
 *
 * @package MogRun
 * @author wibus
 * @version 1.0.0
 * @link https://mog.js.org
 */
class MogRun_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        // Add a new page to the admin panel
        Helper::addPanel(1, 'MogRun/manage.php', 'MogRun 面板', 'MogRun 面板', 'administrator');
        // Add a action
        Helper::addAction('MogRun', 'MogRun_Action');
    }

    public static function deactivate()
    {
        // Remove the page from the admin panel
        Helper::removePanel(1, 'MogRun/manage.php');
        // Remove the action
        Helper::removeAction('MogRun_Action');
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}


}
