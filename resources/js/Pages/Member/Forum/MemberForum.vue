<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CreatePost from "@/Pages/Member/Forum/Partials/CreatePost.vue";
import {
    IconSearch,
    IconCircleXFilled
} from "@tabler/icons-vue";
import InputText from 'primevue/inputtext';
import {ref} from "vue";
import ForumPost from "@/Pages/Member/Forum/ForumPost.vue"

const search = ref('');

const clearSearch = () => {
    search.value = '';
}
</script>

<template>
    <AuthenticatedLayout :title="$t('public.member_forum')">
        <div class="flex items-start gap-5 self-stretch">
            <div class="flex w-full gap-5 max-w-[320px] flex-col items-center self-stretch">
                <!-- new post -->
                <div class="flex flex-col items-center gap-5 p-6 self-stretch bg-white shadow-toast rounded-2xl">
                    <span class="text-left w-full text-sm text-gray-400">Share your thoughts here...</span>
                    <CreatePost />
                </div>

                <!-- manage posting permissions -->
                <div class="flex pt-6 px-6 flex-col items-center self-stretch bg-white shadow-toast rounded-2xl">
                    <div class="flex flex-col items-center self-stretch gap-5 pb-3">
                        <span class="text-left w-full text-sm font-bold text-gray-950">Manage Posting Permissions</span>
                        <div class="relative w-full">
                            <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                                <IconSearch size="20" stroke-width="1.25" />
                            </div>
                            <InputText 
                                v-model="search" 
                                :placeholder="$t('public.search_agent')"
                                class="font-normal pl-12 w-full" 
                                />
                            <div
                                v-if="search"
                                class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                                @click="clearSearch"
                            >
                                <IconCircleXFilled size="16" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- posts -->
            <ForumPost />
        </div>
    </AuthenticatedLayout>
</template>