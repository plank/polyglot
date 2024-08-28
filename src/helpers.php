<?php

if (!function_exists('html_split')) {
    /**
     *
     * Return an array of safely split HTML strings
     *
     * @param  string  $html
     * @param  int  $maxLength
     * @return array
     */
    function html_split(string $html, int $maxLength = 1): array {
        if ($html === strip_tags($html)) {
            return str_split($html, $maxLength);
        }

        // Regex to find all end tags
        preg_match_all('/<\/[^>]+>/', $html, $matches, PREG_OFFSET_CAPTURE);
        $endTags = $matches[0];

        $chunks = [];
        $lastSplit = 0;
        $lastTagPos = 0;
        foreach ($endTags as $tag) {
            $tagPos = $tag[1] + strlen($tag[0]);
            if ($tagPos - $lastSplit > $maxLength) {
                $chunks[] = substr($html, $lastSplit, $lastTagPos - $lastSplit);
                $lastSplit = $lastTagPos;
            }
            $lastTagPos = $tagPos;
        }

        // Add the remaining part
        if ($lastSplit < strlen($html)) {
            $chunks = array_merge($chunks, str_split(substr($html, $lastSplit), $maxLength));
        }

        return $chunks;
    }
}
