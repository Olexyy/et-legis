<?php

namespace Drupal\court\Plugin\Filter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CaseFilter.
 *
 * @Filter(
 *   id = "case_filter",
 *   title = @Translation("Case filter"),
 *   description = @Translation("Embed iframe in article."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   weight = -9
 * )
 * @package Drupal\mi_input_filters\Plugin\Filter
 */
class CaseFilter extends FilterBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'));
  }

  /**
   * IframeFilter constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Client $httpClient) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Use the infogr.am embed code.');
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    $text = preg_replace_callback('/\[case (\d+)\]/i',
      function($matches) {
      $number = $matches[1];
      return '<a href="http://www.reyestr.court.gov.ua/Review/'.$number.'">' . $number . '</a>';
    }, $text);

    return new FilterProcessResult($text);
  }

}
