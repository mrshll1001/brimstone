<?php

namespace AppBundle\DependencyInjection\Compiler;
//
// use AppBundle\Service\ArticleRssFeedProvider;
// use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
// use Symfony\Component\DependencyInjection\ContainerBuilder;
//
// class OverrideServiceCompilerPass implements CompilerPassInterface
// {
//     public function process(ContainerBuilder $container)
//     {
//         $definition = $container->getDefinition('debril.provider.mock');
//         $definition->setClass(ArticleRssFeedProvider::class);
//     }
// }
use AppBundle\Service\ArticleRssFeedProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideServiceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('debril.provider.default');
        $definition->setClass(ArticleRssFeedProvider::class);
        // $definition->addArgument(new Reference('AppBundle\Repository\PostRepository'));
        $definition->addArgument(new Reference('doctrine.orm.entity_manager'));
        $definition->addArgument(new Reference('router'));


    }
}
