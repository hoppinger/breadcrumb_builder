<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\path_breadcrumb_builder\BreadcrumbResult;

/**
 * Provides a breadcrumb manager.
 *
 * Can be assigned any number of BreadcrumbBuilderInterface objects by calling
 * the addBuilder() method. When build() is called it iterates over the objects
 * in priority order and uses the first one that returns TRUE from
 * BreadcrumbBuilderInterface::applies() to build the breadcrumbs.
 *
 * @see \Drupal\Core\DependencyInjection\Compiler\RegisterBreadcrumbBuilderPass
 */
class BreadcrumbsManager implements ChainBreadcrumbBuilderInterface {
  /**
   * Holds arrays of breadcrumb builders, keyed by priority.
   *
   * @var array
   */
  protected $builders = [];

  /**
   * Holds the array of breadcrumb builders sorted by priority.
   *
   * Set to NULL if the array needs to be re-calculated.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface[]|null
   */
  protected $sortedBuilders;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }
  /**
   * {@inheritdoc}
   */
  public function addBuilder(BreadcrumbBuilderInterface $builder, $priority) {
    $this->builders[$priority][] = $builder;
    // Force the builders to be re-sorted.
    $this->sortedBuilders = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    foreach ($this->getSortedBuilders() as $builder) {
      if ($builder->applies($route_match)) {
        $breadcrumb = $builder->build($route_match);
        return $breadcrumb;
      }
    }

    return BreadcrumbResult::buildEmptyResult();
  }

  protected function getSortedBuilders() {
    if (!isset($this->sortedBuilders)) {
      // Sort the builders according to priority.
      krsort($this->builders);
      // Merge nested builders from $this->builders into $this->sortedBuilders.
      $this->sortedBuilders = [];
      foreach ($this->builders as $builders) {
        $this->sortedBuilders = array_merge($this->sortedBuilders, $builders);
      }
    }
    return $this->sortedBuilders;
  }

}
