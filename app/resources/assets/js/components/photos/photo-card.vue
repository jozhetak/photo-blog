<template>
    <div class="card" v-if="photo.original.url">
        <a :href="photo.original.url"
           target="_blank"><img class="card-img-top"
                                :src="photo.thumbnail.url"
                                :alt="photo.description"
                                :title="photo.description"></a>
        <div class="card-img-avg-color" :style="{'background-color': photo.averageColor}"></div>
        <exif-description :exif="photo.exif"></exif-description>
    </div>
</template>

<style scoped>
    .card-img-avg-color {
        display: block;
        width: 100%;
        height: 20px;
    }
</style>

<script>
    import ExifDescription from "./exif-description";

    export default {
        components: {ExifDescription},
        props: {
            photo: {
                type: Object,
                default: function () {
                    return {};
                },
            },
        },
    }
</script>
