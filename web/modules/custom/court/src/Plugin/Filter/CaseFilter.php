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

    $text = preg_replace_callback('/\[iframe url="(.+)".*title="(.*)".*width="([\d]+|auto)".*height="(\d+)".*\]/i',
      function($matches) {

      $url = $matches[1];
      $title = $matches[2];
      $width = $matches[3];
      $height = $matches[4];
      $width = ($width == 'auto')? "style=width:100%" : "style='width:'{$width}px";
      // 1) If url is valid.
      if (UrlHelper::isValid($url, TRUE)) {
        try {
          $response = $this->httpClient->head($url);
          // 2) If response is 200.
          if ($response->getStatusCode() == 200) {
            // 3) If page is not forbidden to be accessed through iframe.
            if (!$response->hasHeader('X-Frame-Options')) {
              return '<div class = "iframe-wrapper" style="text-align:center;">
              <iframe title="' . $title . '" src="' . $url . '" frameborder="0" allowfullscreen height="' . $height . '" ' . $width . '></iframe></div>';
            }
          }
        }
        catch(\Exception $exception) {
          return '';
        }
      }
      return '';
    }, $text);

    return new FilterProcessResult($text);
  }

}
