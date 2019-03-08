<?php

namespace Drupal\court\Service;

use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\court\Data\RequestDataInterface;
use Drupal\court\Parser\Parser;
use Drupal\court\Data\ResponseData;
use Drupal\court\UserAgent\Generator;
use Drupal\court\Proxy\Generator as ProxyGenerator;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CourtApiService.
 *
 * @package Drupal\court\Service
 */
class CourtApiService implements CourtApiServiceInterface {

  use LoggerChannelTrait;

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Proxy generator.
   *
   * @var \Drupal\court\Proxy\Generator
   */
  protected $proxyGenerator;

  /**
   * Proxy url.
   *
   * @var string|null
   */
  protected $proxy;

  /**
   * CourtApiService constructor.
   *
   * @param \GuzzleHttp\Client $client
   *   Http client.
   */
  public function __construct(Client $client) {
    $this->client = $client;
    $config = \Drupal::config('court.settings');
    $this->proxyGenerator = ProxyGenerator::create([
      'provider' => $config->get('proxy_provider'),
      'query' => $config->get('proxy_query'),
      'keyIp' => $config->get('proxy_key_ip'),
      'keyPort' => $config->get('proxy_key_port'),
      'keyType' => $config->get('proxy_key_type'),
    ]);
  }

  /**
   * Proxy getter.
   *
   * @return string|null
   *   Url if any.
   */
  protected function getProxy() {

    if (!$this->proxy) {
      $this->proxy = $this->proxyGenerator->getProxy();
    }
    if (!$this->proxy) {
      $this->getLogger('court')->info('Failed to obtain proxy.');
    }

    return $this->proxy;
  }

  /**
   * {@inheritdoc}
   */
  public function getReviewUrl($number) {

    return implode('/', [static::BASE_URL, static::REVIEW_PREFIX, $number]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCasesCount() {

    try {
      $response = $this->client->get(new Uri(static::BASE_URL));
      $code = $response->getStatusCode();
      $body = $response->getBody()->getContents();
      $crawler = new Crawler($body);
      $count = '';
      if ($crawler->filter('#divAllDocuments a')->count()) {
        foreach ($crawler->filter('#divAllDocuments a') as $digit) {
          $count .= $digit->textContent;
        }
      }

      return (int) $count;
    }
    catch (\Exception $exception) {

      return 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function review(RequestDataInterface $requestData) {

    $response = $this->search($requestData);
    // Get review only if connection is ok and any results.
    if (!$response->isEmpty() && $response->getParser()->getSearchResult()->hasResults()) {
      try {
        $number = $requestData->getRegNumber();
        $rawResponse = $this->client->get($this->getReviewUrl($number), [
          'headers' => [
            'User-Agent' => Generator::create()->generate(),
          ],
          'proxy' => $this->getProxy(),
        ]);
        $html = $rawResponse->getBody()->getContents();
        //$html = $this->requestGet($this->getReviewUrl($number));
        $response->addParser(Parser::createReview($html));

        return $response;
      }
      catch (\Exception $exception) {
        $this->getLogger('court')
          ->error($exception->getMessage());
        return $response;
      }
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function search(RequestDataInterface $requestData) {

    $post = $requestData->toApiArray();
    // Special handling for query params.
    $post = http_build_query($post);
    $post = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $post);
    // TODO try catch
    // TODO manage response code
    // TODO manage session
    // TODO query time
    try {
      $response = $this->client->post(
        $requestData->getUrl(),
        [
          'headers' => [
            //'User-Agent' => Generator::create()->generate(),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Content-Length' => strlen($post),
          ],
          'connect_timeout' => 10,
          'body' => $post,
          'proxy' => $this->getProxy(),
        ]
      );
      $code = $response->getStatusCode();
      $headers = $response->getHeaders();
      // how get sessid $headers['Set-Cookie'][0] = "ASP.NET_SessionId=4pwwovh10fuyzq1awl0jzjyj; path=/; HttpOnly";
      $body = $response->getBody()->getContents();

      //$body = $this->requestPost($requestData->getUrl(),$post);
      return ResponseData::create()
        ->addParser(Parser::createSearch($body));
    }
    catch (\Exception $exception) {
      $this->getLogger('court')
        ->error($exception->getMessage());
      return ResponseData::createEmpty();
    }
  }

  protected function requestGet($url) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, Generator::create()->generate());
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_PROXYPORT, '40329');
    curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTPS');
    curl_setopt($ch, CURLOPT_PROXY, '85.223.157.204');
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $this->getLogger('court')->info($response);
    if ($curl_error) {
      throw new \Exception($curl_error);
    }
    // close the connection, release resources used
    curl_close($ch);

    return $response;
  }

  protected function requestPost($url, $body) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, Generator::create()->generate());
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_PROXYPORT, '40329');
    curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTPS');
    curl_setopt($ch, CURLOPT_PROXY, '85.223.157.204');

    //curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
    function HandleHeaderLine( $curl, $header_line ) {
      echo "<br>YEAH: ".$header_line; // or do whatever
      return strlen($header_line);
    }
    $response = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $this->getLogger('court')->info($response);
    if ($curl_error) {
      throw new \Exception($curl_error);
    }
    // close the connection, release resources used
    curl_close($ch);

    return $response;
  }

}

/*$results = [];
$category1ID = 5139;
$categories2 = CaseCategory2::getList();
$category2 = $categories2[$category1ID];
$total = count($category2);
$start = 400;
$limit = 466;
$i = 0;
foreach ($category2 as $category2ID => $label) {
  if($i < $start)  {
    $i++;
    continue;
  }
  if($i >= $limit)  {
    break;
  }
  $searchRequestData = SearchRequestData::create()
    ->addCaseCategory1($category1ID)
    ->addCaseCategory2($category2ID);
  $post = $searchRequestData->getParams();
  $post = http_build_query($post);
  $post = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $post);
  $response = $this->client->post(
    new Uri($searchRequestData->getUrl()),
    [
      'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:38.0) Gecko/20100101 Firefox/38.0',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Content-Length' => strlen($post),
      ],
      'connect_timeout' => 10,
      'body' => $post,
    ]
  );
  $code = $response->getStatusCode();
  $headers = $response->getHeaders();
  // how get sessid $headers['Set-Cookie'][0] = "ASP.NET_SessionId=4pwwovh10fuyzq1awl0jzjyj; path=/; HttpOnly";
  $body = $response->getBody()->getContents();
  $crawler = new Crawler($body);
  if ($options = $crawler->filter('#CaseCat3 option')->count()) {
    foreach ($crawler->filter('#CaseCat3 option') as $option) {
      $value = $option->nodeValue;
      $id = $option->getAttribute('value');
      $results[$category1ID][$category2ID][$id] = $value;
    }
    //$summary = $crawler->filter('#caseCategory3')->first()->html();
  }
  sleep(1);
  $i++;
}
$a = 1;
exit;*/

/*
  $ch = curl_init($requestData->getUrl());
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:38.0) Gecko/20100101 Firefox/38.0");
  curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, TRUE);
  curl_setopt($ch, CURLOPT_POST, TRUE);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  //curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
  function HandleHeaderLine( $curl, $header_line ) {
    echo "<br>YEAH: ".$header_line; // or do whatever
    return strlen($header_line);
  }
  $response2 = curl_exec($ch);
  $curl_errno = curl_errno($ch);
  $curl_error = curl_error($ch);
  // close the connection, release resources used
  curl_close($ch);
*/