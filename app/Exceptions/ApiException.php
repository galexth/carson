<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ApiException extends Exception
{
    /**
     * @var string
     */
    protected $reason;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * ApiException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param string $reason
     * @param string $description
     */
    public function __construct(string $message, int $code, string $reason = 'api_error', string $description = 'Api exception error.')
    {
        parent::__construct($message, $code);

        $this->reason = $reason;
        $this->description = $description;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Illuminate\Http\Response
     */
    public function getResponse()
    {
        return new Response([
            'reason' => $this->getReason(),
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'trace' => $this->getTrace(),
        ], $this->code);
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

}
