# Breadcrumb Builder

Navigation can greatly enhance the way users find their way around. In terms of usability, breadcrumbs reduce the number of actions a website visitor needs to take in order to get to a higher-level page, and they improve the findability of website sections and pages. They are also an effective visual aid that indicates the location of the user within the website’s hierarchy, making it a great source of contextual information for landing pages.

## Installation

```sh
composer require hoppinger/breadcrumb_builder
```

## Usage
Path Breadcrumb builder automatically recognizes the correct breadcrumb you want based on the URLs of the pages and the items included in the menu. In case of special breadcrumb trail requirement, you can extend the functionality of the Path breadcrumb builder and create your own breadcrumb trail.

Create a custom service and define the service in the modulename.services.yml file. Replace the ExampleBreadcrumbBuilder with your own filename. Add the priority to the custom breadcrumb builder.
```yml
  class: Drupal\module_name\ExampleBreadcrumbBuilder
    arguments: ['@plugin.manager.menu.link']
    tags:
      - { name: custom_breadcrumb_builder, priority: x }
```

Create a file ExampleBreadcrumbBuilder.php inside the <module_name>/src folder and extend the Breadcrumb manager.
```php

namespace Drupal\<module_name>;

use Drupal\breadcrumb_builder\BreadcrumbBuilderInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\breadcrumb_builder\BreadcrumbResult;


class ExampleBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  //Variables

  public function __construct() {
    //Intitialize
  }
  
   /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  } 

  public function build(RouteMatchInterface $route_match) {
    //Custom logic
  }
}
```

In some cases we need more menu types than the specified 'main' menu, to contribute to the breadcrumb generation.

Create a .php file in the module directory <directory>/src with the following content. Replace the `<target_identifier>` with the project-specific menu type that needs to be embedded into the API response (for example: `footer`)

The <target_identifiers> can be Menu types. 

The filename must be similar to the module name i.e., ModuleNameServiceProvider.php and must be placed in the src folder.

```php
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

class AlterServiceProvider extends ServiceProviderBase {
  public function alter(ContainerBuilder $container) {
    if ($container->hasParameter('breadcrumb_builder.target_identifiers')) {
      $target_identifiers = $container->getParameter('breadcrumb_builder.target_identifiers');
      
      $target_identifiers[] = <target_identifier>; #e.g., footer
  
      $container->setParameter('breadcrumb_builder.target_identifiers', $target_identifiers);
  }
}
```

By default, main menu is already added as the target identifier.
