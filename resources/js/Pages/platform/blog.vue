<script setup>
import { computed, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import PlatformSectionHeading from '@/Components/platform/PlatformSectionHeading.vue'
import PlatformCtaBand from '@/Components/platform/PlatformCtaBand.vue'
import { blogCategories, blogFeatured, blogPosts } from '@/data/platform'

/**
 * Blog index. Post permalinks point at platform/blog/show, which resolves the
 * slug against this same data module.
 */
const activeCategory = ref('all')

const visiblePosts = computed(() =>
    activeCategory.value === 'all'
        ? blogPosts
        : blogPosts.filter((post) => categorySlug(post.category) === activeCategory.value),
)

function categorySlug(label) {
    return blogCategories.find((category) => category.label === label)?.slug
}
</script>

<template>
    <Head title="Blog — Commerce, explained" />

    <PlatformLayout>
        <!-- ════════════════════════════════════════
             HEADER
        ════════════════════════════════════════ -->
        <section class="grad-hero py-14 sm:py-20 text-center">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <h1 class="text-[32px] sm:text-5xl font-extrabold tracking-tight text-ink">
                    Notes on running a modern store
                </h1>
                <p class="text-gray-500 text-[15px] sm:text-lg mt-4">
                    Guides, benchmarks, and product updates from the people who build Shopwave —
                    written for merchants first, developers second.
                </p>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FEATURED POST
        ════════════════════════════════════════ -->
        <section class="pb-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <Link
                    :href="`/blog/${blogFeatured.slug}`"
                    class="grid md:grid-cols-2 gap-7 items-center border border-gray-200 rounded-2xl overflow-hidden hover:border-gray-300 transition-colors"
                >
                    <div
                        class="h-52 sm:h-64 md:h-full flex items-center justify-center text-7xl"
                        :class="blogFeatured.coverClass"
                    >
                        {{ blogFeatured.emoji }}
                    </div>
                    <div class="p-7 sm:p-9">
                        <div class="flex items-center gap-2.5 mb-3.5">
                            <span
                                class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-primarylt text-primary"
                            >
                                {{ blogFeatured.category }}
                            </span>
                            <span class="text-[12px] text-gray-400">Featured</span>
                        </div>
                        <h2
                            class="text-[22px] sm:text-[26px] font-extrabold tracking-tight text-ink leading-snug"
                        >
                            {{ blogFeatured.title }}
                        </h2>
                        <p class="text-[14px] text-gray-500 leading-relaxed mt-3">
                            {{ blogFeatured.excerpt }}
                        </p>
                        <div class="flex items-center gap-3 mt-6">
                            <span
                                class="w-9 h-9 rounded-full bg-primary text-white text-[12px] font-bold flex items-center justify-center shrink-0"
                            >
                                {{ blogFeatured.initials }}
                            </span>
                            <div class="text-[12.5px] leading-tight">
                                <p class="font-semibold text-ink">{{ blogFeatured.author }}</p>
                                <p class="text-gray-400">
                                    {{ blogFeatured.date }} · {{ blogFeatured.readTime }}
                                </p>
                            </div>
                        </div>
                        <span
                            class="inline-block mt-6 text-[14px] font-bold text-primary"
                        >
                            Read the article →
                        </span>
                    </div>
                </Link>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             CATEGORY FILTER + POST GRID
        ════════════════════════════════════════ -->
        <section class="py-16 sm:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <PlatformSectionHeading
                    eyebrow="Latest"
                    title="Everything we've published"
                    subtitle="Filter by topic, or read the lot."
                />

                <div class="flex flex-wrap justify-center gap-2 mb-10">
                    <button
                        v-for="category in blogCategories"
                        :key="category.slug"
                        type="button"
                        class="text-[13.5px] font-semibold px-4 py-2 rounded-full transition-colors"
                        :class="
                            activeCategory === category.slug
                                ? 'bg-primary text-white'
                                : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300'
                        "
                        :aria-pressed="activeCategory === category.slug"
                        @click="activeCategory = category.slug"
                    >
                        {{ category.label }}
                    </button>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <Link
                        v-for="post in visiblePosts"
                        :key="post.slug"
                        :href="`/blog/${post.slug}`"
                        class="group block bg-white border border-gray-100 rounded-2xl p-6 hover:border-gray-300 hover:shadow-sm transition-all"
                    >
                        <div
                            class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl mb-4"
                            :class="post.coverClass"
                        >
                            {{ post.emoji }}
                        </div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <span
                                class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"
                            >
                                {{ post.category }}
                            </span>
                            <span class="text-[11.5px] text-gray-400">{{ post.readTime }}</span>
                        </div>
                        <h3
                            class="text-[16px] font-bold text-ink leading-snug group-hover:text-primary transition-colors"
                        >
                            {{ post.title }}
                        </h3>
                        <p class="text-[13px] text-gray-500 leading-relaxed mt-2">
                            {{ post.excerpt }}
                        </p>
                        <div class="flex items-center gap-2.5 mt-5 pt-5 border-t border-gray-100">
                            <span
                                class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-[10.5px] font-bold flex items-center justify-center shrink-0"
                            >
                                {{ post.initials }}
                            </span>
                            <p class="text-[12px] text-gray-400">
                                {{ post.author }} · {{ post.date }}
                            </p>
                        </div>
                    </Link>
                </div>

                <p
                    v-if="visiblePosts.length === 0"
                    class="text-center text-[14px] text-gray-400 py-10"
                >
                    Nothing published in this category yet — check back soon.
                </p>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FINAL CTA
        ════════════════════════════════════════ -->
        <PlatformCtaBand
            tone="ink"
            title="Put the advice to work"
            body="Start your free trial and build the store these posts keep talking about. No credit card, no commitment."
            cta-label="Start Free Trial"
            cta-to="/register"
        />
    </PlatformLayout>
</template>