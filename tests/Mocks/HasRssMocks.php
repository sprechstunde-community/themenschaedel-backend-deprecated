<?php

namespace Tests\Mocks;

/**
 * Integrate RSS response mocks into your test cases.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
trait HasRssMocks
{
    /**
     * Return response mocks keyed by their identifier.
     *
     * @param callable|string|null $filter Filter responses by either providing a keyword, that the filename must
     *     contain or by providing a callback to check each filename individually.
     *
     * @return string[] Response mocks
     * @internal
     */
    protected function getRssResponses($filter = null): array
    {
        // generate directory path
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'Rss';

        $files = scandir($path, SCANDIR_SORT_ASCENDING);

        array_shift($files); // remove entry '.'
        array_shift($files); // remove entry '..'

        // only contain files that have the keyword response in it
        //        $files = array_filter($files, fn($file) => substr($file, 0, 9) === 'response.');

        if (is_callable($filter)) {
            // apply provided filter
            $files = array_filter($files, $filter);
        }

        if (is_string($filter)) {
            // filter by keyword
            $files = array_filter($files, fn($file) => (bool)substr_count($file, $filter));
        }

        // build identifiers
        $keys = array_map(fn($file) => str_replace(['.xml'], '', $file), $files);

        // load response from file
        $responses = array_map(fn($filename) => file_get_contents($path . DIRECTORY_SEPARATOR . $filename), $files);

        return array_combine($keys, $responses);
    }
}
