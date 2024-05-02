<script>
import axios from "axios";
import Layout from "@/Pages/Layouts/Layout.vue";
import {Link} from "@inertiajs/inertia-vue3";
import {usePage} from "@inertiajs/vue3";


export default {
    name: "Index",
    components: {Layout, Link},
    props: [
        'messages',
        'otherUser',
        'users',
    ],
    data() {
        return {
            body: '',
            userid: null

        }
    },
    mounted() {
        this.scrollToBottom()
    },
    created() {
        const page = usePage()
        this.userid = page.props.auth.user.id
        const sender_id = page.props.auth.user.id
        const receiver_id = this.otherUser.id
        Echo.private('messages.' + sender_id + '.' + receiver_id)
            .listen('.store_message', res => {
                this.messages.push(res.message);
            })

        Echo.join('online_status')
            .here(users => {
                users.forEach(user => {
                    this.setUserOnline(user.id);
                });
            })
            .joining(user => {
                this.setUserOnline(user.id);
            })
            .leaving(user => {
                this.setUserOffline(user.id);
            });

        Echo.private(`new_messages.${this.userid}`)
            .listen('.new_message', (e) => {
                this.updateLastMessage(e.message);
            });
    },
    methods: {
        store() {
            const page = usePage()
            axios.post('/messages', {
                body: this.body,
                receiver_id: this.otherUser.id,
                sender_id: page.props.auth.user.id
            })
                .then(res => {
                    this.messages.push(res.data);
                    this.body = '';
                    this.scrollToBottom()
                })
        },
        scrollToBottom() {
            const container = this.$refs.messages;
            container.scrollTop = container.scrollHeight;
        },
        setUserOnline(userId) {
            const user = this.users.find(user => user.id === userId);
            if (user) {
                user.isOnline = true;
            }
        },
        setUserOffline(userId) {
            const user = this.users.find(user => user.id === userId);
            if (user) {
                user.isOnline = false;
            }
        },
        updateLastMessage(message) {
            const user = this.users.find(user => user.id === message.sender_id);
            if (user) {
                user.lastMessage = message;
            }
        }
    }
}
</script>

<template>
    <Layout :users="users">
        <div class="w-1/4 bg-white border-r border-gray-300">
            <!-- Sidebar Header -->
            <header class="p-4 border-b border-gray-300 flex justify-between items-center bg-indigo-600 text-white">
                <h1 class="text-2xl font-semibold">Chat Web</h1>
                <div class="relative">
                    <button id="menuButton" class="focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-100" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path d="M2 10a2 2 0 012-2h12a2 2 0 012 2 2 2 0 01-2 2H4a2 2 0 01-2-2z"/>
                        </svg>
                    </button>
                    <!-- Menu Dropdown -->
                    <div id="menuDropdown"
                         class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-md shadow-lg hidden">
                        <ul class="py-2 px-3">
                            <li><a href="#" class="block px-4 py-2 text-gray-800 hover:text-gray-400">Option 1</a></li>
                            <li><a href="#" class="block px-4 py-2 text-gray-800 hover:text-gray-400">Option 2</a></li>
                            <!-- Add more menu options here -->
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Contact List -->
            <div class="overflow-y-auto h-screen p-3 mb-9 pb-20">
                <Link :href="route('index', {'otherUser': user.id})"
                      class="flex items-center mb-4 cursor-pointer hover:bg-gray-100 p-2 rounded-md"
                      v-for="user in users">
                    <div class="w-12 h-12 bg-gray-300 rounded-full mr-3">
                        <img src="https://placehold.co/200x/ad922e/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato" alt="User Avatar"
                             class="w-12 h-12 rounded-full">
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h2 class="text-lg font-semibold">{{ user.name }}</h2>
                            <h2 class="text-xs text-gray-400 font-semibold h-fit self-center">{{
                                    user.isOnline ? 'Online' : 'Offline'
                                }}</h2>
                        </div>
                        <p class="text-gray-600" v-if="user.lastMessage">{{ user.lastMessage.sender_id === userid ? 'You: ' : ''}} {{
                                user.lastMessage.body
                            }}</p>
                    </div>
                </Link>

            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1">
            <!-- Chat Header -->
            <header class="bg-white p-4 text-gray-700">
                <h1 class="text-2xl font-semibold">{{ otherUser.name }}</h1>
            </header>

            <!-- Chat Messages -->
            <div class="h-screen overflow-y-scroll p-4 pb-36" ref="messages">
                <template v-for="message in messages">
                    <div class="flex mb-4 cursor-pointer" v-if="message.sender_id !== userid">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center mr-2">
                            <img src="https://placehold.co/200x/ffa8e4/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato"
                                 alt="User Avatar"
                                 class="w-8 h-8 rounded-full">
                        </div>
                        <div class="flex-col max-w-96 bg-white rounded-lg p-3 gap-3">
                            <p class="text-gray-700">{{ message.body }}</p>
                            <p class="text-gray-500 text-xs">{{ message.time }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end mb-4 cursor-pointer" v-else>
                        <div class="flex-col max-w-96 bg-indigo-500 text-white rounded-lg p-3 gap-3">
                            <p>{{ message.body }}</p>
                            <p class="text-gray-200 text-xs w-fit ml-auto">{{ message.time }}</p>

                        </div>
                        <div class="w-9 h-9 rounded-full flex items-center justify-center ml-2">
                            <img src="https://placehold.co/200x/b7a8ff/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato"
                                 alt="My Avatar"
                                 class="w-8 h-8 rounded-full">
                        </div>
                    </div>
                </template>
            </div>

            <!-- Chat Input -->
            <footer class="bg-white border-t border-gray-300 p-4 absolute bottom-0 w-3/4">
                <div class="flex items-center">
                    <input type="text" placeholder="Type a message..." v-model="body"
                           class="w-full p-2 rounded-md border border-gray-400 focus:outline-none focus:border-blue-500">
                    <button class="bg-indigo-500 text-white px-4 py-2 rounded-md ml-2" @click="store">Send</button>
                </div>
            </footer>
        </div>
    </Layout>
</template>

<style scoped>

</style>
