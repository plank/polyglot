<?php

describe('html_split helper', function () {
    it('can split html safely', function () {
        $html = '<p>First paragraph</p><p>Second paragraph</p>';
        $expected = ['<p>First paragraph</p>', '<p>Second paragraph</p>'];
        $max = 25;

        expect(html_split($html, $max))->toBe($expected)->not->toBe(str_split($html, $max));
    });

    it('falls back to str_split on simple strings', function () {
        $text = fake()->paragraph(4, false);

        expect(html_split($text, 25))->toBe(str_split($text, 25));
        expect(html_split($text))->toBe(str_split($text));
    });

    it('falls back to str_split when length is smaller than any html tag', function () {
        $html = fake()->randomHtml();

        // smallest html tag is 4 characters
        for ($i = 1; $i < 5; $i++) {
            expect(html_split($html, $i))->toBe(str_split($html, $i));
        }
    });

    it('can handle long strings used in the translation service', function () {
        $html = '';
        while (strlen($html) < 200000) {
            $html = fake()->randomHtml(5, 25);
        }

        foreach ([10000, 15000, 25000, 102400] as $max) {
            $chunks = html_split($html, $max);

            expect($chunks)
                ->toBeArray()
                ->not->toBeEmpty()
                ->and(implode('', $chunks))
                ->toBe($html);

            $lengths = array_map(fn ($chunk) => strlen($chunk), $chunks);

            expect($lengths)->each(fn ($chunk) => $chunk->toBeLessThanOrEqual($max));
        }
    });
});
