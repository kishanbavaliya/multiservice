<div class="p-6 bg-white rounded-lg shadow-sm">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Restaurant Categories</h2>
            <p class="text-gray-600">Manage categories for your restaurants</p>
        </div>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Category
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input wire:model.debounce.300ms="search" type="text" placeholder="Search categories..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Restaurant</label>
            <select wire:model="filter_restaurant" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Restaurants</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select wire:model="filter_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
            <select wire:model="filter_featured" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All</option>
                <option value="1">Featured</option>
                <option value="0">Not Featured</option>
            </select>
        </div>
        
        <div class="flex items-end">
            <button wire:click="$set('search', '')" wire:click="$set('filter_restaurant', '')" 
                    wire:click="$set('filter_status', '')" wire:click="$set('filter_featured', '')"
                    class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <!-- Categories Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('name')">
                        <div class="flex items-center">
                            Name
                            @if($sort_by === 'name')
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restaurant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image/Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('sort_order')">
                        <div class="flex items-center">
                            Sort Order
                            @if($sort_by === 'sort_order')
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                        <div class="flex items-center">
                            Created
                            @if($sort_by === 'created_at')
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($category->icon_url_formatted)
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ $category->icon_url_formatted }}" alt="{{ $category->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($category->description, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $category->restaurant->name }}</div>
                            <div class="text-sm text-gray-500">{{ $category->restaurant->cuisine_type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                @if($category->image_url_formatted)
                                    <img class="h-8 w-8 rounded object-cover" src="{{ $category->image_url_formatted }}" alt="Category Image">
                                @endif
                                @if($category->icon_url_formatted)
                                    <img class="h-8 w-8 rounded object-cover" src="{{ $category->icon_url_formatted }}" alt="Category Icon">
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $category->products_count }} products
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($category->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Featured
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $category->sort_order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $category->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button wire:click="toggleStatus({{ $category->id }})" 
                                        class="text-{{ $category->is_active ? 'red' : 'green' }}-600 hover:text-{{ $category->is_active ? 'red' : 'green' }}-900">
                                    {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="toggleFeatured({{ $category->id }})" 
                                        class="text-{{ $category->is_featured ? 'gray' : 'yellow' }}-600 hover:text-{{ $category->is_featured ? 'gray' : 'yellow' }}-900">
                                    {{ $category->is_featured ? 'Unfeature' : 'Feature' }}
                                </button>
                                <button wire:click="edit({{ $category->id }})" class="text-blue-600 hover:text-blue-900">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $category->id }})" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No categories found. Create your first category to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $categories->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $isEditing ? 'Edit Category' : 'Create New Category' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Restaurant *</label>
                            <select wire:model="restaurant_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Restaurant</option>
                                @foreach($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                                @endforeach
                            </select>
                            @error('restaurant_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                            <input wire:model="name" type="text" placeholder="Enter category name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="3" placeholder="Enter category description" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                            <input wire:model="image" type="file" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($image_url)
                                <div class="mt-2">
                                    <img src="{{ $image_url }}" alt="Current Image" class="h-20 w-20 object-cover rounded">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category Icon</label>
                            <input wire:model="icon" type="file" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('icon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @if($icon_url)
                                <div class="mt-2">
                                    <img src="{{ $icon_url }}" alt="Current Icon" class="h-20 w-20 object-cover rounded">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <input wire:model="sort_order" type="number" min="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('sort_order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input wire:model="is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center">
                                <input wire:model="is_featured" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Featured</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" wire:click="closeModal" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $isEditing ? 'Update Category' : 'Create Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDelete)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Category</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete this category? This action cannot be undone.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="$set('confirmingDelete', false)" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="confirmDelete" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
