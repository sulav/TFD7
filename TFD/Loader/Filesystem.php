<?php
/**
 * Loads template from the filesystem.
 *
 * Part of the Drupal twig extension distribution
 * http://renebakx.nl/twig-for-drupal
 */

class TFD_Loader_Filesystem extends Twig_Loader_Filesystem {
  protected $resolverCache;
  protected $apcprefix = FALSE;

  public function __construct() {
    parent::__construct(array());
    $this->resolverCache = array();
    if (extension_loaded('apc')) {
      global $drupal_hash_salt;
      $this->apcprefix = 'tfd.' . $drupal_hash_salt . '.';
      if ($resolvercache = apc_fetch($this->apcprefix . 'resolver')) {
        $this->resolverCache = $resolvercache;
      }
    }
  }

  // TODO: Figure out if this can be cached in APC as well.
  public function getCacheKey($name) {

    $found = FALSE;
    if (isset($this->cache[$name])) {
      $found = TRUE;
      return $this->cache[$name];
    }
    elseif (is_readable($name)) {
      $this->cache[$name] = $name;
      $found = TRUE;
    }
    else {
      $lookup_paths = twig_get_discovered_templates(); // Very expensive call
      $fullTemplatePath = $lookup_paths[$name];
      if (is_readable($fullTemplatePath)) {
        $this->cache[$name] = $fullTemplatePath;
        $found = TRUE;
      }
    }
    if (!$found) {
      throw new RuntimeException(sprintf('Unable to load template "%s"', $name));
    }
    return $this->cache[$name];
  }


  public function getSource($filename) {
    return file_get_contents($this->getCacheKey($filename));
  }


  public function findTemplate($name) {
    if (!isset($this->resolverCache[$name])) {
      $found = FALSE;
      if ($fullPath = $this->getCacheKey($name)) {
        $this->resolverCache[$name] = $fullPath;
        if ($this->apcprefix) {
          apc_store($this->apcprefix . 'resolver', $this->resolverCache);
        }
        $found = TRUE;
      }
      if (!$found) {
        throw new RuntimeException(sprintf('Unable to load template "%s"', $name));
      }
    }
    return $this->resolverCache[$name];
  }

  public function isFresh($name, $time) {
    if (!is_readable($name)) {
      $name = $this->findTemplate($name);
    }
    return filemtime($name) <= $time;
  }

}

