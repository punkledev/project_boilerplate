<?php

/**
 * WORDPRESS ROUTINES, includes rudimentary disk cache
 *
 */

class WordpressBridge {

    var $wordpress_location = '/wp-admin/admin-ajax.php';
    var $cache_location = '/tmp';

    /**
     * Instantiate
     */
    public function __construct ($wordpress_location = false, $cache_location = false) 
    {
        if ($wordpress_location) $this->wordpress_location = $wordpress_location;
        if ($cache_location) $this->cache_location = $cache_location;
    }

    /**
     * Make a CURL request to the wordpress backend;
     */
    private function makeGetRequest ($url) 
    {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $retval = curl_exec($ch);  
        curl_close($ch);
        // only return if this is valid json - the plugin will throw zero in any other case
        return json_decode($retval) ? $retval : 0;  
    }

    /**
     * Get a cached item or return false
     */
    private function getCache($page_name) 
    {
        $page_hash = $this->cache_location . '/' . md5($page_name) . '.json';
        if (file_exists($page_hash)) {
            return file_get_contents($page_hash);
        } else {
            return false;
        }
    }

    /**
     * Save new cached item
     */
    private function setCache($page_name, $payload) 
    {
        $page_hash = $this->cache_location . '/' . md5($page_name) . '.json';
        $fh = fopen($page_hash, 'w+');
        $retval = fwrite($fh, $payload);
        fclose($fh);
        return $retval;
    }

    /**
     * Delete all cached items acording to config
     */
    public function clearCache () 
    {
        array_map('unlink', glob($this->cache_location . '/*.json'));
        return true;
    }

    /**
     * Delete a single cache item only (for WP hooks specifically)
     */
    public function clearCacheItem ($page_name) 
    {
        $page_hash = md5($page_name);
        // is it even in cache?
        $cached = $this->getCache($page_name);
        if ($cached) {
            // yeah, we have this page cached; unlink it;
            unlink($this->cache_location . '/' . $page_hash . '.json');
            return true;
        } else return false;
    }

    /**
     * Retrieve JSON data for an action
     */
    public function getAction ($action, $params = array()) 
    {
        // compile a unique action ID
        $action_id = $action;
        foreach ($params as $var=>$val) $action_id .= ($var . '-' . $val);
        $page_hash = md5($action_id);

        $cached = $this->getCache($action_id);
        // check if we have a cached version of this page
        if ( ! $cached) {
            // combine the request url to the plugin
            $url = $this->wordpress_location . '?action=' . $action;
            foreach ($params as $var=>$val) $url .= ('&' . $var . '=' . urlencode($val));

            // get the data
            $data = $this->makeGetRequest($url);
            // do we have data?
            if ($data) {
                // save it to cache
                $this->setCache($action_id, $data);
                // return everything
                return json_decode($data);
            } else return false;
        } else {
            // return cached value
            return json_decode($cached);
        }
    }

}
