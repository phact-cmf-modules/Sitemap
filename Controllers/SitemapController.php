<?php
/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @date 06/04/17 08:30
 */
namespace Modules\Sitemap\Controllers;

use Modules\Sitemap\Helpers\SitemapProcessor;
use Phact\Controller\Controller;
use Phact\Di\ContainerInterface;
use Phact\Request\HttpRequestInterface;
use Phact\Template\RendererInterface;

class SitemapController extends Controller
{
    /**
     * @var ContainerInterface
     */
    protected $_container;

    public function __construct(ContainerInterface $container, HttpRequestInterface $request, RendererInterface $renderer = null)
    {
        $this->_container = $container;
        parent::__construct($request, $renderer);
    }

    public function xmlIndex()
    {
        $processor = $this->getProcessor();
        $sitemap = $processor->getSitemapIndexXml();
        header("Content-Type: text/xml");
        echo $sitemap->asXML();
    }

    public function xmlItem($name)
    {
        $processor = $this->getProcessor();
        $sitemap = $processor->getSitemapXml($name);
        if (!$sitemap) {
            $this->error(404);
        }
        header("Content-Type: text/xml");
        echo $sitemap->asXML();
    }

    /**
     * @throws \Phact\Exceptions\ContainerException
     * @throws \Phact\Exceptions\NotFoundContainerException
     * @throws \ReflectionException
     * @return SitemapProcessor
     */
    public function getProcessor(): SitemapProcessor
    {
        return $this->_container->construct(SitemapProcessor::class);
    }
}