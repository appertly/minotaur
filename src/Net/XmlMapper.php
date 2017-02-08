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
namespace Minotaur\Net;

/**
 * Turns strings into XML
 */
class XmlMapper
{
    /**
     * Converts a string to a SimpleXMLElement.
     *
     * @param string $xml The string to convert, or `null`
     * @return \SimpleXmlElement The XML version
     * @throws \Minotaur\Net\Exception\Illegible If the string is not XML
     */
    public function toXml(?string $xml): \SimpleXMLElement
    {
        try {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xml);
            if ($xml === false) {
                $errors = libxml_get_errors();
                throw new Exception\Illegible($xml, "Invalid XML. " . implode(". ", $errors));
            }
            return $xml;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(true);
        }
    }
}
