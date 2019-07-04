<?php

namespace Drupal\legal_position\ProcessParser;

use Aspera\Spreadsheet\XLSX\Reader;
use Drupal\legal_position\Decorator\LegalPosition;

/**
 * Class Client.
 *
 * @package Drupal\legal_position\ProcessParser
 */
class ProcessParserClient {

  /**
   * Processed item.
   *
   * @var \Drupal\legal_position\ProcessParser\ProcessParserItem
   */
  protected $item;

  /**
   * Counter.
   *
   * @var int
   */
  protected $counter;

  /**
   * Limit.
   *
   * @var int
   */
  protected $limit;

  /**
   * Url.
   *
   * @var string
   */
  protected $url;

  /**
   * Type.
   *
   * @var string
   */
  protected $type;

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
   * Setter.
   *
   * @param string $type
   *   Url or path.
   *
   * @return $this
   *   Chaining.
   */
  public function setType($type) {

    $this->type = $type;

    return $this;
  }

  /**
   * Setter.
   *
   * @param string $url
   *   Url or path.
   *
   * @return $this
   *   Chaining.
   */
  public function setUrl($url) {

    $this->url = $url;

    return $this;
  }

  /**
   * Setter.
   *
   * @param int $limit
   *   Limit created items.
   *
   * @return $this
   *   Chaining.
   */
  public function setLimit($limit) {

    $this->limit = $limit;

    return $this;
  }

  /**
   * Executes process func.
   *
   * @return int
   *   Count of created items.
   *
   * @throws \Exception
   */
  public function processXml() {

    $this->counter = 0;
    $reader = new Reader();
    $reader->open($this->url);
    foreach ($reader as $row) {
      $this->processRow($row);
      if ($this->limit && $this->counter == $this->limit) {
        break;
      }
    }

    return $this->counter;
  }

  /**
   * Explodes string.
   *
   * @param string $data
   *   Data.
   *
   * @return array|string[]
   *   Exploded string.
   */
  protected function explode($data) {

    $data = preg_split('~\R~', $data);

    return array_map('trim', $data);
  }

  /**
   * Internal helper.
   *
   * @param bool $refresh
   *   Flag.
   */
  protected function ensureItem($refresh = FALSE) {

    if ($refresh) {
      $this->item = NULL;
    }
    if (!$this->item) {
      $this->item = new ProcessParserItem();
    }
  }

  /**
   * Internal helper.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function finalizeItem() {

    if ($this->item) {
      // If type is 'ВВ'.
      if ($this->item->hasType($this->type)) {
        // If there is final link.
        if ($this->item->getLinkCount() == 2) {
          // If no legal position with court link exists.
          if (!LegalPosition::loadByCourtLink($this->item->getLastLink())) {
            $legalPosition = LegalPosition::create();
            $legalPosition->getEntity()->setOwnerId(1);
            $legalPosition->getEntity()->setPublished(FALSE);
            $legalPosition->setBody($this->item->getText());
            $legalPosition->setDecisionLink([
              'title' => $this->item->getLastLink(),
              'uri' => $this->item->getLastLink(),
            ]);
            $legalPosition->save();
            $this->counter++;
          }
        }
      }
      $this->ensureItem(TRUE);
    }
  }

  /**
   * Row processor.
   *
   * @param array|string[] $row
   *   Row to process.
   *
   * @throws \Exception
   */
  protected function processRow(array $row) {

    $initialRow = FALSE;
    // Ensure format.
    if (count($row) !== 11) {
      throw new \Exception("Unexpected format");
    }
    // Skip heading.
    if (!empty($row[0]) && !is_numeric($row[0])) {
      return;
    }
    // Skip empty spaces.
    if (empty(array_filter($row))) {
      return;
    }
    $this->ensureItem();
    // If initial row, finalize item if exist.
    if (!empty($row[0])) {
      $this->finalizeItem();
      $this->item->setNumber($row[0]);
      $initialRow = TRUE;
    }
    // Types only on initial row.
    if (!empty($row[2]) && $initialRow) {
      $this->item->setTypes($this->explode($row[2]));
    }
    // Text on initial, links on any.
    if (!empty($row[3])) {
      if ($initialRow) {
        $this->item->setText($row[3]);
      }
      elseif (strpos($row[3], 'reyestr.court.gov.ua') !== FALSE) {
        $this->item->addLink($row[3]);
      }
    }
    // Set info only on initial.
    if (!empty($row[4]) && $initialRow) {
      $this->item->setInfo($this->explode($row[4]));
    }
  }

}
