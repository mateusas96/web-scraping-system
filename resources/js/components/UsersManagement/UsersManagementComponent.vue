<template>
    <div class="container">
        <md-card md-with-hover>
            <!-- <md-ripple> -->
                <md-progress-bar md-mode="indeterminate" v-if="loading || saving"></md-progress-bar>
                <md-table v-model="users" md-sort="last_name" md-sort-order="asc" md-card md-fixed-header>
                    <md-table-toolbar>
                        <div class="md-toolbar-section-start">
                            <h1 class="md-title">Users</h1>
                        </div>

                        <md-field md-clearable class="md-toolbar-section-end">
                            <md-input placeholder="Search" v-on:input="searchit" v-model="search"/>
                        </md-field>
                    </md-table-toolbar>

                    <md-table-empty-state
                        md-label="No users found"
                        :md-description="`No user found for this query. Try a different search term.`"
                        v-if="!loading">
                    </md-table-empty-state>

                    <md-table-row slot="md-table-row" slot-scope="{ item }" v-on:click="showEditUser = true; editUser(item)">
                        <md-table-cell md-label="#" style="width: 15px;">#</md-table-cell>
                        <md-table-cell md-label="First Name" md-sort-by="first_name">{{ item.first_name }}</md-table-cell>
                        <md-table-cell md-label="Last Name" md-sort-by="last_name">{{item.last_name}}</md-table-cell>
                        <md-table-cell md-label="Email" md-sort-by="email">{{ item.email }}</md-table-cell>
                        <md-table-cell md-label="Is Admin" md-sort-by="is_admin" style="width: 35px;">
                            <div v-if="item.is_admin" style="color: green;">True</div>
                            <div v-else style="color: red;">False</div>
                        </md-table-cell>
                        <md-table-cell md-label="Is Disabled" md-sort-by="is_disabled" md-numeric style="width: 35px;">
                            <div v-if="item.is_disabled" style="color: red;">True</div>
                            <div v-else style="color: green;">False</div>
                        </md-table-cell>
                        <md-table-cell md-label="Created At" md-sort-by="created_at" style="width: 250px;">{{ item.created_at }}</md-table-cell>
                        <md-table-cell md-label="Email Verified At" md-sort-by="email_verified_at" style="width: 250px;">
                            <div v-if="item.email_verified_at !== null">{{ item.email_verified_at }}</div>
                            <div v-else>-</div>
                        </md-table-cell>
                        <md-table-cell md-label="Edit User" style="width: 35px;">
                            <a href="#" v-on:click="showEditUser = true; editUser(item)"> 
                                <i class="fa fa-edit"></i>
                            </a>
                        </md-table-cell>
                    </md-table-row>
                </md-table>
            <!-- </md-ripple> -->
            <pagination :data="usersPagination" 
                v-on:pagination-change-page="getResults"
                style="padding: 5px">
            </pagination>
        </md-card>
        <md-dialog :md-active.sync="showEditUser">
            <form novalidate class="md-layout" v-on:submit.prevent="updateUser">
                <md-card>
                    <md-card-header>
                        <div class="md-title">Users</div>
                    </md-card-header>

                    <md-card-content>
                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-small-size-100">
                            <md-field >
                                <label for="first-name">First Name</label>
                                <md-input name="first-name" id="first-name" autocomplete="given-name" v-model="userForm.first_name" :disabled="true" />
                            </md-field>
                            </div>

                            <div class="md-layout-item md-small-size-100">
                            <md-field>
                                <label for="last-name">Last Name</label>
                                <md-input name="last-name" id="last-name" autocomplete="family-name" v-model="userForm.last_name" :disabled="true" />
                            </md-field>
                            </div>
                        </div>

                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-small-size-100">
                            <md-field>
                                <label for="is-admin">Is Admin</label>
                                <md-select name="is-admin" id="is-admin" v-model="userForm.is_admin" md-dense :disabled="saving">
                                    <md-option value="1">Yes</md-option>
                                    <md-option value="0">No</md-option>
                                </md-select>
                            </md-field>
                            </div>
                        </div>

                        <div class="md-layout md-gutter">
                            <div class="md-layout-item md-small-size-100">
                            <md-field>
                                <label for="is-disabled">Is Disabled</label>
                                <md-select name="is-disabled" id="is-disabled" v-model="userForm.is_disabled" md-dense :disabled="saving">
                                    <md-option value="1">Yes</md-option>
                                    <md-option value="0">No</md-option>
                                </md-select>
                            </md-field>
                            </div>
                        </div>
                    </md-card-content>

                    <md-progress-bar md-mode="indeterminate" v-if="saving" />

                    <md-card-actions>
                        <md-button type="submit" class="md-primary" :disabled="saving">Update user</md-button>
                    </md-card-actions>
                </md-card>
            </form>
        </md-dialog>
    </div>
</template>

<script>
    export default {
        data: () => ({
            loading: false,
            saving: false,
            usersPagination: {},
            users: {},
            userForm: new Form({
                first_name: '',
                last_name: '',
                email: '',
                is_admin: '',
                is_disabled: '',
            }),
            showEditUser: false,
            search: '',
        }),
        mounted() {
            this.getUsersList();
            Fire.$on('AfterUpdate', () => {
                this.getUsersList();
            });
            Fire.$on('searching',()=>{
                axios.get('../api/findUser?query=' + this.search)
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
                Fire.$emit('searching');
            }, 500),
            getResults(page = 1) {
                axios.get('../api/user?page=' + page)
                    .then(({data})=> {
                    this.users = data.data;
                    this.usersPagination = data;
                });
            },
            getUsersList() {
                this.loading = true;
                axios.get('../api/user')
                .then(({data}) => {
                    this.users = data.data;
                    this.usersPagination = data;
                    this.loading = false;
                })
            },
            editUser(user) {
                this.userForm.reset();
                this.userForm.clear();
                this.userForm.fill(user); 
            },
            updateUser() {
                this.saving = true;
                this.userForm.put('../api/user/' + this.userForm.email)
                .then(() =>{
                    Fire.$emit('AfterUpdate');
                    this.showEditUser = false;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'User info updated successfully',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    this.saving = false;
                })
                .catch(()=>{
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Something went wrong',
                        showConfirmButton: false,
                        timer: 2000
                    })
                    this.saving = false;
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .md-field {
        max-width: 300px;
    }
</style>