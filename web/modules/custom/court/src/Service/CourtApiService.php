<?php

namespace Drupal\court\Service;

use Drupal\court\Utils\RequestDataInterface;
use Drupal\court\Utils\SearchResponseData;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CourtApiService.
 *
 * @package Drupal\court\Service
 */
class CourtApiService implements CourtApiServiceInterface {

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * CourtApiService constructor.
   *
   * @param \GuzzleHttp\Client $client
   *   Http client.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public function getCasesCount() {

    try {
      $response = $this->client->get(new Uri('http://reyestr.court.gov.ua/'));
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
  public function request(RequestDataInterface $searchRequestData) {
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

    $post = $searchRequestData->toApiArray();
    // Special handling for query params.
    $post = http_build_query($post);
    $post = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $post);
    // TODO try catch
    // TODO manage response code
    // TODO dynamic user agent
    // TODO manage session
    // TODO query time
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
    if ($crawler->filter('#divFooterSearch .td1')->count()) {
      $summary = $crawler->filter('#divFooterSearch .td1')->first()->html();
    }

    return SearchResponseData::create()->setSummary($summary);

  }

}

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