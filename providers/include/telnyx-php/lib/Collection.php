<?php

namespace Telnyx;

/**
 * Class Collection
 *
 * @property string $object
 * @property string $url
 * @property mixed $data
 *
 * @package Telnyx
 */
class Collection extends TelnyxObject implements \IteratorAggregate
{
    const OBJECT_NAME = "list";

    use ApiOperations\Request;

    protected $_requestParams = [];

    /**
     * @return string The base URL for the given class.
     */
    public static function baseUrl()
    {
        return Telnyx::$apiBase;
    }

    public function setRequestParams($params)
    {
        $this->_requestParams = $params;
    }

    public function all($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('get', $url, $params, $opts);
        $this->_requestParams = $params;

        // This is needed for nextPage() and previousPage()
        $response['url'] = $url;

        $obj = Util\Util::convertToTelnyxObject($response, $opts);
        $obj->setRequestParams($params);

        return $obj;
    }

    public function create($params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->_requestParams = $params;
        return Util\Util::convertToTelnyxObject($response, $opts);
    }

    public function retrieve($id, $params = null, $opts = null)
    {
        list($url, $params) = $this->extractPathAndUpdateParams($params);

        $id = Util\Util::utf8($id);
        $extn = urlencode($id);
        list($response, $opts) = $this->_request(
            'get',
            "$url/$extn",
            $params,
            $opts
        );
        $this->_requestParams = $params;
        return Util\Util::convertToTelnyxObject($response, $opts);
    }

    /**
     * @return \ArrayIterator An iterator that can be used to iterate
     *    across objects in the current page.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @return Util\AutoPagingIterator An iterator that can be used to iterate
     *    across all objects across all pages. As page boundaries are
     *    encountered, the next page will be fetched automatically for
     *    continued iteration.
     */
    public function autoPagingIterator()
    {
        return new Util\AutoPagingIterator($this, $this->_requestParams);
    }

    /**
     * Returns an empty collection. This is returned from {@see nextPage()}
     * when we know that there isn't a next page in order to replicate the
     * behavior of the API when it attempts to return a page beyond the last.
     *
     * @param array|string|null $opts
     * @return Collection
     */
    public static function emptyCollection($opts = null)
    {
        return Collection::constructFrom(['data' => []], $opts);
    }

    /**
     * Returns true if the page object contains no element.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * See if there are more results
     *
     * @return boolean
     */
    public function hasMore() {
        if (isset($this->meta)) {
            if ($this->meta['page_number'] < $this->meta['total_pages']) {
                return true;
            }
        }
        return false;
    }

    /**
     * See if there are previous results
     *
     * @return boolean
     */
    public function hasPrev() {
        if (isset($this->meta)) {
            if ($this->meta['page_number'] > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetches the next page in the resource list (if there is one).
     *
     * This method will try to respect the limit of the current page. If none
     * was given, the default limit will be fetched again.
     *
     * @param array|null $params
     * @param array|string|null $opts
     * @return Collection
     */
    public function nextPage($params = null, $opts = null)
    {
        if (!$this->hasMore()) {
            return static::emptyCollection($opts);
        }

        $params = array_merge(
            $this->_requestParams,
            $params ?: []
        );

        // Remove page number from the next request. Detect both syntaxes
        if (isset($params['page']) && isset($params['page']['number'])) {
            unset($params['page']['number']);
        }
        elseif (isset($params['page[number]'])) {
            unset($params['page[number]']);
        }

        // Set a new page number
        $params['page[number]'] = $this->meta['page_number'] + 1;

        return $this->all($params, $opts);
    }

    /**
     * Fetches the previous page in the resource list (if there is one).
     *
     * This method will try to respect the limit of the current page. If none
     * was given, the default limit will be fetched again.
     *
     * @param array|null $params
     * @param array|string|null $opts
     * @return Collection
     */
    public function previousPage($params = null, $opts = null)
    {
        if (!$this->hasPrev()) {
            return static::emptyCollection($opts);
        }

        $params = array_merge(
            $this->_requestParams,
            $params ?: []
        );

        // Remove page number from the next request. Detect both syntaxes
        if (isset($params['page']) && isset($params['page']['number'])) {
            unset($params['page']['number']);
        }
        elseif (isset($params['page[number]'])) {
            unset($params['page[number]']);
        }

        // Set a new page number
        $params['page[number]'] = $this->meta['page_number'] - 1;

        return $this->all($params, $opts);
    }

    private function extractPathAndUpdateParams($params)
    {
        $url = parse_url($this->url);
        if (!isset($url['path'])) {
            throw new Exception\UnexpectedValueException("Could not parse list url into parts: {$url}");
        }

        if (isset($url['query'])) {
            // If the URL contains a query param, parse it out into $params so they
            // don't interact weirdly with each other.
            $query = [];
            parse_str($url['query'], $query);
            $params = array_merge($params ?: [], $query);
        }

        return [$url['path'], $params];
    }
}
