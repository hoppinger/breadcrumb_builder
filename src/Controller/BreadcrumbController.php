<?php

namespace Drupal\breadcrumb_builder\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\breadcrumb_builder\BreadcrumbsManager;
use Drupal\breadcrumb_builder\BreadcrumbResult;
use Drupal\breadcrumb_builder\RouteMatchBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class BreadcrumbController extends ControllerBase {
  protected $breadcrumbManager;
  protected $pathProcessor;
  protected $router;
  protected $routeMatchBuilder;

  public function __construct(BreadcrumbsManager $breadcrumbManager, RouteMatchBuilder $routeMatchBuilder) {
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatchBuilder = $routeMatchBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('breadcrumb_builder.breadcrumb_manager'),
      $container->get('breadcrumb_builder.route_match_builder')
    );
  }

  public function build(Request $request) {
    $path = $request->query->get('path');

    $route_match = $this->routeMatchBuilder->getRouteMatchForPath($path);
    $result = $route_match ? $this->breadcrumbManager->build($route_match) : NULL;

    $response = new CacheableJsonResponse($result->getResult());
    $response->addCacheableDependency($result);

    return $response;
  }
}
