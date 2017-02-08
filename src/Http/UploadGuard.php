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
use Psr\Http\Message\UploadedFileInterface;

/**
 * A helper for dealing with file upload validation.
 */
class UploadGuard
{
    /**
     * @var \finfo $finfo
     */
    private $finfo;

    /**
     * Creates a new UploadGuard.
     *
     * @param \finfo $finfo The MIME detector
     */
    public function __construct(\finfo $finfo)
    {
        $this->finfo = $finfo;
    }

    /**
     * Gets the list of uploaded files, validating file size.
     *
     * @param Request $request The PSR HTTP Request
     * @param string $field The request field containing the files
     * @param int $maxSize The maximum allowed file size, or `null`
     * @return array<\Psr\Http\Message\UploadedFileInterface> The uploaded files
     * @throws \Caridea\Validate\Exception\Invalid if any files aren't valid
     */
    public function getUploadedFiles(Request $request, string $field, ?int $maxSize = null): array
    {
        $allFiles = $request->getUploadedFiles();
        if (!array_key_exists($field, $allFiles)) {
            throw new \Caridea\Validate\Exception\Invalid([$field => 'REQUIRED']);
        }
        $files = $allFiles[$field];
        $files = is_iterable($files) ? $files : [$files];
        foreach ($files as $file) {
            $error = $file->getError();
            if (UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error) {
                throw new \Caridea\Validate\Exception\Invalid([$field => 'TOO_LONG']);
            } elseif (UPLOAD_ERR_PARTIAL === $error) {
                throw new \Caridea\Validate\Exception\Invalid([$field => 'TOO_SHORT']);
            } elseif (UPLOAD_ERR_NO_FILE === $error) {
                throw new \Caridea\Validate\Exception\Invalid([$field => 'CANNOT_BE_EMPTY']);
            } elseif (UPLOAD_ERR_NO_TMP_DIR === $error || UPLOAD_ERR_CANT_WRITE === $error) {
                throw new \RuntimeException("Cannot write uploaded file to disk");
            }
            if ($maxSize !== null && $maxSize > 0) {
                $size = $file->getSize();
                if ($size > $maxSize) {
                    throw new \Caridea\Validate\Exception\Invalid([$field => 'TOO_LONG']);
                }
            }
        }
        return is_array($files) ? $files : iterator_to_array($files, false);
    }

    /**
     * Validates the uploaded files in a request.
     *
     * @param \Psr\Http\Message\UploadedFileInterface $file The uploaded file
     * @param array<string> $mimeTypes A set of allowed MIME types (e.g. `image/svg+xml`, 'video/*')
     * @return string The MIME type
     * @throws \Caridea\Validate\Exception\Invalid if the file isn't valid
     */
    public function getMimeType(UploadedFileInterface $file, string $field, array $mimeTypes = []): string
    {
        $mime = $this->finfo->file($file->getStream()->getMetadata('uri'), FILEINFO_MIME_TYPE);
        if ($mimeTypes !== null && !$mimeTypes->contains($mime)) {
            $match = false;
            foreach ($mimeTypes as $t) {
                if (substr($t, -2, 2) === '/*' &&
                        substr_compare($mime, strstr($t, '/', true), 0, strlen($t) - 2) === 0) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                throw new \Caridea\Validate\Exception\Invalid([$field => 'WRONG_FORMAT']);
            }
        }
        return $mime;
    }
}
