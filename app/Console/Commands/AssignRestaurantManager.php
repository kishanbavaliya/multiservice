<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUserRole;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AssignRestaurantManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurant:assign-manager 
                            {restaurant_id : The ID of the restaurant}
                            {--email= : Email for the new manager}
                            {--name= : Name for the new manager}
                            {--password=password : Password for the new manager}
                            {--existing-user= : ID of existing user to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a restaurant manager to a specific restaurant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $restaurantId = $this->argument('restaurant_id');
        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant) {
            $this->error("Restaurant with ID {$restaurantId} not found.");
            return 1;
        }

        $this->info("Restaurant: {$restaurant->name}");

        // Get the restaurant-manager role
        $restaurantManagerRole = Role::where('name', 'restaurant-manager')->first();
        
        if (!$restaurantManagerRole) {
            $this->error('Restaurant manager role not found. Please run PermissionsTableSeeder first.');
            return 1;
        }

        // Check if restaurant already has a manager
        if ($restaurant->assigned_manager_id) {
            $existingManager = User::find($restaurant->assigned_manager_id);
            $this->warn("Restaurant already has a manager: {$existingManager->name} ({$existingManager->email})");
            
            if (!$this->confirm('Do you want to replace the existing manager?')) {
                return 0;
            }
        }

        // Handle existing user assignment
        if ($existingUserId = $this->option('existing-user')) {
            $existingUser = User::find($existingUserId);
            
            if (!$existingUser) {
                $this->error("User with ID {$existingUserId} not found.");
                return 1;
            }

            $this->assignUserToRestaurant($existingUser, $restaurant, $restaurantManagerRole);
            return 0;
        }

        // Create new manager user
        $email = $this->option('email') ?: 'manager.' . strtolower(str_replace(' ', '', $restaurant->name)) . '@example.com';
        $name = $this->option('name') ?: $restaurant->name . ' Manager';
        $password = $this->option('password');

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");
            return 1;
        }

        $manager = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->assignUserToRestaurant($manager, $restaurant, $restaurantManagerRole);

        $this->info("Restaurant manager created successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");

        return 0;
    }

    /**
     * Assign a user to a restaurant
     */
    private function assignUserToRestaurant(User $user, Restaurant $restaurant, Role $role)
    {
        // Assign the restaurant-manager role
        $user->assignRole($role);

        // Remove existing restaurant user role if exists
        RestaurantUserRole::where('restaurant_id', $restaurant->id)
            ->where('user_id', $user->id)
            ->delete();

        // Create the restaurant user role relationship
        RestaurantUserRole::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => 'manager',
            'is_active' => true,
            'permissions' => json_encode([
                'view-restaurant',
                'view-restaurant-categories',
                'view-restaurant-subcategories',
                'view-restaurant-serving-sizes',
                'view-restaurant-modifiers',
                'view-restaurant-modifier-groups',
                'view-restaurant-products',
                'view-restaurant-banners',
                'manage-assigned-restaurant',
            ]),
        ]);

        // Update the restaurant with the assigned manager
        $restaurant->update([
            'assigned_manager_id' => $user->id,
        ]);

        $this->info("User {$user->name} ({$user->email}) assigned to restaurant {$restaurant->name}");
    }
}
