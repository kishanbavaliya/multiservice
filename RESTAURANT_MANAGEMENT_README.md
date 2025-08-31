# Restaurant Management System

A comprehensive restaurant management system built with Laravel and Livewire, providing complete control over restaurants, categories, products, and promotional content.

## Features

### 🏪 Restaurant Management
- **Create, Update, Delete** restaurants with comprehensive details
- **Restaurant Information**: Name, description, cuisine type, contact details
- **Location Management**: Address, coordinates, delivery radius
- **Operating Hours**: Flexible JSON-based opening hours configuration
- **Delivery Settings**: Delivery fees, minimum orders, delivery time ranges
- **Status Management**: Active, Inactive, Suspended states
- **Verification System**: Mark restaurants as verified
- **Featured Restaurants**: Highlight special restaurants
- **Image Management**: Logo and banner uploads

### 👥 User Role Management
- **Super Admin**: Full access to all restaurants and features
- **Restaurant Admin**: Manage assigned restaurants
- **Restaurant Manager**: Limited management capabilities
- **Staff**: Basic access for daily operations
- **Role-based Permissions**: Granular access control
- **User Assignment**: Assign users to specific restaurants

### 📂 Category Management
- **Hierarchical Structure**: Categories → Subcategories → Products
- **Category Details**: Name, description, images, icons
- **Sorting & Organization**: Custom sort orders
- **Status Control**: Active/Inactive states
- **Featured Categories**: Highlight important categories
- **Image Management**: Category and icon uploads

### 🍽️ Product Management
- **Comprehensive Product Details**: Name, description, pricing, images
- **Pricing System**: Base price, discounts, original pricing
- **Customization Options**: Size options, toppings, customizations
- **Stock Management**: Track inventory levels
- **Availability Control**: Active/Inactive products
- **Product Features**: Featured, popular, recommended flags
- **Nutritional Information**: Calories, dietary info, allergens
- **Preparation Details**: Cooking time, ingredients

### 🎯 Banner Management
- **Promotional Banners**: Homepage, category, product, offer banners
- **Position Control**: Top, middle, bottom, sidebar placements
- **Scheduling**: Start/end dates for campaigns
- **Targeting**: Category, product, audience targeting
- **Analytics**: Click and view tracking
- **Mobile Optimization**: Separate mobile images
- **Link Management**: Custom URLs and call-to-action buttons

### 📊 Dashboard & Analytics
- **Restaurant Statistics**: Total products, categories, banners
- **Performance Metrics**: Ratings, reviews, order counts
- **User Management**: Assigned users and roles
- **Quick Actions**: Status toggles, feature management

## Database Structure

### Core Tables
- `restaurants` - Main restaurant information
- `restaurant_categories` - Food categories
- `restaurant_subcategories` - Subcategories under main categories
- `restaurant_products` - Menu items and products
- `restaurant_banners` - Promotional banners

- `restaurant_user_roles` - User role assignments

### Key Relationships
- Restaurants have many Categories
- Categories have many Subcategories
- Subcategories have many Products

- Users can be assigned to multiple restaurants with different roles

## Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Sample Data (Optional)
```bash
php artisan db:seed --class=RestaurantSeeder
```

### 3. Access the Admin Panel
Navigate to `/restaurants` in your admin panel to access the restaurant management interface.

## Usage Guide

### Creating a Restaurant
1. Click "Add Restaurant" button
2. Fill in basic information (name, description, cuisine type)
3. Add contact details (phone, email, website)
4. Set location information (address, coordinates)
5. Configure delivery settings (fees, minimum orders, delivery times)
6. Upload logo and banner images
7. Assign admin and manager users
8. Set status and feature flags
9. Save the restaurant

### Managing Categories
1. Select a restaurant from the list
2. Navigate to Categories section
3. Create new categories with names and descriptions
4. Upload category images and icons
5. Set sort order and status
6. Create subcategories under main categories

### Adding Products
1. Select a category and subcategory
2. Click "Add Product"
3. Enter product details (name, description, price)
4. Set availability and feature flags
5. Add nutritional information
6. Configure customization options
7. Upload product images
8. Set stock levels if needed

### Creating Banners
1. Navigate to Banners section
2. Click "Add Banner"
3. Enter banner title and description
4. Select banner type and position
5. Upload banner images (desktop and mobile)
6. Set scheduling dates
7. Configure targeting options
8. Add call-to-action links

### User Role Management
1. Go to User Management section
2. Select a user to assign roles
3. Choose restaurant and role type
4. Set permissions and access levels
5. Configure role expiration dates
6. Save user assignments

## API Endpoints

The system includes RESTful API endpoints for mobile app integration:

### Restaurants
- `GET /api/restaurants` - List all restaurants
- `POST /api/restaurants` - Create new restaurant
- `GET /api/restaurants/{id}` - Get restaurant details
- `PUT /api/restaurants/{id}` - Update restaurant
- `DELETE /api/restaurants/{id}` - Delete restaurant

### Categories
- `GET /api/restaurants/{id}/categories` - Get restaurant categories
- `POST /api/restaurants/{id}/categories` - Create category
- `GET /api/categories/{id}/subcategories` - Get subcategories
- `POST /api/categories/{id}/subcategories` - Create subcategory

### Products
- `GET /api/restaurants/{id}/products` - Get restaurant products
- `POST /api/restaurants/{id}/products` - Create product


### Banners
- `GET /api/restaurants/{id}/banners` - Get restaurant banners
- `POST /api/restaurants/{id}/banners` - Create banner

## Configuration

### File Upload Settings
Configure file upload paths in `config/filesystems.php`:
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### Image Storage
Restaurant images are stored in:
- Logos: `storage/app/public/restaurants/logos/`
- Banners: `storage/app/public/restaurants/banners/`
- Product Images: `storage/app/public/restaurants/products/`

### Permissions
The system uses Laravel's built-in role and permission system. Ensure users have appropriate roles:
- `super_admin` - Full access
- `restaurant_admin` - Restaurant management
- `restaurant_manager` - Limited management
- `staff` - Basic access

## Customization

### Adding New Fields
To add new fields to restaurants, categories, or products:

1. Create a migration:
```bash
php artisan make:migration add_new_field_to_restaurants_table
```

2. Update the model's `$fillable` array
3. Update the Livewire component
4. Update the view template

### Custom Validation Rules
Add custom validation rules in the Livewire components:
```php
protected $rules = [
    'name' => 'required|string|max:255|unique:restaurants,name,' . $this->restaurant_id,
    // Add your custom rules here
];
```

### Styling Customization
The interface uses Tailwind CSS. Customize styles by modifying the view files in `resources/views/livewire/`.

## Troubleshooting

### Common Issues

1. **Images not displaying**
   - Ensure storage link is created: `php artisan storage:link`
   - Check file permissions on storage directory
   - Verify image paths in database

2. **Permission errors**
   - Check user roles and permissions
   - Verify middleware configuration
   - Ensure proper role assignments

3. **Database connection issues**
   - Check database configuration in `.env`
   - Verify migration status
   - Check for foreign key constraints

### Performance Optimization

1. **Database Indexing**
   - Add indexes for frequently queried fields
   - Optimize complex queries with eager loading

2. **Image Optimization**
   - Compress uploaded images
   - Use appropriate image formats
   - Implement image resizing

3. **Caching**
   - Cache frequently accessed data
   - Use Redis for session storage
   - Implement query result caching

## Support

For technical support or feature requests, please contact the development team or create an issue in the project repository.

## License

This restaurant management system is proprietary software. Please refer to the license agreement for usage terms and conditions.
