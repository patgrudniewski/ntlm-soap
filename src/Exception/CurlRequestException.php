<?php

namespace PG\NtlmSoap\Exception;

class CurlRequestException extends \Exception implements NtlmSoapException
{
    /**
     * @var resource
     */
    protected $adapter;

    /**
     * @param resource $adapter
     * @return void
     */
    public function __construct($adapter)
    {
        $this->adapter = $adapter;

        parent::__construct(curl_error($adapter), curl_errno($adapter));
    }

    /**
     * @return resource
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
