@php
    $amenityItems = is_array($amenities ?? null) ? array_values($amenities) : [];
    $amenityLimit = (int) ($amenityLimit ?? 6);
    $visibleAmenities = array_slice($amenityItems, 0, max(1, $amenityLimit));
    $hiddenAmenityCount = max(0, count($amenityItems) - count($visibleAmenities));
    $amenityClass = trim('bus-v2-trip-amenity-rail ' . ($amenityClass ?? ''));
@endphp

@if (!empty($visibleAmenities))
    <div class="{{ $amenityClass }}" aria-label="Trip amenities">
        @foreach ($visibleAmenities as $amenity)
            @php
                $amenityLabel = trim((string) ($amenity['label'] ?? $amenity['key'] ?? ''));
                $amenityIcon = trim((string) ($amenity['icon'] ?? 'fas fa-check'));
                $amenityKey = trim((string) ($amenity['key'] ?? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $amenityLabel))));
                $amenityKey = preg_replace('/[^a-z0-9_-]+/i', '', $amenityKey) ?: 'amenity';
            @endphp
            @if ($amenityLabel !== '')
                <span class="bus-v2-amenity-chip bus-v2-amenity-chip--{{ $amenityKey }}" title="{{ $amenityLabel }}">
                    <i class="{{ $amenityIcon }}" aria-hidden="true"></i>
                    <span>{{ $amenityLabel }}</span>
                </span>
            @endif
        @endforeach

        @if ($hiddenAmenityCount > 0)
            <span class="bus-v2-amenity-chip bus-v2-amenity-chip--more" title="{{ $hiddenAmenityCount }} more amenities">
                <i class="fas fa-plus" aria-hidden="true"></i>
                <span>+{{ $hiddenAmenityCount }} more</span>
            </span>
        @endif
    </div>
@endif
