<?php
namespace Drahak\Rest\DI;

use Drahak\Rest\Application\MethodAnnotation;
use Drahak\Rest\Application\Routes\ResourceRoute;
use Drahak\Rest\Application\Routes\ResourceRouteList;
use Drahak\Rest\IResourceRouter;
use Nette\Caching\Storages\FileStorage;
use Nette\Config\CompilerExtension;
use Nette\Config\Configurator;
use Nette\Loaders\RobotLoader;

/**
 * Drahak\Rest Extension
 * @package Drahak\Rest\DI
 * @author Drahomír Hanák
 */
class RestExtension extends CompilerExtension
{

    /**
     * Default DI settings
     * @var array
     */
    protected $defaults = array(
        'cacheDir' => '%tempDir%/cache',
        'routes' => array(
            'presentersRoot' => '%appDir%',
            'autoGenerated' => TRUE,
            'module' => ''
        )
    );

    /**
     * Load DI configuration
     */
    public function loadConfiguration()
    {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        $container->addDefinition($this->prefix('responseFactory'))
            ->setClass('Drahak\Rest\ResponseFactory');

        $container->addDefinition($this->prefix('resourceFactory'))
            ->setClass('Drahak\Rest\ResourceFactory');

        // Generate routes from presenter annotations
        if ($config['routes']['autoGenerated']) {
            $container->addDefinition($this->prefix('routeListFactory'))
                ->setClass('Drahak\Rest\Application\Routes\RouteListFactory')
                ->setArguments(array($config['routes']));

            $container->getDefinition('router')
                ->addSetup('@rest.routeListFactory::addRoutes', '@self');
        }
    }


    /**
     * Register REST API extension
     * @param Configurator $configurator
     */
    public static function install(Configurator $configurator)
    {
        $configurator->onCompile[] = function($configurator, $compiler) {
            $compiler->addExtension('rest', new RestExtension);
        };
    }

}