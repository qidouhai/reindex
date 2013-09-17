<?php

//! @file InitCommand.php
//! @brief This file contains the InitCommand class.
//! @details
//! @author Filippo F. Fadda


namespace PitPress\Console\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use ElephantOnCouch\Doc\DesignDoc;
use ElephantOnCouch\Handler\ViewHandler;


//! @brief Initializes the PitPress database, adding the required design documents.
//! @nosubgrouping
class InitCommand extends AbstractCommand {

  protected $mysql;
  protected $couch;


  //! @brief Insert all design documents.
  private function initAll() {
    $this->initPosts();
    $this->initTags();
    $this->initVotes();
    $this->initScores();
    $this->initStars();
    $this->initSubscriptions();
    $this->initClassifications();
    $this->initReputation();
    $this->initBadges();
    $this->initFavorites();
    $this->initUsers();
  }


  private function initPosts() {
    $doc = DesignDoc::create('posts');


    // @params: NONE
    function allPosts() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'post')
                  \$emit(\$doc->_id, [
                     'title' => \$doc->title,
                     'excerpt' => \$doc->excerpt,
                     'slug' => \$doc->slug,
                     'section' => \$doc->section,
                     'publishingType' => \$doc->publishingType,
                     'publishingDate' => \$doc->publishingDate,
                     'userId' => \$doc->userId,
                     'username' => \$doc->username
                   ]);
              };";

      $handler = new ViewHandler("all");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount(); // Used to count the posts.

      return $handler;
    }

    $doc->addHandler(allPosts());


    // @params: section, year, month, day, slug
    function postsByUrl() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'post')
                  \$emit([\$doc->section, \$doc->year, \$doc->month, \$doc->day, \$doc->slug]);
              };";

      $handler = new ViewHandler("byUrl");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(postsByUrl());


    // @params: type
    function latestPostsPerType() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'post')
                  \$emit([\$doc->type, \$doc->publishingDate]);
              };";

      $handler = new ViewHandler("latestPerType");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount(); // Used to count the posts.

      return $handler;
    }

    $doc->addHandler(latestPostsPerType());


    // @params: section
    function latestPostsPerSection() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->section))
                  \$emit([\$doc->section, \$doc->publishingDate]);
              };";

      $handler = new ViewHandler("latestPerSection");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount(); // Used to count the posts.

      return $handler;
    }

    $doc->addHandler(latestPostsPerSection());


    // @params: NONE
    function latestPosts() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'post')
                  \$emit(\$doc->publishingDate);
              };";

      $handler = new ViewHandler("latest");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount(); // Used to count the posts.

      return $handler;
    }

    $doc->addHandler(latestPosts());


    // @params: NONE
    function postsPerDate() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'post')
                  \$emit([\$doc->year, \$doc->month, \$doc->day]);
              };";

      $handler = new ViewHandler("perDate");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount(); // Used to count the posts.

      return $handler;
    }

    $doc->addHandler(postsPerDate());


    $this->couch->saveDoc($doc);
  }


  private function initTags() {
    $doc = DesignDoc::create('tags');


    // @params NONE
    function allTags() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'tag')
                  \$emit(\$doc->_id, \$doc->name);
              };";

      $handler = new ViewHandler("all");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(allTags());


    $this->couch->saveDoc($doc);
  }


  private function initVotes() {
    $doc = DesignDoc::create('votes');


    // @params: [postId]
    function votesPerPost() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'vote')
                  \$emit(\$doc->postId, \$doc->value);
              };";

      $handler = new ViewHandler("perPost");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnSum(); // Used to count the votes.

      return $handler;
    }

    $doc->addHandler(votesPerPost());


    // @params: postId, userId
    function votesPerPostAndUser() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'vote')
                  \$emit([\$doc->postId, \$doc->userId], \$doc->value);
              };";

      $handler = new ViewHandler("perPostAndUser");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnSum(); // Used to count the votes.

      return $handler;
    }

    $doc->addHandler(votesPerPostAndUser());


    // @params: type, postId
    function votesPerType() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'vote')
                  \$emit([\$doc->postType, \$doc->postId], \$doc->value);
              };";

      $handler = new ViewHandler("perType");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnSum(); // Used to count the votes.

      return $handler;
    }

    $doc->addHandler(votesPerType());


    // @params: section, postId
    function votesPerSection() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'vote')
                  \$emit([\$doc->section, \$doc->postId], \$doc->value);
              };";

      $handler = new ViewHandler("perSection");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnSum(); // Used to count the votes.

      return $handler;
    }

    $doc->addHandler(votesPerSection());


    // @params: timestamp
    function votesNotRecorded() {
      $map = "function(\$doc) use (\$emit) {
                if ((\$doc->type == 'vote') && (!\$doc->recorded))
                  \$emit(\$doc->timestamp, \$doc);
              };";

      $handler = new ViewHandler("notRecorded");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(votesNotRecorded());


    $this->couch->saveDoc($doc);
  }


  private function initScores() {
    $doc = DesignDoc::create('scores');


    // @params postId
    function scoresPerPost() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'score')
                  \$emit(\$doc->postId, \$doc);
              };";

      $handler = new ViewHandler("perPost");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(scoresPerPost());


    $this->couch->saveDoc($doc);
  }


  private function initStars() {
    $doc = DesignDoc::create('stars');


    // @params postId, [userId]
    // @methods: VersionedItem.isStarred(), VersionedItem.getStarsCount()
    function starsPerItem() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'star')
                  \$emit([\$doc->itemId, \$doc->userId]);
              };";

      $handler = new ViewHandler("perItem");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(starsPerItem());


    $this->couch->saveDoc($doc);
  }


  private function initSubscriptions() {
    $doc = DesignDoc::create('subscriptions');


    // @params itemId, [userId]
    // @methods: VersionedItem.isStarred(), VersionedItem.getSubscribersCount()
    function subscriptionsPerItem() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'subscription')
                  \$emit([\$doc->itemId, \$doc->userId]);
              };";

      $handler = new ViewHandler("perItem");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(subscriptionsPerItem());


    $this->couch->saveDoc($doc);
  }


  private function initClassifications() {
    $doc = DesignDoc::create('classifications');


    // @params postId
    // @methods: Post.getTags()
    function classificationsPerPost() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'classification')
                  \$emit(\$doc->postId, \$doc->tagId);
              };";

      $handler = new ViewHandler("perPost");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(classificationsPerPost());


    // @params NONE
    function latestClassifications() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'classification')
                  \$emit(\$doc->timestamp, \$doc->tagId);
              };";

      $handler = new ViewHandler("latest");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(latestClassifications());


    // @params tagId
    function classificationsPerTag() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'classification')
                  \$emit(\$doc->tagId);
              };";

      $handler = new ViewHandler("perTag");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(classificationsPerTag());


    $this->couch->saveDoc($doc);
  }


  private function initBadges() {
  }


  private function initReputation() {
    $doc = DesignDoc::create('reputation');


    // @params userId, [timestamp]
    // @methods: User.getReputation()
    function reputationPerUser() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'reputation')
                  \$emit([\$doc->userId, \$doc->timestamp], \$doc->points);
              };";

      $handler = new ViewHandler("perUser");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnSum();

      return $handler;
    }

    $doc->addHandler(reputationPerUser());


    $this->couch->saveDoc($doc);
  }


  private function initFavorites() {
    $doc = DesignDoc::create('favorites');


    // @params userId
    // @methods: todo
    function lastAddedFavorites() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'star')
                  \$emit([\$doc->userId, \$doc->timestamp], \$doc->postId);
              };";

      $handler = new ViewHandler("lastAdded");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(lastAddedFavorites());


    // @params userId, type
    // @methods: todo
    function lastAddedFavoritesPerType() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'star')
                  \$emit([\$doc->userId, \$doc->postType, \$doc->timestamp], \$doc->postId);
              };";

      $handler = new ViewHandler("lastAddedPerType");
      $handler->mapFn = $map;

      return $handler;
    }

    $doc->addHandler(lastAddedFavoritesPerType());


    $this->couch->saveDoc($doc);
  }


  private function initUsers() {
    $doc = DesignDoc::create('users');


    // @params: [userId]
    function allUserNames() {
      $map = "function(\$doc) use (\$emit) {
                if (\$doc->type == 'user')
                  \$emit(\$doc->_id, [\$doc->displayName, \$doc->email]);
              };";

      $handler = new ViewHandler("allNames");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(allUserNames());


    $this->couch->saveDoc($doc);
  }


  private function initReplays() {
    $doc = DesignDoc::create('replays');


    // @params: postId
    function latestReplaysPerPost() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'replay')
                  \$emit([\$doc->postId, \$doc->publishingDate]);
              };";

      $handler = new ViewHandler("latestPerPost");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(latestReplaysPerPost());


    // @params: postId
    function lastUpdatedReplaysPerPost() {
      $map = "function(\$doc) use (\$emit) {
                if (isset(\$doc->supertype) and \$doc->supertype == 'replay')
                  \$emit([\$doc->postId, \$doc->lastUpdate]);
              };";

      $handler = new ViewHandler("lastUpdatedPerPost");
      $handler->mapFn = $map;
      $handler->useBuiltInReduceFnCount();

      return $handler;
    }

    $doc->addHandler(lastUpdatedReplaysPerPost());


    $this->couch->saveDoc($doc);
  }


  //! @brief Configures the command.
  protected function configure() {
    $this->setName("init");
    $this->setDescription("Initializes the PitPress database, adding the required design documents.");
    $this->addArgument("documents",
      InputArgument::IS_ARRAY | InputArgument::REQUIRED,
      "The documents containing the views you want create. Use 'all' if you want insert all the documents, 'users' if
      you want just init the users or separate multiple documents with a space. The available documents are: users,
      articles, books, tags, reputation.");
  }


  //! @brief Executes the command.
  protected function execute(InputInterface $input, OutputInterface $output) {

    $this->mysql = $this->_di['mysql'];
    $this->couch = $this->_di['couchdb'];

    $documents = $input->getArgument('documents');

    // Checks if the argument 'all' is provided.
    $index = array_search("all", $documents);

    if ($index === FALSE) {

      foreach ($documents as $name)
        switch ($name) {
          case 'posts':
            $this->initPosts();
            break;

          case 'tags':
            $this->initTags();
            break;

          case 'votes':
            $this->initVotes();
            break;

          case 'scores':
            $this->initScores();
            break;

          case 'stars':
            $this->initStars();
            break;

          case 'subscriptions':
            $this->initSubscriptions();
            break;

          case 'classifications':
            $this->initClassifications();
            break;

          case 'badges':
            $this->initBadges();
            break;

          case 'favourites':
            $this->initFavorites();
            break;

          case 'users':
            $this->initUsers();
            break;

          case 'reputation':
            $this->initReputation();
            break;

          case 'replays':
            $this->initReplays();
            break;
        }

    }
    else
      $this->initAll();
  }

}