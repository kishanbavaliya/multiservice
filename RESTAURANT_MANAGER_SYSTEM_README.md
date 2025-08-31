# Restaurant Manager Role System

This document explains how to set up and use the new restaurant manager role system in the multiservice application.

## Overview

The restaurant manager role system allows:
- **Admins** to manage all restaurants
- **Restaurant Managers** to manage only their assigned restaurant
- Proper access control and permissions for restaurant-related operations

## Features

### Roles and Permissions

#### Admin Role
- Can view and manage all restaurants
- Full access to all restaurant features
- Can assign restaurant managers to restaurants

#### Restaurant Manager Role
- Can only view and manage their assigned restaurant
- Limited access based on restaurant assignment
- Cannot access other restaurants

### Restaurant Management Features
- Restaurant Categories
- Restaurant Subcategories  
- Restaurant Serving Sizes
- Restaurant Modifiers
- Restaurant Modifier Groups
- Restaurant Products
- Restaurant Banners

## Setup Instructions

### 1. Run the Permissions Seeder

First, run the permissions seeder to create the new role and permissions:

```bash
php artisan db:seed --class=PermissionsTableSeeder
```

This will create:
- `restaurant-manager` role
- All restaurant-related permissions
- Assign permissions to appropriate roles

### 2. Register the Policy

Make sure the RestaurantPolicy is registered in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Restaurant::class => RestaurantPolicy::class,
];
```

### 3. Create Restaurant Managers

#### Option A: Using the Artisan Command

```bash
# Create a new manager for restaurant ID 1
php artisan restaurant:assign-manager 1

# Create with custom email and name
php artisan restaurant:assign-manager 1 --email="manager@restaurant.com" --name="John Manager"

# Assign existing user to restaurant
php artisan restaurant:assign-manager 1 --existing-user=5
```

#### Option B: Using the Seeder

```bash
php artisan db:seed --class=RestaurantManagerSeeder
```

This creates a manager for each existing restaurant.

### 4. Demo Data (Optional but Recommended)

To test the system with realistic data, you can run the demo data seeders:

#### Basic Demo Data
```bash
# Creates restaurants, categories, products, etc.
php artisan db:seed --class=RestaurantDemoDataSeeder
```

#### Complete Demo Setup (Recommended)
```bash
# Creates demo data + restaurant managers for each restaurant
php artisan db:seed --class=CompleteRestaurantDemoSeeder
```

This will create:
- 3 sample restaurants (Pizza Palace, Burger House, Sushi Express)
- Complete menu structure with categories and subcategories
- Sample products with realistic pricing
- Serving sizes and modifier options
- Promotional banners
- Restaurant manager accounts for each restaurant

#### View Demo Data
```bash
# Overview of all restaurants
php artisan restaurant:view-demo-data

# Detailed view of specific restaurant
php artisan restaurant:view-demo-data --restaurant-id=1
```

### 5. Update Routes (Optional)

You can add the restaurant access middleware to your routes for additional security:

```php
Route::middleware(['auth', 'restaurant.access'])->group(function () {
    // Restaurant routes
});
```

## Usage Examples

### For Restaurant Managers

Restaurant managers will see only their assigned restaurant in the navigation menu. They can:

1. **View Restaurant Dashboard**: See only their restaurant's data
2. **Manage Categories**: Add/edit categories for their restaurant
3. **Manage Products**: Add/edit products for their restaurant
4. **Manage Banners**: Upload and manage banners for their restaurant
5. **Manage Modifiers**: Create and manage product modifiers

### For Admins

Admins can:

1. **View All Restaurants**: See the complete list of restaurants
2. **Assign Managers**: Assign users as restaurant managers
3. **Manage Any Restaurant**: Edit any restaurant's settings
4. **Create New Restaurants**: Add new restaurants to the system

## Database Structure

### New Tables/Collumns

- `restaurant_user_roles` table (already exists)
- `assigned_manager_id` column in restaurants table (already exists)

### Key Relationships

- `User` ↔ `Restaurant` (through `RestaurantUserRole`)
- `Restaurant` has one `assigned_manager_id`

## Security Features

### Access Control

- Restaurant managers can only access their assigned restaurant
- All restaurant operations are validated against user permissions
- Middleware ensures proper access control

### Permission Checks

The system uses Spatie permissions to check:
- `view-restaurant`: Basic restaurant access
- `manage-assigned-restaurant`: Manage assigned restaurant
- `manage-all-restaurants`: Admin access to all restaurants

## Troubleshooting

### Common Issues

1. **"Restaurant manager role not found"**
   - Run `php artisan db:seed --class=PermissionsTableSeeder`

2. **"No restaurants found"**
   - Create restaurants first before running the seeder
   - Or run the demo seeder: `php artisan db:seed --class=RestaurantDemoDataSeeder`

3. **Permission denied errors**
   - Check if user has the correct role
   - Verify restaurant assignment

4. **Demo data not showing up**
   - Ensure all models have proper relationships
   - Check if database migrations are up to date
   - Run `php artisan migrate:fresh --seed` to reset and seed database

5. **Restaurant managers can't see their data**
   - Verify the user has the `restaurant-manager` role
   - Check if the restaurant is properly assigned to the user
   - Use `php artisan restaurant:list-managers` to verify assignments

### Debug Commands

```bash
# Check user roles
php artisan tinker
>>> Auth::user()->getRoleNames()

# Check restaurant assignments
php artisan tinker
>>> App\Models\Restaurant::with('assignedManager')->get()

# Check permissions
php artisan tinker
>>> Auth::user()->getAllPermissions()->pluck('name')
```

## API Endpoints

The system works with existing API endpoints. Restaurant managers will only see data for their assigned restaurant when using:

- `/api/restaurants`
- `/api/restaurant-categories`
- `/api/restaurant-products`
- etc.

## Frontend Integration

The navigation menu automatically shows/hides restaurant options based on user permissions:

- **Admin/City-Admin**: See all restaurant management options
- **Restaurant Manager**: See only restaurant management for their assigned restaurant
- **Other Users**: No restaurant management options visible

## Maintenance

### Adding New Restaurant Features

When adding new restaurant features:

1. Add permissions to `PermissionsTableSeeder`
2. Update `RestaurantPolicy` with new methods
3. Add menu items to the navigation
4. Update the trait if needed

### Removing Restaurant Managers

```bash
# Remove role from user
php artisan tinker
>>> $user = App\Models\User::find(1); $user->removeRole('restaurant-manager');

# Remove restaurant assignment
php artisan tinker
>>> $restaurant = App\Models\Restaurant::find(1); $restaurant->update(['assigned_manager_id' => null]);
```

## Support

For issues or questions about the restaurant manager system:

1. Check the logs for error messages
2. Verify database permissions and roles
3. Test with different user accounts
4. Check if all seeders have been run

## Future Enhancements

Potential improvements for the system:

1. **Multiple Restaurant Support**: Allow managers to manage multiple restaurants
2. **Role Hierarchy**: Create sub-roles within restaurant management
3. **Audit Logging**: Track all restaurant management actions
4. **Bulk Operations**: Allow admins to perform bulk operations on restaurants
5. **API Rate Limiting**: Implement rate limiting for restaurant managers
