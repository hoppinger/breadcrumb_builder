<?php

namespace Drupal\breadcrumb_builder;

use Drupal\Core\Render\BubbleableMetadata;

class BreadcrumbResult extends BubbleableMetadata {
  protected $result = [];

  public function getResult() {
    return $this->result;
  }

  public function setResult($result) {
    $this->result = $result;
    return $this;
  }

  public function addResultItem($item) {
    $this->result[] = $item;
  }

  public function override(BreadcrumbResult $result) {
    $this->result = $result->getResult();
    $this->addCacheableDependency($result);
  }

  public static function buildEmptyResult($x = '') {
    $result = new BreadcrumbResult();
    $result->addCacheContexts(['url.query_args']);
    $result->addCacheTags(['node_list', 'menu_link_list']);
    $result->setResult([
      [
        'url' => '/',
        'title' => 'Home' . $x
      ]
    ]);

    return $result;
  }
}
