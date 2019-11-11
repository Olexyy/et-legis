<?php

namespace Drupal\legal_position\DigestParser;

use Drupal\legal_position\Decorator\LegalPosition;
use Drupal\taxonomy\TermInterface;
use Gufy\PdfToHtml\Config;
use Gufy\PdfToHtml\Html;
use Gufy\PdfToHtml\Pdf;
use PHPHtmlParser\Dom\HtmlNode;

/**
 * Class DigestParser.
 *
 * @package Drupal\mi_media_support
 */
class DigestParser {

  /**
   * Delimiter.
   *
   * @string delimiter
   */
  const DELIMITER = PHP_EOL . PHP_EOL . PHP_EOL;

  /**
   * Static factory.
   *
   * @return $this
   *   Instance.
   */
  public static function instance() {

    return new static();
  }

  /**
   * Parse handler.
   *
   * @param string $filePath
   *   File path.
   *
   * @return int
   *   Count of created items.
   *
   * @throws \Exception
   */
  public function doParse($filePath = 'dajdzhest_nomer7_1_05_10_05.pdf') {

    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $url = 'https://supreme.court.gov.ua/userfiles/media/daidjest_6.pdf';
    $fileSystem = \Drupal::service('file_system');
    $bufferPath = $fileSystem->realpath('public://digest_parser');
    $fileSystem->deleteRecursive($bufferPath);
    Config::getInstance()->set('pdftohtml.output', $bufferPath);
    // Set and clear buffer.
    $pdfHtml = new Pdf($filePath);
    $total_pages = (int) $pdfHtml->getPages();
    $dom = $pdfHtml->getDom();
    //$raws = [];
    $aggregatedText = '';
    $legalPositions = [];
    $digestNum = '';
    $pageNum = '';
    for ($i = 0; $i < $total_pages; $i++) {
      $currentPage = $i + 1;
      if (static::pageApplies($currentPage, $dom)) {
        $raw = $dom->raw($i + 1);
        //$raws[] = $raw;
        if ($num = $this->defineNumber($currentPage, $raw)) {
          $digestNum = $num;
          continue;
        }
        $text = $aggregatedText ? $aggregatedText : '';
        $pageNum = $pageNum ? $pageNum : $currentPage;
        $elements = $dom->goToPage($i + 1)->find('p');
        /** @var \PHPHtmlParser\Dom\Collection $elements */
        /** @var \PHPHtmlParser\Dom\HtmlNode $element */
        foreach ($elements->getIterator() as $key => $element) {
          if (static::elementApplies($key, $element)) {
            if (static::isEmpty($element)) {
              $text .= static::DELIMITER;
            }
            $text .= $element->text(TRUE);
          }
        }
        if ($split = $this->getSplit($text)) {
          $data = [];
          $data['decision_link'] = $split[1];
          $splitText = $this->doSplit($split[0], $text);
          $data['unparsed_text'] = $splitText[0];
          $data['page_num'] = $pageNum;
          $pageNum = $currentPage;
          $data['digest_num'] = $digestNum;
          $legalPositions[] = $data;
          $aggregatedText = $splitText[1];
        }
        else {
          $aggregatedText = $text;
        }
      }
    }
    if ($terms = LegalPosition::getTermStorage()->loadByProperties([
      'name' => 'Дайджест судової практики ВП ВС № ' . $digestNum,
      'vid' => 'legal_position_source',
    ])) {
      $term = current($terms);
    }
    else {
      $term = LegalPosition::getTermStorage()->create([
        'name' => 'Дайджест судової практики ВП ВС № ' . $digestNum,
        'vid' => 'legal_position_source',
      ]);
      /** @var \Drupal\taxonomy\TermInterface $term */
      $term->set('field_url', [
        'title' => 'Дайджест судової практики ВП ВС № ' . $digestNum,
        'uri' => $url,
      ]);
      $term->save();
    };
    $fileSystem->deleteRecursive($bufferPath);

    return $this->processLegalPositions($legalPositions, $term);
  }

  /**
   * Creates legal positions.
   *
   * @param array $legalPositions
   *   Legal positions data.
   * @param \Drupal\taxonomy\TermInterface $term
   *   Taxonomy term.
   *
   * @return int
   *   Count of created items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function processLegalPositions(array $legalPositions, TermInterface $term) {

    $count = 0;
    foreach ($legalPositions as $legalPosition) {
      if (!LegalPosition::loadByDecisionLink($legalPosition['decision_link'])) {
        $parsed = explode(static::DELIMITER, $legalPosition['unparsed_text']);
        $parsed = array_map('trim', $parsed);
        $parsed = array_filter($parsed);
        $legalPositionEntity = LegalPosition::create();
        $body = array_shift($parsed);
        $legalPositionEntity->setBody($body);
        if ($parsed) {
          $legalPositionEntity->getEntity()
            ->set('field_extract', implode(' ', $parsed));
        }
        $legalPositionEntity->getEntity()->set('field_source_reference', $term);
        $legalPositionEntity->getEntity()->setPublished(FALSE);
        $legalPositionEntity->getEntity()->setOwnerId(1);
        $legalPositionEntity->setDecisionLink([
          'title' => trim($legalPosition['decision_link']),
          'uri' => trim($legalPosition['decision_link']),
        ]);
        $legalPositionEntity->getEntity()->set('field_source_page', $legalPosition['page_num']);
        $legalPositionEntity->save();
        $count++;
      }
    }

    return $count;
  }

  /**
   * Predicate to define if page applies.
   *
   * @param int $num
   *   Number.
   * @param \Gufy\PdfToHtml\Html $dom
   *   Html dom.
   *
   * @return bool
   *   Result.
   */
  protected function pageApplies($num, Html $dom) {

    if ($num == 2) {
      if ($raw = $dom->raw($num)) {
        // If second page is contents, then skip it.
        if (mb_strpos($raw, 'Показчик термінів') !== FALSE) {

          return FALSE;
        }
        if (mb_strpos($raw, 'Покажчик термінів') !== FALSE) {

          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * Predicate to define if element applies.
   *
   * @param int $num
   *   Number.
   * @param \PHPHtmlParser\Dom\HtmlNode $element
   *   Element.
   *
   * @return bool
   *   Result.
   */
  protected function elementApplies($num, HtmlNode $element) {

    return !in_array($num, [0, 1, 2, 3, 4, 5]);
  }

  /**
   * @param \PHPHtmlParser\Dom\HtmlNode $element
   *
   * @return bool
   */
  protected function isEmpty(HtmlNode $element) {

    return empty(trim($element->text(TRUE)));
  }

  /**
   * @param $pageNum
   * @param $raw
   *
   * @return mixed|null
   * @throws \Exception
   */
  protected function defineNumber($pageNum, $raw) {

    if ($pageNum == 1) {
      $matches = [];
      $pattern = '~\d+/\d+~';
      if (preg_match($pattern, $raw, $matches)) {

        return current($matches);
      }
      else {
        throw new \Exception('Could not define digest number.');
      }
    }

    return NULL;
  }

  /**
   * @param $text
   *
   * @return array
   */
  protected function getSplit($text) {

    $matches = [];
    $pattern = '~Детальніше.*з.*текстом.*постанови.+ознайомитис.*(https?://[w\.]*reyestr\.court\.gov\.ua.+\d+)\.~Us';
    if (preg_match($pattern, $text, $matches)) {

      return $matches;
    }

    return [];
  }

  /**
   * @param $split
   * @param $text
   *
   * @return array
   * @throws \Exception
   */
  protected function doSplit($split, $text) {

    $parts = explode($split, $text);
    if (count($parts) != 2) {
      throw new \Exception('Parse error, unexpected split on single page.');
    }

    return $parts;
  }

}
