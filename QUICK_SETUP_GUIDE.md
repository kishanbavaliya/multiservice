# Quick Setup Guide - Restaurant Manager System

This guide will get you up and running with the restaurant manager system in under 5 minutes.

## 🚀 Quick Start (5 minutes)

### Step 1: Set up permissions and roles
```bash
php artisan db:seed --class=PermissionsTableSeeder
```

### Step 2: Create demo data and managers
```bash
php artisan db:seed --class=CompleteRestaurantDemoSeeder
```

### Step 3: Test the system
```bash
# View all demo data
php artisan restaurant:view-demo-data

# List all restaurant managers
php artisan restaurant:list-managers
```

## 🔐 Login Credentials

After running the demo seeder, you can log in with these accounts:

- **Pizza Palace Manager**: `manager.pizzapalace@demo.com` / `password`
- **Burger House Manager**: `manager.burgerhouse@demo.com` / `password`
- **Sushi Express Manager**: `manager.sushiexpress@demo.com` / `password`

## 📋 What Gets Created

### Restaurants
- **Pizza Palace** - Italian cuisine in New York
- **Burger House** - American burgers in Los Angeles  
- **Sushi Express** - Japanese sushi in Chicago

### Menu Structure
Each restaurant gets:
- 4 categories (Appetizers, Main Courses, Desserts, Beverages)
- 2-4 subcategories per category
- 6 sample products with realistic pricing
- 4 serving sizes (Small, Medium, Large, Family)
- 4 modifier groups (Toppings, Add-ons, Sauces, Sides)
- 3 promotional banners

### User Management
- Restaurant manager role with appropriate permissions
- Each restaurant gets an assigned manager
- Managers can only see their assigned restaurant

## 🧪 Testing the System

### 1. Login as Restaurant Manager
- Use any of the demo manager accounts above
- Navigate to the Restaurant section in the menu
- You should only see data for your assigned restaurant

### 2. Login as Admin
- Create an admin user or use existing admin account
- You should see all restaurants and full management options

### 3. Test Access Control
- Try accessing restaurant data from different accounts
- Verify that managers can only see their assigned restaurant
- Confirm admins can see and manage all restaurants

## 🛠️ Customization

### Add Your Own Restaurants
```bash
# Create a new restaurant manager
php artisan restaurant:assign-manager 1 --email="your@email.com" --name="Your Name"
```

### Modify Demo Data
Edit `database/seeders/RestaurantDemoDataSeeder.php` to:
- Change restaurant names and details
- Modify menu items and pricing
- Add more categories or products
- Customize modifier options

## 🔍 Troubleshooting

### Common Issues
1. **"Role not found"** → Run permissions seeder first
2. **"No restaurants"** → Run demo seeder
3. **"Permission denied"** → Check user roles and assignments

### Reset Everything
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=CompleteRestaurantDemoSeeder
```

## 📚 Next Steps

After testing the demo system:

1. **Customize the data** for your specific needs
2. **Add real restaurant data** using the management interface
3. **Configure additional permissions** if needed
4. **Integrate with your frontend** using the existing API endpoints

## 🆘 Need Help?

- Check the full README: `RESTAURANT_MANAGER_SYSTEM_README.md`
- Run debug commands to verify setup
- Check Laravel logs for error messages
- Ensure all database migrations are complete

---

**That's it!** You should now have a fully functional restaurant manager system with demo data and user accounts to test with.
