<?php
namespace Brander\Bundle\EAVBundle\Service\Filter;

use Brander\Bundle\EAVBundle\Model\Filter\FilterModelProviderInterface;

/**
 * Service to work with js filter attributes
 *
 * @author Tomfun <tomfun1990@gmail.com>
 */
class FilterHolder
{
    /**
     * @var FilterModelProviderInterface[]
     */
    private $providers = [];
    /**
     * @var string[]
     */
    private $list = null;

    /**
     * @param FilterModelProviderInterface $provider
     */
    public function addProvider(FilterModelProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @return string[]
     */
    public function getJsModels()
    {
        if ($this->list === null) {
            $list = [];
            foreach ($this->providers as $provider) {
                $list = array_merge($list, $provider->getAvailableFilterModels());
            }
            $this->list = array_unique($list);
        }

        return $this->list;
    }
}