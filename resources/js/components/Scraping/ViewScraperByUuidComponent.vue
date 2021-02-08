<template>
    <div class="container component">
        <div class="row justify-content-center">
            
        </div>
    </div>
</template>

<script>
import {showScrollbar ,hideScrollbar} from '../../app';

export default {
    data: () => {
        return {
            
        }
    },
    mounted() {
        hideScrollbar();
    },
    destroyed() {
        hideScrollbar();
    },
    watch: {
        
    },
    methods: {
        
    }
}
</script>
