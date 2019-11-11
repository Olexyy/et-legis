<?php

namespace Drupal\legal_position\DigestParser;

use Drupal\Component\Utility\Unicode;
use Drupal\legal_position\Decorator\LegalPosition;
use Drupal\taxonomy\Entity\Term;
use Gufy\PdfToHtml\Config;
use Gufy\PdfToHtml\Pdf;
use Smalot\PdfParser\Parser;

/**
 * Class DigestParserClient.
 *
 * @package Drupal\legal_position\DigestParser
 */
class DigestParserClient {

  /**
   * Lol.
   *
   * @throws \Exception
   */
  public static function parse() {
    // Parse pdf file and build necessary objects.
    $parser = new Parser();
    $url = 'https://supreme.court.gov.ua/userfiles/media/daidjest_6.pdf';
    $pdf = $parser->parseFile('file.pdf');
    $pages = $pdf->getPages();
    $previousText = '';
    $legalPositions = [];
    $digestNumber = '';
    foreach ($pages as $num => $page) {
      if (in_array($num, [0, 1])) {
        if(!$num) {
          $matches = [];
          $pageText = str_replace(' ', '', $page->getText());
          if (preg_match('~\d+/\d+~', $pageText, $matches)) {
            $digestNumber = current($matches);
          }
          else throw new \Exception('Cannot define digest number.');
        }
        continue;
      }
      $text = $page->getText();
      $text = str_replace('  ', ' ', $text);
      $regex = '~\d+[\R \t]*Рішення внесені до ЄДРСР за період.+Дайджест судової практики ВП ВС[\R \t]~sU';
      $text = preg_replace($regex, '', $text);
      if ($previousText) {
        $text = $previousText . $text;
        $previousText = '';
      }
      $regex = '~Детальніше із текстом постанови Верховного Суду.*(http.*reyestr\.court\.gov\.ua.*)\.~sU';
      $matches = [];
      if (preg_match($regex, $text, $matches)) {
        $split = $matches[0];
        $legalPositionCourtLink = $matches[1];
        $parts = explode($split, $text);
        if (count($parts) == 2) {
          $data = [];
          $data['unparsed'] = $parts[0];
          $data['decision_link'] = $legalPositionCourtLink;
          $data['page_num'] = $num;
          $legalPositions[] = $data;
          $previousText = $previousText . $parts[1];
        }
        else throw new \Exception('Parse error');
      }
      else {
        $previousText = $previousText . $text;
      }
    }
    foreach ($legalPositions as $key => $legalPosition) {
      $parts = preg_split('~\t\R \t\R~sU', $legalPosition['unparsed']);
      if (count($parts) > 1) {
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);
        $legalPositions[$key]['text'] = array_shift($parts);
        $legalPositions[$key]['resume'] = implode(PHP_EOL, $parts);
      }
    }
    if ($terms = LegalPosition::getTermStorage()->loadByProperties([
      'name' => 'Дайджест судової практики ВП ВС № ' . $digestNumber,
      'vid' => 'legal_position_source',
    ])) {
      $term = current($terms);
    }
    else {
      $term = Term::create([
        'name' => 'Дайджест судової практики ВП ВС № ' . $digestNumber,
        'vid' => 'legal_position_source',
      ]);
      $term->set('field_url', [
        'title' => 'Дайджест судової практики ВП ВС № ' . $digestNumber,
        'uri' => $url,
      ]);
      $term->save();
    };
    foreach ($legalPositions as $legalPosition) {
      if (!LegalPosition::loadByDecisionLink($legalPosition['decision_link'])) {
        $legalPositionEntity = LegalPosition::create();
        $legalPositionEntity->setBody(Unicode::convertToUtf8($legalPosition['text'], 'utf-8'));
        $legalPositionEntity->getEntity()
          ->set('field_extract', Unicode::convertToUtf8($legalPosition['resume'], 'utf-8'));
        $legalPositionEntity->getEntity()->set('field_source_reference', $term);
        $legalPositionEntity->getEntity()->setPublished(FALSE);
        $legalPositionEntity->getEntity()->setOwnerId(1);
        $legalPositionEntity->setDecisionLink([
          'title' => trim($legalPosition['decision_link']),
          'uri' => trim($legalPosition['decision_link']),
        ]);
        $legalPositionEntity->getEntity()->set('field_source_page', $legalPosition['page_num']);
        $legalPositionEntity->save();
      }
    }
  }

  public static function parseIt() {

    $bufferPath = $url = \Drupal::service('file_system')
      ->realpath('public://buffer');
    \Drupal::service('file_system')->deleteRecursive($bufferPath);
    Config::getInstance()->set('pdftohtml.output', $bufferPath);
    $pdfHtml = new Pdf('daidjest_6.pdf');
    $total_pages = $pdfHtml->getPages();
    $dom = $pdfHtml->getDom();
    $previousText = '';
    $legalPositions = [];
    for($i = 0; $i < $total_pages; $i++) {
      $pageNum = $i +1;
      // On landing, get digest number.
      if ($pageNum == 1) {
        $raw = $dom->raw($i + 1);
        if (preg_match('~\d+/\d+~', $raw, $matches)) {
          $digestNumber = current($matches);
        }
        else throw new \Exception('Cannot define digest number.');
      }
      else {
        // Skip second page.
        if ($pageNum > 2) {
          $raw = $dom->raw($i + 1);
          $elements = $dom->goToPage($i + 1)->find('p');
          $text = '';
          /** @var \PHPHtmlParser\Dom\Collection $elements */
          foreach ($elements->getIterator() as $elNum => $element) {
            if (in_array($elNum, [0, 1, 2, 3, 4, 5,])) {
              continue;
            }
            // Start each document with headings unset.
            /** @var \PHPHtmlParser\Dom\HtmlNode $element */
            $elText = $element->text();
            if (!trim($elText)) {
              $elText = PHP_EOL;
            }
            $text .= $elText;
          }
          if ($previousText) {
            $text = $previousText . $text;
            $previousText = '';
          }
          $matches = [];
          $regex = '~Детальніше із текстом постанови Верховного Суду.*(http.*reyestr\.court\.gov\.ua.*)\.~sU';
          if (preg_match($regex, $text, $matches)) {
            $split = $matches[0];
            $legalPositionCourtLink = $matches[1];
            $parts = explode($split, $text);
            if (count($parts) == 2) {
              $data = [];
              $data['unparsed'] = $parts[0];
              $data['decision_link'] = $legalPositionCourtLink;
              $data['page_num'] = $pageNum;
              $legalPositions[] = $data;
              $previousText = $previousText . $parts[1];
            }
            else throw new \Exception('Parse error');
          }
          else {
            $previousText = $previousText . $text;
          }
        }
      }
    }
    $a = 1;
    // Clean buffer.
    \Drupal::service('file_system')->deleteRecursive($bufferPath);
  }

}
