<?php
namespace Brander\Bundle\EAVBundle;

use Brander\Bundle\EAVBundle\DependencyInjection\Compiler\ElasticEavListCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * BranderEAVBundle.
 *
 * @author Vladimir Odesskij <odesskij1992@gmail.com>
 */
class BranderEAVBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticEavListCompilerPass(), PassConfig::TYPE_OPTIMIZE);
    }
}
