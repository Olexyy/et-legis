<?php

namespace Drupal\court\Proxy;

/**
 * Class Proxy.
 *
 * @package Drupal\court\Proxy
 */
class Proxy {

  /**
   * Type (http|https)
   *
   * @var string
   */
  public $type;

  /**
   * Ip.
   *
   * @var int
   */
  public $ip;

  /**
   * Port.
   *
   * @var string
   */
  public $port;

  /**
   * Speed (mbs)
   *
   * @var int
   */
  public $speed;

  /**
   * Up time in percents.
   *
   * @var int
   */
  public $upTime;

  /**
   * Proxy constructor.
   *
   * @param string $type
   *   Type.
   * @param string $ip
   *   Ip.
   * @param string $port
   *   Port.
   */
  public function __construct($type, $ip, $port) {

    $this->type = $type;
    $this->ip = $ip;
    $this->port = $port;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   String.
   */
  public function __toString() {

    return $this->toString();
  }

  /**
   * Casts object to string.
   *
   * @return string
   *   String.
   */
  public function toString() {

    return "{$this->type}://{$this->ip}:{$this->port}";
  }

}
