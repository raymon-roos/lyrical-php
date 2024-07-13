<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp;

class Lyrical
{
    private GuzzleHttp\Client $client;

    private ?string $url;

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . trim(file_get_contents(__DIR__ .  '/../.token')),
            ]
        ]);
    }

    /**
     * Retrieve plain text lyrics from [genius](genius.com)
     *
     * Searching is most effective with both a song title and
     * artist name, though the latter is optional.
     *
     * @return string formatted plain text lyrics.
     */
    public function getLyricsFromSearch(string $title, ?string $artist = null): string
    {
        return $this->extractLyrics(
            $this->getLyricsDomNodes(
                $this->url = $this->getLyricsUrl($title, $artist)
            )
        );
    }

    /**
    * Extract lyrics from a genius URL directly.
    *
    * @return formatted lyrics as string
    */
    public function getLyricsFromUrl(string $url): string
    {
        return $this->extractLyrics(
            $this->getLyricsDomNodes($url)
        );
    }

    /**
    * Retrieve URL to lyrics page of the closest match to the search terms,
    * as determined by simple heuristics.
    *
    * Searching depends on the results provided by Genius. The only
    * optimization towards accuracy is to limit results to those of
    * the given artist.
    *
    * @return string URL to lyrics page
    */
    private function getLyricsUrl(string $title, ?string $artist = null): string
    {
        $hits = $this->searchGenius($title, $artist);

        if ($artist) {
            $hits = array_filter(
                $hits,
                fn ($hit) => str_contains(
                    strtolower($hit['result']['artist_names']),
                    $artist
                )
                    && $hit['type'] === 'song'
            );
        }

        return (array_shift($hits) ?? die('no lyrics found'))['result']['url'];
    }

    /**
    * Query the [genius API](https://api.genius.com/search) to find candidate
    * results matching the search terms.
    *
    * @return array search hits
    */
    private function searchGenius(string $title, ?string $artist = null): array
    {
        return json_decode(
            $this->client
                    ->get('https://api.genius.com/search', [
                        'query' => [ 'q' => "$artist $title" ]
                    ])
                ->getBody()
                ->getContents(),
            true
        )['response']['hits'];
    }

    /**
    * Retrieve the DOM nodes containing lyrics from a [genius API](https://api.genius.com/search) URL
    *
    * @param string $lyricsUrl URL to a Genius song page containing lyrics
    */
    private function getLyricsDomNodes(string $lyricsUrl): \DOMNodeList
    {
        ($doc = new \DOMDocument())
            ->loadHTML(
                $this->client
                    ->get("$lyricsUrl")
                    ->getBody()
                    ->getContents(),
                LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOBLANKS
            );

        return $doc->getElementById('lyrics-root')
            ->childNodes;
    }

    /**
     * Pull lyrics strings out of DOM nodes containing lyrics
     */
    private function extractLyrics(\DOMNodeList $nodes): string
    {
        $lyrics = "Retrieved from: {$this->url}" . PHP_EOL;

        foreach ($nodes as $node) {
            if (str_contains($node->className, 'Lyrics__Container')) {
                $lyrics .= $this->formatLyricsText($node->childNodes);
            }
        }

        return $lyrics;
    }

    /**
     * Nicely format an un-punctuated input lyrics string.
     *
     * Bars of lyrics are separated with `<br>` elements, which are
     * lost when calling `->textContent`. As such, new lines have
     * to be added between nodes.
     *
     * @return string formatted plain text lyrics.
     */
    private function formatLyricsText(\DOMNodeList $nodes): string
    {
        $lyrics = '';
        $NL = PHP_EOL;

        foreach ($nodes as $node) {
            // New line for sections like '[Chorus]'
            if (trim($node->textContent, '[]') !== $node->textContent) {
                $lyrics .= $NL;
            }

            if (!empty($node->textContent)) {
                // Workaround for punctuation being lost because <br> inside of <span>
                // not being handled by PHP's HTML parser.
                $lyrics .= wordwrap(
                    preg_replace(
                        '/([)a-z])([A-Z(])/',
                        "\\1$NL\\2",
                        $node->textContent
                    ) . $NL,
                    50
                );
            }
        }

        return $lyrics;
    }
}
