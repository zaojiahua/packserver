<?php

define("__TYPECHO_DEBUG__", true);

/** 定义根目录 */
define('__TYPECHO_ROOT_DIR__', dirname(__FILE__));

/** 定义插件目录(相对路径) */
define('__TYPECHO_PLUGIN_DIR__', '/usr/plugins');

/** 定义模板目录(相对路径) */
define('__TYPECHO_THEME_DIR__', '/usr/themes');

/** 定义前端代码仓库路径 */
define('__SAN_WORK_PATH__', '/Users/san/frontend');

/** 定义前端Python脚本所在的路径 */
define('__PYTHON_SCRIPT_PATH__', __SAN_WORK_PATH__ . '/Game/san_slg/Script');

/** 定义log文件路径，记录shell执行过程的log信息以及其他信息 */
define('__SHELL_LOG_PATH__', '/log');

/** 设置包含路径 */
@set_include_path(get_include_path() . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . '/var' . PATH_SEPARATOR .
__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__);

/** 载入API支持 */
require_once 'Typecho/Common.php';

/** 载入Response支持 */
require_once 'Typecho/Response.php';

/** 载入异常支持 */
require_once 'Typecho/Exception.php';

/** 载入路由器支持 */
require_once 'Typecho/Router.php';

/** 程序初始化 */
Typecho_Common::init();

/** 初始化组件 */
Typecho_Widget::widget('Widget_Init');

/** 开始路由分发 */
Typecho_Router::dispatch();

echo "index.php";
