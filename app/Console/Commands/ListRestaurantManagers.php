<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Restaurant;
use App\Models\User;
use Spatie\Permission\Models\Role;

class ListRestaurantManagers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restaurant:list-managers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all restaurant managers and their assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Restaurant Manager Assignments');
        $this->info('=============================');

        // Get all restaurants with their managers
        $restaurants = Restaurant::with('assignedManager')->get();

        if ($restaurants->isEmpty()) {
            $this->warn('No restaurants found in the system.');
            return 0;
        }

        $tableData = [];

        foreach ($restaurants as $restaurant) {
            $manager = $restaurant->assignedManager;
            
            $tableData[] = [
                'Restaurant ID' => $restaurant->id,
                'Restaurant Name' => $restaurant->name,
                'Manager ID' => $manager ? $manager->id : 'N/A',
                'Manager Name' => $manager ? $manager->name : 'Not Assigned',
                'Manager Email' => $manager ? $manager->email : 'N/A',
                'Status' => $manager ? 'Assigned' : 'Unassigned',
            ];
        }

        $this->table(
            ['Restaurant ID', 'Restaurant Name', 'Manager ID', 'Manager Name', 'Manager Email', 'Status'],
            $tableData
        );

        // Show summary
        $assignedCount = $restaurants->whereNotNull('assigned_manager_id')->count();
        $unassignedCount = $restaurants->whereNull('assigned_manager_id')->count();
        $totalCount = $restaurants->count();

        $this->newLine();
        $this->info("Summary:");
        $this->info("- Total Restaurants: {$totalCount}");
        $this->info("- Assigned Managers: {$assignedCount}");
        $this->info("- Unassigned Restaurants: {$unassignedCount}");

        // Show users with restaurant-manager role
        $this->newLine();
        $this->info('Users with Restaurant Manager Role:');
        $this->info('==================================');

        $restaurantManagerRole = Role::where('name', 'restaurant-manager')->first();
        
        if ($restaurantManagerRole) {
            $managers = User::role('restaurant-manager')->get();
            
            if ($managers->isEmpty()) {
                $this->warn('No users found with restaurant-manager role.');
            } else {
                $managerData = [];
                
                foreach ($managers as $manager) {
                    $assignedRestaurants = Restaurant::where('assigned_manager_id', $manager->id)->get();
                    $restaurantNames = $assignedRestaurants->pluck('name')->implode(', ');
                    
                    $managerData[] = [
                        'Manager ID' => $manager->id,
                        'Manager Name' => $manager->name,
                        'Manager Email' => $manager->email,
                        'Assigned Restaurants' => $restaurantNames ?: 'None',
                    ];
                }
                
                $this->table(
                    ['Manager ID', 'Manager Name', 'Manager Email', 'Assigned Restaurants'],
                    $managerData
                );
            }
        } else {
            $this->error('Restaurant manager role not found. Please run PermissionsTableSeeder first.');
        }

        return 0;
    }
}
