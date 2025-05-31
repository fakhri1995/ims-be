# 🌍 Geolocation Clustering & Normalization System

A Laravel command-line tool for clustering nearby GPS coordinates, normalizing location data, and optimizing geocoding operations. Perfect for deduplicating location records and reducing API calls to geocoding services.

## 📋 Table of Contents

- [Overview](#-overview)
- [Installation](#-installation)
- [Database Schema](#-database-schema)
- [Usage](#-usage)
- [Normalization Strategies](#-normalization-strategies)
- [Geo-Location Copying](#-geo-location-copying)
- [Examples](#-examples)
- [Database Management](#-database-management)
- [Integration with SearchGeoLocation](#-integration-with-searchgeolocation)
- [Troubleshooting](#-troubleshooting)

## 🎯 Overview

This system identifies and clusters GPS coordinates that are geographically close to each other, then normalizes them to a single representative location while preserving the original data. Additionally, it can copy geocoding data between related records to reduce API calls.

### Key Features

- ✅ **Distance-based clustering** using configurable radius
- ✅ **Multiple normalization strategies** (centroid, best accuracy, most recent)
- ✅ **Raw data preservation** before normalization
- ✅ **Geo-location data copying** to reduce API calls
- ✅ **Smart filtering** for null geocoding data
- ✅ **Self-referential parent-child relationships**
- ✅ **Haversine distance calculations** for accuracy
- ✅ **Bounding box optimization** for performance

### Use Cases

- Deduplicating GPS readings from the same location
- Reducing geocoding API calls by copying existing data
- Cleaning location datasets with coordinate variations
- Grouping nearby points of interest
- Normalizing user-generated location data
- Optimizing SearchGeoLocation command performance

## 🛠 Installation

### 1. Database Migrations

```bash
# Generate first migration file
php artisan make:migration add_raw_and_parent_fields_to_long_lat_lists_table --table=long_lat_lists

# Generate second migration for geo copying features
php artisan make:migration add_raw_attempts_to_long_lat_lists_table --table=long_lat_lists

# Apply migrations
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
| `raw_attempts` | `integer` | Original attempts count before geo copying |

### Record States

| State | `parent_id` | `is_nearby_processed` | `attempts` | Description |
|-------|-------------|----------------------|------------|-------------|
| **Unprocessed** | `NULL` | `false` | varies | Available for clustering |
| **Parent** | `id` (self-reference) | `true` | varies | Cluster center/representative |
| **Child** | `parent_id` | `true` | varies | Belongs to a cluster |
| **Geo-copied** | varies | varies | `100` | Received geo_location from parent |

## 🚀 Usage

### Basic Syntax

```bash
php artisan geo:find-nearby {parent_id} {range=1000} {--mark} {--strategy=centroid} {--search-null-geo} {--copy-geo}
```

### Parameters

- **`parent_id`** (required): ID of the record to use as cluster center
- **`range`** (optional): Search radius in meters (default: 1000)
- **`--mark`** (optional): Apply clustering changes to database
- **`--strategy`** (optional): Normalization strategy (default: centroid)
- **`--search-null-geo`** (optional): Only find children with null attempts AND null geo_location
- **`--copy-geo`** (optional): Copy geo_location from parent to children with null geo_location

### Search Only (No Changes)

```bash
# Find nearby records within 1000m (default)
php artisan geo:find-nearby 112

# Find within 5000m radius
php artisan geo:find-nearby 112 5000

# Find only records needing geocoding
php artisan geo:find-nearby 112 5000 --search-null-geo
```

### Apply Clustering

```bash
# Apply clustering with default centroid strategy
php artisan geo:find-nearby 112 8000 --mark

# Apply with specific strategy
php artisan geo:find-nearby 112 8000 --mark --strategy=best_accuracy
```

### Copy Geocoding Data

```bash
# Copy geo_location from parent to nearby children
php artisan geo:find-nearby 112 5000 --copy-geo

# Find null geo records and copy data
php artisan geo:find-nearby 112 5000 --search-null-geo --copy-geo

# Combine all operations
php artisan geo:find-nearby 112 5000 --mark --search-null-geo --copy-geo
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

## 🌐 Geo-Location Copying

### Purpose

The geo-location copying feature helps optimize the SearchGeoLocation command by copying existing geocoding data to nearby points, reducing API calls to Nominatim OpenStreetMap.

### How It Works

1. **Parent Validation**: Ensures parent record has geo_location data
2. **Target Selection**: Only copies to children with null geo_location
3. **Data Preservation**: Stores original attempts in raw_attempts
4. **Marking**: Sets attempts to 100 to indicate data was copied

### Benefits

- **Reduced API Calls**: Avoid redundant geocoding requests
- **Faster Processing**: No need to wait for external API responses
- **Rate Limit Compliance**: Helps stay within Nominatim's 1 req/sec limit
- **Data Traceability**: Preserves original attempts for audit trails

### Usage Patterns

```bash
# Before running SearchGeoLocation, copy existing geo data
php artisan geo:find-nearby 112 1000 --search-null-geo --copy-geo

# Then run normal geocoding for remaining records
php artisan geo:search-geo-location
```

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
    "filters": {
        "mark": false,
        "search_null_geo": false,
        "copy_geo": false
    },
    "children_count": 8,
    "normalized": null
}
```

```bash
# 2. Apply clustering
php artisan geo:find-nearby 112 8000 --mark
```

**Result:** Parent record normalized to cluster centroid, 8 children assigned.

### Example 2: Geo-Location Optimization

```bash
# 1. Find records needing geocoding
php artisan geo:find-nearby 112 5000 --search-null-geo
```

**Output:**
```json
{
    "success": true,
    "parent_id": 112,
    "children_count": 5,
    "children": [
        {
            "child_id": 115,
            "geo_location": null,
            "attempts": null,
            "distance_m": 234.56
        }
    ]
}
```

```bash
# 2. Copy geo data to reduce API calls
php artisan geo:find-nearby 112 5000 --search-null-geo --copy-geo
```

**Result:** "Copied geo_location from parent to 5 children."

### Example 3: Complete Workflow

```bash
# 1. Find and normalize cluster
php artisan geo:find-nearby 112 5000 --mark --strategy=centroid

# 2. Copy geo data to related records
php artisan geo:find-nearby 112 8000 --search-null-geo --copy-geo

# 3. Run geocoding for remaining records
php artisan geo:search-geo-location
```

### Example 4: Strategy Comparison

```bash
# Test different strategies on the same data
php artisan geo:find-nearby 112 5000 --strategy=centroid
php artisan geo:find-nearby 112 5000 --strategy=best_accuracy  
php artisan geo:find-nearby 112 5000 --strategy=most_recent
```

## 🔧 Database Management

### Reset All Records

```sql
-- Reset everything to unprocessed state
UPDATE long_lat_lists 
SET parent_id = NULL, 
    is_nearby_processed = false;

-- Restore original coordinates and attempts
UPDATE long_lat_lists 
SET latitude = raw_latitude,
    longitude = raw_longitude,
    geo_location = raw_geo_location,
    attempts = raw_attempts
WHERE raw_latitude IS NOT NULL;
```

### Reset Specific Cluster

```sql
-- Reset only records related to parent 112
UPDATE long_lat_lists 
SET parent_id = NULL, 
    is_nearby_processed = false 
WHERE parent_id = 112;

-- Restore parent 112's data
UPDATE long_lat_lists 
SET latitude = raw_latitude,
    longitude = raw_longitude,
    geo_location = raw_geo_location,
    attempts = raw_attempts,
    parent_id = NULL,
    is_nearby_processed = false
WHERE id = 112 AND raw_latitude IS NOT NULL;
```

### Reset Geo-Copied Records Only

```sql
-- Reset only records with copied geo data (attempts = 100)
UPDATE long_lat_lists 
SET geo_location = NULL,
    attempts = raw_attempts
WHERE attempts = 100 AND raw_attempts IS NOT NULL;
```

### Monitoring Queries

```sql
-- Check cluster and geo-copy states
SELECT 
  COUNT(CASE WHEN parent_id IS NULL AND is_nearby_processed = false THEN 1 END) as unprocessed,
  COUNT(CASE WHEN id = parent_id THEN 1 END) as parents,
  COUNT(CASE WHEN parent_id IS NOT NULL AND id != parent_id THEN 1 END) as children,
  COUNT(CASE WHEN attempts = 100 THEN 1 END) as geo_copied,
  COUNT(CASE WHEN geo_location IS NULL AND attempts IS NULL THEN 1 END) as needs_geocoding
FROM long_lat_lists;

-- View specific cluster with geo data
SELECT id, parent_id, is_nearby_processed, latitude, longitude, 
       attempts, raw_attempts,
       CASE WHEN geo_location IS NOT NULL THEN 'Has Geo' ELSE 'No Geo' END as geo_status
FROM long_lat_lists 
WHERE parent_id = 112 OR id = 112
ORDER BY parent_id, id;

-- Find candidates for geo copying
SELECT p.id as parent_id, 
       COUNT(c.id) as children_needing_geo
FROM long_lat_lists p
LEFT JOIN long_lat_lists c ON c.parent_id = p.id
WHERE p.geo_location IS NOT NULL 
  AND c.geo_location IS NULL 
  AND c.attempts IS NULL
GROUP BY p.id
HAVING children_needing_geo > 0;
```

## 🔗 Integration with SearchGeoLocation

### Optimization Strategy

The FindNearbyLocations command helps optimize SearchGeoLocation by:

1. **Pre-processing**: Copy existing geo data to nearby points
2. **Filtering**: Reduce records needing API calls
3. **Rate Limiting**: Respect Nominatim's 1 req/sec limit

### Recommended Workflow

```bash
# 1. Daily optimization before geocoding
# Find all records with geo data and copy to nearby null records
php artisan geo:find-nearby [parent_id] 1000 --search-null-geo --copy-geo

# 2. Run normal geocoding for remaining records
php artisan geo:search-geo-location

# 3. Weekly clustering for data normalization
php artisan geo:find-nearby [parent_id] 5000 --mark --strategy=centroid
```

### Cron Schedule Integration

Add to your `schedule()` method in `app/Console/Kernel.php`:

```php
// Daily geo-location copying before geocoding
$schedule->command('geo:find-nearby [id] 1000 --search-null-geo --copy-geo')
         ->dailyAt('11:00')->runInBackground();

// Existing geocoding command
$schedule->command(SearchGeoLocation::class)->cron('0 12 * * *')->runInBackground();
```

## 🔍 Troubleshooting

### Common Issues

#### "Parent record has no geo_location. Cannot copy geo data."

**Cause:** Trying to copy from parent without geocoding data

**Solution:**
```bash
# Check parent's geo data
SELECT id, geo_location FROM long_lat_lists WHERE id = 112;

# Find a better parent with geo data
SELECT id, geo_location FROM long_lat_lists 
WHERE geo_location IS NOT NULL 
ORDER BY id LIMIT 10;
```

#### No children found with --search-null-geo

**Cause:** No nearby records with both null attempts and null geo_location

**Solution:**
```bash
# Check what records exist nearby
php artisan geo:find-nearby 112 5000

# Check for records needing geocoding
SELECT COUNT(*) FROM long_lat_lists 
WHERE attempts IS NULL AND geo_location IS NULL;
```

#### Geo-location copying not working

**Cause:** Children already have geo_location or attempts values

**Solution:**
```bash
# Check children status
SELECT id, geo_location, attempts, raw_attempts 
FROM long_lat_lists 
WHERE parent_id = 112;

# Reset if needed
UPDATE long_lat_lists 
SET geo_location = NULL, attempts = raw_attempts 
WHERE attempts = 100;
```

### Performance Tips

- Use appropriate radius sizes (1000-5000m for geo copying)
- Run geo copying before geocoding commands
- Use `--search-null-geo` to focus on records needing data
- Monitor with SQL queries to track progress
- Consider batch processing for large datasets

## 📊 Output Format

The command returns structured JSON with enhanced geo-copying information:

```json
{
    "success": true,
    "parent_id": 112,
    "strategy": "centroid",
    "filters": {
        "mark": false,
        "search_null_geo": true,
        "copy_geo": true
    },
    "original": {
        "latitude": "-6.4564",
        "longitude": "106.7311",
        "geo_location": {...},
        "attempts": 1
    },
    "normalized": null,
    "children_count": 5,
    "children": [
        {
            "child_id": 111,
            "latitude": "-6.4872",
            "longitude": "106.7710", 
            "distance_m": 234.56,
            "geo_location": {...},
            "attempts": 100,
            "raw_attempts": null
        }
    ]
}
```

### Key Changes in Output

- **`filters` object**: Shows which options were used
- **`raw_attempts` field**: Shows original attempts before copying
- **`attempts: 100`**: Indicates geo data was copied
- **Enhanced geo_location**: Shows copied geocoding data

## 🤝 Contributing

When extending this system:

1. Maintain backward compatibility with existing data
2. Add comprehensive tests for new geo-copying features
3. Update this documentation
4. Consider performance impact on SearchGeoLocation integration
5. Test with various Nominatim response formats

## 📄 License

This geolocation clustering and normalization system is part of your Laravel application. Use according to your project's license terms.