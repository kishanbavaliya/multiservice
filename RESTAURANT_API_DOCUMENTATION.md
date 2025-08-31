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

---

## 2. Restaurant Categories

### 2.1 Get All Categories
```http
GET /restaurant-categories
```

**Query Parameters:**
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 15)
- `restaurant_id` (optional): Filter by restaurant ID
- `is_active` (optional): Filter by active status (true/false)
- `is_featured` (optional): Filter by featured status (true/false)
- `search` (optional): Search by name or description
- `sort_by` (optional): Sort field (name, sort_order, created_at, etc.)
- `sort_direction` (optional): Sort direction (asc, desc)

**Example Request:**
```bash
curl -X GET "https://yourdomain.com/api/restaurant-categories?restaurant_id=1&is_active=true&sort_by=name&sort_direction=asc" \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "restaurant_id": 1,
        "name": "Appetizers",
        "description": "Start your meal with our delicious appetizers",
        "image_url": null,
        "icon_url": null,
        "is_active": true,
        "is_featured": true,
        "sort_order": 1,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z",
        "restaurant": {
          "id": 1,
          "name": "Pizza Palace"
        },
        "subcategories": [
          {
            "id": 1,
            "name": "Hot Appetizers"
          }
        ]
      }
    ],
    "total": 5,
    "per_page": 15
  },
  "message": "Categories retrieved successfully"
}
```

### 2.2 Create Category
```http
POST /restaurant-categories
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": "New Category",
  "description": "Category description",
  "image_url": "https://example.com/image.jpg",
  "icon_url": "https://example.com/icon.svg",
  "is_active": true,
  "is_featured": false,
  "sort_order": 5
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `name`: Category name (string, max 255 characters)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 26,
    "restaurant_id": 1,
    "name": "New Category",
    "description": "Category description",
    "is_active": true,
    "is_featured": false,
    "sort_order": 5,
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T10:00:00.000000Z",
    "restaurant": {
      "id": 1,
      "name": "Pizza Palace"
    }
  },
  "message": "Category created successfully"
}
```

### 2.3 Get Category by ID
```http
GET /restaurant-categories/{id}
```

**Path Parameters:**
- `id`: Category ID (integer)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "restaurant_id": 1,
    "name": "Appetizers",
    "description": "Start your meal with our delicious appetizers",
    "image_url": null,
    "icon_url": null,
    "is_active": true,
    "is_featured": true,
    "sort_order": 1,
    "created_at": "2025-01-15T10:00:00.000000Z",
    "updated_at": "2025-01-15T10:00:00.000000Z",
    "restaurant": {
      "id": 1,
      "name": "Pizza Palace"
    },
    "subcategories": [
      {
        "id": 1,
        "name": "Hot Appetizers"
      }
    ]
  },
  "message": "Category retrieved successfully"
}
```

### 2.4 Update Category
```http
PUT /restaurant-categories/{id}
```

**Path Parameters:**
- `id`: Category ID (integer)

**Request Body:** (All fields are optional for updates)
```json
{
  "name": "Updated Category Name",
  "description": "Updated description",
  "is_active": false
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Category Name",
    "description": "Updated description",
    "is_active": false,
    "updated_at": "2025-01-15T11:00:00.000000Z"
  },
  "message": "Category updated successfully"
}
```

### 2.5 Delete Category
```http
DELETE /restaurant-categories/{id}
```

**Path Parameters:**
- `id`: Category ID (integer)

**Note:** Cannot delete categories with existing subcategories or products.

**Response:**
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

### 2.6 Get Categories by Restaurant
```http
GET /restaurant-categories/restaurant/{restaurantId}
```

**Path Parameters:**
- `restaurantId`: Restaurant ID (integer)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Appetizers",
      "description": "Start your meal with our delicious appetizers",
      "is_active": true,
      "is_featured": true,
      "sort_order": 1,
      "subcategories": [
        {
          "id": 1,
          "name": "Hot Appetizers"
        }
      ]
    }
  ],
  "message": "Restaurant categories retrieved successfully"
}
```

### 2.7 Toggle Category Status
```http
PATCH /restaurant-categories/{id}/toggle-status
```

**Path Parameters:**
- `id`: Category ID (integer)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "is_active": false,
    "updated_at": "2025-01-15T11:00:00.000000Z"
  },
  "message": "Category status updated successfully"
}
```

### 2.8 Toggle Featured Status
```http
PATCH /restaurant-categories/{id}/toggle-featured
```

**Path Parameters:**
- `id`: Category ID (integer)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "is_featured": false,
    "updated_at": "2025-01-15T11:00:00.000000Z"
  },
  "message": "Category featured status updated successfully"
}
```

### 2.9 Search Categories
```http
GET /restaurant-categories/search
```

**Query Parameters:**
- `query` (required): Search term (min 2 characters)
- `restaurant_id` (optional): Filter by restaurant ID
- `is_active` (optional): Filter by active status

**Example Request:**
```bash
curl -X GET "https://yourdomain.com/api/restaurant-categories/search?query=pizza&restaurant_id=1" \
  -H "Authorization: Bearer {token}"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Pizza",
      "description": "Delicious pizza varieties",
      "restaurant": {
        "id": 1,
        "name": "Pizza Palace"
      }
    }
  ],
  "message": "Search completed successfully"
}
```

### 2.10 Get Category Statistics
```http
GET /restaurant-categories/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_categories": 25,
    "active_categories": 20,
    "featured_categories": 5,
    "categories_with_subcategories": 15,
    "categories_with_products": 18,
    "recent_categories": [
      {
        "id": 25,
        "name": "New Category",
        "created_at": "2025-01-15T12:00:00.000000Z"
      }
    ]
  },
  "message": "Category statistics retrieved successfully"
}
```

---

## 3. Restaurant Subcategories

### 3.1 Get All Subcategories
```http
GET /restaurant-subcategories
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `category_id` (optional): Filter by category ID
- `is_active` (optional): Filter by active status
- `search` (optional): Search by name or description

### 3.2 Create Subcategory
```http
POST /restaurant-subcategories
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "category_id": 1,
  "name": "Hot Appetizers",
  "description": "Warm and crispy appetizers",
  "image_url": "https://example.com/image.jpg",
  "icon_url": "https://example.com/icon.svg",
  "is_active": true,
  "is_featured": false,
  "sort_order": 1
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `category_id`: Category ID (integer, must exist)
- `name`: Subcategory name (string, max 255 characters)

### 3.3 Get Subcategories by Category
```http
GET /restaurant-subcategories/category/{categoryId}
```

**Path Parameters:**
- `categoryId`: Category ID (integer)

---

## 4. Restaurant Products

### 4.1 Get All Products
```http
GET /restaurant-products
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `category_id` (optional): Filter by category ID
- `subcategory_id` (optional): Filter by subcategory ID
- `is_available` (optional): Filter by availability
- `is_featured` (optional): Filter by featured status
- `price_min` (optional): Minimum price filter
- `price_max` (optional): Maximum price filter
- `search` (optional): Search by name, description, or ingredients
- `sort_by` (optional): Sort field (name, price, created_at, etc.)
- `sort_direction` (optional): Sort direction (asc, desc)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "restaurant_id": 1,
        "category_id": 2,
        "subcategory_id": 3,
        "name": "Margherita Pizza",
        "description": "Classic pizza with tomato sauce, mozzarella, and basil",
        "price": 14.99,
        "original_price": 14.99,
        "discount_percentage": 0,
        "discount_amount": 0,
        "ingredients": "Tomato sauce, mozzarella cheese, fresh basil, olive oil",
        "allergens": "Dairy, Gluten",
        "preparation_time": 20,
        "calories": 285,
        "dietary_info": "Vegetarian",
        "is_available": true,
        "is_featured": true,
        "is_popular": true,
        "is_recommended": false,
        "stock_quantity": null,
        "track_stock": false,
        "allow_out_of_stock_orders": true,
        "allow_customization": true,
        "customization_options": ["extra cheese", "extra toppings"],
        "sort_order": 1,
        "view_count": 0,
        "order_count": 0,
        "rating": 4.5,
        "total_reviews": 12,
        "created_at": "2025-01-15T10:00:00.000000Z",
        "updated_at": "2025-01-15T10:00:00.000000Z"
      }
    ],
    "total": 25,
    "per_page": 15
  },
  "message": "Products retrieved successfully"
}
```

### 4.2 Create Product
```http
POST /restaurant-products
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "category_id": 2,
  "subcategory_id": 3,
  "name": "New Product",
  "description": "Product description",
  "price": 19.99,
  "original_price": 19.99,
  "discount_percentage": 0,
  "discount_amount": 0,
  "ingredients": "Ingredient list",
  "allergens": "Allergen information",
  "preparation_time": 25,
  "calories": 350,
  "dietary_info": "Vegetarian",
  "is_available": true,
  "is_featured": false,
  "is_popular": false,
  "is_recommended": false,
  "stock_quantity": null,
  "track_stock": false,
  "allow_out_of_stock_orders": true,
  "allow_customization": true,
  "customization_options": ["option1", "option2"],
  "sort_order": 1
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `category_id`: Category ID (integer, must exist)
- `name`: Product name (string, max 255 characters)
- `price`: Product price (numeric, min 0)

### 4.3 Get Products by Restaurant
```http
GET /restaurant-products/restaurant/{restaurantId}
```

**Path Parameters:**
- `restaurantId`: Restaurant ID (integer)

### 4.4 Get Featured Products
```http
GET /restaurant-products/featured
```

### 4.5 Get Popular Products
```http
GET /restaurant-products/popular
```

### 4.6 Search Products
```http
GET /restaurant-products/search
```

**Query Parameters:**
- `query` (required): Search term (min 2 characters)
- `restaurant_id` (optional): Filter by restaurant ID
- `category_id` (optional): Filter by category ID
- `price_min` (optional): Minimum price filter
- `price_max` (optional): Maximum price filter

---

## 5. Restaurant Serving Sizes

### 5.1 Get All Serving Sizes
```http
GET /restaurant-serving-sizes
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `status` (optional): Filter by status

### 5.2 Create Serving Size
```http
POST /restaurant-serving-sizes
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": "Large",
  "description": "Generous portion size",
  "status": true
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `name`: Serving size name (string, max 255 characters)

---

## 6. Restaurant Modifiers

### 6.1 Get All Modifiers
```http
GET /restaurant-modifiers
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `status` (optional): Filter by status

### 6.2 Create Modifier
```http
POST /restaurant-modifiers
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": "Extra Cheese",
  "description": "Additional mozzarella cheese",
  "status": true
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `name`: Modifier name (string, max 255 characters)

---

## 7. Restaurant Modifier Groups

### 7.1 Get All Modifier Groups
```http
GET /restaurant-modifier-groups
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `type` (optional): Filter by selection type
- `status` (optional): Filter by status

### 7.2 Create Modifier Group
```http
POST /restaurant-modifier-groups
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": "Pizza Toppings",
  "selection_type": "optional",
  "required_count": null,
  "status": true
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `name`: Group name (string, max 255 characters)
- `selection_type`: Selection type (string: optional, required, multiple)

---

## 8. Restaurant Banners

### 8.1 Get All Banners
```http
GET /restaurant-banners
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page
- `restaurant_id` (optional): Filter by restaurant ID
- `type` (optional): Filter by banner type
- `position` (optional): Filter by position
- `is_active` (optional): Filter by active status

### 8.2 Create Banner
```http
POST /restaurant-banners
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "title": "Welcome Banner",
  "description": "Welcome to our restaurant",
  "image_url": "https://example.com/banner.jpg",
  "banner_type": "homepage",
  "position": 1,
  "is_active": true,
  "start_date": "2025-01-15",
  "end_date": "2025-04-15",
  "link_url": "https://example.com",
  "target_blank": false,
  "sort_order": 1
}
```

**Required Fields:**
- `restaurant_id`: Restaurant ID (integer, must exist)
- `title`: Banner title (string, max 255 characters)
- `banner_type`: Banner type (string: homepage, category, product, offers)
- `position`: Banner position (integer)

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "restaurant_id": ["The selected restaurant id is invalid."]
  }
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Unauthorized Error (401)
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### Forbidden Error (403)
```json
{
  "success": false,
  "message": "Insufficient permissions"
}
```

---

## Rate Limiting

The API implements rate limiting to ensure fair usage:
- **General endpoints**: 60 requests per minute
- **Search endpoints**: 30 requests per minute
- **File upload endpoints**: 10 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642234567
```

---

## Pagination

All list endpoints support pagination with the following response structure:
```json
{
  "current_page": 1,
  "data": [...],
  "first_page_url": "https://api.example.com/endpoint?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "https://api.example.com/endpoint?page=5",
  "next_page_url": "https://api.example.com/endpoint?page=2",
  "path": "https://api.example.com/endpoint",
  "per_page": 15,
  "prev_page_url": null,
  "to": 15,
  "total": 75
}
```

---

## File Upload

For endpoints that accept file uploads (images, icons), use `multipart/form-data`:

```bash
curl -X POST "https://yourdomain.com/api/restaurant-products" \
  -H "Authorization: Bearer {token}" \
  -F "name=Product Name" \
  -F "price=19.99" \
  -F "restaurant_id=1" \
  -F "category_id=2" \
  -F "image=@/path/to/image.jpg"
```

**Supported file types:**
- Images: JPG, JPEG, PNG, GIF
- Icons: SVG, PNG
- Maximum file size: 2MB for images, 1MB for icons

---

## Testing

You can test the API endpoints using:
- **Postman**: Import the collection
- **cURL**: Examples provided above
- **Insomnia**: REST client
- **Swagger/OpenAPI**: Available at `/api/documentation`

---

## Support

For API support and questions:
- Email: api-support@yourdomain.com
- Documentation: https://docs.yourdomain.com/api
- Status page: https://status.yourdomain.com
