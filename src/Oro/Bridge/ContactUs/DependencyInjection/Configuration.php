<?php

namespace Oro\Bridge\ContactUs\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'oro_contact_us_bridge';
    const ENABLE_CONTACT_REQUEST = 'enable_contact_request';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root(self::ROOT_NODE);

        SettingsBuilder::append(
            $rootNode,
            [
                self::ENABLE_CONTACT_REQUEST => ['type' => 'boolean', 'value' => true],
            ]
        );

        return $treeBuilder;
    }
}
