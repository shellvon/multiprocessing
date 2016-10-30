<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午4:05
 *
 * @version 1.0
 */

namespace MultiProcessing\Bootstrap;

defined('MP_ROOT') or define('MP_ROOT', __DIR__.'/../');

class Autoloader
{
    public static $appDir = array();

    public function __construct()
    {
        self::$appDir = array(
            MP_ROOT,
        );
    }

    public static function instance()
    {
        return new self();
    }
    public function addRoot($root)
    {
        self::$appDir[] = $root;

        return $this;
    }
    public function init()
    {
        spl_autoload_register(array($this, 'loadByNameSpace'));

        return $this;
    }

    public function loadByNamespace($name)
    {
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        $needle = 'MultiProcessing';
        foreach (static::$appDir as $root) {
            if ((strpos($classPath, $needle)) === 0) {
                $classPath = substr($classPath, strlen($needle));
            }
            $file = $root.$classPath.'.php';
            if (is_file($file)) {
                require_once $file;

                return true;
            }
        }

        return false;
    }
}
