## Usages

Install dependency into your project.
```
composer require wp-strap/view
```

This exposes some classes which are responsible loading template files inside plugins (but can also be used for themes).

The classes follow PSR practices with interfaces, so it can be included trough OOP with dependency injection and IoC containers. It also provides a Facade class that allows you to use static methods instead to call the methods everywhere you like.



Example with using the facade:
```php
use WPStrap\View\Views;

// Resolves instance and registers project configurations
Views::register([
    'dir' => plugin_dir_path(__FILE__), // or get_stylesheet_directory() for themes
]);

echo Views::render('my-component')->args([
    'my-argument' => 'my-value'
])
```

Example with using the instance
```php
use WPStrap\View\Views;
use WPStrap\View\ViewService;

// Instantiates the Asset service and registers project configurations
$views = new ViewService();

$views->register([
    'dir' => plugin_dir_path(__FILE__), // or get_stylesheet_directory() for themes
]);

// Renders template with arguments
echo $views->render('my-component')->args([
    'my-argument' => 'my-value'
])

// You can also use the facade based on this instance.
Views::setFacade($views);
Views::render('my-second-component');
```

Example with using instance as function
```php
use WPStrap\View\ViewsInterface;
use WPStrap\View\ViewService;

function views(): ViewsInterface {
     static $views;
     
     if(!isset($views)) {
        $views = (new ViewService())->register([
            'dir' => plugin_dir_path(__FILE__), 
        ]);
     }
     
     return $views;
}

echo views()->render('my-component')->args([
    'my-argument' => 'my-value'
])
```

Example with using the League Container
```php
use League\Container\Container;
use WPStrap\View\Assets;
use WPStrap\View\ViewsInterface;
use WPStrap\View\ViewService;

$container = new Container();
$container->add(ViewsInterface::class)->setConcrete(ViewService::class)->addMethodCall('register', [
    'dir' => plugin_dir_path(__FILE__), 
]);

$views = $container->get(ViewsInterface::class);

echo $views->render('my-component')->args([
    'my-argument' => 'my-value'
])

// You can also set a PSR container as facade accessor
Views::setFacadeAccessor($container);
Views::render('my-second-component');
```

### Base settings

It locates the template files from the "views" folder by default (eg: `your-plugin-folder/views` ).

```php
$views->register([
    // Determines the root dir of your project
    'dir' => plugin_dir_path(__FILE__), 
    
    // Will change templates path to "your-plugin-folder/path-to-views/views"
    'path' => 'path-to-views', 
    
    // Changes "your-plugin-folder/views" to "your-plugin-folder/templates"
    'folder' => 'templates', 
    
    // Will override templates from theme/child-themes if templates exist in the
    // "clients-theme/my-plugin-name" directory, this is turned off by default
    // to remove the performance load since it won't be needed for most plugins
    'locate' => 'my-plugin-name', 
    
    // By default it uses the plugin folder name as hook prefix for filters inside the
    // classes (eg: a filter for "my-plugin-folder" becomes "my_plugin_folder_view_args")
    // With this setting you can change the prefix
    'hook' => 'my_plugin_hook', 
]);
```

### Domain folder
If you have a domain folder structure like this
```
my-custom-plugin/
├── src/                  
│   ├── Blocks/
│   │    └── Static/     
│   │         ├── css/  
│   │         ├── js/  
│   │         ├── views/  
│   │         │    ├── example-block.php  
│   │         │    └── another-example-block.php
│   │         └── images/  
│   ├── Admin/             
│   │    ├── Admin.php
│   │    └── Static/
│   │         ├── css/  
│   │         ├── js/  
│   │         ├── views/  
│   │         │    ├── admin-page.php  
│   │         │    └── another-admin-page.php
│   │         └── images/  
│   ├── Main/        
│   │    ├── Main.php     
│   │    └── Static/
│   │         ├── css/  
│   │         ├── js/  
│   │         └── images/  
```
You can use the first param of the render() method to point to the domain folder
```php
$views->register([
    'dir' => plugin_dir_path(__FILE__), 
    'path' => 'src'
 ]);
 
echo $views->render('Blocks', 'my-example-block')->args([
    'my-argument' => 'my-value'
]);
```

If you use another name for "Static" you can use the "entry" setting to change it to something else
```php
$views->register([
    'dir' => plugin_dir_path(__FILE__), 
    'path' => 'src',
    'entry' => 'templates'
 ]);
```