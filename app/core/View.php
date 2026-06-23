<?php
namespace App\Core;

class View
{
    private static string $viewsPath = '';

    public static function init(): void
    {
        self::$viewsPath = dirname(__DIR__) . '/views';
    }

    public static function render(string $template, array $data = [], string $layout = 'main'): void
    {
        if (empty(self::$viewsPath)) self::init();
        $content = self::capture($template, $data);
        $layoutFile = self::$viewsPath . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            // Make all data vars + $content available in layout scope
            extract($data, EXTR_SKIP);
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function capture(string $template, array $data = []): string
    {
        if (empty(self::$viewsPath)) self::init();
        $file = self::$viewsPath . '/pages/' . $template . '.php';
        if (!file_exists($file)) {
            return "<p style='color:red;padding:20px'>View not found: <code>$template</code></p>";
        }
        // Extract data into local scope for the view
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean() ?: '';
    }

    public static function partial(string $name, array $data = []): void
    {
        if (empty(self::$viewsPath)) self::init();
        extract($data, EXTR_SKIP);
        $file = self::$viewsPath . '/components/' . $name . '.php';
        if (file_exists($file)) include $file;
    }

    public static function escape(?string $str): string
    {
        return Security::escapeHtml($str);
    }

    public static function e(?string $str): string
    {
        return self::escape($str);
    }

    public static function escapeAttr(?string $str): string
    {
        return Security::escapeHtmlAttr($str);
    }

    public static function escapeJs(?string $str): string
    {
        return Security::escapeJs($str);
    }
}
