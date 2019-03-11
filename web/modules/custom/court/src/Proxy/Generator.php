<?php

namespace Drupal\court\Proxy;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Generator.
 *
 * @package Drupal\court\Proxy
 */
class Generator {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client|null
   */
  protected $client;

  /**
   * Provider url.
   *
   * @var string
   */
  protected $provider;

  /**
   * Query string.
   *
   * @var string
   */
  protected $query;

  /**
   * Options.
   *
   * @var array|string[]
   */
  protected $options;

  /**
   * Key of url.
   *
   * @var string
   */
  protected $keyIp;

  /**
   * Key of url.
   *
   * @var string
   */
  protected $keyPort;

  /**
   * Key of url.
   *
   * @var string
   */
  protected $keyType;

  /**
   * Factory method.
   *
   * @param array|string[] $options
   *   Options array.
   * @param \GuzzleHttp\Client|null $client
   *   Http client.
   *
   * @return $this
   *   Instance.
   */
  public static function create(array $options, Client $client = NULL) {

    return new static($options, $client);
  }

  /**
   * Generator constructor.
   *
   * @param array|string[] $options
   *   Options array.
   * @param \GuzzleHttp\Client|null $client
   *   Http client.
   */
  public function __construct(array $options, Client $client = NULL) {

    $this->client = $client ? $client : new Client();
    $this->provider = !empty($options['provider']) ? $options['provider'] : '';
    $this->query = !empty($options['query']) ? $options['query'] : '';
    $this->keyIp = !empty($options['keyIp']) ? $options['keyIp'] : '';
    $this->keyPort = !empty($options['keyPort']) ? $options['keyPort'] : '';
    $this->keyType = !empty($options['keyType']) ? $options['keyType'] : '';
    $this->options = $options;
  }

  /**
   * Value getter, supports dot notation.
   *
   * @param string $key
   *   Key.
   * @param array|null $array
   *   Given array.
   *
   * @return mixed
   *   Value.
   */
  protected function getValue($key, array $array = NULL) {

    if ($array) {
      if (strpos($key, '.') === FALSE) {

        return isset($array[$key]) ? $array[$key] : NULL;
      }
      foreach (explode('.', $key) as $segment) {
        if (is_array($array) && isset($array[$segment])) {
          $array = $array[$segment];
        }
        else {
          return NULL;
        }
      }
      return $array;
    }

    return NULL;
  }

  /**
   * Getter for fresh structure.
   *
   * @return array|string[]
   *   Values.
   */
  public function getProxy() {

    $url = $this->query ?
      $this->provider . '?' . $this->query : $this->provider;
    $url = 'https://www.proxynova.com/proxy-server-list/country-ua/';
    $response = $this->client->get($url, [
      'User-Agent' => \Drupal\court\UserAgent\Generator::create()->generate(),
      'connect_timeout' => 5,
      ]);
    $content = $response->getBody()->getContents();
    // This will return null if none match or parse error.
    try {
      $proxies = $this->parse($content);
      return $this->findLive($proxies);
    }
    catch (\Exception $exception) {
      return NULL;
    }
  }

  /**
   * @param $content
   *
   * @return array
   * @throws \Exception
   */
  public function parse($content) {

    $crawler = new Crawler($content);
    $rows = [];

    if ($crawler->filter('table#tbl_proxy_list tbody tr')->count()) {
      foreach ($crawler->filter('table#tbl_proxy_list tbody tr')->getIterator() as $i => $node) {
        if ($row = $this->processRow($node->textContent)) {
          $rows[] = $row;
        }
      }
    }

    return $rows;
  }

  /**
   * @param $row
   *
   * @return \Drupal\court\Proxy\Proxy
   * @throws \Exception
   */
  public function processRow($row) {

    $elements = preg_split('~\R~', $row);
    foreach ($elements as &$element) {
      $element = trim($element);
    }
    $elements = array_values(array_filter($elements));
    $matches = [];
    if (count($elements) > 7) {
      if ($parsed = preg_match("@document\.write\(\'(.*)\'\.substr\(8\) \+ \'(.*)\'\);@", $elements[0], $matches)) {
        $ip = substr($matches[1], 8, strlen($matches[1])) . $matches[2];
        $port = $elements[1];
        $speed = (int) $elements[2];
        $uptime = (int) $elements[3];
        $proxy = new Proxy('http', $ip, $port);
        $proxy->upTime = $uptime;
        $proxy->speed = $speed;

        return $proxy;
      }
    }

    return NULL;
  }

  public function ping(Proxy $proxy) {

    try {
      return (bool) $this->client->get('https://www.google.com/', [
        'connect_timeout' => 2,
        'proxy' => $proxy->toString(),
      ]);
    }
    catch (\Exception $exception) {
      return FALSE;
    }
  }

  /**
   * @param array|\Drupal\court\Proxy\Proxy[] $proxies
   *
   * @return \Drupal\court\Proxy\Proxy|mixed|null
   */
  public function findLive(array $proxies) {

    foreach ($proxies as $proxy) {
      if ($proxy->upTime >= 80) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
      }
    }
    foreach ($proxies as $proxy) {
      if ($proxy->upTime >= 50 && $proxy->upTime < 80) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
      }
    }
    foreach ($proxies as $proxy) {
      if ($proxy->upTime < 50) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
      }
    }

    return NULL;
  }

}
