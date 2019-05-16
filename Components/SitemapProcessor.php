<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @date 06/04/17 08:31
 */

namespace Modules\Sitemap\Components;


use Modules\Meta\Interfaces\ModelMetaInterface;
use Modules\Sitemap\Contrib\Sitemap;
use Phact\Application\ModulesInterface;
use Phact\Di\ContainerInterface;
use Phact\Main\Phact;
use Phact\Request\HttpRequestInterface;
use Phact\Router\RouterInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleXMLElement;

class SitemapProcessor
{
    public $sitemapFolder = 'Sitemap';

    /**
     * @var ModulesInterface
     */
    protected $_modules;

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * @var ContainerInterface
     */
    protected $_router;

    /**
     * @var HttpRequestInterface
     */
    protected $_request;

    /**
     * @var string
     */
    protected $_hostInfo;

    public function __construct(ModulesInterface $modules, ContainerInterface $container, RouterInterface $router, HttpRequestInterface $request = null)
    {
        $this->_modules = $modules;
        $this->_container = $container;
        $this->_router = $router;
        $this->_request = $request;
    }

    /**
     * @return string
     */
    public function getHostInfo()
    {
        if (!$this->_hostInfo && $this->_request) {
            $this->_hostInfo = $this->_request->getHostInfo();
        }
        return $this->_hostInfo;
    }

    /**
     * @param $hostInfo string
     */
    public function setHostInfo($hostInfo)
    {
        $this->_hostInfo = $hostInfo;
    }

    /**
     * @return Sitemap[]
     * @throws \Phact\Exceptions\ContainerException
     * @throws \Phact\Exceptions\NotFoundContainerException
     * @throws \ReflectionException
     */
    public function getSitemaps()
    {
        $activeModules = $this->_modules->getModules();
        $sitemaps = [];
        foreach ($activeModules as $moduleName => $module) {
            $path = implode(DIRECTORY_SEPARATOR, [$module->getPath(), $this->sitemapFolder]);
            if (is_dir($path)) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $filename) {
                    // filter out "." and ".."
                    if ($filename->isDir()) continue;
                    $name = $filename->getBasename('.php');
                    $sitemapName = $this->createName($module->getName(), $name);

                    $class = implode('\\', [$module::classNamespace(), $this->sitemapFolder, $name]);

                    if (class_exists($class) && is_a($class, Sitemap::class, true) && ($reflection = new \ReflectionClass($class))) {
                        if (!$reflection->isAbstract()) {
                            /** @var Sitemap $sitemap */
                            $sitemap = $this->_container->construct($class);
                            $sitemap->setHostInfo($this->getHostInfo());
                            $sitemaps[$sitemapName] = $sitemap;
                        }
                    }
                }
            }
        }
        return $sitemaps;
    }

    /**
     * @return SimpleXMLElement
     * @throws \Phact\Exceptions\ContainerException
     * @throws \Phact\Exceptions\NotFoundContainerException
     * @throws \ReflectionException
     */
    public function getSitemapIndexXml()
    {
        $sitemap = new SimpleXMLElement("<sitemapindex></sitemapindex>");
        $sitemap->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($this->getSitemaps() as $name => $item) {
            $url = $sitemap->addChild('sitemap');
            $url->addChild('loc', $this->getHostInfo() . $this->_router->url('sitemap:xml_item', [
                'name' => $name
            ]));
        }
        return $sitemap;
    }

    /**
     * @param $name
     * @return null|SimpleXMLElement
     * @throws \Phact\Exceptions\ContainerException
     * @throws \Phact\Exceptions\NotFoundContainerException
     * @throws \ReflectionException
     */
    public function getSitemapXml($name)
    {
        $name = mb_strtolower($name, 'UTF-8');
        $items = $this->getSitemaps();
        if (!isset($items[$name])) {
            return null;
        }
        $sitemapItem = $items[$name];
        $data = $sitemapItem->getData();
        $sitemap = new SimpleXMLElement("<urlset></urlset>");
        $sitemap->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($data as $item) {
            $url = $sitemap->addChild('url');
            foreach ($item as $attribute => $value) {
                if (in_array($attribute, ['name', 'level'])) {
                    continue;
                }
                if ($value !== null) {
                    $url->addChild($attribute, $value);
                }
            }
        }

        return $sitemap;
    }

    /**
     * @return array
     * @throws \Phact\Exceptions\ContainerException
     * @throws \Phact\Exceptions\NotFoundContainerException
     * @throws \ReflectionException
     */
    public function getSitemapData()
    {
        $sitemaps = [];
        foreach ($this->getSitemaps() as $name => $sitemap) {
            $sitemaps[] = [
                'title' => $sitemap->getTitle(),
                'content' => $sitemap->getData()
            ];
        }
        return $sitemaps;
    }
    
    public function createName($module, $name)
    {
        return mb_strtolower(implode('-', [$module, $name]), "UTF-8");
    }
}
