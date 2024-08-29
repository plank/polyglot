<?php

if (! function_exists('html_split')) {
    /**
     * Return an array of safely split HTML strings
     */
    function html_split(string $html, int $maxLength = 1): array
    {
        if ($html === strip_tags($html)) {
            return str_split($html, $maxLength);
        }

        // Regex to find all end tags
        preg_match_all('/<\/[^>]+>/', $html, $matches, PREG_OFFSET_CAPTURE);
        $endTags = $matches[0];

        $chunks = [];
        $lastSplit = 0;
        $lastPosition = 0;
        foreach ($endTags as $tag) {
            $position = $tag[1] + strlen($tag[0]);
            $currentSize = $position - $lastSplit;
            $lastSize = $lastPosition - $lastSplit;

            // If adding this tag exceeds maxLength and lastSize is between 0 and maxLength, split here
            if ($currentSize > $maxLength && $lastSize > 0 && $lastSize < $maxLength) {
                $chunks[] = substr($html, $lastSplit, $lastSize);
                $lastSplit = $lastPosition;
            }
            $lastPosition = $position;
        }

        // Add the remaining part
        if ($lastSplit < strlen($html)) {
            $chunks = array_merge($chunks, str_split(substr($html, $lastSplit), $maxLength));
        }

        return $chunks;
    }
}
