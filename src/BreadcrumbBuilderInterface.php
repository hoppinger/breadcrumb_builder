<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Routing\RouteMatchInterface;

interface BreadcrumbBuilderInterface {
  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return bool
   */
  public function applies(RouteMatchInterface $route_match);

  /**
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @return \Drupal\path_breadcrumb_builder\BreadcrumbResult
   */
  public function build(RouteMatchInterface $route_match);
}