# Restaurant Serving Size Management System

A comprehensive system for managing restaurant serving sizes in Laravel with Livewire and API endpoints.

## Features

### Core Functionality
- ✅ **Add new serving sizes** - Create both global and restaurant-specific serving sizes
- ✅ **Edit serving sizes** - Update existing serving size details
- ✅ **Delete serving sizes** - Remove serving sizes (with safety checks)
- ✅ **List all serving sizes** - Paginated listing with search and filters
- ✅ **Status management** - Toggle active/inactive status
- ✅ **Type management** - Global vs Restaurant-specific serving sizes

### Advanced Features
- ✅ **Search functionality** - Search by name and description
- ✅ **Filtering** - Filter by restaurant, status, and type
- ✅ **Sorting** - Sort by name, date, and status
- ✅ **Usage tracking** - Track which products use each serving size
- ✅ **Safety checks** - Prevent deletion of serving sizes in use
- ✅ **API endpoints** - Full RESTful API for mobile apps
- ✅ **Statistics** - Usage statistics and analytics

## Database Schema

### `restaurant_serving_sizes` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT (PK, AI) | Primary key |
| `restaurant_id` | BIGINT (FK, nullable) | Reference to restaurants table (null for global) |
| `name` | VARCHAR(100) | Serving size name (e.g., Small, Medium, Large) |
| `description` | TEXT (nullable) | Optional description |
| `status` | TINYINT(1) | 1 = Active, 0 = Inactive |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Last update timestamp |
| `deleted_at` | TIMESTAMP (nullable) | Soft delete timestamp |

### Indexes
- `restaurant_id` + `status` (composite index)
- `name` (for search optimization)
- `status` (for filtering)
- Unique constraint on `restaurant_id` + `name`

## Models

### RestaurantServingSize Model

**Location:** `app/Models/RestaurantServingSize.php`

**Key Features:**
- Soft deletes support
- Relationships with Restaurant and RestaurantProduct models
- Scopes for filtering (active, byRestaurant, global, ordered)
- Accessors for formatted data (status_text, type, etc.)
- Methods for usage tracking and safety checks

**Key Methods:**
- `isGlobal()` - Check if serving size is global
- `isRestaurantSpecific()` - Check if serving size is restaurant-specific
- `getUsageCount()` - Get number of products using this serving size
- `canBeDeleted()` - Check if serving size can be safely deleted

## Livewire Component

### RestaurantServingSizeLivewire

**Location:** `app/Http/Livewire/RestaurantServingSizeLivewire.php`

**Features:**
- Full CRUD operations
- Real-time search and filtering
- Modal-based forms
- Status toggling
- Pagination
- Error handling

**Key Properties:**
- Form fields: `serving_size_id`, `restaurant_id`, `name`, `description`, `status`, `is_global`
- Filters: `search`, `filter_restaurant`, `filter_status`, `filter_type`
- Sorting: `sort_by`, `sort_direction`
- UI state: `showModal`, `isEditing`, `confirmingDelete`

## Blade View

### Restaurant Serving Size Management UI

**Location:** `resources/views/livewire/restaurant-serving-size-livewire.blade.php`

**Features:**
- Modern, responsive design with Tailwind CSS
- Data table with sorting and pagination
- Search and filter controls
- Modal forms for add/edit operations
- Delete confirmation modal
- Status indicators and badges
- Usage count display

## API Endpoints

### Base URL: `/api/restaurant-serving-sizes`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | List all serving sizes (with filters) |
| POST | `/` | Create new serving size |
| GET | `/{id}` | Get specific serving size |
| PUT | `/{id}` | Update serving size |
| DELETE | `/{id}` | Delete serving size |
| GET | `/restaurant/{restaurantId}` | Get serving sizes for specific restaurant |
| GET | `/global` | Get global serving sizes |
| GET | `/available/{restaurantId}` | Get all available serving sizes for restaurant |
| PATCH | `/{id}/toggle-status` | Toggle serving size status |
| GET | `/search` | Search serving sizes |
| GET | `/stats` | Get serving size statistics |

### API Controller

**Location:** `app/Http/Controllers/API/RestaurantServingSizeController.php`

**Features:**
- Full CRUD operations
- Validation and error handling
- Unique name validation per restaurant
- Safety checks for deletion
- Comprehensive filtering and search
- Statistics endpoint

## Routes

### Web Routes
```php
Route::get('restaurant-serving-sizes', RestaurantServingSizeLivewire::class)->name('restaurant-serving-sizes');
```

### API Routes
```php
Route::prefix('restaurant-serving-sizes')->group(function () {
    // All API endpoints listed above
});
```

## Usage Examples

### Creating a Global Serving Size
```php
$servingSize = RestaurantServingSize::create([
    'restaurant_id' => null, // Global
    'name' => 'Large',
    'description' => 'Large portion size',
    'status' => true
]);
```

### Creating a Restaurant-Specific Serving Size
```php
$servingSize = RestaurantServingSize::create([
    'restaurant_id' => 1,
    'name' => 'Family Pack',
    'description' => 'Extra large family portion',
    'status' => true
]);
```

### Getting Available Serving Sizes for a Restaurant
```php
// Get both global and restaurant-specific serving sizes
$availableSizes = RestaurantServingSize::global()
    ->active()
    ->ordered()
    ->get()
    ->merge(
        RestaurantServingSize::byRestaurant($restaurantId)
            ->active()
            ->ordered()
            ->get()
    );
```

### Checking if Serving Size Can Be Deleted
```php
$servingSize = RestaurantServingSize::find(1);
if ($servingSize->canBeDeleted()) {
    $servingSize->delete();
} else {
    // Handle error - serving size is in use
}
```

## API Response Examples

### List Serving Sizes
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "restaurant_id": null,
                "name": "Small",
                "description": "Small portion",
                "status": true,
                "type": "Global",
                "usage_count": 5
            }
        ],
        "total": 10
    },
    "message": "Serving sizes retrieved successfully"
}
```

### Create Serving Size
```json
{
    "success": true,
    "data": {
        "id": 2,
        "restaurant_id": 1,
        "name": "Medium",
        "description": "Medium portion",
        "status": true,
        "restaurant": {
            "id": 1,
            "name": "Pizza Palace"
        }
    },
    "message": "Serving size created successfully"
}
```

## Frontend Integration

### Accessing the Management Interface
Navigate to: `http://your-domain/admin/restaurant-serving-sizes`

### Key UI Features
1. **Add Serving Size Button** - Opens modal to create new serving size
2. **Search Bar** - Real-time search by name and description
3. **Filter Dropdowns** - Filter by restaurant, status, and type
4. **Sort Options** - Sort by name, date, or status
5. **Action Buttons** - Edit, toggle status, and delete for each serving size
6. **Usage Indicators** - Shows how many products use each serving size
7. **Type Badges** - Visual indicators for global vs restaurant-specific

### Form Fields
- **Type Selection** - Radio buttons for Global vs Restaurant Specific
- **Restaurant Selection** - Dropdown (only shown for restaurant-specific)
- **Name** - Required text field (max 100 characters)
- **Description** - Optional textarea
- **Status** - Checkbox for active/inactive

## Security Features

1. **Unique Name Validation** - Prevents duplicate names per restaurant
2. **Usage Safety Checks** - Prevents deletion of serving sizes in use
3. **Soft Deletes** - Preserves data integrity
4. **Input Validation** - Server-side validation for all inputs
5. **Error Handling** - Comprehensive error messages and logging

## Performance Optimizations

1. **Database Indexes** - Optimized for common queries
2. **Eager Loading** - Loads relationships efficiently
3. **Pagination** - Handles large datasets
4. **Debounced Search** - Reduces API calls during typing
5. **Caching** - Model relationships cached appropriately

## Error Handling

The system includes comprehensive error handling:

1. **Validation Errors** - Clear feedback for form validation
2. **Database Errors** - Graceful handling of database operations
3. **Usage Conflicts** - Clear messages when deletion is blocked
4. **API Errors** - Standardized error responses
5. **Frontend Errors** - User-friendly error messages

## Future Enhancements

Potential improvements for the system:

1. **Bulk Operations** - Bulk create, update, or delete
2. **Import/Export** - CSV import/export functionality
3. **Audit Trail** - Track changes and modifications
4. **Advanced Analytics** - Usage trends and insights
5. **Template System** - Predefined serving size templates
6. **Multi-language Support** - Internationalization
7. **Image Support** - Visual representations of serving sizes

## Support

For technical support or questions about the Restaurant Serving Size Management system, please refer to the Laravel documentation or contact the development team.

