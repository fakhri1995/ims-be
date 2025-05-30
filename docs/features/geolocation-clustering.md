# 🌍 Geolocation Clustering System

A Laravel command-line tool for clustering nearby GPS coordinates and normalizing location data. Perfect for deduplicating location records from multiple GPS readings of the same area.

## 📋 Table of Contents

- [Overview](#-overview)
- [Installation](#-installation)
- [Database Schema](#-database-schema)
- [Usage](#-usage)
- [Normalization Strategies](#-normalization-strategies)
- [Examples](#-examples)
- [Database Management](#-database-management)
- [Troubleshooting](#-troubleshooting)

## 🎯 Overview

This system identifies and clusters GPS coordinates that are geographically close to each other, then normalizes them to a single representative location while preserving the original data.

### Key Features

- ✅ **Distance-based clustering** using configurable radius
- ✅ **Multiple normalization strategies** (centroid, best accuracy, most recent)
- ✅ **Raw data preservation** before normalization
- ✅ **Self-referential parent-child relationships**
- ✅ **Haversine distance calculations** for accuracy
- ✅ **Bounding box optimization** for performance

### Use Cases

- Deduplicating GPS readings from the same location
- Cleaning location datasets with coordinate variations
- Grouping nearby points of interest
- Normalizing user-generated location data

## 🛠 Installation

### 1. Database Migration

```bash
# Generate migration file
php artisan make:migration add_raw_and_parent_fields_to_long_lat_lists_table --table=long_lat_lists

# Apply migration
php artisan migrate
```

### 2. Command Registration

Add to `app/Console/Kernel.php`:

```php
use App\Console\Commands\FindNearbyLocations;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        FindNearbyLocations::class,
    ];
}
```

### 3. Autoloader Update

```bash
composer dump-autoload
```

## 🗄 Database Schema

The system uses these additional fields in your `long_lat_lists` table:

| Field | Type | Purpose |
|-------|------|---------|
| `parent_id` | `unsignedBigInteger` | Self-referential foreign key for clustering |
| `is_nearby_processed` | `boolean` | Processing status flag |
| `raw_latitude` | `decimal(7,4)` | Original latitude before normalization |
| `raw_longitude` | `decimal(7,4)` | Original longitude before normalization |
| `raw_geo_location` | `text` | Original location data before normalization |

### Record States

| State | `parent_id` | `is_nearby_processed` | Description |
|-------|-------------|----------------------|-------------|
| **Unprocessed** | `NULL` | `false` | Available for clustering |
| **Parent** | `id` (self-reference) | `true` | Cluster center/representative |
| **Child** | `parent_id` | `true` | Belongs to a cluster |

## 🚀 Usage

### Basic Syntax

```bash
php artisan geo:find-nearby {parent_id} {range=1000} {--mark} {--strategy=centroid}
```

### Parameters

- **`parent_id`** (required): ID of the record to use as cluster center
- **`range`** (optional): Search radius in meters (default: 1000)
- **`--mark`** (optional): Apply clustering changes to database
- **`--strategy`** (optional): Normalization strategy (default: centroid)

### Search Only (No Changes)

```bash
# Find nearby records within 1000m (default)
php artisan geo:find-nearby 112

# Find within 5000m radius
php artisan geo:find-nearby 112 5000

# Find within 8000m radius
php artisan geo:find-nearby 112 8000
```

### Apply Clustering

```bash
# Apply clustering with default centroid strategy
php artisan geo:find-nearby 112 8000 --mark

# Apply with specific strategy
php artisan geo:find-nearby 112 8000 --mark --strategy=best_accuracy
```

## 🎯 Normalization Strategies

### 1. Centroid (Default)

Uses the average coordinates of all clustered points.

```bash
php artisan geo:find-nearby 112 5000 --mark --strategy=centroid
```

**Best for:** Balanced representation of the cluster area

### 2. Best Accuracy

Uses the point with the fewest geocoding attempts (most reliable).

```bash
php artisan geo:find-nearby 112 5000 --mark --strategy=best_accuracy
```

**Best for:** When data quality varies and you want the most accurate reading

### 3. Most Recent

Uses the point with the highest ID (most recently added).

```bash
php artisan geo:find-nearby 112 5000 --mark --strategy=most_recent
```

**Best for:** When newer data is more reliable or represents current conditions

## 📖 Examples

### Example 1: Basic Clustering

```bash
# 1. Search for nearby records
php artisan geo:find-nearby 112 8000
```

**Output:**
```json
{
    "success": true,
    "parent_id": 112,
    "strategy": "centroid",
    "children_count": 8,
    "normalized": null
}
```

```bash
# 2. Apply clustering
php artisan geo:find-nearby 112 8000 --mark
```

**Result:** Parent record normalized to cluster centroid, 8 children assigned.

### Example 2: Strategy Comparison

```bash
# Test different strategies on the same data
php artisan geo:find-nearby 112 5000 --strategy=centroid
php artisan geo:find-nearby 112 5000 --strategy=best_accuracy  
php artisan geo:find-nearby 112 5000 --strategy=most_recent
```

### Example 3: Progressive Clustering

```bash
# Start with small radius
php artisan geo:find-nearby 112 3000 --mark

# Expand to capture more distant points
php artisan geo:find-nearby 112 6000 --mark

# Final expansion
php artisan geo:find-nearby 112 10000 --mark
```

## 🔧 Database Management

### Reset All Records

```sql
-- Reset everything to unprocessed state
UPDATE long_lat_lists 
SET parent_id = NULL, 
    is_nearby_processed = false;

-- Restore original coordinates
UPDATE long_lat_lists 
SET latitude = raw_latitude,
    longitude = raw_longitude,
    geo_location = raw_geo_location
WHERE raw_latitude IS NOT NULL;
```

### Reset Specific Cluster

```sql
-- Reset only records related to parent 112
UPDATE long_lat_lists 
SET parent_id = NULL, 
    is_nearby_processed = false 
WHERE parent_id = 112;

-- Restore parent 112's coordinates
UPDATE long_lat_lists 
SET latitude = raw_latitude,
    longitude = raw_longitude,
    geo_location = raw_geo_location,
    parent_id = NULL,
    is_nearby_processed = false
WHERE id = 112 AND raw_latitude IS NOT NULL;
```

### Monitoring Queries

```sql
-- Check cluster states
SELECT 
  COUNT(CASE WHEN parent_id IS NULL AND is_nearby_processed = false THEN 1 END) as unprocessed,
  COUNT(CASE WHEN id = parent_id THEN 1 END) as parents,
  COUNT(CASE WHEN parent_id IS NOT NULL AND id != parent_id THEN 1 END) as children
FROM long_lat_lists;

-- View specific cluster
SELECT id, parent_id, is_nearby_processed, latitude, longitude, attempts
FROM long_lat_lists 
WHERE parent_id = 112 OR id = 112
ORDER BY parent_id, id;

-- Find all cluster parents
SELECT id, latitude, longitude, 
       (SELECT COUNT(*) FROM long_lat_lists l2 WHERE l2.parent_id = l1.id AND l2.id != l1.id) as children_count
FROM long_lat_lists l1
WHERE id = parent_id;
```

## 🔍 Troubleshooting

### Common Issues

#### "No children found" with expected nearby records

**Cause:** Records already processed or radius too small

**Solution:**
```bash
# Check if records are already assigned
SELECT COUNT(*) FROM long_lat_lists WHERE parent_id = 112;

# Try larger radius
php artisan geo:find-nearby 112 10000

# Reset if needed
UPDATE long_lat_lists SET parent_id = NULL, is_nearby_processed = false;
```

#### Decreasing results on repeated runs

**Cause:** Normal behavior - records get assigned and excluded from future searches

**Solution:** This is expected. Use reset commands to test repeatedly.

#### Distance calculations seem wrong

**Cause:** Coordinate precision or formula implementation

**Solution:**
```sql
-- Verify distances manually
SELECT id, latitude, longitude,
       (6371000 * acos(
           cos(radians(-6.4564)) * cos(radians(latitude)) * 
           cos(radians(longitude) - radians(106.7311)) + 
           sin(radians(-6.4564)) * sin(radians(latitude))
       )) as distance_m
FROM long_lat_lists 
WHERE id != 112
ORDER BY distance_m LIMIT 10;
```

### Performance Tips

- Use appropriate radius sizes (1000-10000m typically)
- The system uses bounding box pre-filtering for efficiency
- Composite indexes on `(longitude, latitude)` improve query performance
- Consider processing in batches for large datasets

## 📊 Output Format

The command returns structured JSON with:

```json
{
    "success": true,
    "parent_id": 112,
    "strategy": "centroid",
    "original": {
        "latitude": "-6.4564",
        "longitude": "106.7311",
        "geo_location": {...}
    },
    "normalized": {
        "latitude": -6.485266666666666,
        "longitude": 106.77555555555554,
        "geo_location": {...}
    },
    "children_count": 8,
    "children": [
        {
            "child_id": 111,
            "latitude": "-6.4872",
            "longitude": "106.7710", 
            "distance_m": 5582.41,
            "geo_location": {...},
            "attempts": 1
        }
    ]
}
```

## 🤝 Contributing

When extending this system:

1. Maintain backward compatibility with existing data
2. Add comprehensive tests for new strategies
3. Update this documentation
4. Consider performance impact of new features

## 📄 License

This geolocation clustering system is part of your Laravel application. Use according to your project's license terms.