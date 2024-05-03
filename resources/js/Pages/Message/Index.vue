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
        'currentUser'
    ],
    data() {
        return {
            body: '',
            userid: null,
            user: null,
            showEdit: false
        }
    },
    mounted() {
        this.scrollToBottom()

        let script = document.createElement('script')
        script.src = "https://js.pusher.com/beams/1.0/push-notifications-cdn.js"
        script.async = true
        document.body.appendChild(script)

        script.onload = () => {
            this.beamsClient = new PusherPushNotifications.Client({
                instanceId: 'ab8ac48b-267c-4e14-903a-3479977bd1ae',
            });

            this.beamsClient.start()
                .then(() => this.beamsClient.addDeviceInterest(`user-${this.userid}`))
                .then(() => console.log('Successfully registered and subscribed!'))
                .catch(console.error);
        }
    },
    created() {
        const page = usePage()
        this.userid = page.props.auth.user.id;
        const sender_id = page.props.auth.user.id;
        const receiver_id = this.otherUser.id;
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
                    const receiver = this.users.find(user => user.id === res.data.receiver_id);
                    receiver.lastMessage = res.data;
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
        <div class="w-1/4 bg-white border-r border-gray-300 ">
            <!-- Sidebar Header -->
            <header class="p-4 border-b border-gray-300 flex justify-between items-center bg-indigo-600 text-white">
                <h1 class="text-2xl font-semibold">Chat Web</h1>
                <div class="relative">
                    <p>{{ currentUser.name }}</p>
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
                        <p class="text-gray-600" v-if="user.lastMessage">
                            {{ user.lastMessage.sender_id === userid ? 'You: ' : '' }} {{
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
