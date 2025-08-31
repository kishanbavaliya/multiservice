# Restaurant APIs Documentation

## Overview
This document provides comprehensive API documentation for the Restaurant Management System, including all endpoints for retrieving restaurants with various filters and sorting options.

## Base URL
```
https://yourdomain.com/api/restaurants
```

## Authentication
All API endpoints require authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your_access_token}
```

---

## 1. Get All Restaurants (GET /)

### Description
Retrieve all restaurants with pagination, filtering, and sorting capabilities.

### Endpoint
```http
GET /api/restaurants
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number for pagination |
| `per_page` | integer | No | 15 | Items per page (max 100) |
| `status` | string | No | - | Filter by status (active, inactive, suspended) |
| `is_featured` | boolean | No | - | Filter by featured status (true/false) |
| `cuisine_type` | string | No | - | Filter by cuisine type |
| `city` | string | No | - | Filter by city |
| `delivery_available` | boolean | No | - | Filter by delivery availability |
| `pickup_available` | boolean | No | - | Filter by pickup availability |
| `min_rating` | numeric | No | - | Minimum rating filter (0-5) |
| `max_delivery_time` | integer | No | - | Maximum delivery time in minutes |
| `search` | string | No | - | Search by name, description, or cuisine type |
| `sort_by` | string | No | rating | Sort field (name, rating, created_at, delivery_fee, min_delivery_time) |
| `sort_direction` | string | No | desc | Sort direction (asc, desc) |

### Example Request
```bash
# Get all restaurants
curl -X GET "https://yourdomain.com/api/restaurants" \
  -H "Authorization: Bearer {token}"

# Get restaurants with filters
curl -X GET "https://yourdomain.com/api/restaurants?city=New%20York&cuisine_type=Italian&min_rating=4.0&sort_by=rating&sort_direction=desc" \
  -H "Authorization: Bearer {token}"

# Get restaurants with pagination
curl -X GET "https://yourdomain.com/api/restaurants?page=1&per_page=10" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "description": "Authentic Italian pizza and pasta",
        "cuisine_type": "Italian",
        "address": "123 Main Street",
        "city": "New York",
        "state": "NY",
        "country": "USA",
        "postal_code": "10001",
        "latitude": 40.7505,
        "longitude": -73.9934,
        "phone": "+1-555-0123",
        "email": "info@pizzapalace.com",
        "website": "https://pizzapalace.com",
        "delivery_fee": 2.99,
        "minimum_order": 15.00,
        "min_delivery_time": 25,
        "max_delivery_time": 45,
        "delivery_available": true,
        "pickup_available": true,
        "delivery_radius": 5.0,
        "status": "active",
        "is_featured": true,
        "is_verified": true,
        "rating": 4.5,
        "total_reviews": 127,
        "total_reviews": 127,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z",
        "categories": [
          {
            "id": 1,
            "name": "Pizza",
            "is_active": true
          }
        ],
        "products": [
          {
            "id": 1,
            "name": "Margherita Pizza",
            "price": 14.99
          }
        ]
      }
    ],
    "total": 25,
    "per_page": 15,
    "last_page": 2
  },
  "message": "Restaurants retrieved successfully"
}
```

---

## 2. Top Trending Restaurants (GET /trending)

### Description
Get top trending restaurants based on view count, order count, and rating using a sophisticated scoring algorithm.

### Endpoint
```http
GET /api/restaurants/trending
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of restaurants to return (max 50) |
| `city` | string | No | - | Filter by city |
| `cuisine_type` | string | No | - | Filter by cuisine type |

### Trending Score Algorithm
```
Trending Score = (rating × 15) + (total_reviews × 0.1) + (featured bonus)
Featured Bonus = 10 if restaurant is featured, 0 otherwise
```

### Example Request
```bash
# Get top 10 trending restaurants
curl -X GET "https://yourdomain.com/api/restaurants/trending" \
  -H "Authorization: Bearer {token}"

# Get top 20 trending restaurants in New York
curl -X GET "https://yourdomain.com/api/restaurants/trending?limit=20&city=New%20York" \
  -H "Authorization: Bearer {token}"

# Get trending Italian restaurants
curl -X GET "https://yourdomain.com/api/restaurants/trending?cuisine_type=Italian" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pizza Palace",
      "description": "Authentic Italian pizza and pasta",
      "cuisine_type": "Italian",
      "city": "New York",
      "state": "NY",
      "delivery_fee": 2.99,
      "min_delivery_time": 25,
      "max_delivery_time": 45,
      "delivery_available": true,
      "pickup_available": true,
      "rating": 4.5,
      "total_reviews": 127,
              "total_reviews": 127,
        "logo_url": "https://example.com/pizza.jpg",
      "is_featured": true,
      "trending_score": 156.75,
      "categories": [
        {
          "id": 1,
          "name": "Pizza",
          "is_active": true
        }
      ]
    }
  ],
  "message": "Top trending restaurants retrieved successfully"
}
```

---

## 3. Best Sellers Restaurants (GET /best-sellers)

### Description
Get best selling restaurants based on order count and rating, with optional timeframe filtering.

### Endpoint
```http
GET /api/restaurants/best-sellers
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of restaurants to return (max 50) |
| `city` | string | No | - | Filter by city |
| `cuisine_type` | string | No | - | Filter by cuisine type |
| `timeframe` | string | No | all | Timeframe filter (all, week, month) |

### Best Seller Score Algorithm
```
Best Seller Score = (rating × 0.7) + (total_reviews × 0.3)
```

### Example Request
```bash
# Get top 10 best sellers
curl -X GET "https://yourdomain.com/api/restaurants/best-sellers" \
  -H "Authorization: Bearer {token}"

# Get best sellers from this week
curl -X GET "https://yourdomain.com/api/restaurants/best-sellers?timeframe=week" \
  -H "Authorization: Bearer {token}"

# Get best sellers in specific city
curl -X GET "https://yourdomain.com/api/restaurants/best-sellers?city=Los%20Angeles" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pizza Palace",
      "description": "Authentic Italian pizza and pasta",
      "cuisine_type": "Italian",
      "city": "New York",
      "state": "NY",
      "delivery_fee": 2.99,
      "min_delivery_time": 25,
      "max_delivery_time": 45,
      "delivery_available": true,
      "pickup_available": true,
      "rating": 4.5,
      "total_reviews": 127,
              "logo_url": "https://example.com/pizza.jpg",
      "is_featured": true,
      "best_seller_score": 58.8,
      "categories": [
        {
          "id": 1,
          "name": "Pizza",
          "is_active": true
        }
      ]
    }
  ],
  "message": "Best sellers restaurants retrieved successfully"
}
```

---

## 4. Nearby Restaurants (GET /nearby)

### Description
Find restaurants within a specified radius from given coordinates using the Haversine formula for accurate distance calculation.

### Endpoint
```http
GET /api/restaurants/nearby
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `latitude` | numeric | Yes | - | Latitude coordinate (-90 to 90) |
| `longitude` | numeric | Yes | - | Longitude coordinate (-180 to 180) |
| `radius` | numeric | No | 5 | Search radius in kilometers (0.1 to 50) |
| `limit` | integer | No | 20 | Number of restaurants to return (max 100) |
| `cuisine_type` | string | No | - | Filter by cuisine type |
| `delivery_available` | boolean | No | - | Filter by delivery availability |
| `min_rating` | numeric | No | - | Minimum rating filter (0-5) |

### Example Request
```bash
# Find restaurants within 5km of coordinates
curl -X GET "https://yourdomain.com/api/restaurants/nearby?latitude=40.7505&longitude=-73.9934" \
  -H "Authorization: Bearer {token}"

# Find restaurants within 10km with filters
curl -X GET "https://yourdomain.com/api/restaurants/nearby?latitude=40.7505&longitude=-73.9934&radius=10&cuisine_type=Italian&min_rating=4.0" \
  -H "Authorization: Bearer {token}"

# Find delivery restaurants within 3km
curl -X GET "https://yourdomain.com/api/restaurants/nearby?latitude=40.7505&longitude=-73.9934&radius=3&delivery_available=true" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "restaurants": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "description": "Authentic Italian pizza and pasta",
        "cuisine_type": "Italian",
        "address": "123 Main Street",
        "city": "New York",
        "state": "NY",
        "latitude": 40.7505,
        "longitude": -73.9934,
        "delivery_fee": 2.99,
        "min_delivery_time": 25,
        "max_delivery_time": 45,
        "delivery_available": true,
        "pickup_available": true,
        "rating": 4.5,
        "total_reviews": 127,
        "logo_url": "https://example.com/pizza.jpg",
        "is_featured": true,
        "distance_km": 0.5,
        "distance_miles": 0.31,
        "categories": [
          {
            "id": 1,
            "name": "Pizza",
            "is_active": true
          }
        ]
      }
    ],
    "search_location": {
      "latitude": 40.7505,
      "longitude": -73.9934,
      "radius_km": 5
    },
    "total_found": 1
  },
  "message": "Nearby restaurants retrieved successfully"
}
```

---

## 5. Search Restaurants (GET /search)

### Description
Advanced search functionality with multiple filters and price range options.

### Endpoint
```http
GET /api/restaurants/search
```

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `query` | string | Yes | - | Search term (min 2 characters) |
| `city` | string | No | - | Filter by city |
| `cuisine_type` | string | No | - | Filter by cuisine type |
| `status` | string | No | - | Filter by status (active, inactive, suspended) |
| `delivery_available` | boolean | No | - | Filter by delivery availability |
| `min_rating` | numeric | No | - | Minimum rating filter (0-5) |
| `max_delivery_time` | integer | No | - | Maximum delivery time in minutes |
| `price_range` | string | No | - | Price range filter (low, medium, high) |
| `limit` | integer | No | 20 | Number of restaurants to return (max 100) |

### Price Range Definitions
- **Low**: Delivery fee ≤ $2.99
- **Medium**: Delivery fee $3.00 - $5.99
- **High**: Delivery fee ≥ $6.00

### Example Request
```bash
# Basic search
curl -X GET "https://yourdomain.com/api/restaurants/search?query=pizza" \
  -H "Authorization: Bearer {token}"

# Search with multiple filters
curl -X GET "https://yourdomain.com/api/restaurants/search?query=italian&city=New%20York&min_rating=4.0&price_range=medium" \
  -H "Authorization: Bearer {token}"

# Search for delivery restaurants
curl -X GET "https://yourdomain.com/api/restaurants/search?query=chinese&delivery_available=true&max_delivery_time=30" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "restaurants": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "description": "Authentic Italian pizza and pasta",
        "cuisine_type": "Italian",
        "city": "New York",
        "rating": 4.5,
        "delivery_fee": 2.99,
        "delivery_available": true,
        "logo_url": "https://example.com/pizza.jpg",
        "categories": [
          {
            "id": 1,
            "name": "Pizza",
            "is_active": true
          }
        ]
      }
    ],
    "total_found": 1,
    "search_query": "pizza"
  },
  "message": "Search completed successfully"
}
```

---

## 6. Restaurant Statistics (GET /stats)

### Description
Get comprehensive statistics and analytics about restaurants in the system.

### Endpoint
```http
GET /api/restaurants/stats
```

### Example Request
```bash
curl -X GET "https://yourdomain.com/api/restaurants/stats" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "total_restaurants": 150,
    "active_restaurants": 142,
    "verified_restaurants": 135,
    "featured_restaurants": 25,
    "delivery_available": 120,
    "pickup_available": 95,
    "top_cuisine_types": [
      {
        "cuisine_type": "Italian",
        "count": 25
      },
      {
        "cuisine_type": "Chinese",
        "count": 20
      }
    ],
    "top_cities": [
      {
        "city": "New York",
        "count": 35
      },
      {
        "city": "Los Angeles",
        "count": 28
      }
    ],
    "rating_distribution": {
      "5_star": 15,
      "4_star": 45,
      "3_star": 35,
      "2_star": 25,
      "1_star": 15,
      "unrated": 15
    },
    "recent_additions": [
      {
        "id": 150,
        "name": "New Restaurant",
        "city": "Chicago",
        "created_at": "2025-01-15T12:00:00.000000Z"
      }
    ]
  },
  "message": "Restaurant statistics retrieved successfully"
}
```

---

## 7. Restaurants by Cuisine Type (GET /cuisine/{cuisineType})

### Description
Get restaurants filtered by specific cuisine type with optional city and rating filters.

### Endpoint
```http
GET /api/restaurants/cuisine/{cuisineType}
```

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `cuisineType` | string | Yes | Cuisine type to filter by |

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 20 | Number of restaurants to return |
| `city` | string | No | - | Filter by city |
| `min_rating` | numeric | No | - | Minimum rating filter |

### Example Request
```bash
# Get Italian restaurants
curl -X GET "https://yourdomain.com/api/restaurants/cuisine/Italian" \
  -H "Authorization: Bearer {token}"

# Get Chinese restaurants in New York with 4+ rating
curl -X GET "https://yourdomain.com/api/restaurants/cuisine/Chinese?city=New%20York&min_rating=4.0" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "cuisine_type": "Italian",
    "restaurants": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "description": "Authentic Italian pizza and pasta",
        "cuisine_type": "Italian",
        "city": "New York",
        "rating": 4.5,
        "logo_url": "https://example.com/pizza.jpg",
        "categories": [
          {
            "id": 1,
            "name": "Pizza",
            "is_active": true
          }
        ]
      }
    ],
    "total_found": 1
  },
  "message": "Restaurants with cuisine type 'Italian' retrieved successfully"
}
```

---

## 8. Restaurants by City (GET /city/{city})

### Description
Get restaurants in a specific city with optional cuisine type and rating filters, plus multiple sorting options.

### Endpoint
```http
GET /api/restaurants/city/{city}
```

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `city` | string | Yes | City name to filter by |

### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 20 | Number of restaurants to return |
| `cuisine_type` | string | No | - | Filter by cuisine type |
| `min_rating` | numeric | No | - | Minimum rating filter |
| `sort_by` | string | No | rating | Sort field (name, rating, delivery_fee, min_delivery_time, total_reviews) |

### Example Request
```bash
# Get restaurants in New York
curl -X GET "https://yourdomain.com/api/restaurants/city/New%20York" \
  -H "Authorization: Bearer {token}"

# Get Italian restaurants in Los Angeles sorted by delivery fee
curl -X GET "https://yourdomain.com/api/restaurants/city/Los%20Angeles?cuisine_type=Italian&sort_by=delivery_fee" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "city": "New York",
    "restaurants": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "description": "Authentic Italian pizza and pasta",
        "cuisine_type": "Italian",
        "city": "New York",
        "rating": 4.5,
        "delivery_fee": 2.99,
        "min_delivery_time": 25,
        "logo_url": "https://example.com/pizza.jpg",
        "categories": [
          {
            "id": 1,
            "name": "Pizza",
            "is_active": true
          }
        ]
      }
    ],
    "total_found": 1,
    "sort_by": "rating"
  },
  "message": "Restaurants in 'New York' retrieved successfully"
}
```

---

## 9. Get Restaurant by ID (GET /{id})

### Description
Get detailed information about a specific restaurant including all related data.

### Endpoint
```http
GET /api/restaurants/{id}
```

### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | Restaurant ID |

### Features
- **Loads all related data** (categories, subcategories, products, etc.)
- **Only active items** are included in relationships

### Example Request
```bash
curl -X GET "https://yourdomain.com/api/restaurants/1" \
  -H "Authorization: Bearer {token}"
```

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Pizza Palace",
    "description": "Authentic Italian pizza and pasta",
    "cuisine_type": "Italian",
    "address": "123 Main Street",
    "city": "New York",
    "state": "NY",
    "country": "USA",
    "postal_code": "10001",
    "latitude": 40.7505,
    "longitude": -73.9934,
    "phone": "+1-555-0123",
    "email": "info@pizzapalace.com",
    "website": "https://pizzapalace.com",
    "delivery_fee": 2.99,
    "minimum_order": 15.00,
    "min_delivery_time": 25,
    "max_delivery_time": 45,
    "delivery_available": true,
    "pickup_available": true,
    "delivery_radius": 5.0,
    "status": "active",
    "is_featured": true,
    "is_verified": true,
    "rating": 4.5,
    "total_reviews": 127,
            "total_reviews": 127,
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T10:00:00.000000Z",
    "categories": [
      {
        "id": 1,
        "name": "Pizza",
        "is_active": true
      }
    ],
    "subcategories": [
      {
        "id": 1,
        "name": "Hot Appetizers",
        "is_active": true
      }
    ],
    "products": [
      {
        "id": 1,
        "name": "Margherita Pizza",
        "price": 14.99,
        "is_available": true,
        "is_active": true
      }
    ],
    "servingSizes": [
      {
        "id": 1,
        "name": "Large",
        "status": true
      }
    ],
    "modifierGroups": [
      {
        "id": 1,
        "name": "Pizza Toppings",
        "status": true
      }
    ],
    "banners": [
      {
        "id": 1,
        "title": "Welcome Banner",
        "is_active": true
      }
    ]
  },
  "message": "Restaurant retrieved successfully"
}
```

---

## Error Handling

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "latitude": ["The latitude field is required."],
    "longitude": ["The longitude field is required."]
  }
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "Restaurant not found"
}
```

### Unauthorized Error (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to retrieve restaurants",
  "error": "Database connection error"
}
```

---

## Performance & Optimization

### Response Time Expectations
- **GET requests**: < 200ms
- **Search requests**: < 300ms
- **Nearby requests**: < 400ms (due to distance calculations)
- **Statistics requests**: < 500ms

### Caching Recommendations
- Cache trending and best sellers for 15 minutes
- Cache statistics for 1 hour
- Cache city-based results for 30 minutes

### Database Indexes
Ensure proper indexes on:
- `status`, `is_verified`, `is_featured`
- `cuisine_type`, `city`
- `rating`, `total_reviews`
- `latitude`, `longitude` (for nearby searches)

---

## Testing Examples

### Postman Collection
```json
{
  "info": {
    "name": "Restaurant APIs",
    "description": "Complete restaurant management API collection"
  },
  "item": [
    {
      "name": "Get All Restaurants",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/restaurants"
      }
    },
    {
      "name": "Top Trending",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/restaurants/trending?limit=10"
      }
    },
    {
      "name": "Best Sellers",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/restaurants/best-sellers?timeframe=week"
      }
    },
    {
      "name": "Nearby Restaurants",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/restaurants/nearby?latitude=40.7505&longitude=-73.9934&radius=5"
      }
    }
  ]
}
```

### Environment Variables
```
base_url: https://yourdomain.com/api
auth_token: {your_bearer_token}
```

---

## Rate Limiting

The API implements rate limiting:
- **General endpoints**: 60 requests per minute
- **Search endpoints**: 30 requests per minute
- **Nearby endpoints**: 20 requests per minute (due to distance calculations)

Rate limit headers included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642234567
```

---

## Support

For API support and questions:
- Email: api-support@yourdomain.com
- Documentation: https://docs.yourdomain.com/api
- Status page: https://status.yourdomain.com
