<script>
import axios from "axios";
import Layout from "@/Pages/Layouts/Layout.vue";
import { Link } from "@inertiajs/inertia-vue3";
import {usePage} from "@inertiajs/vue3";


export default {
    name: "Index",
    components: {Layout, Link},
    props: [
        'messages',
        'otherUser',
        'users'
    ],
    data() {
        return {
            body: '',
        }
    },
    created() {
        const page = usePage()
        const sender_id = page.props.auth.user.id
        const receiver_id = this.otherUser.id

        Echo.channel('messages.' + sender_id + '.' + receiver_id)
            .listen('.store_message', res => {
                this.messages.push(res.message);
            })

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
                })
        }
    }
}
</script>

<template>
    <Layout :users="users">
        <!-- Main Chat Area -->
        <div class="flex-1">
            <!-- Chat Header -->
            <header class="bg-white p-4 text-gray-700">
                <h1 class="text-2xl font-semibold">{{ otherUser.name }}</h1>
            </header>

            <!-- Chat Messages -->
            <div class="h-screen overflow-y-auto p-4 pb-36">
                <!-- Incoming Message -->
                <div class="flex mb-4 cursor-pointer">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center mr-2">
                        <img src="https://placehold.co/200x/ffa8e4/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato" alt="User Avatar"
                             class="w-8 h-8 rounded-full">
                    </div>
                    <div class="flex-col max-w-96 bg-white rounded-lg p-3 gap-3">
                        <p class="text-gray-700">Hey Bob, how's it going?</p>
                        <p class="text-gray-500 text-xs">erwr</p>
                    </div>
                </div>

                <!-- Outgoing Message -->
                <div class="flex justify-end mb-4 cursor-pointer">
                    <div class="flex-col max-w-96 bg-indigo-500 text-white rounded-lg p-3 gap-3">
                        <p>Hi Alice! I'm good, just finished a great book. How about you?</p>
                        <p class="text-gray-200 text-xs w-fit ml-auto">erwr</p>

                    </div>
                    <div class="w-9 h-9 rounded-full flex items-center justify-center ml-2">
                        <img src="https://placehold.co/200x/b7a8ff/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato" alt="My Avatar"
                             class="w-8 h-8 rounded-full">
                    </div>
                </div>

                <!--                -->

                <div class="flex mb-4 cursor-pointer" v-for="message in messages">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center mr-2">
                        <img src="https://placehold.co/200x/ffa8e4/ffffff.svg?text=ʕ•́ᴥ•̀ʔ&font=Lato" alt="User Avatar"
                             class="w-8 h-8 rounded-full">
                    </div>
                    <div class="flex-col max-w-96 bg-white rounded-lg p-3 gap-3">
                        <p class="text-gray-700">{{ message.body }}</p>
                        <p class="text-gray-500 text-xs">{{ message.time }}</p>
                    </div>
                </div>
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
