<?php

namespace Drupal\path_breadcrumb_builder\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\path_breadcrumb_builder\BreadcrumbsManager;
use Drupal\path_breadcrumb_builder\BreadcrumbResult;
use Drupal\path_breadcrumb_builder\RouteMatchBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class BreadcrumbController extends ControllerBase {
  protected $breadcrumbManager;
  protected $pathProcessor;
  protected $router;
  protected $routeMatchBuilder;

  public function __construct(BreadcrumbsManager $breadcrumbManager, InboundPathProcessorInterface $pathProcessor, RouterInterface $router, RouteMatchBuilder $routeMatchBuilder) {
    $this->breadcrumbManager = $breadcrumbManager;
    $this->pathProcessor = $pathProcessor;
    $this->router = $router;
    $this->routeMatchBuilder = $routeMatchBuilder;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path_breadcrumb_builder.breadcrumb_manager'),
      $container->get('path_processor_manager'),
      $container->get('router'),
      $container->get('path_breadcrumb_builder.routematch_builder')
    );
  }
  
  public function build(Request $request) {
    $path = $request->query->get('path');
  
    $route_match = $this->getRouteMatchForPath($path);
    $result = $this->breadcrumbManager->build($route_match);

    if (!$result) {
      $result = BreadcrumbResult::buildEmptyResult();
    }
    
    $response = CacheableJsonResponse::create($result->getResult());
    $response->addCacheableDependency($result);  

    return $response;
  }

  protected function getRouteMatchForPath($path) {
    $request = $this->routeMatchBuilder->getRequestForPath($path, array());
    return RouteMatch::createFromRequest($request);
  }  
}
