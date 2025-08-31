# Restaurant Modifier Management System

A comprehensive system for managing restaurant modifiers (customizations, add-ons, etc.) in Laravel with Livewire.

## Features

- ✅ **Full CRUD Operations** - Create, Read, Update, Delete modifiers
- ✅ **Restaurant-Specific Modifiers** - Each modifier belongs to a specific restaurant
- ✅ **Search & Filtering** - Search by name/description, filter by restaurant and status
- ✅ **Status Management** - Active/Inactive status with toggle functionality
- ✅ **Validation** - Unique names per restaurant, required fields validation
- ✅ **Pagination** - Efficient data loading with pagination
- ✅ **API Endpoints** - Complete RESTful API for external integrations
- ✅ **Responsive UI** - Modern, mobile-friendly interface with Tailwind CSS
- ✅ **Error Handling** - Comprehensive error handling and user feedback

## Database Schema

### `restaurant_modifiers` Table

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | BIGINT | PK, AI | Primary key |
| `name` | VARCHAR(100) | Required | Modifier name (e.g., "Extra Cheese", "No Onions") |
| `description` | TEXT | Nullable | Optional description |
| `restaurant_id` | BIGINT | FK, Required | Reference to restaurants table |
| `status` | TINYINT(1) | Default: true | 1 = Active, 0 = Inactive |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |
| `deleted_at` | TIMESTAMP | Nullable | Soft delete timestamp |

### Indexes
- `restaurant_id` - For efficient restaurant-based queries
- `status` - For status-based filtering
- `name` - For name-based searches
- Unique constraint on `(restaurant_id, name)` - Ensures unique names per restaurant

## Usage

### Web Interface

Access the modifier management interface at:
```
http://127.0.0.1:8000/restaurant-modifiers
```

#### Features:
- **Add New Modifier**: Click "Add Modifier" button
- **Edit Modifier**: Click the edit icon (pencil) in the actions column
- **Toggle Status**: Click the status toggle icon (checkmark/x-mark)
- **Delete Modifier**: Click the delete icon (trash) - only if not in use
- **Search**: Use the search box to find modifiers by name or description
- **Filter**: Use dropdown filters for restaurant and status
- **Sort**: Click column headers to sort data

### API Endpoints

#### Base URL: `/api/restaurant-modifiers`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | List all modifiers with pagination and filters |
| POST | `/` | Create a new modifier |
| GET | `/{id}` | Get specific modifier details |
| PUT | `/{id}` | Update a modifier |
| DELETE | `/{id}` | Delete a modifier |
| GET | `/restaurant/{restaurantId}` | Get modifiers for specific restaurant |
| PATCH | `/{id}/toggle-status` | Toggle modifier status |
| GET | `/search` | Search modifiers |
| GET | `/stats` | Get modifier statistics |

#### Example API Usage

```bash
# Get all modifiers
GET /api/restaurant-modifiers

# Create a new modifier
POST /api/restaurant-modifiers
{
    "name": "Extra Cheese",
    "description": "Add extra cheese to your order",
    "restaurant_id": 1,
    "status": true
}

# Get modifiers for a specific restaurant
GET /api/restaurant-modifiers/restaurant/1

# Search modifiers
GET /api/restaurant-modifiers/search?q=cheese&restaurant_id=1

# Toggle status
PATCH /api/restaurant-modifiers/1/toggle-status
```

## Model Methods

### RestaurantModifier Model

#### Relationships
- `restaurant()` - Belongs to Restaurant

#### Scopes
- `scopeActive()` - Filter active modifiers
- `scopeByRestaurant($restaurantId)` - Filter by restaurant
- `scopeOrdered()` - Order by name ascending

#### Accessors
- `getStatusTextAttribute()` - Returns "Active" or "Inactive"
- `getStatusColorAttribute()` - Returns "green" or "red"
- `getFormattedCreatedAtAttribute()` - Formatted creation date
- `getFormattedUpdatedAtAttribute()` - Formatted update date

#### Methods
- `canBeDeleted()` - Check if modifier can be deleted (not in use)

## Validation Rules

### Create/Update Validation
```php
[
    'name' => 'required|string|max:100',
    'description' => 'nullable|string',
    'restaurant_id' => 'required|exists:restaurants,id',
    'status' => 'boolean',
]
```

### Unique Constraints
- Modifier name must be unique within the same restaurant
- Restaurant must exist in the restaurants table

## Error Handling

The system includes comprehensive error handling:

- **Validation Errors**: Displayed in the form with field-specific messages
- **Database Errors**: Logged and displayed to users
- **API Errors**: Proper HTTP status codes and error messages
- **Soft Delete Protection**: Prevents deletion of modifiers in use

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Protection**: Uses Eloquent ORM with parameterized queries
- **XSS Protection**: Output is properly escaped in Blade templates

## Performance Optimizations

- **Eager Loading**: Restaurant relationships are loaded efficiently
- **Database Indexes**: Optimized queries with proper indexing
- **Pagination**: Large datasets are paginated for better performance
- **Caching**: Application caches are cleared when needed

## Installation & Setup

1. **Run Migration**:
   ```bash
   php artisan migrate
   ```

2. **Clear Caches**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Access the Interface**:
   ```
   http://127.0.0.1:8000/restaurant-modifiers
   ```

## Dependencies

- **Laravel** - PHP framework
- **Livewire** - Dynamic interfaces
- **Tailwind CSS** - Styling
- **Alpine.js** - JavaScript functionality (included with Livewire)

## File Structure

```
app/
├── Models/
│   └── RestaurantModifier.php
├── Http/
│   ├── Livewire/
│   │   └── RestaurantModifierLivewire.php
│   └── Controllers/
│       └── API/
│           └── RestaurantModifierController.php
resources/
└── views/
    └── livewire/
        └── restaurant-modifier-livewire.blade.php
database/
└── migrations/
    └── 2025_01_15_000010_create_restaurant_modifiers_table.php
routes/
├── web.php
└── api.php
```

## Troubleshooting

### Common Issues

1. **Restaurants not showing in dropdown**:
   - Ensure restaurants exist in the database
   - Check if restaurants have 'active' status
   - Clear application caches

2. **Validation errors**:
   - Check that restaurant_id exists
   - Ensure modifier name is unique within the restaurant
   - Verify all required fields are filled

3. **API errors**:
   - Check authentication if required
   - Verify request format and validation rules
   - Check server logs for detailed error messages

### Debug Mode

Enable debug logging in the Livewire component:
```php
logger('RestaurantModifierLivewire - Restaurants found: ' . $restaurants->count());
```

## Future Enhancements

- **Bulk Operations** - Select multiple modifiers for bulk actions
- **Import/Export** - CSV import/export functionality
- **Audit Trail** - Track changes to modifiers
- **Advanced Filtering** - Date range, usage statistics
- **Modifier Categories** - Group modifiers by type
- **Price Integration** - Add pricing to modifiers
- **Image Support** - Add images to modifiers

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connections and migrations
3. Clear application caches
4. Check browser console for JavaScript errors

