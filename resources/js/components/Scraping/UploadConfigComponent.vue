<template>
    <div class="container">
        <div class="row justify-content-center">
            <v-expansion-panels 
                focusable
                v-model="panelOpened"
                :mandatory="true"
            >
                <v-expansion-panel>
                    <v-expansion-panel-header>
                        Upload files
                    </v-expansion-panel-header>
                    <v-expansion-panel-content class="mt-12">
                        <v-row no-gutters justify="center" align="center">
                            <v-col cols="10">
                                <v-file-input
                                    type="file"
                                    v-model="tempFiles"
                                    color="deep-purple accent-4"
                                    label="File input"
                                    multiple
                                    placeholder="Select your files"
                                    prepend-icon="mdi-paperclip"
                                    outlined
                                    accept=".txt"
                                    v-on:change="handleFilesChange"
                                >
                                    <template v-slot:selection="{ text }">
                                        <v-chip
                                            color="deep-purple accent-4"
                                            dark
                                            label
                                            small
                                        >
                                            {{ text }}
                                        </v-chip>
                                    </template>
                                </v-file-input>
                            </v-col>
                            <v-col cols="2" class="pl-8 pb-8">
                                <v-btn
                                    color="success"
                                    v-on:click="uploadFiles"
                                    :disabled="readyForUploadFiles < 1"
                                >
                                    Upload files
                                </v-btn>
                            </v-col>
                        </v-row>
                        <v-card v-if="readyForUploadFiles.length > 0" class="mx-auto">
                            <v-simple-table
                                fixed-header
                                height="250px"
                            >
                                <template v-slot:default>
                                <thead>
                                    <tr>
                                    <th class="text-left">
                                        Name
                                    </th>
                                    <th class="text-left">
                                        Action
                                    </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                    v-for="(file, index) in readyForUploadFiles"
                                    :key="index"
                                    >
                                    <td>{{ file.name }}</td>
                                    <td>
                                        <v-icon
                                            class="ml-4"
                                            v-on:click="handleFileDelete(index)"
                                            color="error darken-2"
                                        >
                                            mdi-trash-can
                                        </v-icon>
                                    </td>
                                    </tr>
                                </tbody>
                                </template>
                            </v-simple-table>
                        </v-card>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn
                                color="error darken-2"
                                v-on:click="handleAllFilesDelete"
                                v-if="readyForUploadFiles.length > 0"
                            >
                                Delete all files
                            </v-btn>
                        </v-card-actions>
                    </v-expansion-panel-content>
                </v-expansion-panel>
                <v-expansion-panel v-on:click="loadFiles = !loadFiles">
                    <v-expansion-panel-header>
                        Manage files
                        <div
                            class="d-flex justify-end mr-6"
                        >
                            <v-btn 
                                v-on:click="refreshFiles = !refreshFiles" 
                                small
                                :disabled="refreshFiles"
                            >
                            <v-icon v-if="refreshFiles" small>fas fa-sync fa-spin</v-icon>
                            <v-icon v-else small>fas fa-sync</v-icon>
                        </v-btn>
                        </div>
                    </v-expansion-panel-header>
                    <v-expansion-panel-content>
                        <template>
                            <v-card-title>
                                Files list
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
                                :page.sync="filesPagination.current_page"
                                :items-per-page="filesPagination.per_page"
                                hide-default-footer
                                :loading="loading"
                                sort-by="last_name"
                                loading-text="Loading... Please wait"
                                :headers="headers"
                                :items="filesData"
                            >
                                <template v-slot:[`item.hashtag`]="{}">
                                    #
                                </template>
                                <template v-slot:[`item.actions`]="{ item }">
                                    <v-icon
                                        class="ml-4"
                                        v-on:click="editFile(item)"
                                    >
                                        mdi-pencil
                                    </v-icon>
                                </template>

                                <!-- <template v-slot:top>
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
                                                v-on:click="console.log('idiNX')"
                                            >
                                                Cancel
                                            </v-btn>
                                            <v-btn
                                                color="success darken-1"
                                                text
                                                v-on:click="console.log('idiNX')"
                                            >
                                                Save
                                            </v-btn>
                                        </v-card-actions>
                                        </v-card>
                                    </v-dialog>
                                </template> -->

                            </v-data-table>
                            <v-pagination
                                v-model="filesPagination.current_page"
                                :length="filesPagination.last_page"
                                v-on:input="getResults"
                                :total-visible="7"
                            ></v-pagination>
                        </template>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-expansion-panels>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
                panelOpened: [0],
                tempFiles: [],
                readyForUploadFiles: [],
                loadFiles: false,
                filesData: [],
                filesPagination: [],
                refreshFiles: false,
                headers: [
                    { text: '#', align: 'start', sortable: false, value: 'hashtag' }, 
                    { text: 'File name', value: 'file_name' }, 
                    { text: 'Uploaded by', value: 'uploaded_by_user_username' },
                    { text: 'File size', value: 'file_size' },
                    { text: 'Edit User', value: 'actions' , sortable: false}
                ],
                loading: true,
                search: '',
            }
        },
        mounted() {

        },
        watch: {
            loadFiles: {
                handler: function(newVal, oldVal) {
                    if (newVal) {
                        this.getFilesData();
                    }
                }
            },
            refreshFiles: {
                handler: function(newVal, oldVal) {
                    if (newVal) {
                        this.getFilesData();
                    }
                }
            }
        },
        methods: {
            editFile(file) {
                console.log(file);
            },
            searchit: _.debounce(() => {
                Fire.$emit('searching');
            }, 500),
            getResults(page = 1) {
                this.loading = true;
                axios.get('/api/file?page=' + page)
                    .then(({data})=> {
                    this.filesPagination = data;
                    this.filesData = data.data;
                    this.loading = false;
                });
            },
            getFilesData() {
                axios.get('/api/file')
                .then(({data}) => {
                    this.filesPagination = data;
                    this.filesData = data.data;
                    this.refreshFiles = false;
                    this.loading = false;
                })
                .catch((error) => {

                });
            },
            uploadFiles() {
                let formData = new FormData();

                formData.append('filesCount', this.readyForUploadFiles.length);

                this.readyForUploadFiles.forEach((value, index) => {
                    formData.append('file' + index, this.readyForUploadFiles[index]);
                })

                axios.post('/api/file', formData)
                .then(({data}) => {
                    console.log(data);
                    if (data.error) {
                        this.$toastr.Add({
                            title: data.message + ': ',
                            msg: data.not_uploaded_files,
                            type: 'info',
                            timeout: 7000,
                            progressbar: true,
                            position: 'toast-top-right',
                        });
                        if (data.files_uploaded.length > 0) {
                            this.$toastr.s('Other files uploaded successfully');
                        }
                        this.readyForUploadFiles = [];
                    } else {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Files uploaded successfully',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        });
                        this.readyForUploadFiles = [];
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
            },
            handleFilesChange() {
                if (this.readyForUploadFiles.length > 0) {
                    this.readyForUploadFiles.forEach((value, index) => {
                        this.tempFiles.forEach((val, idx) => {
                            if (value.name === val.name) {
                                this.$toastr.w('File: ' + value.name + ' already exists in the list');
                                this.tempFiles.splice(idx, 1);
                            }
                        })
                    });
                }
                this.tempFiles.forEach((value, index) => {
                    if (value.type !== 'text/plain') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'Only .txt file formats are supported',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        })
                    } else {
                        this.readyForUploadFiles.push(this.tempFiles[index]);
                    }
                });
                this.tempFiles = [];
            },
            handleFileDelete(index) {
                this.readyForUploadFiles.splice(index, 1);
            },
            handleAllFilesDelete() {
                Swal.fire({
                    title: 'Are you sure you want to remove all files?',
                    showDenyButton: true,
                    confirmButtonText: 'Yes',
                    denyButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.readyForUploadFiles = [];
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'All files successfully removed',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true,
                        })
                    }
                })
            }
        }
    }
</script>
