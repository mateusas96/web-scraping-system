<template>
    <div class="container">
        <div class="row justify-content-center">
            <v-alert
                v-if="error.error"
                border="left"
                type="error"
            >{{error.message}}</v-alert>
            <v-hover v-else>
                <template v-slot:default="{ hover }">
                    <v-card 
                        :class="`elevation-${hover ? 24 : 6}`"
                        class="mx-auto pa-6 transition-swing"
                    >
                        <v-card-title>
                            Users list
                        <v-spacer></v-spacer>
                            <v-text-field
                                v-on:input="searchit"
                                v-model="search"
                                append-icon="mdi-magnify"
                                label="Search"
                                single-line
                                hide-details
                                style="max-width: 300px;"
                            ></v-text-field>
                        </v-card-title>
                        <v-data-table
                            :page.sync="usersPagination.current_page"
                            :items-per-page="usersPagination.per_page"
                            hide-default-footer
                            :loading="loading"
                            sort-by="last_name"
                            loading-text="Loading... Please wait"
                            :headers="headers"
                            :items="users"
                        >
                        <template v-slot:[`item.hashtag`]="{}">
                            #
                        </template>
                        <template v-slot:[`item.is_admin`]="{ item }">
                            <span v-if="item.is_admin" style="color: green;">True</span>
                            <span v-else style="color: red;">False</span>
                        </template>
                        <template v-slot:[`item.is_disabled`]="{ item }">
                            <span v-if="item.is_disabled" style="color: red;">True</span>
                            <span v-else style="color: green;">False</span>
                        </template>
                        <template v-slot:[`item.email_verified_at`]="{ item }">
                            <span v-if="item.email_verified_at !== null">{{item.email_verified_at}}</span>
                            <span v-else class="d-flex justify-center">-</span>
                        </template>
                            <template v-slot:[`item.actions`]="{ item }">
                                <v-icon
                                    class="ml-4"
                                    v-on:click="editUser(item)"
                                >
                                    mdi-pencil
                                </v-icon>
                            </template>

                            <template v-slot:top>
                                <v-dialog
                                    v-model="editUserDialog"
                                    max-width="500px"
                                >
                                    <v-card>
                                    <v-card-title>
                                        <span class="headline">Edit User</span>
                                    </v-card-title>
                        
                                    <v-card-text>
                                        <v-container>
                                        <v-row>
                                            <v-col
                                                cols="12"
                                                sm="6"
                                                md="6"
                                            >
                                            <v-text-field
                                                v-model="userForm.first_name"
                                                label="First Name"
                                                :disabled="true"
                                            ></v-text-field>
                                            </v-col>
                                            <v-col
                                                cols="12"
                                                sm="6"
                                                md="6"
                                            >
                                            <v-text-field
                                                v-model="userForm.last_name"
                                                label="Last Name"
                                                :disabled="true"
                                            ></v-text-field>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col
                                                class="d-flex"
                                                cols="12"
                                            >
                                                <v-select
                                                    v-model="userForm.is_admin"
                                                    label="Is Admin"
                                                    :items="selectOptions"
                                                    item-text="name"
                                                    item-value="value"
                                                ></v-select>
                                            </v-col>
                                        </v-row>
                                        <v-row>
                                            <v-col
                                                class="d-flex"
                                                cols="12"
                                            >
                                                <v-select
                                                    v-model="userForm.is_disabled"
                                                    label="Is Disabled"
                                                    :items="selectOptions"
                                                    item-text="name"
                                                    item-value="value"
                                                ></v-select>
                                            </v-col>
                                        </v-row>
                                        </v-container>
                                    </v-card-text>
                        
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            color="error darken-1"
                                            text
                                            v-on:click="editUserDialog = false"
                                        >
                                            Cancel
                                        </v-btn>
                                        <v-btn
                                            color="success darken-1"
                                            text
                                            v-on:click="updateUser"
                                        >
                                            Save
                                        </v-btn>
                                    </v-card-actions>
                                    </v-card>
                                </v-dialog>
                            </template>

                        </v-data-table>
                        <v-pagination
                            v-model="usersPagination.current_page"
                            :length="usersPagination.last_page"
                            v-on:input="getResults"
                            :total-visible="7"
                        ></v-pagination>
                    </v-card>
                </template>
            </v-hover>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => ({
            loading: false,
            editUserDialog: false,
            error: [
                { 'error': false },
                { 'message': '' },
            ],
            showContent: false,
            usersPagination: [],
            users: [],
            userForm: new Form({
                first_name: '',
                last_name: '',
                email: '',
                is_admin: '',
                is_disabled: '',
            }),
            search: '',
            headers: [
                { text: '#', align: 'start', sortable: false, value: 'hashtag' }, 
                { text: 'First Name', value: 'first_name' },
                { text: 'Last Name', value: 'last_name' },
                { text: 'Username', value: 'username' },
                { text: 'Email', value: 'email' },
                { text: 'Is Admin', value: 'is_admin' }, 
                { text: 'Is Disabled', value: 'is_disabled' },
                { text: 'Created At', value: 'created_at' },
                { text: 'Email Verified At', value: 'email_verified_at' },
                { text: 'Edit User', value: 'actions' , sortable: false}
            ],
            selectOptions: [
                { name: 'True', value: 1 },
                { name: 'False', value: 0 },
            ]
        }),
        mounted() {
            this.getUsersList();
            Fire.$on('AfterUpdate', () => {
                this.getUsersList();
            });
            Fire.$on('searchUser', ()=>{
                this.loading = true;
                axios.get('/api/findUser?query=' + this.search)
                .then(({data})=>{
                    this.users = data.data;
                    this.usersPagination = data;
                    this.loading = false;
                })
                .catch(()=>{
                    this.loading = false;
                });
            })
        },
        methods: {
            searchit: _.debounce(() => {
                Fire.$emit('searchUser');
            }, 500),
            getResults(page = 1) {
                this.loading = true;
                axios.get('/api/user?page=' + page)
                    .then(({data})=> {
                    this.users = data.data;
                    this.usersPagination = data;
                    this.loading = false;
                });
            },
            getUsersList() {
                this.loading = true;
                axios.get('/api/user')
                .then(({data}) => {
                    this.users = data.data;
                    this.usersPagination = data;
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error = error.response.data;

                })
            },
            editUser(user) {
                this.editUserDialog = true;
                this.userForm.reset();
                this.userForm.clear();
                this.userForm.fill(user); 
            },
            updateUser() {
                this.userForm.put('/api/user/' + this.userForm.email)
                .then(() =>{
                    this.editUserDialog = false;
                    Fire.$emit('AfterUpdate');
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'User info updated successfully',
                        showConfirmButton: false,
                        timer: 2500
                    })
                })
                .catch(()=>{
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Something went wrong',
                        showConfirmButton: false,
                        timer: 4000
                    })
                })
            }
        }
    }
</script>