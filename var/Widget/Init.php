<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Typecho Blog Platform
 *
 * @copyright  Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id$
 */

/**
 * 初始化模块
 *
 * @package Widget
 */
class Widget_Init extends Typecho_Widget
{
    /**
     * 入口函数,初始化路由器
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        /** 对变量赋值 */
        $options = $this->widget('Widget_Options');

        /** 语言包初始化 */
        if ($options->lang && $options->lang != 'zh_CN') {
            $dir = defined('__TYPECHO_LANG_DIR__') ? __TYPECHO_LANG_DIR__ : __TYPECHO_ROOT_DIR__ . '/usr/langs';
            Typecho_I18n::setLang($dir . '/' . $options->lang . '.mo');
        }

        /** cookie初始化 */
        Typecho_Cookie::setPrefix($options->rootUrl);

        /** 初始化charset */
        Typecho_Common::$charset = $options->charset;

        /** 初始化exception */
        Typecho_Common::$exceptionHandle = 'Widget_ExceptionHandle';

        /** 设置路径 */
        if (defined('__TYPECHO_PATHINFO_ENCODING__')) {
            $pathInfo = $this->request->getPathInfo(__TYPECHO_PATHINFO_ENCODING__, $options->charset);
        } else {
            $pathInfo = $this->request->getPathInfo();
        }

        Typecho_Router::setPathInfo($pathInfo);

        /** 路由选择没有从数据库读取，在这里加入自定义的路由 */
        Helper::addRoute('api/packlist', '/api/packlist', 'Widget_SanVersionCtrl', 'getPackedList');
        Helper::addRoute('api/curversion', '/api/curversion', 'Widget_SanVersionCtrl', 'getCurrentVersion');
        Helper::addRoute('api/dopack', '/api/doPack', 'Widget_SanVersionCtrl', 'doPack');
        Helper::addRoute('api/getpacktype', '/api/getpacktype', 'Widget_SanVersionCtrl', 'getPackType');
        Helper::addRoute('api/getpackageurl', '/api/getpackageurl', 'Widget_SanVersionCtrl', 'getPackageUrl');

        Helper::addRoute('index', '/', 'Widget_SanVersionCtrl', 'render');
        Helper::addRoute('svnup', '/svnup.php', 'Widget_SanVersionCtrl', 'render');
        Helper::addRoute('packlist', '/packlist.php', 'Widget_SanVersionCtrl', 'render');
        Helper::addRoute('packCommand', '/packcommand.php', 'Widget_SanVersionCtrl', 'render');

        /** 初始化路由器 */
        Typecho_Router::setRoutes($options->routingTable);

        /** 初始化插件 */
        // Typecho_Plugin::init($options->plugins);

        /** 初始化回执 */
        $this->response->setCharset($options->charset);
        $this->response->setContentType($options->contentType);

        /** 默认时区 */
        if (function_exists("ini_get") && !ini_get("date.timezone") && function_exists("date_default_timezone_set")) {
            @date_default_timezone_set('UTC');
        }

        /** 初始化时区 */
        // Typecho_Date::setTimezoneOffset($options->timezone);

        // /** 开始会话, 减小负载只针对后台打开session支持 */
        // if ($this->widget('Widget_User')->hasLogin()) {
        //     @session_start();
        // }

        /** 开启session */
        @session_start();

        // /** 监听缓冲区 */
        // ob_start();
    }
}
