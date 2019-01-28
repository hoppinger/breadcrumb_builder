<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\path_breadcrumb_builder\BreadcrumbResult;


class MenuBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  protected $menuLinkManager;

  public function __construct(MenuLinkManagerInterface $menuLinkManager) {
    $this->menuLinkManager = $menuLinkManager;
  }
  
   /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $menuLinks = $this->menuLinkManager->loadLinksByRoute($route_match->getRouteName(), $route_match->getRawParameters()->all());
    return !empty($menuLinks);
  } 

  public function build(RouteMatchInterface $route_match) {
    //Menu links breadcrumb
   
    $menuLinks = $this->menuLinkManager->loadLinksByRoute($route_match->getRouteName(), $route_match->getRawParameters()->all());
    if (!empty($menuLinks)) {
      $menuLink = end($menuLinks);
      return $this->buildForMenuLink($menuLink);
    }
  }
  
  protected function buildForMenuLink(MenuLinkInterface $menuLink) {

    $result = BreadcrumbResult::buildEmptyResult();
    $result->addCacheableDependency($menuLink);
    $result_data = $result->getResult();
    $menuParents = $this->getMenuLinkParents($menuLink);
    foreach ($menuParents as $menuParent) {
      $result->addCacheableDependency($menuParent);

      $url = $menuParent->getUrlObject()->toString(TRUE);
      $result->addCacheableDependency($url);

      $result_data[] = [
        'url' => $url->getGeneratedUrl(),
        'title' => $menuParent->getTitle(),
      ];
    }

    $url = $menuLink->getUrlObject()->toString(TRUE);
    $result->addCacheableDependency($url);
    $result_data[] = [
      'url' => $url->getGeneratedUrl(),
      'title' => $menuLink->getTitle(),
    ];

    $result->setResult($result_data);
    return $result;
  }

  protected function getMenuLinkParents(MenuLinkInterface $link) {
    $result = [];

    $current = $link;

    while ($parent_id = $current->getParent()) {
      $new = $this->menuLinkManager->createInstance($parent_id);
      if ($new) {
        array_unshift($result, $new);
        $current = $new;
      } else {
        break;
      }
    }

    return $result;
  }
}
