<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="chatApp({{ auth()->id() }})">
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
                                <!-- Display chat thread or prompt to select a user -->
                                <template x-if="selectedUser">
                                    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200">
                                            Chat with <span x-text="selectedUser.name"></span>
                                        </h3>
                                        <div class="message-thread mt-4 max-h-[500px] overflow-y-auto"
                                             x-ref="messageThread">
                                            <template x-for="chat in chats" :key="chat.id">
                                                <div
                                                    :class="{'text-right': chat.sender_id === authUserId, 'text-left': chat.sender_id !== authUserId}">
                                                    <p :class="{'bg-blue-500 text-white': chat.sender_id === authUserId, 'bg-gray-200 text-gray-800': chat.sender_id !== authUserId}"
                                                       class="inline-block p-2 rounded-lg my-2">
                                                        <span x-text="chat.message"></span>
                                                    <p  class="text-sm text-gray-500 mt-1"
                                                      x-text="(chat.sender_id === authUserId && chat.is_read) ? 'Read' : ''"></p>
                                                    </p>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="mt-4 flex space-x-2">
                                            <textarea x-model="newMessage" @keydown.enter="sendMessage"
                                                      placeholder="Type a message..."
                                                      class="w-full p-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200"></textarea>
                                            <button @click="sendMessage"
                                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-blue-700 dark:hover:bg-blue-800">
                                                Send
                                            </button>
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
        function chatApp(authUserId) {
            return {
                authUserId: authUserId,
                selectedUser: null,
                chats: [],
                newMessage: '',
                async selectUser(id, name) {
                    this.selectedUser = {id, name};
                    console.log(id);
                    const baseUrl = `{{ route('get-chat', ['receiver_id' => ':id']) }}`;
                    const url = baseUrl.replace(':id', encodeURIComponent(id));

                    try {
                        const response = await fetch(url);
                        const data = await response.json();

                        if (data.status === 'success') {
                            this.chats = data.chats;
                            const conversation_Id = data.conversation_id;
                            this.initEchoListener(conversation_Id)
                        } else {
                            this.chats = [];
                        }
                    } catch (error) {
                        console.error('Error fetching chat messages:', error);
                        this.chats = [];
                    }
                },


                initEchoListener(conversationId) {
                    // Unsubscribe from previous channel if any
                    if (this.channel) {
                        this.channel.stopListening('NewMessageEvent');
                        this.channel = null;
                    }

                    // Subscribe to the private channel for the current conversation
                    this.channel = Echo.private(`new_message.${conversationId}`)
                        .listen('NewMessageEvent', (event) => {
                            console.log(event.id)
                            // Check if the incoming message is for the current conversation
                            if (this.chats.length > 0 && event.conversation_id === this.chats[0].conversation_id) {
                                this.chats.push({
                                    id: event.id,
                                    conversation_id: event.conversation_id,
                                    sender_id: event.sender_id,
                                    is_read: event.is_read,
                                    message: event.message,
                                });
                                this.scrollToBottom();
                            }
                        });
                },


                async sendMessage() {

                    if (!this.newMessage.trim()) return;

                    const url = `{{ route('send-message') }}`;
                    const payload = {
                        receiver_id: this.selectedUser.id,
                        message: this.newMessage.trim()
                    };

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            // this.chats.push({
                            //     sender_id: this.authUserId,
                            //     id: data.data.id,
                            //     conversation_id: data.data.conversation_id,
                            //     message: data.data.message
                            // });

                            this.newMessage = '';
                            this.scrollToBottom()
                        }
                        console.log(this.chats)
                    } catch (error) {
                        console.error('Error sending message:', error);
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.messageThread) {
                            this.$refs.messageThread.scrollTop = this.$refs.messageThread.scrollHeight;
                        }
                    });
                },
            }
        }
    </script>
</x-app-layout>
