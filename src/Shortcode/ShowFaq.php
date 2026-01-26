<?php

declare(strict_types=1);

namespace NiceSimpleVp\Shortcode;

use NiceSimpleVp\Repository\FaqRepository;

readonly class ShowFaq implements Shortcode
{

    public function __construct(private FaqRepository $faqRepository) {}

    public function register(): void
    {
        add_shortcode( 'show_faq', [$this, 'shortcode_show_faq'] );
        add_shortcode( 'show_faq_table', [$this, 'shortcode_show_faq_table'] );
    }

    /**
     * @param array{"open_first"?: bool, "two_columns_layout"?: bool} $atts
     */
    public function shortcode_show_faq(array $atts = []): string
    {
        $faqs = $this->faqRepository->getAll();
        $html = '';

        if (empty($faqs)) {
            return $html;
        }

        $defaults = ['open_first' => false, 'two_columns_layout' => false];
        $atts = shortcode_atts($defaults, $atts );
        $openFirst = filter_var($atts['open_first'], FILTER_VALIDATE_BOOLEAN);
        $twoColumnsLayout = filter_var($atts['two_columns_layout'], FILTER_VALIDATE_BOOLEAN);
        $lastKey = array_key_last($faqs);
        $jsonLd = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];

        $jsonLdScript = '<script type="application/ld+json">';
        $html .= '<div class="accordion-wrapper">';
        $html .= sprintf('<h2>%s</h2>', __('FAQ', 'nice-simple-vp'));

        if ($twoColumnsLayout) {
            $html .= '<div class="row">';
        }

        foreach ($faqs as $key => $faq) {
            setup_postdata( $faq );
            $index = $key + 1;
            $id = $faq->ID;
            $title = get_the_title($id);
            $content = get_the_content($id);
            $checked = ($openFirst && $key === 0) ? 'checked' : '';

            $html .= sprintf('<input type="checkbox" id="faq-item-%d" class="accordion-input" %s>', $id, $checked);
            $html .= '<div class="accordion-item">';

            $html .= sprintf('<label for="faq-item-%d" class="accordion-label">%s</label>', $id, $title);
            $html .= '<div class="accordion-content">';
            $html .= sprintf('<div class="accordion-inner">%s</div>', $content);
            $html .= '</div>'; // ./accordion-content
            $html .= '</div>'; // ./accordion-item

            $jsonLd['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $title,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $content
                ]
            ];

            if ($twoColumnsLayout && ($lastKey > $key) && ($index % 2 === 0)) {
                $html .= '</div>'; // ./row
                $html .= '<div class="row">';
            }
        }
        wp_reset_postdata();
        $jsonLdScript .=  json_encode($jsonLd, JSON_UNESCAPED_UNICODE). '</script>'; // json+ld

        if ($twoColumnsLayout) {
            $html .= '</div>'; // ./row
        }

        $html .= '</div>'; // ./accordion-wrapper

        return $jsonLdScript.$html;
    }

    public function shortcode_show_faq_table(): string
    {
        $faqs = $this->faqRepository->getAll();
        $html = '';

        if (empty($faqs)) {
            return $html;
        }

        $jsonLd = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
        $jsonLdScript = '<script type="application/ld+json">';

        $html .= '<div class="responsive-table">';
        $html .= '<div class="thead">';
        $html .= '<div class="tr">';
        $html .= '<div class="th">Question</div><div class="th">Answer</div>';
        $html .= '</div>'; // ./tr
        $html .= '</div>'; // ./thead
        $html .= '<div class="tbody">';

        foreach ($faqs as $faq) {
            setup_postdata( $faq );
            $id = $faq->ID;
            $title = get_the_title($id);
            $content = get_the_content($id);

            $html .= '<div class="tr">';
            $html .= sprintf('<div class="td" data-th="Question">%s</div>', $title);
            $html .= sprintf('<div class="td" data-th="Answer">%s</div>', $content);
            $html .= '</div>'; // ./tr

            $jsonLd['mainEntity'][] = [
                '@type' => 'Question',
                'name' => $title,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $content
                ]
            ];
        }
        wp_reset_postdata();
        $jsonLdScript .=  json_encode($jsonLd, JSON_UNESCAPED_UNICODE). '</script>'; // json+ld

        $html .= '</div>'; // ./tbody
        $html .= '</div>'; // ./responsive-table

        return $jsonLdScript.$html;
    }
}