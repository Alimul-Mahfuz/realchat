<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="chatApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="grid grid-cols-12 gap-4">
                            <!-- User List Column (3 of 12 columns) -->
                            <div class="col-span-12 md:col-span-3 space-y-4">
                                @forelse($users as $user)
                                    <div class="bg-white dark:bg-gray-600 shadow-md rounded-lg p-4 cursor-pointer"
                                         @click="selectUser({{ $user->id }}, '{{ $user->name }}')">
                                        <h3 class="text-lg dark:text-white font-semibold">{{ $user->name }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                    </div>
                                @empty
                                    <p>No users found.</p>
                                @endforelse
                            </div>

                            <!-- Chat Thread Column (9 of 12 columns) -->
                            <div class="col-span-12 md:col-span-9">
                                <template x-if="selectedUser">
                                    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                            Chat with <span x-text="selectedUser.name"></span>
                                        </h3>
                                        <!-- You can replace this with the chat messages -->
                                        <div class="mt-4">
                                            <p class="text-gray-600 dark:text-gray-400">
                                                Chat thread for <span x-text="selectedUser.name"></span> will appear here.
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Message to display if no user is selected -->
                                <template x-if="!selectedUser">
                                    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4">
                                        <p class="text-gray-600 dark:text-gray-400">
                                            Select a user to view the chat thread.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function chatApp() {
            return {
                selectedUser: null,
                selectUser(id, name) {
                    this.selectedUser = { id, name };
                    // You can make an AJAX request here to load the chat thread for the selected user
                    // For example:
                    // fetch(`/api/chat-threads/${id}`)
                    //     .then(response => response.json())
                    //     .then(data => {
                    //         // Populate chat thread here
                    //     });
                }
            }
        }
    </script>
</x-app-layout>
