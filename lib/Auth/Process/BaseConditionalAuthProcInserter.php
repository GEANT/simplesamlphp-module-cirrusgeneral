<?php

namespace SimpleSAML\Module\cirrusgeneral\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;
use SimpleSAML\Module\cirrusgeneral\Auth\AuthProcRuleInserter;

/**
 * Conditionally create new authproc filters at the location of this filter
 */
abstract class BaseConditionalAuthProcInserter extends ProcessingFilter
{
    protected $authProcs;

    protected $elseAuthProcs;


    public function __construct(&$config, $reserved)
    {
        parent::__construct($config, $reserved);
        $conf = Configuration::loadFromArray($config);
        $this->authProcs = $conf->getArray('authproc', []);
        $this->elseAuthProcs = $conf->getArray('elseAuthproc', []);
    }


    public function process(&$state)
    {
        if ($this->checkCondition($state)) {
            $filtersToAdd = $this->authProcs;
            Logger::debug('conditionalAuthProc true. Adding `authproc` filters:' . count($filtersToAdd));
        } else {
            $filtersToAdd = $this->elseAuthProcs;
            Logger::debug('conditionalAuthProc false. Adding `elseAuthproc` filters:' . count($filtersToAdd));
        }
        $ruleInserter = new AuthProcRuleInserter();
        $ruleInserter->createAndInsertFilters($state, $filtersToAdd);
    }

    /**
     * @return bool true indicate `authproc` filters should be added. false to add `elseAuthproc`
     */
    abstract protected function checkCondition(array &$state): bool;
}
