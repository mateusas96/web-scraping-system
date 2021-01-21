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
        console.log('mounted');
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
