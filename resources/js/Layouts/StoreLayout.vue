<script setup>
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue";
</script>


<template>
    <Header></Header>

    <!-- Page Content -->
    <main>
        <slot/>
    </main>

    <Footer></Footer>
</template>

<style scoped>
.vss{

}
</style>
