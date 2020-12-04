<template>

</template>

<script>
    export default {
        data(){
            return {
                users: {},
                user_form: new Form({

                })
            }
        },
        mounted() {
            this.getUsersList();
        },
        methods: {
            getUsersList() {
                this.$Progress.start();
                axios.get('../api/user')
                .then(({data}) => {
                    this.users = data.data;
                    this.$Progress.finish();
                })
            }
        }
    }
</script>
