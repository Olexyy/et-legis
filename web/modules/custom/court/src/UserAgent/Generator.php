<?php

namespace Drupal\court\UserAgent;

/**
 * Class Generator.
 *
 * @package Drupal\court\UserAgent
 */
class Generator {

  const CHROME = 'chrome';
  const EXPLORER = 'explorer';
  const FIREFOX = 'firefox';
  const OPERA = 'opera';
  const SAFARI = 'safari';

  /**
   * Inline constructor.
   *
   * @return $this
   *   Instance.
   */
  public static function create() {

    return new static();
  }

  /**
   * Getter.
   *
   * @return array|string[]
   *   Browsers list.
   */
  protected function getBrowsers() {

    return [
      static::CHROME, static::EXPLORER, static::FIREFOX, static::OPERA, static::SAFARI,
    ];
  }

  /**
   * Generator.
   *
   * @param null|string $browser
   *   Browser, defined as constant.
   *
   * @return string|null
   *   User agent if any.
   */
  public function generate($browser = NULL) {

    if (!$browser || !in_array($browser, $this->getBrowsers())) {
      $browser = $this->getBrowsers()[mt_rand(0, count($this->getBrowsers()) - 1)];
    }
    $path = dirname(__FILE__) . "/Data/{$browser}.txt";
    if ($list = file_get_contents($path)) {
      if ($list = array_filter(explode(PHP_EOL, $list))) {

        return trim($list[mt_rand(0, count($list) - 1)]);
      }
    }

    return NULL;
  }

}
