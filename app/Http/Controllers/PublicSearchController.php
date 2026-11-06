<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        $term = trim($validated['q']);
        $like = '%' . addcslashes($term, '%_\\') . '%';

        $profiles = Profile::query()
            ->published()
            ->where(function ($query) use ($like) {
                $query->where('slug', 'like', $like)
                    ->orWhereHas('publishedVersion.personalDetails', function ($details) use ($like) {
                        $details->where(function ($match) use ($like) {
                            $match->where('full_name', 'like', $like)
                                ->orWhere('headline', 'like', $like)
                                ->orWhere('professional_title', 'like', $like)
                                ->orWhere('current_position', 'like', $like)
                                ->orWhere('company_organisation', 'like', $like)
                                ->orWhere('short_bio', 'like', $like)
                                ->orWhereHas('industry', fn ($industry) => $industry->where('name', 'like', $like));
                        });
                    })
                    ->orWhereHas('publishedVersion.companyDetails', function ($details) use ($like) {
                        $details->where(function ($match) use ($like) {
                            $match->where('company_name', 'like', $like)
                                ->orWhere('tagline', 'like', $like)
                                ->orWhere('short_description', 'like', $like)
                                ->orWhere('overview', 'like', $like)
                                ->orWhere('business_type', 'like', $like)
                                ->orWhereHas('industry', fn ($industry) => $industry->where('name', 'like', $like));
                        });
                    });
            })
            ->with([
                'publishedVersion.personalDetails.industry',
                'publishedVersion.companyDetails.industry',
            ])
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        $results = $profiles->map(function (Profile $profile) {
            $version = $profile->publishedVersion;
            $isPersonal = $profile->profile_type === Profile::TYPE_PERSONAL;
            $details = $isPersonal ? $version?->personalDetails : $version?->companyDetails;

            if (!$details) {
                return null;
            }

            $industry = $details->industry?->name;

            return [
                'type' => $isPersonal ? 'Entrepreneur' : 'Company',
                'title' => $isPersonal ? $details->full_name : $details->company_name,
                'subtitle' => $isPersonal
                    ? ($details->professional_title ?: $details->headline ?: $details->current_position ?: $industry)
                    : ($details->tagline ?: $industry ?: $details->business_type),
                'description' => $isPersonal ? $details->short_bio : $details->short_description,
                'url' => $profile->publicUrl(),
            ];
        })->filter()->values();

        return response()->json([
            'query' => $term,
            'count' => $results->count(),
            'results' => $results,
        ]);
    }
}
