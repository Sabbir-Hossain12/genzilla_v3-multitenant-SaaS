<script setup>
import { computed } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination, A11y, EffectFade } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import 'swiper/css/effect-fade'
import { slides as fallbackSlides } from '@/data/store'

const props = defineProps({
    slides: {
        type: Array,
        default: () => [],
    },
})

const displaySlides = computed(() => {
    return props.slides && props.slides.length > 0
        ? props.slides
        : fallbackSlides
})

const modules = [Autoplay, Navigation, Pagination, A11y, EffectFade]

// Let Swiper render + own its arrows: passing prevEl/nextEl selectors makes the
// Vue wrapper skip its own buttons and inject swiper-button-* classes onto
// ours, which fights the custom sizing. addIcons: false prevents Swiper 14
// from injecting its bulky default SVG icons.
const navigation = { enabled: true, hideOnClick: false, addIcons: false }
const pagination = { clickable: true }
const autoplay = { delay: 4500, disableOnInteraction: false }

// rewind (not loop) - with 3 slides, loop mode flickers duplicate slides.
const rewind = true

// swiper/vue does not expose the instance through a template ref, so the
// instance is captured from the `swiper` event instead.
let swiperInstance = null
const onSwiper = (swiper) => { swiperInstance = swiper }

const pause = () => swiperInstance?.autoplay?.pause()
const resume = () => swiperInstance?.autoplay?.resume()
</script>

<template>
    <div
        id="hero-banner"
        class="relative rounded-xl sm:rounded-2xl overflow-hidden w-full aspect-[2.2/1] sm:aspect-[2.4/1] md:aspect-auto md:h-80 lg:h-[26rem] bg-gray-100"
        @mouseenter="pause"
        @mouseleave="resume"
    >
        <Swiper
            :modules="modules"
            :navigation="navigation"
            :pagination="pagination"
            :autoplay="autoplay"
            :rewind="rewind"
            :slides-per-view="1"
            effect="fade"
            :fade-effect="{ crossFade: true }"
            :a11y="{ prevSlideMessage: 'Previous banner', nextSlideMessage: 'Next banner' }"
            @swiper="onSwiper"
        >
            <SwiperSlide v-for="(slide, i) in displaySlides" :key="slide.id ?? i">
                <a :href="slide.link || '#'" class="block w-full h-full relative">
                    <img
                        :src="slide.src || slide.image"
                        :alt="slide.alt || slide.title || 'Promotional banner'"
                        class="w-full h-full object-cover object-center"
                        :loading="i === 0 ? 'eager' : 'lazy'"
                        :fetchpriority="i === 0 ? 'high' : 'auto'"
                    />
                </a>
            </SwiperSlide>
        </Swiper>
    </div>
</template>
