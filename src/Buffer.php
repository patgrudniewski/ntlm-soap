<?php

namespace PG\NtlmSoap;

/**
 * @author Patryk Grudniewski <patgrudniewski@gmail.com>
 */
class Buffer
{
    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $length;

    /**
     * @param string $buffer
     * @return void
     */
    public function __construct($buffer)
    {
        $this->buffer = $buffer;
        $this->position = 0;
        $this->length = strlen($buffer);
    }
}
