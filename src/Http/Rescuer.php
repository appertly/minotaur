<?php
declare(strict_types=1);
/**
 * Minotaur
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 *
 * @copyright 2015-2017 Appertly
 * @license   Apache-2.0
 */
namespace Minotaur\Http;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Caridea\Http\ProblemDetails;

/**
 * A pretty basic contingency plan.
 *
 * Under normal circumstances, this class will simply return the response given
 * by the `$next` function. In the event that an Exception occurred in the
 * `$next` function, this class will craft a new response containing details
 * about the error itself.
 */
class Rescuer implements \Minotaur\Route\Plugin
{
    /**
     * @var bool Whether to include exception information in responses
     */
    protected $debug;
    /**
     * @var string The class name of the XHP to render
     */
    // TODO figure out XHP replacement
    protected $xhpClass;

    /**
     * Convenient map of HTTP status codes to human-readable explanations
     */
    protected const MESSAGES = [
        403 => "You are not allowed to perform this action.",
        404 => "We don't have anything at this URL. Double-check the URL you requested.",
        405 => "You can't use that HTTP method for this URL. Check the Allow response header for the ones you can.",
        406 => "We don't have any content available in the MIME type you specified in your Accept header. Try specifying additional MIME types.",
        422 => "There was a problem with the data you submitted. Review the messages for each field and try again.",
        423 => "This data is locked. You have permission, but it is no longer allowed to be changed.",
        500 => "It looks like we have a problem on our end! Our staff has been notified. Please try again later."
    ];

    /**
     * Creates a new Contingency.
     *
     * The following options are available:
     * * `debug` – Whether to include exception stack trace information (*should be `false` in production!*). Defaults to `false`.
     * * `xhpClass` – The class name of XHP to render (must be `xhp_class_name` format). Defaults to `xhp__labrys__error_page`.
     *
     * @param array<string,mixed> $options The options
     */
    public function __construct(array $options = [])
    {
        $this->debug = (bool)($options['debug'] ?? false);
        // TODO figure out XHP replacement
        $c = (string)($options['xhpClass'] ?? 'xhp_labrys__error_page');
        $this->xhpClass = $c;
    }

    /**
     * Gets the plugin priority; larger means first.
     *
     * @return - The plugin priority
     */
    public function getPriority(): int
    {
        return PHP_INT_MAX;
    }

    /**
     * Middleware request–response handling.
     *
     * @param $request - The server request
     * @param $response - The response
     * @param callable $next - The next layer. (function (Request,Response): Response)
     * @return - The response
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            return $this->process($request, $response, $e);
        }
    }

    /**
     * Handles an exception.
     *
     * This is your chance for logging, changing the HTTP status header, and
     * rendering some kind of message for the client.
     *
     * @param $request - The request
     * @param $response - The response
     * @param $e - The exception to process
     * @return - The new response
     */
    public function process(Request $request, Response $response, \Exception $e) : Response
    {
        if ($e instanceof \Minotaur\Route\Exception\Unroutable) {
            $response = $response->withStatus($e->getCode(), $e->getMessage());
            foreach ($e->getHeaders() as $k => $v) {
                $response = $response->withHeader($k, $v);
            }
        } elseif ($e instanceof \Caridea\Acl\Exception\Forbidden) {
            $response = $response->withStatus(403, 'Forbidden');
        } elseif ($e instanceof \Caridea\Dao\Exception\Unretrievable ||
            $e instanceof \Caridea\Acl\Exception\Unloadable) {
            $response = $response->withStatus(404, 'Not Found');
        } elseif ($e instanceof \Caridea\Dao\Exception\Conflicting ||
            $e instanceof \Caridea\Dao\Exception\Duplicative) {
            $response = $response->withStatus(409, 'Conflict');
        } elseif ($e instanceof \Caridea\Validate\Exception\Invalid) {
            $response = $response->withStatus(422, 'Unprocessable Entity');
        } elseif ($e instanceof \Caridea\Dao\Exception\Locked) {
            $response = $response->withStatus(423, 'Locked');
        } else {
            $response = $response->withStatus(500, 'Internal Server Error');
        }
        $values = $this->getValues($request, $e);
        $types = new \Caridea\Http\AcceptTypes($request->getServerParams());
        switch ($types->preferred(['application/json', ProblemDetails::MIME_TYPE_JSON, 'text/html'])) {
            /* HH_IGNORE_ERROR[4110]: Not sure why hh_client has a problem with this */
            case ProblemDetails::MIME_TYPE_JSON:
            /* HH_IGNORE_ERROR[4110]: Not sure why hh_client has a problem with this */
            case 'application/json':
                $response = $response->withHeader('Content-Type', ProblemDetails::MIME_TYPE_JSON);
                $response->getBody()->write((string)$this->renderJson($values));
                break;
            default:
                $response = $response->withHeader('Content-Type', 'text/html');
                $response->getBody()->write((string)$this->renderHtml($values));
        }
        return $response;
    }

    /**
     * Assembles the values from the Request and Exception.
     *
     * @param $request - The request
     * @param $e - The Exception
     * @return array<string,mixed> The assembled values
     */
    protected function getValues(Request $request, \Exception $e): array
    {
        $values = [];
        $extra = ['success' => false];
        if ($this->debug) {
            $extra['exception'] = $this->getStackTrace($e);
        }
        if ($e instanceof \Minotaur\Route\Exception\Unroutable) {
            $code = $e->getCode();
            $values['title'] = $e->getMessage();
            $values['status'] = $code;
            $values['detail'] = self::MESSAGES[$code];
        } elseif ($e instanceof \Caridea\Acl\Exception\Forbidden) {
            $values['title'] = 'Access Denied';
            $values['status'] = 403;
            $values['detail'] = self::MESSAGES[403];
        } elseif ($e instanceof \Caridea\Dao\Exception\Unretrievable ||
            $e instanceof \Caridea\Acl\Exception\Unloadable) {
            $values['title'] = 'Resource Not Found';
            $values['status'] = 404;
            $values['detail'] = self::MESSAGES[404];
        } elseif ($e instanceof \Caridea\Dao\Exception\Duplicative) {
            $values['title'] = 'Constraint Violation';
            $values['status'] = 409;
            $values['detail'] = 'The data you submitted violates unique constraints. Most likely, this is a result of an existing record with similar data. Double-check existing records and try again.';
        } elseif ($e instanceof \Caridea\Dao\Exception\Conflicting) {
            $values['title'] = 'Concurrent Modification';
            $values['status'] = 409;
            $values['detail'] = 'Someone else saved changes to this same data while you were editing. Try your request again using the latest copy of the record.';
        } elseif ($e instanceof \Caridea\Validate\Exception\Invalid) {
            $values['title'] = 'Data Validation Failure';
            $values['status'] = 422;
            $values['detail'] = self::MESSAGES[422];
            $errors = [];
            foreach ($e->getErrors() as $field => $code) {
                $errors[] = ['field' => $field, 'code' => $code];
            }
            $extra['errors'] = $errors;
        } elseif ($e instanceof \Caridea\Dao\Exception\Locked) {
            $values['title'] = 'Resource Locked';
            $values['status'] = 423;
            $values['detail'] = self::MESSAGES[423];
        } else {
            $values['title'] = 'Internal Server Error';
            $values['status'] = 500;
            $values['detail'] = self::MESSAGES[500];
        }
        $values['extra'] = $extra;
        return $values;
    }

    /**
     * Gets an exception stack trace as a string, including nested exceptions.
     *
     * @param $e - The exception
     * @return array<string,string> The full stack trace
     */
    private function getStackTrace(\Exception $e): array
    {
        $details = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ];
        if ($e->getPrevious() !== null) {
            $details['previous'] = $this->getStackTrace($e->getPrevious());
        }
        return $details;
    }

    /**
     * Returns the ProblemDetails to render.
     *
     * @param array<string,mixed> $values The values
     * @return ProblemDetails The JSON response
     */
    protected function renderJson(array $values): ProblemDetails
    {
        $extra = $values['extra'] ?? [];
        return new ProblemDetails(
            null,
            (string) $values['title'],
            (int) $values['status'],
            (string) $values['detail'],
            null,
            $extra
        );
    }

    /**
     * Returns the XHP to render.
     *
     * @param array<string,mixed> $values The values
     * @return mixed The HTML response
     */
    // TODO figure out XHP replacement
    protected function renderHtml(array $values)
    {
        $c = $this->xhpClass;
        /* HH_IGNORE_ERROR[4026]: This definitely works */
        return new $c(['values' => $values], []);
    }
}
