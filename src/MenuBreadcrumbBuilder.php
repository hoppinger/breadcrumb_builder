<?php

namespace Drupal\path_breadcrumb_builder;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;


class MenuBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  protected $menuLinkManager;
   /**
   * @var string[]
   */
  protected $target_identifiers;

  public function __construct(MenuLinkManagerInterface $menuLinkManager, $target_identifiers) {
    $this->menuLinkManager = $menuLinkManager;
    $this->target_identifiers = $target_identifiers;
  }
  
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return !empty($this->getMenuLinkForRouteMatch($route_match));
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $menuLink = $this->getMenuLinkForRouteMatch($route_match);
    return $menuLink ? $this->buildFromMenuLink($menuLink) : BreadcrumbResult::buildEmptyResult();
  }

  /**
   * @param RouteMatchInterface $route_match
   * @return MenuLinkInterface|null
   */
  protected function getMenuLinkForRouteMatch(RouteMatchInterface $route_match) {
    // Find the correct menu link: in a valid menu, with the lowest depth

    foreach($this->target_identifiers as $identifier) {
      $menuLinks = $this->menuLinkManager->loadLinksByRoute($route_match->getRouteName(), $route_match->getRawParameters()->all(), $identifier);
      if (!empty($menuLinks)) {
        break 1;
      }
    }
    
    return !empty($menuLinks) ? end($menuLinks) : null;
  }
  
  protected function buildFromMenuLink(MenuLinkInterface $menuLink) {
    $result = BreadcrumbResult::buildEmptyResult();

    $menuParents = $this->getMenuLinkParents($menuLink);
    foreach ($menuParents as $menuParent) {
      $this->addMenuLinkToResult($menuParent, $result);
    }

    $this->addMenuLinkToResult($menuLink, $result);

    return $result;
  }

  protected function addMenuLinkToResult(MenuLinkInterface $menuLink, BreadcrumbResult $result) {
    $result->addCacheableDependency($menuLink);

    $url = $menuLink->getUrlObject()->toString(TRUE);
    $result->addCacheableDependency($url);

    $result->addResultItem([
      'url' => $url->getGeneratedUrl(),
      'title' => $menuLink->getTitle(),
    ]);
  }

  protected function getMenuLinkParents(MenuLinkInterface $link) {
    $result = [];

    $current = $link;

    while ($parent_id = $current->getParent()) {
      $parent = $this->menuLinkManager->createInstance($parent_id);
      if ($parent) {
        array_unshift($result, $parent);
        $current = $parent;
      } else {
        break;
      }
    }

    return $result;
  }
}
