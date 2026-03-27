<?php

function renderADF($adf, $attachments = [])
{
    if (!is_array($adf) || empty($adf['content'])) {
        return '';
    }

    // map attachment id → url
    $jiraAttachmentsMap = [];

    if (!empty($attachments)) {
        foreach ($attachments as $att) {
            $jiraAttachmentsMap[$att['id']] = $att['content'];
        }
    }

    $html = '';

    foreach ($adf['content'] as $block) switch ($block['type']) {

        case 'paragraph':
            $html .= '<p>';
            if (!empty($block['content'])) {
                foreach ($block['content'] as $node) {
                    $html .= renderInlineNode($node);
                }
            }
            $html .= '</p>';
            break;

        case 'heading':
            $level = $block['attrs']['level'] ?? 2;
            $html .= "<h{$level}>";
            foreach ($block['content'] as $node) {
                $html .= renderInlineNode($node);
            }
            $html .= "</h{$level}>";
            break;

        case 'bulletList':
            $html .= '<ul>';
            foreach ($block['content'] as $item) {
                $html .= '<li>';
                foreach ($item['content'][0]['content'] as $node) {
                    $html .= renderInlineNode($node);
                }
                $html .= '</li>';
            }
            $html .= '</ul>';
            break;

        case 'orderedList':
            $html .= '<ol>';
            foreach ($block['content'] as $item) {
                $html .= '<li>';
                foreach ($item['content'][0]['content'] as $node) {
                    $html .= renderInlineNode($node);
                }
                $html .= '</li>';
            }
            $html .= '</ol>';
            break;

        case 'mediaSingle':

            $media = $block['content'][0]['attrs'] ?? [];

            $dataWidth = $block['attrs']['width'] ?? null;
            $widthStyle = '';
            if (is_numeric($dataWidth)) {
                $widthStyle = "max-width:{$dataWidth}px;height:auto;";
            }

            // external (OK)
            if (!empty($media['url'])) {
                $url = htmlspecialchars($media['url']);
                $html .= "<p><img loading=\"lazy\" decoding=\"async\" src=\"{$url}\" style=\"{$widthStyle}\" /></p>";
            }

            // Jira file
            elseif (!empty($media['type']) && $media['type'] === 'file') {

                $filename = $media['alt'] ?? '';

                if (!empty($attachments)) {
                    foreach ($attachments as $att) {

                        if ($att['filename'] === $filename) {

                            $id = $att['id'];
                            $name = urlencode($att['filename']);

                            $proxyUrl = "/attachment-proxy?id={$id}&name={$name}";

                            $html .= "<p><img loading=\"lazy\" decoding=\"async\" src=\"{$proxyUrl}\" style=\"{$widthStyle}\" /></p>";
                            break;
                        }
                    }
                }
            }
            break;

    }

    return $html;
}

function renderInlineNode($node)
{
    $text = htmlspecialchars($node['text'] ?? '');

    if (!empty($node['marks'])) {
        foreach ($node['marks'] as $mark) {

            switch ($mark['type']) {
                case 'strong':
                    $text = "<strong>{$text}</strong>";
                    break;
                case 'em':
                    $text = "<em>{$text}</em>";
                    break;
                case 'link':
                    $href = $mark['attrs']['href'] ?? '#';
                    $text = "<a href='{$href}' target='_blank'>{$text}</a>";
                    break;
            }
        }
    }

    return $text;
}