# Restaurant Subcategory Management System

This document explains how to use the Restaurant Subcategory Management system that has been implemented for your Laravel application.

## Overview

The Restaurant Subcategory Management system allows you to create, edit, delete, and manage subcategories under restaurant categories. For example, under a "Pizza" category, you can have subcategories like "Veg Pizza", "Non-Veg Pizza", "Thin Crust", etc.

## Features

### 🏪 **Full CRUD Operations**
- **Create** new subcategories with detailed information
- **Read** subcategory listings with advanced filtering and search
- **Update** existing subcategory details
- **Delete** subcategories with confirmation

### 🔍 **Advanced Filtering & Search**
- Search by subcategory name or description
- Filter by restaurant
- Filter by parent category
- Filter by status (active/inactive)
- Filter by featured status
- Sort by various fields (name, sort order, creation date)

### 🖼️ **Image Management**
- Upload subcategory images (max 2MB)
- Upload subcategory icons (max 1MB)
- Automatic image storage in organized folders
- Image cleanup on deletion

### 📊 **Status Management**
- Toggle active/inactive status
- Toggle featured status
- Bulk status updates

### 🔗 **Relationship Management**
- Each subcategory belongs to a specific restaurant
- Each subcategory belongs to a specific category
- Validation ensures proper relationships
- Cascade deletion support

## Accessing the System

### Web Interface
Navigate to: `/restaurant-subcategories`

### API Endpoints
All endpoints are prefixed with `/api/restaurant-subcategories`

## Usage Guide

### 1. Creating a New Subcategory

1. **Access the Interface**
   - Go to `/restaurant-subcategories`
   - Click the "Add Subcategory" button

2. **Fill in the Details**
   - **Restaurant**: Select the restaurant this subcategory belongs to
   - **Category**: Select the parent category (automatically filtered by restaurant)
   - **Name**: Enter a descriptive name (e.g., "Veg Pizza", "Non-Veg Pizza")
   - **Description**: Optional description of the subcategory
   - **Image**: Optional subcategory image (max 2MB)
   - **Icon**: Optional subcategory icon (max 1MB)
   - **Sort Order**: Numeric value for ordering (lower numbers appear first)
   - **Active**: Check to make the subcategory active
   - **Featured**: Check to mark as featured

3. **Save**
   - Click "Create" to save the subcategory

### 2. Editing an Existing Subcategory

1. **Find the Subcategory**
   - Use search or filters to locate the subcategory
   - Click the edit icon (pencil) in the Actions column

2. **Modify Details**
   - Update any fields as needed
   - Upload new images if desired
   - Old images are automatically replaced

3. **Save Changes**
   - Click "Update" to save modifications

### 3. Managing Subcategory Status

#### Toggle Active Status
- Click the status badge in the Status column
- Toggle between Active (green) and Inactive (red)

#### Toggle Featured Status
- Click the featured badge in the Featured column
- Toggle between Featured (yellow) and Not Featured (gray)

### 4. Deleting a Subcategory

1. **Confirm Deletion**
   - Click the delete icon (trash) in the Actions column
   - Confirm the deletion in the modal

2. **Automatic Cleanup**
   - Associated images are automatically deleted
   - Database record is soft-deleted (can be restored if needed)

### 5. Filtering and Searching

#### Search
- Use the search box to find subcategories by name or description
- Search is real-time with 300ms debounce

#### Filters
- **Restaurant**: Filter by specific restaurant
- **Category**: Filter by parent category
- **Status**: Filter by active/inactive status
- **Featured**: Filter by featured status

#### Sorting
- **Name**: Alphabetical sorting
- **Sort Order**: Numeric sorting
- **Created Date**: Chronological sorting

## API Usage

### Authentication
All API endpoints require proper authentication. Include your API token in the request headers.

### Endpoints

#### List Subcategories
```http
GET /api/restaurant-subcategories
```

**Query Parameters:**
- `restaurant_id`: Filter by restaurant
- `category_id`: Filter by category
- `is_active`: Filter by status (true/false)
- `is_featured`: Filter by featured status (true/false)
- `search`: Search by name or description
- `sort_by`: Sort field (name, sort_order, created_at)
- `sort_direction`: Sort direction (asc/desc)
- `per_page`: Items per page (default: 15)

**Example:**
```bash
curl -X GET "https://yourdomain.com/api/restaurant-subcategories?restaurant_id=1&is_active=true" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

#### Create Subcategory
```http
POST /api/restaurant-subcategories
```

**Request Body:**
```json
{
  "restaurant_id": 1,
  "category_id": 5,
  "name": "Veg Pizza",
  "description": "Vegetarian pizza options",
  "sort_order": 1,
  "is_active": true,
  "is_featured": false
}
```

**File Upload:**
- Use `multipart/form-data` for image uploads
- `image`: Subcategory image file
- `icon`: Subcategory icon file

#### Get Subcategory Details
```http
GET /api/restaurant-subcategories/{id}
```

#### Update Subcategory
```http
PUT /api/restaurant-subcategories/{id}
```

#### Delete Subcategory
```http
DELETE /api/restaurant-subcategories/{id}
```

#### Get Subcategories by Restaurant
```http
GET /api/restaurant-subcategories/restaurant/{restaurantId}
```

#### Get Subcategories by Category
```http
GET /api/restaurant-subcategories/category/{categoryId}
```

#### Toggle Status
```http
PATCH /api/restaurant-subcategories/{id}/toggle-status
```

#### Toggle Featured
```http
PATCH /api/restaurant-subcategories/{id}/toggle-featured
```

## Database Structure

### Table: `restaurant_subcategories`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `restaurant_id` | bigint | Foreign key to restaurants table |
| `category_id` | bigint | Foreign key to restaurant_categories table |
| `name` | varchar(255) | Subcategory name |
| `description` | text | Optional description |
| `image_url` | varchar(255) | Path to subcategory image |
| `icon_url` | varchar(255) | Path to subcategory icon |
| `sort_order` | int | Sorting order (lower = first) |
| `is_active` | boolean | Active status |
| `is_featured` | boolean | Featured status |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |
| `deleted_at` | timestamp | Soft delete timestamp |

### Relationships

- **Restaurant**: `belongsTo(Restaurant::class)`
- **Category**: `belongsTo(RestaurantCategory::class)`
- **Products**: `hasMany(RestaurantProduct::class)`

### Indexes

- `restaurant_id` - For fast restaurant-based queries
- `category_id` - For fast category-based queries
- `restaurant_id + category_id` - Composite index for relationship queries
- `restaurant_id + category_id + is_active` - Composite index for active subcategories
- `sort_order` - For sorting operations

## File Storage

### Image Storage Paths
- **Subcategory Images**: `storage/app/public/restaurant-subcategories/images/`
- **Subcategory Icons**: `storage/app/public/restaurant-subcategories/icons/`

### File Management
- Images are automatically stored in organized folders
- Old images are automatically deleted when replaced
- File cleanup occurs on subcategory deletion
- Maximum file sizes enforced (2MB for images, 1MB for icons)

## Security Features

### Validation
- Required field validation
- File type validation (images only)
- File size limits
- Relationship validation (restaurant-category compatibility)

### Access Control
- Admin role required for web interface
- API authentication required for all endpoints
- Soft delete protection for data integrity

## Integration Points

### Restaurant Categories
- Subcategories are linked to restaurant categories
- Category view shows subcategory count
- Direct links from categories to subcategories

### Restaurant Products
- Products can be assigned to subcategories
- Subcategory view shows product count
- Hierarchical organization: Restaurant → Category → Subcategory → Product

### Restaurant Management
- Subcategories are restaurant-specific
- Restaurant view shows all associated subcategories
- Bulk operations by restaurant

## Best Practices

### Naming Conventions
- Use descriptive, clear names
- Avoid abbreviations
- Be consistent across similar subcategories

### Organization
- Use sort order for logical grouping
- Group related subcategories together
- Consider user experience when ordering

### Image Management
- Use appropriate image sizes
- Optimize images before upload
- Use consistent aspect ratios for icons

### Status Management
- Keep inactive subcategories for historical data
- Use featured status sparingly
- Regular review of active subcategories

## Troubleshooting

### Common Issues

1. **Images Not Displaying**
   - Check storage link: `php artisan storage:link`
   - Verify file permissions
   - Check image paths in database

2. **Validation Errors**
   - Ensure restaurant and category exist
   - Verify file types and sizes
   - Check required fields

3. **Relationship Errors**
   - Ensure category belongs to selected restaurant
   - Check foreign key constraints
   - Verify data integrity

### Performance Tips

1. **Database Optimization**
   - Use provided indexes
   - Limit result sets with pagination
   - Use eager loading for relationships

2. **Image Optimization**
   - Compress images before upload
   - Use appropriate formats (JPEG for photos, PNG for icons)
   - Consider implementing image resizing

3. **Caching**
   - Cache frequently accessed subcategories
   - Use Redis for session storage
   - Implement query result caching

## Support

For technical support or feature requests related to the Restaurant Subcategory Management system, please contact the development team or create an issue in the project repository.

## Changelog

### Version 1.0.0 (Current)
- Initial implementation
- Full CRUD operations
- Image management
- Advanced filtering and search
- API endpoints
- Web interface integration
- Relationship management
- Status management

