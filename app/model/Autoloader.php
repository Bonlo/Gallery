<?php
class Autoloader
{
    public static $path = '';
    static function register($your_path = 'app/')
    {
        self::$path = $your_path;
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    static function autoload($class_name)
    {
        $path = self::$path;
        require $path . 'model/' . $class_name . '.php';
    }
}
