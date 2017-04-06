<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @company HashStudio
 * @site http://hashstudio.ru
 * @date 06/04/17 08:31
 */

namespace Modules\Sitemap\Helpers;


use Modules\Sitemap\Contrib\Sitemap;
use Phact\Helpers\Paths;
use Phact\Main\Phact;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleXMLElement;

class SitemapProcessor
{
    public static $sitemapFolder = 'Sitemap';

    public static function getSitemaps()
    {
        $modulesPath = Paths::get('Modules');
        $activeModules = Phact::app()->getModulesList();
        $classes = [];
        foreach ($activeModules as $module) {
            $path = implode(DIRECTORY_SEPARATOR, [$modulesPath, $module, self::$sitemapFolder]);
            if (is_dir($path)) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename) {
                    // filter out "." and ".."
                    if ($filename->isDir()) continue;
                    $name = $filename->getBasename('.php');
                    $sitemapName = self::createName($module, $name);
                    $classes[$sitemapName] = implode('\\', ['Modules', $module, self::$sitemapFolder, $name]);
                }
            }
        }
        return $classes;
    }

    public static function getSitemapIndexXml()
    {
        $sitemap = new SimpleXMLElement("<sitemapindex></sitemapindex>");
        $sitemap->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $router = Phact::app()->router;
        foreach (self::getSitemaps() as $name => $item) {
            $url = $sitemap->addChild('sitemap');
            $url->addChild('loc', Phact::app()->request->getHostInfo() . $router->url('sitemap:xml_item', [
                'name' => $name
            ]));
        }
        return $sitemap;
    }

    public static function getSitemapXml($name)
    {
        $name = mb_strtolower($name, 'UTF-8');
        $items = self::getSitemaps();
        if (!isset($items[$name])) {
            return null;
        }
        $sitemapClass = $items[$name];
        /** @var Sitemap $sitemapItem */
        $sitemapItem = new $sitemapClass();
        $data = $sitemapItem->getData();
        $sitemap = new SimpleXMLElement("<urlset></urlset>");
        $sitemap->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($data as $item) {
            $url = $sitemap->addChild('url');
            foreach ($item as $attribute => $value) {
                if (in_array($attribute, ['name', 'level'])) {
                    continue;
                }
                if (!is_null($value)) {
                    $url->addChild($attribute, $value);
                }
            }
        }

        return $sitemap;
    }

    public static function createName($module, $name)
    {
        return mb_strtolower(implode('-', [$module, $name]), "UTF-8");
    }
}