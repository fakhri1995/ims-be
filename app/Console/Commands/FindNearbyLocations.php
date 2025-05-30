<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LongLatList;

class FindNearbyLocations extends Command
{
    protected $signature = 'geo:find-nearby 
        {parent_id : ID of the parent record} 
        {range=1000 : Search radius in meters} 
        {--mark : Mark, preserve raw values, & normalize parent to best child}
        {--strategy=centroid : Normalization strategy: centroid, best_accuracy, most_recent}';

    protected $description = 'Find and (optionally) normalize a parent record with its nearby points.';

    public function handle()
    {
        $parentId    = (int) $this->argument('parent_id');
        $rangeMeters = (float) $this->argument('range');
        $mark        = $this->option('mark');
        $strategy    = $this->option('strategy');

        $parent = LongLatList::find($parentId);
        if (! $parent) {
            return $this->error(json_encode([
                'success' => false,
                'message' => "Parent record ID {$parentId} not found."
            ], JSON_PRETTY_PRINT));
        }

        // Store original parent coordinates for distance calculations
        $originalParentLat = $parent->latitude;
        $originalParentLon = $parent->longitude;

        // Compute degree deltas for bounding box
        $deltaLat = $rangeMeters / 111320.0;
        $deltaLon = $rangeMeters / (111320.0 * cos(deg2rad($originalParentLat)));

        // Find unprocessed nearby children
        $children = LongLatList::whereNull('parent_id')
            ->where('is_nearby_processed', false)
            ->where('id', '<>', $parentId)
            ->whereBetween('latitude',  [$originalParentLat - $deltaLat,  $originalParentLat + $deltaLat])
            ->whereBetween('longitude', [$originalParentLon - $deltaLon, $originalParentLon + $deltaLon])
            ->whereRaw(
                "(6371000 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) <= ?",
                [$originalParentLat, $originalParentLon, $originalParentLat, $rangeMeters]
            )
            ->get();

        // Prepare normalization data based on strategy
        $normalized = null;
        if ($children->isNotEmpty()) {
            $normalized = $this->calculateNormalizedLocation($parent, $children, $strategy);
        }

        // Apply normalization if requested
        if ($mark && $normalized) {
            // Preserve raw values only if not already set
            if (is_null($parent->raw_latitude)) {
                $parent->raw_latitude     = $parent->latitude;
                $parent->raw_longitude    = $parent->longitude;
                $parent->raw_geo_location = $parent->geo_location;
            }

            // Apply normalized values
            $parent->latitude     = $normalized['latitude'];
            $parent->longitude    = $normalized['longitude'];
            $parent->geo_location = $normalized['geo_location'];
            $parent->parent_id    = $parent->id; // Self-reference to mark as parent
            $parent->is_nearby_processed = true;
            $parent->save();

            // Update children
            foreach ($children as $child) {
                $child->parent_id           = $parent->id; // Reference to parent
                $child->is_nearby_processed = true;
                $child->save();
            }
        }

        // Calculate distances using original parent coordinates
        $childrenWithDistance = $children->map(function ($child) use ($originalParentLat, $originalParentLon) {
            $distance = $this->calculateDistance(
                $originalParentLat, 
                $originalParentLon, 
                $child->latitude, 
                $child->longitude
            );
            
            return [
                'child_id'    => $child->id,
                'latitude'    => $child->latitude,
                'longitude'   => $child->longitude,
                'distance_m'  => round($distance, 2),
                'geo_location'=> $child->geo_location,
                'attempts'    => $child->attempts,
            ];
        })->sortBy('distance_m')->values()->all();

        // Output JSON
        $output = [
            'success'    => true,
            'parent_id'  => $parentId,
            'strategy'   => $strategy,
            'original'   => [
                'latitude'    => $originalParentLat,
                'longitude'   => $originalParentLon,
                'geo_location'=> $parent->raw_geo_location ?? $parent->geo_location,
            ],
            'normalized' => $normalized,
            'children_count' => $children->count(),
            'children'   => $childrenWithDistance,
        ];

        $this->info(json_encode($output, JSON_PRETTY_PRINT));
    }

    /**
     * Calculate normalized location based on strategy
     */
    private function calculateNormalizedLocation($parent, $children, $strategy)
    {
        $allPoints = collect([$parent])->concat($children);

        switch ($strategy) {
            case 'centroid':
                return [
                    'latitude'    => $allPoints->avg('latitude'),
                    'longitude'   => $allPoints->avg('longitude'),
                    'geo_location'=> $this->mergeGeoLocation($allPoints),
                ];

            case 'best_accuracy':
                // Point with fewest attempts (likely most accurate)
                $bestPoint = $allPoints->sortBy('attempts')->first();
                return [
                    'latitude'    => $bestPoint->latitude,
                    'longitude'   => $bestPoint->longitude,
                    'geo_location'=> $bestPoint->geo_location,
                ];

            case 'most_recent':
                // Point with highest ID (assuming auto-increment = most recent)
                $mostRecent = $allPoints->sortByDesc('id')->first();
                return [
                    'latitude'    => $mostRecent->latitude,
                    'longitude'   => $mostRecent->longitude,
                    'geo_location'=> $mostRecent->geo_location,
                ];

            default:
                // Default to centroid
                return [
                    'latitude'    => $allPoints->avg('latitude'),
                    'longitude'   => $allPoints->avg('longitude'),
                    'geo_location'=> $this->mergeGeoLocation($allPoints),
                ];
        }
    }

    /**
     * Merge geo_location data from multiple points
     */
    private function mergeGeoLocation($points)
    {
        $geoData = [];
        
        foreach ($points as $point) {
            if ($point->geo_location) {
                $data = is_string($point->geo_location) 
                    ? json_decode($point->geo_location, true) 
                    : (array) $point->geo_location;
                
                $geoData = array_merge($geoData, $data);
            }
        }

        return !empty($geoData) ? (object) $geoData : null;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        return 6371000 * acos(
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            cos(deg2rad($lon2) - deg2rad($lon1)) +
            sin(deg2rad($lat1)) * sin(deg2rad($lat2))
        );
    }
}