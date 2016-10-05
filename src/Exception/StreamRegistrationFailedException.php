<?php

namespace PG\NtlmSoap\Exception;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class StreamRegistrationFailedException extends \Exception implements NtlmSoapException
{
    /**
     * @param string $classname
     * @return void
     */
    public function __construct($classname)
    {
        parent::__construct(sprintf(
            'Unable to register handler %s',
            $classname
        ));
    }
}
