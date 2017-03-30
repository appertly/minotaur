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

use Psr\Http\Message\ResponseInterface as Response;
use Caridea\Http\Pagination;

/**
 * A trait that can be used by controllers who need to return typical JSON
 */
trait JsonHelper
{
    /**
     * Send something as JSON
     *
     * @param $response - The response
     * @param $payload - The object to serialize
     * @return - The JSON response
     */
    protected function sendJson(Response $response, $payload): Response
    {
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Sends a Content-Range header for pagination
     *
     * @param $response - The response
     * @return - The JSON response
     * @since 0.6.0
     */
    protected function sendItems(Response $response, iterable $items, ?Pagination $pagination = null, ?int $total = null): Response
    {
        if ($items instanceof \Minotaur\Db\CursorSubset) {
            $total = $items->getTotal();
            $items = $items->toArray();
        } else {
            $items = is_array($items) ? $items : iterator_to_array($items, false);
            $total = $total ?? count($items);
        }
        $start = $pagination === null ? 0 : $pagination->getOffset();
        $max = $pagination === null ? 0 : $pagination->getMax();
        // make sure $end is no higher than $total and isn't negative
        $end = max(min((PHP_INT_MAX - $max < $start ? PHP_INT_MAX : $start + $max), $total) - 1, 0);
        return $this->sendJson(
            $response->withHeader('Content-Range', "items $start-$end/$total"),
            $items
        );
    }

    /**
     * Send notice that an entity was created.
     *
     * @param $response - The response
     * @param $type - The entity type
     * @param array<string> $ids The entity ids
     * @param array<string,mixed> $extra Any extra data to serialize
     * @return - The JSON response
     */
    protected function sendCreated(Response $response, string $type, array $ids, array $extra = []): Response
    {
        return $this->sendVerb('created', $response, $type, $ids, $extra)
            ->withStatus(201, "Created");
    }

    /**
     * Send notice that objects were deleted.
     *
     * @param $response - The response
     * @param $type - The entity type
     * @param array<string> $ids The entity ids
     * @param array<string,mixed> $extra Any extra data to serialize
     * @return - The JSON response
     */
    protected function sendDeleted(Response $response, string $type, array $ids, array $extra = []): Response
    {
        return $this->sendVerb('deleted', $response, $type, $ids, $extra);
    }

    /**
     * Send notice that objects were updated.
     *
     * @param $response - The response
     * @param $type - The entity type
     * @param array<string> $ids The entity ids
     * @param array<string,mixed> $extra Any extra data to serialize
     * @return - The JSON response
     */
    protected function sendUpdated(Response $response, string $type, array $ids, array $extra = []): Response
    {
        return $this->sendVerb('updated', $response, $type, $ids, $extra);
    }

    /**
     * Sends a generic notice that objects have been operated on
     *
     * @param $verb - The verb
     * @param $response - The response
     * @param $type - The entity type
     * @param array<string> $ids The entity ids
     * @param array<string,mixed> $extra Any extra data to serialize
     * @return - The JSON response
     */
    protected function sendVerb(string $verb, Response $response, string $type, array $ids, array $extra = []): Response
    {
        $send = array_merge([], $extra);
        $send['success'] = true;
        $send['message'] = "Objects $verb successfully";
        $send['objects'] = array_map(function ($id) use($type) {
            return ['type' => $type, 'id' => $id];
        }, $ids);
        return $this->sendJson($response, $send);
    }
}
