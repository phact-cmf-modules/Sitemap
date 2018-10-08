<?php

/**
 *
 *
 * All rights reserved.
 *
 * @author Okulov Anton
 * @email qantus@mail.ru
 * @version 1.0
 * @date 05/04/17 15:10
 */

namespace Modules\Sitemap\Contrib;

use DateTime;
use Phact\Orm\QuerySet;
use Phact\Request\HttpRequestInterface;
use Phact\Router\RouterInterface;

abstract class Sitemap
{
    /** @var RouterInterface */
    protected $_router;

    /** @var HttpRequestInterface */
    protected $_request;

    /** @var string */
    protected $_hostInfo;

    public function __construct(RouterInterface $router, HttpRequestInterface $request = null)
    {
        $this->_router = $router;
        $this->_request = $request;
    }

    public function getRouter()
    {
        return $this->_router;
    }

    public function setHostInfo($hostInfo)
    {
        $this->_hostInfo = $hostInfo;
    }

    public function getHostInfo()
    {
        if (!$this->_hostInfo && $this->_request) {
            $this->_hostInfo = $this->_request->getHostInfo();
        }
        return $this->_hostInfo;
    }

    public function url($route, $params = [])
    {
        return $this->getHostInfo() . $this->getRouter()->url($route, $params);
    }

    public function formatDate($date)
    {
        $date = new DateTime($date);
        return $date->format(DATE_W3C);
    }

    /**
     * @return QuerySet|null
     */
    public function getQuerySet()
    {
        return null;
    }

    public function getData()
    {
        $data = $this->getQueryItems();
        $static = $this->getStaticItems();
        if ($static) {
            $data = array_merge($data, $static);
        }
        return $data;
    }

    public function getQueryItems()
    {
        $data = [];
        $qs = $this->getQuerySet();
        if ($qs) {
            $items = $qs->values();
            foreach ($items as $attributes) {
                $data[] = $this->getQueryItemData($attributes);
            }
        }
        return $data;
    }

    public function getStaticItems()
    {
        return [];
    }

    public function getTitle()
    {
        return null;
    }

    /**
     * @param $attributes
     * @return mixed
     *
     * [
     *  'name' => 'Item name'
     *  'loc' => 'http://example.com/item-link',
     *  'lastmod' => '2017-02-12T12:00:00+01:00',
     *  'priority' => '0.5'
     * ]
     *
     */
    public function getQueryItemData($attributes)
    {
        return [
            'name' => $this->getName($attributes),
            'level' => $this->getLevel($attributes),
            'loc' => $this->getLoc($attributes),
            'lastmod' => $this->getLastMod($attributes),
            'priority' => $this->getPriority($attributes),
            'changefreq' => $this->getChangeFreq($attributes)
        ];
    }

    /**
     * Attribute for HTML map
     *
     * @param $attributes
     * @return null
     */
    public function getName($attributes)
    {
        if (isset($attributes['name'])) {
            return $attributes['name'];
        }
        return null;
    }

    /**
     * Attribute for HTML map
     *
     * @param $attributes
     * @return null
     */
    public function getLevel($attributes)
    {
        if (isset($attributes['depth'])) {
            return $attributes['depth'];
        }
        return null;
    }

    public function getLoc($attributes)
    {
        return null;
    }

    public function getLastMod($attributes)
    {
        if (isset($attributes['updated_at'])) {
            return $this->formatDate($attributes['updated_at']);
        }
        return null;
    }

    public function getPriority($attributes)
    {
        return '0.5';
    }

    public function getChangeFreq($attributes)
    {
        return 'monthly';
    }
}
