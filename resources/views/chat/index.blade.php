<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Chat') }}
        </h2>
    </x-slot>

    <div class="flex justify-center items-center h-[85vh] bg-gray-200 dark:bg-gray-900">
        <div class="w-full max-w-6xl h-full flex shadow-lg border dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg overflow-hidden">

            <!-- Left Sidebar (Users) -->
            <div class="w-1/3 bg-gray-100 dark:bg-gray-900 border-r border-gray-300 dark:border-gray-700 overflow-y-auto">
                <div class="p-4 font-bold text-lg border-b dark:border-gray-600">Users</div>
                <ul>
                    @foreach ($users as $user)
                        @if ($user->id !== auth()->id())
                            <li class="user-item cursor-pointer px-4 py-3 hover:bg-gray-200 dark:hover:bg-gray-800 border-b dark:border-gray-700" data-id="{{ $user->id }}">
                                {{ $user->name }}
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <!-- Right Chat Section -->
            <div class="w-2/3 flex flex-col">
                <!-- Messages -->
                <div id="chat-box" class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
                    <!-- Messages will be loaded here -->
                </div>

                <!-- Chat Input -->
                <form id="chat-form" class="flex items-center gap-2 p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-900">
                    @csrf
                    <input type="hidden" name="to_user_id" id="to_user_id">
                    <input type="text" name="message" id="message" placeholder="Type a message" class="flex-1 px-4 py-2 rounded-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600">Send</button>
                </form>
            </div>
        </div>
    </div>


@push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const currentUserId = {{ auth()->id() }};
                let selectedUserId = null;
                //make select
                document.querySelectorAll('.user-item').forEach(function (item) {
                    item.addEventListener('click', function () {
                        selectedUserId = this.getAttribute('data-id');
                        document.getElementById('to_user_id').value = selectedUserId;
                        loadMessages();

                        fetch(`/mark-as-read/${selectedUserId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                    });
                });

                // message load for each users
                function loadMessages() {
                    console.log('fetching messages for', selectedUserId);
                    if (!selectedUserId) return;

                    fetch(`/messages/${selectedUserId}`)
                        .then(res => res.json())
                        .then(data => {
                            const chatBox = document.getElementById('chat-box');
                            chatBox.innerHTML = '';
                            data.forEach(msg => {
                                const align = msg.from_user_id == currentUserId ? 'text-right' : 'text-left';
                                const bgColor = msg.from_user_id == currentUserId
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 dark:text-white';

                                chatBox.innerHTML += `
                                <div class="${align}">
                                    <div class="inline-block px-4 py-2 my-1 rounded-lg ${bgColor} max-w-[75%]">
                                        ${msg.message}
                                    </div>
                                </div>`;
                            });
                            chatBox.scrollTop = chatBox.scrollHeight;
                        })
                        .catch(err => console.error('Fetch error:', err));
                }

                // msg send to eachother
                document.getElementById('chat-form').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('/messages/send', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    })
                        .then(() => {
                            document.getElementById('message').value = '';
                            loadMessages();
                        });
                });
            });


            // notfiy automatic for user's
            const loggedInUserId = {{ auth()->id() }};

            setInterval(() => {
                fetch(`/check-new-messages`)
                    .then(res => res.json())
                    .then(data => {

                        // Clear all current badges
                        document.querySelectorAll('.message-count-badge').forEach(el => el.remove());

                        data.forEach(msg => {
                            const userItem = document.querySelector(`.user-item[data-id='${msg.from_user_id}']`);
                            if (userItem) {
                                const badge = document.createElement('span');
                                badge.className = "message-count-badge bg-red-500 text-red text-xs font-bold px-2 py-1 rounded-full ml-2";
                                badge.textContent = msg.count;
                                userItem.appendChild(badge);
                            }
                        });
                    });
            }, 1000);

        </script>


    @endpush

</x-app-layout>

