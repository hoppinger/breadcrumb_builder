<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

class PathBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  protected $breadcrumbManager;
  protected $titleResolver;
  protected $pathValidator;
  protected $routeMatchBuilder;

  public function __construct(BreadcrumbsManager $breadcrumbManager, TitleResolverInterface $titleResolver, PathValidator $pathValidator, RouteMatchBuilder $routeMatchBuilder) {
    $this->titleResolver = $titleResolver;
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

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
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
        $prefix_route_match = $this->routeMatchBuilder->getRouteMatchForPath($prefix_path, $exclude);
        if ($prefix_route_match) {
          $prefix_result = $this->breadcrumbManager->build($prefix_route_match);
          $result->override($prefix_result);
        }
      }
    }

    $route_request = $this->routeMatchBuilder->getRequestForPath('/' . implode('/', $path_elements), $exclude);
    if ($route_request) {
      $route_match = RouteMatch::createFromRequest($route_request);

      $title = $this->getTitle($route_request, $route_match, end($path_elements));
      
      $url = Url::fromRouteMatch($route_match)->toString(TRUE);
      $result->addCacheableDependency($url);
      
      $result->addResultItem([
        'url' => $url->getGeneratedUrl(),
        'title' => $title,
      ]);
    }

    return $result;
  }

  protected function getTitle($request, $route_match, $path_element) {
    $title = $this->titleResolver->getTitle($request, $route_match->getRouteObject());
    if (!isset($title)) {
      // Fallback to using the raw path component as the title if the
      // route is missing a _title or _title_callback attribute.
      $title = str_replace(['-', '_'], ' ', Unicode::ucfirst($path_element));
    }

    return $title;
  }
}