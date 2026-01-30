<?php

declare(strict_types=1);

namespace NiceSimpleVp\Shortcode;

use NiceSimpleVp\Includes\Utils;
use NiceSimpleVp\Repository\PortfolioRepository;

readonly class ShowPortfolio implements Shortcode
{
    public function __construct(private PortfolioRepository $portfolioRepository) {}

    /**
     * Register all shortcodes here
     */
    public function register(): void
    {
        add_shortcode( 'show_projects', [$this, 'shortcode_show_projects'] );
        add_shortcode( 'featured_projects_slider', [$this, 'shortcode_featured_projects_slider'] );
        add_shortcode('fancy_portfolio_slider', [$this, 'shortcode_fancy_portfolio_slider']);
    }

    public function shortcode_show_projects(): string
    {
        $projects = $this->portfolioRepository->getAll();
        $html = '';

        if (empty($projects)) {
            return $html;
        }

        $html .= '<div class="filter-collections">';
        $html .= Utils::getTermsListAsButtons(
            ['taxonomy' => $this->portfolioRepository->getTaxonomyName(), 'hide_empty' => true]
        );
        $html .= '</div>'; // ./collections

        $html .= '<div class="portfolio-grid">';
        foreach ( $projects as $project ) {
            setup_postdata( $project );
            $id = $project->ID;
            $title = get_the_title($id);
            $featuredImg = get_the_post_thumbnail_url($id, 'medium_large');
            $link = esc_url(get_permalink($id));
            $excerpt = get_the_excerpt($id);
            $collectionLinks = Utils::getTermsAsLinksForTaxonomy($id, $this->portfolioRepository->getTaxonomyName());
            $collections = strip_tags($collectionLinks);

            $html .= '<div class="project-card" data-category="'.$collections.'">';
            $html .= '<div class="card-bg" style="background-image: url('. esc_url($featuredImg) . ');"></div>';
            $html .= '<div class="card-overlay">';
            $html .= '<div class="card-info">';
            $html .= $collectionLinks;
            $html .= '<h3 class="card-title"><a href="'. $link .'">' . $title . '</a></h3>';
            if (!empty($excerpt)) {
                $html .= '<p class="card-desc">' . $excerpt . '</p>';
            }
            $html .= '</div>'; // ./card-info
            $html .= '</div>'; // ./card-overlay
            $html .= '</div>'; // ./project-card
        }
        wp_reset_postdata();
        $html .= '</div>'; // ./portfolio-grid

        wp_enqueue_script('show_projects_portfolio'); // enqueue js file
        return $html;
    }

    public function shortcode_featured_projects_slider(): string
    {
        $projects = $this->portfolioRepository->getFeatured();
        $html = '';

        if (empty($projects)) {
            return $html;
        }

        $projectData = [];
        $countProjects = count($projects);
        $styles = '<style>';
        $styles .= sprintf('.slider .slide {width: %01.2f%%}', 100 / $countProjects).PHP_EOL;
        $styles .= sprintf('.slider .slides-track {width: %d%%}', 100 * $countProjects).PHP_EOL;
        $html .= '<div class="slider">';
        
        foreach ($projects as $key => $project) {
            setup_postdata($project);
            $id = $project->ID;
            $title = get_the_title($id);
            $featuredImgUrl = esc_url(get_the_post_thumbnail_url($id, '2048x2048')); // large , ocean-thumb-l
            $link = esc_url(get_permalink($id));
            $excerpt = get_the_excerpt($id);
            $collectionLinks = Utils::getTermsAsLinksForTaxonomy($id, $this->portfolioRepository->getTaxonomyName());
            $collections = strip_tags($collectionLinks);

            $projectData[] = [
                'id' => $id,
                'title' => $title,
                'featuredImgUrl' => $featuredImgUrl,
                'link' => $link,
                'excerpt' => $excerpt,
                'collections' => $collections,
            ];

            $checked = ($key === 0) ? 'checked' : '';
            $html .= sprintf(
                '<input type="radio" name="slide" id="r%d" %s>',
                $key,
                $checked
            );
        }
        wp_reset_postdata();

        $html .= '<div class="slides-track">';
        foreach ($projectData as $project) {
            $html .= '<div class="slide" style="background-image: url('. $project['featuredImgUrl'] . ');">';
            $html .= '<div class="slide-text-overlay">';
            $html .= '<div class="slide-content">';
            $html .= sprintf('<h2><a href="%s">%s</a></h2>', $project['link'], $project['title']);
            if (!empty($project['excerpt'])) {
                $html .= '<p>'. $project['excerpt'] .'</p>';
            }
            $html .= '</div>'; // ./slide-content
            $html .= '</div>'; // ./slide-text-overlay
            $html .= '</div>'; // ./slide
        }
        $html .= '</div>'; // ./slides-track

//            <div class="arrows">
//            <label for="r3" class="arrow arrow-prev"></label>
//            <label for="r0" class="arrow arrow-prev"></label>
//            <label for="r1" class="arrow arrow-prev"></label>
//            <label for="r2" class="arrow arrow-prev"></label>
//            <label for="r1" class="arrow arrow-next"></label>
//            <label for="r2" class="arrow arrow-next"></label>
//            <label for="r3" class="arrow arrow-next"></label>
//            <label for="r0" class="arrow arrow-next"></label>
//            </div>

        $indexes = array_keys($projectData);
        $lastIndex = array_key_last($indexes);
        $firstIndex = $indexes[0];
        $cnt = count($indexes);

        $html .= '<div class="arrows">';
        $styles .= sprintf(
            '#r%1$d:checked ~ .arrows label[for="r%2$d"].arrow-prev,
                    #r%1$d:checked ~ .arrows label[for="r%3$d"].arrow-next,
                    #r%2$d:checked ~ .arrows label[for="r%4$d"].arrow-prev,
                    #r%2$d:checked ~ .arrows label[for="r%1$d"].arrow-next{ display: flex; }',
            $firstIndex, $lastIndex, $firstIndex + 1, $lastIndex - 1
        ).PHP_EOL;

        foreach ($indexes as $index) {
            if ($index === $firstIndex || $index === $lastIndex) {
                continue;
            }

            $styles .= sprintf(
                '#r%1$d:checked ~ .arrows label[for="r%2$d"].arrow-prev,
                        #r%1$d:checked ~ .arrows label[for="r%3$d"].arrow-next { display: flex; }',
                $index, ($index - 1), ($index + 1)
            ).PHP_EOL;
        }

        $html .= sprintf('<label for="r%d" class="arrow arrow-prev"></label>', array_last($indexes));
        foreach ($indexes as $index) {
            if ($index === $lastIndex) {
                continue;
            }
            $html .= sprintf('<label for="r%d" class="arrow arrow-prev"></label>', $index);
        }

        foreach ($indexes as $index) {
            if ($index === $firstIndex) {
                continue;
            }

            $translateStep = ($index * 100) / $cnt;
            $styles .= sprintf('#r%d:checked ~ .slides-track { transform: translateX(-%01.2f%%); }', $index, $translateStep).PHP_EOL;
            $styles .= sprintf('#r%d:checked ~ .slides-track .slide:nth-child(%d) .slide-text-overlay {opacity: 1; transform: translateY(0);}', $index, ($index + 1)).PHP_EOL;
            $styles .= sprintf('#r%1$d:checked ~ .dots-nav label[for="r%1$d"] { background: #fff; transform: scale(1.2); }', $index).PHP_EOL;
            $html .= sprintf('<label for="r%d" class="arrow arrow-next"></label>', $index);
        }
        $html .= sprintf('<label for="r%d" class="arrow arrow-next"></label>', $firstIndex);
        $html .= '</div>'; // ./arrows

        $html .= '<div class="dots-nav">';
        foreach ($indexes as $index) {
            $html .= sprintf('<label for="r%d" class="dot"></label>', $index);
        }
        $html .= '</div>'; // ./dots-nav
        $html .= '</div>'; // ./slider

        $styles .= '</style>';

        return $styles.$html;
    }

    public function shortcode_fancy_portfolio_slider(): string
    {
        $projects = $this->portfolioRepository->getFeatured();
        $countProjects = count($projects);
        $html = '';

        if (empty($projects)) {
            return $html;
        }

        $projectData = [];
        $styles = '<style>';
        $html .= '<section id="hero" class="hero-slider">';

        foreach ($projects as $key => $project) {
            setup_postdata($project);
            $id = $project->ID;
            $title = get_the_title($id);
            $featuredImgUrl = esc_url(get_the_post_thumbnail_url($id, 'large')); // large , ocean-thumb-l
            $link = esc_url(get_permalink($id));
            $excerpt = get_the_excerpt($id);
            $collectionLinks = Utils::getTermsAsLinksForTaxonomy($id, $this->portfolioRepository->getTaxonomyName());
            $collections = strip_tags($collectionLinks);

            $projectData[] = [
                'id' => $id,
                'title' => $title,
                'featuredImgUrl' => $featuredImgUrl,
                'link' => $link,
                'excerpt' => $excerpt,
                'collections' => $collections,
            ];

            if ($countProjects < 3) {
                $checked = ($key === 0) ? 'checked' : '';
            } else {
                $checked = ($key === 1) ? 'checked' : '';
            }

            $html .= sprintf(
                '<input type="radio" name="slider" id="slide%d" %s>',
                $key,
                $checked
            );
        }
        wp_reset_postdata();

        $items = 0;

        $html .= '<div class="slides-track">';
        foreach ($projectData as $key => $project) {
            $html .= sprintf('<div class="slide s%s">', $key);
            $html .= '<div class="img-container">';
            $html .= sprintf('<img src="%1$s" alt="%2$s" title="%2$s">', $project['featuredImgUrl'], $project['title']);
            $html .= '<div class="slide-overlay"></div>';
            $html .= '<div class="slide-content">';
            $html .= '<h2>'. $project['title'] .'</h2>';

            if (!empty($project['excerpt'])) {
                $html .= '<p>'. $project['excerpt'] .'</p>';
            }

            $html .=  '</div>'; // /.slide-content
            $html .= '</div>'; // /.img-container
            $html .= '</div>'; // /.slide
            $items++;
        }
        $html .= '</div>'; // /.slides-track

        /* --- ЛОГИКАТА НА СЛАЙДЪРА (ИЗЧИСЛЕНИЯ) ---
           Ширина на слайд = 60vw
           Маржин = 2.5vw отляво + 2.5vw отдясно = 5vw общо разстояние.
           Обща ширина на един "блок" = 65vw.
           За да центрираме слайд с ширина 60vw на екран 100vw, ни трябва отстъп отляво: (100 - 60) / 2 = 20vw.
        */

        /* 1. Движение на трака (translateX) за центриране на активния слайд */

        $offset = 20;
        $blockWidth = 65;
        /* Слайд 1 активен: Трябва да започне на 20vw отляво */
//        .hero-slider #slide0:checked ~ .slides-track { transform: translateX(20vw); }

        /* Слайд 2 активен: Започва на 65vw. Трябва да го върнем до 20vw. (20 - 65 = -45vw) */
//        .hero-slider #slide1:checked ~ .slides-track { transform: translateX(-45vw); }

        /* Слайд 3 активен: Започва на 130vw. (20 - 130 = -110vw) */
//        .hero-slider #slide2:checked ~ .slides-track { transform: translateX(-110vw); }

        /* Слайд 4 активен: Започва на 195vw. (20 - 195 = -175vw) */
//        .hero-slider #slide3:checked ~ .slides-track { transform: translateX(-175vw); }

        $html .= '<div class="slider-nav">';
        for ($i = 0; $i < $items; $i++) {
            $html .= sprintf('<label for="slide%d"></label>', $i);

            $styles .= sprintf('.hero-slider #slide%d:checked ~ .slides-track { transform: translateX(%dvw); }', $i, $offset).PHP_EOL;
            $offset -= $blockWidth;

            $styles .= sprintf('.hero-slider #slide%1$d:checked ~ .slides-track .s%1$d .slide-content { opacity: 1; transform: translate(-50%%, -50%%); }', $i).PHP_EOL;
            $styles .= sprintf('.hero-slider #slide%1$d:checked ~ .slides-track .s%1$d .slide-content h2 { color: var(--color-text-light); }', $i).PHP_EOL;
            $styles .= sprintf('.hero-slider #slide%1$d:checked ~ .slides-track .s%1$d { transform: scale(1); z-index: 90; }', $i).PHP_EOL;
            $styles .= sprintf('.hero-slider #slide%1$d:checked ~ .slides-track .s%1$d .slide-overlay { opacity: 0.4; }', $i).PHP_EOL;
            $styles .= sprintf('.hero-slider #slide%1$d:checked ~ .slider-nav label[for="slide%1$d"] { background: var(--color-accent); transform: scale(1.2); }', $i).PHP_EOL;
        }
        $html .= '</div>'; // /.slider-nav

        $html .= '</section>'; // /#hero
        $styles .= '</style>';

        return $styles.$html;
    }
}