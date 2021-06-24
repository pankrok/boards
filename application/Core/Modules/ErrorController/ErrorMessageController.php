<?php

declare(strict_types = 1);

namespace Application\Core\Modules\ErrorController;

use Slim\Interfaces\ErrorRendererInterface;

class ErrorMessageController implements ErrorRendererInterface
{
    public function __invoke(\Throwable $e, bool $displayErrorDetails): string
    {
        $data = file_get_contents(MAIN_DIR . '/public/pages/error.html');
        $displayErrorDetails ? $style = 'body{padding:1em;} *{padding:0px;margin:3px;}' : $style = file_get_contents(MAIN_DIR . '/public/pages/style.css');
        $displayErrorDetails ? $html = self::renderExceptionFragment($e) : $html =  '<p class="output"><strong>Message:</strong> '.htmlentities($e->getMessage());
        $message = sprintf($data, $style, 'Error: ', $e->getCode(), $html);
        return $message;
    }
    
    private function renderExceptionFragment(\Throwable $exception): string
    {
        $html = sprintf('<p class="output"><strong>Type:</strong> %s</p>', get_class($exception));

        $code = $exception->getCode();
        if ($code !== null) {
            $html .= sprintf('<p class="output"><strong>Code:</strong> %s</p>', $code);
        }

        $message = $exception->getMessage();
        if ($message !== null) {
            $html .= sprintf('<p class="output"><strong>Message:</strong> %s</p>', htmlentities($message));
        }

        $file = $exception->getFile();
        if ($file !== null) {
            $html .= sprintf('<p class="output"><strong>File:</strong> %s</p>', $file);
        }

        $line = $exception->getLine();
        if ($line !== null) {
            $html .= sprintf('<p class="output"><strong>Line:</strong> %s</p>', $line);
        }

        $trace = str_replace('#', '<p class="pm-0">#', $exception->getTraceAsString().'</p>');

        if ($trace !== null) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<div class="trace">%s</div>', $trace);
        }

        return $html;
    }
}
