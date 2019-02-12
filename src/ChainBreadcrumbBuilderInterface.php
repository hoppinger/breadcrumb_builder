<?php

namespace Drupal\path_breadcrumb_builder;

interface ChainBreadcrumbBuilderInterface extends BreadcrumbBuilderInterface {
  /**
   * @param \Drupal\path_breadcrumb_builder\BreadcrumbBuilderInterface $builder
   * @param int $priority
   */
  public function addBuilder(BreadcrumbBuilderInterface $builder, $priority);
}