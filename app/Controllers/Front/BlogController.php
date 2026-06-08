<?php
namespace App\Controllers\Front;

use App\Core\Controller;
use App\Helpers\HtmlSanitizer;
use PDO;

class BlogController extends Controller {
    public function index() {
        $posts = self::getPosts();
        $categories = $this->getCategories();

        $featured = null;
        foreach ($posts as $post) {
            if (!empty($post['featured'])) {
                $featured = $post;
                break;
            }
        }

        $gridPosts = array_values(array_filter($posts, function ($post) {
            return empty($post['featured']);
        }));

        $this->view('front/blog', [
            'posts' => $posts,
            'gridPosts' => $gridPosts,
            'featured' => $featured,
            'categories' => $categories,
        ]);
    }

    public function show($slug) {
        $posts = self::getPosts();
        $post = null;

        foreach ($posts as $item) {
            if ($item['slug'] === $slug) {
                $post = $item;
                break;
            }
        }

        if (!$post) {
            http_response_code(404);
            echo '<h1>404 Not Found</h1><p>Article not found. <a href="' . BASE_URL . '/blogs">Back to blog</a></p>';
            exit;
        }

        $post['body'] = $this->getArticleBody($post);
        $related = $this->getRelatedPosts($posts, $post, 3);

        $this->view('front/blog-detail', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    private function getRelatedPosts(array $posts, array $current, int $limit): array {
        $related = [];

        foreach ($posts as $post) {
            if ($post['slug'] === $current['slug']) {
                continue;
            }
            if ($post['categoryKey'] === $current['categoryKey']) {
                $related[] = $post;
            }
        }

        if (count($related) < $limit) {
            foreach ($posts as $post) {
                if ($post['slug'] === $current['slug']) {
                    continue;
                }
                if ($post['categoryKey'] !== $current['categoryKey']) {
                    $related[] = $post;
                }
                if (count($related) >= $limit) {
                    break;
                }
            }
        }

        return array_slice($related, 0, $limit);
    }

    private function getArticleBody(array $post): array {
        $bodies = [
            'read-uss-auction-sheet' => [
                ['type' => 'p', 'text' => 'Every vehicle listed at USS Tokyo, TAA, and other major Japan auction houses ships with a standardized inspection sheet. For importers, that sheet is your first filter — long before you calculate freight or customs.'],
                ['type' => 'h2', 'text' => 'Understanding the grade score'],
                ['type' => 'p', 'text' => 'The overall grade (typically 3.5 to 5 for passenger cars) reflects cosmetic and structural condition at auction time. A 4.5 grade usually means light wear; below 4.0 warrants closer review of panel notes and underbody photos.'],
                ['type' => 'ul', 'items' => [
                    'Grade 5 / 4.5 — Excellent to good cosmetic condition',
                    'Grade 4 / 3.5 — Visible wear, check repair history',
                    'Grade R — Repaired vehicle; verify work quality',
                    'Grade *** — Accident history flagged on sheet',
                ]],
                ['type' => 'tip', 'title' => 'Pro tip for importers', 'text' => 'Always cross-check the auction sheet with USS live photos and Eisen\'s pre-export inspection report before confirming a bid.'],
                ['type' => 'h2', 'text' => 'Inspector notes and chassis codes'],
                ['type' => 'p', 'text' => 'Handwritten or stamped notes highlight rust, panel replacement, interior stains, and odometer verification. Match the chassis number on the sheet to export documents — mismatches delay clearance at destination ports.'],
                ['type' => 'p', 'text' => 'When you shortlist stock through Eisen, our team translates key sheet fields and flags vehicles that fit your market — saving hours of manual review across hundreds of weekly listings.'],
            ],
        ];

        if (isset($bodies[$post['slug']])) {
            return $bodies[$post['slug']];
        }

        if (!empty($post['content'])) {
            if (HtmlSanitizer::containsHtml($post['content'])) {
                return [
                    ['type' => 'raw_html', 'html' => HtmlSanitizer::sanitizeBlogHtml($post['content'])],
                ];
            }
            $paragraphs = explode("\n\n", str_replace("\r", "", $post['content']));
            $blocks = [];
            foreach ($paragraphs as $p) {
                $p = trim($p);
                if (empty($p)) continue;
                if (str_starts_with($p, '## ')) {
                    $blocks[] = ['type' => 'h2', 'text' => substr($p, 3)];
                } elseif (str_starts_with($p, '# ')) {
                    $blocks[] = ['type' => 'h2', 'text' => substr($p, 2)];
                } else {
                    $blocks[] = ['type' => 'p', 'text' => $p];
                }
            }
            return $blocks;
        }

        return [
            ['type' => 'p', 'text' => $post['excerpt']],
            ['type' => 'h2', 'text' => 'Key takeaways for importers'],
            ['type' => 'p', 'text' => 'Japan auction sourcing rewards preparation. Review condition reports, confirm export documentation early, and align your bid with landed cost — not just hammer price at the lane.'],
            ['type' => 'ul', 'items' => [
                'Verify auction grade and inspector notes before bidding',
                'Plan shipping method and port timelines in advance',
                'Factor currency conversion and local compliance into budget',
                'Work with a partner who documents every handover step',
            ]],
            ['type' => 'tip', 'title' => 'Need help sourcing?', 'text' => 'Eisen supports dealers and private buyers from auction selection through export logistics. Browse current inventory or contact our team for a tailored sourcing plan.'],
            ['type' => 'p', 'text' => 'This article is part of our Insights series — practical guidance for anyone importing vehicles from Japan\'s wholesale auction network.'],
        ];
    }

    private function getCategories(): array {
        return [
            ['key' => 'all', 'label' => 'All topics'],
            ['key' => 'auctions', 'label' => 'Japan Auctions'],
            ['key' => 'export', 'label' => 'Import & Export'],
            ['key' => 'guides', 'label' => 'Buying Guides'],
            ['key' => 'market', 'label' => 'Market & Pricing'],
            ['key' => 'spotlights', 'label' => 'Vehicle Spotlights'],
            ['key' => 'company', 'label' => 'Company'],
        ];
    }

    public static function getPosts(): array {
        try {
            $db = \App\Core\Database::getConnection();
            $stmt = $db->query("SELECT * FROM blog_posts ORDER BY published_date DESC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $posts = [];
            foreach ($rows as $index => $row) {
                $posts[] = [
                    'slug' => $row['slug'],
                    'title' => $row['title'],
                    'category' => $row['category'],
                    'categoryKey' => $row['category_key'],
                    'date' => $row['published_date'],
                    'dateLabel' => date('F j, Y', strtotime($row['published_date'])),
                    'readMin' => (int)$row['read_min'],
                    'excerpt' => $row['excerpt'],
                    'image' => $row['image'],
                    'featured' => ($index === 0),
                    'author' => $row['author'],
                    'content' => $row['content']
                ];
            }
            return $posts;
        } catch (\Exception $e) {
            return [];
        }
    }
}
