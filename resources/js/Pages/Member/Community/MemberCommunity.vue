<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {router, usePage} from "@inertiajs/vue3";
import { IconChevronDown, IconChevronUp, IconRefresh, IconSearch} from "@tabler/icons-vue";
import {ref, onMounted, h, onBeforeUnmount} from "vue";
import CreatePost from './Partials/CreatePost.vue';
import Button from "@/Components/Button.vue";
import Posts from "./Partials/Posts.vue";
import ProfileHeader from "./Partials/ProfileHeader.vue";
import axios from 'axios';
import Empty from "@/Components/Empty.vue";
import TrendingPosts from "./Partials/TrendingPosts.vue";
import FilterPost from "./Partials/FilterPost.vue";
import PopularTags from "./Partials/PopularTags.vue";
import ActivityFeed from "./Partials/ActivityFeed.vue";

const user = usePage().props.auth.user;

const props = defineProps({
    postCounts: Number,
    likeCounts: Number,
    commentCounts: Number,
})

const posts = ref([]);
const filteredPosts = ref([])
const loading = ref(false);
const loadMore = ref(false);
const showMore = ref(false)
const morePost = ref(route('member.getCommunityPosts'));
const activeSearch = ref('');
const activeFilters = ref([]);

const toggleMore = () => {
  showMore.value = !showMore.value
}

const handleFilterChange = ({ search, filters }) => {
    // Store filter state
    activeSearch.value = search || '';
    activeFilters.value = filters || [];
    
    // Reset posts and pagination
    posts.value = [];
    filteredPosts.value = [];
    
    // Update API URL with filters
    const params = { 
        search: search || '',
        filters: filters || []
    };
    
    morePost.value = route('member.getCommunityPosts', params);
    
    // Fetch posts with new filters
    fetchPosts(true);
};

const handleHashtagFilter = (hashtag) => {
    if (!hashtag.startsWith('#')) {
        hashtag = '#' + hashtag;
    }
    
    posts.value = [];
    filteredPosts.value = [];
    
    // Instead of setting a separate hashtag filter, update search input
    activeSearch.value = hashtag;
    
    // Update API URL with search parameter
    const params = {
        search: hashtag,
        filters: activeFilters.value || []
    };
    
    morePost.value = route('member.getCommunityPosts', params);
    
    fetchPosts(true);
};

const fetchPosts = async (isInitialLoad = false) => {
    if (!morePost.value || (isInitialLoad ? loading.value : loadMore.value)) return;

    if (isInitialLoad) {
        loading.value = true;
    } else {
        loadMore.value = true;
    }
    
    try {
        const response = await axios.get(morePost.value);
        const newPosts = response.data.data;
        
        if (newPosts.length > 0) {
            posts.value.push(...newPosts);
            filteredPosts.value.push(...newPosts);
        }

        // Extract the next page number
        const nextPage = response.data.current_page + 1;
        
        // Check if we've reached the last page
        if (nextPage > response.data.last_page) {
            morePost.value = null; // No more pages
        } else {
            // Create a new URL with all original params plus the new page number
            const url = new URL(morePost.value);
            url.searchParams.set('page', nextPage);
            morePost.value = url.toString();
        }
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        if (isInitialLoad) {
            loading.value = false;
        } else {
            loadMore.value = false;
        }
    }
};

const handleScroll = () => {
    const scrollBottom = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.offsetHeight;

    if (scrollBottom + 50 >= documentHeight) {
        fetchPosts(false); 
    }
};

onMounted(() => {
    fetchPosts(true); 
    window.addEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});

const handlePostCreated = (newPost) => {
    filteredPosts.value.unshift(newPost);
};

const handlePostUpdated = (updatedPost) => {
    const index = posts.value.findIndex(p => p.id === updatedPost.id);
    if (index !== -1) {
        posts.value[index] = updatedPost;
    }
};

const handlePostDeleted = (postId) => {
    filteredPosts.value = filteredPosts.value.filter(post => post.id !== postId);
};

const renderHashtags = (html) => {
    // 1. Replace <span data-hashtag="tag">#tag</span> with <a ...>
    let result = html.replace(
        /<span[^>]*data-hashtag="([^"]+)"[^>]*>(#[^<]+)<\/span>/g,
        (match, tag, text) =>
            `<span class="text-primary hover:underline cursor-pointer hashtag-span" data-hashtag="${tag.toLowerCase()}">${text}</span>`
    );
    // 2. Replace plain #hashtag (not inside a tag) with <a ...>
    result = result.replace(
        /(^|[>\s])#([\u4e00-\u9fa5_a-zA-Z0-9]+)/gu,
        (match, prefix, tag) =>
            `${prefix}<span class="text-primary hover:underline cursor-pointer hashtag-span" data-hashtag="${tag.toLowerCase()}">#${tag}</span>`
    );
    return result;
}

</script>

<template>
    <AuthenticatedLayout :title="$t('public.community')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex max-w-[1440px] items-start gap-5 flex-[1_0_0] self-stretch">
                <!-- left -->
                <div class="hidden md:flex max-w-[400px] flex-col items-center gap-5 flex-[1_0_0] self-stretch">
                    <!-- profile -->
                    <ProfileHeader 
                        :user="user"
                        :posts="posts"
                        :postCounts="postCounts"
                        :likeCounts="likeCounts"
                        :commentCounts="commentCounts"
                        :showEditCoverButton="false"
                        marginSize="-mt-10"
                        avatarSize="md:w-20 md:h-20 border-[6px]" 
                        nameTextSize="text-base"
                        introTextSize="text-sm"
                        numberSize="text-lg"
                        labelSize="text-sm"
                        @click="router.visit(route('member.communityProfile'))"
                        class="cursor-pointer"
                    />
                    <!-- Search -->
                        <FilterPost @filterChange="handleFilterChange" :hashtag="activeSearch"/>
                    <!-- Activity Feed -->
                        <ActivityFeed :user="user" :posts="posts" :renderHashtags="renderHashtags" @hashtagFilter="handleHashtagFilter" />
                </div>
                <!-- middle -->
                <div class="flex md:min-w-[420px] w-full flex-col items-center gap-3 md:gap-4 flex-[1_0_0] self-stretch">
                    <!-- profile header in sm view -->
                    <ProfileHeader class="md:hidden cursor-pointer"
                        :user="user"
                        :posts="posts"
                        :postCounts="postCounts"
                        :likeCounts="likeCounts"
                        :commentCounts="commentCounts"
                        :showEditCoverButton="false"
                        marginSize="md:-mt-10"
                        avatarSize="md:w-20 md:h-20 md:border-[6px]" 
                        nameTextSize="md:text-base"
                        introTextSize="text-sm md:text-sm"
                        numberSize="text-md md:text-lg"
                        labelSize="text-xs md:text-sm"
                        @click="router.visit(route('member.communityProfile'))" 
                    />
                    <!-- Search in sm view -->
                    <template v-if="showMore" class="md:hidden flex p-4 flex-col items-start gap-3 self-stretch bg-white shadow-toast rounded-2xl">
                        <FilterPost @filterChange="handleFilterChange" :hashtag="activeSearch"/>
                    </template>
                    <!-- Activity Feed in sm view -->
                    <template v-if="showMore" class="md:hidden flex py-4 flex-col items-center gap-3 self-stretch bg-white shadow-toast rounded-2xl">
                        <ActivityFeed :user="user" :posts="posts" :renderHashtags="renderHashtags" @hashtagFilter="handleHashtagFilter" />
                    </template>

                    <!-- Show More Button in sm view-->
                    <Button
                        variant="gray-text"
                        type="button"
                        class="md:hidden flex-1 !py-2 !px-4 !gap-2"
                        @click="toggleMore"
                        >
                        <div class="text-primary-500">
                            {{ showMore ? 'Show Less' : 'Show More' }}
                        </div>
                        <component
                            :is="showMore ? IconChevronUp : IconChevronDown"
                            class="w-5 h-5 text-primary-500"
                        />
                    </Button>

                    <!-- Create Post -->
                    <CreatePost :user="user" @post-created="handlePostCreated"/>

                    <!-- Posts -->
                    <div v-if="!filteredPosts.length && !loading"
                        class="flex px-6 py-5 flex-col justify-center items-center gap-4 self-stretch flex-[1_0_0]"
                    >
                        <Empty
                            :title="$t('public.nothing_here')"
                            :message="$t('public.no_community_post_caption')"
                        >
                            <template #image>
                                <img src="/img/no_data/illustration-forum.svg" alt="no data" />
                            </template>
                        </Empty>
                    </div>

                    <div v-else class="flex flex-col gap-4 w-full">
                        <Posts :user="user" :posts="filteredPosts" :loading="loading" :renderHashtags="renderHashtags" @post-updated="handlePostUpdated"  @post-deleted="handlePostDeleted" @hashtagFilter="handleHashtagFilter"/>
                        <div v-if="loadMore" class="flex justify-center py-4 mt-2">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-4 border-primary-500"></div>
                        </div>
                    </div>
                </div>
                <!-- right -->
                <div class="hidden xl:flex max-w-[400px] flex-col items-center gap-5 flex-[1_0_0] self-stretch">
                    <!-- Trending Posts -->
                    <TrendingPosts  :user="user" :renderHashtags="renderHashtags"  @hashtagFilter="handleHashtagFilter"/>
                    <!-- Popular Tags -->
                    <PopularTags :handleHashtagFilter="handleHashtagFilter"/>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>


</template>