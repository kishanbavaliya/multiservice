# Restaurant Modifier Group Management System

A comprehensive system for managing restaurant modifier groups (collections of modifiers with selection rules) in Laravel with Livewire.

## Features

- ✅ **Full CRUD Operations** - Create, Read, Update, Delete modifier groups
- ✅ **Multiple Modifier Selection** - Select multiple modifiers per group
- ✅ **Required/Optional Selection** - Configure if selections are required or optional
- ✅ **Required Count** - Set number of required selections when type is required
- ✅ **Restaurant-Specific Groups** - Each group belongs to a specific restaurant
- ✅ **Search & Filtering** - Search by name, filter by restaurant, status, and selection type
- ✅ **Status Management** - Active/Inactive status with toggle functionality
- ✅ **Validation** - Unique names per restaurant, required fields validation
- ✅ **Pagination** - Efficient data loading with pagination
- ✅ **API Endpoints** - Complete RESTful API for external integrations
- ✅ **Responsive UI** - Modern, mobile-friendly interface with Tailwind CSS
- ✅ **Error Handling** - Comprehensive error handling and user feedback

## Database Schema

### `restaurant_modifier_groups` Table

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | BIGINT | PK, AI | Primary key |
| `name` | VARCHAR(100) | Required | Group name (e.g., "Toppings", "Size Options") |
| `selection_type` | ENUM | Required | 'required' or 'optional' |
| `required_count` | INTEGER | Nullable | Number of required selections when type is required |
| `restaurant_id` | BIGINT | FK, Required | Reference to restaurants table |
| `status` | TINYINT(1) | Default: true | 1 = Active, 0 = Inactive |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |
| `deleted_at` | TIMESTAMP | Nullable | Soft delete timestamp |

### `restaurant_modifier_group_modifier` Pivot Table

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| `id` | BIGINT | PK, AI | Primary key |
| `modifier_group_id` | BIGINT | FK | Reference to modifier groups table |
| `modifier_id` | BIGINT | FK | Reference to modifiers table |
| `sort_order` | INTEGER | Default: 0 | Order of modifiers in the group |
| `created_at` | TIMESTAMP | Auto | Creation timestamp |
| `updated_at` | TIMESTAMP | Auto | Last update timestamp |

### Indexes
- `restaurant_id` - For efficient restaurant-based queries
- `status` - For status-based filtering
- `name` - For name-based searches
- `selection_type` - For selection type filtering
- Unique constraint on `(restaurant_id, name)` - Ensures unique names per restaurant
- Unique constraint on `(modifier_group_id, modifier_id)` - Prevents duplicate relationships

## Usage

### Web Interface

Access the modifier group management interface at:
```
http://127.0.0.1:8000/restaurant-modifier-groups
```

#### Features:
- **Add New Modifier Group**: Click "Add Modifier Group" button
- **Edit Modifier Group**: Click the edit icon (pencil) in the actions column
- **Toggle Status**: Click the status toggle icon (checkmark/x-mark)
- **Delete Modifier Group**: Click the delete icon (trash) - only if not in use
- **Search**: Use the search box to find groups by name
- **Filter**: Use dropdown filters for restaurant, status, and selection type
- **Sort**: Click column headers to sort data
- **Multiple Modifier Selection**: Select multiple modifiers in the modal

### API Endpoints

#### Base URL: `/api/restaurant-modifier-groups`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | List all modifier groups with pagination and filters |
| POST | `/` | Create a new modifier group |
| GET | `/{id}` | Get specific modifier group details |
| PUT | `/{id}` | Update a modifier group |
| DELETE | `/{id}` | Delete a modifier group |
| GET | `/restaurant/{restaurantId}` | Get modifier groups for specific restaurant |
| GET | `/type/{type}` | Get modifier groups by selection type |
| PATCH | `/{id}/toggle-status` | Toggle modifier group status |
| GET | `/search` | Search modifier groups |
| GET | `/stats` | Get modifier group statistics |
| GET | `/restaurant/{restaurantId}/available-modifiers` | Get available modifiers for a restaurant |

#### Example API Usage

```bash
# Get all modifier groups
GET /api/restaurant-modifier-groups

# Create a new modifier group
POST /api/restaurant-modifier-groups
{
    "name": "Pizza Toppings",
    "selection_type": "required",
    "required_count": 2,
    "restaurant_id": 1,
    "status": true,
    "modifier_ids": [1, 2, 3, 4]
}

# Get modifier groups for a specific restaurant
GET /api/restaurant-modifier-groups/restaurant/1

# Get modifier groups by selection type
GET /api/restaurant-modifier-groups/type/required

# Search modifier groups
GET /api/restaurant-modifier-groups/search?q=pizza&restaurant_id=1

# Toggle status
PATCH /api/restaurant-modifier-groups/1/toggle-status

# Get available modifiers for a restaurant
GET /api/restaurant-modifier-groups/restaurant/1/available-modifiers
```

## Model Methods

### RestaurantModifierGroup Model

#### Relationships
- `restaurant()` - Belongs to Restaurant
- `modifiers()` - BelongsToMany with RestaurantModifier

#### Scopes
- `scopeActive()` - Filter active modifier groups
- `scopeByRestaurant($restaurantId)` - Filter by restaurant
- `scopeBySelectionType($type)` - Filter by selection type
- `scopeOrdered()` - Order by name ascending

#### Accessors
- `getStatusTextAttribute()` - Returns "Active" or "Inactive"
- `getStatusColorAttribute()` - Returns "green" or "red"
- `getSelectionTypeTextAttribute()` - Returns "Required" or "Optional"
- `getSelectionTypeColorAttribute()` - Returns "red" or "blue"
- `getFormattedCreatedAtAttribute()` - Formatted creation date
- `getFormattedUpdatedAtAttribute()` - Formatted update date
- `getModifiersCountAttribute()` - Count of modifiers in the group
- `getSelectionDescriptionAttribute()` - Description of selection requirements

#### Methods
- `canBeDeleted()` - Check if modifier group can be deleted (not in use)
- `isRequired()` - Check if selection type is required
- `isOptional()` - Check if selection type is optional
- `getAvailableModifiers()` - Get available modifiers for the restaurant
- `syncModifiers($modifierIds)` - Sync modifiers with sort order

## Validation Rules

### Create/Update Validation
```php
[
    'name' => 'required|string|max:100',
    'selection_type' => 'required|in:required,optional',
    'required_count' => 'nullable|integer|min:1',
    'restaurant_id' => 'required|exists:restaurants,id',
    'status' => 'boolean',
    'modifier_ids' => 'array',
    'modifier_ids.*' => 'exists:restaurant_modifiers,id',
]
```

### Conditional Validation
- `required_count` is mandatory when `selection_type` is 'required'
- Modifier group name must be unique within the same restaurant
- Restaurant must exist in the restaurants table
- Modifier IDs must exist in the restaurant_modifiers table

## Error Handling

The system includes comprehensive error handling:

- **Validation Errors**: Displayed in the form with field-specific messages
- **Database Errors**: Logged and displayed to users
- **API Errors**: Proper HTTP status codes and error messages
- **Soft Delete Protection**: Prevents deletion of modifier groups in use
- **Conditional Validation**: Required count validation based on selection type

## Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Protection**: Uses Eloquent ORM with parameterized queries
- **XSS Protection**: Output is properly escaped in Blade templates

## Performance Optimizations

- **Eager Loading**: Restaurant and modifier relationships are loaded efficiently
- **Database Indexes**: Optimized queries with proper indexing
- **Pagination**: Large datasets are paginated for better performance
- **Caching**: Application caches are cleared when needed
- **Many-to-Many Optimization**: Efficient pivot table operations

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
   http://127.0.0.1:8000/restaurant-modifier-groups
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
│   ├── RestaurantModifierGroup.php
│   └── RestaurantModifier.php (updated)
├── Http/
│   ├── Livewire/
│   │   └── RestaurantModifierGroupLivewire.php
│   └── Controllers/
│       └── API/
│           └── RestaurantModifierGroupController.php
resources/
└── views/
    └── livewire/
        └── restaurant-modifier-group-livewire.blade.php
database/
└── migrations/
    ├── 2025_01_15_000010_create_restaurant_modifiers_table.php
    └── 2025_01_15_000011_create_restaurant_modifier_groups_table.php
routes/
├── web.php
└── api.php
```

## Use Cases

### Example Scenarios

1. **Pizza Restaurant**:
   - Group: "Pizza Toppings" (Required: 2 selections)
   - Modifiers: "Extra Cheese", "Pepperoni", "Mushrooms", "Olives"

2. **Burger Restaurant**:
   - Group: "Sauce Options" (Optional)
   - Modifiers: "Ketchup", "Mustard", "Mayo", "BBQ Sauce"

3. **Coffee Shop**:
   - Group: "Milk Options" (Required: 1 selection)
   - Modifiers: "Whole Milk", "Skim Milk", "Almond Milk", "Soy Milk"

## Troubleshooting

### Common Issues

1. **Restaurants not showing in dropdown**:
   - Ensure restaurants exist in the database
   - Check if restaurants have 'active' status
   - Clear application caches

2. **No modifiers available**:
   - Ensure modifiers exist for the selected restaurant
   - Check if modifiers have 'active' status
   - Verify restaurant-modifier relationship

3. **Validation errors**:
   - Check that restaurant_id exists
   - Ensure modifier group name is unique within the restaurant
   - Verify required_count when selection_type is 'required'
   - Verify all required fields are filled

4. **API errors**:
   - Check authentication if required
   - Verify request format and validation rules
   - Check server logs for detailed error messages

### Debug Mode

Enable debug logging in the Livewire component:
```php
logger('RestaurantModifierGroupLivewire - Restaurants found: ' . $restaurants->count());
logger('RestaurantModifierGroupLivewire - Available modifiers: ' . $availableModifiers->count());
```

## Future Enhancements

- **Bulk Operations** - Select multiple modifier groups for bulk actions
- **Import/Export** - CSV import/export functionality
- **Audit Trail** - Track changes to modifier groups
- **Advanced Filtering** - Date range, usage statistics
- **Modifier Group Categories** - Group modifier groups by type
- **Price Integration** - Add pricing to modifier groups
- **Image Support** - Add images to modifier groups
- **Sorting** - Drag-and-drop reordering of modifiers within groups
- **Templates** - Pre-defined modifier group templates

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connections and migrations
3. Clear application caches
4. Check browser console for JavaScript errors
5. Verify modifier-restaurant relationships

