@foreach($profiles as $profile)
    @php
        $isPersonal = $profile->profile_type === \App\Models\Profile::TYPE_PERSONAL;

        $details = $isPersonal
            ? $profile->publishedVersion?->personalDetails
            : $profile->publishedVersion?->companyDetails;

        $name = $isPersonal
            ? ($details?->full_name ?? $profile->slug)
            : ($details?->company_name ?? $profile->slug);

        $subtitle = $isPersonal
            ? ($details?->professional_title ?: ($details?->headline ?: $details?->current_position))
            : ($details?->tagline ?: $details?->business_type);

        $description = $isPersonal
            ? $details?->short_bio
            : $details?->short_description;

        $imageRole = $isPersonal ? 'portrait' : 'logo';
        $image = $profile->publishedVersion?->mediaLinks
            ?->firstWhere('role', $imageRole)
            ?->media?->url;

        $image = $image ?: (
            $isPersonal
                ? asset('public/website/assets/nazim-founder.jpg')
                : asset('public/website/assets/company-logo.jpg')
        );

        $location = $isPersonal
            ? $details?->location
            : $details?->headquarters;
    @endphp

    <article class="profile-card">
        <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">

        <div class="profile-card-body">
            <h3>
                <a href="{{ route('site.profile.show', $profile) }}">{{ $name }}</a>

                @if($profile->is_verified)
                    <i class="bi bi-patch-check-fill verified" title="Verified"></i>
                @endif
            </h3>

            <p class="role">
                {{ $subtitle ?: ($isPersonal ? 'Professional profile' : 'Company profile') }}

                @if($location)
                    <br>{{ $location }}
                @endif
            </p>

            @if($description)
                <p class="bio">{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 150) }}</p>
            @endif

            <a href="{{ route('site.profile.show', $profile) }}">
                View Profile <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </article>
@endforeach
