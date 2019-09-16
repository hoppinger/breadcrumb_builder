<?php

namespace Drupal\breadcrumb_builder;

interface ChainBreadcrumbBuilderInterface extends BreadcrumbBuilderInterface {
  /**
   * @param \Drupal\breadcrumb_builder\BreadcrumbBuilderInterface $builder
   * @param int $priority
   */
  public function addBuilder(BreadcrumbBuilderInterface $builder, $priority);
}