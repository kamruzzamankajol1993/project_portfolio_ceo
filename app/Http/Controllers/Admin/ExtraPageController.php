<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraPageController extends Controller
{
    private const DEFAULT_PAGES = [
        'about-us' => [
            'page_key' => 'about',
            'title' => 'About Us',
            'content' => '<p>EntrepreneurWiki is an independent platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make reliable, useful and clearly presented information easier to discover. We bring together professional stories, company information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>',
            'is_published' => true,
        ],
        'contact-us' => [
            'page_key' => 'contact',
            'title' => 'Contact Us',
            'content' => '<p>We welcome questions, profile suggestions, corrections and partnership enquiries.</p><p>Use the contact details shown on this page to reach the EntrepreneurWiki team. For profile submissions, please create an account and follow the review process.</p>',
            'is_published' => true,
        ],
        'terms-and-conditions' => [
            'page_key' => 'terms',
            'title' => 'Terms & Conditions',
            'content' => '',
            'is_published' => false,
        ],
        'privacy-policy' => [
            'page_key' => 'privacy',
            'title' => 'Privacy Policy',
            'content' => '',
            'is_published' => false,
        ],
    ];

    public function index(Request $request)
    {
        $pages = collect(self::DEFAULT_PAGES)->map(function (array $page, string $slug) {
            return ExtraPage::firstOrCreate(
                ['page_key' => $page['page_key']],
                [
                    'title' => $page['title'],
                    'slug' => $slug,
                    'content' => $page['content'],
                    'meta_title' => $page['title'],
                    'meta_robots' => 'index,follow',
                    'is_published' => $page['is_published'],
                ],
            );
        });

        $activeSlug = $request->input('tab', 'about-us');
        if (!$pages->contains('slug', $activeSlug)) {
            $activeSlug = 'terms-and-conditions';
        }
        $editing = $pages->firstWhere('slug', $activeSlug);

        return view('admin.extra-pages.index', compact('pages', 'activeSlug', 'editing'));
    }

    public function update(Request $request, ExtraPage $extraPage)
    {
        abort_unless(in_array($extraPage->slug, array_keys(self::DEFAULT_PAGES), true), 404);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:1000000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:2000',
            'meta_robots' => ['required', Rule::in(['index,follow', 'noindex,follow', 'index,nofollow', 'noindex,nofollow'])],
            'canonical_url' => 'nullable|url|max:255',
            'is_published' => 'nullable|boolean',
        ]);

        $data['content'] = $this->cleanHtml($data['content'] ?? '');
        $data['is_published'] = $request->boolean('is_published');

        if ($data['is_published'] && trim(strip_tags($data['content'])) === '') {
            return back()->withErrors(['content' => 'Add page content before publishing.'])->withInput();
        }

        $extraPage->update($data);

        return redirect()->route('admin.extra-pages.index', ['tab' => $extraPage->slug])
            ->with('success', $extraPage->title . ' updated.');
    }

    private function cleanHtml(string $html): string
    {
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|svg|math)[^>]*>.*?</\\1>#is', '', $html) ?? '';
        $html = preg_replace('#</?(script|style|iframe|object|embed|form|input|button|svg|math)[^>]*>#i', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><h1><h2><h3><h4><h5><h6><strong><b><em><i><u><s><ul><ol><li><blockquote><a><hr><table><thead><tbody><tr><th><td>');

        return preg_replace_callback('/<([a-z][a-z0-9]*)([^>]*)>/i', function (array $match): string {
            $tag = strtolower($match[1]);
            if ($tag !== 'a') {
                return '<' . $tag . '>';
            }
            if (!preg_match("~\\bhref\\s*=\\s*(?:\"([^\"]*)\"|'([^']*)'|([^\\s>]+))~i", $match[2], $href)) {
                return '<a>';
            }
            $url = $href[1] ?? ($href[2] ?? ($href[3] ?? ''));
            if (!preg_match('~^(?:https?://|mailto:|tel:|/|#)~i', trim($url))) {
                return '<a>';
            }

            return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" rel="nofollow noopener noreferrer">';
        }, $html) ?? $html;
    }
}
