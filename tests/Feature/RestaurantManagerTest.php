<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantUserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RestaurantManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions and roles
        $this->createPermissionsAndRoles();
    }

    private function createPermissionsAndRoles()
    {
        // Create restaurant permissions
        $permissions = [
            'view-restaurant',
            'view-restaurant-categories',
            'view-restaurant-subcategories',
            'view-restaurant-serving-sizes',
            'view-restaurant-modifiers',
            'view-restaurant-modifier-groups',
            'view-restaurant-products',
            'view-restaurant-banners',
            'manage-assigned-restaurant',
            'manage-all-restaurants',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create restaurant-manager role
        $restaurantManagerRole = Role::create(['name' => 'restaurant-manager']);
        $restaurantManagerRole->givePermissionTo([
            'view-restaurant',
            'view-restaurant-categories',
            'view-restaurant-subcategories',
            'view-restaurant-serving-sizes',
            'view-restaurant-modifiers',
            'view-restaurant-modifier-groups',
            'view-restaurant-products',
            'view-restaurant-banners',
            'manage-assigned-restaurant',
        ]);

        // Create admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);
    }

    /** @test */
    public function restaurant_manager_can_view_their_assigned_restaurant()
    {
        // Create a restaurant
        $restaurant = Restaurant::factory()->create();

        // Create a restaurant manager
        $manager = User::factory()->create();
        $manager->assignRole('restaurant-manager');

        // Assign manager to restaurant
        RestaurantUserRole::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $manager->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $restaurant->update(['assigned_manager_id' => $manager->id]);

        // Acting as the manager
        $this->actingAs($manager);

        // Manager should be able to view their restaurant
        $this->assertTrue($manager->can('view-restaurant'));
        $this->assertTrue($manager->can('manage-assigned-restaurant'));
    }

    /** @test */
    public function restaurant_manager_cannot_view_other_restaurants()
    {
        // Create two restaurants
        $restaurant1 = Restaurant::factory()->create();
        $restaurant2 = Restaurant::factory()->create();

        // Create a restaurant manager
        $manager = User::factory()->create();
        $manager->assignRole('restaurant-manager');

        // Assign manager only to restaurant1
        RestaurantUserRole::create([
            'restaurant_id' => $restaurant1->id,
            'user_id' => $manager->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $restaurant1->update(['assigned_manager_id' => $manager->id]);

        // Acting as the manager
        $this->actingAs($manager);

        // Manager should not be able to view restaurant2
        $this->assertFalse($manager->can('manage-all-restaurants'));
    }

    /** @test */
    public function admin_can_view_all_restaurants()
    {
        // Create restaurants
        $restaurant1 = Restaurant::factory()->create();
        $restaurant2 = Restaurant::factory()->create();

        // Create an admin
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Acting as the admin
        $this->actingAs($admin);

        // Admin should be able to view all restaurants
        $this->assertTrue($admin->can('view-restaurant'));
        $this->assertTrue($admin->can('manage-all-restaurants'));
    }

    /** @test */
    public function restaurant_manager_has_correct_permissions()
    {
        $manager = User::factory()->create();
        $manager->assignRole('restaurant-manager');

        $expectedPermissions = [
            'view-restaurant',
            'view-restaurant-categories',
            'view-restaurant-subcategories',
            'view-restaurant-serving-sizes',
            'view-restaurant-modifiers',
            'view-restaurant-modifier-groups',
            'view-restaurant-products',
            'view-restaurant-banners',
            'manage-assigned-restaurant',
        ];

        foreach ($expectedPermissions as $permission) {
            $this->assertTrue($manager->can($permission));
        }

        // Should not have admin permissions
        $this->assertFalse($manager->can('manage-all-restaurants'));
    }
}
