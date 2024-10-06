<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // For a 500 Internal Server Error, pass custom data to the error view
        if ($this->isHttpException($exception) && $exception->getStatusCode() == 500) {
            // Generate a unique reference ID for this error
            $referenceId = Str::uuid();

            // Log the error with the reference ID for support tracking
            Log::error('500 Error', [
                'reference_id' => $referenceId,
                'error_message' => $exception->getMessage(),
                'stack_trace' => $exception->getTraceAsString()
            ]);

            // Pass the reference ID and timestamp to the view
            return response()->view('errors.500', [
                'referenceId' => $referenceId,
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }

        // Default error handling
        return parent::render($request, $exception);
    }
}
