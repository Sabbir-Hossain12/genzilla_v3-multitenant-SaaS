<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import PlatformCtaBand from '@/Components/platform/PlatformCtaBand.vue'
import { findBlogPost, relatedBlogPosts } from '@/data/platform'

/**
 * Single blog post.
 *
 * The catalogue lives in a JS module rather than the database, so the route is
 * a thin shell that hands over the slug and the lookup happens here. An unknown
 * slug renders the not-found panel below instead of throwing — if these posts
 * ever move into a table, move the lookup server-side and return a real 404.
 */
const props = defineProps({
    slug: { type: String, required: true },
})

const post = computed(() => findBlogPost(props.slug))
const related = computed(() => relatedBlogPosts(post.value))
</script>

<template>
    <Head
        :title="post ? post.title : 'Post not found'"
        :meta="post ? [{ name: 'description', content: post.excerpt }] : []"
    />

    <PlatformLayout>
        <!-- ════════════════════════════════════════
             NOT FOUND
        ════════════════════════════════════════ -->
        <section v-if="!post" class="py-24 sm:py-32 text-center">
            <div class="max-w-lg mx-auto px-4 sm:px-6">
                <p class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2">
                    404
                </p>
                <h1 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
                    We could not find that post
                </h1>
                <p class="text-gray-500 text-[15px] mt-4">
                    The link may be out of date, or the article may have been renamed.
                </p>
                <Link
                    href="/blog"
                    class="inline-block mt-7 bg-primary text-white font-bold text-[15px] px-7 py-3.5 rounded-xl hover:bg-primarydark transition-colors"
                >
                    Back to the blog
                </Link>
            </div>
        </section>

        <template v-else>
            <!-- ════════════════════════════════════════
                 HEADER
            ════════════════════════════════════════ -->
            <section class="grad-hero py-12 sm:py-16">
                <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
                    <Link
                        href="/blog"
                        class="inline-block text-[13.5px] font-semibold text-gray-400 hover:text-primary transition-colors mb-5"
                    >
                        ← Back to the blog
                    </Link>
                    <div class="flex justify-center mb-4">
                        <span
                            class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-white text-primary shadow-sm"
                        >
                            {{ post.category }}
                        </span>
                    </div>
                    <h1
                        class="text-[28px] sm:text-[40px] font-extrabold tracking-tight text-ink leading-[1.15]"
                    >
                        {{ post.title }}
                    </h1>
                    <p class="text-gray-500 text-[15px] sm:text-lg mt-4">{{ post.excerpt }}</p>

                    <div class="flex items-center justify-center gap-3 mt-7">
                        <span
                            class="w-10 h-10 rounded-full bg-primary text-white text-[13px] font-bold flex items-center justify-center shrink-0"
                        >
                            {{ post.initials }}
                        </span>
                        <div class="text-left text-[12.5px] leading-tight">
                            <p class="font-semibold text-ink">{{ post.author }}</p>
                            <p class="text-gray-400">
                                {{ post.role }} · {{ post.date }} · {{ post.readTime }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ════════════════════════════════════════
                 COVER
            ════════════════════════════════════════ -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div
                    class="h-52 sm:h-72 rounded-2xl flex items-center justify-center text-8xl sm:text-9xl"
                    :class="post.coverClass"
                >
                    {{ post.emoji }}
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 BODY
            ════════════════════════════════════════ -->
            <article class="py-14 sm:py-16">
                <div class="max-w-2xl mx-auto px-4 sm:px-6">
                    <template v-for="(block, index) in post.body" :key="index">
                        <p
                            v-if="block.type === 'p'"
                            class="text-[15.5px] text-gray-600 leading-[1.8] mb-5"
                        >
                            {{ block.text }}
                        </p>

                        <h2
                            v-else-if="block.type === 'h2'"
                            class="text-[21px] sm:text-[24px] font-extrabold tracking-tight text-ink mt-10 mb-4"
                        >
                            {{ block.text }}
                        </h2>

                        <ul
                            v-else-if="block.type === 'ul'"
                            class="space-y-3 mb-6 pl-1"
                        >
                            <li
                                v-for="item in block.items"
                                :key="item"
                                class="flex items-start gap-3 text-[15px] text-gray-600 leading-relaxed"
                            >
                                <svg
                                    width="18"
                                    height="18"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                    class="text-accent shrink-0 mt-0.5"
                                >
                                    <path
                                        d="M20 6L9 17l-5-5"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <span>{{ item }}</span>
                            </li>
                        </ul>

                        <blockquote
                            v-else-if="block.type === 'quote'"
                            class="border-l-[3px] border-primary bg-primarylt/60 rounded-r-xl py-5 px-6 my-8"
                        >
                            <p class="text-[15.5px] text-ink leading-relaxed font-medium">
                                {{ block.text }}
                            </p>
                            <cite
                                class="not-italic text-[12.5px] text-gray-500 block mt-2.5"
                            >
                                — {{ block.cite }}
                            </cite>
                        </blockquote>
                    </template>
                </div>
            </article>

            <!-- ════════════════════════════════════════
                 RELATED READS
            ════════════════════════════════════════ -->
            <section v-if="related.length" class="py-14 bg-gray-50 border-t border-gray-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6">
                    <h2
                        class="text-xl sm:text-2xl font-extrabold tracking-tight text-ink text-center mb-9"
                    >
                        More in {{ post.category }}
                    </h2>
                    <div class="grid sm:grid-cols-3 gap-5">
                        <Link
                            v-for="item in related"
                            :key="item.slug"
                            :href="`/blog/${item.slug}`"
                            class="group bg-white border border-gray-100 rounded-2xl p-6 hover:border-gray-300 hover:shadow-sm transition-all"
                        >
                            <div
                                class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl mb-4"
                                :class="item.coverClass"
                            >
                                {{ item.emoji }}
                            </div>
                            <h3
                                class="text-[15.5px] font-bold text-ink leading-snug group-hover:text-primary transition-colors"
                            >
                                {{ item.title }}
                            </h3>
                            <p class="text-[12.5px] text-gray-400 mt-2.5">
                                {{ item.date }} · {{ item.readTime }}
                            </p>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ════════════════════════════════════════
                 FINAL CTA
            ════════════════════════════════════════ -->
            <PlatformCtaBand
                tone="primary"
                title="Ready when you are"
                body="Start your free trial and build the store this post walks you through. No credit card, no commitment."
                cta-label="Start Free Trial"
                cta-to="/register"
            />
        </template>
    </PlatformLayout>
</template>