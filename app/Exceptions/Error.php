<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class Error extends Exception
{
    protected $message;
    protected $errorData;
    protected $statusCode;

    /**
     * Error constructor.
     * @param $message
     * @param $errorData
     * @param $statusCode
     */
    public function __construct($statusCode, $message, $errorData=[])
    {
        $this->message = $message;
        $this->errorData = $errorData;
        $this->statusCode = $statusCode;
    }


    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return response()->json([
            'message' => $this->message,
            'error_data' => $this->errorData
        ], $this->statusCode);
    }
}
