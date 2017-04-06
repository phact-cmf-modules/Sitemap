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
 * @date 06/04/17 08:30
 */
namespace Modules\Sitemap\Controllers;

use Modules\Sitemap\Helpers\SitemapProcessor;
use Phact\Controller\Controller;
use Phact\Main\Phact;
use SimpleXMLElement;

class SitemapController extends Controller
{
    public function xmlIndex()
    {
        $sitemap = SitemapProcessor::getSitemapIndexXml();
        header("Content-Type: text/xml");
        echo $sitemap->asXML();
    }

    public function xmlItem($name)
    {
        $sitemap = SitemapProcessor::getSitemapXml($name);
        if (!$sitemap) {
            $this->error(404);
        }
        header("Content-Type: text/xml");
        echo $sitemap->asXML();
    }
}