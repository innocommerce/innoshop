<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use DOMDocument;
use DOMNodeList;
use DOMXPath;

class Dom
{
    private DOMDocument $document;

    private DOMXPath $xpath;

    public function __construct(string $htmlContent)
    {
        $this->document = new DOMDocument;
        @$this->document->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $this->xpath = new DOMXPath($this->document);
    }

    /**
     * Creates an instance of the Dom class.
     *
     * @param  string  $htmlContent  The HTML content to parse.
     * @return Dom
     */
    public static function getInstance(string $htmlContent): Dom
    {
        return new self($htmlContent);
    }

    /**
     * Inserts new content before the specified element.
     *
     * @param  string  $selector  The selector for the element (supports .class or #id).
     * @param  string  $content  The HTML content to insert.
     * @return string The modified HTML content.
     */
    public function insertBefore(string $selector, string $content): string
    {
        $nodes = $this->findNodes($selector);
        foreach ($nodes as $node) {
            $fragment = $this->document->createDocumentFragment();
            $fragment->appendXML($content);
            $node->parentNode->insertBefore($fragment, $node);
        }

        return $this->document->saveHTML();
    }

    /**
     * Inserts new content after the specified element.
     *
     * @param  string  $selector  The selector for the element (supports .class or #id).
     * @param  string  $content  The HTML content to insert.
     * @return string The modified HTML content.
     */
    public function insertAfter(string $selector, string $content): string
    {
        $nodes = $this->findNodes($selector);
        foreach ($nodes as $node) {
            $fragment = $this->document->createDocumentFragment();
            $fragment->appendXML($content);
            if ($node->nextSibling) {
                $node->parentNode->insertBefore($fragment, $node->nextSibling);
            } else {
                $node->parentNode->appendChild($fragment);
            }
        }

        return $this->document->saveHTML();
    }

    /**
     * Replaces the content of the specified element with new content.
     *
     * @param  string  $selector  The selector for the element (supports .class or #id).
     * @param  string  $content  The new HTML content.
     * @return string The modified HTML content.
     */
    public function replaceContent(string $selector, string $content): string
    {
        $nodes = $this->findNodes($selector);
        foreach ($nodes as $node) {
            $fragment = $this->document->createDocumentFragment();
            $fragment->appendXML($content);
            $node->parentNode->replaceChild($fragment, $node);
        }

        return $this->document->saveHTML();
    }

    /**
     * Finds nodes in the document based on the given selector.
     *
     * @param  string  $selector  The selector for the element (supports .class or #id).
     * @return DOMNodeList|null The list of found nodes or null if no nodes match.
     */
    private function findNodes(string $selector): ?DOMNodeList
    {
        if (str_starts_with($selector, '.')) {
            $className = substr($selector, 1);

            return $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
        } elseif (str_starts_with($selector, '#')) {
            $idName = substr($selector, 1);

            return $this->xpath->query("//*[@id='$idName']");
        }

        return null;
    }
}
