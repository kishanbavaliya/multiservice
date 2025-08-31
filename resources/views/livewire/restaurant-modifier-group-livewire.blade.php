<div class="p-6 bg-white rounded-lg shadow-lg">
    @if(isset($error))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $error }}
        </div>
    @endif

    @if(session()->has('message'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Restaurant Modifier Groups</h2>
            <p class="text-gray-600 mt-1">Manage modifier groups for restaurant products</p>
        </div>
        <button wire:click="create" class="mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Modifier Group
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 space-y-4">
        <!-- Search -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input wire:model.debounce.300ms="search" type="text" placeholder="Search modifier groups..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <select wire:model="filter_restaurant" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Restaurants ({{ $restaurants->count() }})</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }} ({{ $restaurant->status }})</option>
                @endforeach
            </select>

            <select wire:model="filter_status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>

            <select wire:model="filter_selection_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="required">Required</option>
                <option value="optional">Optional</option>
            </select>

            <select wire:model="sort_by" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="name">Sort by Name</option>
                <option value="created_at">Sort by Date</option>
                <option value="status">Sort by Status</option>
            </select>
        </div>
    </div>

    <!-- Modifier Groups Table -->
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selection Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modifiers</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($modifierGroups as $modifierGroup)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $modifierGroup->name }}</div>
                                <div class="text-sm text-gray-500">{{ $modifierGroup->selection_description }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $modifierGroup->restaurant->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $modifierGroup->selection_type_color === 'red' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $modifierGroup->selection_type_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $modifierGroup->modifiers_count }} modifiers
                            </span>
                            @if($modifierGroup->modifiers->count() > 0)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $modifierGroup->modifiers->take(3)->pluck('name')->implode(', ') }}
                                    @if($modifierGroup->modifiers->count() > 3)
                                        +{{ $modifierGroup->modifiers->count() - 3 }} more
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $modifierGroup->status_color === 'green' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $modifierGroup->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="edit({{ $modifierGroup->id }})" class="text-blue-600 hover:text-blue-900">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                
                                <button wire:click="toggleStatus({{ $modifierGroup->id }})" class="text-green-600 hover:text-green-900">
                                    @if($modifierGroup->status)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                
                                <button wire:click="delete({{ $modifierGroup->id }})" 
                                        class="text-red-600 hover:text-red-900 {{ !$modifierGroup->canBeDeleted() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$modifierGroup->canBeDeleted() ? 'disabled' : '' }}
                                        title="{{ !$modifierGroup->canBeDeleted() ? 'Cannot delete - in use by products' : 'Delete modifier group' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No modifier groups found. {{ $search ? 'Try adjusting your search criteria.' : 'Start by adding your first modifier group.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(method_exists($modifierGroups, 'hasPages') && $modifierGroups->hasPages())
        <div class="mt-6">
            {{ $modifierGroups->links() }}
        </div>
    @endif

    <!-- Add/Edit Modifier Group Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $isEditing ? 'Edit Modifier Group' : 'Add New Modifier Group' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Restaurant Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant <span class="text-red-500">*</span> ({{ $restaurants->count() }} available)</label>
                            <select wire:model="restaurant_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Restaurant</option>
                                @foreach($restaurants as $restaurant)
                                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }} ({{ $restaurant->status }})</option>
                                @endforeach
                            </select>
                            @error('restaurant_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Group Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" placeholder="e.g., Toppings, Size Options, Extras" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Selection Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selection Type <span class="text-red-500">*</span></label>
                            <select wire:model="selection_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="optional">Optional</option>
                                <option value="required">Required</option>
                            </select>
                            @error('selection_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Required Count (only show when required) -->
                        @if($selection_type === 'required')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Required Count <span class="text-red-500">*</span></label>
                                <input wire:model="required_count" type="number" min="1" placeholder="Number of required selections" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                @error('required_count') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Modifier Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Modifiers</label>
                        @if($restaurant_id)
                            @if($availableModifiers->count() > 0)
                                <div class="border border-gray-300 rounded-lg p-4 max-h-60 overflow-y-auto">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($availableModifiers as $modifier)
                                            <label class="flex items-center space-x-3 p-2 border border-gray-200 rounded hover:bg-gray-50">
                                                <input wire:model="selected_modifiers" type="checkbox" value="{{ $modifier->id }}" 
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ $modifier->name }}</div>
                                                    @if($modifier->description)
                                                        <div class="text-xs text-gray-500">{{ Str::limit($modifier->description, 30) }}</div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500 mt-2">
                                    Selected: {{ count($selected_modifiers) }} modifier(s)
                                </div>
                            @else
                                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                    <p class="text-gray-500 text-center">No modifiers available for this restaurant. Please add modifiers first.</p>
                                </div>
                            @endif
                        @else
                            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <p class="text-gray-500 text-center">Please select a restaurant first to see available modifiers.</p>
                            </div>
                        @endif
                        @error('selected_modifiers') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input wire:model="status" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $isEditing ? 'Update Modifier Group' : 'Create Modifier Group' }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Modifier Group</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">Are you sure you want to delete this modifier group? This action cannot be undone.</p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
