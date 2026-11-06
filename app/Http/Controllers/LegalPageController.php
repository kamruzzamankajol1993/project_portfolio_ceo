<?php

namespace App\Http\Controllers;

use App\Models\ExtraPage;

class LegalPageController extends Controller
{
    public function terms()
    {
        return $this->show('terms-and-conditions');
    }

    public function privacy()
    {
        return $this->show('privacy-policy');
    }

    private function show(string $slug)
    {
        abort_unless(in_array($slug, ['terms-and-conditions', 'privacy-policy'], true), 404);
        $extraPage = ExtraPage::query()->where('slug', $slug)->firstOrFail();
        abort_unless($extraPage->is_published || (auth()->check() && auth()->user()->can('extra-pages.manage')), 404);
        return view('pages.show', compact('extraPage'));
    }
}
