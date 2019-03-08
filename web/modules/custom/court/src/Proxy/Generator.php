<?php

namespace Drupal\court\Proxy;

use GuzzleHttp\Client;

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
   * Getter for proxy ip:post.
   *
   * @return string|null
   *   Proxy if any.
   */
  public function getProxy() {

    $data = $this->getApiData();
    $type = $this->getValue($this->keyType, $data);
    $ip = $this->getValue($this->keyIp, $data);
    $port = $this->getValue($this->keyPort, $data);
    $url = $type ? "$type://$ip" : $ip;

    return $port ? "$url:$port" : $url;
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
  public function getApiData() {

    $url = $this->query ?
      $this->provider . '?' . $this->query : $this->provider;
    try {
      $response = $this->client->get($url);
      $content = $response->getBody()->getContents();
      if ($json = json_decode($content, TRUE)) {
        return $json;
      }
      return [];
    }
    catch (\Exception $exception) {
      return [];
    }
  }

}
