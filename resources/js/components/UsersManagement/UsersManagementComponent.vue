<template>
    <div class="container component" style="width: auto;">
        <div class="row justify-content-center">
            <v-alert
                v-if="error.error"
                border="left"
                type="error"
            >{{error.message}}</v-alert>
            <template v-else>
                <v-card 
                    class="mx-auto pa-6"
                >
                    <v-card-title>
                        Users list
                        <div
                            class="d-flex justify-end ml-6"
                        >
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on:click="refreshUsers = !refreshUsers"
                                        v-on="on"
                                        small
                                        :disabled="refreshUsers"
                                        class="mr-6"
                                    >
                                        <v-icon v-if="refreshUsers" small>fas fa-sync fa-spin</v-icon>
                                        <v-icon v-else small>fas fa-sync</v-icon>
                                    </v-btn>
                                </template>
                                <small>Refresh users list</small>
                            </v-tooltip>
                        </div>
                    <v-spacer></v-spacer>
                        <v-text-field
                            v-on:input="searchit"
                            v-model="search"
                            append-icon="mdi-magnify"
                            label="Search"
                            single-line
                            hide-details
                            style="max-width: 18.75rem; margin-bottom: 1.25rem"
                            clearable
                            clear-icon="mdi-close-circle-outline"
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
                            <span v-if="item.is_admin" style="color: green;">Yes</span>
                            <span v-else style="color: red;">No</span>
                        </template>
                        <template v-slot:[`item.is_disabled`]="{ item }">
                            <span v-if="item.is_disabled" style="color: red;">Yes</span>
                            <span v-else style="color: green;">No</span>
                        </template>
                        <template v-slot:[`item.can_upload_files`]="{ item }">
                            <span v-if="item.can_upload_files" style="color: green;">Yes</span>
                            <span v-else style="color: red;">No</span>
                        </template>
                        <template v-slot:[`item.email_verified_at`]="{ item }">
                            <span v-if="item.email_verified_at !== null">{{item.email_verified_at}}</span>
                            <span v-else class="d-flex justify-center">-</span>
                        </template>
                        <template v-slot:[`item.actions`]="{ item }">
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-icon
                                        class="ml-4"
                                        v-on:click="editUser(item)"
                                        v-on="on"
                                    >
                                        mdi-pencil
                                    </v-icon>
                                </template>
                                <small>Edit user</small>
                            </v-tooltip>
                        </template>

                        <template v-slot:top>
                            <v-dialog
                                v-model="showEditUserDialog"
                                max-width="500px"
                                @click:outside="dirty = false"
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
                                                v-on:change="handleSelectChange"
                                                :disabled="userForm.email === 'matasxlx@gmail.com' && currentUser.email !== 'matasxlx@gmail.com'"
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
                                                v-on:change="handleSelectChange"
                                                :disabled="userForm.email === 'matasxlx@gmail.com' && currentUser.email !== 'matasxlx@gmail.com'"
                                            ></v-select>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col
                                            class="d-flex"
                                            cols="12"
                                        >
                                            <v-select
                                                v-model="userForm.can_upload_files"
                                                label="Can upload files"
                                                :items="selectOptions"
                                                item-text="name"
                                                item-value="value"
                                                v-on:change="handleSelectChange"
                                                :disabled="userForm.email === 'matasxlx@gmail.com' && currentUser.email !== 'matasxlx@gmail.com'"
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
                                        v-on:click="showEditUserDialog = false"
                                    >
                                        Cancel
                                    </v-btn>
                                    <v-btn
                                        color="success darken-1"
                                        text
                                        v-on:click="updateUser"
                                        :disabled="!dirty"
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
        </div>
    </div>
</template>

<script>
import {showScrollbar, hideScrollbar, updateCurrentUser, current_user} from '../../app';

export default {
    data: () => ({
        loading: false,
        showEditUserDialog: false,
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
            can_upload_files: '',
        }),
        search: '',
        headers: [
            { text: '#', align: 'start', sortable: false, value: 'hashtag' }, 
            { text: 'First name', value: 'first_name' },
            { text: 'Last name', value: 'last_name' },
            { text: 'Username', value: 'username' },
            { text: 'Email', value: 'email' },
            { text: 'Is admin', value: 'is_admin' }, 
            { text: 'Is disabled', value: 'is_disabled' },
            { text: 'Can upload files', value: 'can_upload_files' },
            { text: 'Created at', value: 'created_at' },
            { text: 'Updated at', value: 'updated_at' },
            { text: 'Actions', value: 'actions' , sortable: false},
        ],
        selectOptions: [
            { name: 'Yes', value: 1 },
            { name: 'No', value: 0 },
        ],
        dirty: false,
        refreshUsers: false,
        currentUser: [],
    }),
    watch: {
        refreshUsers: {
            handler: function(newVal, oldVal) {
                if (newVal) {
                    this.loading = true;
                    this.getUsersList();
                }
            }
        },
        users: {
            handler: function() {
                setTimeout(() => {
                    $(window).height() < 950 &&
                    $('.container.component').height() > $(window).height() - 110 ?
                    showScrollbar() : hideScrollbar();
                }, 300);
            }
        }
    },
    mounted() {
        current_user.then(({data}) => {
            this.currentUser = data;
        });
        this.loading = true;
        this.getUsersList();
        Fire.$on('searchUser', () => {
            this.loading = true;
            axios.get('/api/find_user?query=' + (this.search === null ? '' : this.search))
            .then(({data}) => {
                this.users = data.data;
                this.usersPagination = data;
                this.loading = false;
            })
            .catch(() => {
                this.loading = false;
            });
        });
    },
    destroyed() {
        hideScrollbar();
    },
    methods: {
        handleSelectChange() {
            this.users.forEach((value, index) => {
                if (value.email === this.userForm.email) {
                    if (
                        value.is_admin !== this.userForm.is_admin ||
                        value.is_disabled !== this.userForm.is_disabled ||
                        value.can_upload_files !== this.userForm.can_upload_files
                    ) {
                        this.dirty = true;
                        return;
                    }
                    this.dirty = false;
                    return;
                }
            });
        },
        searchit: _.debounce(() => {
            Fire.$emit('searchUser');
        }, 500),
        getResults(page = 1) {
            this.loading = true;
            axios.get('/api/user?page=' + page)
                .then(({data}) => {
                this.users = data.data;
                this.usersPagination = data;
                this.loading = false;
            });
        },
        getUsersList() {
            axios.get('/api/user')
            .then(({data}) => {
                this.users = data.data;
                this.usersPagination = data;
                this.refreshUsers = false;
                this.loading = false;
            })
            .catch((error) => {
                this.loading = false;
                this.error = error.response.data;
            });
        },
        editUser(user) {
            this.showEditUserDialog = true;
            this.userForm.clear();
            this.userForm.fill(user); 
        },
        updateUser() {
            this.userForm.put('/api/user/' + this.userForm.email)
            .then(() => {
                this.showEditUserDialog = false;
                this.$toastr.Add({
                    title: 'Updated',
                    msg: 'User info updated successfully',
                    type: 'success',
                    timeout: 3500,
                    progressbar: true,
                    position: 'toast-top-right',
                });
                this.loading = true;
                this.getUsersList();
                if (this.userForm.email === this.currentUser.email) {
                    updateCurrentUser();
                    current_user.then(({data}) => {
                        this.currentUser = data;
                    });
                    if (this.userForm.is_admin !== this.currentUser.is_admin) {
                        window.location.reload();
                    }
                }
            })
            .catch(() => {
                this.$toastr.Add({
                    title: 'Error',
                    msg: 'Something went wrong',
                    type: 'error',
                    timeout: 3500,
                    progressbar: true,
                    position: 'toast-top-right',
                });
            });
        }
    }
}
</script>