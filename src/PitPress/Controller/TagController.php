<?php

/**
 * @file TagController.php
 * @brief This file contains the TagController class.
 * @details
 * @author Filippo F. Fadda
 */


namespace PitPress\Controller;


use ElephantOnCouch\Opt\ViewQueryOpts;
use ElephantOnCouch\Couch;

use PitPress\Helper;


/**
 * @brief Controller of Tag actions.
 * @nosubgrouping
 */
class TagController extends ListController {


  protected function getEntries($ids) {
    if (empty($ids))
      return [];

    $opts = new ViewQueryOpts();

    // Gets the tags properties.
    $opts->doNotReduce();
    $tags = $this->couch->queryView("tags", "all", $ids, $opts);

    Helper\ArrayHelper::unversion($ids);

    // Retrieves the number of posts per tag.
    $opts->reset();
    $opts->groupResults()->includeMissingKeys();
    $postsCount = $this->couch->queryView("posts", "perTag", $ids, $opts);

    $entries = [];
    $tagsCount = count($tags);
    for ($i = 0; $i < $tagsCount; $i++) {
      $entry = new \stdClass();
      $entry->id = $tags[$i]['id'];
      $entry->name = $tags[$i]['value'][0];
      $entry->excerpt = $tags[$i]['value'][1];
      $entry->createdAt = $tags[$i]['value'][2];
      //$entry->whenHasBeenPublished = Helper\Time::when($tags[$i]['value'][2]);
      $entry->postsCount = is_null($postsCount[$i]['value']) ? 0 : $postsCount[$i]['value'];

      $entries[] = $entry;
    }

    return $entries;
  }


  public function initialize() {
    parent::initialize();
    $this->resultsPerPage = 40;
    $this->view->pick('views/tag');
  }


  public function afterExecuteRoute() {
    parent::afterExecuteRoute();
  }


  /**
   * @brief Displays the most popular tags.
   */
  public function popularAction() {
    $this->view->setVar('title', 'Tags popolari');
  }


  /**
   * @brief Displays the last updated tags.
   */
  public function activeAction() {
    $set = "tmp_tags".'_'.'post';

    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $keys = $this->redis->zRevRangeByScore($set, '+inf', 0, ['limit' => [$offset, $this->resultsPerPage-1]]);
    $count = $this->redis->zCount($set, 0, '+inf');

    if ($count > $this->resultsPerPage)
      $this->view->setVar('nextPage', $this->buildPaginationUrlForRedis($offset + $this->resultsPerPage));

    if (!empty($keys)) {
      $opts = new ViewQueryOpts();
      $opts->doNotReduce();
      $rows = $this->couch->queryView("tags", "allNames", $keys, $opts);
      $ids = $this->getEntries(array_column($rows->asArray(), 'id'));
    }
    else
      $ids = [];

    $this->view->setVar('entries', $ids);
    $this->view->setVar('title', 'Tags attivi');
  }


  /**
   * @brief Displays the tags sorted by name.
   */
  public function byNameAction() {
    $opts = new ViewQueryOpts();
    $opts->doNotReduce()->setLimit($this->resultsPerPage+1);

    // Paginates results.
    $startKey = isset($_GET['startkey']) ? $_GET['startkey'] : chr(0);
    if (isset($_GET['startkey_docid'])) $opts->setStartDocId($_GET['startkey_docid']);

    $opts->setStartKey($startKey);

    $tags = $this->couch->queryView("tags", "byName", NULL, $opts)->asArray();

    $entries = $this->getEntries(array_column($tags, 'id'));

    if (count($entries) > $this->resultsPerPage) {
      $last = array_pop($entries);
      $this->view->setVar('nextPage', $this->buildPaginationUrlForCouch($last->name, $last->id));
    }

    $this->view->setVar('entries', $entries);
    $this->view->setVar('title', 'Tags per nome');
  }


  /**
   * @brief Displays the newest tags.
   */
  public function newestAction() {
    $opts = new ViewQueryOpts();
    $opts->doNotReduce()->reverseOrderOfResults()->setLimit($this->resultsPerPage+1);

    // Paginates results.
    $startKey = isset($_GET['startkey']) ? (int)$_GET['startkey'] : Couch::WildCard();
    if (isset($_GET['startkey_docid'])) $opts->setStartDocId($_GET['startkey_docid']);

    $opts->setStartKey($startKey);

    $tags = $this->couch->queryView("tags", "newest", NULL, $opts)->asArray();

    $entries = $this->getEntries(array_column($tags, 'id'));

    if (count($entries) > $this->resultsPerPage) {
      $last = array_pop($entries);
      $this->view->setVar('nextPage', $this->buildPaginationUrlForCouch($last->createdAt, $last->id));
    }

    $this->view->setVar('entries', $entries);
    $this->view->setVar('title', 'Nuovi tags');
  }


  /**
   * @brief Displays the synonyms.
   * @todo I still don't know how to make this one.
   */
  public function synonymsAction() {
    $this->view->setVar('title', 'Sinonimi');
  }


}