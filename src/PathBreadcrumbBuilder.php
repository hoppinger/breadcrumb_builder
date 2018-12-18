<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\path_breadcrumb_builder\BreadcrumbsManager;
use Drupal\path_breadcrumb_builder\BreadcrumbResult;
use Drupal\path_breadcrumb_builder\RouteMatchBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class PathBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  protected $breadcrumbManager;
  protected $pathProcessor;
  protected $router;
  protected $title_resolver;
  protected $pathValidator;
  protected $routeMatchBuilder;

  public function __construct(BreadcrumbsManager $breadcrumbManager, InboundPathProcessorInterface $pathProcessor, RouterInterface $router, TitleResolverInterface $title_resolver, PathValidator $pathValidator, RouteMatchBuilder $routeMatchBuilder) {
    $this->pathProcessor = $pathProcessor;
    $this->router = $router;
    $this->titleResolver = $title_resolver;
    $this->breadcrumbManager = $breadcrumbManager;
    $this->pathValidator = $pathValidator;
    $this->routeMatchBuilder = $routeMatchBuilder;
  }
  
   /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  } 

  public function build(RouteMatchInterface $route_match) {
    return $this->buildPathBreadcrumb($route_match);
  }

  protected function buildPathBreadcrumb($route_match) {
    $result = BreadcrumbResult::buildEmptyResult();

    $url = Url::fromRouteMatch($route_match);
    if ($url) {
      $generated_url = $url->toString(TRUE);
      $path = $generated_url->getGeneratedUrl();
      $result->addCacheableDependency($generated_url);
    }

    $path = trim($path, '/');
    $path_elements = explode('/', $path);
    $exclude = [];
    // Don't show a link to the front-page path.
    $exclude['/'] = TRUE;
    // /user is just a redirect, so skip it.
    // @todo Find a better way to deal with /user.
    $exclude['/user'] = TRUE;


    $prefix = array_slice($path_elements, 0, -1);
    if (!empty($prefix)) {
      $prefix_path = '/' . implode('/', $prefix);
      $valid_path = $this->pathValidator->isValid($prefix_path);
      if ($valid_path) {
        $prefix_route_request = $this->routeMatchBuilder->getRequestForPath($prefix_path, $exclude);
        if ($prefix_route_request) {
          $prefix_route_match = RouteMatch::createFromRequest($prefix_route_request);
          $prefix_result = $this->breadcrumbManager->build($prefix_route_match);
          $result->setResult($prefix_result->getResult());
          $result->addCacheableDependency($prefix_result);
        }
      }
    }

    $route_request = $this->routeMatchBuilder->getRequestForPath('/' . implode('/', $path_elements), $exclude);
    if ($route_request) {
      $route_match = RouteMatch::createFromRequest($route_request);
      
      $title = $this->titleResolver->getTitle($route_request, $route_match->getRouteObject());
      if (!isset($title)) {
        // Fallback to using the raw path component as the title if the
        // route is missing a _title or _title_callback attribute.
        $title = str_replace(['-', '_'], ' ', Unicode::ucfirst(end($path_elements)));
      }
      $url = Url::fromRouteMatch($route_match);
      
      $links = $result->getResult();
      $links[] = [
        'url' => $url->toString(TRUE)->getGeneratedUrl(),
        'title' => $title
      ];
      $result->setResult($links);
    }

    return $result;
  }
}