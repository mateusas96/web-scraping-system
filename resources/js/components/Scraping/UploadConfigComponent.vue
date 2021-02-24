<template>
    <div class="container component">
        <div class="row justify-content-center">
            <v-expansion-panels 
                focusable
                v-model="panelOpened"
                :mandatory="true"
            >
                <v-expansion-panel v-show="currentUser.can_upload_files">
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
                                    accept=".json"
                                    v-on:change="handleFilesUpload"
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
                                    :disabled="readyForUploadFiles.length < 1"
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
                                                Nr.
                                            </th>
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
                                            <td>{{ index + 1 }}</td>
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
                <v-expansion-panel>
                    <v-expansion-panel-header>
                        {{currentUser.can_upload_files == true ? 'Manage files' : 'View uploaded files'}}
                        <div
                            class="d-flex justify-end mr-6"
                        >
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on }">
                                    <v-btn 
                                        v-on:click="refreshFiles = !refreshFiles" 
                                        small
                                        v-on="on"
                                        :disabled="refreshFiles"
                                    >
                                        <v-icon v-if="refreshFiles" small>fas fa-sync fa-spin</v-icon>
                                        <v-icon v-else small>fas fa-sync</v-icon>
                                    </v-btn>
                                </template>
                                <small>Refresh files list</small>
                            </v-tooltip>
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
                                    style="max-width: 18.75rem;"
                                    clearable
                                    clear-icon="mdi-close-circle-outline"
                                ></v-text-field>
                            </v-card-title>
                            <v-data-table
                                :page.sync="filesPagination.current_page"
                                :items-per-page="filesPagination.per_page"
                                hide-default-footer
                                :loading="loading || loadFiles"
                                sort-by="last_name"
                                loading-text="Loading... Please wait"
                                :headers="headers"
                                :items="filesData"
                            >
                                <template v-slot:[`item.hashtag`]="{}">
                                    #
                                </template>

                                <template v-slot:[`item.actions`]="{ item }">
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <a
                                                :href="item.file_path + item.file_name"
                                                download
                                                v-on="on"
                                            >
                                                <v-icon>
                                                    mdi-arrow-down-bold
                                                </v-icon>
                                            </a>
                                            </template>
                                        <small>Download file</small>
                                    </v-tooltip>
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-icon
                                                class="ml-1"
                                                v-on="on"
                                                v-on:click="prepareReuploadFileDialog(item)"
                                            >
                                                mdi-pencil
                                            </v-icon>
                                        </template>
                                        <small>Edit file</small>
                                    </v-tooltip>
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-icon
                                                v-on="on"
                                                class="ml-2"
                                                v-on:click="deleteFile(item.uuid, item.file_name)"
                                                dense
                                                v-show="currentUser.is_admin"
                                            >
                                                fas fa-trash
                                            </v-icon>
                                        </template>
                                        <small>Delete file</small>
                                    </v-tooltip>
                                </template>

                                <template v-slot:top>
                                    <v-dialog
                                        v-model="reuploadFileDialog"
                                        max-width="600px"
                                        @click:outside="reuploadFileDialog = false; canUpload = false; fileReuploadError = [];"
                                    >
                                        <v-card>
                                        <v-card-title>
                                            <span class="headline">File Reupload</span>
                                        </v-card-title>
                            
                                        <v-card-text>
                                            <v-container>
                                                <v-row>
                                                    <v-col
                                                        cols="12"
                                                        md="12"
                                                    >
                                                        <v-text-field
                                                            v-model="fileReuploadForm.file_name"
                                                            label="File name"
                                                            :disabled="true"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col
                                                        cols="12"
                                                        sm="6"
                                                        md="6"
                                                    >
                                                        <v-text-field
                                                            v-model="fileReuploadForm.uploaded_by_user_username"
                                                            label="Uploaded by"
                                                            :disabled="true"
                                                        ></v-text-field>
                                                    </v-col>
                                                    <v-col
                                                        cols="12"
                                                        sm="6"
                                                        md="6"
                                                    >
                                                        <v-text-field
                                                            v-model="fileReuploadForm.updated_at"
                                                            label="Uploaded at"
                                                            :disabled="true"
                                                        ></v-text-field>
                                                    </v-col>
                                                </v-row>
                                                <v-row>
                                                    <v-col cols="12">
                                                        <v-file-input
                                                            type="file"
                                                            v-model="fileReuploadForm.file"
                                                            color="deep-purple accent-4"
                                                            label="File input"
                                                            placeholder="Select file"
                                                            prepend-icon="mdi-paperclip"
                                                            outlined
                                                            accept=".json"
                                                            v-on:change="handleFileReupload"
                                                            :rules="fileReuploadError"
                                                            show-size
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
                                                </v-row>
                                            </v-container>
                                        </v-card-text>
                            
                                        <v-card-actions>
                                            <v-spacer></v-spacer>
                                            <v-btn
                                                color="error darken-1"
                                                text
                                                v-on:click="reuploadFileDialog = false; canUpload = false; fileReuploadError = [];"
                                            >
                                                Cancel
                                            </v-btn>
                                            <v-btn
                                                color="success darken-1"
                                                text
                                                :disabled="!canUpload"
                                                v-on:click="reuploadFile"
                                            >
                                                Save
                                            </v-btn>
                                        </v-card-actions>
                                        </v-card>
                                    </v-dialog>
                                </template>
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
import {showScrollbar ,hideScrollbar, current_user} from '../../app';

export default {
    data: () => {
        return {
            panelOpened: 0,
            tempFiles: [],
            readyForUploadFiles: [],
            filesData: [],
            filesPagination: [],
            refreshFiles: false,
            headers: [
                { text: '#', align: 'start', sortable: false, value: 'hashtag' },
                { text: 'File name', value: 'file_name' },
                { text: 'File version', value: 'version' },
                { text: 'Uploaded by', value: 'uploaded_by_user_username' },
                { text: 'File size', value: 'file_size' },
                { text: 'Error message', value: 'error_msg' },
                { text: 'Uploaded at', value: 'updated_at' },
                { text: 'Actions', value: 'actions' , sortable: false },
            ],
            loading: true,
            search: '',
            reuploadFileDialog: false,
            fileReuploadForm: new Form({
                uuid: '',
                file_name: '',
                uploaded_by_user_username: '',
                updated_at: '',
                file: [],
            }),
            formData: new FormData(),
            canUpload: false,
            fileReuploadError: [],
            currentUser: [],
        }
    },
    mounted() {
        console.log(current_user);
        current_user.then(({data}) => {
            if (data.can_upload_files == false) {
                this.loading = true;
                this.panelOpened = 1;
                this.headers = [
                    { text: '#', align: 'start', sortable: false, value: 'hashtag' },
                    { text: 'File name', value: 'file_name' },
                    { text: 'File version', value: 'version' },
                    { text: 'Uploaded by', value: 'uploaded_by_user_username' },
                    { text: 'File size', value: 'file_size' },
                    { text: 'Error message', value: 'error_msg' },
                    { text: 'Uploaded at', value: 'updated_at' },
                ];
            }
            this.currentUser = data;
        });
        hideScrollbar();
        Fire.$on('refreshFiles', () => {
            this.canUpload = false;
            this.loading = true;
            this.fileReuploadForm.clear();
            this.formData = new FormData();
            this.reuploadFileDialog = false;
            this.getFilesData();
        });
        Fire.$on('searchFile', () => {
            this.loading = true;
            axios.get('/api/find_file?query=' + (this.search === null ? '' : this.search))
            .then(({data}) => {
                this.loading = false;
                this.filesPagination = data;
                this.filesData = data.data;
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
    watch: {
        refreshFiles: {
            handler: function(newVal, oldVal) {
                if (newVal) {
                    this.loading = true;
                    this.getFilesData();
                }
            }
        },
        panelOpened: {
            handler: function(newVal, oldVal) {
                if (newVal) {
                    this.loading = true;
                    this.getFilesData();
                }
            }
        },
        filesData: {
            handler: function() {
                setTimeout(() => {
                    $(window).height() < 950 &&
                    $('.container.component').height() > $(window).height() - 110 ?
                    showScrollbar() : hideScrollbar();
                }, 300);
            }
        },
    },
    methods: {
        handleFileReupload() {
            if (this.fileReuploadForm.file === undefined || this.fileReuploadForm.file < 1 ) {
                this.fileReuploadError = [];
                this.canUpload = false;
                return;
            }
            if (this.fileReuploadForm.file.type !== 'application/json') {
                this.fileReuploadError = [
                    'Only JSON file formats are supported',
                ];
                return;
            } else if (this.fileReuploadForm.file.name !== this.fileReuploadForm.file_name) {
                this.fileReuploadError = [
                    'Selected file should be with the same name: ' + this.fileReuploadForm.file_name,
                ];
                return;
            } else {
                this.fileReuploadError = [];
                this.canUpload = true;
            }
        },
        reuploadFile() {
            this.formData.append('file', this.fileReuploadForm.file);
            axios.post('/api/update_file/' + this.fileReuploadForm.uuid, this.formData)
                .then(({data}) => {
                    if (data.error) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true,
                        });
                        return;
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true,
                    });
                    this.canUpload = false;
                    this.loading = true;
                    this.fileReuploadForm.clear();
                    this.formData = new FormData();
                    this.reuploadFileDialog = false;
                    this.getFilesData();
                });
        },
        prepareReuploadFileDialog(file) {
            this.reuploadFileDialog = true;
            this.fileReuploadForm.clear();
            this.fileReuploadForm.fill(file);
        },
        searchit: _.debounce(() => {
            Fire.$emit('searchFile');
        }, 500),
        getResults(page = 1) {
            this.loading = true;
            axios.get('/api/file?page=' + page)
                .then(({data}) => {
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
                this.loadFiles = false;
            })
            .catch((error) => {

            });
        },
        uploadFiles() {
            this.formData.append('filesCount', this.readyForUploadFiles.length);

            this.readyForUploadFiles.forEach((value, index) => {
                this.formData.append('file' + index, this.readyForUploadFiles[index]);
            })

            axios.post('/api/file', this.formData)
            .then(({data}) => {
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
                        this.$toastr.Add({
                            title: 'Uploaded',
                            msg: 'Other files uploaded successfully',
                            type: 'success',
                            timeout: 3500,
                            progressbar: true,
                            position: 'toast-top-right',
                        });
                    }
                    this.readyForUploadFiles = [];
                } else {
                    this.$toastr.Add({
                        title: 'Uploaded',
                        msg: 'Files uploaded successfully',
                        type: 'success',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    });
                    this.readyForUploadFiles = [];
                }
                this.formData = new FormData();
            })
            .catch((error) => {
                console.log(error);
            });
        },
        handleFilesUpload() {
            let showedTooManyFilesErrorOnce = false;
            let showedWrongFileTypeErrorOnce = false;

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
                if (value.type !== 'application/json') {
                    showedWrongFileTypeErrorOnce === false ? 
                    this.$toastr.Add({
                        title: 'Wrong file type',
                        msg: 'Only JSON file formats are supported',
                        type: 'warning',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    }) : null;
                    showedWrongFileTypeErrorOnce = true;
                    return;
                }
                if (this.readyForUploadFiles.length === 10) {
                    showedTooManyFilesErrorOnce === false ? 
                    this.$toastr.Add({
                        title: 'Too many files',
                        msg: 'You can upload only 10 files at once!',
                        type: 'error',
                        timeout: 7000,
                        progressbar: true,
                        position: 'toast-top-right',
                    }) : null;
                    showedTooManyFilesErrorOnce = true;
                    return;
                }
                this.readyForUploadFiles.push(this.tempFiles[index]);
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
                    this.$toastr.Add({
                        title: 'Removed',
                        msg: 'All files successfully removed',
                        type: 'success',
                        timeout: 3500,
                        progressbar: true,
                        position: 'toast-top-right',
                    });
                }
            });
        },
        deleteFile(fileUuid, fileName) {
            Swal.fire({
                title: 'Delete file',
                text: `Are you sure you want to  delete file - ${fileName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(`/api/file/${fileUuid}`)
                    .then(({data}) => {
                        if (!data.error) {
                            this.refreshFiles = true;
                            this.$toastr.Add({
                                title: 'Success',
                                msg: data.message,
                                type: 'success',
                                timeout: 3500,
                                progressbar: true,
                                position: 'toast-top-right',
                            });
                        }
                    });
                }
            });
        }
    }
}
</script>
