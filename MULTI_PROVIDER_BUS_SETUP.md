# Multi-Provider Bus API Integration Guide

## Overview
This implementation provides a unified bus booking system that searches and merges results from multiple providers (Flixbus and BlaBlaCar) in real-time, similar to how RedBus operates.

## Architecture

```
┌─────────────────────────────────────────────────────┐
│          Frontend (Blade Templates)                  │
│        - Search Form (index.blade.php)               │
│        - Results Display with Sorting                │
│        - Multi-Provider Filter                       │
└────────────────────┬────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────┐
│      Controller (TamaBusV2ControllerNew)             │
│   - Handles requests                                 │
│   - Validates input                                  │
│   - Delegates to BusApiManager                       │
└────────────────────┬────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────┐
│       BusApiManager (Coordinator)                    │
│   - Registers enabled services                       │
│   - Calls both APIs in parallel                      │
│   - Merges results                                   │
└──┬─────────────────────────────────────┬────────────┘
   │                                     │
   ▼                                     ▼
┌──────────────────────┐        ┌──────────────────────┐
│ FlixbusService       │        │ BlablacarService     │
├──────────────────────┤        ├──────────────────────┤
│ - searchTrips()      │        │ - searchTrips()      │
│ - searchCities()     │        │ - searchCities()     │
│ - normalizeTrips()   │        │ - getAccessToken()   │
│ - API Key auth       │        │ - OAuth2 auth        │
└──────────────────────┘        └──────────────────────┘
```

## Installation Steps

### 1. Install Configuration
Copy the provided `config/bus-api.php` to your Laravel config directory.

### 2. Environment Variables
Add these to your `.env` file:

```env
# Flixbus Configuration
FLIXBUS_ENABLED=true
FLIXBUS_API_KEY=your_flixbus_api_key_here
FLIXBUS_ENDPOINT=https://global.api.flixbus.com
FLIXBUS_SANDBOX_MODE=false
FLIXBUS_TIMEOUT=120
FLIXBUS_LANGUAGE=en

# BlaBlaCar Configuration
BLABLACAR_ENABLED=true
BLABLACAR_CLIENT_ID=your_client_id_here
BLABLACAR_CLIENT_SECRET=your_client_secret_here
BLABLACAR_USERNAME=your_username_here
BLABLACAR_PASSWORD=your_password_here
BLABLACAR_ENDPOINT=https://api.blablabus-prod.cloud.sqills.com
BLABLACAR_SANDBOX_MODE=false
BLABLACAR_TIMEOUT=120
BLABLACAR_LANGUAGE=en-GB
BLABLACAR_CURRENCY=EUR

# Cache Configuration
BUS_API_CACHE_ENABLED=true
BUS_STATIONS_CACHE_TTL=86400
BUS_SEARCH_CACHE_TTL=300
BUS_API_DEFAULT=both
```

### 3. Register Service Provider
Add this to your `config/app.php` providers array:

```php
'providers' => [
    // ...
    App\Providers\BusApiServiceProvider::class,
],
```

### 4. Register Routes
Add this to your `routes/web.php`:

```php
require base_path('routes/bus-api-v2.php');
```

### 5. Create Directories
```bash
mkdir -p app/Services/BusApi
mkdir -p resources/views/service/bus-v2
```

### 6. Copy Files
Copy all provided service files to `app/Services/BusApi/`:
- `BusProviderInterface.php`
- `FlixbusService.php`
- `BlablacarService.php`
- `BusApiManager.php`
- `BusResultMerger.php`

## Key Features

### 1. Dual API Integration
- **Flixbus**: Simple API key authentication
- **BlaBlaCar**: OAuth 2.0 with token refresh (30-min expiry)

### 2. Result Merging
- Automatic deduplication of similar trips
- Configurable price variance threshold (default: 10%)
- Multiple sorting options (price, duration, departure time)

### 3. Caching Strategy
- **Station data**: Cached for 24 hours
- **Search results**: Cached for 5 minutes
- Configurable via environment variables

### 4. City/Station Search
- Multi-provider autocomplete
- Removes duplicate cities
- Supports station codes and names

### 5. Error Handling
- Graceful fallback if one provider fails
- Comprehensive logging
- User-friendly error messages

## API Endpoints

### Frontend Routes

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/service/bus-v2` | Bus search page |
| POST | `/service/bus-v2/search` | Search buses (JSON) |
| GET | `/service/bus-v2/results` | Get paginated results |
| POST | `/service/bus-v2/cities` | City autocomplete |
| GET | `/service/bus-v2/trip-details` | Get trip details |

### Request/Response Examples

#### Search Request
```json
{
  "from": "Paris",
  "to": "Berlin",
  "departure_date": "2026-05-15",
  "return_date": null,
  "trip_type": "one_way",
  "adults": 2,
  "children": 1
}
```

#### Search Response
```json
{
  "success": true,
  "message": "Found 24 buses"
}
```

#### Results Request
```
GET /service/bus-v2/results?page=1&sort=price_asc&provider=
```

#### Results Response
```json
{
  "success": true,
  "data": [
    {
      "provider": "flixbus",
      "provider_trip_id": "trip_123",
      "from_city": "Paris",
      "to_city": "Berlin",
      "departure_time": "2026-05-15T08:00:00",
      "arrival_time": "2026-05-15T16:30:00",
      "duration": 510,
      "price": 29.99,
      "currency": "EUR",
      "available_seats": 45,
      "bus_name": "Flixbus Premium",
      "amenities": ["wifi", "power", "restroom"],
      "rating": 4.5,
      "stops_count": 3
    }
  ],
  "pagination": {
    "total": 48,
    "per_page": 10,
    "page": 1,
    "last_page": 5
  },
  "providers": {
    "flixbus": 24,
    "blablacar": 24
  }
}
```

## Configuration Options

### Merger Settings
```php
'merger' => [
    'sort_by' => 'price', // price, duration, departure_time, rating
    'remove_duplicates' => true,
    'price_variance_threshold' => 0.10, // 10% variance
],
```

### Cache Settings
```php
'cache' => [
    'enabled' => true,
    'stations_ttl' => 86400, // 24 hours
    'search_ttl' => 300, // 5 minutes
],
```

## Usage in Controllers

```php
use App\Services\BusApi\BusApiManager;

class YourController extends Controller
{
    public function search()
    {
        $busManager = new BusApiManager();
        
        $searchParams = [
            'from' => 'Paris',
            'to' => 'Berlin',
            'departure_date' => '2026-05-15',
            'return_date' => null,
            'adults' => 2,
            'children' => 1,
        ];
        
        $trips = $busManager->searchTrips($searchParams);
        
        // Results are already merged and sorted
        $trips->each(function($trip) {
            // Each trip has provider info
            echo $trip['provider']; // 'flixbus' or 'blablacar'
            echo $trip['price'];
            echo $trip['departure_time'];
        });
    }
}
```

## Troubleshooting

### 1. No Results Returned
- **Check**: Verify API credentials in `.env`
- **Check**: Ensure providers are enabled in config
- **Check**: Review logs in `storage/logs/`

### 2. Token Expiration (BlaBlaCar)
- **Automatic**: Tokens are cached and refreshed automatically
- **Check**: Ensure `BLABLACAR_PASSWORD` doesn't change during session

### 3. City Search Not Working
- **Check**: Verify cache is working
- **Clear**: Run `php artisan cache:clear`

### 4. Duplicate Results
- **Adjust**: Modify `price_variance_threshold` in config
- **Disable**: Set `remove_duplicates` to false

## Performance Optimization

### 1. Caching
- Station data is cached for 24 hours
- Search results cached for 5 minutes
- Token automatically cached (30 min for BlaBlaCar)

### 2. Parallel Requests
- Both APIs called simultaneously (not sequential)
- Results merged after both complete

### 3. Database Optimization
- Consider caching popular routes
- Store booking history for quick access

## Security Considerations

1. **API Keys**: Store in `.env`, never commit to version control
2. **OAuth Tokens**: Automatically cached and refreshed
3. **Input Validation**: All inputs validated before API calls
4. **Rate Limiting**: Consider implementing per-IP rate limits

## Future Enhancements

1. Add more providers (e.g., Eurostar, coach services)
2. Real-time price tracking
3. Booking history and favorites
4. Price comparison alerts
5. Multi-currency support
6. Advanced filtering (amenities, operator, etc.)

## Support

For issues or questions:
1. Check logs in `storage/logs/laravel.log`
2. Review error messages in browser console
3. Verify API credentials and permissions
4. Contact API providers for account issues
