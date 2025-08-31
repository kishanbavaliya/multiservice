# Restaurant Categories API Testing Guide

## Overview
The Restaurant Categories API provides comprehensive endpoints for managing restaurant categories. This guide shows how to test each endpoint.

## Base URL
```
https://yourdomain.com/api/restaurant-categories
```

## Authentication
All endpoints require authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your_access_token}
```

---

## 1. Get All Categories (GET /)

### Test with cURL:
```bash
curl -X GET "https://yourdomain.com/api/restaurant-categories" \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

### Test with Query Parameters:
```bash
# Get categories for a specific restaurant
curl -X GET "https://yourdomain.com/api/restaurant-categories?restaurant_id=1" \
  -H "Authorization: Bearer {your_token}"

# Search categories
curl -X GET "https://yourdomain.com/api/restaurant-categories?search=pizza" \
  -H "Authorization: Bearer {your_token}"

# Filter by active status
curl -X GET "https://yourdomain.com/api/restaurant-categories?is_active=true" \
  -H "Authorization: Bearer {your_token}"

# Filter by featured status
curl -X GET "https://yourdomain.com/api/restaurant-categories?is_featured=true" \
  -H "Authorization: Bearer {your_token}"

# Pagination
curl -X GET "https://yourdomain.com/api/restaurant-categories?page=1&per_page=10" \
  -H "Authorization: Bearer {your_token}"

# Sorting
curl -X GET "https://yourdomain.com/api/restaurant-categories?sort_by=name&sort_direction=asc" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
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

---

## 2. Get Categories by Restaurant (GET /restaurant/{restaurantId})

### Test with cURL:
```bash
curl -X GET "https://yourdomain.com/api/restaurant-categories/restaurant/1" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
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

---

## 3. Search Categories (GET /search)

### Test with cURL:
```bash
# Basic search
curl -X GET "https://yourdomain.com/api/restaurant-categories/search?query=pizza" \
  -H "Authorization: Bearer {your_token}"

# Search with filters
curl -X GET "https://yourdomain.com/api/restaurant-categories/search?query=pizza&restaurant_id=1&is_active=true" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
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

---

## 4. Get Category Statistics (GET /stats)

### Test with cURL:
```bash
curl -X GET "https://yourdomain.com/api/restaurant-categories/stats" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
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

## 5. Create Category (POST /)

### Test with cURL:
```bash
curl -X POST "https://yourdomain.com/api/restaurant-categories" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "restaurant_id": 1,
    "name": "Desserts",
    "description": "Sweet treats and desserts",
    "is_active": true,
    "is_featured": false,
    "sort_order": 5
  }'
```

### Expected Response:
```json
{
  "success": true,
  "data": {
    "id": 26,
    "restaurant_id": 1,
    "name": "Desserts",
    "description": "Sweet treats and desserts",
    "is_active": true,
    "is_featured": false,
    "sort_order": 5,
    "created_at": "2025-01-15T12:00:00.000000Z",
    "updated_at": "2025-01-15T12:00:00.000000Z",
    "restaurant": {
      "id": 1,
      "name": "Pizza Palace"
    }
  },
  "message": "Category created successfully"
}
```

---

## 6. Update Category (PUT /{id})

### Test with cURL:
```bash
curl -X PUT "https://yourdomain.com/api/restaurant-categories/26" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sweet Desserts",
    "description": "Updated description for sweet treats"
  }'
```

### Expected Response:
```json
{
  "success": true,
  "data": {
    "id": 26,
    "name": "Sweet Desserts",
    "description": "Updated description for sweet treats",
    "updated_at": "2025-01-15T12:30:00.000000Z"
  },
  "message": "Category updated successfully"
}
```

---

## 7. Toggle Category Status (PATCH /{id}/toggle-status)

### Test with cURL:
```bash
curl -X PATCH "https://yourdomain.com/api/restaurant-categories/26/toggle-status" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
```json
{
  "success": true,
  "data": {
    "id": 26,
    "is_active": false,
    "updated_at": "2025-01-15T12:35:00.000000Z"
  },
  "message": "Category status updated successfully"
}
```

---

## 8. Toggle Featured Status (PATCH /{id}/toggle-featured)

### Test with cURL:
```bash
curl -X PATCH "https://yourdomain.com/api/restaurant-categories/26/toggle-featured" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
```json
{
  "success": true,
  "data": {
    "id": 26,
    "is_featured": true,
    "updated_at": "2025-01-15T12:40:00.000000Z"
  },
  "message": "Category featured status updated successfully"
}
```

---

## 9. Delete Category (DELETE /{id})

### Test with cURL:
```bash
curl -X DELETE "https://yourdomain.com/api/restaurant-categories/26" \
  -H "Authorization: Bearer {your_token}"
```

### Expected Response:
```json
{
  "success": true,
  "message": "Category deleted successfully"
}
```

---

## Testing with Postman

1. **Import Collection**: Create a new collection in Postman
2. **Set Environment Variables**:
   - `base_url`: `https://yourdomain.com/api`
   - `auth_token`: `{your_bearer_token}`
3. **Create Requests** for each endpoint
4. **Test Authentication**: Ensure all requests include the Authorization header

## Testing with Insomnia

1. **Create New Project**: Start a new REST project
2. **Set Base URL**: `https://yourdomain.com/api/restaurant-categories`
3. **Add Headers**: Authorization: Bearer {token}
4. **Test Each Endpoint**: Create requests for all CRUD operations

## Error Handling

### Validation Error (422):
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

### Not Found Error (404):
```json
{
  "success": false,
  "message": "Category not found"
}
```

### Unauthorized Error (401):
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

---

## Performance Testing

### Load Testing with Apache Bench:
```bash
# Test GET endpoint with 100 requests, 10 concurrent
ab -n 100 -c 10 -H "Authorization: Bearer {token}" \
   "https://yourdomain.com/api/restaurant-categories"

# Test search endpoint
ab -n 50 -c 5 -H "Authorization: Bearer {token}" \
   "https://yourdomain.com/api/restaurant-categories/search?query=pizza"
```

### Response Time Expectations:
- **GET requests**: < 200ms
- **POST/PUT requests**: < 500ms
- **Search requests**: < 300ms
- **Statistics requests**: < 400ms

---

## Security Testing

1. **Authentication**: Test without token (should return 401)
2. **Authorization**: Test with insufficient permissions
3. **Input Validation**: Test with malformed data
4. **SQL Injection**: Test with suspicious input
5. **Rate Limiting**: Test API rate limits

---

## Monitoring

### Key Metrics to Track:
- Response times
- Error rates
- Request volume
- Authentication failures
- Database query performance

### Logs to Monitor:
- API access logs
- Error logs
- Database query logs
- Authentication logs

---

## Troubleshooting

### Common Issues:

1. **404 Not Found**: Check if the route is properly registered
2. **500 Internal Error**: Check server logs and database connections
3. **Validation Errors**: Verify request payload format
4. **Authentication Issues**: Check token validity and expiration
5. **Performance Issues**: Monitor database queries and caching

### Debug Commands:
```bash
# Check routes
php artisan route:list --path=api/restaurant-categories

# Clear route cache
php artisan route:clear

# Check database
php artisan tinker --execute="echo App\Models\RestaurantCategory::count();"

# Test controller
php artisan tinker --execute="echo (new App\Http\Controllers\API\RestaurantCategoryController)->index(new Illuminate\Http\Request());"
```

---

## Support

For API support and questions:
- Email: api-support@yourdomain.com
- Documentation: https://docs.yourdomain.com/api
- Status page: https://status.yourdomain.com
