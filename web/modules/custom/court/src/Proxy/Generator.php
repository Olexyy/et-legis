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

  protected $connectTimeout;

  protected $proxies;

  protected $proxy;

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
    $this->connectTimeout = !empty($options['connectTimeout']) ? $options['connectTimeout'] : 3;
    $this->options = $options;
  }

  /**
   * Getter for fresh structure.
   *
   * @return $this
   *   Values.
   */
  public function generate() {

    $url = 'https://www.proxynova.com/proxy-server-list/country-ua/';
    $response = $this->client->get($url, [
      'User-Agent' => \Drupal\court\UserAgent\Generator::create()->generate(),
      'connect_timeout' => $this->connectTimeout,
    ]);
    $content = $response->getBody()->getContents();
    $this->proxies = $this->parse($content);

    return $this;
  }

  /**
   * Getter.
   *
   * @return Proxy[]
   *   Proxy list.
   */
  public function getProxies() {

    return $this->proxies;
  }

  /**
   * Getter.
   *
   * @return Proxy[]
   *   Proxy list.
   */
  public function getProxy() {

    return $this->proxy;
  }

  /**
   * Proxies setter.
   *
   * @param Proxy[]|array $proxies
   *   Proxies.
   */
  public function setProxies($proxies) {

    $this->proxies = $proxies;
  }

  /**
   * @param $content
   *
   * @return array|\Drupal\court\Proxy\Proxy[]
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
        'connect_timeout' => $this->connectTimeout,
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
  public function findLive() {

    foreach ($this->proxies as $index => $proxy) {
      if ($proxy->upTime >= 80) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
        else {
          unset($this->proxies[$index]);
        }
      }
    }
    foreach ($this->proxies as $index => $proxy) {
      if ($proxy->upTime >= 50 && $proxy->upTime < 80) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
        else {
          unset($this->proxies[$index]);
        }
      }
    }
    foreach ($this->proxies as $index => $proxy) {
      if ($proxy->upTime < 50) {
        if ($this->ping($proxy)) {
          return $proxy;
        }
        else {
          unset($this->proxies[$index]);
        }
      }
    }

    return NULL;
  }

}
