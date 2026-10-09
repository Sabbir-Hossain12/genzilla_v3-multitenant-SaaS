<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import PlatformCtaBand from '@/Components/platform/PlatformCtaBand.vue'

/**
 * Single blog post, resolved server-side. An unknown slug returns a null post
 * and renders the not-found panel below.
 */
const props = defineProps({
    post: { type: Object, default: () => null },
    related: { type: Array, default: () => [] },
})

const post = computed(() => props.post)
const related = computed(() => props.related ?? [])
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
                <div
                    class="max-w-2xl mx-auto px-4 sm:px-6 [&_p]:text-[15.5px] [&_p]:text-gray-600 [&_p]:leading-[1.8] [&_p]:mb-5 [&_h2]:text-[21px] [&_h2]:sm:text-[24px] [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-ink [&_h2]:mt-10 [&_h2]:mb-4 [&_ul]:space-y-3 [&_ul]:mb-6 [&_ul]:pl-1 [&_li]:relative [&_li]:pl-6 [&_li]:text-[15px] [&_li]:text-gray-600 [&_li]:leading-relaxed [&_li]:before:content-['•'] [&_li]:before:absolute [&_li]:before:left-0 [&_li]:before:text-accent [&_li]:before:font-bold [&_blockquote]:border-l-[3px] [&_blockquote]:border-primary [&_blockquote]:bg-primarylt/60 [&_blockquote]:rounded-r-xl [&_blockquote]:py-5 [&_blockquote]:px-6 [&_blockquote]:my-8 [&_blockquote]:text-[15.5px] [&_blockquote]:text-ink [&_blockquote]:leading-relaxed [&_blockquote]:font-medium [&_blockquote_p]:mb-0 [&_cite]:not-italic [&_cite]:text-[12.5px] [&_cite]:text-gray-500 [&_cite]:block [&_cite]:mt-2.5 [&_cite]:before:content-['—'] [&_cite]:before:mr-1.5"
                    v-html="post.html"
                ></div>
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